<?php

namespace App\Http\Controllers;

use App\Services\WhatsAppChatbotService;
use App\Services\WhatsAppService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class WhatsAppWebhookController extends Controller
{
    public function handle(
        Request $request,
        WhatsAppChatbotService $chatbot,
        WhatsAppService $whatsApp,
        ?string $routeAccount = null
    ): JsonResponse {
        try {
            $payload = $request->all();

            Log::debug('WhatsApp webhook payload recibido', [
                'payload' => $payload,
            ]);

            $webhookToken = config('services.whatsapp.webhook_token');
            $receivedToken = $request->bearerToken()
                ?? $request->header('X-Webhook-Token')
                ?? $request->input('token')
                ?? $request->input('webhook_token');

            if ($webhookToken && !hash_equals((string) $webhookToken, (string) $receivedToken)) {
                Log::warning('WhatsApp webhook token invalido');

                return response()->json([
                    'status' => 'unauthorized',
                    'message' => 'invalid_token',
                ], 401);
            }

            $data = $payload['data'] ?? $payload;
            $phone = (string) ($data['phone'] ?? $data['from'] ?? $data['sender'] ?? '');
            $message = (string) ($data['message'] ?? $data['text'] ?? $data['body'] ?? '');
            $inboundAccount = $this->extractInboundAccount($payload, (array) $data);
            $sendAccount = $this->extractSendAccount($payload, (array) $data, $routeAccount);
            $sessionAccount = $inboundAccount !== '' ? $inboundAccount : $sendAccount;

            Log::debug('WhatsApp webhook datos extraidos', [
                'phone' => $phone,
                'message' => $message,
                'inbound_account' => $inboundAccount,
                'send_account' => $sendAccount,
                'session_account' => $sessionAccount,
                'route_account' => $routeAccount,
            ]);

            if (trim($phone) === '' || trim($message) === '') {
                Log::warning('WhatsApp webhook datos faltantes', [
                    'has_phone' => trim($phone) !== '',
                    'has_message' => trim($message) !== '',
                ]);

                return response()->json([
                    'status' => 'missing_data',
                    'message' => 'missing_data',
                ], 422);
            }

            if ($sessionAccount === '') {
                Log::warning('WhatsApp webhook sin cuenta identificada', [
                    'phone' => $phone,
                    'route_account' => $routeAccount,
                    'data_keys' => array_keys((array) $data),
                ]);

                return response()->json([
                    'status' => 'missing_account',
                    'message' => 'missing_account',
                ], 422);
            }

            if (!$this->accountAllowed($inboundAccount)) {
                Log::warning('WhatsApp webhook cuenta no permitida para este sistema', [
                    'phone' => $phone,
                    'inbound_account' => $inboundAccount,
                    'send_account' => $sendAccount,
                    'route_account' => $routeAccount,
                    'allowed_accounts' => config('services.whatsapp.allowed_accounts', []),
                ]);

                return response()->json([
                    'status' => 'ignored_account',
                    'sent' => false,
                ]);
            }

            Log::debug('WhatsApp webhook inicio chatbot', [
                'phone' => $phone,
                'session_account' => $sessionAccount,
            ]);

            $chatbotResult = $chatbot->handleIncoming($phone, $message, $sessionAccount);
            $reply = (string) ($chatbotResult['reply'] ?? '');

            Log::debug('WhatsApp webhook respuesta chatbot', [
                'phone' => $phone,
                'reply' => $reply,
                'session_id' => $chatbotResult['session']->id ?? null,
            ]);

            if (trim($reply) === '') {
                return response()->json([
                    'status' => 'no_reply',
                    'sent' => false,
                ]);
            }

            $sendResult = $whatsApp->sendText($phone, $reply, $sendAccount ?: null);

            Log::debug('WhatsApp webhook envio', [
                'phone' => $phone,
                'inbound_account' => $inboundAccount,
                'send_account' => $sendAccount,
                'sent' => (bool) ($sendResult['success'] ?? false),
                'status' => $sendResult['status'] ?? null,
            ]);

            return response()->json([
                'status' => 'ok',
                'sent' => (bool) ($sendResult['success'] ?? false),
            ]);
        } catch (\Throwable $e) {
            Log::error('WhatsApp webhook error', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function verify(Request $request, ?string $routeAccount = null): JsonResponse
    {
        $challenge = $request->input('challenge')
            ?? $request->input('hub_challenge')
            ?? $request->input('hub.challenge');

        return response()->json([
            'challenge' => $challenge,
            'account' => $routeAccount,
        ]);
    }

    private function extractInboundAccount(array $payload, array $data): string
    {
        $account = $data['wid']
            ?? $data['account']
            ?? $data['unique']
            ?? $data['account_unique']
            ?? $data['wa_account']
            ?? $payload['wid']
            ?? $payload['account']
            ?? $payload['unique']
            ?? '';

        if (is_array($account)) {
            $account = $account['unique']
                ?? $account['id']
                ?? $account['account']
                ?? '';
        }

        return trim((string) $account);
    }

    private function extractSendAccount(array $payload, array $data, ?string $routeAccount): string
    {
        $account = $data['account']
            ?? $data['unique']
            ?? $data['account_unique']
            ?? $data['wa_account']
            ?? $payload['account']
            ?? $payload['unique']
            ?? $routeAccount
            ?? config('services.whatsapp.default_account')
            ?? '';

        if (is_array($account)) {
            $account = $account['unique']
                ?? $account['id']
                ?? $account['account']
                ?? '';
        }

        return trim((string) $account);
    }

    private function accountAllowed(string $inboundAccount): bool
    {
        $allowedAccounts = config('services.whatsapp.allowed_accounts', []);

        if (empty($allowedAccounts)) {
            return true;
        }

        if ($inboundAccount === '') {
            return false;
        }

        return in_array($inboundAccount, $allowedAccounts, true);
    }
}
