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
        WhatsAppService $whatsApp
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
            $account = (string) ($data['account'] ?? $data['unique'] ?? '');

            Log::debug('WhatsApp webhook datos extraidos', [
                'phone' => $phone,
                'message' => $message,
                'account' => $account,
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

            Log::debug('WhatsApp webhook inicio chatbot', [
                'phone' => $phone,
            ]);

            $chatbotResult = $chatbot->handleIncoming($phone, $message);
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

            $sendResult = $whatsApp->sendText($phone, $reply, $account ?: null);

            Log::debug('WhatsApp webhook envio', [
                'phone' => $phone,
                'account' => $account,
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

    public function verify(Request $request): JsonResponse
    {
        $challenge = $request->input('challenge')
            ?? $request->input('hub_challenge')
            ?? $request->input('hub.challenge');

        return response()->json([
            'challenge' => $challenge,
        ]);
    }
}
