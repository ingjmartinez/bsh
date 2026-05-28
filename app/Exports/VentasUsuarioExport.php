<?php

namespace App\Exports;

use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithMapping;

class VentasUsuarioExport implements FromQuery, WithHeadings, WithMapping, ShouldAutoSize
{
    public function __construct(
        protected $tipo = null,
        protected $fecha = null,
        protected $mes = null
    ) {}

    public function query()
    {
        ini_set('memory_limit', '2G'); // Aumentar el límite de memoria a 512MB

        $query = DB::table('ventas_usuarios_bet')
            ->selectRaw('NULL AS consorcio_id, agencia_id, cedula, NULL AS tipo')
            ->whereNotIn('cedula', function ($sub) {
                $sub->select('cedula')->from('empleados')->whereNotNull('cedula');
            });

        if ($this->fecha) {
            $query->whereDate('fecha', $this->fecha);
        }

        // 🔹 Filtro por mes completo (ejemplo: 2025-11)
        if ($this->mes) {
            [$year, $month] = explode('-', $this->mes);
            $query->whereYear('fecha', $year)->whereMonth('fecha', $month);
        }

        return $query->groupBy('agencia_id', 'cedula')
            ->orderBy('cedula', 'desc');
    }

    public function headings(): array
    {
        return [
            'Consorcio',
            'Agencia',
            'Cédula',
            'Tipo',
        ];
    }

    public function map($row): array
    {
        return [
            $row->consorcio_id,
            $row->agencia_id,
            $row->cedula,
            $row->tipo,
        ];
    }
}
