<?php

namespace App\Http\Controllers;

use App\Models\Asistencia;
use App\Models\AsistenciaNet;
use App\Models\Token;
use App\Support\LotedomRowMapper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AsistenciaController extends Controller
{
    public function getAsistenciasLotobet(Request $request)
    {
        header('Content-Type: application/json');

        $curl = curl_init();

        $fecha = $request->query('fecha');

        $token = Token::find(1);

        if (! $token) {
            return response()->json(['error' => 'Genere un token'], 404);
        }

        $fechaActual = now();
        if ($fechaActual->greaterThan($token->fecha)) {
            return response()->json(['error' => 'El token ha expirado, genere uno nuevo'], 401);
        }

        curl_setopt_array($curl, [
            CURLOPT_URL => "https://apiadmin.prodrl.lotvirtual.com/api/V1/var4XZ3ojQiPZq5BpI/{$token->token}/{$fecha}/07",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'GET',
            CURLOPT_HTTPHEADER => [
                'AhfCC: yB0tt5KW3wVVCYYtCpen',
                'AhfVB: xSzdgtOKbGRhUhtv1ois',
            ],
            CURLOPT_SSL_VERIFYHOST => 0,
            CURLOPT_SSL_VERIFYPEER => 0,
        ]);

        $response = curl_exec($curl);

        curl_close($curl);

        $ventas = json_decode($response, true);

        return response()->json(['asistencias' => $ventas['Content'], 'code' => $ventas['code'], 'message' => $ventas['msg']]);
    }

    public function saveAsistenciasLotobet(Request $request)
    {
        ini_set('memory_limit', '1G');
        ini_set('max_execution_time', 300); // 300 segundos = 5 minutos
        set_time_limit(300);                // alternativa equivalente
        header('Content-Type: application/json');

        $curl = curl_init();

        $fecha = $request->query('fecha');

        $token = Token::find(1);

        if (! $token) {
            return response()->json(['error' => 'Genere un token'], 404);
        }

        $fechaActual = now();
        if ($fechaActual->greaterThan($token->fecha)) {
            return response()->json(['error' => 'El token ha expirado, genere uno nuevo'], 401);
        }

        $existe = Asistencia::whereDate('fecha', $fecha)->exists();

        if ($existe) {
            return response()->json(['message' => 'Ya hay data guardada en la fecha: '.$fecha]);
        }

        curl_setopt_array($curl, [
            CURLOPT_URL => "https://apiadmin.prodrl.lotvirtual.com/api/V1/var4XZ3ojQiPZq5BpI/{$token->token}/{$fecha}/07",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'GET',
            CURLOPT_HTTPHEADER => [
                'AhfCC: yB0tt5KW3wVVCYYtCpen',
                'AhfVB: xSzdgtOKbGRhUhtv1ois',
            ],
            CURLOPT_SSL_VERIFYHOST => 0,
            CURLOPT_SSL_VERIFYPEER => 0,
        ]);

        $response = curl_exec($curl);

        curl_close($curl);

        $ventas = json_decode($response, true);

        $data = [];

        if (! empty($ventas['Content'])) {
            foreach ($ventas['Content'] as $v) {
                $data[] = [
                    'consorcio_id' => $v['consorcio'] ?? null,
                    'agencia_id' => $v['agencia'] ?? null,
                    'usuario' => $v['usuario'] ?? null,
                    'cedula' => $v['cedula'] ?? null,
                    'fecha' => $v['fecha'] ?? null,
                    'primer_login' => $v['primer_login'] ?? null,
                    'ultimo_login' => $v['ultimo_logout'] ?? null,
                ];
            }
        }

        if (! empty($data)) {
            foreach (array_chunk($data, 5000) as $chunk) {
                DB::table('asistencias_bet')->insert($chunk);
            }
        }

        return response()->json([
            'message' => 'Datos guardados correctamente. Total insertados: '.count($data),
            'total' => count($data),
        ]);
    }

    public function deleteAsistenciasLotobet(Request $request)
    {
        header('Content-Type: application/json');

        $fecha = $request->query('fecha');

        Asistencia::whereDate('fecha', $fecha)->delete();

        return response()->json([
            'message' => 'Datos eliminados correctamente',
        ]);
    }

    public function getAsistenciasLotedom(Request $request)
    {
        header('Content-Type: application/json');

        $curl = curl_init();

        $fecha = $request->query('fecha');

        curl_setopt_array($curl, [
            CURLOPT_URL => "https://lotedom-api.orkapi.net/api/finan/asistencia_usuarios/{$fecha}",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'GET',
            CURLOPT_HTTPHEADER => [
                'token: ZFozLWdBYyqERusVdTsW',
                'Cookie: _orkapi_session=pOlvwaeBapzBuOeprHO383uE04gMCWtYJZdQSy1rZyw989340KoFtCGmOvlw%2BFZP6qu30myqfhh4uFWAvEH%2FYGEuH3Ex7s3cIZ0AFE%2F%2FiwQGeu%2FlveYjYGojxJ9p9F3rYuRRoGXQgsU2ASNvNlOtZrBUdQFG%2BXsJvZAl9wNTS%2Bzr470PpsqLDCO9WuhCm6%2FbVv8CAN%2FRFRsaF2Kj%2BfyH%2BpvHYOZE9VQmk3PR0qZZ2Xew5mbL%2FDD5iUQClG2Yq4a5r10ZQDm9i9l%2FY2ssiIAsn911vR4b%2FHhWVlWEfqff%2BA%3D%3D--sLXbUXCyprfh3hK5--i8FViCpBoxoJKnwl3D%2Bv7g%3D%3D',
            ],
            CURLOPT_SSL_VERIFYHOST => 0,
            CURLOPT_SSL_VERIFYPEER => 0,
        ]);

        $response = curl_exec($curl);
        $curlError = curl_error($curl);
        $httpCode = (int) curl_getinfo($curl, CURLINFO_HTTP_CODE);

        curl_close($curl);

        if ($response === false || $httpCode >= 400) {
            return response()->json([
                'error' => $curlError !== '' ? $curlError : 'La API de Lotedom respondio con HTTP '.$httpCode.'.',
                'message' => 'No fue posible obtener las asistencias de Lotedom.',
            ], $httpCode >= 400 ? $httpCode : 502);
        }

        $ventas = json_decode($response, true);

        if (! is_array($ventas)) {
            return response()->json([
                'error' => 'La API de Lotedom devolvio una respuesta invalida.',
                'message' => 'No fue posible obtener las asistencias de Lotedom.',
            ], 502);
        }

        $code = (string) ($ventas['code'] ?? '');
        if ($code !== '00' && $code !== '0') {
            return response()->json([
                'error' => $ventas['error'] ?? $ventas['message'] ?? 'La API de Lotedom devolvio un error.',
                'message' => $ventas['error'] ?? $ventas['message'] ?? 'La API de Lotedom devolvio un error.',
            ], 502);
        }

        $data = array_map(
            fn (array $row): array => LotedomRowMapper::asistenciaParaConsulta($row, $fecha),
            is_array($ventas['data']['result'] ?? null) ? $ventas['data']['result'] : []
        );

        return response()->json(['asistencias' => $data, 'code' => $code, 'message' => '']);
    }

    public function saveAsistenciasLotedom(Request $request)
    {
        ini_set('memory_limit', '1G'); // Aumentar el límite de memoria a 512MB
        ini_set('max_execution_time', 300); // 300 segundos = 5 minutos
        set_time_limit(300);                // alternativa equivalente
        header('Content-Type: application/json');

        $curl = curl_init();

        $fecha = $request->query('fecha');

        $dateParts = explode('-', $fecha);
        $year = $dateParts[0];
        $month = $dateParts[1];
        $day = $dateParts[2];

        $existe = AsistenciaNet::whereYear('entrada', $year)
            ->whereMonth('entrada', $month)
            ->whereDay('entrada', $day)
            ->exists();

        if ($existe) {
            return response()->json(['message' => 'Ya hay data guardada en la fecha: '.$fecha]);
        }

        curl_setopt_array($curl, [
            CURLOPT_URL => "https://lotedom-api.orkapi.net/api/finan/asistencia_usuarios/{$fecha}",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'GET',
            CURLOPT_HTTPHEADER => [
                'token: ZFozLWdBYyqERusVdTsW',
                'Cookie: _orkapi_session=pOlvwaeBapzBuOeprHO383uE04gMCWtYJZdQSy1rZyw989340KoFtCGmOvlw%2BFZP6qu30myqfhh4uFWAvEH%2FYGEuH3Ex7s3cIZ0AFE%2F%2FiwQGeu%2FlveYjYGojxJ9p9F3rYuRRoGXQgsU2ASNvNlOtZrBUdQFG%2BXsJvZAl9wNTS%2Bzr470PpsqLDCO9WuhCm6%2FbVv8CAN%2FRFRsaF2Kj%2BfyH%2BpvHYOZE9VQmk3PR0qZZ2Xew5mbL%2FDD5iUQClG2Yq4a5r10ZQDm9i9l%2FY2ssiIAsn911vR4b%2FHhWVlWEfqff%2BA%3D%3D--sLXbUXCyprfh3hK5--i8FViCpBoxoJKnwl3D%2Bv7g%3D%3D',
            ],
            CURLOPT_SSL_VERIFYHOST => 0,
            CURLOPT_SSL_VERIFYPEER => 0,
        ]);

        $response = curl_exec($curl);
        $curlError = curl_error($curl);
        $httpCode = (int) curl_getinfo($curl, CURLINFO_HTTP_CODE);

        curl_close($curl);

        if ($response === false || $httpCode >= 400) {
            return response()->json([
                'error' => $curlError !== '' ? $curlError : 'La API de Lotedom respondio con HTTP '.$httpCode.'.',
                'message' => 'No fue posible guardar las asistencias de Lotedom.',
            ], $httpCode >= 400 ? $httpCode : 502);
        }

        $ventas = json_decode($response, true);

        if (! is_array($ventas)) {
            return response()->json([
                'error' => 'La API de Lotedom devolvio una respuesta invalida.',
                'message' => 'No fue posible guardar las asistencias de Lotedom.',
            ], 502);
        }

        $code = (string) ($ventas['code'] ?? '');
        if ($code !== '00' && $code !== '0') {
            return response()->json([
                'error' => $ventas['error'] ?? $ventas['message'] ?? 'La API de Lotedom devolvio un error.',
                'message' => $ventas['error'] ?? $ventas['message'] ?? 'La API de Lotedom devolvio un error.',
            ], 502);
        }

        $data = array_map(
            fn (array $row): array => LotedomRowMapper::asistencia($row, $fecha),
            is_array($ventas['data']['result'] ?? null) ? $ventas['data']['result'] : []
        );

        if (! empty($data)) {
            foreach (array_chunk($data, 5000) as $chunk) {
                DB::table('asistencias_net')->insert($chunk);
            }
        }

        return response()->json([
            'message' => 'Datos guardados correctamente. Total insertados: '.count($data),
            'total' => count($data),
        ]);
    }

    public function deleteAsistenciasLotedom(Request $request)
    {
        header('Content-Type: application/json');

        $fecha = $request->query('fecha');

        $dateParts = explode('-', $fecha);
        $year = $dateParts[0];
        $month = $dateParts[1];
        $day = $dateParts[2];

        AsistenciaNet::whereYear('entrada', $year)
            ->whereMonth('entrada', $month)
            ->whereDay('entrada', $day)
            ->delete();

        return response()->json([
            'message' => 'Datos eliminados correctamente',
        ]);
    }
}
