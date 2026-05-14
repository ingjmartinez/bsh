<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ContabilidadReporteController extends Controller
{
    public function estadoResultado()
    {
        return view('contabilidad.reportes.estado_resultado', [
            'tipos' => collect([
                'Ingreso',
                'Costo',
                'Gasto',
            ]),
        ]);
    }

    public function estadoResultadoData(Request $request)
    {
        @set_time_limit(300);

        $validated = $request->validate([
            'fecha_desde' => ['required', 'date_format:Y-m-d'],
            'fecha_hasta' => ['required', 'date_format:Y-m-d'],
            'tipos' => ['nullable', 'array'],
            'tipos.*' => ['nullable', 'string', 'max:50'],
        ]);

        $tiposSeleccionados = collect($validated['tipos'] ?? [])
            ->map(fn ($tipo) => trim((string) $tipo))
            ->filter()
            ->values();

        $tiposSeleccionadosNormalizados = $tiposSeleccionados
            ->map(fn ($tipo) => mb_strtolower(trim($tipo)))
            ->filter()
            ->unique()
            ->values();

        $desde = Carbon::createFromFormat('Y-m-d', $validated['fecha_desde'])->startOfDay();
        $hasta = Carbon::createFromFormat('Y-m-d', $validated['fecha_hasta'])->endOfDay();

        if ($desde->gt($hasta)) {
            return response()->json([
                'message' => 'El período desde no puede ser mayor que el período hasta.',
            ], 422);
        }

        $inicioPeriodo = $desde->toDateString();
        $finPeriodo = $hasta->toDateString();
        $inicioAcumulado = $desde->copy()->startOfYear()->toDateString();

        $cuentasQuery = DB::table('cuentas_contables')
            ->select('cuenta', 'descripcion', 'tipo')
            ->whereNotNull('tipo')
            ->whereRaw("TRIM(tipo) <> ''");

        if ($tiposSeleccionadosNormalizados->isNotEmpty()) {
            $placeholders = implode(',', array_fill(0, $tiposSeleccionadosNormalizados->count(), '?'));
            $cuentasQuery->whereRaw(
                'LOWER(TRIM(COALESCE(tipo, ""))) IN (' . $placeholders . ')',
                $tiposSeleccionadosNormalizados->all()
            );
        }

        $cuentas = $cuentasQuery
            ->orderBy('tipo')
            ->orderBy('cuenta')
            ->get();

        $this->sincronizarDetalleCuentasDesdeApi($cuentas->pluck('cuenta')->all(), Carbon::parse($inicioAcumulado), $hasta);

        $query = DB::table('cuentas_contables as c')
            ->leftJoin('detalle_cuentas as d', 'c.cuenta', '=', 'd.cuenta')
            ->selectRaw('c.cuenta')
            ->selectRaw('c.descripcion')
            ->selectRaw('c.tipo')
            ->selectRaw('SUM(CASE WHEN d.fecha BETWEEN ? AND ? THEN COALESCE(d.debito, 0) ELSE 0 END) AS debito_periodo', [$inicioPeriodo, $finPeriodo])
            ->selectRaw('SUM(CASE WHEN d.fecha BETWEEN ? AND ? THEN COALESCE(d.credito, 0) ELSE 0 END) AS credito_periodo', [$inicioPeriodo, $finPeriodo])
            ->selectRaw('SUM(CASE WHEN d.fecha BETWEEN ? AND ? THEN COALESCE(d.debito, 0) ELSE 0 END) AS debito_acumulado', [$inicioAcumulado, $finPeriodo])
            ->selectRaw('SUM(CASE WHEN d.fecha BETWEEN ? AND ? THEN COALESCE(d.credito, 0) ELSE 0 END) AS credito_acumulado', [$inicioAcumulado, $finPeriodo])
            ->whereNotNull('c.tipo')
            ->whereRaw("TRIM(c.tipo) <> ''");

        if ($tiposSeleccionadosNormalizados->isNotEmpty()) {
            $placeholders = implode(',', array_fill(0, $tiposSeleccionadosNormalizados->count(), '?'));
            $query->whereRaw(
                'LOWER(TRIM(COALESCE(c.tipo, ""))) IN (' . $placeholders . ')',
                $tiposSeleccionadosNormalizados->all()
            );
        }

        $rows = $query
            ->groupBy('c.cuenta', 'c.descripcion', 'c.tipo')
            ->orderBy('c.tipo')
            ->orderBy('c.cuenta')
            ->get();

        $gruposBase = [
            'ingreso' => [
                'titulo' => 'Ingresos',
                'orden' => 1,
                'cuentas' => [],
                'total_periodo' => 0.0,
                'total_acumulado' => 0.0,
            ],
            'costo' => [
                'titulo' => 'Costos',
                'orden' => 2,
                'cuentas' => [],
                'total_periodo' => 0.0,
                'total_acumulado' => 0.0,
            ],
            'gasto' => [
                'titulo' => 'Gastos',
                'orden' => 3,
                'cuentas' => [],
                'total_periodo' => 0.0,
                'total_acumulado' => 0.0,
            ],
            'otro' => [
                'titulo' => 'Otros',
                'orden' => 4,
                'cuentas' => [],
                'total_periodo' => 0.0,
                'total_acumulado' => 0.0,
            ],
        ];

        foreach ($rows as $row) {
            $grupoKey = $this->resolverGrupoPorTipo((string) $row->tipo);

            $periodo = $this->calcularMontoPorGrupo(
                $grupoKey,
                (float) $row->debito_periodo,
                (float) $row->credito_periodo
            );

            $acumulado = $this->calcularMontoPorGrupo(
                $grupoKey,
                (float) $row->debito_acumulado,
                (float) $row->credito_acumulado
            );

            $gruposBase[$grupoKey]['cuentas'][] = [
                'cuenta' => (string) $row->cuenta,
                'descripcion' => (string) $row->descripcion,
                'tipo' => (string) $row->tipo,
                'periodo' => round($periodo, 2),
                'acumulado' => round($acumulado, 2),
            ];

            $gruposBase[$grupoKey]['total_periodo'] += $periodo;
            $gruposBase[$grupoKey]['total_acumulado'] += $acumulado;
        }

        $grupos = collect($gruposBase)
            ->filter(fn ($grupo) => !empty($grupo['cuentas']))
            ->sortBy('orden')
            ->values()
            ->map(function ($grupo) {
                $grupo['total_periodo'] = round($grupo['total_periodo'], 2);
                $grupo['total_acumulado'] = round($grupo['total_acumulado'], 2);
                return $grupo;
            })
            ->all();

        $totales = collect($grupos)->keyBy('titulo');
        $ingresosPeriodo = (float) ($totales['Ingresos']['total_periodo'] ?? 0);
        $costosPeriodo = (float) ($totales['Costos']['total_periodo'] ?? 0);
        $gastosPeriodo = (float) ($totales['Gastos']['total_periodo'] ?? 0);

        $ingresosAcumulado = (float) ($totales['Ingresos']['total_acumulado'] ?? 0);
        $costosAcumulado = (float) ($totales['Costos']['total_acumulado'] ?? 0);
        $gastosAcumulado = (float) ($totales['Gastos']['total_acumulado'] ?? 0);

        return response()->json([
            'periodo_texto' => $desde->format('d/m/Y') . ' hasta ' . $hasta->format('d/m/Y'),
            'columnas' => [
                'periodo' => $desde->format('d/m/Y') . ' a ' . $hasta->format('d/m/Y'),
                'acumulado' => 'Acumulado',
            ],
            'grupos' => $grupos,
            'resumen' => [
                'ingresos_periodo' => round($ingresosPeriodo, 2),
                'costos_periodo' => round($costosPeriodo, 2),
                'gastos_periodo' => round($gastosPeriodo, 2),
                'resultado_periodo' => round($ingresosPeriodo - $costosPeriodo - $gastosPeriodo, 2),
                'ingresos_acumulado' => round($ingresosAcumulado, 2),
                'costos_acumulado' => round($costosAcumulado, 2),
                'gastos_acumulado' => round($gastosAcumulado, 2),
                'resultado_acumulado' => round($ingresosAcumulado - $costosAcumulado - $gastosAcumulado, 2),
            ],
        ]);
    }

    private function sincronizarDetalleCuentasDesdeApi(array $cuentas, Carbon $desde, Carbon $hasta): void
    {
        $cuentas = collect($cuentas)
            ->map(fn ($cuenta) => trim((string) $cuenta))
            ->filter()
            ->unique()
            ->values();

        if ($cuentas->isEmpty()) {
            return;
        }

        foreach ($cuentas as $cuenta) {
            $fechaActual = $desde->copy();

            while ($fechaActual->lte($hasta)) {
                $items = $this->fetchDetalleByDate($cuenta, $fechaActual->toDateString());

                foreach ($items as $rawItem) {
                    if (!is_array($rawItem)) {
                        continue;
                    }

                    $item = $this->normalizeKeys($rawItem);
                    $fechaRaw = (string) ($item['FECHA'] ?? $fechaActual->toDateString());
                    $fecha = $this->parseDate($fechaRaw) ?? $fechaActual->toDateString();

                    $externalKey = sha1(implode('|', [
                        $cuenta,
                        (string) ($item['NOASIENTO'] ?? ''),
                        $fecha,
                        (string) ($item['REF'] ?? ''),
                        (string) ($item['NOREF'] ?? ''),
                        (string) ($item['MODULO'] ?? ''),
                        (string) ($item['DEBITO'] ?? ''),
                        (string) ($item['CREDITO'] ?? ''),
                    ]));

                    DB::table('detalle_cuentas')->updateOrInsert(
                        ['external_key' => $externalKey],
                        [
                            'cuenta' => $cuenta,
                            'no_asiento' => $this->nullableString($item['NOASIENTO'] ?? null),
                            'fecha' => $fecha,
                            'fecha_raw' => $fechaRaw,
                            'ref' => $this->nullableString($item['REF'] ?? null),
                            'no_ref' => $this->nullableString($item['NOREF'] ?? null),
                            'debito' => $this->parseDecimal($item['DEBITO'] ?? null),
                            'credito' => $this->parseDecimal($item['CREDITO'] ?? null),
                            'descripcion' => $this->nullableString($item['DESCRIPCION'] ?? null),
                            'grupo' => $this->nullableString($item['GRUPO'] ?? null),
                            'sub_grupo' => $this->nullableString($item['SUBGRUPO'] ?? null),
                            'division' => $this->nullableString($item['DIVISION'] ?? null),
                            'centro_costo' => $this->nullableString($item['CENTROCOSTO'] ?? null),
                            'conciliado' => $this->nullableString($item['CONCILIADO'] ?? null),
                            'modulo' => $this->nullableString($item['MODULO'] ?? null),
                            'fecha_grabado' => $this->nullableString($item['FECHAGRABADO'] ?? null),
                            'fecha_modificado' => $this->nullableString($item['FECHAMODIFICADO'] ?? null),
                            'creado_por' => $this->nullableString($item['CREADOPOR'] ?? null),
                            'modificado_por' => $this->nullableString($item['MODIFICADOPOR'] ?? null),
                            'ref_desc' => $this->nullableString($item['REFDESC'] ?? null),
                            'sociedad' => $this->nullableString($item['SOCIEDAD'] ?? null),
                            'updated_at' => now(),
                            'created_at' => now(),
                        ]
                    );
                }

                $fechaActual->addDay();
            }
        }
    }

    private function resolverGrupoPorTipo(string $tipo): string
    {
        $normalizado = mb_strtolower(trim($tipo));

        if (str_contains($normalizado, 'ingreso')) {
            return 'ingreso';
        }

        if (str_contains($normalizado, 'costo')) {
            return 'costo';
        }

        if (str_contains($normalizado, 'gasto')) {
            return 'gasto';
        }

        return 'otro';
    }

    private function calcularMontoPorGrupo(string $grupo, float $debito, float $credito): float
    {
        if ($grupo === 'ingreso') {
            return $credito - $debito;
        }

        return $debito - $credito;
    }

    private function fetchDetalleByDate(string $cuenta, string $fecha): array
    {
        $url = 'https://apisj.azurewebsites.net/ApiSJ/EntradaDiario/Listar?strToken=87eb2d56-25f3-4d46-9cb0-73c07a550bd2&intIdEmpresa=168&dtFecha=' . $fecha . '&strCuenta=' . urlencode($cuenta);

        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json'
            ],
            CURLOPT_SSL_VERIFYHOST => 0,
            CURLOPT_SSL_VERIFYPEER => 0,
        ]);

        $response = curl_exec($curl);

        if ($response === false) {
            curl_close($curl);
            return [];
        }

        curl_close($curl);

        $decoded = json_decode($response, true);

        if (!is_array($decoded)) {
            return [];
        }

        $decoded = $this->normalizeKeys($decoded);

        if (isset($decoded['RESULT']) && is_array($decoded['RESULT']) && isset($decoded['RESULT']['DET']) && is_array($decoded['RESULT']['DET'])) {
            return $decoded['RESULT']['DET'];
        }

        if (isset($decoded['DET']) && is_array($decoded['DET'])) {
            return $decoded['DET'];
        }

        return [];
    }

    private function normalizeKeys($array)
    {
        $normalized = [];
        foreach ($array as $key => $value) {
            $upperKey = strtoupper($key);
            if (is_array($value)) {
                $normalized[$upperKey] = $this->normalizeKeys($value);
            } else {
                $normalized[$upperKey] = $value;
            }
        }

        return $normalized;
    }

    private function nullableString($value): ?string
    {
        if ($value === null) {
            return null;
        }

        $value = trim((string) $value);

        return $value === '' ? null : $value;
    }

    private function parseDate(?string $value): ?string
    {
        if ($value === null || trim($value) === '') {
            return null;
        }

        try {
            return Carbon::parse($value)->toDateString();
        } catch (\Throwable $e) {
            return null;
        }
    }

    private function parseDecimal($value): ?float
    {
        if ($value === null) {
            return null;
        }

        $clean = str_replace(',', '', trim((string) $value));

        if ($clean === '' || !is_numeric($clean)) {
            return null;
        }

        return (float) $clean;
    }
}
