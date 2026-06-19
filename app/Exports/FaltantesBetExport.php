<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

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
            'Id Centro Costo',
            'Cedula',
            'Nombre Empleado',
            'Grupo',
            'Sub Grupo',
            'Cantidad de Faltantes',
            'Monto Total',
        ];
    }

    public function map($row): array
    {
        return [
            $row->id_centro_costo,
            $row->identificacion,
            trim($row->nombre_empleado ?? '') ?: 'Sin especificar',
            trim($row->id_grupo ?? ''),
            trim($row->id_sub_grupo ?? ''),
            $row->cantidad_faltantes,
            number_format($row->total_monto, 2, '.', ''),
        ];
    }
}
