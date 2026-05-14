<?php

namespace App\Http\Controllers;

use App\Models\Token;
use Illuminate\Http\Request;
use App\Models\Asistencia;
use App\Models\AsistenciaNet;
use Illuminate\Support\Facades\DB;

class AsistenciaController extends Controller
{
    public function getAsistenciasLotobet(Request $request)
    {
        header('Content-Type: application/json');

        $curl = curl_init();

        $fecha = $request->query('fecha');

        $token = Token::find(1);

        if (!$token) {
            return response()->json(['error' => 'Genere un token'], 404);
        }

        $fechaActual = now();
        if ($fechaActual->greaterThan($token->fecha)) {
            return response()->json(['error' => 'El token ha expirado, genere uno nuevo'], 401);
        }

        curl_setopt_array($curl, array(
            CURLOPT_URL => "https://ltkadapi.lotobet.bet/api/V1/var4XZ3ojQiPZq5BpI/{$token->token}/{$fecha}/05",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
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

        if (!$token) {
            return response()->json(['error' => 'Genere un token'], 404);
        }

        $fechaActual = now();
        if ($fechaActual->greaterThan($token->fecha)) {
            return response()->json(['error' => 'El token ha expirado, genere uno nuevo'], 401);
        }

        $existe = Asistencia::whereDate('fecha', $fecha)->exists();

        if ($existe) {
            return response()->json(['message' => 'Ya hay data guardada en la fecha: ' . $fecha]);
        }

        curl_setopt_array($curl, array(
            CURLOPT_URL => "https://ltkadapi.lotobet.bet/api/V1/var4XZ3ojQiPZq5BpI/{$token->token}/{$fecha}/05",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
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

        curl_close($curl);

        $ventas = json_decode($response, true);

        $data = [];

        if (!empty($ventas['Content'])) {
            foreach ($ventas['Content'] as $v) {
                $data[] = [
                    'consorcio_id'  => $v['consorcio'] ?? null,
                    'agencia_id'    => $v['agencia'] ?? null,
                    'usuario'       => $v['usuario'] ?? null,
                    'cedula'        => $v['cedula'] ?? null,
                    'fecha'         => $v['fecha'] ?? null,
                    'primer_login'  => $v['primer_login'] ?? null,
                    'ultimo_login' => $v['ultimo_logout'] ?? null,
                ];
            }
        }

        if (!empty($data)) {
            foreach (array_chunk($data, 5000) as $chunk) {
                DB::table('asistencias_bet')->insert($chunk);
            }
        }

        return response()->json([
            'message' => 'Datos guardados correctamente. Total insertados: ' . count($data),
            'total' => count($data)
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

    public function getAsistenciasLotonet(Request $request)
    {
        header('Content-Type: application/json');

        $curl = curl_init();

        $fecha = $request->query('fecha');

        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => "http://contable.apploteka.com//api/finan/asistencia_usuarios/{$fecha}/5",
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

        return response()->json(['asistencias' => $data, 'code' => $ventas['code'], 'message' => '']);
    }

    public function saveAsistenciasLotonet(Request $request)
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
            return response()->json(['message' => 'Ya hay data guardada en la fecha: ' . $fecha]);
        }

        curl_setopt_array($curl, array(
            CURLOPT_URL => "http://contable.apploteka.com//api/finan/asistencia_usuarios/{$fecha}/5",
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

        if (!empty($data)) {
            foreach (array_chunk($data, 5000) as $chunk) {
                DB::table('asistencias_net')->insert($chunk);
            }
        }

        return response()->json([
            'message' => 'Datos guardados correctamente. Total insertados: ' . count($data),
            'total' => count($data)
        ]);
    }

    public function deleteAsistenciasLotonet(Request $request)
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
