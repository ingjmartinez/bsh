<?php

namespace App\Http\Controllers;

use App\Models\Token;
use App\Models\Recarga;
use App\Models\RecargaNet;
use App\Support\LotonetRowMapper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RecargasController extends Controller
{
    private const LOTEDOM_TOKEN_ID = 3;

    public function getRecargasLotobet(Request $request)
    {
        header('Content-Type: application/json');

        $fecha = $request->query('fecha');
        if (!$this->isValidDate($fecha)) {
            return response()->json(['recargas' => [], 'code' => 1, 'message' => 'Fecha invalida'], 422);
        }

        $recargas = array_map(
            fn (array $row): array => $this->normalizeRecargaLotedomRow($row, $fecha),
            $this->fetchRecargasLotedom($fecha)
        );

        return response()->json(['recargas' => $recargas, 'code' => 0, 'message' => '']);
    }

    public function saveRecargasLotobet(Request $request)
    {
        ini_set('memory_limit', '1G');
        ini_set('max_execution_time', 300); // 300 segundos = 5 minutos
        set_time_limit(300);                // alternativa equivalente
        header('Content-Type: application/json');

        $fecha = $request->query('fecha');
        if (!$this->isValidDate($fecha)) {
            return response()->json(['message' => 'Fecha invalida', 'total' => 0], 422);
        }

        $existe = Recarga::whereDate('fecha', $fecha)->exists();

        if ($existe) {
            return response()->json(['message' => 'Ya hay data guardada en la fecha: ' . $fecha]);
        }

        $data = array_map(
            fn (array $row): array => $this->mapRecargaLotedomRow($row, $fecha),
            $this->fetchRecargasLotedom($fecha)
        );

        if (!empty($data)) {
            foreach (array_chunk($data, 5000) as $chunk) {
                DB::table('recargas_bet')->insert($chunk);
            }
        }

        return response()->json([
            'message' => 'Datos guardados correctamente. Total insertados: ' . count($data),
            'total' => count($data)
        ]);
    }

    public function deleteRecargasLotobet(Request $request)
    {
        header('Content-Type: application/json');

        $fecha = $request->query('fecha');

        Recarga::whereDate('fecha', $fecha)->delete();

        return response()->json([
            'message' => 'Datos eliminados correctamente',
        ]);
    }

    private function fetchRecargasLotedom(string $fecha): array
    {
        $curl = curl_init();
        $headers = $this->lotedomSessionHeaders();

        curl_setopt_array($curl, array(
            CURLOPT_URL => "https://lotedom-api.orkapi.net/api/finan/ventas_recarga/{$fecha}",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_CONNECTTIMEOUT => 15,
            CURLOPT_TIMEOUT => 120,
            CURLOPT_PROXY => '',
            CURLOPT_NOPROXY => '*',
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'GET',
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_SSL_VERIFYHOST => 0,
            CURLOPT_SSL_VERIFYPEER => 0,
        ));

        $response = curl_exec($curl);
        $error = curl_error($curl);
        curl_close($curl);

        if ($response === false || $error) {
            abort(response()->json([
                'recargas' => [],
                'code' => 1,
                'message' => 'No se pudo consultar recargas Lotedom',
            ], 502));
        }

        $items = json_decode($response, true);
        if (!is_array($items)) {
            abort(response()->json([
                'recargas' => [],
                'code' => 1,
                'message' => 'Respuesta invalida de recargas Lotedom',
            ], 502));
        }

        return $items['data']['result']
            ?? $items['data']
            ?? $items['result']
            ?? $items['Content']
            ?? [];
    }

    private function lotedomSessionHeaders(): array
    {
        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => 'https://lotedom-api.orkapi.net/api/finan/sessions',
            CURLOPT_PROXY => '',
            CURLOPT_NOPROXY => '*',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HEADER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_CONNECTTIMEOUT => 15,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_SSL_VERIFYHOST => 0,
            CURLOPT_SSL_VERIFYPEER => 0,
            CURLOPT_POSTFIELDS => json_encode([
                'usuario' => [
                    'username' => 'api_contabilidad@bsh',
                    'password' => 'P4@23498sd$$+',
                ],
            ]),
            CURLOPT_HTTPHEADER => array(
                'Content-Type: application/json',
            ),
        ));

        $response = curl_exec($curl);
        $error = curl_error($curl);
        $httpCode = (int) curl_getinfo($curl, CURLINFO_HTTP_CODE);
        $headerSize = (int) curl_getinfo($curl, CURLINFO_HEADER_SIZE);
        curl_close($curl);

        if ($response === false || $error) {
            abort(response()->json([
                'recargas' => [],
                'code' => 1,
                'message' => 'No se pudo iniciar sesion Lotedom para recargas',
            ], 502));
        }

        $rawHeaders = substr((string) $response, 0, $headerSize);
        $body = substr((string) $response, $headerSize);
        $data = json_decode($body, true);

        $token = data_get($data, 'Content.Token')
            ?? data_get($data, 'content.token')
            ?? data_get($data, 'token')
            ?? data_get($data, 'data.token');

        preg_match_all('/^Set-Cookie:\s*([^;]+)/mi', $rawHeaders, $matches);
        $cookie = implode('; ', $matches[1] ?? []);

        if (!is_string($token) || trim($token) === '' || $cookie === '') {
            abort(response()->json([
                'recargas' => [],
                'code' => 1,
                'message' => 'No se pudo autenticar Lotedom para recargas' . ($httpCode > 0 ? " (HTTP {$httpCode})" : ''),
            ], $httpCode >= 400 ? $httpCode : 502));
        }

        Token::query()->updateOrCreate(['id' => self::LOTEDOM_TOKEN_ID], [
            'token' => trim($token),
            'fecha' => now()->addHours(12)->format('Y-m-d H:i:s'),
        ]);

        return [
            'token: ' . trim($token),
            'Cookie: ' . $cookie,
        ];
    }

    private function mapRecargaLotedomRow(array $row, string $fecha): array
    {
        $row = $this->normalizeRecargaLotedomRow($row, $fecha);
        $now = now();

        return [
            'consorcio_id' => $row['consorcio_id'],
            'consorcio_codigo' => $row['consorcio_codigo'],
            'consorcio_nombre' => $row['consorcio_nombre'],
            'banca_id' => $row['banca_id'],
            'banca_nombre' => $row['banca_nombre'],
            'producto_id' => $row['producto_id'],
            'producto_nombre' => $row['producto_nombre'],
            'monto' => $row['monto'],
            'agencia_id' => $row['agencia_id'],
            'terminal_codigo' => $row['terminal_codigo'],
            'terminal_nombre' => $row['terminal_nombre'],
            'descripcion' => $row['descripcion'],
            'distribuidora_id' => $row['distribuidora_id'],
            'distribuidora_nombre' => $row['distribuidora_nombre'],
            'proveedor_id' => $row['proveedor_id'],
            'proveedor_nombre' => $row['proveedor_nombre'],
            'comision' => $row['comision'],
            'comision_supervisor' => $row['comision_supervisor'],
            'cedula' => $row['cedula'],
            'fecha' => $fecha,
            'created_at' => $now,
            'updated_at' => $now,
        ];
    }

    private function normalizeRecargaLotedomRow(array $row, string $fecha): array
    {
        $terminalCodigo = $row['terminal_codigo'] ?? $row['agencia_id'] ?? null;

        return [
            'fecha' => $row['fecha'] ?? $fecha,
            'consorcio_id' => $row['consorcio_id'] ?? null,
            'consorcio_codigo' => $row['consorcio_codigo'] ?? null,
            'consorcio_nombre' => $row['consorcio_nombre'] ?? null,
            'banca_id' => $row['banca_id'] ?? null,
            'banca_nombre' => $row['banca_nombre'] ?? null,
            'producto_id' => isset($row['producto_id']) ? (int) $row['producto_id'] : null,
            'producto_nombre' => $row['producto_nombre'] ?? null,
            'monto' => $this->toDecimal($row['monto'] ?? 0),
            'terminal_codigo' => $terminalCodigo,
            'agencia_id' => $row['agencia_id'] ?? $terminalCodigo,
            'terminal_nombre' => $row['terminal_nombre'] ?? null,
            'descripcion' => $row['descripcion'] ?? $row['producto_nombre'] ?? null,
            'distribuidora_id' => $row['distribuidora_id'] ?? null,
            'distribuidora_nombre' => $row['distribuidora_nombre'] ?? null,
            'proveedor_id' => $row['proveedor_id'] ?? null,
            'proveedor_nombre' => $row['proveedor_nombre'] ?? null,
            'comision' => $this->toDecimal($row['comision'] ?? 0),
            'comision_supervisor' => $this->toDecimal($row['comision_supervisor'] ?? 0),
            'cedula' => $row['cedula'] ?? $row['identificacion'] ?? null,
        ];
    }

    private function toDecimal(mixed $value): float
    {
        return (float) str_replace(',', '', (string) ($value ?? 0));
    }

    private function isValidDate(?string $fecha): bool
    {
        return is_string($fecha) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $fecha) === 1;
    }

    public function getRecargasLotonet(Request $request)
    {
        header('Content-Type: application/json');

        $curl = curl_init();

        $fecha = $request->query('fecha');

        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => "http://contable.apploteka.com/api/finan/ventas_recarga/{$fecha}/5",
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

        return response()->json(['recargas' => $data, 'code' => $items['code'], 'message' => '']);
    }

    public function saveRecargasLotonet(Request $request)
    {
        ini_set('memory_limit', '1G');
        ini_set('max_execution_time', 300); // 300 segundos = 5 minutos
        set_time_limit(300);                // alternativa equivalente
        header('Content-Type: application/json');

        $curl = curl_init();

        $fecha = $request->query('fecha');

        $existe = RecargaNet::whereDate('fecha', $fecha)->exists();

        if ($existe) {
            return response()->json(['message' => 'Ya hay data guardada en la fecha: ' . $fecha]);
        }

        curl_setopt_array($curl, array(
            CURLOPT_URL => "http://contable.apploteka.com/api/finan/ventas_recarga/{$fecha}/5",
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
                DB::table('recargas_net')->insert($chunk);
            }
        }

        return response()->json([
            'message' => 'Datos guardados correctamente. Total insertados: ' . count($data),
            'total' => count($data)
        ]);
    }

    public function deleteRecargasLotonet(Request $request)
    {
        header('Content-Type: application/json');

        $fecha = $request->query('fecha');

        RecargaNet::whereDate('fecha', $fecha)->delete();

        return response()->json([
            'message' => 'Datos eliminados correctamente',
        ]);
    }
}
