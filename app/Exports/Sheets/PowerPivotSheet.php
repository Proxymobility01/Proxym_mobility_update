<?php

namespace App\Exports\Sheets;

use App\Models\PowerReading;
use App\Models\Agence;
use Illuminate\Contracts\View\View;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class PowerPivotSheet implements FromView, WithTitle, ShouldAutoSize
{
    public function __construct(
        private ?string $dateMin,
        private ?string $dateMax,
        private string  $mode,   // 'first' | 'latest'
        private string  $title,  // sheet title
    ) {}

    public function title(): string
    {
        return $this->title;
    }

    public function view(): View
    {
        $dateMin = $this->dateMin ? Carbon::parse($this->dateMin) : now()->startOfMonth();
        $dateMax = $this->dateMax ? Carbon::parse($this->dateMax) : now()->endOfMonth();

        $agences = Agence::orderBy('nom_agence')->get(['id', 'nom_agence']);

        $readings = PowerReading::whereBetween('created_at', [
            $dateMin->copy()->startOfDay(),
            $dateMax->copy()->endOfDay(),
        ])
            ->orderBy('created_at')
            ->get(['agence_id', 'created_at', 'kw1', 'kw2', 'kw3', 'kw4']);

        // Group by day & agence
        $perDayAgence = [];
        foreach ($readings as $r) {
            $d = Carbon::parse($r->created_at)->toDateString();
            $perDayAgence[$d][$r->agence_id][] = $r;
        }

        // Build rows for each date (continuous period) WITHOUT CarbonPeriod
        $rows   = [];
        $cursor = $dateMin->copy()->startOfDay();
        $end    = $dateMax->copy()->startOfDay();

        while ($cursor->lte($end)) {
            $date  = $cursor->toDateString();
            $cells = [];
            foreach ($agences as $a) {
                $cells[$a->id] = $this->pick($perDayAgence[$date][$a->id] ?? []);
            }
            $rows[] = ['date' => $date, 'cells' => $cells];
            $cursor->addDay();
        }

        return view('power_readings.export-pivot-excel', [
            'sheetLabel' => $this->title,
            'agences'    => $agences,
            'rows'       => $rows,
            'dateMin'    => $dateMin->toDateString(),
            'dateMax'    => $dateMax->toDateString(),
        ]);
    }

    private function pick(array $list): array
    {
        if (empty($list)) {
            return ['kw1'=>null, 'kw2'=>null, 'kw3'=>null, 'kw4'=>null];
        }

        usort($list, fn($a, $b) => Carbon::parse($a->created_at) <=> Carbon::parse($b->created_at));
        $chosen = $this->mode === 'first' ? $list[0] : $list[count($list) - 1];

        return [
            'kw1' => $chosen->kw1,
            'kw2' => $chosen->kw2,
            'kw3' => $chosen->kw3,
            'kw4' => $chosen->kw4,
        ];
    }
}
