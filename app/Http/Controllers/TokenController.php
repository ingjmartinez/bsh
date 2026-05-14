<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use DateTime;
use App\Models\Token;
use App\Services\Lotobet\LotobetSessionService;
use Illuminate\Http\JsonResponse;

class TokenController extends Controller
{
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
            CURLOPT_URL => 'http://contable.apploteka.com/api/finan/sessions',
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
            CURLOPT_POSTFIELDS => '{
                "usuario": {
                    "username": "fjoselito",
                    "password": "mnXd5pSyF3HXjCC4"
                }
            }',
            CURLOPT_HTTPHEADER => array(
                'Content-Type: application/json',
                'Cookie: _orkapi_session=RkZLWFpIMnM1UTdUdjRXVzNuMFRmZFZnQ2U5N0JoV0JaSzBheUFlZ21TSVoyUEhWWFc2Y2R4Nzd2SmVhQXJKOGtsSktHWnNmelgzWGsxcmJESEVkcXRlWW5tdGpzU1ZZcXRBZFNva2lqL3pGMFppZFZnZUxPUXBscWxLYVdVcUwzdURYb1V5bGJwanZkeDdJTGUzZndkV3FxNmtiMjdvNkxpU0ZQK2RWRU1nPS0tbkVwL215TXpYTXpLS1lYYXJTR3Y2UT09--7e272c2a327d71d9feb7996870d828122936b682'
            ),
        ));

        $response = curl_exec($curl);

        curl_close($curl);

        return response()->json([
            'success' => 'Sesión iniciada correctamente.'
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
