<?php

namespace App\Http\Controllers;

use App\Http\Requests\ConsultarCalendarioIncentivoV6Request;
use App\Http\Requests\GuardarCalendarioIncentivoV6Request;
use App\Http\Requests\GuardarPeriodoIncentivoV6Request;
use App\Http\Requests\ReconocerTerminalesCalendarioIncentivoV6Request;
use App\Http\Requests\ReporteBonoIncentivoRequest;
use App\Http\Requests\ReporteNuevoIncentivoV6Request;
use App\Imports\AgenciasActualizacionMasivaImport;
use App\Models\Agencia;
use App\Models\IncentivoPeriodo;
use App\Models\IncentivoPeriodoDetalle;
use App\Models\IncentivoTerminalTipoPago;
use App\Services\IncentivoBonusReportService;
use App\Services\IncentivoV6Calculator;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Database\Query\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;

class IncentivosAdministrativosController extends Controller
{
    public function __construct(
        private readonly IncentivosAdministrativosReportController $incentivosController,
        private readonly IncentivoV6Calculator $calculator,
        private readonly IncentivoBonusReportService $bonusReportService
    ) {}

    public function index(): View
    {
        $v5View = $this->incentivosController->reporteNuevoIncentivoV5View();

        return view('incentivos.incentivos-administrativos', $v5View->getData());
    }

    public function calendario(ConsultarCalendarioIncentivoV6Request $request): JsonResponse
    {
        $fechaInicio = $request->string('fecha_ini')->toString();
        $fechaFin = $request->string('fecha_fin')->toString();
        $sistema = $request->string('sistema', 'Todos')->toString();
        $buscar = mb_strtolower(trim($request->string('buscar')->toString()));
        $agencias = $this->terminalesActivas($sistema);
        $assignmentHistory = IncentivoTerminalTipoPago::query()
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
        $configuredTerminalKeys = $assignmentHistory
            ->filter(function (Collection $assignments) use ($fechaFin): bool {
                return $this->effectiveCalendarAssignment($assignments, $fechaFin)?->tipo_pago !== null;
            })
            ->map(fn (): bool => true);

        if ($buscar !== '') {
            $agencias = $agencias->filter(function (Agencia $agencia) use ($buscar): bool {
                $texto = mb_strtolower(implode(' ', [
                    $agencia->terminal,
                    $agencia->nombre_agencia,
                    $agencia->empresa,
                    $agencia->sistema_normalizado,
                ]));

                return str_contains($texto, $buscar);
            })->values();
        }

        $agencias = $agencias->sort(function (Agencia $left, Agencia $right) use ($configuredTerminalKeys): int {
            $leftIsConfigured = $configuredTerminalKeys->has($this->terminalKey(
                (string) $left->sistema_normalizado,
                (string) $left->terminal
            ));
            $rightIsConfigured = $configuredTerminalKeys->has($this->terminalKey(
                (string) $right->sistema_normalizado,
                (string) $right->terminal
            ));

            if ($leftIsConfigured !== $rightIsConfigured) {
                return $leftIsConfigured ? -1 : 1;
            }

            $systemComparison = strcasecmp(
                (string) $left->sistema_normalizado,
                (string) $right->sistema_normalizado
            );

            return $systemComparison !== 0
                ? $systemComparison
                : strnatcasecmp((string) $left->terminal, (string) $right->terminal);
        })->values();

        $perPage = $request->integer('per_page', 50);
        $total = $agencias->count();
        $lastPage = max(1, (int) ceil($total / $perPage));
        $page = min($request->integer('page', 1), $lastPage);
        $agencias = $agencias->forPage($page, $perPage)->values();
        $terminales = $agencias->pluck('terminal')->unique()->values();
        $ventas = $this->terminalesConVentas($fechaInicio, $fechaFin, $sistema, $terminales)
            ->keyBy(fn ($venta): string => $this->terminalKey((string) $venta->sistema, (string) $venta->terminal));
        $asignaciones = $assignmentHistory->only(
            $agencias->map(fn (Agencia $agencia): string => $this->terminalKey(
                (string) $agencia->sistema_normalizado,
                (string) $agencia->terminal
            ))->all()
        );

        $rows = $agencias
            ->map(function ($agencia) use ($ventas, $asignaciones, $configuredTerminalKeys, $fechaInicio, $fechaFin): array {
                $terminal = trim((string) $agencia->terminal);
                $sistema = (string) $agencia->sistema_normalizado;
                $venta = $ventas->get($this->terminalKey($sistema, $terminal));
                $tiposPorFecha = [];

                foreach (CarbonPeriod::create($fechaInicio, $fechaFin) as $fecha) {
                    $fechaString = $fecha->toDateString();
                    $asignacion = $this->effectiveCalendarAssignment(
                        $asignaciones->get($this->terminalKey($sistema, $terminal), collect()),
                        $fechaString
                    );
                    if ($asignacion?->tipo_pago !== null) {
                        $tiposPorFecha[$fechaString] = $asignacion->tipo_pago;
                    }
                }

                return [
                    'sistema' => $sistema,
                    'terminal' => $terminal,
                    'agencia' => trim((string) ($agencia->nombre_agencia ?? '')) ?: 'SIN AGENCIA',
                    'empresa' => trim((string) ($agencia->empresa ?? '')) ?: 'Sin empresa',
                    'ventas' => (int) round((float) ($venta->ventas ?? 0)),
                    'tiene_configuracion' => $configuredTerminalKeys->has($this->terminalKey($sistema, $terminal)),
                    'tipos_por_fecha' => $tiposPorFecha,
                ];
            })
            ->values();

        return response()->json([
            'fechas' => collect(CarbonPeriod::create($fechaInicio, $fechaFin))
                ->map(fn (Carbon $fecha): string => $fecha->toDateString())
                ->values(),
            'terminales' => $rows,
            'resumen' => [
                'terminales' => $total,
                'configuraciones' => $rows->sum(fn (array $row): int => count($row['tipos_por_fecha'])),
            ],
            'paginacion' => [
                'pagina_actual' => $page,
                'ultima_pagina' => $lastPage,
                'por_pagina' => $perPage,
                'total' => $total,
                'desde' => $total === 0 ? 0 : (($page - 1) * $perPage) + 1,
                'hasta' => min($page * $perPage, $total),
            ],
        ]);
    }

    public function guardarCalendario(GuardarCalendarioIncentivoV6Request $request): JsonResponse
    {
        $userId = auth()->id();
        $asignaciones = collect($request->validated('asignaciones'))
            ->map(function (array $item) use ($userId): array {
                return [
                    'sistema' => trim($item['sistema']),
                    'terminal' => trim($item['terminal']),
                    'fecha' => $item['fecha'],
                    'tipo_pago' => $item['tipo_pago'] ?? null,
                    'created_by' => $userId,
                    'updated_by' => $userId,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            })
            ->unique(fn (array $item): string => $this->calendarKey($item['sistema'], $item['terminal'], $item['fecha']))
            ->values();
        $desactivadas = $asignaciones->whereNull('tipo_pago')->values();
        $guardadas = $asignaciones->whereNotNull('tipo_pago')->values();

        DB::transaction(function () use ($asignaciones): void {
            if ($asignaciones->isNotEmpty()) {
                IncentivoTerminalTipoPago::query()->upsert(
                    $asignaciones->all(),
                    ['sistema', 'terminal', 'fecha'],
                    ['tipo_pago', 'updated_by', 'updated_at']
                );
            }
        });

        return response()->json([
            'ok' => true,
            'message' => 'Calendario de tipos de pago actualizado.',
            'guardadas' => $guardadas->count(),
            'desactivadas' => $desactivadas->count(),
            'eliminadas' => $desactivadas->count(),
        ]);
    }

    public function reconocerTerminalesCalendario(ReconocerTerminalesCalendarioIncentivoV6Request $request): JsonResponse
    {
        $terminalesLeidas = collect();
        $totalFilas = 0;

        if ($request->hasFile('file')) {
            $import = new AgenciasActualizacionMasivaImport;
            Excel::import($import, $request->file('file'));
            $totalFilas = $import->rows->count();
            $terminalesLeidas = $terminalesLeidas->merge(
                $import->rows
                    ->map(fn ($row): string => trim((string) collect($row)->get('terminal', '')))
                    ->filter()
            );
        }

        $terminalesLeidas = $terminalesLeidas->merge(
            preg_split('/[\s,;]+/', $request->string('terminales_manual')->toString()) ?: []
        )
            ->map(fn ($terminal): string => trim((string) $terminal))
            ->filter();
        $terminalesUnicas = $terminalesLeidas->unique()->values();

        if ($terminalesUnicas->isEmpty()) {
            return response()->json([
                'message' => 'No se encontraron terminales. La plantilla debe tener una columna llamada Terminal.',
            ], 422);
        }

        $agenciasRegistradas = collect();
        foreach ($terminalesUnicas->chunk(1000) as $terminalesChunk) {
            $agenciasRegistradas = $agenciasRegistradas->merge(
                Agencia::query()
                    ->whereIn(DB::raw('TRIM(CAST(terminal AS CHAR))'), $terminalesChunk->all())
                    ->selectRaw('TRIM(CAST(terminal AS CHAR)) AS terminal')
                    ->selectRaw("COALESCE(NULLIF(TRIM(nombre_agencia), ''), NULLIF(TRIM(agencia), ''), 'SIN AGENCIA') AS nombre_agencia")
                    ->selectRaw("COALESCE(NULLIF(TRIM(empresa), ''), 'Sin empresa') AS empresa")
                    ->get()
            );
        }

        $agenciasRegistradas = $agenciasRegistradas
            ->unique(fn (Agencia $agencia): string => (string) $agencia->terminal)
            ->sortBy(fn (Agencia $agencia): string => (string) $agencia->terminal, SORT_NATURAL)
            ->values();
        $terminalesEncontradas = $agenciasRegistradas->pluck('terminal')->unique()->values();
        $sistemaSeleccionado = $request->string('sistema', 'Todos')->toString();
        $sistemas = $sistemaSeleccionado === 'Todos'
            ? collect(IncentivoTerminalTipoPago::SISTEMAS)
            : collect([$sistemaSeleccionado]);
        $agencias = $agenciasRegistradas
            ->map(fn (Agencia $agencia): array => [
                'terminal' => (string) $agencia->terminal,
                'agencia' => (string) $agencia->nombre_agencia,
                'empresa' => (string) $agencia->empresa,
                'sistemas' => $sistemas->values()->all(),
            ])
            ->values();

        return response()->json([
            'ok' => true,
            'total_filas' => $totalFilas,
            'terminales_leidas' => $terminalesLeidas->count(),
            'terminales_unicas' => $terminalesUnicas->count(),
            'encontradas' => $terminalesEncontradas->count(),
            'coincidencias' => $agencias->count(),
            'asignaciones_preparadas' => $agencias->count() * $sistemas->count(),
            'terminales' => $agencias,
            'terminales_no_encontradas' => $terminalesUnicas->diff($terminalesEncontradas)->values(),
        ]);
    }

    public function reporte(ReporteNuevoIncentivoV6Request $request): JsonResponse
    {
        $baseResponse = $this->incentivosController->reporteNuevoIncentivoV5($request);
        $payload = $baseResponse->getData(true);

        if ($baseResponse->getStatusCode() !== 200 || ! is_array($payload) || isset($payload['message'])) {
            return response()->json($payload, $baseResponse->getStatusCode());
        }

        $filters = $request->validated();
        $filters['terminales_excluidas'] = $this->decodeJsonArray($filters['terminales_excluidas'] ?? null);
        $rangesByType = $this->paymentRangesByType($filters['rangos_pago_por_tipo'] ?? null);
        $payload = $this->calculator->applyDailyPaymentTypes($payload, $filters, $rangesByType);

        return response()->json($payload);
    }

    public function reporteBono(ReporteBonoIncentivoRequest $request): JsonResponse
    {
        return response()->json($this->bonusReportService->generate(
            $request->string('fecha_ini')->toString(),
            $request->string('fecha_fin')->toString(),
            $request->string('sistema', 'Todos')->toString()
        ));
    }

    public function guardarPeriodo(GuardarPeriodoIncentivoV6Request $request): JsonResponse
    {
        $validated = $request->validated();
        $fechaInicio = Carbon::createFromFormat('Y-m-d', $validated['fecha_inicio'])->startOfDay();
        $fechaFin = Carbon::createFromFormat('Y-m-d', $validated['fecha_fin'])->startOfDay();
        $userId = auth()->id();

        $result = DB::transaction(function () use ($validated, $fechaInicio, $fechaFin, $userId): array {
            $periodo = IncentivoPeriodo::query()
                ->where('anio', $fechaInicio->year)
                ->where('mes', $fechaInicio->month)
                ->lockForUpdate()
                ->first();
            $actualizado = $periodo !== null;

            if ($periodo === null) {
                $periodo = new IncentivoPeriodo([
                    'anio' => $fechaInicio->year,
                    'mes' => $fechaInicio->month,
                    'revision' => 1,
                    'created_by' => $userId,
                ]);
            } else {
                $periodo->revision++;
            }

            $periodo->fill([
                'fecha_inicio' => $fechaInicio->toDateString(),
                'fecha_fin' => $fechaFin->toDateString(),
                'sistema' => $validated['sistema'],
                'modo_calculo' => $validated['modo_calculo'],
                'tipo_pago_defecto' => $validated['tipo_pago_defecto'],
                'min_dias_venta' => $validated['min_dias_venta'],
                'rangos_pago_por_tipo' => $validated['rangos_pago_por_tipo'] ?? [],
                'terminales_excluidas' => $validated['terminales_excluidas'] ?? [],
                'updated_by' => $userId,
            ]);
            $periodo->save();

            $detalles = collect($validated['detalles'])
                ->map(function (array $detalle) use ($periodo): array {
                    $incentivoGenerado = (int) round((float) $detalle['incentivo_generado']);
                    $montoPagado = min(
                        $incentivoGenerado,
                        (int) round((float) $detalle['monto_pagado'])
                    );
                    $montoNoPagado = max($incentivoGenerado - $montoPagado, 0);
                    $motivos = $incentivoGenerado <= 0
                        ? ['meta_no_alcanzada']
                        : collect($detalle['motivos'] ?? [])
                            ->reject(fn (string $motivo): bool => $motivo === 'meta_no_alcanzada')
                            ->unique()
                            ->values()
                            ->all();
                    $estado = match (true) {
                        $incentivoGenerado <= 0 => 'no_califica',
                        $montoPagado <= 0 => 'no_pagado',
                        $montoNoPagado > 0 => 'pagado_parcial',
                        default => 'pagado',
                    };

                    return [
                        'incentivo_periodo_id' => $periodo->id,
                        'cedula' => preg_replace('/\D+/', '', (string) $detalle['cedula']),
                        'empleadoid' => $this->nullableTrimmedString($detalle['empleadoid'] ?? null),
                        'nombre' => trim((string) $detalle['nombre']),
                        'empresa' => trim((string) $detalle['empresa']),
                        'ultima_terminal' => $this->nullableTrimmedString($detalle['ultima_terminal'] ?? null),
                        'ultima_agencia_nombre' => $this->nullableTrimmedString($detalle['ultima_agencia_nombre'] ?? null),
                        'ventas_ultimo_mes' => (int) round((float) $detalle['ventas_ultimo_mes']),
                        'ventas_mes_actual' => (int) round((float) $detalle['ventas_mes_actual']),
                        'dias_ventas' => (int) $detalle['dias_ventas'],
                        'horas_total' => round((float) ($detalle['horas_total'] ?? 0), 2),
                        'incentivo_generado' => $incentivoGenerado,
                        'monto_pagado' => $montoPagado,
                        'monto_no_pagado' => $montoNoPagado,
                        'estado' => $estado,
                        'motivos' => json_encode($motivos, JSON_THROW_ON_ERROR),
                        'tipos_pago_detalle' => json_encode(
                            $detalle['tipos_pago_detalle'] ?? [],
                            JSON_THROW_ON_ERROR
                        ),
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                })
                ->unique(fn (array $detalle): string => mb_strtolower(
                    $detalle['cedula'].'|'.$detalle['empresa']
                ))
                ->values();

            $periodo->detalles()->delete();
            $detalles->chunk(500)->each(
                fn (Collection $chunk) => IncentivoPeriodoDetalle::query()->insert($chunk->all())
            );

            $resumen = [
                'registros' => $detalles->count(),
                'pagados' => $detalles->where('estado', 'pagado')->count(),
                'pagados_parciales' => $detalles->where('estado', 'pagado_parcial')->count(),
                'no_pagados' => $detalles->where('estado', 'no_pagado')->count(),
                'no_califican' => $detalles->where('estado', 'no_califica')->count(),
                'incentivo_generado' => (int) $detalles->sum('incentivo_generado'),
                'monto_pagado' => (int) $detalles->sum('monto_pagado'),
                'monto_no_pagado' => (int) $detalles->sum('monto_no_pagado'),
            ];
            $periodo->update(['resumen' => $resumen]);

            return [
                'periodo' => $periodo->fresh(),
                'actualizado' => $actualizado,
                'resumen' => $resumen,
            ];
        });

        /** @var IncentivoPeriodo $periodo */
        $periodo = $result['periodo'];

        return response()->json([
            'ok' => true,
            'actualizado' => $result['actualizado'],
            'message' => $result['actualizado']
                ? 'El período existente fue actualizado correctamente.'
                : 'El período fue guardado correctamente.',
            'periodo' => [
                'id' => $periodo->id,
                'anio' => $periodo->anio,
                'mes' => $periodo->mes,
                'revision' => $periodo->revision,
            ],
            'resumen' => $result['resumen'],
        ]);
    }

    private function terminalesConVentas(
        string $fechaInicio,
        string $fechaFin,
        string $sistema,
        Collection $terminales
    ): Collection {
        if ($terminales->isEmpty()) {
            return collect();
        }

        $buildQuery = function (string $table, string $system) use ($fechaInicio, $fechaFin, $terminales): Builder {
            return DB::table($table)
                ->selectRaw('? AS sistema, TRIM(CAST(agencia_id AS CHAR)) AS terminal, SUM(monto) AS ventas', [$system])
                ->whereBetween('fecha', [$fechaInicio, $fechaFin])
                ->whereIn(DB::raw('TRIM(CAST(agencia_id AS CHAR))'), $terminales->all())
                ->whereNotNull('agencia_id')
                ->whereRaw("TRIM(CAST(agencia_id AS CHAR)) <> ''")
                ->groupByRaw('TRIM(CAST(agencia_id AS CHAR))');
        };

        if ($sistema === 'Lotobet') {
            return $buildQuery('vt_usuarios_bet', 'Lotobet')->get();
        }

        if ($sistema === 'Lotonet') {
            return $buildQuery('vt_usuarios_net', 'Lotonet')->get();
        }

        return DB::query()
            ->fromSub(
                $buildQuery('vt_usuarios_bet', 'Lotobet')->unionAll($buildQuery('vt_usuarios_net', 'Lotonet')),
                'ventas_terminales'
            )
            ->selectRaw('sistema, terminal, SUM(ventas) AS ventas')
            ->groupBy('sistema', 'terminal')
            ->get();
    }

    private function terminalesActivas(string $sistema): Collection
    {
        $normalizedSystemSql = "CASE
            WHEN UPPER(TRIM(sistema)) = 'LOTOBET' THEN 'Lotobet'
            WHEN UPPER(TRIM(sistema)) IN ('LOTONET', 'LOTENET') THEN 'Lotonet'
            ELSE NULL
        END";

        return Agencia::query()
            ->where('estatus', 1)
            ->whereNotNull('terminal')
            ->whereRaw("TRIM(CAST(terminal AS CHAR)) <> ''")
            ->whereRaw("{$normalizedSystemSql} IS NOT NULL")
            ->when($sistema !== 'Todos', fn ($query) => $query->whereRaw("{$normalizedSystemSql} = ?", [$sistema]))
            ->selectRaw('TRIM(CAST(terminal AS CHAR)) AS terminal')
            ->selectRaw("{$normalizedSystemSql} AS sistema_normalizado")
            ->selectRaw("COALESCE(NULLIF(TRIM(nombre_agencia), ''), NULLIF(TRIM(agencia), ''), 'SIN AGENCIA') AS nombre_agencia")
            ->selectRaw("COALESCE(NULLIF(TRIM(empresa), ''), 'Sin empresa') AS empresa")
            ->orderByRaw("{$normalizedSystemSql}")
            ->orderByRaw('CAST(terminal AS UNSIGNED)')
            ->get()
            ->unique(fn (Agencia $agencia): string => $this->terminalKey(
                (string) $agencia->sistema_normalizado,
                (string) $agencia->terminal
            ))
            ->values();
    }

    private function calendarKey(string $sistema, string $terminal, string $fecha): string
    {
        return mb_strtolower(trim($sistema)).'|'.trim($terminal).'|'.$fecha;
    }

    private function effectiveCalendarAssignment(Collection $assignments, string $date): ?IncentivoTerminalTipoPago
    {
        return $assignments->last(
            fn (IncentivoTerminalTipoPago $assignment): bool => $assignment->fecha->toDateString() <= $date
        );
    }

    private function terminalKey(string $sistema, string $terminal): string
    {
        return mb_strtolower(trim($sistema)).'|'.trim($terminal);
    }

    private function nullableTrimmedString(mixed $value): ?string
    {
        $value = trim((string) $value);

        return $value !== '' ? $value : null;
    }

    /**
     * @return array<int, mixed>
     */
    private function decodeJsonArray(mixed $value): array
    {
        if (is_array($value)) {
            return $value;
        }

        if (! is_string($value) || trim($value) === '') {
            return [];
        }

        $decoded = json_decode($value, true);

        return is_array($decoded) ? $decoded : [];
    }

    /**
     * @return array<string, array<int, array<string, mixed>>>
     */
    private function paymentRangesByType(mixed $value): array
    {
        $decoded = is_string($value) ? json_decode($value, true) : $value;
        $defaults = [
            'tramos_60' => $this->buildPaymentRanges(1, [1000, 2000, 4000, 6000, 8000, 9000]),
            'tramos_70' => $this->buildPaymentRanges(0.75, [750, 1500, 3000, 4500, 6000, 6750]),
            'tramos_80' => $this->buildPaymentRanges(0.5, [500, 1000, 2000, 3000, 4000, 4500]),
        ];

        if (! is_array($decoded)) {
            return $defaults;
        }

        foreach (IncentivoTerminalTipoPago::TIPOS_PAGO as $paymentType) {
            if (isset($decoded[$paymentType]) && is_array($decoded[$paymentType]) && $decoded[$paymentType] !== []) {
                $defaults[$paymentType] = $decoded[$paymentType];
            }
        }

        return $defaults;
    }

    /**
     * @param  array<int, int>  $fixedPayments
     * @return array<int, array<string, int|float|string|null>>
     */
    private function buildPaymentRanges(float $percentage, array $fixedPayments): array
    {
        return [
            ['desde' => 100001, 'hasta' => 250000, 'pago' => $fixedPayments[0], 'tipo' => 'fijo'],
            ['desde' => 250001, 'hasta' => 400000, 'pago' => $fixedPayments[1], 'tipo' => 'fijo'],
            ['desde' => 400001, 'hasta' => 550000, 'pago' => $fixedPayments[2], 'tipo' => 'fijo'],
            ['desde' => 550001, 'hasta' => 700000, 'pago' => $fixedPayments[3], 'tipo' => 'fijo'],
            ['desde' => 700001, 'hasta' => 850000, 'pago' => $fixedPayments[4], 'tipo' => 'fijo'],
            ['desde' => 850001, 'hasta' => 1000000, 'pago' => $fixedPayments[5], 'tipo' => 'fijo'],
            ['desde' => 1000001, 'hasta' => 5000000, 'pago' => $percentage, 'tipo' => 'porcentaje'],
            ['desde' => 5000001, 'hasta' => null, 'pago' => $percentage, 'tipo' => 'porcentaje'],
        ];
    }
}
