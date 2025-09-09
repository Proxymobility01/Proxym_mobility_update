<?php
// app/Http/Controllers/power_reading/PowerReadingController.php

namespace App\Http\Controllers\power_reading;

use App\Http\Controllers\Controller;
use App\Models\PowerReading;
use App\Models\Agence;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Carbon\Carbon;

use App\Exports\PowerReadingsPivotExport;
use Maatwebsite\Excel\Facades\Excel;
use PDF;

class PowerReadingController extends Controller
{
    /**
     * Show the pivot table view.
     */
    public function index(Request $request)
    {
        // Modes: latest | first | sum | avg | max | all
        $mode    = $request->get('mode', 'latest');
        $dateMin = $request->date_min ? Carbon::parse($request->date_min) : now()->startOfMonth();
        $dateMax = $request->date_max ? Carbon::parse($request->date_max) : now()->endOfMonth();

        $agences = Agence::orderBy('nom_agence')->get(['id', 'nom_agence']);
        $rows    = $this->buildPivot($agences, $dateMin, $dateMax, $mode);

        return view('power_readings.index-pivot', [
            'agences' => $agences,
            'rows'    => $rows, // each row: ['date'=>'Y-m-d','time'=>HH:mm|null,'cells'=>[agence_id=>['kw1'..'kw4']]]
            'dateMin' => $dateMin->toDateString(),
            'dateMax' => $dateMax->toDateString(),
            'mode'    => $mode,
        ]);
    }

    /**
     * Excel export (uses your Export class).
     */
    public function exportExcel(Request $request)
    {
        return Excel::download(
            new PowerReadingsPivotExport(
                $request->date_min,
                $request->date_max,
                $request->get('mode', 'latest') // keep if your export supports mode
            ),
            'Rapport_Suivi_Elec_Pivot.xlsx'
        );
    }

    /**
     * PDF export (reuses the same pivot data & view).
     */
    public function exportPdf(Request $request)
    {
        $mode    = $request->get('mode', 'latest');
        $dateMin = $request->date_min ? Carbon::parse($request->date_min) : now()->startOfMonth();
        $dateMax = $request->date_max ? Carbon::parse($request->date_max) : now()->endOfMonth();

        $agences = Agence::orderBy('nom_agence')->get(['id', 'nom_agence']);
        $rows    = $this->buildPivot($agences, $dateMin, $dateMax, $mode);

        $pdf = PDF::loadView('power_readings.export-pivot-pdf', [
            'agences' => $agences,
            'rows'    => $rows,
            'dateMin' => $dateMin->toDateString(),
            'dateMax' => $dateMax->toDateString(),
            'mode'    => $mode,
        ])->setPaper('a4', 'landscape');

        return $pdf->download('Rapport_Suivi_Elec_Pivot.pdf');
    }

    /**
     * Build pivot rows according to selected mode.
     *
     * @param  \Illuminate\Support\Collection<int, \App\Models\Agence>  $agences
     * @return array<int, array{date:string, time:?string, cells:array<int, array{kw1:?float,kw2:?float,kw3:?float,kw4:?float}>}>
     */
    private function buildPivot(Collection $agences, Carbon $dateMin, Carbon $dateMax, string $mode): array
    {
        $base = PowerReading::whereBetween('created_at', [$dateMin->copy()->startOfDay(), $dateMax->copy()->endOfDay()])
            ->orderBy('created_at')
            ->get(['agence_id', 'created_at', 'kw1', 'kw2', 'kw3', 'kw4']);

        // Mode "all": one row per distinct timestamp (minute precision) across agences
        if ($mode === 'all') {
            $grouped = [];
            foreach ($base as $r) {
                $d    = Carbon::parse($r->created_at);
                $date = $d->toDateString();
                $time = $d->format('H:i');
                $key  = $date.'|'.$time;

                $grouped[$key][$r->agence_id] = [
                    'kw1' => $r->kw1, 'kw2' => $r->kw2, 'kw3' => $r->kw3, 'kw4' => $r->kw4,
                ];
            }
            ksort($grouped);

            $rows = [];
            foreach ($grouped as $key => $byAgence) {
                [$date, $time] = explode('|', $key, 2);
                $cells = [];
                foreach ($agences as $a) {
                    $cells[$a->id] = $byAgence[$a->id] ?? ['kw1'=>null,'kw2'=>null,'kw3'=>null,'kw4'=>null];
                }
                $rows[] = ['date'=>$date, 'time'=>$time, 'cells'=>$cells];
            }
            return $rows;
        }

        // Aggregate per day & agence (for latest/first/sum/avg/max)
        $perDayAgence = [];
        foreach ($base as $r) {
            $d = Carbon::parse($r->created_at)->toDateString();
            $perDayAgence[$d][$r->agence_id][] = $r;
        }

        // Build continuous date range WITHOUT CarbonPeriod
        $rows   = [];
        $cursor = $dateMin->copy()->startOfDay();
        $end    = $dateMax->copy()->startOfDay();

        while ($cursor->lte($end)) {
            $date  = $cursor->toDateString();
            $cells = [];
            foreach ($agences as $a) {
                $list          = $perDayAgence[$date][$a->id] ?? [];
                $cells[$a->id] = $this->aggregateList($list, $mode);
            }
            $rows[] = ['date' => $date, 'time' => null, 'cells' => $cells];
            $cursor->addDay();
        }

        return $rows;
    }

    /**
     * Aggregate readings for one (day, agence) according to mode.
     *
     * @param  array<int, \App\Models\PowerReading> $list
     * @return array{kw1:?float,kw2:?float,kw3:?float,kw4:?float}
     */
    private function aggregateList(array $list, string $mode): array
    {
        if (empty($list)) {
            return ['kw1'=>null, 'kw2'=>null, 'kw3'=>null, 'kw4'=>null];
        }

        // Sort by timestamp
        usort($list, fn($a, $b) => Carbon::parse($a->created_at) <=> Carbon::parse($b->created_at));

        // Collect values per compteur
        $vals = ['kw1'=>[], 'kw2'=>[], 'kw3'=>[], 'kw4'=>[]];
        foreach ($list as $r) {
            $vals['kw1'][] = $r->kw1;
            $vals['kw2'][] = $r->kw2;
            $vals['kw3'][] = $r->kw3;
            $vals['kw4'][] = $r->kw4;
        }

        $first = fn($k) => $vals[$k][0];
        $last  = fn($k) => $vals[$k][count($vals[$k]) - 1];
        $sum   = fn($k) => array_sum(array_map(fn($v) => $v ?? 0, $vals[$k]));
        $avg   = fn($k) => count($vals[$k]) ? $sum($k) / count($vals[$k]) : null;
        $max   = fn($k) => count($vals[$k]) ? max(array_map(fn($v) => $v ?? 0, $vals[$k])) : null;

        return match ($mode) {
            'first' => ['kw1'=>$first('kw1'), 'kw2'=>$first('kw2'), 'kw3'=>$first('kw3'), 'kw4'=>$first('kw4')],
            'sum'   => ['kw1'=>$sum('kw1'),   'kw2'=>$sum('kw2'),   'kw3'=>$sum('kw3'),   'kw4'=>$sum('kw4')],
            'avg'   => ['kw1'=>$avg('kw1') !== null ? round($avg('kw1'), 3) : null,
                'kw2'=>$avg('kw2') !== null ? round($avg('kw2'), 3) : null,
                'kw3'=>$avg('kw3') !== null ? round($avg('kw3'), 3) : null,
                'kw4'=>$avg('kw4') !== null ? round($avg('kw4'), 3) : null],
            'max'   => ['kw1'=>$max('kw1'),   'kw2'=>$max('kw2'),   'kw3'=>$max('kw3'),   'kw4'=>$max('kw4')],
            default => ['kw1'=>$last('kw1'),  'kw2'=>$last('kw2'),  'kw3'=>$last('kw3'),  'kw4'=>$last('kw4')], // latest
        };
    }
}
