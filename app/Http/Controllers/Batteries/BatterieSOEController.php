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
    $now = Carbon::now();

    Log::info("🕑 [CRON SOE] Lancement du stockage SOE pour toutes les dates historiques...");

    $macIds = BatteriesValide::pluck('mac_id')->filter()->unique()->toArray();

    if (empty($macIds)) {
        Log::warning('[CRON SOE] Aucun mac_id trouvé dans batteries_valides.');
        return;
    }

    // Étape 1 : récupérer les enregistrements déjà existants pour éviter les doublons
    $existing = DB::table('soe_batteries')
        ->select('mac_id', 'date')
        ->get()
        ->map(function ($row) {
            return $row->mac_id . '_' . $row->date;
        })
        ->toArray();

    // Étape 2 : extraire les SOE des jours où SOC = 100% pour toutes les batteries
    $results = DB::table('historique_bms_data_actuel')
        ->select(
            'mac_id',
            DB::raw("MIN(JSON_UNQUOTE(JSON_EXTRACT(state, '$.SOC'))) as soc"),
            DB::raw("MIN(JSON_UNQUOTE(JSON_EXTRACT(state, '$.SYLA'))) as soe"),
            DB::raw("DATE(timestamp) as date"),
            DB::raw("MIN(timestamp) as first_seen")
        )
        ->whereIn('mac_id', $macIds)
        ->whereRaw("JSON_EXTRACT(state, '$.SOC') = '100'")
        ->groupBy(DB::raw('mac_id, DATE(timestamp)'))
        ->get();

    $inserted = 0;

    foreach ($results as $record) {
        $uniqueKey = $record->mac_id . '_' . $record->date;

        if (in_array($uniqueKey, $existing)) {
            continue; // déjà enregistré → on ignore
        }

        DB::table('soe_batteries')->insert([
            'mac_id' => $record->mac_id,
            'soc' => $record->soc,
            'soe' => $record->soe,
            'date' => $record->date,
            'first_seen' => $record->first_seen,
            'created_at' => $now,
            'updated_at' => $now,
        ]);
        $inserted++;
    }

    Log::info("✅ [CRON SOE] $inserted nouveaux enregistrements insérés dans soe_batteries.");
}

}
