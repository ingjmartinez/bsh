<?php

namespace App\Services;

use App\Models\Agencia;
use App\Models\AgenciaDelta;
use App\Models\AgenciaLotedom;
use App\Models\AgenciaSinActividadMotivo;
use App\Models\VentaDsVirtual;
use App\Models\VentasDelta;
use App\Models\VtUsuarioBet;
use App\Models\VtUsuarioNet;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class AgenciaSinActividadReportService
{
    /** @var array<string, string> */
    private const SYSTEM_NAMES = [
        'lotobet' => 'Lotobet Real',
        'lotedom' => 'Lotedom',
        'delta' => 'Delta',
        'ds_virtual' => 'DS Virtual',
    ];

    /** @return array<string, list<string>> */
    public function filterOptions(string $system): array
    {
        $model = match ($system) {
            'lotedom' => AgenciaLotedom::class,
            'delta' => AgenciaDelta::class,
            default => Agencia::class,
        };

        $options = [];
        foreach (['centrales' => 'central', 'gerentes' => 'gerente_de_servicio', 'grupos' => 'grupo'] as $key => $column) {
            $options[$key] = $model::query()
                ->where('estatus', 1)
                ->whereNotNull($column)
                ->where($column, '<>', '')
                ->distinct()
                ->orderBy($column)
                ->pluck($column)
                ->map(fn ($value): string => trim((string) $value))
                ->filter()
                ->values()
                ->all();
        }

        return $options;
    }

    /**
     * @param  array{sistema:string,fecha_inicio:string,fecha_fin:string,central?:string|null,gerente?:string|null,grupo?:string|null}  $filters
     * @return array<string, mixed>
     */
    public function report(array $filters): array
    {
        $agencies = $this->agencies($filters);
        $sales = $this->sales($filters['sistema'], $filters['fecha_inicio'], $filters['fecha_fin']);
        $reasons = AgenciaSinActividadMotivo::query()
            ->where('sistema', $filters['sistema'])
            ->get()
            ->keyBy(fn (AgenciaSinActividadMotivo $reason): string => $this->terminalKey($reason->terminal));

        $rows = $agencies->map(function ($agency) use ($filters, $sales, $reasons): array {
            $terminal = trim((string) $agency->terminal);
            $terminalKey = $this->terminalKey($terminal);
            $amount = (float) ($sales->get($terminalKey) ?? 0);
            $reason = $reasons->get($terminalKey);

            return [
                'terminal' => $terminal,
                'agencia' => (string) ($agency->agencia ?? $agency->codigo ?? ''),
                'nombre' => (string) ($agency->nombre_agencia ?? $agency->nombre ?? ''),
                'grupo' => (string) ($agency->grupo ?? ''),
                'central' => (string) ($agency->central ?? ''),
                'gerente' => (string) ($agency->gerente_de_servicio ?? ''),
                'ventas' => round($amount, 2),
                'estado' => $amount > 0 ? 'Con ventas' : 'Sin ventas',
                'motivo' => $reason?->motivo,
                'observacion' => $reason?->observacion,
                'sistema' => $filters['sistema'],
            ];
        })->sortBy([
            ['ventas', 'asc'],
            ['central', 'asc'],
            ['terminal', 'asc'],
        ])->values();

        $withSales = $rows->where('ventas', '>', 0)->count();
        $withoutSales = $rows->count() - $withSales;
        $byCentral = $rows->groupBy(fn (array $row): string => $row['central'] ?: 'Sin central')
            ->map(function (Collection $items, string $central): array {
                $withSales = $items->where('ventas', '>', 0)->count();
                $withoutSales = $items->where('ventas', '<=', 0)->count();
                $total = $withSales + $withoutSales;

                return [
                    'central' => $central,
                    'con_ventas' => $withSales,
                    'sin_ventas' => $withoutSales,
                    'cumplimiento' => $total === 0 ? 0 : round(($withSales / $total) * 100, 2),
                ];
            })->sortBy('cumplimiento')->values();

        return [
            'sistema' => self::SYSTEM_NAMES[$filters['sistema']],
            'resumen' => [
                'total' => $rows->count(),
                'con_ventas' => $withSales,
                'sin_ventas' => $withoutSales,
                'porcentaje_actividad' => $rows->isEmpty() ? 0 : round(($withSales / $rows->count()) * 100, 2),
            ],
            'por_central' => $byCentral,
            'agencias' => $rows,
        ];
    }

    /**
     * @param  array{sistema:string,central?:string|null,gerente?:string|null,grupo?:string|null}  $filters
     * @return Collection<int, mixed>
     */
    private function agencies(array $filters): Collection
    {
        /** @var Builder $query */
        [$query, $columns] = match ($filters['sistema']) {
            'lotedom' => [AgenciaLotedom::query(), [
                'terminal', 'agencia', 'codigo', 'nombre_agencia', 'nombre', 'grupo', 'central', 'gerente_de_servicio',
            ]],
            'delta' => [AgenciaDelta::query(), [
                'terminal', 'agencia', 'codigo', 'nombre_agencia', 'nombre', 'grupo', 'central', 'gerente_de_servicio',
            ]],
            default => [Agencia::query(), [
                'terminal', 'codigo', 'nombre', 'grupo', 'central', 'gerente_de_servicio',
            ]],
        };

        $query->where('estatus', 1)->whereNotNull('terminal')->where('terminal', '<>', '');

        foreach (['central' => 'central', 'gerente' => 'gerente_de_servicio', 'grupo' => 'grupo'] as $filter => $column) {
            if (! empty($filters[$filter])) {
                $query->where($column, $filters[$filter]);
            }
        }

        return $query->get($columns);
    }

    /** @return Collection<string, float> */
    private function sales(string $system, string $start, string $end): Collection
    {
        $rows = match ($system) {
            'lotedom' => VtUsuarioNet::query()
                ->selectRaw('agencia_id AS terminal, SUM(COALESCE(monto, 0)) AS total')
                ->whereBetween('fecha', [$start, $end])->groupBy('agencia_id')->get(),
            'delta' => VentasDelta::query()
                ->selectRaw('numero_externo AS terminal, SUM(COALESCE(venta_loteria, 0) + COALESCE(venta_recarga, 0) + COALESCE(ventas_no_tradicional, 0)) AS total')
                ->whereBetween('fecha', [$start, $end])->groupBy('numero_externo')->get(),
            'ds_virtual' => VentaDsVirtual::query()
                ->selectRaw('agencia_id AS terminal, SUM(COALESCE(ventas, 0)) AS total')
                ->whereBetween('fecha', [$start, $end])->groupBy('agencia_id')->get(),
            default => VtUsuarioBet::query()
                ->selectRaw('agencia_id AS terminal, SUM(COALESCE(monto, 0)) AS total')
                ->whereBetween('fecha', [$start, $end])->groupBy('agencia_id')->get(),
        };

        return $rows->mapWithKeys(fn ($row): array => [
            $this->terminalKey((string) $row->terminal) => (float) $row->total,
        ]);
    }

    private function terminalKey(string $terminal): string
    {
        $normalized = ltrim(trim($terminal), '0');

        return $normalized === '' ? '0' : $normalized;
    }
}
