<?php

namespace App\Exports;

use App\Models\Employe;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class EmployesExport implements FromCollection, WithHeadings, WithMapping, WithStyles, ShouldAutoSize
{
    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        return Employe::withTrashed()->get();
    }

    /**
     * @return array
     */
    public function headings(): array
    {
        return [
            'ID',
            'Nom',
            'Prénom',
            'Email',
            'Téléphone',
            'Statut',
            'Date de création',
            'Dernière modification',
        ];
    }

    /**
     * @param Employe $employe
     * @return array
     */
    public function map($employe): array
    {
        return [
            $employe->id,
            $employe->nom,
            $employe->prenom,
            $employe->email,
            $employe->phone,
            $employe->status,
            $employe->created_at->format('d/m/Y H:i'),
            $employe->updated_at->format('d/m/Y H:i'),
        ];
    }

    /**
     * @param Worksheet $sheet
     */
    public function styles(Worksheet $sheet)
    {
        return [
            // Style the first row as bold text
            1 => ['font' => ['bold' => true]],
        ];
    }
}