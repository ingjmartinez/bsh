<?php

namespace App\Http\Controllers;

use App\Models\Token;
use App\Models\VentasDelta;
use Carbon\Carbon;
use Illuminate\Http\Request;

class VentasDeltaController extends Controller
{
    private const DELTA_TOKEN_ID = 2;
    private const DELTA_API_URL = 'https://bdeltaadapi.lotobet.bet/api/V1/FALUhPLdFAD';
    private const DELTA_API_HEADERS = [
        'AhfCC: VJgej8Mn2yFYNXEr',
        'AhfVB: tnusa4hPNsSbAVPQ',
    ];

    public function ventasDelta()
    {
        return view('lotobet.ventas_delta');
    }

    public function dashboardFlashLotobet()
    {
        return view('dashboard.lotobet.ventas-flash');
    }

    public function ventasFlashLotedom()
    {
        return view('lotedom.ventas-flash');
    }

    public function getVentasDelta(Request $request)
    {
        header('Content-Type: application/json');

        $fecha = $request->query('fecha');

        if (!$this->isValidDate($fecha)) {
            return response()->json(['message' => 'Fecha invalida'], 422);
        }

        $token = $this->getDeltaToken();
        if (!$token) {
            return response()->json(['error' => 'Genere un token'], 404);
        }

        if ($this->tokenExpired($token)) {
            return response()->json(['error' => 'El token ha expirado, genere uno nuevo'], 401);
        }

        $ventas = $this->fetchVentasDelta($fecha, $token->token);

        if ($ventas['code'] !== 0) {
            return response()->json(['ventas' => [], 'code' => $ventas['code'], 'message' => $ventas['msg']], 502);
        }

        return response()->json(['ventas' => $ventas['Content'], 'code' => $ventas['code'], 'message' => $ventas['msg']]);
    }

    public function saveVentasDelta(Request $request)
    {
        ini_set('memory_limit', '1G');
        ini_set('max_execution_time', 360); // 300 segundos = 5 minutos
        set_time_limit(360);                // alternativa equivalente
        header('Content-Type: application/json');

        $fecha = $request->query('fecha');

        if (!$this->isValidDate($fecha)) {
            return response()->json(['message' => 'Fecha invalida'], 422);
        }

        $existe = VentasDelta::query()->whereDate('fecha', $fecha)->exists();

        if ($existe) {
            return response()->json(['message' => 'Ya hay data guardada en la fecha: ' . $fecha]);
        }

        $token = $this->getDeltaToken();
        if (!$token) {
            return response()->json(['message' => 'Genere un token'], 404);
        }

        if ($this->tokenExpired($token)) {
            return response()->json(['message' => 'El token ha expirado, genere uno nuevo'], 401);
        }

        $ventas = $this->fetchVentasDelta($fecha, $token->token);

        if ($ventas['code'] !== 0) {
            return response()->json(['message' => $ventas['msg'], 'code' => $ventas['code']], 502);
        }

        $data = [];

        foreach ($ventas['Content'] as $v) {
            $data[] = [
                'fecha' => $fecha,
                'grupo' => $v['Grupo'] ?? null,
                'banca' => $v['Banca'] ?? null,
                'numero_externo' => $v['NumeroExterno'] ?? null,
                'venta_loteria' => $v['VentaLoteria'] ?? null,
                'comision_loteria' => $v['ComisionLoteria'] ?? null,
                'premios_pagado' => $v['PremiosPagado'] ?? null,
                'venta_recarga' => $v['VentaRecarga'] ?? null,
                'comision_recarga' => $v['ComisionRecarga'] ?? null,
                'ventas_no_tradicional' => $v['VentasNoTrad'] ?? null,
                'premios_pagados_no_tradicional' => $v['PremiosPagadosNoTrad'] ?? null,
                'comision_loterias_lot_no_tradicional' => $v['ComisionLoteriasLotNoTrad'] ?? null,
                'comision_gobierno' => $v['ComisionGobierno'] ?? null,
            ];
        }

        if (!empty($data)) {
            foreach (array_chunk($data, 5000) as $chunk) {
                VentasDelta::query()->insert($chunk);
            }
        }

        return response()->json([
            'message' => 'Datos guardados correctamente. Total insertados: ' . count($data),
            'total' => count($data),
        ]);
    }

    public function deleteVentasDelta(Request $request)
    {
        header('Content-Type: application/json');

        $fecha = $request->query('fecha');

        VentasDelta::query()->whereDate('fecha', $fecha)->delete();

        return response()->json([
            'message' => 'Datos eliminados correctamente',
        ]);
    }

    public function dashboardFlashLotobetData(Request $request)
    {
        $fechaInicio = $request->get('fecha_inicio', Carbon::today()->format('Y-m-d'));
        $fechaFin = $request->get('fecha_fin', Carbon::today()->format('Y-m-d'));
        $agenciaId = $request->get('agencia_id', null);

        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $fechaInicio)) {
            $fechaInicio = Carbon::today()->format('Y-m-d');
        }

        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $fechaFin)) {
            $fechaFin = Carbon::today()->format('Y-m-d');
        }

        $inicio = Carbon::createFromFormat('Y-m-d', $fechaInicio)->startOfDay();
        $fin = Carbon::createFromFormat('Y-m-d', $fechaFin)->endOfDay();

        $rangeStart = $inicio->format('Y-m-d');
        $rangeEnd = $fin->format('Y-m-d');

        $baseQuery = VentasDelta::query()
            ->whereBetween('fecha', [$rangeStart, $rangeEnd]);

        if ($agenciaId) {
            $baseQuery->where('numero_externo', $agenciaId);
        }

        $selectedAgencia = null;
        if ($agenciaId) {
            $selectedAgencia = VentasDelta::query()
                ->selectRaw('numero_externo as agencia_id')
                ->selectRaw("COALESCE(MAX(banca), '') as banca")
                ->whereBetween('fecha', [$rangeStart, $rangeEnd])
                ->where('numero_externo', $agenciaId)
                ->groupBy('numero_externo')
                ->first();
        }

        $aggregates = (clone $baseQuery)
            ->selectRaw('COALESCE(SUM(venta_loteria), 0) as total_tradicional')
            ->selectRaw('COALESCE(SUM(venta_recarga), 0) as total_recarga')
            ->selectRaw('COALESCE(SUM(ventas_no_tradicional), 0) as total_no_tradicional')
            ->selectRaw('COALESCE(SUM(premios_pagado), 0) as total_premios_tradicional')
            ->selectRaw('COALESCE(SUM(premios_pagados_no_tradicional), 0) as total_premios_no_tradicional')
            ->selectRaw('COALESCE(SUM(comision_loteria), 0) as total_comision_loteria')
            ->selectRaw('COALESCE(SUM(comision_recarga), 0) as total_comision_recarga')
            ->selectRaw('COALESCE(SUM(comision_gobierno), 0) as total_comision_gobierno')
            ->first();

        $totales = [
            'tradicional' => (float) ($aggregates->total_tradicional ?? 0),
            'recarga' => (float) ($aggregates->total_recarga ?? 0),
            'no_tradicional' => (float) ($aggregates->total_no_tradicional ?? 0),
        ];

        $totalGeneralVentas = array_sum($totales);

        $transPorTipo = (clone $baseQuery)
            ->selectRaw('SUM(CASE WHEN venta_loteria > 0 THEN 1 ELSE 0 END) as trans_tradicional')
            ->selectRaw('SUM(CASE WHEN venta_recarga > 0 THEN 1 ELSE 0 END) as trans_recarga')
            ->selectRaw('SUM(CASE WHEN ventas_no_tradicional > 0 THEN 1 ELSE 0 END) as trans_no_tradicional')
            ->first();

        $transData = [
            'tradicional' => (int) ($transPorTipo->trans_tradicional ?? 0),
            'recarga' => (int) ($transPorTipo->trans_recarga ?? 0),
            'no_tradicional' => (int) ($transPorTipo->trans_no_tradicional ?? 0),
        ];

        $transacciones = (clone $baseQuery)->count();
        $totalAgencias = (clone $baseQuery)
            ->selectRaw('numero_externo')
            ->selectRaw('COALESCE(SUM(venta_loteria + venta_recarga + ventas_no_tradicional), 0) as total_venta')
            ->groupBy('numero_externo')
            ->havingRaw('total_venta > 0')
            ->count();
        $ticketPromedio = $transacciones > 0 ? $totalGeneralVentas / $transacciones : 0;

        $tablaData = [
            [
                'tipo' => 'Ventas Tradicionales',
                'total' => $totales['tradicional'],
                'transacciones' => $transData['tradicional'],
                'promedio' => $transData['tradicional'] > 0 ? $totales['tradicional'] / $transData['tradicional'] : 0,
                'porcentaje' => $totalGeneralVentas > 0 ? ($totales['tradicional'] / $totalGeneralVentas) * 100 : 0,
            ],
            [
                'tipo' => 'Ventas Recargas',
                'total' => $totales['recarga'],
                'transacciones' => $transData['recarga'],
                'promedio' => $transData['recarga'] > 0 ? $totales['recarga'] / $transData['recarga'] : 0,
                'porcentaje' => $totalGeneralVentas > 0 ? ($totales['recarga'] / $totalGeneralVentas) * 100 : 0,
            ],
            [
                'tipo' => 'Ventas No Tradicionales',
                'total' => $totales['no_tradicional'],
                'transacciones' => $transData['no_tradicional'],
                'promedio' => $transData['no_tradicional'] > 0 ? $totales['no_tradicional'] / $transData['no_tradicional'] : 0,
                'porcentaje' => $totalGeneralVentas > 0 ? ($totales['no_tradicional'] / $totalGeneralVentas) * 100 : 0,
            ],
        ];

        $ventasPorDia = (clone $baseQuery)
            ->selectRaw('fecha')
            ->selectRaw('COALESCE(SUM(venta_loteria), 0) as venta_tradicional')
            ->selectRaw('COALESCE(SUM(venta_recarga), 0) as venta_recarga')
            ->selectRaw('COALESCE(SUM(ventas_no_tradicional), 0) as venta_no_tradicional')
            ->selectRaw('COALESCE(SUM(venta_loteria + venta_recarga + ventas_no_tradicional), 0) as total_general')
            ->groupBy('fecha')
            ->orderBy('fecha')
            ->get();

        $labels = $ventasPorDia->pluck('fecha')->values();
        $labelsArray = $labels->toArray();

        $chartDiarioLinea = [
            'labels' => $labelsArray,
            'values' => $ventasPorDia->pluck('total_general')->map(fn($value) => (float) $value)->toArray(),
            'promedio_mes_anterior' => 0,
        ];

        $tipoMeta = [
            'tradicional' => ['label' => 'Ventas Tradicionales', 'color' => '#FF6384'],
            'recarga' => ['label' => 'Ventas Recargas', 'color' => '#36A2EB'],
            'no_tradicional' => ['label' => 'Ventas No Tradicionales', 'color' => '#FFCE56'],
        ];

        $chartDiarioTipos = [
            'labels' => $labelsArray,
            'datasets' => [
                [
                    'label' => $tipoMeta['tradicional']['label'],
                    'data' => $ventasPorDia->pluck('venta_tradicional')->map(fn($value) => (float) $value)->toArray(),
                    'backgroundColor' => $tipoMeta['tradicional']['color'],
                ],
                [
                    'label' => $tipoMeta['recarga']['label'],
                    'data' => $ventasPorDia->pluck('venta_recarga')->map(fn($value) => (float) $value)->toArray(),
                    'backgroundColor' => $tipoMeta['recarga']['color'],
                ],
                [
                    'label' => $tipoMeta['no_tradicional']['label'],
                    'data' => $ventasPorDia->pluck('venta_no_tradicional')->map(fn($value) => (float) $value)->toArray(),
                    'backgroundColor' => $tipoMeta['no_tradicional']['color'],
                ],
            ],
        ];

        $mesAnteriorInicio = $inicio->copy()->subMonth()->startOfMonth();
        $mesAnteriorFin = $inicio->copy()->subMonth()->endOfMonth();

        $ventasMesAnteriorQuery = VentasDelta::query()
            ->whereBetween('fecha', [$mesAnteriorInicio->format('Y-m-d'), $mesAnteriorFin->format('Y-m-d')])
            ->selectRaw('fecha')
            ->selectRaw('COALESCE(SUM(venta_loteria), 0) as venta_tradicional')
            ->selectRaw('COALESCE(SUM(venta_recarga), 0) as venta_recarga')
            ->selectRaw('COALESCE(SUM(ventas_no_tradicional), 0) as venta_no_tradicional')
            ->selectRaw('COALESCE(SUM(venta_loteria + venta_recarga + ventas_no_tradicional), 0) as total_general')
            ->groupBy('fecha');

        if ($agenciaId) {
            $ventasMesAnteriorQuery->where('numero_externo', $agenciaId);
        }

        $ventasMesAnterior = $ventasMesAnteriorQuery->orderBy('fecha')->get();
        $conteoMesAnterior = $ventasMesAnterior->count();

        $promedioDiarioMesAnterior = $conteoMesAnterior > 0
            ? $ventasMesAnterior->sum('total_general') / $conteoMesAnterior
            : 0;

        $promediosPorTipoMesAnterior = [
            'tradicional' => $conteoMesAnterior > 0 ? $ventasMesAnterior->sum('venta_tradicional') / $conteoMesAnterior : 0,
            'recarga' => $conteoMesAnterior > 0 ? $ventasMesAnterior->sum('venta_recarga') / $conteoMesAnterior : 0,
            'no_tradicional' => $conteoMesAnterior > 0 ? $ventasMesAnterior->sum('venta_no_tradicional') / $conteoMesAnterior : 0,
        ];

        $chartDiarioLinea['promedio_mes_anterior'] = $promedioDiarioMesAnterior;

        foreach ($tipoMeta as $key => $meta) {
            $chartDiarioTipos['datasets'][] = [
                'label' => 'Promedio ' . $meta['label'] . ' Mes Anterior: ' . number_format($promediosPorTipoMesAnterior[$key], 2),
                'data' => array_fill(0, count($labelsArray), round($promediosPorTipoMesAnterior[$key], 2)),
                'type' => 'line',
                'borderColor' => $meta['color'],
                'backgroundColor' => 'rgba(255,255,255,0)',
                'borderWidth' => 2,
                'fill' => false,
                'pointRadius' => 0,
                'tension' => 0,
                'borderDash' => [5, 5],
            ];
        }

        $agencias = [];
        if (!$agenciaId) {
            $agencias = VentasDelta::query()
                ->selectRaw('numero_externo as agencia_id')
                ->selectRaw("COALESCE(MAX(banca), '') as banca")
                ->selectRaw('COALESCE(SUM(venta_loteria + venta_recarga + ventas_no_tradicional), 0) as total')
                ->whereBetween('fecha', [$rangeStart, $rangeEnd])
                ->groupBy('numero_externo')
                ->havingRaw('total > 0')
                ->orderByDesc('total')
                ->get()
                ->map(fn($agencia) => [
                    'agencia_id' => $agencia->agencia_id,
                    'total' => (float) $agencia->total,
                    'banca' => $agencia->banca,
                ])->toArray();
        }

        return response()->json([
            'kpis' => [
                'total' => $totalGeneralVentas,
                'transacciones' => $transacciones,
                'ticket_promedio' => $ticketPromedio,
                'total_agencias' => $totalAgencias,
            ],
            'chart_diario' => $chartDiarioLinea,
            'chart_diario_tipos' => $chartDiarioTipos,
            'tabla' => $tablaData,
            'agencias' => $agenciaId ? [] : $agencias,
            'agencia' => $selectedAgencia ? [
                'agencia_id' => $selectedAgencia->agencia_id,
                'banca' => $selectedAgencia->banca,
            ] : null,
        ]);
    }

    private function fetchVentasDelta(string $fecha, string $token): array
    {
        $curl = curl_init();

        curl_setopt_array($curl, [
            CURLOPT_URL => self::DELTA_API_URL . "/{$token}/{$fecha}/{$fecha}",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'GET',
            CURLOPT_HTTPHEADER => self::DELTA_API_HEADERS,
            CURLOPT_SSL_VERIFYHOST => 0,
            CURLOPT_SSL_VERIFYPEER => 0,
        ]);

        $response = curl_exec($curl);

        if ($response === false) {
            $error = curl_error($curl);
            curl_close($curl);

            return [
                'code' => 1,
                'msg' => 'Error consultando API Delta: ' . $error,
                'Content' => [],
            ];
        }

        curl_close($curl);

        $ventas = json_decode($response, true);

        if (!is_array($ventas)) {
            return [
                'code' => 1,
                'msg' => 'La API Delta no devolvio un JSON valido',
                'Content' => [],
            ];
        }

        return [
            'Content' => $ventas['Content'] ?? $ventas['content'] ?? (array_is_list($ventas) ? $ventas : []),
            'code' => (int) ($ventas['code'] ?? 0),
            'msg' => $ventas['msg'] ?? $ventas['message'] ?? 'Datos obtenidos correctamente',
        ];
    }

    private function isValidDate(?string $date): bool
    {
        if (!$date || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
            return false;
        }

        try {
            return Carbon::createFromFormat('Y-m-d', $date)->format('Y-m-d') === $date;
        } catch (\Throwable $e) {
            return false;
        }
    }

    private function getDeltaToken(): ?Token
    {
        $token = Token::find(self::DELTA_TOKEN_ID);

        if (!$token || empty($token->token)) {
            return null;
        }

        return $token;
    }

    private function tokenExpired(Token $token): bool
    {
        if (empty($token->fecha)) {
            return true;
        }

        return now()->greaterThan(Carbon::parse($token->fecha));
    }
}
