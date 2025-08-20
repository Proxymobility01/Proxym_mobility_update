<?php

namespace App\Http\Controllers\Batteries;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\BatteriesValide;
use Carbon\Carbon;

class BatterieSOEController extends Controller
{
    /**
     * Affiche les SOE (SYLA) lorsque SOC atteint 100% pour chaque batterie, par jour.
     */
    public function showSoeAtFullCharge(Request $request)
{
    $start = $request->query('start');
    $end = $request->query('end');

    if (!$start || !$end) {
        return view('batteries.soe', [
            'results' => [],
            'start' => null,
            'end' => null,
            'error' => 'Veuillez fournir les dates de début et de fin.'
        ]);
    }

    $results = DB::table('soe_batteries')
        ->whereBetween('date', [$start, $end])
        ->orderBy('date', 'asc')
        ->get();

    return view('batteries.soe', [
        'results' => $results,
        'start' => $start,
        'end' => $end,
        'error' => null
    ]);
}


    /**
     * 📅 Méthode appelée automatiquement chaque jour par cron pour stocker les SOE à SOC = 100%.
     */
   public function storeDailySoe()
{
    $now = \Carbon\Carbon::now();
    \Log::info("🕑 [CRON SOE] START storeDailySoe");

    // 1) Récup liste des batteries (mac_id)
    $macIds = \App\Models\BatteriesValide::pluck('mac_id')
        ->filter()         // enlève null/vides
        ->unique()
        ->values()
        ->toArray();

    $expected = count($macIds);
    \Log::info("[SOE] macIds: expected={$expected}");

    if ($expected === 0) {
        \Log::warning('[SOE] Aucun mac_id trouvé dans batteries_valides.');
        return;
    }

    $found = 0; $inserts = 0; $updates = 0; $missing = [];

    \DB::transaction(function () use ($macIds, $now, &$found, &$inserts, &$updates, &$missing) {
        foreach ($macIds as $mac) {
            // 2) Dernier enregistrement SOC=100% pour CE mac_id
            $row = \DB::table('historique_bms_data_actuel')
                ->where('mac_id', $mac)
                ->whereRaw("CAST(JSON_UNQUOTE(JSON_EXTRACT(state, '$.SOC')) AS UNSIGNED) = 100")
                ->orderByDesc('timestamp')
                ->selectRaw("
                    ? as mac_id,
                    JSON_UNQUOTE(JSON_EXTRACT(state, '$.SOC'))  as soc,
                    JSON_UNQUOTE(JSON_EXTRACT(state, '$.SYLA')) as soe,
                    DATE(`timestamp`)                           as date,
                    `timestamp`                                 as first_seen
                ", [$mac])
                ->first();

            if (!$row) {
                $missing[] = $mac;
                continue;
            }

            $found++;

            // 3) Upsert par (mac_id, date) – respecte ta contrainte unique
            $key = [
                'mac_id' => $mac,
                'date'   => $row->date,
            ];

            $payload = [
                'soc'        => is_null($row->soc) ? null : (int) $row->soc,
                'soe'        => is_null($row->soe) ? null : (float) $row->soe,
                'first_seen' => $row->first_seen,
                'updated_at' => $now,
            ];

            $exists = \DB::table('soe_batteries')->where($key)->exists();

            \DB::table('soe_batteries')->updateOrInsert(
                $key,
                $exists ? $payload : array_merge($payload, ['created_at' => $now])
            );

            $exists ? $updates++ : $inserts++;
        }
    });

    $missCount = count($missing);
    \Log::info("[SOE] found(last SOC=100)={$found}");
    \Log::info("[SOE] coverage: expected={$expected} | found={$found} | missing={$missCount}");
    if ($missCount > 0) {
        \Log::warning("[SOE] missing_mac_ids=" . implode(',', $missing));
    }
    \Log::info("[SOE] UPSERT: inserts={$inserts} | updates={$updates}");
    \Log::info("✅ [CRON SOE] END storeDailySoe");
}


}
