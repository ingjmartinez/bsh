<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

class NovedadesHorarioExport implements FromCollection, ShouldAutoSize, WithColumnFormatting, WithHeadings, WithMapping
{
    public function __construct(
        private readonly Collection $rows,
        private readonly float $horasRequeridas,
        private readonly float $valorHora,
    ) {}

    public function collection(): Collection
    {
        return $this->rows;
    }

    /** @return array<int, string> */
    public function headings(): array
    {
        return ['Terminal', 'Nombre de Agencia', 'Ruta', 'Empleado', 'Cédula', 'Fecha', 'Primer Login', 'Último Login', 'Horas Acumuladas', 'Detalle', 'Horas Faltantes', 'Monto Falta'];
    }

    /** @return array<int, mixed> */
    public function map($row): array
    {
        $horasAcumuladas = round((float) ($row->horas_acumuladas ?? 0), 2);
        $horasFaltantes = round(max($this->horasRequeridas - $horasAcumuladas, 0), 2);

        return [
            (string) ($row->terminal ?? ''),
            (string) ($row->nombre_agencia ?? ''),
            (string) ($row->ruta ?? ''),
            (string) ($row->nombre_empleado ?? ''),
            (string) ($row->cedula ?? ''),
            (string) ($row->fecha ?? ''),
            (string) ($row->primer_login ?? ''),
            (string) ($row->ultimo_login ?? ''),
            $horasAcumuladas,
            $horasFaltantes > 0 ? 'Tiene falta' : 'Cumple',
            $horasFaltantes,
            round($horasFaltantes * $this->valorHora, 2),
        ];
    }

    /** @return array<string, string> */
    public function columnFormats(): array
    {
        return ['A' => NumberFormat::FORMAT_TEXT, 'E' => NumberFormat::FORMAT_TEXT];
    }
}
