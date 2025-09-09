<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use App\Exports\Sheets\PowerPivotSheet;
use App\Exports\Sheets\RechargeAmountSheet;

class PowerReadingsPivotExport implements WithMultipleSheets
{
    public function __construct(
        private ?string $dateMin = null,
        private ?string $dateMax = null,
    ) {}

    public function sheets(): array
    {
        return [
            // Matin = première lecture de la journée
            new PowerPivotSheet($this->dateMin, $this->dateMax, mode: 'first',  title: 'kwH Matin'),
            // Fin = dernière lecture de la journée
            new PowerPivotSheet($this->dateMin, $this->dateMax, mode: 'latest', title: 'kwH Fin'),
            // Delta = Fin - Matin (kWh "rechargés" sur la journée)
            new RechargeAmountSheet($this->dateMin, $this->dateMax, 'Montant Recharge'),
        ];
    }
}
