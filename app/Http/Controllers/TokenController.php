<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use DateTime;
use App\Models\Token;
use App\Services\Lotobet\LotobetSessionService;
use Illuminate\Http\JsonResponse;

class TokenController extends Controller
{
    private const LOTEDOM_TOKEN_ID = 3;

    public function generateToken(): JsonResponse
    {
        try {
            app(LotobetSessionService::class)->generateToken();

            return response()->json([
                'success' => 'Token generado y guardado correctamente.'
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 502);
        }
    }

    public function iniciarSession()
    {
        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => 'https://lotedom-api.orkapi.net/api/finan/sessions',
            CURLOPT_PROXY => '',
            CURLOPT_NOPROXY => '*',
            CURLOPT_RETURNTRANSFER => true,
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
                'Cookie: _orkapi_session=ETstr4v9hJAqMdNCFLXG8h31I%2BgnbAgpck0YOlrh9r3xJ9DFTWYISeLV96ssfQoBQmnoi6zsWrIRDan65X2aW%2BUNtQq1ENV5VvUvIpl%2FD0Nx7TerItjXiT4a6eoN4X%2BxMfCvA%2BiBTbBcTwKT8SocnY00vDc2o%2BU6UGdi9NuvSlGSCAuGZ9SUiwFj%2FDDav1bztzbYgUICd8%2BydXSE2lHdn9BHicT8zQUFCagAfEaeTGW00y%2BAycha23LdOwmkGdaTG3Z4XVA42QnA5S%2BW%2B5%2FwEedRYsfYNLHykSngqut%2FnQ%3D%3D--XraL0AcmmRfoNqf1--ddnMNwA3T6FwVRFyO%2BW3GA%3D%3D',
            ),
        ));

        $response = curl_exec($curl);
        $curlError = curl_error($curl);
        $httpCode = (int) curl_getinfo($curl, CURLINFO_HTTP_CODE);

        curl_close($curl);

        if ($response === false || $curlError !== '') {
            return response()->json([
                'message' => $curlError !== '' ? $curlError : 'No se pudo conectar con la API de token Lotedom.',
            ], 502);
        }

        $data = json_decode($response, true);

        if (!is_array($data)) {
            return response()->json([
                'message' => 'La API de token Lotedom devolvio una respuesta invalida.',
            ], 502);
        }

        $tokenValue = data_get($data, 'Content.Token')
            ?? data_get($data, 'content.token')
            ?? data_get($data, 'token')
            ?? data_get($data, 'data.token');
        $fechaString = data_get($data, 'Content.DateExpire')
            ?? data_get($data, 'content.date_expire')
            ?? data_get($data, 'content.expire')
            ?? data_get($data, 'expires_at')
            ?? data_get($data, 'data.expires_at');
        $fecha = $this->parseTokenExpiry($fechaString) ?? now()->addHours(12);

        if (!is_string($tokenValue) || trim($tokenValue) === '') {
            return response()->json([
                'message' => data_get($data, 'msg')
                    ?: data_get($data, 'message')
                    ?: ('No se pudo generar el token Lotedom' . ($httpCode > 0 ? " (HTTP {$httpCode})" : '') . '.'),
            ], $httpCode >= 400 ? $httpCode : 502);
        }

        Token::query()->updateOrCreate(['id' => self::LOTEDOM_TOKEN_ID], [
            'token' => trim($tokenValue),
            'fecha' => $fecha->format('Y-m-d H:i:s'),
        ]);

        return response()->json([
            'success' => 'Token Lotedom generado y guardado correctamente.'
        ]);
    }
    public function loginFlash(): JsonResponse
    {
        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => 'https://bjoselitoadapi.lotobet.bet/api/v1/MfgFGBXCFF/JCtLkiQNHi/QTpWZl9XId',
            CURLOPT_PROXY => '',
            CURLOPT_NOPROXY => '*',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_CONNECTTIMEOUT => 15,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'GET',
            CURLOPT_HTTPHEADER => array(
                'AhfCC: VJgej8Mn2yFYNXEr',
                'AhfVB: tnusa4hPNsSbAVPQ'
            ),
            CURLOPT_SSL_VERIFYHOST => 0,
            CURLOPT_SSL_VERIFYPEER => 0,
        ));

        $response = curl_exec($curl);
        $curlError = curl_error($curl);
        $httpCode = (int) curl_getinfo($curl, CURLINFO_HTTP_CODE);

        curl_close($curl);

        $data = json_decode($response, true);

        if ($response === false || $curlError !== '') {
            return response()->json([
                'message' => $curlError !== '' ? $curlError : 'No se pudo conectar para generar el token flash.',
            ], 502);
        }

        if (!is_array($data)) {
            return response()->json([
                'message' => 'La API de token flash devolvio una respuesta invalida.',
            ], 502);
        }

        $tokenValue = data_get($data, 'Content.Token');
        $fechaString = data_get($data, 'Content.DateExpire');
        $fecha = $this->parseTokenExpiry($fechaString);

        if (!is_string($tokenValue) || trim($tokenValue) === '' || !$fecha) {
            return response()->json([
                'message' => data_get($data, 'msg')
                    ?: data_get($data, 'message')
                    ?: ('No se pudo generar el token flash' . ($httpCode > 0 ? " (HTTP {$httpCode})" : '') . '.'),
            ], $httpCode >= 400 ? $httpCode : 502);
        }

        Token::query()->updateOrCreate(['id' => 2], [
            'token' => $tokenValue,
            'fecha' => $fecha->format('Y-m-d H:i:s')
        ]);

        return response()->json([
            'success' => 'Token generado y guardado correctamente.'
        ]);
    }

    private function parseTokenExpiry($fechaString): ?Carbon
    {
        if (!is_string($fechaString) || trim($fechaString) === '') {
            return null;
        }

        $fechaString = trim($fechaString);

        $formatos = [
            'Y-m-d\TH:i:s.uP',
            'Y-m-d\TH:i:s.u',
            DateTime::ATOM,
            'Y-m-d H:i:s',
        ];

        foreach ($formatos as $formato) {
            try {
                $fecha = Carbon::createFromFormat($formato, $fechaString);
                if ($fecha !== false) {
                    return $fecha->setTimezone(config('app.timezone'));
                }
            } catch (\Throwable $e) {
            }
        }

        try {
            return Carbon::parse($fechaString)->setTimezone(config('app.timezone'));
        } catch (\Throwable $e) {
            return null;
        }
    }
}
