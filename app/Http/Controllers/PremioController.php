<?php

namespace App\Http\Controllers;

use App\Models\Token;
use App\Models\Premio;
use App\Models\PremioNet;
use App\Support\LotonetRowMapper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PremioController extends Controller
{
    public function getPremiosLotobet(Request $request)
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
            CURLOPT_URL => "https://apiadmin.prodrl.lotvirtual.com/api/V1/YhJ23fkZyVNDVy4ilB/{$token->token}/{$fecha}/07",
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

        return response()->json(['premios' => $ventas['Content'], 'code' => $ventas['code'], 'message' => $ventas['msg']]);
    }

    public function savePremiosLotobet(Request $request)
    {
        ini_set('memory_limit', '1G');
        ini_set('max_execution_time', 300); // 300 segundos = 5 minutos
        set_time_limit(300);                // alternativa equivalente
        header('Content-Type: application/json');

        $curl = curl_init();

        $fecha = $request->query('fecha');

        return response()->json(app(\App\Services\Lotobet\LotobetIngestionService::class)->save('premios', $fecha));

        $token = Token::find(1);

        if (!$token) {
            return response()->json(['error' => 'Genere un token'], 404);
        }

        $fechaActual = now();
        if ($fechaActual->greaterThan($token->fecha)) {
            return response()->json(['error' => 'El token ha expirado, genere uno nuevo'], 401);
        }

        $existe = Premio::whereDate('fecha', $fecha)->exists();

        if ($existe) {
            return response()->json(['message' => 'Ya hay data guardada en la fecha: ' . $fecha]);
        }

        curl_setopt_array($curl, array(
            CURLOPT_URL => "https://apiadmin.prodrl.lotvirtual.com/api/V1/YhJ23fkZyVNDVy4ilB/{$token->token}/{$fecha}/07",
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
                'producto_id'   => $v['producto_id'] ?? null,
                'monto'         => $v['monto'] ?? null,
                'fecha'         => $v['fecha'] ?? null,
                'cedula'        => $v['cedula'] ?? $v['identificacion'] ?? null,
                'sorteo_id'     => $v['sorteo_id'] ?? null,
                'created_at'    => now(),
                'updated_at'    => now(),
            ];
        }

        if (!empty($data)) {
            foreach (array_chunk($data, 5000) as $chunk) {
                DB::table('premios_bet')->insert($chunk);
            }
        }

        return response()->json([
            'message' => 'Datos guardados correctamente. Total insertados: ' . count($data),
            'total'   => count($data)
        ]);
    }

    public function deletePremiosLotobet(Request $request)
    {
        header('Content-Type: application/json');

        $fecha = $request->query('fecha');

        Premio::whereDate('fecha', $fecha)->delete();

        return response()->json([
            'message' => 'Datos eliminados correctamente',
        ]);
    }

    public function getPremiosLotonet(Request $request)
    {
        header('Content-Type: application/json');

        $curl = curl_init();

        $fecha = $request->query('fecha');

        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => "http://contable.apploteka.com/api/finan/premios/{$fecha}/5",
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
                'Cookie: _orkapi_session=QkViaFBzMmJPTEU0U3YxWEEyd0k4eVZuR2RkTFV2bktWY0srZ2NyaWc1Y2J1eGhhdTRxZXZ3VDByTG9vT3VFL0ZpTlNvalgzK3dOcG5EZGNHTDAxbE5OMGU3dUFzaHYxYVlkSzhFc241eE52YXpaaHNOcmFtbUVPdnVTSUZ1L1A3UEVoSDhtV3QvUVZJUy9USU45WUU4OU03SUUxZ0JjQXNVUFBRY2Z6VlFRPS0tc1ZQNDA1NExkWldOTDluU2lLVzhLdz09--384f330e993c1c076f324f7ed51ee9439ccf2a85'
            ),
        ));

        $response = curl_exec($curl);

        curl_close($curl);

        $items = json_decode($response, true);

        $data = $items['data']['result'] ?? [];

        return response()->json(['premios' => $data, 'code' => $items['code'], 'message' => '']);
    }

    public function savePremiosLotonet(Request $request)
    {
        ini_set('memory_limit', '1G');
        ini_set('max_execution_time', 300); // 300 segundos = 5 minutos
        set_time_limit(300);                // alternativa equivalente
        header('Content-Type: application/json');

        $curl = curl_init();

        $fecha = $request->query('fecha');

        $existe = PremioNet::whereDate('fecha', $fecha)->exists();

        if ($existe) {
            return response()->json(['message' => 'Ya hay data guardada en la fecha: ' . $fecha]);
        }

        curl_setopt_array($curl, array(
            CURLOPT_URL => "http://contable.apploteka.com/api/finan/premios/{$fecha}/5",
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
                'Cookie: _orkapi_session=QkViaFBzMmJPTEU0U3YxWEEyd0k4eVZuR2RkTFV2bktWY0srZ2NyaWc1Y2J1eGhhdTRxZXZ3VDByTG9vT3VFL0ZpTlNvalgzK3dOcG5EZGNHTDAxbE5OMGU3dUFzaHYxYVlkSzhFc241eE52YXpaaHNOcmFtbUVPdnVTSUZ1L1A3UEVoSDhtV3QvUVZJUy9USU45WUU4OU03SUUxZ0JjQXNVUFBRY2Z6VlFRPS0tc1ZQNDA1NExkWldOTDluU2lLVzhLdz09--384f330e993c1c076f324f7ed51ee9439ccf2a85'
            ),
        ));

        $response = curl_exec($curl);

        curl_close($curl);

        $items = json_decode($response, true);

        $data = array_map(
            fn (array $row): array => LotonetRowMapper::premio($row, $fecha),
            $items['data']['result'] ?? []
        );

        if (!empty($data)) {
            foreach (array_chunk($data, 5000) as $chunk) {
                DB::table('premios_net')->insert($chunk);
            }
        }

        return response()->json([
            'message' => 'Datos guardados correctamente. Total insertados: ' . count($data),
            'total' => count($data)
        ]);
    }

    public function deletePremiosLotonet(Request $request)
    {
        header('Content-Type: application/json');
        $fecha = $request->query('fecha');
        PremioNet::whereDate('fecha', $fecha)->delete();
        return response()->json([
            'message' => 'Datos eliminados correctamente',
        ]);
    }
}
