<?php

namespace App\Services;

use App\Models\IncentivoTerminalTipoPago;
use Carbon\CarbonImmutable;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class IncentivoV6Calculator
{
    /**
     * @param  array<string, mixed>  $payload
     * @param  array<string, mixed>  $filters
     * @param  array<string, array<int, array<string, mixed>>>  $rangesByType
     * @return array<string, mixed>
     */
    public function applyDailyPaymentTypes(array $payload, array $filters, array $rangesByType): array
    {
        if (! isset($payload['data']) || ! is_array($payload['data'])) {
            return $payload;
        }

        $fechaInicio = (string) $filters['fecha_ini'];
        $fechaFin = (string) $filters['fecha_fin'];
        $sistema = (string) ($filters['sistema'] ?? 'Todos');
        $productScope = (string) ($filters['alcance_productos'] ?? 'completo');
        $defaultPaymentType = $this->validPaymentType((string) ($filters['tipo_pago'] ?? 'tramos_60'));
        $minimumDays = max(1, (int) ($filters['min_dias_venta'] ?? 1));
        $excludedTerminals = collect($filters['terminales_excluidas'] ?? [])
            ->map(fn ($terminal): string => trim((string) $terminal))
            ->filter()
            ->unique()
            ->values();
        $dailySales = $this->dailySales($fechaInicio, $fechaFin, $sistema, $excludedTerminals, $productScope);
        $agencies = $this->agenciesByTerminal($dailySales->pluck('terminal')->unique()->values());
        $assignments = IncentivoTerminalTipoPago::query()
            ->whereDate('fecha', '<=', $fechaFin)
            ->when($sistema !== 'Todos', fn ($query) => $query->where('sistema', $sistema))
            ->orderBy('fecha')
            ->orderBy('id')
            ->get()
            ->toBase()
            ->groupBy(fn (IncentivoTerminalTipoPago $item): string => $this->terminalKey(
                $item->sistema,
                $item->terminal
            ));
        $calculations = [];
        $typeDistribution = collect(IncentivoTerminalTipoPago::TIPOS_PAGO)
            ->mapWithKeys(fn (string $type): array => [$type => ['ventas' => 0, 'incentivo' => 0, 'dias_terminal' => 0]])
            ->all();
        $terminalsByPaymentType = collect(IncentivoTerminalTipoPago::TIPOS_PAGO)
            ->mapWithKeys(fn (string $type): array => [$type => []])
            ->all();
        $terminalsByPaymentTypeAndCompany = collect(IncentivoTerminalTipoPago::TIPOS_PAGO)
            ->mapWithKeys(fn (string $type): array => [$type => []])
            ->all();
        $configuredTerminalDays = 0;

        foreach ($dailySales as $sale) {
            $terminal = trim((string) $sale->terminal);
            $system = (string) $sale->sistema;
            $date = (string) $sale->fecha_venta;
            $agency = $agencies->get($this->terminalKey($system, $terminal))
                ?? $agencies->get($this->terminalKey('', $terminal));
            $company = $this->normalizeCompany((string) ($agency->empresa ?? 'Agencias por asignar empresa'));
            $rowKey = $this->rowKey((string) $sale->cedula, $company);
            $assignment = $this->effectiveCalendarAssignment($assignments, $system, $terminal, $date);
            $hasConfiguredPaymentType = $assignment?->tipo_pago !== null;
            $paymentType = $hasConfiguredPaymentType
                ? $this->validPaymentType($assignment->tipo_pago)
                : $defaultPaymentType;
            $amount = (int) round((float) $sale->ventas);

            $calculations[$rowKey]['cedula'] = (string) $sale->cedula;
            $calculations[$rowKey]['empresa'] = $company;
            $calculations[$rowKey]['dias'][$date] = true;
            $calculations[$rowKey]['tipos'][$paymentType]['ventas'] =
                ($calculations[$rowKey]['tipos'][$paymentType]['ventas'] ?? 0) + $amount;
            $calculations[$rowKey]['tipos'][$paymentType]['dias'][$date] = true;
            $calculations[$rowKey]['tipos'][$paymentType]['terminales'][$system.'|'.$terminal] = true;
            $calculations[$rowKey]['tipos'][$paymentType]['dias_terminal'][$system.'|'.$terminal.'|'.$date] = true;
            $calculations[$rowKey]['configuraciones_aplicadas'] =
                ($calculations[$rowKey]['configuraciones_aplicadas'] ?? 0) + ($hasConfiguredPaymentType ? 1 : 0);
            $typeDistribution[$paymentType]['ventas'] += $amount;
            $typeDistribution[$paymentType]['dias_terminal']++;
            $terminalKey = $system.'|'.$terminal;
            $terminalsByPaymentType[$paymentType][$terminalKey] = true;
            $terminalsByPaymentTypeAndCompany[$paymentType][$company][$terminalKey] = true;
            $configuredTerminalDays += $hasConfiguredPaymentType ? 1 : 0;
        }

        foreach (IncentivoTerminalTipoPago::TIPOS_PAGO as $paymentType) {
            $typeDistribution[$paymentType]['agencias'] = count($terminalsByPaymentType[$paymentType]);
            $typeDistribution[$paymentType]['agencias_por_empresa'] = collect($terminalsByPaymentTypeAndCompany[$paymentType])
                ->map(fn (array $terminals): int => count($terminals))
                ->all();
        }

        foreach ($calculations as &$calculation) {
            $qualifies = count($calculation['dias'] ?? []) >= $minimumDays;
            $totalSales = (int) collect($calculation['tipos'] ?? [])->sum(
                fn (array $segment): int => (int) ($segment['ventas'] ?? 0)
            );
            $calculation['incentivo'] = 0;
            $calculation['detalle'] = [];

            foreach ($calculation['tipos'] ?? [] as $paymentType => $segment) {
                $segmentSales = (int) $segment['ventas'];
                $fullScaleIncentive = $qualifies
                    ? $this->calculateIncentive($totalSales, $rangesByType[$paymentType] ?? [])
                    : 0;
                $incentive = $totalSales > 0
                    ? (int) round($fullScaleIncentive * ($segmentSales / $totalSales))
                    : 0;
                $calculation['incentivo'] += $incentive;
                $calculation['detalle'][] = [
                    'tipo_pago' => $paymentType,
                    'ventas' => $segmentSales,
                    'ventas_base_escala' => $totalSales,
                    'incentivo' => $incentive,
                    'dias' => count($segment['dias'] ?? []),
                    'terminales' => count($segment['terminales'] ?? []),
                ];
                $typeDistribution[$paymentType]['incentivo'] += $incentive;
            }

            $calculation['cumple'] = $qualifies && $calculation['incentivo'] > 0;
        }
        unset($calculation);

        $payload['data'] = collect($payload['data'])
            ->map(function (array $row) use ($calculations): array {
                $calculation = $calculations[$this->rowKey(
                    (string) ($row['cedula'] ?? ''),
                    (string) ($row['empresa'] ?? '')
                )] ?? null;

                if (! $calculation) {
                    return $row + [
                        'tipos_pago_detalle' => [],
                        'configuraciones_diarias_aplicadas' => 0,
                    ];
                }

                $incentive = (int) $calculation['incentivo'];
                $row['pago_escala'] = number_format($incentive, 0, '.', ',');
                $row['nuevo_incentivo'] = number_format($incentive, 0, '.', ',');
                $row['cumple_minimo'] = $calculation['cumple'] ? 'SI' : 'NO';
                $row['tipos_pago_detalle'] = $calculation['detalle'];
                $row['configuraciones_diarias_aplicadas'] = (int) ($calculation['configuraciones_aplicadas'] ?? 0);

                return $row;
            })
            ->values()
            ->all();

        $totalIncentive = (int) collect($payload['data'])->sum(
            fn (array $row): int => $this->integerValue($row['nuevo_incentivo'] ?? 0)
        );
        $payload['meta']['total_incentivo'] = $totalIncentive;
        $payload['meta']['total_incentivo_format'] = number_format($totalIncentive, 0, '.', ',');
        $payload['meta']['tipo_pago'] = $defaultPaymentType;
        $payload['meta']['alcance_productos'] = $productScope;
        $payload['meta']['alcance_productos_label'] = $productScope === 'no_tradicionales'
            ? 'Solo productos no tradicionales'
            : 'Reporte completo';
        $payload['meta']['modo_tipos_pago'] = 'calendario_diario_terminal';
        $payload['meta']['configuraciones_diarias_aplicadas'] = $configuredTerminalDays;
        $payload['meta']['distribucion_tipos_pago'] = $typeDistribution;
        $payload['meta']['detalle_calendario_tipos_pago'] = $this->calendarPaymentTypeBreakdown(
            $dailySales,
            $assignments,
            $fechaInicio,
            $fechaFin,
            $defaultPaymentType
        );
        $payload['meta']['resumen_empresas'] = collect($payload['data'])
            ->groupBy(fn (array $row): string => (string) ($row['empresa'] ?? 'Agencias por asignar empresa'))
            ->map(function (Collection $rows, string $company): array {
                return [
                    'empresa' => $company,
                    'total_vendido' => $rows->sum(fn (array $row): int => $this->integerValue($row['ventas_mes_actual'] ?? 0)),
                    'total_incentivo' => $rows->sum(fn (array $row): int => $this->integerValue($row['nuevo_incentivo'] ?? 0)),
                    'usuarios' => $rows->pluck('cedula')->unique()->count(),
                ];
            })
            ->values()
            ->all();
        $payload = $this->updateCoordinatorAmounts($payload);

        return $payload;
    }

    /**
     * @return array<string, array{agencias: int, rangos: array<int, array{desde: string, hasta: string, agencias: int}>}>
     */
    private function calendarPaymentTypeBreakdown(
        Collection $dailySales,
        Collection $assignments,
        string $startDate,
        string $endDate,
        string $defaultPaymentType
    ): array {
        $salesDatesByTerminal = [];

        foreach ($dailySales as $sale) {
            $terminalKey = $this->terminalKey((string) $sale->sistema, (string) $sale->terminal);
            $salesDatesByTerminal[$terminalKey]['sistema'] = (string) $sale->sistema;
            $salesDatesByTerminal[$terminalKey]['terminal'] = trim((string) $sale->terminal);
            $salesDatesByTerminal[$terminalKey]['fechas'][(string) $sale->fecha_venta] = true;
        }

        $terminalsByType = collect(IncentivoTerminalTipoPago::TIPOS_PAGO)
            ->mapWithKeys(fn (string $type): array => [$type => []])
            ->all();
        $rangeTerminalsByType = collect(IncentivoTerminalTipoPago::TIPOS_PAGO)
            ->mapWithKeys(fn (string $type): array => [$type => []])
            ->all();

        foreach ($salesDatesByTerminal as $terminalKey => $terminalData) {
            $currentType = null;
            $rangeStart = null;
            $rangeEnd = null;
            $hasSalesInRange = false;

            $finishRange = function () use (
                &$currentType,
                &$rangeStart,
                &$rangeEnd,
                &$hasSalesInRange,
                &$terminalsByType,
                &$rangeTerminalsByType,
                $terminalKey
            ): void {
                if ($currentType === null || ! $hasSalesInRange || $rangeStart === null || $rangeEnd === null) {
                    return;
                }

                $rangeKey = $rangeStart.'|'.$rangeEnd;
                $terminalsByType[$currentType][$terminalKey] = true;
                $rangeTerminalsByType[$currentType][$rangeKey][$terminalKey] = true;
            };

            for ($dateString = $startDate; $dateString <= $endDate; $dateString = CarbonImmutable::parse($dateString)->addDay()->toDateString()) {
                $assignment = $this->effectiveCalendarAssignment(
                    $assignments,
                    (string) $terminalData['sistema'],
                    (string) $terminalData['terminal'],
                    $dateString
                );
                $paymentType = $assignment?->tipo_pago !== null
                    ? $this->validPaymentType((string) $assignment->tipo_pago)
                    : $defaultPaymentType;

                if ($currentType !== $paymentType) {
                    $finishRange();
                    $currentType = $paymentType;
                    $rangeStart = $dateString;
                    $hasSalesInRange = false;
                }

                $rangeEnd = $dateString;
                $hasSalesInRange = $hasSalesInRange || isset($terminalData['fechas'][$dateString]);
            }

            $finishRange();
        }

        return collect(IncentivoTerminalTipoPago::TIPOS_PAGO)
            ->mapWithKeys(function (string $paymentType) use ($terminalsByType, $rangeTerminalsByType): array {
                $ranges = collect($rangeTerminalsByType[$paymentType])
                    ->map(function (array $terminals, string $rangeKey): array {
                        [$from, $to] = explode('|', $rangeKey, 2);

                        return [
                            'desde' => $from,
                            'hasta' => $to,
                            'agencias' => count($terminals),
                        ];
                    })
                    ->sortBy(fn (array $range): string => $range['desde'].'|'.$range['hasta'])
                    ->values()
                    ->all();

                return [$paymentType => [
                    'agencias' => count($terminalsByType[$paymentType]),
                    'rangos' => $ranges,
                ]];
            })
            ->all();
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>
     */
    private function updateCoordinatorAmounts(array $payload): array
    {
        $rowsByKey = collect($payload['data'] ?? [])->keyBy(fn (array $row): string => $this->rowKey(
            (string) ($row['cedula'] ?? ''),
            (string) ($row['empresa'] ?? '')
        ));
        $rowsByCedula = collect($payload['data'] ?? [])->groupBy(
            fn (array $row): string => $this->normalizeIdentity((string) ($row['cedula'] ?? ''))
        );
        $detailsByCoordinator = $payload['meta']['coordinador_detalle_usuarios'] ?? [];
        $amountsByCoordinator = [];

        foreach ($detailsByCoordinator as $coordinatorId => &$details) {
            $coordinatorTotal = 0;
            foreach ($details as &$detail) {
                $identity = $this->normalizeIdentity((string) ($detail['cedula'] ?? ''));
                $userLabel = (string) ($detail['usuario'] ?? '');
                $company = str_contains($userLabel, ' | ')
                    ? trim((string) strrchr($userLabel, '|'), "| \t\n\r\0\x0B")
                    : '';
                $row = $company !== '' ? $rowsByKey->get($this->rowKey($identity, $company)) : null;
                $row ??= $rowsByCedula->get($identity, collect())->first();
                $detail['incentivo'] = $row ? $this->integerValue($row['nuevo_incentivo'] ?? 0) : 0;
                $coordinatorTotal += (int) $detail['incentivo'];
            }
            unset($detail);
            $amountsByCoordinator[(string) $coordinatorId] = $coordinatorTotal;
        }
        unset($details);

        $payload['meta']['coordinador_detalle_usuarios'] = $detailsByCoordinator;
        $payload['meta']['coordinador_monto_usuarios'] = $amountsByCoordinator;

        return $payload;
    }

    private function dailySales(
        string $startDate,
        string $endDate,
        string $system,
        Collection $excludedTerminals,
        string $productScope
    ): Collection {
        $buildQuery = function (string $table, string $systemLabel) use ($startDate, $endDate, $excludedTerminals, $productScope): Builder {
            return DB::table($table)
                ->selectRaw('? AS sistema, TRIM(CAST(agencia_id AS CHAR)) AS terminal, cedula, DATE(fecha) AS fecha_venta, SUM(monto) AS ventas', [$systemLabel])
                ->whereBetween('fecha', [$startDate, $endDate])
                ->whereNotNull('cedula')
                ->whereRaw("TRIM(CAST(cedula AS CHAR)) <> ''")
                ->whereNotNull('agencia_id')
                ->whereRaw("TRIM(CAST(agencia_id AS CHAR)) <> ''")
                ->when($excludedTerminals->isNotEmpty(), fn ($query) => $query->whereNotIn(
                    DB::raw('TRIM(CAST(agencia_id AS CHAR))'),
                    $excludedTerminals->all()
                ))
                ->when($productScope === 'no_tradicionales', fn (Builder $query) => $query->whereIn(
                    DB::raw('CAST(producto_id AS SIGNED)'),
                    function (Builder $catalogQuery): void {
                        $catalogQuery
                            ->select('producto_id')
                            ->from('catalogo_juegos')
                            ->where('tipo', 'No Tradicional');
                    }
                ))
                ->groupByRaw('TRIM(CAST(agencia_id AS CHAR)), cedula, DATE(fecha)');
        };

        if ($system === 'Lotobet') {
            return $buildQuery('vt_usuarios_bet', 'Lotobet')->get();
        }

        if ($system === 'Lotonet') {
            return $buildQuery('vt_usuarios_net', 'Lotonet')->get();
        }

        return $buildQuery('vt_usuarios_bet', 'Lotobet')
            ->unionAll($buildQuery('vt_usuarios_net', 'Lotonet'))
            ->get();
    }

    private function agenciesByTerminal(Collection $terminals): Collection
    {
        $agencies = collect();
        foreach ($terminals->chunk(1000) as $chunk) {
            $agencies = $agencies->merge(
                DB::table('agencias')
                    ->whereIn(DB::raw('TRIM(CAST(terminal AS CHAR))'), $chunk->all())
                    ->selectRaw("TRIM(CAST(terminal AS CHAR)) AS terminal, COALESCE(NULLIF(TRIM(sistema), ''), '') AS sistema")
                    ->selectRaw("COALESCE(NULLIF(TRIM(empresa), ''), 'Sin empresa') AS empresa")
                    ->get()
            );
        }

        $bySystem = $agencies->mapWithKeys(fn ($agency): array => [
            $this->terminalKey((string) $agency->sistema, (string) $agency->terminal) => $agency,
        ]);
        $fallback = $agencies->mapWithKeys(fn ($agency): array => [
            $this->terminalKey('', (string) $agency->terminal) => $agency,
        ]);

        return $bySystem->merge($fallback);
    }

    /**
     * @param  array<int, array<string, mixed>>  $ranges
     */
    private function calculateIncentive(int $sales, array $ranges): int
    {
        foreach ($ranges as $range) {
            $from = (int) round((float) ($range['desde'] ?? 0));
            $to = isset($range['hasta']) && $range['hasta'] !== ''
                ? (int) round((float) $range['hasta'])
                : null;

            if ($sales >= $from && ($to === null || $sales <= $to)) {
                $payment = (float) ($range['pago'] ?? 0);

                return (int) round(($range['tipo'] ?? 'fijo') === 'porcentaje'
                    ? $sales * ($payment / 100)
                    : $payment);
            }
        }

        return 0;
    }

    private function validPaymentType(string $paymentType): string
    {
        return in_array($paymentType, IncentivoTerminalTipoPago::TIPOS_PAGO, true)
            ? $paymentType
            : 'tramos_60';
    }

    private function normalizeCompany(string $company): string
    {
        $company = trim($company);
        $lower = mb_strtolower($company);

        if ($company === '' || $lower === 'sin empresa') {
            return 'Agencias por asignar empresa';
        }

        if (str_contains($lower, 'joselito')) {
            return 'Grupo Joselito';
        }

        if (str_contains($lower, 'negosur')) {
            return 'Negosur';
        }

        return $company;
    }

    private function rowKey(string $identity, string $company): string
    {
        return $this->normalizeIdentity($identity).'|'.mb_strtolower($this->normalizeCompany($company));
    }

    private function normalizeIdentity(string $identity): string
    {
        $digits = preg_replace('/\D+/', '', $identity);
        $normalized = ltrim((string) $digits, '0');

        return $normalized === '' ? '0' : $normalized;
    }

    private function integerValue(mixed $value): int
    {
        return (int) round((float) str_replace(',', '', (string) $value));
    }

    private function terminalKey(string $system, string $terminal): string
    {
        return mb_strtolower(trim($system)).'|'.trim($terminal);
    }

    private function effectiveCalendarAssignment(
        Collection $assignments,
        string $system,
        string $terminal,
        string $date
    ): ?IncentivoTerminalTipoPago {
        return $assignments
            ->get($this->terminalKey($system, $terminal), collect())
            ->last(fn (IncentivoTerminalTipoPago $assignment): bool => $assignment->fecha->toDateString() <= $date);
    }
}
