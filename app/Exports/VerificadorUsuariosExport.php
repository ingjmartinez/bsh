<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class VerificadorUsuariosExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithStyles
{
    protected $registros;

    public function __construct($registros)
    {
        $this->registros = $registros;
    }

    public function collection()
    {
        return collect($this->registros);
    }

    public function headings(): array
    {
        return [
            'Empleado ID',
            'Nombres',
            'Apellidos',
            'Cédula',
            'Horas NET',
            'Horas BET',
            'Horas Total',
            'Cant. Faltantes NET',
            'Cant. Faltantes BET',
            'Cant. Faltantes Total',
            'Monto Faltantes NET',
            'Monto Faltantes BET',
            'Monto Faltantes Total',
            'Comentario',
        ];
    }

    public function map($row): array
    {
        return [
            $row->empleadoid ?? '',
            $row->nombres ?? '',
            $row->apellidos ?? '',
            $row->cedula ?? '',
            number_format($row->horas_net ?? 0, 2, '.', ''),
            number_format($row->horas_bet ?? 0, 2, '.', ''),
            number_format($row->horas_total ?? 0, 2, '.', ''),
            $row->cant_faltantes_net ?? 0,
            $row->cant_faltantes_bet ?? 0,
            $row->cant_faltantes_total ?? 0,
            number_format($row->monto_faltantes_net ?? 0, 2, '.', ''),
            number_format($row->monto_faltantes_bet ?? 0, 2, '.', ''),
            number_format($row->monto_faltantes_total ?? 0, 2, '.', ''),
            $row->comentario ?? '',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
