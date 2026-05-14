<?php

namespace App\Http\Controllers;

use App\Models\Paquetico;
use App\Support\LotonetRowMapper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PaqueticoController extends Controller
{
    public function get(Request $request)
    {
        header('Content-Type: application/json');

        $fecha = $request->query('fecha');

        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => "http://contable.apploteka.com/api/finan/compra_paqueticos/{$fecha}/5",
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

        foreach ($data as &$v) {
            $v['identificacion'] = str_replace('-', '', $v['identificacion']);
        }
        unset($v); // 🔹 Importante: liberar la referencia

        return response()->json(['paquetico' => $data, 'code' => $items['code'], 'message' => 'Resultas obtenidos correctamente']);
    }

    public function save(Request $request)
    {
        ini_set('memory_limit', '1G');
        ini_set('max_execution_time', 300); // 300 segundos = 5 minutos
        set_time_limit(300);                // alternativa equivalente
        header('Content-Type: application/json');

        $curl = curl_init();

        $fecha = $request->query('fecha');

        $existe = Paquetico::whereDate('fecha', $fecha)->exists();

        if ($existe) {
            return response()->json(['message' => 'Ya hay data guardada en la fecha: ' . $fecha]);
        }

        curl_setopt_array($curl, array(
            CURLOPT_URL => "http://contable.apploteka.com/api/finan/compra_paqueticos/{$fecha}/5",
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
            fn (array $row): array => LotonetRowMapper::recarga($row, $fecha),
            $items['data']['result'] ?? []
        );

        if (!empty($data)) {
            foreach (array_chunk($data, 5000) as $chunk) {
                DB::table('paquetico_net')->insert($chunk);
            }
        }

        return response()->json([
            'message' => 'Datos guardados correctamente. Total insertados: ' . count($data),
            'total' => count($data)
        ]);
    }

    public function delete(Request $request)
    {
        header('Content-Type: application/json');

        $fecha = $request->query('fecha');

        Paquetico::whereDate('fecha', $fecha)->delete();

        return response()->json([
            'message' => 'Datos eliminados correctamente',
        ]);
    }
}
