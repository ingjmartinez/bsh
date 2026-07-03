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
            'Empresa',
            'Id Empleado',
            'Id CC Empleado',
            'Id Centro Costo',
            'Cedula',
            'Nombre Empleado',
            'Estado',
            'Grupo',
            'Sub Grupo',
            'Id Division',
            'Cantidad de Faltantes',
            'Monto Total',
        ];
    }

    public function map($row): array
    {
        return [
            $row->companyid,
            $row->empleadoid,
            $row->idcentrocosto,
            $row->id_centro_costo,
            $row->identificacion,
            trim($row->nombre_empleado ?? '') ?: 'Sin especificar',
            $row->estado_empleado ?? '',
            trim($row->id_grupo ?? ''),
            trim($row->id_sub_grupo ?? ''),
            trim($row->id_division ?? ''),
            $row->cantidad_faltantes,
            number_format($row->total_monto, 2, '.', ''),
        ];
    }
}
