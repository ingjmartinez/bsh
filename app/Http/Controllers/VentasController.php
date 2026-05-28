<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\Token;
use App\Models\VtUsuarioBet;
use App\Models\VtUsuarioNet;
use App\Support\InicioVentasCache;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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

        return response()->json(app(\App\Services\Lotobet\LotobetIngestionService::class)->save('ventas_usuarios', $fecha));

        $existe = VtUsuarioBet::whereDate('fecha', $fecha)->exists();

        if ($existe) {
            return response()->json(['message' => 'Ya hay data guardada en la fecha: ' . $fecha]);
        }

        $apiResult = $this->fetchVentasUsuariosLotobetApi($fecha);

        if (!$apiResult['ok']) {
            return response()->json([
                'code' => 1,
                'message' => $apiResult['message'],
            ], $apiResult['status']);
        }

        $data = [];

        if (empty($apiResult['rows'])) {
            return response()->json([
                'message' => 'No hay datos para guardar en la fecha: ' . $fecha,
                'total' => 0,
            ]);
        }

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
                DB::table('ventas_usuarios_bet')->insert($chunk);
            }

            InicioVentasCache::bust();
        }

        return response()->json([
            'message' => 'Datos guardados correctamente. Total insertados: ' . count($data),
            'total' => count($data),
        ]);
    }

    public function deleteVentasUsuariosLotobet(Request $request)
    {
        header('Content-Type: application/json');

        $fecha = $request->query('fecha');

        VtUsuarioBet::whereDate('fecha', $fecha)->delete();
        InicioVentasCache::bust();

        return response()->json([
            'message' => 'Datos eliminados correctamente',
        ]);
    }

    public function getVentasUsuariosLotonet(Request $request)
    {
        header('Content-Type: application/json');

        $fecha = $request->query('fecha');
        $apiResult = $this->fetchVentasUsuariosLotonetApi($fecha);

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

    public function saveVentasUsuariosLotonet(Request $request)
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

        $apiResult = $this->fetchVentasUsuariosLotonetApi($fecha);

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

    public function deleteVentasUsuariosLotonet(Request $request)
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

    private function fetchVentasUsuariosLotonetApi(?string $fecha): array
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
                'message' => $curlError !== '' ? $curlError : 'No se pudo conectar con la API de Lotonet.',
                'rows' => [],
            ];
        }

        $ventas = json_decode($response, true);

        if (!is_array($ventas)) {
            return [
                'ok' => false,
                'status' => 502,
                'message' => 'La API de Lotonet devolvio una respuesta invalida.',
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
                'message' => $message !== '' ? $message : ('La API de Lotonet respondio con HTTP ' . $httpCode . '.'),
                'rows' => [],
            ];
        }

        if (!is_array($rows)) {
            return [
                'ok' => false,
                'status' => 502,
                'message' => $message !== '' ? $message : 'La API de Lotonet no devolvio el listado esperado.',
                'rows' => [],
            ];
        }

        if ($code !== 0 && empty($rows)) {
            return [
                'ok' => false,
                'status' => 422,
                'message' => $message !== '' ? $message : 'La API de Lotonet devolvio un error.',
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
