<?php

namespace App\Services;

use App\Models\Agencia;
use App\Models\CentroDeCosto;
use App\Models\Empleado;
use App\Models\VentaDsVirtual;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class IncentivoBonusReportService
{
    private const BONUS_PERCENTAGE = 0.5;

    /** @var array<int, array{fecha: string, consorcio_id: int, terminal: string, monto: float}> */
    private array $pendingDsSales = [];

    /**
     * @return array<string, mixed>
     */
    public function generate(string $startDate, string $endDate, string $system = 'Todos'): array
    {
        $sales = $this->nonTraditionalSales($startDate, $endDate, $system);
        $externalSales = $this->externalSales($startDate, $endDate, $system);
        $reportKeys = $sales->keys()->merge($externalSales->keys())->unique()->values();
        $cedulas = $sales->pluck('cedula')->merge($externalSales->keys())->filter()->unique()->values();
        $employees = $this->employeesByCedula($cedulas);
        $agencies = $this->agenciesByTerminal(
            $sales->pluck('terminal')->merge($externalSales->pluck('terminal'))->filter()->unique()->values()
        );
        $costCenters = $this->costCentersByEmployee($employees);
        $shortages = $this->shortagesByCedula($cedulas, $startDate, $endDate);

        $rows = $reportKeys->map(function (string $reportKey) use ($sales, $externalSales, $employees, $agencies, $costCenters, $shortages): array {
            $nonTraditional = $sales->get($reportKey, []);
            $cedula = (string) ($nonTraditional['cedula'] ?? $reportKey);
            $external = $cedula !== '' ? $externalSales->get($cedula, []) : [];
            $employee = $employees->get($cedula);
            $terminal = (string) ($nonTraditional['terminal'] ?? $external['terminal'] ?? '');
            $salesSystem = (string) ($nonTraditional['sistema'] ?? $external['sistema'] ?? '');
            $agency = $agencies->get($this->agencyKey($salesSystem, $terminal))
                ?? $agencies->get($this->agencyKey('', $terminal));
            $costCenter = $employee ? $costCenters->get((string) $employee->idcentrocosto) : null;
            $nonTraditionalAmount = round((float) ($nonTraditional['monto'] ?? 0), 2);
            $externalAmount = round((float) ($external['monto'] ?? 0), 2);
            $total = round($nonTraditionalAmount + $externalAmount, 2);
            $shortage = round((float) $shortages->get($cedula, 0), 2);

            return [
                'cedula' => $cedula,
                'empleadoid' => $employee?->empleadoid,
                'nombre' => $employee ? trim($employee->nombres.' '.$employee->apellidos) : 'Pendiente de vincular',
                'empleada' => $employee ? trim($employee->empleadoid.'-'.$employee->nombres.' '.$employee->apellidos) : null,
                'idcentrocosto' => $employee?->idcentrocosto,
                'centro_costo' => $this->costCenterLabel($costCenter, $employee?->idcentrocosto),
                'division' => $costCenter?->id_division,
                'grupo' => $costCenter?->id_grupo,
                'ruta' => $agency?->ruta,
                'terminal' => $terminal,
                'agencia' => $this->agencyName($agency),
                'empresa' => $agency?->empresa,
                'no_tradicional' => $nonTraditionalAmount,
                'venta_externa' => $externalAmount,
                'total' => $total,
                'porcentaje_bono' => self::BONUS_PERCENTAGE,
                'bono' => $shortage > 0 ? 0.0 : round($total * (self::BONUS_PERCENTAGE / 100), 3),
                'faltante' => $shortage,
                'estado' => match (true) {
                    ! $employee => 'pendiente_empleado',
                    ! $agency => 'pendiente_agencia',
                    $shortage > 0 => 'con_faltante',
                    default => 'pagable',
                },
            ];
        })->sortByDesc('bono')->values();

        return [
            'data' => $rows,
            'meta' => [
                'fecha_inicio' => $startDate,
                'fecha_fin' => $endDate,
                'sistema' => $system,
                'porcentaje_bono' => self::BONUS_PERCENTAGE,
                'total_registros' => $rows->count(),
                'total_no_tradicional' => round((float) $rows->sum('no_tradicional'), 2),
                'total_venta_externa' => round((float) $rows->sum('venta_externa'), 2),
                'total_ventas' => round((float) $rows->sum('total'), 2),
                'total_bono' => round((float) $rows->sum('bono'), 3),
                'total_faltantes' => round((float) $rows->sum('faltante'), 2),
                'empleados_pendientes' => $rows->where('estado', 'pendiente_empleado')->count(),
                'agencias_pendientes' => $rows->where('estado', 'pendiente_agencia')->count(),
                'venta_externa_disponible' => Schema::hasTable('ventas_ds_virtual'),
                'ventas_ds_pendientes' => $this->pendingDsSales,
                'total_ds_pendiente' => round(array_sum(array_column($this->pendingDsSales, 'monto')), 2),
                'total_ds_recibido' => round((float) $rows->sum('venta_externa') + array_sum(array_column($this->pendingDsSales, 'monto')), 2),
            ],
        ];
    }

    /**
     * Distribuye DS entre las cédulas que vendieron en el mismo consorcio, terminal y día.
     *
     * @return Collection<string, array{cedula: string, monto: float, terminal: string, sistema: string}>
     */
    protected function externalSales(string $startDate, string $endDate, string $system): Collection
    {
        $this->pendingDsSales = [];
        if ($system === 'Lotonet' || ! Schema::hasTable('ventas_ds_virtual')) {
            return collect();
        }

        $source = collect(['ventas_usuarios_bet', 'vt_usuarios_bet'])
            ->first(fn (string $table): bool => Schema::hasTable($table));
        $identities = collect();
        if ($source && Schema::hasColumn($source, 'consorcio_id')) {
            $identities = DB::table($source)
                ->whereBetween('fecha', [$startDate, $endDate])
                ->where('monto', '>', 0)
                ->whereNotNull('cedula')
                ->select(['fecha', 'consorcio_id', 'agencia_id', 'cedula'])
                ->distinct()
                ->get()
                ->groupBy(fn (object $row): string => $this->dsDailyKey($row))
                ->map(fn (Collection $rows): Collection => $rows
                    ->map(fn (object $row): string => $this->normalizeCedula($row->cedula))
                    ->filter(fn (string $cedula): bool => strlen($cedula) === 11)
                    ->unique()->sort()->values());
        }

        $grouped = [];
        $dailySales = VentaDsVirtual::query()
            ->whereBetween('fecha', [$startDate, $endDate])
            ->select(['fecha', 'consorcio_id', 'agencia_id'])
            ->selectRaw('SUM(ventas) AS monto')
            ->groupBy('fecha', 'consorcio_id', 'agencia_id')
            ->orderBy('fecha')
            ->toBase()->get();

        foreach ($dailySales as $sale) {
            $cedulas = $identities->get($this->dsDailyKey($sale), collect());
            $cents = (int) round((float) $sale->monto * 100);
            if ($cedulas->isEmpty()) {
                if ($cents !== 0) {
                    $this->pendingDsSales[] = [
                        'fecha' => (string) $sale->fecha,
                        'consorcio_id' => (int) $sale->consorcio_id,
                        'terminal' => trim((string) $sale->agencia_id),
                        'monto' => $cents / 100,
                    ];
                }

                continue;
            }

            $share = intdiv($cents, $cedulas->count());
            $remainder = $cents % $cedulas->count();
            foreach ($cedulas as $index => $cedula) {
                $allocated = $share + ($index < abs($remainder) ? ($remainder <=> 0) : 0);
                $grouped[$cedula] = [
                    'cedula' => $cedula,
                    'monto' => ($grouped[$cedula]['monto'] ?? 0) + $allocated / 100,
                    'terminal' => trim((string) $sale->agencia_id),
                    'sistema' => 'Lotobet',
                ];
            }
        }

        return collect($grouped);
    }

    private function dsDailyKey(object $row): string
    {
        return (string) $row->fecha.'|'.(string) $row->consorcio_id.'|'.trim((string) $row->agencia_id);
    }

    /**
     * @return Collection<string, array{monto: float, terminal: string, sistema: string}>
     */
    private function nonTraditionalSales(string $startDate, string $endDate, string $system): Collection
    {
        if (! Schema::hasTable('catalogo_juegos')) {
            return collect();
        }

        $sources = collect([
            ['tables' => ['ventas_usuarios_bet', 'vt_usuarios_bet'], 'system' => 'Lotobet'],
            ['tables' => ['ventas_usuarios_net', 'vt_usuarios_net'], 'system' => 'Lotonet'],
        ])->map(function (array $source): array {
            $source['table'] = collect($source['tables'])
                ->first(fn (string $table): bool => Schema::hasTable($table));

            return $source;
        })->filter(fn (array $source): bool => is_string($source['table']))
            ->filter(fn (array $source): bool => $system === 'Todos' || $system === $source['system']);

        $grouped = [];

        foreach ($sources as $source) {
            $this->nonTraditionalSalesQuery($source['table'], $source['system'], $startDate, $endDate)
                ->get()
                ->each(function ($row) use (&$grouped): void {
                    $cedula = $this->normalizeCedula($row->cedula);
                    $terminal = trim((string) $row->terminal);
                    $reportKey = $cedula !== ''
                        ? $cedula
                        : '__sin_cedula__|'.mb_strtolower((string) $row->sistema).'|'.$terminal;

                    $grouped[$reportKey]['cedula'] = $cedula;
                    $grouped[$reportKey]['monto'] = ($grouped[$reportKey]['monto'] ?? 0) + (float) $row->monto;
                    if (! isset($grouped[$reportKey]['ultima_fecha']) || $row->ultima_fecha >= $grouped[$reportKey]['ultima_fecha']) {
                        $grouped[$reportKey]['terminal'] = $terminal;
                        $grouped[$reportKey]['sistema'] = (string) $row->sistema;
                        $grouped[$reportKey]['ultima_fecha'] = (string) $row->ultima_fecha;
                    }
                });
        }

        return collect($grouped);
    }

    private function nonTraditionalSalesQuery(
        string $table,
        string $system,
        string $startDate,
        string $endDate
    ): Builder {
        return DB::table($table)
            ->selectRaw('? AS sistema, TRIM(CAST(agencia_id AS CHAR)) AS terminal, cedula, SUM(monto) AS monto, MAX(fecha) AS ultima_fecha', [$system])
            ->whereBetween('fecha', [$startDate, $endDate])
            ->when(
                Schema::hasColumn($table, 'tipo'),
                fn (Builder $query): Builder => $query->whereRaw("LOWER(TRIM(tipo)) IN ('no tradicional', 'no_tradicional')"),
                fn (Builder $query): Builder => $query->whereIn(DB::raw('CAST(producto_id AS SIGNED)'), function (Builder $productQuery): void {
                    $productQuery->select('producto_id')
                        ->from('catalogo_juegos')
                        ->whereRaw("LOWER(TRIM(tipo)) IN ('no tradicional', 'no_tradicional')");
                })
            )
            ->groupByRaw('TRIM(CAST(agencia_id AS CHAR)), cedula');
    }

    /**
     * @param  Collection<int, string>  $cedulas
     * @return Collection<string, Empleado>
     */
    private function employeesByCedula(Collection $cedulas): Collection
    {
        if (! Schema::hasTable('empleados') || $cedulas->isEmpty()) {
            return collect();
        }

        $employees = collect();
        foreach ($cedulas->chunk(800) as $chunk) {
            $query = Empleado::query()
                ->whereIn(DB::raw("REPLACE(REPLACE(TRIM(cedula), '-', ''), ' ', '')"), $chunk->all());

            if (Schema::hasColumn('empleados', 'estatus')) {
                $query->orderByDesc('estatus');
            }

            $employees = $employees->merge($query->get());
        }

        return $employees
            ->groupBy(fn (Empleado $employee): string => $this->normalizeCedula($employee->cedula))
            ->map(fn (Collection $matches): Empleado => $matches->first());
    }

    /**
     * @param  Collection<int, string>  $terminals
     * @return Collection<string, Agencia>
     */
    private function agenciesByTerminal(Collection $terminals): Collection
    {
        if (! Schema::hasTable('agencias') || $terminals->isEmpty()) {
            return collect();
        }

        $agencies = collect();
        foreach ($terminals->chunk(800) as $chunk) {
            $agencies = $agencies->merge(
                Agencia::query()
                    ->whereIn(DB::raw('TRIM(CAST(terminal AS CHAR))'), $chunk->all())
                    ->get()
            );
        }

        $bySystem = $agencies->mapWithKeys(fn (Agencia $agency): array => [
            $this->agencyKey((string) $agency->sistema, (string) $agency->terminal) => $agency,
        ]);
        $fallback = $agencies->mapWithKeys(fn (Agencia $agency): array => [
            $this->agencyKey('', (string) $agency->terminal) => $agency,
        ]);

        return $bySystem->merge($fallback);
    }

    /**
     * @param  Collection<string, Empleado>  $employees
     * @return Collection<string, CentroDeCosto>
     */
    private function costCentersByEmployee(Collection $employees): Collection
    {
        if (! Schema::hasTable('centros_de_costo')) {
            return collect();
        }

        $ids = $employees->pluck('idcentrocosto')->filter()->unique()->values();
        if ($ids->isEmpty()) {
            return collect();
        }

        return CentroDeCosto::query()
            ->whereIn('id_centro_costo', $ids)
            ->get()
            ->keyBy(fn (CentroDeCosto $costCenter): string => (string) $costCenter->id_centro_costo);
    }

    /**
     * @param  Collection<int, string>  $cedulas
     * @return Collection<string, float>
     */
    private function shortagesByCedula(Collection $cedulas, string $startDate, string $endDate): Collection
    {
        $shortages = [];

        foreach (['faltantes_bet', 'faltantes_net'] as $table) {
            if (! Schema::hasTable($table)) {
                continue;
            }

            $cedulaColumn = collect(['identificacion', 'cedula', 'observacion'])
                ->first(fn (string $column): bool => Schema::hasColumn($table, $column));

            if (! $cedulaColumn) {
                continue;
            }

            DB::table($table)
                ->whereBetween('fecha', [$startDate, $endDate])
                ->whereNotNull($cedulaColumn)
                ->selectRaw("{$cedulaColumn} AS identificacion, SUM(COALESCE(monto, 0)) AS monto")
                ->groupBy($cedulaColumn)
                ->get()
                ->each(function ($row) use (&$shortages, $cedulas): void {
                    $cedula = $this->normalizeCedula($row->identificacion);
                    $isCedula = preg_match('/^[\d\s-]+$/u', trim((string) $row->identificacion)) === 1
                        && strlen($cedula) === 11;

                    if ($isCedula && $cedulas->contains($cedula)) {
                        $shortages[$cedula] = ($shortages[$cedula] ?? 0) + (float) $row->monto;
                    }
                });
        }

        return collect($shortages);
    }

    private function normalizeCedula(mixed $cedula): string
    {
        return preg_replace('/\D+/', '', (string) $cedula) ?? '';
    }

    private function agencyKey(string $system, string $terminal): string
    {
        return mb_strtolower(trim($system)).'|'.trim($terminal);
    }

    private function costCenterLabel(?CentroDeCosto $costCenter, mixed $id): ?string
    {
        if (! $costCenter) {
            return $id ? (string) $id : null;
        }

        return trim($costCenter->id_centro_costo.'-'.$costCenter->descripcion);
    }

    private function agencyName(?Agencia $agency): ?string
    {
        if (! $agency) {
            return null;
        }

        return trim((string) ($agency->nombre_agencia ?? $agency->nombre ?? $agency->agencia ?? $agency->codigo));
    }
}
