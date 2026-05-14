<?php

namespace App\Http\Controllers;

use App\Models\MarVentas;
use Carbon\Carbon;
use SoapFault;
use SoapClient;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MarController extends Controller
{
    public function getVentas(Request $request)
    {
        $wsdl = "http://joselito.ddns.net/mar-svr5/mar-export.asmx?WSDL";

        try {
            // Configurar cliente SOAP
            $client = new SoapClient($wsdl, [
                'trace' => true,
                'exceptions' => true,
                'cache_wsdl' => WSDL_CACHE_NONE,
                'stream_context' => stream_context_create([
                    'ssl' => [
                        'verify_peer' => false,
                        'verify_peer_name' => false,
                    ],
                ]),
            ]);

            $fechaLaravel = $request->fecha; // o $request->fecha
            $fechaFormatoSOAP = date('Y-m-d\TH:i:s', strtotime($fechaLaravel));

            // Parámetros del método ResumenContablePorDia
            $params = [
                'Llave' => 'MAR_25523341-2ED6-4A52-AD71-932166CAAC86',
                'FechaCierre' => $fechaFormatoSOAP,
                'PaginaNo' => 1,
                'FilasPorPagina' => 100000,
            ];

            // Llamada al método SOAP
            $response = $client->__soapCall('ResumenContablePorDia', [$params]);

            // Opcional: inspeccionar toda la estructura
            // dd($response);

            // Acceder al resultado principal
            $result = $response->ResumenContablePorDiaResult ?? null;

            $arrayDatos = [];
            if (isset($result->Datos->any)) {
                $rawXml = $result->Datos->any;

                // 💡 Limpiar caracteres especiales o espacios
                $rawXml = trim($rawXml);

                // 💡 Extraer solo el bloque principal si hay más de uno
                // (normalmente Microsoft devuelve dos secciones pegadas)
                if (substr_count($rawXml, '<?xml') > 1) {
                    // Si vienen múltiples XML juntos, tomar el último
                    $pos = strrpos($rawXml, '<?xml');
                    $rawXml = substr($rawXml, $pos);
                }

                // 💡 Si el XML tiene contenido duplicado antes del dataset
                if (strpos($rawXml, '<diffgr:diffgram') !== false) {
                    $pos = strpos($rawXml, '<diffgr:diffgram');
                    $rawXml = substr($rawXml, $pos);
                }

                // Intentar parsear nuevamente
                libxml_use_internal_errors(true);
                $xml = simplexml_load_string($rawXml, "SimpleXMLElement", LIBXML_NOCDATA);
                if (!$xml) {
                    $errors = libxml_get_errors();
                    libxml_clear_errors();
                    return response()->json(['error' => 'XML inválido', 'detalle' => $errors]);
                }

                $json = json_encode($xml);
                $arrayDatos = json_decode($json, true);
            }

            return response()->json([
                'code' => 0,
                'msg' => $result->Mensaje ?? '',
                'pagina' => $result->PaginaNo ?? 0,
                'total_paginas' => $result->TotalPaginas ?? 0,
                'total_filas' => $result->TotalFilas ?? 0,
                'datos' => $arrayDatos['DocumentElement']['ResumenContablePorDia1'] ?? [],
            ]);
        } catch (SoapFault $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function saveVentas(Request $request)
    {
        ini_set('memory_limit', '1G'); // Aumentar límite de memoria si es necesario
        ini_set('max_execution_time', 300); // Aumentar tiempo máximo de ejecución si es necesario

        $wsdl = "http://joselito.ddns.net/mar-svr5/mar-export.asmx?WSDL";

        try {
            // Configurar cliente SOAP
            $client = new SoapClient($wsdl, [
                'trace' => true,
                'exceptions' => true,
                'cache_wsdl' => WSDL_CACHE_NONE,
                'stream_context' => stream_context_create([
                    'ssl' => [
                        'verify_peer' => false,
                        'verify_peer_name' => false,
                    ],
                ]),
            ]);

            $fechaLaravel = $request->fecha; // o $request->fecha
            $fechaFormatoSOAP = date('Y-m-d\TH:i:s', strtotime($fechaLaravel));

            // Parámetros del método ResumenContablePorDia
            $params = [
                'Llave' => 'MAR_25523341-2ED6-4A52-AD71-932166CAAC86',
                'FechaCierre' => $fechaFormatoSOAP,
                'PaginaNo' => 1,
                'FilasPorPagina' => 100000,
            ];

            // Llamada al método SOAP
            $response = $client->__soapCall('ResumenContablePorDia', [$params]);

            // Opcional: inspeccionar toda la estructura
            // dd($response);

            // Acceder al resultado principal
            $result = $response->ResumenContablePorDiaResult ?? null;

            $arrayDatos = [];
            if (isset($result->Datos->any)) {
                $rawXml = $result->Datos->any;

                // 💡 Limpiar caracteres especiales o espacios
                $rawXml = trim($rawXml);

                // 💡 Extraer solo el bloque principal si hay más de uno
                // (normalmente Microsoft devuelve dos secciones pegadas)
                if (substr_count($rawXml, '<?xml') > 1) {
                    // Si vienen múltiples XML juntos, tomar el último
                    $pos = strrpos($rawXml, '<?xml');
                    $rawXml = substr($rawXml, $pos);
                }

                // 💡 Si el XML tiene contenido duplicado antes del dataset
                if (strpos($rawXml, '<diffgr:diffgram') !== false) {
                    $pos = strpos($rawXml, '<diffgr:diffgram');
                    $rawXml = substr($rawXml, $pos);
                }

                // Intentar parsear nuevamente
                libxml_use_internal_errors(true);
                $xml = simplexml_load_string($rawXml, "SimpleXMLElement", LIBXML_NOCDATA);
                if (!$xml) {
                    $errors = libxml_get_errors();
                    libxml_clear_errors();
                    return response()->json(['error' => 'XML inválido', 'detalle' => $errors]);
                }

                $json = json_encode($xml);
                $arrayDatos = json_decode($json, true);
            }

            $existe = MarVentas::whereDate('EDiFecha', $fechaLaravel)->exists();

            if ($existe) {
                return response()->json(['message' => 'Ya hay data guardada en la fecha: ' . $fechaLaravel]);
            }

            $data = $arrayDatos['DocumentElement']['ResumenContablePorDia1'] ?? [];

            if (!empty($data)) {
                foreach (array_chunk($data, 5000) as $chunk) {
                    DB::table('mar_ventas')->insert($chunk);
                }
            }

            return response()->json([
                'code' => 0,
                'message' => 'Datos guardados correctamente',
                'pagina' => $result->PaginaNo ?? 0,
                'total_paginas' => $result->TotalPaginas ?? 0,
                'total_filas' => $result->TotalFilas ?? 0,
                'total' => count($data),
            ]);
        } catch (SoapFault $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function deleteVentas(Request $request)
    {
        header('Content-Type: application/json');

        $fecha = $request->query('fecha');

        MarVentas::whereDate('EDiFecha', $fecha)->delete();

        return response()->json([
            'message' => 'Datos eliminados correctamente',
        ]);
    }

    public function dashboardVentasMar()
    {
        return view('dashboard.mar.ventas');
    }

    public function dashboardVentasMarData(Request $request)
    {
        $fechaInicio = $request->get('fecha_inicio', Carbon::today()->format('Y-m-d'));
        $fechaFin = $request->get('fecha_fin', Carbon::today()->format('Y-m-d'));
        $agenciaId = $request->get('agencia_id');

        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $fechaInicio)) {
            $fechaInicio = Carbon::today()->format('Y-m-d');
        }

        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $fechaFin)) {
            $fechaFin = Carbon::today()->format('Y-m-d');
        }

        $inicio = Carbon::createFromFormat('Y-m-d', $fechaInicio)->startOfDay();
        $fin = Carbon::createFromFormat('Y-m-d', $fechaFin)->endOfDay();

        $rangeStart = $inicio->toDateTimeString();
        $rangeEnd = $fin->toDateTimeString();

        $baseQuery = DB::table('mar_ventas')
            ->whereBetween('EDiFecha', [$rangeStart, $rangeEnd]);

        if ($agenciaId) {
            $baseQuery->where('BancaID', $agenciaId);
        }

        $selectedAgencia = null;
        if ($agenciaId) {
            $selectedAgencia = DB::table('mar_ventas')
                ->selectRaw('BancaID as agencia_id')
                ->selectRaw("COALESCE(MAX(BanNombre), '') as banca")
                ->whereBetween('EDiFecha', [$rangeStart, $rangeEnd])
                ->where('BancaID', $agenciaId)
                ->groupBy('BancaID')
                ->first();
        }

        $aggregates = (clone $baseQuery)
            ->selectRaw('COALESCE(SUM(VTarjetas), 0) as total_recargas')
            ->selectRaw('COALESCE(SUM(VQuinielas + CVPales + CVTripletas), 0) as total_tradicional')
            ->first();

        $totales = [
            'recargas' => (float) ($aggregates->total_recargas ?? 0),
            'tradicional' => (float) ($aggregates->total_tradicional ?? 0),
        ];

        $totalGeneralVentas = array_sum($totales);

        $transPorTipo = (clone $baseQuery)
            ->selectRaw('SUM(CASE WHEN VTarjetas > 0 THEN 1 ELSE 0 END) as trans_recargas')
            ->selectRaw('SUM(CASE WHEN (VQuinielas + CVPales + CVTripletas) > 0 THEN 1 ELSE 0 END) as trans_tradicional')
            ->first();

        $transData = [
            'recargas' => (int) ($transPorTipo->trans_recargas ?? 0),
            'tradicional' => (int) ($transPorTipo->trans_tradicional ?? 0),
        ];

        $transacciones = (clone $baseQuery)->count();
        $totalAgencias = (clone $baseQuery)->distinct()->count('BancaID');
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
                'total' => $totales['recargas'],
                'transacciones' => $transData['recargas'],
                'promedio' => $transData['recargas'] > 0 ? $totales['recargas'] / $transData['recargas'] : 0,
                'porcentaje' => $totalGeneralVentas > 0 ? ($totales['recargas'] / $totalGeneralVentas) * 100 : 0,
            ],
        ];

        $ventasPorDia = (clone $baseQuery)
            ->selectRaw('DATE(EDiFecha) as fecha')
            ->selectRaw('COALESCE(SUM(VQuinielas + CVPales + CVTripletas), 0) as venta_tradicional')
            ->selectRaw('COALESCE(SUM(VTarjetas), 0) as venta_recargas')
            ->selectRaw('COALESCE(SUM(VTarjetas + VQuinielas + CVPales + CVTripletas), 0) as total_general')
            ->groupBy(DB::raw('DATE(EDiFecha)'))
            ->orderBy('fecha')
            ->get();

        $labelsArray = $ventasPorDia->pluck('fecha')->map(fn($fecha) => Carbon::parse($fecha)->format('Y-m-d'))->toArray();

        $chartDiarioLinea = [
            'labels' => $labelsArray,
            'values' => $ventasPorDia->pluck('total_general')->map(fn($value) => (float) $value)->toArray(),
            'promedio_mes_anterior' => 0,
        ];

        $tipoMeta = [
            'tradicional' => ['label' => 'Ventas Tradicionales', 'color' => '#FF6384'],
            'recargas' => ['label' => 'Ventas Recargas', 'color' => '#36A2EB'],
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
                    'label' => $tipoMeta['recargas']['label'],
                    'data' => $ventasPorDia->pluck('venta_recargas')->map(fn($value) => (float) $value)->toArray(),
                    'backgroundColor' => $tipoMeta['recargas']['color'],
                ],
            ],
        ];

        $mesAnteriorInicio = $inicio->copy()->subMonth()->startOfMonth();
        $mesAnteriorFin = $inicio->copy()->subMonth()->endOfMonth();

        $ventasMesAnteriorQuery = DB::table('mar_ventas')
            ->whereBetween('EDiFecha', [$mesAnteriorInicio->toDateTimeString(), $mesAnteriorFin->toDateTimeString()])
            ->selectRaw('DATE(EDiFecha) as fecha')
            ->selectRaw('COALESCE(SUM(VQuinielas + CVPales + CVTripletas), 0) as venta_tradicional')
            ->selectRaw('COALESCE(SUM(VTarjetas), 0) as venta_recargas')
            ->selectRaw('COALESCE(SUM(VTarjetas + VQuinielas + CVPales + CVTripletas), 0) as total_general')
            ->groupBy(DB::raw('DATE(EDiFecha)'));

        if ($agenciaId) {
            $ventasMesAnteriorQuery->where('BancaID', $agenciaId);
        }

        $ventasMesAnterior = $ventasMesAnteriorQuery->orderBy('fecha')->get();
        $conteoMesAnterior = $ventasMesAnterior->count();

        $promedioDiarioMesAnterior = $conteoMesAnterior > 0
            ? $ventasMesAnterior->sum('total_general') / $conteoMesAnterior
            : 0;

        $promediosPorTipoMesAnterior = [
            'tradicional' => $conteoMesAnterior > 0 ? $ventasMesAnterior->sum('venta_tradicional') / $conteoMesAnterior : 0,
            'recargas' => $conteoMesAnterior > 0 ? $ventasMesAnterior->sum('venta_recargas') / $conteoMesAnterior : 0,
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
            $agencias = DB::table('mar_ventas')
                ->selectRaw('BancaID as agencia_id')
                ->selectRaw("COALESCE(MAX(BanNombre), '') as banca")
                ->selectRaw('COALESCE(SUM(VTarjetas + VQuinielas + CVPales + CVTripletas), 0) as total')
                ->whereBetween('EDiFecha', [$rangeStart, $rangeEnd])
                ->groupBy('BancaID')
                ->orderByDesc('total')
                ->get()
                ->map(fn($agencia) => [
                    'agencia_id' => $agencia->agencia_id,
                    'banca' => $agencia->banca,
                    'total' => (float) $agencia->total,
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
            'agencias' => $agencias,
            'agencia' => $selectedAgencia ? [
                'agencia_id' => $selectedAgencia->agencia_id,
                'banca' => $selectedAgencia->banca,
            ] : null,
        ]);
    }
}
