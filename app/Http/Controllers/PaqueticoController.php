<?php

namespace App\Http\Controllers;

use App\Models\Paquetico;
use App\Support\LotedomRowMapper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PaqueticoController extends Controller
{
    private const PRODUCTO_ID = 'PAQUETICOS_ZATACA';

    public function get(Request $request)
    {
        header('Content-Type: application/json');

        $fecha = $request->query('fecha');

        $curl = curl_init();

        curl_setopt_array($curl, [
            CURLOPT_URL => "https://lotedom-api.orkapi.net/api/finan/productos_externos/{$fecha}",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'GET',
            CURLOPT_HTTPHEADER => [
                'token: ZFozLWdBYyqERusVdTsW',
                'Cookie: _orkapi_session=gYHpUnlJPzeJ4Q4BBxpzUMLB3t1xysT0uubxOi1MHTT04iCiTRnoB0s4SUoIHeh3MlQX%2FoJ2Un6%2Fd5tpa5QnRqz1c7lhQyrTpzQ%2BfeSjGILNO6FO9T2Lba8KZMWx6wL1TagahpRU2wvGJLvf4%2FzSWfxMt6LAkxmUkBlmtfonut%2FULWbTwPwhHfBrWgKurg2l34KpWujnG6laaaO7rYzAiGDooAbTIaI0MVevV%2F3BnHN6RFgOlrAHJ8ZNQlMKl79udwYEo1LC6yBvihXeWwnuJCbR73RdOEdDe3PbYJzKHQ%3D%3D--ULLy9F7ay3cc2F%2Bj--WDpxNmUrgmf1GuXEhEq8dg%3D%3D',
            ],
            CURLOPT_SSL_VERIFYHOST => 0,
            CURLOPT_SSL_VERIFYPEER => 0,
        ]);

        $response = curl_exec($curl);

        curl_close($curl);

        $items = json_decode($response, true);

        $data = array_values(array_filter(
            $items['data']['result'] ?? [],
            fn (array $row): bool => ($row['producto_id'] ?? null) === self::PRODUCTO_ID
        ));

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
            return response()->json(['message' => 'Ya hay data guardada en la fecha: '.$fecha]);
        }

        curl_setopt_array($curl, [
            CURLOPT_URL => "https://lotedom-api.orkapi.net/api/finan/productos_externos/{$fecha}",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'GET',
            CURLOPT_HTTPHEADER => [
                'token: ZFozLWdBYyqERusVdTsW',
                'Cookie: _orkapi_session=gYHpUnlJPzeJ4Q4BBxpzUMLB3t1xysT0uubxOi1MHTT04iCiTRnoB0s4SUoIHeh3MlQX%2FoJ2Un6%2Fd5tpa5QnRqz1c7lhQyrTpzQ%2BfeSjGILNO6FO9T2Lba8KZMWx6wL1TagahpRU2wvGJLvf4%2FzSWfxMt6LAkxmUkBlmtfonut%2FULWbTwPwhHfBrWgKurg2l34KpWujnG6laaaO7rYzAiGDooAbTIaI0MVevV%2F3BnHN6RFgOlrAHJ8ZNQlMKl79udwYEo1LC6yBvihXeWwnuJCbR73RdOEdDe3PbYJzKHQ%3D%3D--ULLy9F7ay3cc2F%2Bj--WDpxNmUrgmf1GuXEhEq8dg%3D%3D',
            ],
            CURLOPT_SSL_VERIFYHOST => 0,
            CURLOPT_SSL_VERIFYPEER => 0,
        ]);

        $response = curl_exec($curl);

        curl_close($curl);

        $items = json_decode($response, true);

        $rows = array_values(array_filter(
            $items['data']['result'] ?? [],
            fn (array $row): bool => ($row['producto_id'] ?? null) === self::PRODUCTO_ID
        ));

        $data = array_map(
            fn (array $row): array => LotedomRowMapper::recarga($row, $fecha),
            $rows
        );

        if (! empty($data)) {
            foreach (array_chunk($data, 5000) as $chunk) {
                DB::table('paquetico_net')->insert($chunk);
            }
        }

        return response()->json([
            'message' => 'Datos guardados correctamente. Total insertados: '.count($data),
            'total' => count($data),
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
