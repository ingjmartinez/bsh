<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class MetaIncentivoExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithStyles
{
    public function __construct(private Collection $rows)
    {
    }

    public function collection()
    {
        return $this->rows;
    }

    public function headings(): array
    {
        return [
            'Agencia ID',
            'Nombre Agencia',
            'Coordinador',
            'Tipo',
            'Nivel',
            'Incremetal',
            'Meta Incremental',
            'BaseT',
            'BaseAjustada',
            'Total Venta Mes Posterior',
            'Cumplimiento Meta',
        ];
    }

    public function map($row): array
    {
        $metaIncremental = (float) ($row->meta_incremental ?? 0);
        $ventaPosterior = (float) ($row->total_venta_mes_posterior ?? 0);

        if ($metaIncremental <= 0) {
            $cumplimientoMeta = 'Cumple 100%';
        } elseif ($ventaPosterior >= $metaIncremental) {
            $cumplimientoMeta = 'Cumple 100%';
        } else {
            $porcentajeCumplido = min(100, ($ventaPosterior / $metaIncremental) * 100);
            $porcentajeFaltante = max(0, 100 - $porcentajeCumplido);
            $cumplimientoMeta = sprintf('Falta %s%% para alcanzar el 100%%', number_format($porcentajeFaltante, 2));
        }

        return [
            (string) ($row->agencia_id ?? ''),
            (string) ($row->nombre_agencia ?? ''),
            (string) ($row->coordinador ?? ''),
            (string) ($row->tipo ?? ''),
            (string) ($row->nivel ?? ''),
            (float) ($row->incremetal ?? 0),
            (float) ($row->meta_incremental ?? 0),
            (float) ($row->ventas_3_meses ?? 0),
            (float) ($row->promedio_3_meses ?? 0),
            (float) ($row->total_venta_mes_posterior ?? 0),
            $cumplimientoMeta,
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
