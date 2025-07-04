<?php

namespace App\Http\Controllers\Batteries;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\BatteriesValide;

class BatterieSOEController extends Controller
{
    /**
     * Affiche les SOE (SYLA) lorsque SOC atteint 100% pour chaque batterie, par jour.
     */
    public function showSoeAtFullCharge(Request $request)
    {
        $start = $request->query('start');
        $end = $request->query('end');

        Log::info('🔍 [SOE] Affichage SOE à 100% en vue');
        Log::info("➡️ Dates: start = $start | end = $end");

        if (!$start || !$end) {
            Log::warning('❌ [SOE] Dates manquantes');
            return view('batteries.soe', ['error' => 'Veuillez fournir les dates de début et de fin.']);
        }

        // 🔎 Récupération des MAC ID valides
        $macIds = BatteriesValide::pluck('mac_id')->filter()->unique()->toArray();
        Log::info('✅ MAC IDs trouvés dans batteries_valides : ' . count($macIds));

        if (empty($macIds)) {
            Log::warning('❌ Aucun MAC ID trouvé dans batteries_valides');
            return view('batteries.soe', ['error' => 'Aucun identifiant de batterie trouvé.']);
        }

        // 🔄 Récupération des données BMS filtrées et agrégées
        Log::info('⏳ [SOE] Requête BMS lancée...');

        $results = DB::table('bms_data')
            ->select(
                'mac_id',
                DB::raw("MIN(JSON_UNQUOTE(JSON_EXTRACT(state, '$.SOC'))) as soc"),
                DB::raw("MIN(JSON_UNQUOTE(JSON_EXTRACT(state, '$.SYLA'))) as soe"),
                DB::raw("DATE(timestamp) as date"),
                DB::raw("MIN(timestamp) as first_seen")
            )
            ->whereIn('mac_id', $macIds)
            ->whereRaw("JSON_EXTRACT(state, '$.SOC') = '100'")
            ->whereBetween('timestamp', [$start . ' 00:00:00', $end . ' 23:59:59'])
            ->groupBy(DB::raw('mac_id, DATE(timestamp)'))
            ->orderBy('first_seen', 'asc')
            ->get();

        Log::info('✅ [SOE] Résultats récupérés : ' . $results->count());

        return view('batteries.soe', [
            'results' => $results,
            'start' => $start,
            'end' => $end,
            'error' => null
        ]);
    }
}
