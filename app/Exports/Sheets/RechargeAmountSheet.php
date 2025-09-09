<?php

namespace App\Exports\Sheets;

use App\Models\PowerReading;
use App\Models\Agence;
use Illuminate\Contracts\View\View;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class RechargeAmountSheet implements FromView, WithTitle, ShouldAutoSize
{
    public function __construct(
        private ?string $dateMin,
        private ?string $dateMax,
        private string  $title = 'Montant Recharge'
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

        // Capture first & last reading per (date, agence)
        $first = [];
        $last  = [];
        foreach ($readings as $r) {
            $d   = Carbon::parse($r->created_at)->toDateString();
            $aid = $r->agence_id;

            if (!isset($first[$d][$aid])) {
                $first[$d][$aid] = $r;
            }
            $last[$d][$aid] = $r; // overwritten until the true last
        }

        // Build rows for each day WITHOUT CarbonPeriod
        $rows   = [];
        $cursor = $dateMin->copy()->startOfDay();
        $end    = $dateMax->copy()->startOfDay();

        while ($cursor->lte($end)) {
            $date  = $cursor->toDateString();
            $cells = [];

            foreach ($agences as $a) {
                $f = $first[$date][$a->id] ?? null;
                $l = $last[$date][$a->id]  ?? null;

                $cells[$a->id] = [
                    'kw1' => ($l ? ($l->kw1 ?? 0) : 0) - ($f ? ($f->kw1 ?? 0) : 0),
                    'kw2' => ($l ? ($l->kw2 ?? 0) : 0) - ($f ? ($f->kw2 ?? 0) : 0),
                    'kw3' => ($l ? ($l->kw3 ?? 0) : 0) - ($f ? ($f->kw3 ?? 0) : 0),
                    'kw4' => ($l ? ($l->kw4 ?? 0) : 0) - ($f ? ($f->kw4 ?? 0) : 0),
                ];
            }

            $rows[] = ['date' => $date, 'cells' => $cells];
            $cursor->addDay();
        }

        return view('power_readings.export-recharge-excel', [
            'sheetLabel' => $this->title,
            'agences'    => $agences,
            'rows'       => $rows,
            'dateMin'    => $dateMin->toDateString(),
            'dateMax'    => $dateMax->toDateString(),
        ]);
    }
}
