<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class TelegramService
{
    public function setWebhook(string $url): array
    {
        return $this->request('setWebhook', [
            'url' => $url,
            'secret_token' => (string) config('services.telegram.webhook_secret'),
            'allowed_updates' => ['message'],
        ]);
    }

    public function getWebhookInfo(): array
    {
        return $this->request('getWebhookInfo', []);
    }

    public function sendText(string $chatId, string $message, ?array $replyMarkup = null): array
    {
        $payload = [
            'chat_id' => $chatId,
            'text' => $message,
        ];

        if ($replyMarkup !== null) {
            $payload['reply_markup'] = $replyMarkup;
        }

        return $this->request('sendMessage', $payload);
    }

    public function requestPhone(string $chatId): array
    {
        return $this->sendText(
            $chatId,
            'Para identificar tus solicitudes y ofrecerte el mismo servicio de WhatsApp, comparte tu numero de telefono.',
            [
                'keyboard' => [[[
                    'text' => 'Compartir mi numero de telefono',
                    'request_contact' => true,
                ]]],
                'resize_keyboard' => true,
                'one_time_keyboard' => true,
                'input_field_placeholder' => 'Pulsa el boton para continuar',
            ]
        );
    }

    public function removeKeyboard(string $chatId, string $message): array
    {
        return $this->sendText($chatId, $message, ['remove_keyboard' => true]);
    }

    public function downloadFile(string $fileId, ?string $originalName = null, ?string $mimeType = null): ?array
    {
        $file = $this->request('getFile', ['file_id' => $fileId]);
        $filePath = $file['provider_response']['result']['file_path'] ?? null;

        if (! ($file['success'] ?? false) || ! is_string($filePath) || $filePath === '') {
            return null;
        }

        $token = (string) config('services.telegram.bot_token');
        $baseUrl = rtrim((string) config('services.telegram.api_url', 'https://api.telegram.org'), '/');
        $extension = strtolower(pathinfo((string) ($originalName ?: $filePath), PATHINFO_EXTENSION));
        $extension = preg_match('/^[a-z0-9]{2,5}$/', $extension) ? $extension : $this->extensionFromMime($mimeType);
        $extension = $extension ?: 'jpg';
        $storagePath = 'chatbot/telegram/'.now()->format('Y/m/d').'/'.Str::uuid().'.'.$extension;

        try {
            $response = Http::timeout($this->timeout())
                ->withOptions(['verify' => $this->verifySsl()])
                ->get("{$baseUrl}/file/bot{$token}/{$filePath}");

            if (! $response->successful()) {
                Log::warning('Telegram no pudo descargar archivo', [
                    'status' => $response->status(),
                    'file_id' => $fileId,
                ]);

                return null;
            }

            Storage::disk('public')->put($storagePath, $response->body());

            return [
                'url' => url('/storage/'.ltrim($storagePath, '/')),
                'filename' => $originalName ?: basename($filePath),
                'mime' => $mimeType ?: $response->header('Content-Type'),
                'extension' => $extension,
                'type' => 'image',
            ];
        } catch (\Throwable $e) {
            Log::error('Telegram excepcion descargando archivo', [
                'file_id' => $fileId,
                'message' => $e->getMessage(),
            ]);

            return null;
        }
    }

    private function request(string $method, array $payload): array
    {
        $token = trim((string) config('services.telegram.bot_token'));

        if ($token === '') {
            return [
                'success' => false,
                'status' => null,
                'message' => 'Configuracion de Telegram incompleta.',
                'provider_response' => null,
            ];
        }

        $baseUrl = rtrim((string) config('services.telegram.api_url', 'https://api.telegram.org'), '/');

        try {
            $response = Http::timeout($this->timeout())
                ->acceptJson()
                ->asJson()
                ->withOptions(['verify' => $this->verifySsl()])
                ->post("{$baseUrl}/bot{$token}/{$method}", $payload);
            $body = $response->json();
            $success = $response->successful() && is_array($body) && ($body['ok'] ?? false) === true;

            if (! $success) {
                Log::warning('Telegram Bot API respuesta no exitosa', [
                    'method' => $method,
                    'status' => $response->status(),
                    'description' => is_array($body) ? ($body['description'] ?? null) : null,
                ]);
            }

            return [
                'success' => $success,
                'status' => $response->status(),
                'message' => is_array($body) ? ($body['description'] ?? null) : null,
                'provider_response' => $body,
            ];
        } catch (\Throwable $e) {
            Log::error('Telegram Bot API excepcion', [
                'method' => $method,
                'message' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'status' => null,
                'message' => $e->getMessage(),
                'provider_response' => null,
            ];
        }
    }

    private function timeout(): int
    {
        return (int) config('services.telegram.timeout', 30);
    }

    private function verifySsl(): bool
    {
        return filter_var(config('services.telegram.verify_ssl', true), FILTER_VALIDATE_BOOLEAN);
    }

    private function extensionFromMime(?string $mimeType): ?string
    {
        return match (strtolower(trim((string) $mimeType))) {
            'image/jpeg', 'image/jpg' => 'jpg',
            'image/png' => 'png',
            'image/heic' => 'heic',
            'image/heif' => 'heif',
            default => null,
        };
    }
}
