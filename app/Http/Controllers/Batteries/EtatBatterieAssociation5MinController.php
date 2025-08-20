<?php

namespace App\Http\Controllers\Batteries;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\BatteriesValide;
use Carbon\Carbon;

class EtatBatterieAssociation5MinController extends Controller
{
    private function roundToNearestFiveMinutes($timestamp)
    {
        $carbon = Carbon::parse($timestamp);
        $minute = (int) $carbon->format('i');
        $remainder = $minute % 5;
        return $carbon->subMinutes($remainder)->format('Y-m-d H:i:00');
    }

    public function index(Request $request)
    {
        Log::info('📊 Début génération du tableau SOC');

        // 🎯 Détermination de la période
        $periode = $request->input('periode', 'today');
        $start = now()->startOfDay();
        $end = now()->endOfDay();

        if ($periode === 'custom') {
            $date = $request->input('date');
            $start = Carbon::parse($date)->startOfDay();
            $end = Carbon::parse($date)->endOfDay();
        } elseif ($periode === 'week') {
            $start = now()->startOfWeek();
            $end = now()->endOfWeek();
        } elseif ($periode === 'month') {
            $start = now()->startOfMonth();
            $end = now()->endOfMonth();
        } elseif ($periode === 'year') {
            $start = now()->startOfYear();
            $end = now()->endOfYear();
        } elseif ($periode === 'range') {
            $start = Carbon::parse($request->input('start'));
            $end = Carbon::parse($request->input('end'))->endOfDay();
        }

        Log::info("📅 Période sélectionnée : de $start à $end");

        // 🔋 Liste des batteries valides
        $batteries = BatteriesValide::pluck('mac_id')->toArray();
        Log::info("🔋 Nombre de batteries valides : " . count($batteries));

        // 🕐 Création des créneaux horaires de 5 minutes
        $timeSlots = [];
        $current = $start->copy();
        while ($current <= $end) {
            $timeSlots[] = $current->format('Y-m-d H:i:00');
            $current->addMinutes(5);
        }

        // 📦 Initialisation du tableau final
        $data = [];
        foreach ($batteries as $macId) {
            foreach ($timeSlots as $time) {
                $data[$macId][$time] = null;
            }
        }

        // 📥 Lecture des SOC depuis la table, extraction directe JSON
        $rows = DB::table('historique_bms_data_actuel')
            ->select(
                'mac_id',
                'timestamp',
                DB::raw("JSON_UNQUOTE(JSON_EXTRACT(state, '$.SOC')) as soc")
            )
            ->whereIn('mac_id', $batteries)
            ->whereBetween('timestamp', [$start, $end])
            ->orderBy('timestamp')
            ->get();

        Log::info("📥 Nombre d'enregistrements BMS lus : " . count($rows));

        foreach ($rows as $row) {
            $timeKey = $this->roundToNearestFiveMinutes($row->timestamp);
            $mac = $row->mac_id;
            $soc = is_numeric($row->soc) ? (int) $row->soc : null;

            if ($soc !== null) {
                $data[$mac][$timeKey] = $soc;
                Log::debug("✅ MAC: $mac | Heure: $timeKey | SOC: $soc");
            } else {
                Log::debug("❌ SOC non numérique pour MAC: $mac à $timeKey");
            }
        }

        Log::info('✅ Fin du traitement. Envoi des données à la vue.');

        return view('batteries.etat_batteries', [
            'data' => $data,
            'timeSlots' => $timeSlots,
            'periode' => $periode,
            'start' => $start->format('Y-m-d'),
            'end' => $end->format('Y-m-d'),
        ]);
    }
}
