<?php

namespace App\Http\Controllers;

use App\Models\Agencia;
use App\Models\Token;
use App\Models\VtProducto;
use App\Models\VtProductoNet;
use App\Services\Etl\LotobetVentasProductoEtlService;
use App\Services\Lotobet\LotobetSessionService;
use App\Support\LotonetRowMapper;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class VentasProductosController extends Controller
{
    private const LOTEDOM_TOKEN_ID = 3;

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

        $fecha = $request->query('fecha');

        $validator = Validator::make(['fecha' => $fecha], [
            'fecha' => ['required', 'date_format:Y-m-d'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'ventas' => [],
                'code' => 1,
                'message' => 'Fecha invalida. Use el formato YYYY-MM-DD.',
            ], 422);
        }

        $apiResult = $this->fetchVentasProductosLotonetApi($fecha);

        if (!$apiResult['ok']) {
            return response()->json([
                'ventas' => [],
                'code' => 1,
                'message' => $apiResult['message'],
            ], $apiResult['status']);
        }

        return response()->json(['ventas' => $apiResult['data'], 'code' => 0, 'message' => '']);
    }

    public function saveVentasProductosLotonet(Request $request)
    {
        ini_set('memory_limit', '1G');
        ini_set('max_execution_time', 300);
        set_time_limit(300);
        header('Content-Type: application/json');

        $fecha = $request->query('fecha');

        $validator = Validator::make(['fecha' => $fecha], [
            'fecha' => ['required', 'date_format:Y-m-d'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'error' => 'Fecha invalida. Use el formato YYYY-MM-DD.',
            ], 422);
        }

        $existe = VtProductoNet::whereDate('fecha', $fecha)->exists();

        if ($existe) {
            return response()->json(['message' => 'Ya hay data guardada en la fecha: ' . $fecha]);
        }

        $apiResult = $this->fetchVentasProductosLotonetApi($fecha);

        if (!$apiResult['ok']) {
            return response()->json([
                'error' => $apiResult['message'],
            ], $apiResult['status']);
        }

        $data = array_map(
            fn (array $row): array => LotonetRowMapper::ventaProducto($row, $fecha),
            $apiResult['data']
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

    private function fetchVentasProductosLotonetApi(string $fecha): array
    {
        $token = Token::find(self::LOTEDOM_TOKEN_ID);

        if (!$token || empty($token->token)) {
            return [
                'ok' => false,
                'status' => 404,
                'message' => 'Genere un token Lotedom antes de consultar la data.',
                'data' => [],
            ];
        }

        if (!empty($token->fecha) && now()->greaterThan(Carbon::parse($token->fecha))) {
            return [
                'ok' => false,
                'status' => 401,
                'message' => 'El token Lotedom ha expirado, genere uno nuevo.',
                'data' => [],
            ];
        }

        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => "https://lotedom-api.orkapi.net/api/finan/ventas/{$fecha}",
            CURLOPT_PROXY => '',
            CURLOPT_NOPROXY => '*',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_CONNECTTIMEOUT => 15,
            CURLOPT_TIMEOUT => 60,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'GET',
            CURLOPT_SSL_VERIFYHOST => 0,
            CURLOPT_SSL_VERIFYPEER => 0,
            CURLOPT_HTTPHEADER => array(
                'token: ' . $token->token,
                'Content-Type: application/json',
                'Cookie: _orkapi_session=41Rb84eiSXPUY%2B%2BsGWuZYW7NPs8KCdPfTK2kKFavRpqbz%2B4V6%2F9kIB9sGvSv%2BvxgIh5z09VulnwhGdWrBeeY6gRzgz9hx19936rO4rSzYcx%2Bi7Q2uvcY%2Fxp1yikmFfAhe%2FHPl7EhQhSZtNrrwyAcnJlUSKR2sPzhMqJCnp%2BH1NPoKBce%2BuJsJWrosCAJwBqPj8mJNhA0Kh%2BFeTDDSmRRI7TCMuEzjbKVER49RZ0TItuNypHToFacRQNi%2B8kD0QCOUvZA9Y2E7zFuGV7x7yfw2zTC3%2FQIvuLrC%2FuiuXx5Iw%3D%3D--L7iC0pRfgG8W4Qor--swcVaBH4o3N%2BE7IIf4VF%2BQ%3D%3D',
            ),
        ));

        $response = curl_exec($curl);
        $curlError = curl_error($curl);
        $httpCode = (int) curl_getinfo($curl, CURLINFO_HTTP_CODE);

        curl_close($curl);

        if ($response === false || $curlError !== '') {
            return [
                'ok' => false,
                'status' => 502,
                'message' => $curlError !== '' ? $curlError : 'No se pudo conectar con la API de ventas Lotedom.',
                'data' => [],
            ];
        }

        $ventas = json_decode($response, true);

        if (!is_array($ventas)) {
            return [
                'ok' => false,
                'status' => 502,
                'message' => 'La API de ventas Lotedom devolvio una respuesta invalida.',
                'data' => [],
            ];
        }

        if ($httpCode >= 400) {
            return [
                'ok' => false,
                'status' => $httpCode,
                'message' => data_get($ventas, 'message') ?: data_get($ventas, 'msg') ?: "La API de ventas Lotedom respondio con HTTP {$httpCode}.",
                'data' => [],
            ];
        }

        $data = data_get($ventas, 'data.result')
            ?? data_get($ventas, 'data')
            ?? data_get($ventas, 'result')
            ?? data_get($ventas, 'Content')
            ?? [];

        if (!is_array($data)) {
            $data = [];
        }

        $data = array_map(function ($row) {
            if (!is_array($row)) {
                return [];
            }

            $terminalCodigo = $row['terminal_codigo'] ?? $row['agencia_id'] ?? null;
            $juegoId = $row['juego_id'] ?? $row['producto_id'] ?? null;
            $juegoDesc = $row['juego_desc'] ?? $row['descripcion'] ?? null;
            $montoJugado = $row['monto_jugado'] ?? $row['monto'] ?? null;

            return array_merge($row, [
                'agencia_id' => $terminalCodigo,
                'producto_id' => $juegoId,
                'descripcion' => $juegoDesc,
                'monto' => $montoJugado,
            ]);
        }, $data);

        return [
            'ok' => true,
            'status' => 200,
            'message' => data_get($ventas, 'message') ?: data_get($ventas, 'msg') ?: '',
            'data' => $data,
        ];
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
