<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WhatsAppService
{
    public function sendText(string $recipient, string $message, ?string $account = null): array
    {
        $endpoint = config('services.whatsapp.send_endpoint');
        $secret = config('services.whatsapp.api_key');
        $timeout = (int) config('services.whatsapp.timeout', 30);
        $account = $account ?: config('services.whatsapp.default_account');
        $verifySsl = filter_var(config('services.whatsapp.verify_ssl', true), FILTER_VALIDATE_BOOLEAN);

        if (!$endpoint || !$secret || !$account) {
            Log::warning('WhatsApp Zender configuracion incompleta', [
                'has_endpoint' => (bool) $endpoint,
                'has_secret' => (bool) $secret,
                'has_account' => (bool) $account,
            ]);

            return [
                'success' => false,
                'status' => null,
                'message' => 'Configuracion de WhatsApp incompleta.',
                'provider_response' => null,
            ];
        }

        $payload = [
            'secret' => $secret,
            'account' => $account,
            'recipient' => $recipient,
            'type' => 'text',
            'message' => $message,
            'priority' => 1,
        ];

        Log::debug('WhatsApp Zender request enviado', [
            'endpoint' => $endpoint,
            'account' => $account,
            'recipient' => $recipient,
            'type' => 'text',
            'priority' => 1,
            'timeout' => $timeout,
            'verify_ssl' => $verifySsl,
        ]);

        try {
            $response = Http::timeout($timeout)
                ->acceptJson()
                ->asMultipart()
                ->withOptions(['verify' => $verifySsl])
                ->post($endpoint, $payload);

            $providerResponse = $response->json();
            if ($providerResponse === null) {
                $providerResponse = $response->body();
            }

            Log::debug('WhatsApp Zender respuesta proveedor', [
                'status' => $response->status(),
                'successful' => $response->successful(),
                'provider_response' => $providerResponse,
            ]);

            return [
                'success' => $response->successful(),
                'status' => $response->status(),
                'message' => $response->successful()
                    ? 'Mensaje enviado.'
                    : 'No se pudo enviar el mensaje.',
                'provider_response' => $providerResponse,
            ];
        } catch (\Throwable $e) {
            Log::error('WhatsApp Zender excepcion', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return [
                'success' => false,
                'status' => null,
                'message' => $e->getMessage(),
                'provider_response' => null,
            ];
        }
    }
}
