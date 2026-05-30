<?php

namespace App\Http\Controllers;

use App\Models\Token;
use Illuminate\Http\Request;
use App\Models\PagoMismaEmpresa;
use App\Models\PagoMismaEmpresaNet;
use App\Support\LotonetRowMapper;
use Illuminate\Support\Facades\DB;

class PagoMismaEmpresaController extends Controller
{
    public function getPagosMismaEmpresaLotobet(Request $request)
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
            CURLOPT_URL => "https://apiadmin.prodrl.lotvirtual.com/api/V1/zGt9KSp2k3B87uDbcN/{$token->token}/{$fecha}/07",
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

        return response()->json(['pagos' => $ventas['Content'], 'code' => $ventas['code'], 'message' => $ventas['msg']]);
    }

    public function savePagosMismaEmpresaLotobet(Request $request)
    {
        ini_set('memory_limit', '1G');
        ini_set('max_execution_time', 300); // 300 segundos = 5 minutos
        set_time_limit(300);                // alternativa equivalente
        header('Content-Type: application/json');

        $curl = curl_init();

        $fecha = $request->query('fecha');

        return response()->json(app(\App\Services\Lotobet\LotobetIngestionService::class)->save('pagos_misma_empresa', $fecha));

        $token = Token::find(1);

        if (!$token) {
            return response()->json(['error' => 'Genere un token'], 404);
        }

        $fechaActual = now();
        if ($fechaActual->greaterThan($token->fecha)) {
            return response()->json(['error' => 'El token ha expirado, genere uno nuevo'], 401);
        }

        $existe = PagoMismaEmpresa::whereDate('fecha', $fecha)->exists();

        if ($existe) {
            return response()->json(['message' => 'Ya hay data guardada en la fecha: ' . $fecha]);
        }

        curl_setopt_array($curl, array(
            CURLOPT_URL => "https://apiadmin.prodrl.lotvirtual.com/api/V1/zGt9KSp2k3B87uDbcN/{$token->token}/{$fecha}/07",
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

        foreach ($ventas['Content'] as $v) {
            $data[] = [
                'agencia_id'    => $v['agencia_id'] ?? null,
                'monto'         => $v['monto'] ?? null,
                'fecha'         => $v['fecha'] ?? null,
                'cedula'        => $v['cedula'] ?? $v['identificacion'] ?? null,
                'tipo_pago'     => $v['tipo_pago'] ?? $v['plataforma_pago'] ?? null,
                'created_at'    => now(),
                'updated_at'    => now(),
            ];
        }

        if (!empty($data)) {
            foreach (array_chunk($data, 5000) as $chunk) {
                DB::table('pagos_misma_empresa_bet')->insert($chunk);
            }
        }

        return response()->json([
            'message' => 'Datos guardados correctamente. Total insertados: ' . count($data),
            'total' => count($data)
        ]);
    }

    public function deletePagosMismaEmpresaLotobet(Request $request)
    {
        header('Content-Type: application/json');

        $fecha = $request->query('fecha');

        PagoMismaEmpresa::whereDate('fecha', $fecha)->delete();

        return response()->json([
            'message' => 'Datos eliminados correctamente',
        ]);
    }

    public function getPagosLotonet(Request $request)
    {
        header('Content-Type: application/json');

        $curl = curl_init();

        $fecha = $request->query('fecha');

        curl_setopt_array($curl, array(
            CURLOPT_URL => "http://contable.apploteka.com/api/finan/pagos_empresa/{$fecha}/5",
            CURLOPT_PROXY => '',
            CURLOPT_NOPROXY => '*',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'GET',
            CURLOPT_HTTPHEADER => array(
                'token: ZFozLWdBYyqERusVdTsW',
                'Content-Type: application/json',
                'Cookie: _orkapi_session=ZEhSS1BsNWdUVUo5YS9CTlBZRU5Ialk3MGttb3pPci9wZEsxMWUxNGtUR0hjTWd1NU95cVRXY21jTFdQRWttNmVrOHRQbm5pQUI3T2ZsR1liT0ZqMGZKVGJsQmlxcENESENITEhGRXU5T2h4Z1Y1ekJUMUZYQjB3UGFuSTM1SVVtcVBlTC9WSXYzOVFHVDZWQm10ejVoL1RlNWtvRklVNTEwalRFeHVLV05VPS0tdzJ2SlRSb2NiZW03NGxvelRkK0pXQT09--05d6531100f387106bdeff0762ad421c499d1535'
            ),
        ));

        $response = curl_exec($curl);

        curl_close($curl);

        $ventas = json_decode($response, true);
        $data = array_map(
            fn (array $row): array => LotonetRowMapper::pago($row, $fecha),
            $ventas['data']['result'] ?? []
        );

        return response()->json(['pagos' => $data, 'code' => $ventas['code'] ?? 0, 'message' => '']);
    }

    public function savePagosLotonet(Request $request)
    {
        ini_set('memory_limit', '1G');
        ini_set('max_execution_time', 300); // 300 segundos = 5 minutos
        set_time_limit(300);                // alternativa equivalente
        header('Content-Type: application/json');

        $curl = curl_init();

        $fecha = $request->query('fecha');

        $existe = PagoMismaEmpresaNet::whereDate('fecha', $fecha)->exists();

        if ($existe) {
            return response()->json(['message' => 'Ya hay data guardada en la fecha: ' . $fecha]);
        }

        curl_setopt_array($curl, array(
            CURLOPT_URL => "http://contable.apploteka.com/api/finan/pagos_empresa/{$fecha}/5",
            CURLOPT_PROXY => '',
            CURLOPT_NOPROXY => '*',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'GET',
            CURLOPT_HTTPHEADER => array(
                'token: ZFozLWdBYyqERusVdTsW',
                'Content-Type: application/json',
                'Cookie: _orkapi_session=ZEhSS1BsNWdUVUo5YS9CTlBZRU5Ialk3MGttb3pPci9wZEsxMWUxNGtUR0hjTWd1NU95cVRXY21jTFdQRWttNmVrOHRQbm5pQUI3T2ZsR1liT0ZqMGZKVGJsQmlxcENESENITEhGRXU5T2h4Z1Y1ekJUMUZYQjB3UGFuSTM1SVVtcVBlTC9WSXYzOVFHVDZWQm10ejVoL1RlNWtvRklVNTEwalRFeHVLV05VPS0tdzJ2SlRSb2NiZW03NGxvelRkK0pXQT09--05d6531100f387106bdeff0762ad421c499d1535'
            ),
        ));

        $response = curl_exec($curl);
        $curlError = curl_error($curl);
        $httpCode = (int) curl_getinfo($curl, CURLINFO_HTTP_CODE);

        curl_close($curl);

        if ($response === false || $httpCode >= 400) {
            return response()->json([
                'error' => $curlError !== '' ? $curlError : 'La API de Lotonet Lotedom respondio con HTTP ' . $httpCode . '.',
            ], $httpCode >= 400 ? $httpCode : 502);
        }

        $ventas = json_decode($response, true);

        $data = array_map(
            fn (array $row): array => LotonetRowMapper::pago($row, $fecha),
            is_array($ventas) ? ($ventas['data']['result'] ?? []) : []
        );

        if (!empty($data)) {
            foreach (array_chunk($data, 5000) as $chunk) {
                DB::table('pagos_misma_empresa_net')->insert($chunk);
            }
        }

        return response()->json([
            'message' => 'Datos guardados correctamente. Total insertados: ' . count($data),
            'total' => count($data)
        ]);
    }

    public function deletePagosLotonet(Request $request)
    {
        header('Content-Type: application/json');

        $fecha = $request->query('fecha');

        PagoMismaEmpresaNet::whereDate('fecha', $fecha)->delete();

        return response()->json([
            'message' => 'Datos eliminados correctamente',
        ]);
    }
}
