<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class AgenciaPlanExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithStyles
{
    public function __construct(
        private Collection $rows,
        private string $sistema,
        private string $rangoInicio,
        private string $rangoFin
    ) {
    }

    public function collection()
    {
        return $this->rows;
    }

    public function headings(): array
    {
        return [
            'Agencia',
            'Nombre Agencia',
            'Sistema',
            'Días Efectivos',
            'Días Faltantes',
            'Aplica',
            'Monto 90 días',
            'Rango Inicio',
            'Rango Fin',
        ];
    }

    public function map($row): array
    {
        return [
            (string) ($row->agencia_id ?? ''),
            (string) ($row->nombre_agencia ?? ''),
            $this->sistema,
            (int) ($row->dias_con_venta ?? 0),
            (int) ($row->dias_faltantes ?? 0),
            ((bool) ($row->aplica ?? false)) ? 'Sí' : 'No',
            (float) ($row->monto_90_dias ?? 0),
            $this->rangoInicio,
            $this->rangoFin,
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
