<?php

namespace App\Http\Controllers;

use App\Models\PagoAOtraEmpresa;
use App\Models\PagoAOtraEmpresaNet;
use App\Models\Token;
use App\Support\LotedomRowMapper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PagoAOtraEmpresaController extends Controller
{
    public function getPagosLotobet(Request $request)
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
            CURLOPT_URL => "https://apiadmin.prodrl.lotvirtual.com/api/V1/XCu6kLrhpbrkYOIvt6/{$token->token}/{$fecha}/07",
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

        return response()->json(['pagos' => $ventas['Content'], 'code' => $ventas['code'], 'message' => $ventas['msg']]);
    }

    public function savePagosLotobet(Request $request)
    {
        ini_set('memory_limit', '1G');
        ini_set('max_execution_time', 300); // 300 segundos = 5 minutos
        set_time_limit(300);                // alternativa equivalente
        header('Content-Type: application/json');

        $curl = curl_init();

        $fecha = $request->query('fecha');

        return response()->json(app(\App\Services\Lotobet\LotobetIngestionService::class)->save('pagos_aotra_empresa', $fecha));

        $token = Token::find(1);

        if (! $token) {
            return response()->json(['error' => 'Genere un token'], 404);
        }

        $fechaActual = now();
        if ($fechaActual->greaterThan($token->fecha)) {
            return response()->json(['error' => 'El token ha expirado, genere uno nuevo'], 401);
        }

        $existe = PagoAOtraEmpresa::whereDate('fecha', $fecha)->exists();

        if ($existe) {
            return response()->json(['message' => 'Ya hay data guardada en la fecha: '.$fecha]);
        }

        curl_setopt_array($curl, [
            CURLOPT_URL => "https://apiadmin.prodrl.lotvirtual.com/api/V1/XCu6kLrhpbrkYOIvt6/{$token->token}/{$fecha}/07",
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

        foreach ($ventas['Content'] as $v) {
            $data[] = [
                'agencia_id' => $v['agencia_id'] ?? null,
                'monto' => $v['monto'] ?? null,
                'fecha' => $v['fecha'] ?? null,
                'cedula' => $v['cedula'] ?? $v['identificacion'] ?? null,
                'tipo_pago' => $v['tipo_pago'] ?? $v['plataforma_pago'] ?? null,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        if (! empty($data)) {
            foreach (array_chunk($data, 5000) as $chunk) {
                DB::table('pagos_aotra_empresa_bet')->insert($chunk);
            }
        }

        return response()->json([
            'message' => 'Datos guardados correctamente. Total insertados: '.count($data),
            'total' => count($data),
        ]);
    }

    public function deletePagosLotobet(Request $request)
    {
        header('Content-Type: application/json');

        $fecha = $request->query('fecha');

        PagoAOtraEmpresa::whereDate('fecha', $fecha)->delete();

        return response()->json([
            'message' => 'Datos eliminados correctamente',
        ]);
    }

    public function getPagosLotedom(Request $request)
    {
        header('Content-Type: application/json');

        $curl = curl_init();

        $fecha = $request->query('fecha');

        curl_setopt_array($curl, [
            CURLOPT_URL => "https://lotedom-api.orkapi.net/api/finan/pagos_a_otra_empresa/{$fecha}",
            CURLOPT_PROXY => '',
            CURLOPT_NOPROXY => '*',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'GET',
            CURLOPT_HTTPHEADER => [
                'token: ZFozLWdBYyqERusVdTsW',
                'Cookie: _orkapi_session=mBuhrBXgfS%2BcdU%2FQp3EoRhgkh%2F2Ujit0eiPO0qGqgOqTIzhG5nNCM2X6vcpkMxoqpAHdnVlZQAcH5WBHkFE06UObTZs4b1dT39vDWwjoa2nU%2FaeT9s57iBd0E2cfUF6L730vbmgeCfuFgnzQ%2B7hb0b0hoxUfwen7SzF8j%2BXCERxro57dRZW9iFx3bp7mvo1vbIUIeVdUe7IYMgWT%2FLw2LiyggRKXcp8EMD1YeuTYY%2FPZTXtDjQiE9QqOhbt2vRrRib%2FagV1AcNhLsnKokXm1%2FKTjDblPwonkuCIWhJYncA%3D%3D--u06kmofng8Mclj2V--Q4%2BlvChhFsPrmRval8O09w%3D%3D',
            ],
            CURLOPT_SSL_VERIFYHOST => 0,
            CURLOPT_SSL_VERIFYPEER => 0,
        ]);

        $response = curl_exec($curl);

        curl_close($curl);

        $ventas = json_decode($response, true);
        $data = is_array($ventas) ? ($ventas['data']['result'] ?? []) : [];

        return response()->json(['pagos' => $data, 'code' => $ventas['code'] ?? 0, 'message' => '']);
    }

    public function savePagosLotedom(Request $request)
    {
        ini_set('memory_limit', '1G');
        ini_set('max_execution_time', 300); // 300 segundos = 5 minutos
        set_time_limit(300);                // alternativa equivalente
        header('Content-Type: application/json');

        $curl = curl_init();

        $fecha = $request->query('fecha');

        $existe = PagoAOtraEmpresaNet::whereDate('fecha', $fecha)->exists();

        if ($existe) {
            return response()->json(['message' => 'Ya hay data guardada en la fecha: '.$fecha]);
        }

        curl_setopt_array($curl, [
            CURLOPT_URL => "https://lotedom-api.orkapi.net/api/finan/pagos_a_otra_empresa/{$fecha}",
            CURLOPT_PROXY => '',
            CURLOPT_NOPROXY => '*',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'GET',
            CURLOPT_HTTPHEADER => [
                'token: ZFozLWdBYyqERusVdTsW',
                'Cookie: _orkapi_session=mBuhrBXgfS%2BcdU%2FQp3EoRhgkh%2F2Ujit0eiPO0qGqgOqTIzhG5nNCM2X6vcpkMxoqpAHdnVlZQAcH5WBHkFE06UObTZs4b1dT39vDWwjoa2nU%2FaeT9s57iBd0E2cfUF6L730vbmgeCfuFgnzQ%2B7hb0b0hoxUfwen7SzF8j%2BXCERxro57dRZW9iFx3bp7mvo1vbIUIeVdUe7IYMgWT%2FLw2LiyggRKXcp8EMD1YeuTYY%2FPZTXtDjQiE9QqOhbt2vRrRib%2FagV1AcNhLsnKokXm1%2FKTjDblPwonkuCIWhJYncA%3D%3D--u06kmofng8Mclj2V--Q4%2BlvChhFsPrmRval8O09w%3D%3D',
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
            ], $httpCode >= 400 ? $httpCode : 502);
        }

        $ventas = json_decode($response, true);

        $data = array_map(
            fn (array $row): array => LotedomRowMapper::pago($row, $fecha),
            is_array($ventas) ? ($ventas['data']['result'] ?? []) : []
        );

        if (! empty($data)) {
            foreach (array_chunk($data, 5000) as $chunk) {
                DB::table('pagos_aotra_empresa_net')->insert($chunk);
            }
        }

        return response()->json([
            'message' => 'Datos guardados correctamente. Total insertados: '.count($data),
            'total' => count($data),
        ]);
    }

    public function deletePagosLotedom(Request $request)
    {
        header('Content-Type: application/json');

        $fecha = $request->query('fecha');

        PagoAOtraEmpresaNet::whereDate('fecha', $fecha)->delete();

        return response()->json([
            'message' => 'Datos eliminados correctamente',
        ]);
    }
}
