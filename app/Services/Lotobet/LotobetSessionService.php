<?php

namespace App\Services\Lotobet;

use App\Models\Token;
use Carbon\Carbon;
use DateTime;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use RuntimeException;

class LotobetSessionService
{
    private const TOKEN_ID = 1;
    private const TOKEN_URL = 'https://apiadmin.prodrl.lotvirtual.com/api/v1/MfgFGBXCFF/17BuSHQhIS/eyJLSmNm1j';
    private const BASE_URL = 'https://apiadmin.prodrl.lotvirtual.com/api/V1';
    private const COMPANY_CODE = '07';

    private array $headers = [
        'AhfCC: yB0tt5KW3wVVCYYtCpen',
        'AhfVB: xSzdgtOKbGRhUhtv1ois',
    ];

    public function generateToken(): Token
    {
        Storage::disk('local')->makeDirectory('etl');
        File::ensureDirectoryExists(dirname($this->cookiePath()));
        $response = $this->request(self::TOKEN_URL);
        $data = json_decode($response['body'], true);

        if (!is_array($data)) {
            throw new RuntimeException('La API de token devolvio una respuesta invalida.');
        }

        $tokenValue = data_get($data, 'Content.Token');
        $fechaString = data_get($data, 'Content.DateExpire');

        if (!is_string($tokenValue) || trim($tokenValue) === '') {
            $message = data_get($data, 'msg') ?: data_get($data, 'message') ?: 'La API no devolvio un token valido.';
            throw new RuntimeException((string) $message);
        }

        $fecha = $this->parseTokenExpiry($fechaString);
        if (!$fecha) {
            throw new RuntimeException('No se pudo interpretar la fecha de expiracion del token.');
        }

        return Token::query()->updateOrCreate(['id' => self::TOKEN_ID], [
            'token' => trim($tokenValue),
            'fecha' => $fecha->format('Y-m-d H:i:s'),
        ]);
    }

    public function getToken(): Token
    {
        $token = Token::find(self::TOKEN_ID);
        if (!$token || now()->greaterThan($token->fecha) || !is_file($this->cookiePath())) {
            return $this->generateToken();
        }

        return $token;
    }

    public function getVentasProducto(string $fecha): array
    {
        return $this->getReport('kotFQlCe5XVFoJcjEz', $fecha);
    }

    public function getReport(string $endpointKey, string $fecha): array
    {
        $token = $this->getToken();
        $url = self::BASE_URL . "/{$endpointKey}/{$token->token}/{$fecha}/" . self::COMPANY_CODE;
        $response = $this->request($url);
        $data = json_decode($response['body'], true);

        if (!is_array($data)) {
            throw new RuntimeException('Respuesta invalida de API externa.');
        }

        $code = strtolower(trim((string) ($data['code'] ?? '')));
        if ($code !== '' && !in_array($code, ['0', '200', 'success', 'ok'], true)) {
            $message = (string) ($data['msg'] ?? $data['message'] ?? 'La API de Lotobet rechazo la solicitud.');

            if ($code === '401' || str_contains(strtolower($message), 'token')) {
                $this->clearSession();
            }

            throw new RuntimeException($message);
        }

        return $data;
    }

    public function clearSession(): void
    {
        Token::query()->where('id', self::TOKEN_ID)->delete();

        if (is_file($this->cookiePath())) {
            @unlink($this->cookiePath());
        }
    }

    private function request(string $url): array
    {
        File::ensureDirectoryExists(dirname($this->cookiePath()));
        $curl = curl_init();

        curl_setopt_array($curl, [
            CURLOPT_URL => $url,
            CURLOPT_PROXY => '',
            CURLOPT_NOPROXY => '*',
            CURLOPT_COOKIEJAR => $this->cookiePath(),
            CURLOPT_COOKIEFILE => $this->cookiePath(),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_CONNECTTIMEOUT => 15,
            CURLOPT_TIMEOUT => 120,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'GET',
            CURLOPT_HTTPHEADER => $this->headers,
            CURLOPT_SSL_VERIFYHOST => 0,
            CURLOPT_SSL_VERIFYPEER => 0,
        ]);

        $body = curl_exec($curl);
        $error = curl_error($curl);
        $status = (int) curl_getinfo($curl, CURLINFO_HTTP_CODE);
        curl_close($curl);

        if ($body === false || $error !== '') {
            throw new RuntimeException($error !== '' ? $error : 'No se pudo conectar con la API de Lotobet.');
        }

        if ($status >= 500) {
            throw new RuntimeException("Lotobet respondio HTTP {$status}.");
        }

        return ['status' => $status, 'body' => (string) $body];
    }

    private function cookiePath(): string
    {
        return storage_path('app/etl/lotobet_prodrl_07_cookie.txt');
    }

    private function parseTokenExpiry($fechaString): ?Carbon
    {
        if (!is_string($fechaString) || trim($fechaString) === '') {
            return null;
        }

        foreach (['Y-m-d\TH:i:s.uP', 'Y-m-d\TH:i:s.u', DateTime::ATOM, 'Y-m-d H:i:s'] as $format) {
            try {
                $fecha = Carbon::createFromFormat($format, trim($fechaString));
                if ($fecha !== false) {
                    return $fecha->setTimezone(config('app.timezone'));
                }
            } catch (\Throwable) {
            }
        }

        try {
            return Carbon::parse($fechaString)->setTimezone(config('app.timezone'));
        } catch (\Throwable) {
            return null;
        }
    }
}
