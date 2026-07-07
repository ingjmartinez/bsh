<?php

namespace App\Http\Controllers;

use App\Models\FaltantesBet;
use App\Models\FaltantesDelta;
use App\Models\FaltantesNet;
use App\Models\Token;
use App\Support\LotedomRowMapper;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class FaltantesController extends Controller
{
    private const DELTA_TOKEN_ID = 2;
    private const DELTA_FALTANTE_TIPO_TRANS = 16;
    private const DELTA_PASIVO_CUENTA_PREFIX = '2';
    private const DELTA_FALTANTES_URL = 'https://bdeltaadapi.lotobet.bet/api/V1/FrBhPLdFAD';
    private const DELTA_HEADERS = [
        'AhfCC: VJgej8Mn2yFYNXEr',
        'AhfVB: tnusa4hPNsSbAVPQ',
    ];

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

    public function getFaltantesLotedom(Request $request)
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
            $registro = LotedomRowMapper::faltante($v, $fecha);

            $data[] = array_merge($registro, [
                'consorcio_id' => $v['consorcio_id'] ?? $v['consorcio'] ?? null,
                'identificacion' => $v['identificacion'] ?? $v['cedula'] ?? null,
                'descripcion' => $registro['observacion'] ?? $v['descripcion'] ?? $v['observacion'] ?? null,
            ]);
        }

        return response()->json(['faltantes' => $data, 'code' => $faltantes['code'], 'message' => 'Resultas obtenidos correctamente']);
    }

    public function saveFaltantesLotedom(Request $request)
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
            $data[] = LotedomRowMapper::faltante($v, $fecha);
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

    public function deleteFaltantesLotedom(Request $request)
    {
        header('Content-Type: application/json');

        $fecha = $request->query('fecha');

        FaltantesNet::whereDate('fecha', $fecha)->delete();

        return response()->json([
            'message' => 'Datos eliminados correctamente',
        ]);
    }

    public function getFaltantesDelta(Request $request)
    {
        header('Content-Type: application/json');

        $fecha = $request->query('fecha');

        if (!$this->validDate($fecha)) {
            return response()->json(['message' => 'Fecha invalida'], 422);
        }

        $token = $this->getDeltaToken();
        if (!$token) {
            return response()->json(['error' => 'Genere un token'], 404);
        }

        if ($this->tokenExpired($token)) {
            return response()->json(['error' => 'El token ha expirado, genere uno nuevo'], 401);
        }

        $faltantes = $this->fetchFaltantesDelta($fecha, $token->token);

        return response()->json([
            'faltantes' => $faltantes['Content'],
            'code' => $faltantes['code'],
            'message' => $faltantes['msg'],
        ], $faltantes['code'] === 0 ? 200 : 502);
    }

    public function saveFaltantesDelta(Request $request)
    {
        ini_set('memory_limit', '1G');
        ini_set('max_execution_time', 300);
        set_time_limit(300);
        header('Content-Type: application/json');

        $fecha = $request->query('fecha');

        if (!$this->validDate($fecha)) {
            return response()->json(['message' => 'Fecha invalida'], 422);
        }

        if (FaltantesDelta::query()->whereDate('fecha', $fecha)->exists()) {
            return response()->json(['message' => 'Ya hay data guardada en la fecha: ' . $fecha]);
        }

        $token = $this->getDeltaToken();
        if (!$token) {
            return response()->json(['message' => 'Genere un token'], 404);
        }

        if ($this->tokenExpired($token)) {
            return response()->json(['message' => 'El token ha expirado, genere uno nuevo'], 401);
        }

        $faltantes = $this->fetchFaltantesDelta($fecha, $token->token);

        if ($faltantes['code'] !== 0) {
            return response()->json(['message' => $faltantes['msg'], 'code' => $faltantes['code']], 502);
        }

        $data = [];
        foreach ($faltantes['Content'] as $v) {
            $data[] = [
                'fecha' => $fecha,
                'id_trans' => $v['IdTrans'] ?? null,
                'id_tipo_trans' => $v['IdTipoTrans'] ?? null,
                'concepto' => $v['Concepto'] ?? null,
                'estatus' => $v['Estatus'] ?? null,
                'fec_transaccion' => $this->parseDeltaDate($v['FecTransaccion'] ?? null),
                'fec_inclusion' => $this->parseDeltaDate($v['FecInclusion'] ?? null),
                'usr_inclusion' => $v['UsrInclusion'] ?? null,
                'id_cuenta' => $v['IdCuenta'] ?? null,
                'debito' => $v['Debito'] ?? null,
                'credito' => $v['Credito'] ?? null,
                'numero' => $v['Numero'] ?? null,
                'nombre_cuenta' => $v['NombreCuenta'] ?? null,
            ];
        }

        if (!empty($data)) {
            foreach (array_chunk($data, 5000) as $chunk) {
                FaltantesDelta::query()->insert($chunk);
            }
        }

        return response()->json([
            'message' => 'Datos guardados correctamente. Total insertados: ' . count($data),
            'total' => count($data),
        ]);
    }

    public function deleteFaltantesDelta(Request $request)
    {
        header('Content-Type: application/json');

        $fecha = $request->query('fecha');

        if (!$this->validDate($fecha)) {
            return response()->json(['message' => 'Fecha invalida'], 422);
        }

        FaltantesDelta::query()->whereDate('fecha', $fecha)->delete();

        return response()->json([
            'message' => 'Datos eliminados correctamente',
        ]);
    }

    private function fetchFaltantesDelta(string $fecha, string $token): array
    {
        $curl = curl_init();

        curl_setopt_array($curl, [
            CURLOPT_URL => self::DELTA_FALTANTES_URL . "/{$token}/{$fecha}/{$fecha}",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'GET',
            CURLOPT_HTTPHEADER => self::DELTA_HEADERS,
            CURLOPT_SSL_VERIFYHOST => 0,
            CURLOPT_SSL_VERIFYPEER => 0,
        ]);

        $response = curl_exec($curl);

        if ($response === false) {
            $error = curl_error($curl);
            curl_close($curl);

            return [
                'Content' => [],
                'code' => 1,
                'msg' => 'Error consultando API Delta: ' . $error,
            ];
        }

        curl_close($curl);

        $data = json_decode($response, true);
        if (!is_array($data)) {
            return [
                'Content' => [],
                'code' => 1,
                'msg' => 'La API Delta no devolvio un JSON valido',
            ];
        }

        $content = collect($data['Content'] ?? [])
            ->filter(fn($row) => $this->isFaltanteDeltaRow($row))
            ->values()
            ->all();

        return [
            'Content' => $content,
            'code' => (int) ($data['code'] ?? 0),
            'msg' => $data['msg'] ?? $data['message'] ?? 'Datos obtenidos correctamente',
        ];
    }

    private function isFaltanteDeltaRow(array $row): bool
    {
        $tipoTrans = (int) ($row['IdTipoTrans'] ?? 0);
        $cuenta = trim((string) ($row['IdCuenta'] ?? ''));

        return $tipoTrans === self::DELTA_FALTANTE_TIPO_TRANS
            && !str_starts_with($cuenta, self::DELTA_PASIVO_CUENTA_PREFIX);
    }

    private function getDeltaToken(): ?Token
    {
        $token = Token::find(self::DELTA_TOKEN_ID);

        if (!$token || empty($token->token)) {
            return null;
        }

        return $token;
    }

    private function tokenExpired(Token $token): bool
    {
        if (empty($token->fecha)) {
            return true;
        }

        return now()->greaterThan(Carbon::parse($token->fecha));
    }

    private function validDate(?string $date): bool
    {
        if (!$date || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
            return false;
        }

        try {
            return Carbon::createFromFormat('Y-m-d', $date)->format('Y-m-d') === $date;
        } catch (\Throwable $e) {
            return false;
        }
    }

    private function parseDeltaDate(?string $date): ?string
    {
        if (!$date) {
            return null;
        }

        try {
            return Carbon::parse($date)->format('Y-m-d H:i:s');
        } catch (\Throwable $e) {
            return null;
        }
    }
}
