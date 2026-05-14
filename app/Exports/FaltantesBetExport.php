<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\FromCollection;

class FaltantesBetExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize
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
            'Cédula',
            'Nombre Empleado',
            'Cantidad de Faltantes',
            'Monto Total',
        ];
    }

    public function map($row): array
    {
        return [
            $row->identificacion,
            trim($row->nombre_empleado) ?? 'Sin especificar',
            $row->cantidad_faltantes,
            number_format($row->total_monto, 2, '.', ''),
        ];
    }
}
