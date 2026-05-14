<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class ContabilidadFlujoRutaController extends Controller
{
    private const MAX_LLAMADAS_API_EN_VIVO = 1000;
    private const CUENTA_CONTROL_DEBITO = '10013';
    private const CUENTA_CONTROL_CREDITO = '10021';

    public function index()
    {
        return view('contabilidad.reportes.flujo_ruta', [
            'tipos' => collect(['Activo']),
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
        $mapaBloqueTipo = [
            'activo' => 'Activo',
            'ingreso' => 'Ingreso',
            'costo' => 'Costo',
            'gasto' => 'Gasto',
        ];

        $conteoPorTipo = $cuentas
            ->groupBy(fn ($cuenta) => (string) ($mapaBloqueTipo[$cuenta['bloque'] ?? ''] ?? ''))
            ->map(fn ($items) => $items->count())
            ->filter(fn ($count, $tipo) => trim((string) $tipo) !== '')
            ->all();

        return response()->json([
            'total_cuentas' => $cuentas->count(),
            'conteo_por_tipo' => $conteoPorTipo,
        ]);
    }

    private function obtenerCuentasPorTipo(Collection $tiposSeleccionados): Collection
    {
        return DB::table('cuentas_contables')
            ->select('cuenta', 'descripcion', 'tipo')
            ->where(function ($query) {
                $query->where('cuenta', 'like', self::CUENTA_CONTROL_DEBITO . '%')
                    ->orWhere('cuenta', 'like', self::CUENTA_CONTROL_CREDITO . '%');
            })
            ->orderByRaw(
                "CASE
                    WHEN cuenta LIKE ? THEN 0
                    WHEN cuenta LIKE ? THEN 1
                    ELSE 2
                END",
                [self::CUENTA_CONTROL_CREDITO . '%', self::CUENTA_CONTROL_DEBITO . '%']
            )
            ->orderBy('cuenta')
            ->get()
            ->map(function ($cuenta) {
                $numeroCuenta = trim((string) $cuenta->cuenta);
                $tipoCuenta = trim((string) $cuenta->tipo);

                return [
                    'cuenta' => $numeroCuenta,
                    'descripcion' => trim((string) $cuenta->descripcion),
                    'tipo' => $tipoCuenta,
                    'bloque' => 'activo',
                ];
            })
            ->values();
    }

    private function construirReportePorBloqueDesdeApi(Collection $cuentas, Carbon $desde, Carbon $hasta): array
    {
        $gruposBase = [
            'activo' => [
                'titulo' => 'Activos',
                'orden' => 1,
                'cuentas' => [],
                'total_debito' => 0.0,
                'total_credito' => 0.0,
                'total_periodo' => 0.0,
                'total_credito_columna' => 0.0,
            ],
        ];

        $totalCuenta10013 = 0.0;
        $totalCuenta10021 = 0.0;

        foreach ($cuentas as $cuenta) {
            $detalle = $this->sumarDetalleCuentaPorRango($cuenta, $desde, $hasta);
            $monto = (float) ($detalle['monto'] ?? 0);
            $debito = (float) ($detalle['debito'] ?? 0);
            $credito = (float) ($detalle['credito'] ?? 0);

            $esCuenta10013 = str_starts_with($cuenta['cuenta'], self::CUENTA_CONTROL_DEBITO);
            $esCuenta10021 = str_starts_with($cuenta['cuenta'], self::CUENTA_CONTROL_CREDITO);
            $debitoColumna = $esCuenta10013 ? $debito : 0.0;
            $creditoColumna = $esCuenta10021 ? $credito : 0.0;

            // Mantener una sola fuente de verdad: solo se acumula lo que realmente se renderiza en la tabla.
            if (abs($debitoColumna) < 0.00001 && abs($creditoColumna) < 0.00001) {
                continue;
            }

            if ($esCuenta10013) {
                $totalCuenta10013 += $debitoColumna;
            }

            if ($esCuenta10021) {
                $totalCuenta10021 += $creditoColumna;
            }

            $gruposBase[$cuenta['bloque']]['cuentas'][] = [
                'cuenta' => $cuenta['cuenta'],
                'descripcion' => $cuenta['descripcion'],
                'tipo' => $cuenta['tipo'],
                'debito' => round($debito, 2),
                'credito' => round($credito, 2),
                'debito_columna' => round($debitoColumna, 2),
                'periodo' => round($debitoColumna, 2),
                'credito_columna' => round($creditoColumna, 2),
                'acumulado' => round($debitoColumna, 2),
            ];

            $gruposBase[$cuenta['bloque']]['total_debito'] += $debito;
            $gruposBase[$cuenta['bloque']]['total_credito'] += $credito;
            $gruposBase[$cuenta['bloque']]['total_periodo'] += $debitoColumna;
            $gruposBase[$cuenta['bloque']]['total_credito_columna'] += $creditoColumna;
        }

        $grupos = collect($gruposBase)
            ->filter(fn ($grupo) => !empty($grupo['cuentas']))
            ->sortBy('orden')
            ->map(function ($grupo) {
                $grupo['total_debito'] = round((float) ($grupo['total_debito'] ?? 0), 2);
                $grupo['total_credito'] = round((float) ($grupo['total_credito'] ?? 0), 2);
                $grupo['total_periodo'] = round($grupo['total_periodo'], 2);
                $grupo['total_credito_columna'] = round((float) ($grupo['total_credito_columna'] ?? 0), 2);
                $grupo['total_acumulado'] = round($grupo['total_periodo'], 2);
                return $grupo;
            })
            ->values()
            ->all();

        $balance = $totalCuenta10013 - $totalCuenta10021;
        $creditosBancos = collect($grupos)->sum(function ($grupo) {
            return (float) ($grupo['total_credito_columna'] ?? 0);
        });

        return [
            'periodo_texto' => $desde->format('d/m/Y') . ' hasta ' . $hasta->format('d/m/Y'),
            'columnas' => [
                'periodo' => 'Debito',
                'credito' => 'Credito',
                'acumulado' => 'Acumulado rango',
            ],
            'grupos' => $grupos,
            'resumen' => [
                'activos_periodo' => round($totalCuenta10013, 2),
                'activos_acumulado' => round($totalCuenta10013, 2),
                'cuenta_10021_periodo' => round($totalCuenta10021, 2),
                'cuenta_10021_acumulado' => round($totalCuenta10021, 2),
                'balance_periodo' => round($balance, 2),
                'balance_acumulado' => round($balance, 2),
                'creditos_bancos_periodo' => round($creditosBancos, 2),
                'creditos_bancos_acumulado' => round($creditosBancos, 2),
            ],
        ];
    }

    private function respuestaVacia(Carbon $desde, Carbon $hasta): array
    {
        return [
            'periodo_texto' => $desde->format('d/m/Y') . ' hasta ' . $hasta->format('d/m/Y'),
            'columnas' => [
                'periodo' => 'Debito',
                'credito' => 'Credito',
                'acumulado' => 'Acumulado rango',
            ],
            'grupos' => [],
            'resumen' => [
                'activos_periodo' => 0,
                'activos_acumulado' => 0,
                'cuenta_10021_periodo' => 0,
                'cuenta_10021_acumulado' => 0,
                'balance_periodo' => 0,
                'balance_acumulado' => 0,
                'creditos_bancos_periodo' => 0,
                'creditos_bancos_acumulado' => 0,
            ],
        ];
    }

    private function resolverBloque(string $tipo): string
    {
        $normalizado = mb_strtolower(trim($tipo));

        if (str_contains($normalizado, 'activo')) {
            return 'activo';
        }

        if (str_contains($normalizado, 'ingreso')) {
            return 'ingreso';
        }

        if (str_contains($normalizado, 'costo')) {
            return 'costo';
        }

        return 'gasto';
    }

    private function sumarDetalleCuentaPorRango(array $cuenta, Carbon $desde, Carbon $hasta): array
    {
        $debitoTotal = 0.0;
        $creditoTotal = 0.0;
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
                $debitoTotal += $debito;
                $creditoTotal += $credito;

                if (($cuenta['bloque'] ?? '') === 'ingreso') {
                    // Para ingresos se mantiene la convención contable previa.
                } else {
                    // Para activos/costos/gastos se mantiene la convención contable previa.
                }
            }

            unset($items);
            $fechaActual->addDay();
        }

        $monto = (($cuenta['bloque'] ?? '') === 'ingreso')
            ? ($creditoTotal - $debitoTotal)
            : ($debitoTotal - $creditoTotal);

        return [
            'debito' => $debitoTotal,
            'credito' => $creditoTotal,
            'monto' => $monto,
        ];
    }

    private function fetchDetalleByDate(string $cuenta, string $fecha): array
    {
        $cacheKey = 'flujo_ruta_api:' . sha1($cuenta . '|' . $fecha);

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
