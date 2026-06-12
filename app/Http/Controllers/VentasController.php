<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\Token;
use App\Models\VtUsuarioBet;
use App\Models\VtUsuarioNet;
use App\Support\InicioVentasCache;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class VentasController extends Controller
{
    private function normalizeCedula($rawCedula): ?string
    {
        $cedula = preg_replace('/\D/', '', (string) $rawCedula);

        if ($cedula === '') {
            return null;
        }

        $cedula = str_pad(substr($cedula, 0, 11), 11, '0', STR_PAD_LEFT);

        if ($cedula === '00000000000') {
            return null;
        }

        return $cedula;
    }

    public function getVentasUsuariosLotobet(Request $request)
    {
        ini_set('memory_limit', '1G');
        ini_set('max_execution_time', 360);
        set_time_limit(360);
        header('Content-Type: application/json');

        $fecha = $request->query('fecha');
        $apiResult = $this->fetchVentasUsuariosLotobetApi($fecha);

        if (!$apiResult['ok']) {
            return response()->json([
                'ventas' => [],
                'code' => 1,
                'message' => $apiResult['message'],
            ], $apiResult['status']);
        }

        return response()->json([
            'ventas' => $apiResult['rows'],
            'code' => 0,
            'message' => $apiResult['message'],
        ]);
    }

    public function saveVentasUsuariosLotobet(Request $request)
    {
        ini_set('memory_limit', '1G');
        ini_set('max_execution_time', 360); // 300 segundos = 5 minutos
        set_time_limit(360);                // alternativa equivalente
        header('Content-Type: application/json');

        $fecha = $request->query('fecha');
        if (empty($fecha)) {
            return response()->json([
                'code' => 1,
                'message' => 'Debe indicar una fecha para guardar la data.',
            ], 422);
        }

        try {
            return response()->json(app(\App\Services\Lotobet\LotobetIngestionService::class)->save('ventas_usuarios', $fecha));
        } catch (\Throwable $e) {
            Log::error('Error guardando ventas por usuario Lotobet', [
                'fecha' => $fecha,
                'message' => $e->getMessage(),
            ]);

            return response()->json([
                'code' => 1,
                'message' => 'No se pudo guardar la data: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function deleteVentasUsuariosLotobet(Request $request)
    {
        header('Content-Type: application/json');

        $fecha = $request->query('fecha');
        $fechaInicio = $request->query('fecha_inicio', $request->query('fechaInicio', $fecha));
        $fechaFin = $request->query('fecha_fin', $request->query('fechaFin', $fechaInicio));

        if (empty($fechaInicio) || empty($fechaFin)) {
            return response()->json([
                'code' => 1,
                'message' => 'Debe indicar una fecha para eliminar la data.',
            ], 422);
        }

        if ($fechaInicio > $fechaFin) {
            return response()->json([
                'code' => 1,
                'message' => 'La fecha inicial no puede ser mayor que la fecha final.',
            ], 422);
        }

        $query = DB::table('ventas_usuarios_bet');
        if ($fechaInicio === $fechaFin) {
            $query->whereDate('fecha', $fechaInicio);
        } else {
            $query->whereBetween('fecha', [$fechaInicio, $fechaFin]);
        }

        $deleted = $query->delete();
        InicioVentasCache::bust();

        return response()->json([
            'message' => 'Datos eliminados correctamente. Total eliminados: ' . $deleted,
            'total' => $deleted,
            'table' => 'ventas_usuarios_bet',
            'fecha_inicio' => $fechaInicio,
            'fecha_fin' => $fechaFin,
        ]);
    }

    public function getVentasUsuariosLotedom(Request $request)
    {
        header('Content-Type: application/json');

        $fecha = $request->query('fecha');
        $apiResult = $this->fetchVentasUsuariosLotedomApi($fecha);

        if (!$apiResult['ok']) {
            return response()->json([
                'ventas' => [],
                'code' => 1,
                'message' => $apiResult['message'],
            ], $apiResult['status']);
        }

        $data = [];

        foreach ($apiResult['rows'] as $v) {
            $data[] = [
                'agencia_id'    => $v['agencia_id'] ?? null,
                'cedula'        => str_replace('-', '', (string) ($v['cedula'] ?? '')),
                'monto'         => $v['monto'] ?? 0,
                'fecha'         => $fecha,
            ];
        }

        return response()->json([
            'ventas' => $data,
            'code' => 0,
            'message' => $apiResult['message'],
        ]);
    }

    public function saveVentasUsuariosLotedom(Request $request)
    {
        ini_set('memory_limit', '1G');
        ini_set('max_execution_time', 360); // 300 segundos = 5 minutos
        set_time_limit(360);                // alternativa equivalente
        header('Content-Type: application/json');

        $fecha = $request->query('fecha');

        $existe = VtUsuarioNet::whereDate('fecha', $fecha)->exists();

        if ($existe) {
            return response()->json(['message' => 'Ya hay data guardada en la fecha: ' . $fecha]);
        }

        $apiResult = $this->fetchVentasUsuariosLotedomApi($fecha);

        if (!$apiResult['ok']) {
            return response()->json([
                'code' => 1,
                'message' => $apiResult['message'],
            ], $apiResult['status']);
        }

        $data = [];

        foreach ($apiResult['rows'] as $v) {
            $data[] = [
                'agencia_id'    => $v['agencia_id'] ?? null,
                'cedula'        => $this->normalizeCedula($v['cedula'] ?? null),
                'monto'         => $v['monto'] ?? 0,
                'fecha'         => $fecha,
                'created_at'    => now(),
                'updated_at'    => now(),
            ];
        }

        if (!empty($data)) {
            foreach (array_chunk($data, 5000) as $chunk) {
                DB::table('ventas_usuarios_net')->insert($chunk);
            }

            InicioVentasCache::bust();
        }

        return response()->json([
            'message' => 'Datos guardados correctamente. Total insertados: ' . count($data),
            'total' => count($data),
        ]);
    }

    public function deleteVentasUsuariosLotedom(Request $request)
    {
        header('Content-Type: application/json');

        $fecha = $request->query('fecha');

        VtUsuarioNet::whereDate('fecha', $fecha)->delete();
        InicioVentasCache::bust();

        return response()->json([
            'message' => 'Datos eliminados correctamente',
        ]);
    }

    private function fetchVentasUsuariosLotobetApi(?string $fecha): array
    {
        $fecha = trim((string) $fecha);

        if ($fecha === '') {
            return [
                'ok' => false,
                'status' => 422,
                'message' => 'Debe indicar una fecha valida.',
                'rows' => [],
            ];
        }

        $token = Token::find(1);

        if (!$token || empty($token->token)) {
            return [
                'ok' => false,
                'status' => 404,
                'message' => 'Genere un token antes de consultar la data.',
                'rows' => [],
            ];
        }

        if (empty($token->fecha) || now()->greaterThan(Carbon::parse($token->fecha))) {
            return [
                'ok' => false,
                'status' => 401,
                'message' => 'El token ha expirado, genere uno nuevo.',
                'rows' => [],
            ];
        }

        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => "https://apiadmin.prodrl.lotvirtual.com/api/V1/EQsEpamN7MuKb0Y7/{$token->token}/{$fecha}/07",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_CONNECTTIMEOUT => 15,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'GET',
            CURLOPT_HTTPHEADER => array(
                'AhfCC: yB0tt5KW3wVVCYYtCpen',
                'AhfVB: xSzdgtOKbGRhUhtv1ois'
            ),
            CURLOPT_SSL_VERIFYHOST => 0,
            CURLOPT_SSL_VERIFYPEER => 0,
        ));

        $response = curl_exec($curl);
        $curlError = curl_error($curl);
        $httpCode = (int) curl_getinfo($curl, CURLINFO_HTTP_CODE);

        curl_close($curl);

        if ($response === false) {
            return [
                'ok' => false,
                'status' => 502,
                'message' => $curlError !== '' ? $curlError : 'No se pudo conectar con la API de Lotobet Real.',
                'rows' => [],
            ];
        }

        $ventas = json_decode($response, true);

        if (!is_array($ventas)) {
            return [
                'ok' => false,
                'status' => 502,
                'message' => 'La API de Lotobet Real devolvio una respuesta invalida.',
                'rows' => [],
            ];
        }

        $rows = $ventas['Content'] ?? [];
        $message = (string) ($ventas['msg'] ?? $ventas['message'] ?? $ventas['error'] ?? '');
        $code = (int) ($ventas['code'] ?? 0);

        if ($httpCode >= 400) {
            return [
                'ok' => false,
                'status' => $httpCode,
                'message' => $message !== '' ? $message : ('La API de Lotobet Real respondio con HTTP ' . $httpCode . '.'),
                'rows' => [],
            ];
        }

        if (!is_array($rows)) {
            return [
                'ok' => false,
                'status' => 502,
                'message' => $message !== '' ? $message : 'La API de Lotobet Real no devolvio el listado esperado.',
                'rows' => [],
            ];
        }

        if ($code !== 0 && empty($rows)) {
            return [
                'ok' => false,
                'status' => 422,
                'message' => $message !== '' ? $message : 'La API de Lotobet Real devolvio un error.',
                'rows' => [],
            ];
        }

        return [
            'ok' => true,
            'status' => 200,
            'message' => $message !== '' ? $message : 'Proceso completado.',
            'rows' => $rows,
        ];
    }

    private function fetchVentasUsuariosLotedomApi(?string $fecha): array
    {
        $fecha = trim((string) $fecha);

        if ($fecha === '') {
            return [
                'ok' => false,
                'status' => 422,
                'message' => 'Debe indicar una fecha valida.',
                'rows' => [],
            ];
        }

        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => "http://contable.apploteka.com//api/finan/ventas_por_usuario/{$fecha}/5",
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
        $curlError = curl_error($curl);
        $httpCode = (int) curl_getinfo($curl, CURLINFO_HTTP_CODE);

        curl_close($curl);

        if ($response === false) {
            return [
                'ok' => false,
                'status' => 502,
                'message' => $curlError !== '' ? $curlError : 'No se pudo conectar con la API de Lotedom.',
                'rows' => [],
            ];
        }

        $ventas = json_decode($response, true);

        if (!is_array($ventas)) {
            return [
                'ok' => false,
                'status' => 502,
                'message' => 'La API de Lotedom devolvio una respuesta invalida.',
                'rows' => [],
            ];
        }

        $rows = data_get($ventas, 'data.result', []);
        $message = (string) ($ventas['message'] ?? $ventas['msg'] ?? $ventas['error'] ?? '');
        $code = (int) ($ventas['code'] ?? 0);

        if ($httpCode >= 400) {
            return [
                'ok' => false,
                'status' => $httpCode,
                'message' => $message !== '' ? $message : ('La API de Lotedom respondio con HTTP ' . $httpCode . '.'),
                'rows' => [],
            ];
        }

        if (!is_array($rows)) {
            return [
                'ok' => false,
                'status' => 502,
                'message' => $message !== '' ? $message : 'La API de Lotedom no devolvio el listado esperado.',
                'rows' => [],
            ];
        }

        if ($code !== 0 && empty($rows)) {
            return [
                'ok' => false,
                'status' => 422,
                'message' => $message !== '' ? $message : 'La API de Lotedom devolvio un error.',
                'rows' => [],
            ];
        }

        return [
            'ok' => true,
            'status' => 200,
            'message' => $message !== '' ? $message : 'Proceso completado.',
            'rows' => $rows,
        ];
    }
}
