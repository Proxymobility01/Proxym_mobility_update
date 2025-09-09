<?php

namespace App\Http\Controllers;

use App\Models\BatteryValide;
use App\Services\BmsApi;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Validator;

class BmsController extends Controller
{
    /**
     * (Optional) Show selection UI — supply batteries list to your Blade
     */
    public function configure()
    {
        $batteries = BatteryValide::query()
            ->whereNull('deleted_at')
            ->whereIn('statut', ['valide', 'validé', 'valides', 'en service', 'en attente']) // adjust
            ->orderBy('mac_id')
            ->get(['mac_id']);

        return view('bms.configure', compact('batteries'));
    }

    /**
     * Pre-flight: check online status (batch) and pull current settings per battery.
     * Returns JSON you can show in a modal/table before applying changes.
     */
    public function preview(Request $req)
    {
        $validated = $req->validate([
            'batteries' => 'required|string', // comma-separated mac_ids
        ]);

        $macs = $this->parseMacList($validated['batteries']);
        if (empty($macs)) {
            return response()->json(['ok' => false, 'message' => 'No batteries provided'], 422);
        }

        // Lookup vendor per mac_id
        $rows = BatteryValide::whereIn('mac_id', $macs)->get(['mac_id', 'fabriquant']);
        $byVendor = $rows->groupBy(fn($r) => $r->vendor_key);

        $result = [];
        foreach ($byVendor as $vendor => $items) {
            $api = BmsApi::forVendor($vendor);
            $numbers = $items->pluck('mac_id')->values()->all();

            // Batch status first
            $status = [];
            foreach (BmsApi::chunkNumbers($numbers) as $chunk) {
                $status[] = $api->bmsRealTimeStateByParam($chunk);
            }

            // Per battery settings
            $settings = [];
            foreach ($numbers as $n) {
                $settings[$n] = $api->bmsRealTimeState($n);
            }

            $result[$vendor] = [
                'status'   => $status,
                'settings' => $settings,
            ];
        }

        return response()->json(['ok' => true, 'data' => $result]);
    }

    /**
     * Submit configuration:
     * - batteries: "mac1,mac2,..."
     * - params:    associative array of CPBV/SRBV/... (strings or numbers)
     * - overrides: {"mac1": {"CPBV": 3450, ...}, ...} (optional)
     */
    public function send(Request $req)
    {
        $validator = Validator::make($req->all(), [
            'batteries' => 'required|string',
            'params'    => 'required|array',
            'overrides' => 'nullable|array',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $macs      = $this->parseMacList($req->input('batteries'));
        $baseParam = $this->sanitizeParams($req->input('params', []));
        $overrides = $this->sanitizePerBatteryOverrides($req->input('overrides', []));

        if (empty($macs)) {
            return back()->with('response', ['errorCode' => 1, 'errorDescribe' => 'No batteries selected']);
        }

        // Validate param ordering/rules (lightweight server-side guard)
        $orderErr = $this->validateParamRules($baseParam);
        if ($orderErr) {
            return back()->with('response', ['errorCode' => 1, 'errorDescribe' => $orderErr])->withInput();
        }
        foreach ($overrides as $mac => $ov) {
            $err = $this->validateParamRules($ov);
            if ($err) {
                return back()->with('response', ['errorCode' => 1, 'errorDescribe' => "Invalid overrides for {$mac}: {$err}"]);
            }
        }

        // Map MAC -> vendor (huiming/vercom)
        $rows = BatteryValide::whereIn('mac_id', $macs)->get(['mac_id', 'fabriquant']);
        if ($rows->isEmpty()) {
            return back()->with('response', ['errorCode' => 1, 'errorDescribe' => 'Selected batteries were not found in battery_valides']);
        }

        // Group by vendor
        $byVendor = $rows->groupBy(fn($r) => $r->vendor_key);

        // Build groups by (vendor × effective params JSON)
        $summary = [];
        foreach ($byVendor as $vendor => $items) {
            $api = BmsApi::forVendor($vendor);

            // Build: paramsKey => [numbers]
            $groups = [];
            foreach ($items as $row) {
                $mac = $row->mac_id;
                $effective = array_merge($baseParam, $overrides[$mac] ?? []);
                $paramsKey = md5(json_encode($effective, JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES|JSON_NUMERIC_CHECK));
                $groups[$paramsKey]['params']   = $effective;
                $groups[$paramsKey]['numbers'][] = $mac;
            }

            // Push per group (chunk numbers safely)
            foreach ($groups as $g) {
                $numbers = $g['numbers'];
                $bmsparam = $g['params'];

                foreach (BmsApi::chunkNumbers($numbers) as $chunk) {
                    try {
                        $resp = $api->sendBmsCommands($chunk, $bmsparam);
                        $summary[] = [
                            'vendor'   => $vendor,
                            'numbers'  => $chunk,
                            'bmsparam' => $bmsparam,
                            'response' => $resp,
                        ];
                    } catch (\Throwable $e) {
                        $summary[] = [
                            'vendor'   => $vendor,
                            'numbers'  => $chunk,
                            'bmsparam' => $bmsparam,
                            'response' => ['errorCode' => 500, 'errorDescribe' => $e->getMessage()],
                        ];
                    }
                }
            }
        }

        // Collate a top-level toast message (simple)
        $errors = collect($summary)->filter(fn($s) => (int)($s['response']['errorCode'] ?? 1) !== 0)->count();
        $ok     = $errors === 0;

        return back()->with('response', [
            'errorCode'      => $ok ? 0 : 1,
            'errorDescribe'  => $ok ? 'Commands queued successfully' : "Completed with {$errors} error group(s)",
            'details'        => $summary, // keep for later UI table if you want
        ]);
    }

    // ================= helpers =================

    private function parseMacList(string $csv): array
    {
        $list = collect(explode(',', $csv))
            ->map(fn($s) => trim($s))
            ->filter()
            ->unique()
            ->values()
            ->all();

        // Optional: enforce 12+ chars; keep flexible if vendors differ
        return array_values(array_filter($list, fn($m) => strlen($m) >= 8));
    }

    private function sanitizeParams(array $params): array
    {
        // Trim strings, keep numeric as given; API accepts numeric/strings.
        $out = [];
        foreach ($params as $k => $v) {
            if ($v === null || $v === '') continue;
            $out[$k] = is_string($v) ? trim($v) : $v;
        }
        return $out;
    }

    private function sanitizePerBatteryOverrides(array $overrides): array
    {
        $clean = [];
        foreach ($overrides as $mac => $ov) {
            if (!is_array($ov)) continue;
            $clean[$mac] = $this->sanitizeParams($ov);
        }
        return $clean;
    }

    /**
     * Minimal param logic checks based on vendor doc:
     * - Voltage ladders
     * - Overcurrent gear ordering
     * - Temperature ladder ordering per sensor slot
     */
    private function validateParamRules(array $p): ?string
    {
        // Only check if fields are present — keep it permissive.

        // Voltage ordering: cell thresholds
        $v = fn($k) => isset($p[$k]) ? floatval($p[$k]) : null;

        $pairs = [
            // cell: CPBV > SRBV > DRBV > DPBV
            ['CPBV','SRBV','>'],
            ['SRBV','DRBV','>'],
            ['DRBV','DPBV','>'],

            // pack ladder: ASCPV > ACPBV > ASRBV > ADRBV > ADPBV > ASDPV
            ['ASCPV','ACPBV','>'],
            ['ACPBV','ASRBV','>'],
            ['ASRBV','ADRBV','>'],
            ['ADRBV','ADPBV','>'],
            ['ADPBV','ASDPV','>'],
        ];
        foreach ($pairs as [$a,$b,$op]) {
            $va = $v($a); $vb = $v($b);
            if ($va !== null && $vb !== null) {
                if ($op === '>' && !($va > $vb)) return "{$a} must be greater than {$b}";
            }
        }

        // Overcurrent gears (charge)
        $c = ['NCHPCA_1','NCHPCA_2','NCHPCA_3'];
        if (count(array_filter($c, fn($k)=>isset($p[$k]))) === 3) {
            if (!($v($c[0]) > $v($c[1]) && $v($c[1]) > $v($c[2]))) {
                return 'Charge current gears must be strictly descending: NCHPCA_1 > NCHPCA_2 > NCHPCA_3';
            }
        }
        $ct = ['NCHPDT_1','NCHPDT_2','NCHPDT_3'];
        if (count(array_filter($ct, fn($k)=>isset($p[$k]))) === 3) {
            if (!($v($ct[0]) < $v($ct[1]) && $v($ct[1]) < $v($ct[2]))) {
                return 'Charge delay gears must be ascending: NCHPDT_1 < NCHPDT_2 < NCHPDT_3';
            }
        }

        // Overcurrent gears (discharge)
        $d = ['NDHPCA_1','NDHPCA_2','NDHPCA_3'];
        if (count(array_filter($d, fn($k)=>isset($p[$k]))) === 3) {
            if (!($v($d[0]) > $v($d[1]) && $v($d[1]) > $v($d[2]))) {
                return 'Discharge current gears must be strictly descending: NDHPCA_1 > NDHPCA_2 > NDHPCA_3';
            }
        }
        $dt = ['NDHPDT_1','NDHPDT_2','NDHPDT_3'];
        if (count(array_filter($dt, fn($k)=>isset($p[$k]))) === 3) {
            if (!($v($dt[0]) < $v($dt[1]) && $v($dt[1]) < $v($dt[2]))) {
                return 'Discharge delay gears must be ascending: NDHPDT_1 < NDHPDT_2 < NDHPDT_3';
            }
        }

        // Temperature ladders: for each sensor 0..5 (example keys)
        for ($i = 0; $i <= 5; $i++) {
            $op = "NPCMOP_{$i}"; // over-protect
            $or = "NPCMOR_{$i}"; // over-recover
            $up = "NPCMUP_{$i}"; // under-protect
            $ur = "NPCMUR_{$i}"; // under-recover

            if (isset($p[$op], $p[$or]) && !($v($op) > $v($or))) return "{$op} must be greater than {$or}";
            if (isset($p[$ur], $p[$up]) && !($v($ur) > $v($up))) return "{$ur} must be greater than {$up}";
        }

        return null; // ok
    }
}
