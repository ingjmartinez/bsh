<?php

namespace App\Exports;

use App\Models\AgenciaDelta;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class AgenciasDeltaExport implements FromCollection, ShouldAutoSize, WithHeadings, WithMapping, WithStyles
{
    public function collection()
    {
        return AgenciaDelta::query()->get();
    }

    public function headings(): array
    {
        return [
            'ID',
            'Agencia',
            'Terminal',
            'Horario AM',
            'Horario PM',
            'Nombre Agencia',
            'Sistema',
            'Empresa',
            'Ciudad',
            'Ruta',
            'Operador',
            'Coordinador',
            'Grupo',
            'Central',
            'Gerente de Servicio',
            'Tipo de Pago',
            'Estatus',
            'Aplica Incentivo',
            'Fecha Creación',
            'Fecha Actualización',
        ];
    }

    public function map($agencia): array
    {
        return [
            $agencia->id,
            $agencia->agencia,
            $agencia->terminal,
            $agencia->horario_am,
            $agencia->horario_pm,
            $agencia->nombre_agencia,
            $agencia->sistema,
            $agencia->empresa,
            $agencia->ciudad,
            $agencia->ruta,
            $agencia->operador,
            $agencia->coordinador,
            $agencia->grupo,
            $agencia->central,
            $agencia->gerente_de_servicio,
            $agencia->tipo_pago,
            (int) ($agencia->estatus ?? 1) === 1 ? 'ACTIVO' : 'INACTIVO',
            $agencia->aplica_incentivo ? 'SI' : 'NO',
            $agencia->created_at ? $agencia->created_at->format('Y-m-d H:i:s') : '',
            $agencia->updated_at ? $agencia->updated_at->format('Y-m-d H:i:s') : '',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
