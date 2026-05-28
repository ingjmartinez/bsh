<?php

namespace App\Http\Controllers;

use App\Models\FaltantesBet;
use App\Models\FaltantesNet;
use App\Models\Token;
use App\Support\LotonetRowMapper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class FaltantesController extends Controller
{
    public function getFaltantesLotobet(Request $request)
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
            CURLOPT_URL => "https://apiadmin.prodrl.lotvirtual.com/api/V1/qmLJoQxThPKErmLtEG/{$token->token}/{$fecha}/07",
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

        $faltantes = json_decode($response, true);

        return response()->json(['faltantes' => $faltantes['Content'], 'code' => $faltantes['code'], 'message' => $faltantes['msg']]);
    }

    public function saveFaltantesLotobet(Request $request)
    {
        ini_set('memory_limit', '1G'); // 300 segundos = 5 minutos
        ini_set('max_execution_time', 300); // 300 segundos = 5 minutos
        set_time_limit(300);                // alternativa equivalente
        header('Content-Type: application/json');

        $curl = curl_init();

        $fecha = $request->query('fecha');

        return response()->json(app(\App\Services\Lotobet\LotobetIngestionService::class)->save('faltantes', $fecha));

        $token = Token::find(1);

        if (!$token) {
            return response()->json(['error' => 'Genere un token'], 404);
        }

        $fechaActual = now();
        if ($fechaActual->greaterThan($token->fecha)) {
            return response()->json(['error' => 'El token ha expirado, genere uno nuevo'], 401);
        }

        $existe = FaltantesBet::whereDate('fecha', $fecha)->exists();

        if ($existe) {
            return response()->json(['message' => 'Ya hay data guardada en la fecha: ' . $fecha]);
        }

        curl_setopt_array($curl, array(
            CURLOPT_URL => "https://apiadmin.prodrl.lotvirtual.com/api/V1/qmLJoQxThPKErmLtEG/{$token->token}/{$fecha}/07",
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

        $faltantes = json_decode($response, true);

        $data = [];

        foreach ($faltantes['Content'] as $v) {
            $data[] = [
                'agencia_id'    => $v['agencia_id'] ?? null,
                'monto'         => $v['monto'] ?? 0,
                'fecha'         => $v['fecha'],
                'motivo'        => $v['motivo'] ?? null,
                'observacion'   => $v['descripcion'] ?? $v['observacion'] ?? $v['identificacion'] ?? null,
                'created_at'    => now(),
                'updated_at'    => now(),
            ];
        }

        if (!empty($data)) {
            foreach (array_chunk($data, 5000) as $chunk) {
                DB::table('faltantes_bet')->insert($chunk);
            }
        }

        return response()->json([
            'message' => 'Datos guardados correctamente. Total insertados: ' . count($data),
            'total' => count($data)
        ]);
    }

    public function deleteFaltantesLotobet(Request $request)
    {
        header('Content-Type: application/json');

        $fecha = $request->query('fecha');

        FaltantesBet::whereDate('fecha', $fecha)->delete();

        return response()->json([
            'message' => 'Datos eliminados correctamente',
        ]);
    }

    public function getFaltantesLotonet(Request $request)
    {
        header('Content-Type: application/json');

        $curl = curl_init();

        $fecha = $request->query('fecha');

        curl_setopt_array($curl, array(
            CURLOPT_URL => "http://contable.apploteka.com/api/finan/faltantes_usuario/{$fecha}",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'GET',
            CURLOPT_POSTFIELDS => '{
                "usuario": {
                    "username": "fcolombo",
                    "password": "RUHTe9t9ZEUzHsyT"
                }
            }',
            CURLOPT_HTTPHEADER => array(
                'token: ZFozLWdBYyqERusVdTsW',
                'Content-Type: application/json',
                'Cookie: _orkapi_session=M1lKREtNTWd5OEZsNFcyZllpbWtOYURCRFRmTWFZd2FDRC9uMWFocmt1UGpwUzZpUE9qY0xYckV3M1FsS2JaTEFXRXdqaHNmMk9SdTZBRURTNTRIeUZEQXlnQ2I0d2JtcXlvZGNoVzlLME1wYkl6NFRhRk5MMTlFRlFpbUs0YWxYVk5aUFUyOThScjcxZjZ1eW9LRU9wOVJ4K21MTTMvR1k4TTQvSmVmQm5jPS0tU01PcEJxSG5GOWg5bHVRR042a3pZUT09--6858f845c423353e929bdc8cde65e15e2793b82c'
            ),
        ));

        $response = curl_exec($curl);

        curl_close($curl);

        $faltantes = json_decode($response, true);

        $data = [];

        foreach (($faltantes['data']['result'] ?? []) as $v) {
            $data[] = LotonetRowMapper::faltante($v, $fecha);
        }

        return response()->json(['faltantes' => $data, 'code' => $faltantes['code'], 'message' => 'Resultas obtenidos correctamente']);
    }

    public function saveFaltantesLotonet(Request $request)
    {
        ini_set('memory_limit', '1G'); // 300 segundos = 5 minutos
        ini_set('max_execution_time', 300); // 300 segundos = 5 minutos
        set_time_limit(300);                // alternativa equivalente
        header('Content-Type: application/json');

        $curl = curl_init();

        $fecha = $request->query('fecha');

        $existe = FaltantesNet::whereDate('fecha', $fecha)->exists();

        if ($existe) {
            return response()->json(['message' => 'Ya hay data guardada en la fecha: ' . $fecha]);
        }

        curl_setopt_array($curl, array(
            CURLOPT_URL => "http://contable.apploteka.com/api/finan/faltantes_usuario/{$fecha}",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'GET',
            CURLOPT_POSTFIELDS => '{
                "usuario": {
                    "username": "fcolombo",
                    "password": "RUHTe9t9ZEUzHsyT"
                }
            }',
            CURLOPT_HTTPHEADER => array(
                'token: ZFozLWdBYyqERusVdTsW',
                'Content-Type: application/json',
                'Cookie: _orkapi_session=M1lKREtNTWd5OEZsNFcyZllpbWtOYURCRFRmTWFZd2FDRC9uMWFocmt1UGpwUzZpUE9qY0xYckV3M1FsS2JaTEFXRXdqaHNmMk9SdTZBRURTNTRIeUZEQXlnQ2I0d2JtcXlvZGNoVzlLME1wYkl6NFRhRk5MMTlFRlFpbUs0YWxYVk5aUFUyOThScjcxZjZ1eW9LRU9wOVJ4K21MTTMvR1k4TTQvSmVmQm5jPS0tU01PcEJxSG5GOWg5bHVRR042a3pZUT09--6858f845c423353e929bdc8cde65e15e2793b82c'
            ),
        ));

        $response = curl_exec($curl);

        curl_close($curl);

        $faltantes = json_decode($response, true);

        $data = [];

        foreach (($faltantes['data']['result'] ?? []) as $v) {
            $data[] = LotonetRowMapper::faltante($v, $fecha);
        }

        if (!empty($data)) {
            foreach (array_chunk($data, 5000) as $chunk) {
                DB::table('faltantes_net')->insert($chunk);
            }
        }

        return response()->json([
            'message' => 'Datos guardados correctamente. Total insertados: ' . count($data),
            'total' => count($data)
        ]);
    }

    public function deleteFaltantesLotonet(Request $request)
    {
        header('Content-Type: application/json');

        $fecha = $request->query('fecha');

        FaltantesNet::whereDate('fecha', $fecha)->delete();

        return response()->json([
            'message' => 'Datos eliminados correctamente',
        ]);
    }
}
