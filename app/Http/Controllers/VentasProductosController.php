<?php

namespace App\Http\Controllers;

use App\Models\Agencia;
use App\Models\VtProducto;
use App\Models\VtProductoNet;
use App\Services\Etl\LotobetVentasProductoEtlService;
use App\Services\Lotobet\LotobetSessionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class VentasProductosController extends Controller
{
    public function getVentasProductosLotobet(Request $request)
    {
        ini_set('memory_limit', '512M');
        ini_set('max_execution_time', 300);
        set_time_limit(300);

        $fecha = $request->query('fecha');
        $validator = Validator::make(['fecha' => $fecha], [
            'fecha' => ['required', 'date_format:Y-m-d'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'error' => 'Fecha invalida. Use el formato YYYY-MM-DD.',
            ], 422);
        }

        try {
            $ventas = app(LotobetSessionService::class)->getVentasProducto($fecha);
            $contenido = $ventas['Content'] ?? [];
            if (!is_array($contenido)) {
                $contenido = [];
            }
        } catch (\Throwable $e) {
            Log::error('Error consultando API ventas producto Lotobet Real', [
                'fecha' => $fecha,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'ventas' => [],
                'code' => 1,
                'message' => $e->getMessage(),
            ], 502);
        }

        $normalizarClave = static function ($value): string {
            $raw = trim((string) $value);
            if ($raw === '') {
                return '';
            }

            $sinCeros = ltrim($raw, '0');
            return $sinCeros === '' ? '0' : $sinCeros;
        };

        try {
            $agencias = Agencia::query()
                ->leftJoin('ciudades as c', 'c.id', '=', 'agencias.ciudad_id')
                ->leftJoin('ruta_agencia as ra', 'ra.agencia_id', '=', 'agencias.id')
                ->leftJoin('rutas as r', 'r.id', '=', 'ra.ruta_id')
                ->leftJoin('coordinador_operador_agencia as coa', 'coa.agencia_id', '=', 'agencias.id')
                ->leftJoin('coordinadores_operador as co', 'co.id', '=', 'coa.coordinador_operador_id')
                ->select([
                    'agencias.codigo as agencia',
                    'agencias.nombre as nombre_agencia',
                    'agencias.terminal',
                    'agencias.estatus',
                    DB::raw('MAX(c.nombre) as ciudad'),
                    DB::raw('MAX(COALESCE(r.nombre, r.serial)) as ruta'),
                    DB::raw('NULL as operador'),
                    DB::raw('MAX(co.nombre) as coordinador'),
                ])
                ->whereNotNull('agencias.terminal')
                ->groupBy('agencias.id', 'agencias.codigo', 'agencias.nombre', 'agencias.terminal', 'agencias.estatus')
                ->get();
        } catch (\Throwable $e) {
            Log::error('Error enriqueciendo ventas producto Lotobet Real con agencias locales', [
                'fecha' => $fecha,
                'error' => $e->getMessage(),
            ]);

            $agencias = collect();
        }

        $agenciasByTerminal = [];
        $agenciasActivasByTerminal = [];
        $terminalesTablaSet = [];
        foreach ($agencias as $agencia) {
            $terminalRaw = trim((string) ($agencia->terminal ?? ''));
            $terminalNormalizada = $normalizarClave($terminalRaw);
            if ($terminalNormalizada === '') {
                continue;
            }

            $agenciaData = [
                'agencia' => $agencia->agencia,
                'nombre_agencia' => $agencia->nombre_agencia,
                'ciudad' => $agencia->ciudad,
                'ruta' => $agencia->ruta,
                'operador' => $agencia->operador,
                'coordinador' => $agencia->coordinador,
                'estatus' => (int) ($agencia->estatus ?? 0),
            ];

            if (!isset($agenciasByTerminal[$terminalNormalizada])) {
                $agenciasByTerminal[$terminalNormalizada] = $agenciaData;
            }

            $terminalesTablaSet[$terminalNormalizada] = true;

            if ((int) ($agencia->estatus ?? 0) !== 1) {
                continue;
            }

            if (!isset($agenciasActivasByTerminal[$terminalNormalizada])) {
                $agenciasActivasByTerminal[$terminalNormalizada] = [
                    'agencia' => trim((string) ($agencia->agencia ?? '')),
                    'nombre_agencia' => trim((string) ($agencia->nombre_agencia ?? '')),
                    'terminal' => $terminalRaw !== '' ? $terminalRaw : $terminalNormalizada,
                ];
            }
        }

        $terminalesConVentaSet = [];
        foreach ($contenido as $item) {
            $terminal = $normalizarClave($item['agencia_id'] ?? '');
            if ($terminal !== '') {
                $terminalesConVentaSet[$terminal] = true;
            }
        }

        $terminalesNoRegistradasMap = [];
        foreach ($contenido as $item) {
            $terminalRaw = trim((string) ($item['agencia_id'] ?? ''));
            $terminalNormalizada = $normalizarClave($terminalRaw);

            if ($terminalNormalizada === '') {
                continue;
            }

            if (isset($terminalesTablaSet[$terminalNormalizada])) {
                continue;
            }

            if (!isset($terminalesNoRegistradasMap[$terminalNormalizada])) {
                $terminalesNoRegistradasMap[$terminalNormalizada] = $terminalRaw;
            }
        }

        $terminalesNoRegistradas = array_values($terminalesNoRegistradasMap);

        $agenciasActivasConVenta = 0;
        foreach ($agenciasActivasByTerminal as $terminalNormalizada => $_) {
            if (isset($terminalesConVentaSet[$terminalNormalizada])) {
                $agenciasActivasConVenta++;
            }
        }

        $agenciasSinVentasListado = [];
        foreach ($agenciasActivasByTerminal as $terminalNormalizada => $agenciaActivaData) {
            if (isset($terminalesConVentaSet[$terminalNormalizada])) {
                continue;
            }

            $nombreAgencia = $agenciaActivaData['nombre_agencia'] !== ''
                ? $agenciaActivaData['nombre_agencia']
                : ($agenciaActivaData['agencia'] !== '' ? $agenciaActivaData['agencia'] : $agenciaActivaData['terminal']);

            $agenciasSinVentasListado[] = [
                'agencia_id' => $agenciaActivaData['terminal'],
                'nombre_agencia' => $nombreAgencia,
                'terminal' => $agenciaActivaData['terminal'],
            ];
        }

        $totalAgenciasActivas = count($agenciasActivasByTerminal);
        $agenciasActivasSinVenta = max(0, $totalAgenciasActivas - $agenciasActivasConVenta);

        $ventasEnriquecidas = array_map(function ($item) use ($agenciasByTerminal, $normalizarClave) {
            $agenciaId = trim((string) ($item['agencia_id'] ?? ''));
            $agenciaNormalizada = $normalizarClave($agenciaId);

            $agenciaLookup = $agenciasByTerminal[$agenciaNormalizada]
                ?? ['agencia' => null, 'nombre_agencia' => null, 'ciudad' => null, 'ruta' => null, 'operador' => null, 'coordinador' => null, 'estatus' => 0];

            return array_merge($item, $agenciaLookup);
        }, $contenido);

        return response()->json([
            'ventas' => $ventasEnriquecidas,
            'resumen_agencias' => [
                'activas' => $totalAgenciasActivas,
                'con_ventas' => $agenciasActivasConVenta,
                'sin_ventas' => $agenciasActivasSinVenta,
                'agencias_sin_ventas' => $agenciasSinVentasListado,
                'terminales_no_registradas_count' => count($terminalesNoRegistradas),
                'terminales_no_registradas' => $terminalesNoRegistradas,
            ],
            'code' => $ventas['code'] ?? null,
            'message' => $ventas['msg'] ?? null,
        ]);
    }

    public function saveVentasProductosLotobet(Request $request)
    {
        ini_set('memory_limit', '1G');
        ini_set('max_execution_time', 900);
        set_time_limit(900);

        $fecha = $request->query('fecha');
        $validator = Validator::make(['fecha' => $fecha], [
            'fecha' => ['required', 'date_format:Y-m-d'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'error' => 'Fecha invalida. Use el formato YYYY-MM-DD.',
            ], 422);
        }

        try {
            $result = app(LotobetVentasProductoEtlService::class)->run($fecha);

            return response()->json([
                'message' => 'ETL ventas producto Lotobet Real completado.',
                'run_id' => $result['run_id'],
                'total' => $result['inserted'],
                'expected' => $result['expected'],
                'skipped' => $result['skipped'],
                'failed' => $result['failed'],
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'error' => $e->getMessage(),
            ], 502);
        }
    }

    public function deleteVentasProductosLotobet(Request $request)
    {
        header('Content-Type: application/json');

        $fecha = $request->query('fecha');

        VtProducto::whereDate('fecha', $fecha)->delete();

        return response()->json([
            'message' => 'Datos eliminados correctamente',
        ]);
    }

    public function getVentasProductosLotonet(Request $request)
    {
        header('Content-Type: application/json');

        $curl = curl_init();

        $fecha = $request->query('fecha');

        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => "http://contable.apploteka.com//api/finan/ventas_loteria/{$fecha}/5",
            CURLOPT_PROXY => '',
            CURLOPT_NOPROXY => '*',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'GET',
            CURLOPT_POSTFIELDS => '{
                "usuario": {
                    "username": "fjoselito",
                    "password": "mnXd5pSyF3HXjCC4"
                }
            }',
            CURLOPT_HTTPHEADER => array(
                'token: ZFozLWdBYyqERusVdTsW',
                'Content-Type: application/json',
                'Cookie: _orkapi_session=RkZLWFpIMnM1UTdUdjRXVzNuMFRmZFZnQ2U5N0JoV0JaSzBheUFlZ21TSVoyUEhWWFc2Y2R4Nzd2SmVhQXJKOGtsSktHWnNmelgzWGsxcmJESEVkcXRlWW5tdGpzU1ZZcXRBZFNva2lqL3pGMFppZFZnZUxPUXBscWxLYVdVcUwzdURYb1V5bGJwanZkeDdJTGUzZndkV3FxNmtiMjdvNkxpU0ZQK2RWRU1nPS0tbkVwL215TXpYTXpLS1lYYXJTR3Y2UT09--7e272c2a327d71d9feb7996870d828122936b682'
            ),
        ));

        $response = curl_exec($curl);

        curl_close($curl);

        $ventas = json_decode($response, true);

        $data = $ventas['data']['result'] ?? [];

        return response()->json(['ventas' => $data, 'code' => $ventas['code'], 'message' => '']);
    }

    public function saveVentasProductosLotonet(Request $request)
    {
        ini_set('memory_limit', '1G'); // Aumentar el límite de memoria a 512MB
        ini_set('max_execution_time', 300); // 300 segundos = 5 minutos
        set_time_limit(300);                // alternativa equivalente
        header('Content-Type: application/json');

        $curl = curl_init();

        $fecha = $request->query('fecha');

        $existe = VtProductoNet::whereDate('fecha', $fecha)->exists();

        if ($existe) {
            return response()->json(['message' => 'Ya hay data guardada en la fecha: ' . $fecha]);
        }

        curl_setopt_array($curl, array(
            CURLOPT_URL => "http://contable.apploteka.com//api/finan/ventas_loteria/{$fecha}/5",
            CURLOPT_PROXY => '',
            CURLOPT_NOPROXY => '*',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'GET',
            CURLOPT_POSTFIELDS => '{
                "usuario": {
                    "username": "fjoselito",
                    "password": "mnXd5pSyF3HXjCC4"
                }
            }',
            CURLOPT_HTTPHEADER => array(
                'token: ZFozLWdBYyqERusVdTsW',
                'Content-Type: application/json',
                'Cookie: _orkapi_session=RkZLWFpIMnM1UTdUdjRXVzNuMFRmZFZnQ2U5N0JoV0JaSzBheUFlZ21TSVoyUEhWWFc2Y2R4Nzd2SmVhQXJKOGtsSktHWnNmelgzWGsxcmJESEVkcXRlWW5tdGpzU1ZZcXRBZFNva2lqL3pGMFppZFZnZUxPUXBscWxLYVdVcUwzdURYb1V5bGJwanZkeDdJTGUzZndkV3FxNmtiMjdvNkxpU0ZQK2RWRU1nPS0tbkVwL215TXpYTXpLS1lYYXJTR3Y2UT09--7e272c2a327d71d9feb7996870d828122936b682'
            ),
        ));

        $response = curl_exec($curl);

        curl_close($curl);

        $ventas = json_decode($response, true);

        $data = array_map(
            fn (array $row): array => LotonetRowMapper::ventaProducto($row, $fecha),
            $ventas['data']['result'] ?? []
        );

        if (!empty($data)) {
            foreach (array_chunk($data, 5000) as $chunk) {
                DB::table('ventas_producto_net')->insert($chunk);
            }
        }

        return response()->json([
            'message' => 'Datos guardados correctamente. Total insertados: ' . count($data),
            'total' => count($data)
        ]);
    }

    public function deleteVentasProductosLotonet(Request $request)
    {
        header('Content-Type: application/json');

        $fecha = $request->query('fecha');

        VtProductoNet::whereDate('fecha', $fecha)->delete();

        return response()->json([
            'message' => 'Datos eliminados correctamente',
        ]);
    }
}
