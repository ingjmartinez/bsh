<?php

namespace App\Http\Controllers;

use App\Models\Token;
use App\Services\Lotobet\LotobetSessionService;
use Carbon\Carbon;
use DateTime;
use Illuminate\Http\JsonResponse;

class TokenController extends Controller
{
    private const LOTEDOM_TOKEN_ID = 3;

    public function generateToken(): JsonResponse
    {
        try {
            app(LotobetSessionService::class)->generateToken();

            return response()->json([
                'success' => 'Token generado y guardado correctamente.',
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

        curl_setopt_array($curl, [
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
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'Cookie: _orkapi_session=UPuVjD2LH%2BZZ1CpfW2921%2FwHMJLBoFGG9OmHhK2n3OfRsN7c1a87%2FUsSgdDsQ9JmuUaj4EdbqBQ2WQWiGcdyeogJOR9c17SFqXCFyPAa6M%2Fivx48eLNQswNJ9G5FZGMC36Lb7q3mIJ6E8GtDwmri2lwIdfukVz9cEFkjEBRuNbzwkVe7a0HwO0hiGU5wqN%2FlfBwL%2B4s9eiYiwhXtVzcSZ9iPU0wzMsLj0%2BlVJ9ULchP1VFdcFTAl14kII1XM67iTcOAGeNCSnrD65Ga0JPUW5zUxNgc%2Fuy7OmzE1SHyj%2Bg%3D%3D--%2FR4xNVnQBHDNmn0t--QUFBKpOoQas0rF0N73LLIg%3D%3D',
            ],
        ]);

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

        if (! is_array($data)) {
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

        if (! is_string($tokenValue) || trim($tokenValue) === '') {
            return response()->json([
                'message' => data_get($data, 'msg')
                    ?: data_get($data, 'message')
                    ?: ('No se pudo generar el token Lotedom'.($httpCode > 0 ? " (HTTP {$httpCode})" : '').'.'),
            ], $httpCode >= 400 ? $httpCode : 502);
        }

        Token::query()->updateOrCreate(['id' => self::LOTEDOM_TOKEN_ID], [
            'token' => trim($tokenValue),
            'fecha' => $fecha->format('Y-m-d H:i:s'),
        ]);

        return response()->json([
            'success' => 'Token Lotedom generado y guardado correctamente.',
        ]);
    }

    public function loginFlash(): JsonResponse
    {
        $curl = curl_init();

        curl_setopt_array($curl, [
            CURLOPT_URL => 'https://bdeltaadapi.lotobet.bet/api/v1/MfgFGBXCFF/36Wwxr6h6WuV/V0mVbv1IAs9Q',
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
            CURLOPT_HTTPHEADER => [
                'AhfCC: VJgej8Mn2yFYNXEr',
                'AhfVB: tnusa4hPNsSbAVPQ',
            ],
            CURLOPT_SSL_VERIFYHOST => 0,
            CURLOPT_SSL_VERIFYPEER => 0,
        ]);

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

        if (! is_array($data)) {
            return response()->json([
                'message' => 'La API de token flash devolvio una respuesta invalida.',
            ], 502);
        }

        $tokenValue = data_get($data, 'Content.Token');
        $fechaString = data_get($data, 'Content.DateExpire');
        $fecha = $this->parseTokenExpiry($fechaString);

        if (! is_string($tokenValue) || trim($tokenValue) === '' || ! $fecha) {
            return response()->json([
                'message' => data_get($data, 'msg')
                    ?: data_get($data, 'message')
                    ?: ('No se pudo generar el token flash'.($httpCode > 0 ? " (HTTP {$httpCode})" : '').'.'),
            ], $httpCode >= 400 ? $httpCode : 502);
        }

        Token::query()->updateOrCreate(['id' => 2], [
            'token' => $tokenValue,
            'fecha' => $fecha->format('Y-m-d H:i:s'),
        ]);

        return response()->json([
            'success' => 'Token generado y guardado correctamente.',
        ]);
    }

    private function parseTokenExpiry($fechaString): ?Carbon
    {
        if (! is_string($fechaString) || trim($fechaString) === '') {
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
