<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class ContabilidadEstadoResultadoController extends Controller
{
    private const MAX_LLAMADAS_API_EN_VIVO = 1000;

    public function index()
    {
        return view('contabilidad.reportes.estado_resultado', [
            'tipos' => collect(['Ingreso', 'Costo', 'Gasto']),
        ]);
    }

    public function data(Request $request)
    {
        @set_time_limit(600);

        $validated = $request->validate([
            'fecha_desde' => ['required', 'date_format:Y-m-d'],
            'fecha_hasta' => ['required', 'date_format:Y-m-d'],
            'tipos' => ['nullable', 'array'],
            'tipos.*' => ['nullable', 'string', 'max:50'],
            'cuenta_offset' => ['nullable', 'integer', 'min:0'],
            'cuenta_limit' => ['nullable', 'integer', 'min:1', 'max:100'],
        ]);

        $desde = Carbon::createFromFormat('Y-m-d', $validated['fecha_desde'])->startOfDay();
        $hasta = Carbon::createFromFormat('Y-m-d', $validated['fecha_hasta'])->endOfDay();

        if ($desde->gt($hasta)) {
            return response()->json([
                'message' => 'La fecha desde no puede ser mayor que la fecha hasta.',
            ], 422);
        }

        $tiposSeleccionados = collect($validated['tipos'] ?? [])
            ->map(fn ($tipo) => trim((string) $tipo))
            ->filter()
            ->unique()
            ->values();

        $cuentas = $this->obtenerCuentasPorTipo($tiposSeleccionados);
        $cuentaOffset = (int) ($validated['cuenta_offset'] ?? 0);
        $cuentaLimit = isset($validated['cuenta_limit']) ? (int) $validated['cuenta_limit'] : null;

        if ($cuentaLimit !== null) {
            $cuentas = $cuentas->slice($cuentaOffset, $cuentaLimit)->values();
        }

        $dias = $desde->copy()->startOfDay()->diffInDays($hasta->copy()->startOfDay()) + 1;
        $llamadasEstimadas = (int) $cuentas->count() * (int) $dias;

        if ($cuentas->isEmpty()) {
            return response()->json($this->respuestaVacia($desde, $hasta));
        }

        if ($llamadasEstimadas > self::MAX_LLAMADAS_API_EN_VIVO) {
            return response()->json([
                'message' => 'La consulta es demasiado amplia para modo API en vivo. Reduce el rango de fechas o selecciona menos bloques contables.',
                'debug' => [
                    'cuentas' => $cuentas->count(),
                    'dias' => $dias,
                    'llamadas_estimadas' => $llamadasEstimadas,
                    'max_llamadas_api_en_vivo' => self::MAX_LLAMADAS_API_EN_VIVO,
                ],
            ], 422);
        }

        return response()->json(
            $this->construirReportePorBloqueDesdeApi($cuentas, $desde, $hasta) + [
                'meta' => [
                    'cuenta_offset' => $cuentaOffset,
                    'cuenta_limit' => $cuentaLimit,
                    'cuentas_procesadas' => $cuentas->count(),
                ],
            ]
        );
    }

    public function meta(Request $request)
    {
        $validated = $request->validate([
            'tipos' => ['nullable', 'array'],
            'tipos.*' => ['nullable', 'string', 'max:50'],
        ]);

        $tiposSeleccionados = collect($validated['tipos'] ?? [])
            ->map(fn ($tipo) => trim((string) $tipo))
            ->filter()
            ->unique()
            ->values();

        $cuentas = $this->obtenerCuentasPorTipo($tiposSeleccionados);
        $conteoPorTipo = $cuentas
            ->groupBy(fn ($cuenta) => (string) ($cuenta['tipo'] ?? ''))
            ->map(fn ($items) => $items->count())
            ->all();

        return response()->json([
            'total_cuentas' => $cuentas->count(),
            'conteo_por_tipo' => $conteoPorTipo,
        ]);
    }

    private function obtenerCuentasPorTipo(Collection $tiposSeleccionados): Collection
    {
        $tiposNormalizados = $tiposSeleccionados
            ->map(fn ($tipo) => mb_strtolower(trim($tipo)))
            ->filter()
            ->unique()
            ->values();

        $query = DB::table('cuentas_contables')
            ->select('cuenta', 'descripcion', 'tipo')
            ->whereNotNull('tipo')
            ->whereRaw("TRIM(tipo) <> ''");

        if ($tiposNormalizados->isNotEmpty()) {
            $placeholders = implode(',', array_fill(0, $tiposNormalizados->count(), '?'));
            $query->whereRaw(
                'LOWER(TRIM(COALESCE(tipo, ""))) IN (' . $placeholders . ')',
                $tiposNormalizados->all()
            );
        }

        return $query
            ->orderBy('tipo')
            ->orderBy('cuenta')
            ->get()
            ->map(function ($cuenta) {
                return [
                    'cuenta' => trim((string) $cuenta->cuenta),
                    'descripcion' => trim((string) $cuenta->descripcion),
                    'tipo' => trim((string) $cuenta->tipo),
                    'bloque' => $this->resolverBloque((string) $cuenta->tipo),
                ];
            })
            ->values();
    }

    private function construirReportePorBloqueDesdeApi(Collection $cuentas, Carbon $desde, Carbon $hasta): array
    {
        $gruposBase = [
            'ingreso' => ['titulo' => 'Ingresos', 'orden' => 1, 'cuentas' => [], 'total_periodo' => 0.0],
            'costo' => ['titulo' => 'Costos', 'orden' => 2, 'cuentas' => [], 'total_periodo' => 0.0],
            'gasto' => ['titulo' => 'Gastos', 'orden' => 3, 'cuentas' => [], 'total_periodo' => 0.0],
        ];

        foreach ($cuentas as $cuenta) {
            $monto = $this->sumarCuentaPorRango($cuenta, $desde, $hasta);

            if (abs($monto) < 0.00001) {
                continue;
            }

            $gruposBase[$cuenta['bloque']]['cuentas'][] = [
                'cuenta' => $cuenta['cuenta'],
                'descripcion' => $cuenta['descripcion'],
                'tipo' => $cuenta['tipo'],
                'periodo' => round($monto, 2),
                'acumulado' => round($monto, 2),
            ];

            $gruposBase[$cuenta['bloque']]['total_periodo'] += $monto;
        }

        $grupos = collect($gruposBase)
            ->filter(fn ($grupo) => !empty($grupo['cuentas']))
            ->sortBy('orden')
            ->map(function ($grupo) {
                $grupo['total_periodo'] = round($grupo['total_periodo'], 2);
                $grupo['total_acumulado'] = round($grupo['total_periodo'], 2);
                return $grupo;
            })
            ->values()
            ->all();

        $totales = collect($grupos)->keyBy('titulo');
        $ingresos = (float) ($totales['Ingresos']['total_periodo'] ?? 0);
        $costos = (float) ($totales['Costos']['total_periodo'] ?? 0);
        $gastos = (float) ($totales['Gastos']['total_periodo'] ?? 0);

        return [
            'periodo_texto' => $desde->format('d/m/Y') . ' hasta ' . $hasta->format('d/m/Y'),
            'columnas' => [
                'periodo' => $desde->format('d/m/Y') . ' a ' . $hasta->format('d/m/Y'),
                'acumulado' => 'Acumulado rango',
            ],
            'grupos' => $grupos,
            'resumen' => [
                'ingresos_periodo' => round($ingresos, 2),
                'costos_periodo' => round($costos, 2),
                'gastos_periodo' => round($gastos, 2),
                'resultado_periodo' => round($ingresos - $costos - $gastos, 2),
                'ingresos_acumulado' => round($ingresos, 2),
                'costos_acumulado' => round($costos, 2),
                'gastos_acumulado' => round($gastos, 2),
                'resultado_acumulado' => round($ingresos - $costos - $gastos, 2),
            ],
        ];
    }

    private function respuestaVacia(Carbon $desde, Carbon $hasta): array
    {
        return [
            'periodo_texto' => $desde->format('d/m/Y') . ' hasta ' . $hasta->format('d/m/Y'),
            'columnas' => [
                'periodo' => $desde->format('d/m/Y') . ' a ' . $hasta->format('d/m/Y'),
                'acumulado' => 'Acumulado rango',
            ],
            'grupos' => [],
            'resumen' => [
                'ingresos_periodo' => 0,
                'costos_periodo' => 0,
                'gastos_periodo' => 0,
                'resultado_periodo' => 0,
                'ingresos_acumulado' => 0,
                'costos_acumulado' => 0,
                'gastos_acumulado' => 0,
                'resultado_acumulado' => 0,
            ],
        ];
    }

    private function resolverBloque(string $tipo): string
    {
        $normalizado = mb_strtolower(trim($tipo));

        if (str_contains($normalizado, 'ingreso')) {
            return 'ingreso';
        }

        if (str_contains($normalizado, 'costo')) {
            return 'costo';
        }

        return 'gasto';
    }

    private function sumarCuentaPorRango(array $cuenta, Carbon $desde, Carbon $hasta): float
    {
        $monto = 0.0;
        $fechaActual = $desde->copy();

        while ($fechaActual->lte($hasta)) {
            $fecha = $fechaActual->toDateString();
            $items = $this->fetchDetalleByDate($cuenta['cuenta'], $fecha);

            foreach ($items as $rawItem) {
                if (!is_array($rawItem)) {
                    continue;
                }

                $item = $this->normalizeKeys($rawItem);
                $debito = (float) ($this->parseDecimal($item['DEBITO'] ?? null) ?? 0);
                $credito = (float) ($this->parseDecimal($item['CREDITO'] ?? null) ?? 0);

                if (($cuenta['bloque'] ?? '') === 'ingreso') {
                    $monto += ($credito - $debito);
                } else {
                    $monto += ($debito - $credito);
                }
            }

            unset($items);
            $fechaActual->addDay();
        }

        return $monto;
    }

    private function fetchDetalleByDate(string $cuenta, string $fecha): array
    {
        $cacheKey = 'estado_resultado_api:' . sha1($cuenta . '|' . $fecha);

        return Cache::remember($cacheKey, now()->addHours(6), function () use ($cuenta, $fecha) {
            $url = 'https://apisj.azurewebsites.net/ApiSJ/EntradaDiario/Listar?strToken=87eb2d56-25f3-4d46-9cb0-73c07a550bd2&intIdEmpresa=168&dtFecha=' . $fecha . '&strCuenta=' . urlencode($cuenta);

            $curl = curl_init();
            curl_setopt_array($curl, [
                CURLOPT_URL => $url,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => '',
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 20,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => 'POST',
                CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
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

            if (isset($decoded['RESULT']['DET']) && is_array($decoded['RESULT']['DET'])) {
                return $decoded['RESULT']['DET'];
            }

            if (isset($decoded['DET']) && is_array($decoded['DET'])) {
                return $decoded['DET'];
            }

            return [];
        });
    }

    private function normalizeKeys(array $array): array
    {
        $normalized = [];

        foreach ($array as $key => $value) {
            $upperKey = strtoupper((string) $key);
            $normalized[$upperKey] = is_array($value)
                ? $this->normalizeKeys($value)
                : $value;
        }

        return $normalized;
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
