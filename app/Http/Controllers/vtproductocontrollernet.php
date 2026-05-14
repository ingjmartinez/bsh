<?php

namespace App\Http\Controllers;

use App\Models\VtUsuarioNet;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class vtproductocontrollernet extends Controller
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

    public function getVentasUsuariosLotonet(Request $request)
    {
        header('Content-Type: application/json');

        $curl = curl_init();

        $fecha = $request->query('fecha');

        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => "http://contable.apploteka.com//api/finan/ventas_por_usuario/{$fecha}/5",
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

        $data = [];

        foreach ($ventas['data']['result'] as $v) {
            $data[] = [
                'consorcio_id'  => $v['consorcio_id'] ?? null,
                'agencia_id'    => $v['agencia_id'] ?? null,
                'producto_id'   => $v['producto_id'] ?? null,
                'cedula'        => $this->normalizeCedula($v['cedula'] ?? null),
                'descripcion'   => $v['descripcion'] ?? null,
                'tipo'          => $v['tipo'] ?? null,
                'monto'         => $v['monto'] ?? 0,
                'fecha'         => $fecha,
            ];
        }

        return response()->json(['ventas' => $data, 'code' => $ventas['code'], 'message' => '']);
    }

    public function saveVentasUsuariosLotonet(Request $request)
    {
        ini_set('max_execution_time', 300); // 300 segundos = 5 minutos
        set_time_limit(300);                // alternativa equivalente
        header('Content-Type: application/json');

        $curl = curl_init();

        $fecha = $request->query('fecha');

        $existe = VtUsuarioNet::whereDate('fecha', $fecha)->exists();

        if ($existe) {
            return response()->json(['message' => 'Ya hay data guardada en la fecha: ' . $fecha]);
        }

        curl_setopt_array($curl, array(
            CURLOPT_URL => "http://contable.apploteka.com//api/finan/ventas_por_usuario/{$fecha}/5",
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

        $data = [];

        foreach ($ventas['data']['result'] as $v) {
            $data[] = [
                'consorcio_id'  => $v['consorcio_id'] ?? null,
                'agencia_id'    => $v['agencia_id'] ?? null,
                'producto_id'   => $v['producto_id'] ?? null,
                'cedula'        => $this->normalizeCedula($v['cedula'] ?? null),
                'descripcion'   => $v['descripcion'] ?? null,
                'tipo'          => $v['tipo'] ?? null,
                'monto'         => $v['monto'] ?? 0,
                'fecha'         => $fecha,
            ];
        }

        if (!empty($data)) {
            foreach (array_chunk($data, 5000) as $chunk) {
                DB::table('vt_usuarios_net')->insert($chunk);
            }
        }

        return response()->json([
            'message' => 'Datos guardados correctamente. Total insertados: ' . count($data),
        ]);
    }

    public function deleteVentasUsuariosLotonet(Request $request)
    {
        header('Content-Type: application/json');

        $fecha = $request->query('fecha');

        VtUsuarioNet::whereDate('fecha', $fecha)->delete();

        return response()->json([
            'message' => 'Datos eliminados correctamente',
        ]);
    }
}
