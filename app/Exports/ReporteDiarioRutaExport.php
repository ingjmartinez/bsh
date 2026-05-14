<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ReporteDiarioRutaExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithStyles
{
    public function __construct(
        private Collection $rows
    ) {
    }

    public function collection()
    {
        return $this->rows;
    }

    public function headings(): array
    {
        return [
            'Fecha',
            'Serial Ruta',
            'Ruta',
            'Empresa',
            'Operador',
            'Monto Procesado',
            'Monto Entregado',
            'Gasto',
            'Diferencia',
            'Estatus',
            'Observacion',
        ];
    }

    public function map($row): array
    {
        $operador = trim((($row->operador->nombre ?? '') . ' ' . ($row->operador->apellido ?? '')));

        return [
            optional($row->fecha)->format('d/m/Y') ?? '',
            (string) ($row->serial_ruta ?? '-'),
            (string) ($row->ruta->nombre_ruta ?? '-'),
            (string) ($row->ruta->empresa ?? '-'),
            $operador !== '' ? $operador : '-',
            number_format((float) $row->procesado, 2, '.', ''),
            number_format((float) $row->entregado, 2, '.', ''),
            number_format((float) ($row->gasto ?? 0), 2, '.', ''),
            number_format((float) $row->diferencia, 2, '.', ''),
            abs((float) $row->diferencia) > 0.00001 ? 'Pendiente' : 'Completada',
            (string) ($row->observacion ?? ''),
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
