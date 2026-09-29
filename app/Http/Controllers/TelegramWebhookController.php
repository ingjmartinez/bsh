<?php

namespace App\Http\Controllers;

use App\Models\ChatbotSession;
use App\Services\TelegramService;
use App\Services\WhatsAppChatbotService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class TelegramWebhookController extends Controller
{
    public function handle(
        Request $request,
        WhatsAppChatbotService $chatbot,
        TelegramService $telegram,
    ): JsonResponse {
        $secret = trim((string) config('services.telegram.webhook_secret'));

        if ($secret === '') {
            Log::error('Telegram webhook sin secreto configurado');

            return response()->json(['status' => 'misconfigured'], 503);
        }

        $receivedSecret = (string) $request->header('X-Telegram-Bot-Api-Secret-Token', '');

        if (! hash_equals($secret, $receivedSecret)) {
            return response()->json(['status' => 'unauthorized'], 401);
        }

        $payload = $request->all();
        $updateId = isset($payload['update_id']) ? (string) $payload['update_id'] : null;

        if ($updateId !== null && ! Cache::add('telegram:update:'.$updateId, true, now()->addDay())) {
            return response()->json(['status' => 'duplicate']);
        }

        $message = $payload['message'] ?? null;

        if (! is_array($message)) {
            return response()->json(['status' => 'ignored']);
        }

        $chat = is_array($message['chat'] ?? null) ? $message['chat'] : [];
        $from = is_array($message['from'] ?? null) ? $message['from'] : [];
        $chatId = trim((string) ($chat['id'] ?? ''));
        $userId = trim((string) ($from['id'] ?? ''));

        if ($chatId === '' || ($chat['type'] ?? null) !== 'private') {
            return response()->json(['status' => 'ignored_chat']);
        }

        if (($from['is_bot'] ?? false) === true) {
            return response()->json(['status' => 'ignored_bot']);
        }

        $account = 'telegram:'.(trim((string) config('services.telegram.bot_username')) ?: 'bot');
        $contact = is_array($message['contact'] ?? null) ? $message['contact'] : null;
        $session = ChatbotSession::query()
            ->where('channel', 'telegram')
            ->where('channel_recipient', $chatId)
            ->first();

        if ($contact !== null) {
            $contactUserId = trim((string) ($contact['user_id'] ?? ''));

            if ($contactUserId === '' || $contactUserId !== $userId) {
                $telegram->sendText($chatId, 'Por seguridad, debes compartir tu propio numero usando el boton del bot.');

                return response()->json(['status' => 'invalid_contact']);
            }

            $phone = $session?->phone ?: $this->normalizePhone((string) ($contact['phone_number'] ?? ''));

            if ($phone === '' || strlen((string) $phone) < 8) {
                $telegram->requestPhone($chatId);

                return response()->json(['status' => 'invalid_phone']);
            }

            $result = $chatbot->handleIncoming($phone, 'hola', $account, $this->incomingMetadata($chatId));
            $telegram->removeKeyboard($chatId, (string) ($result['reply'] ?? ''));

            return response()->json(['status' => 'ok']);
        }

        $text = trim((string) ($message['text'] ?? $message['caption'] ?? ''));
        $command = strtolower((string) strtok($text, ' '));

        if ($session === null) {
            $telegram->requestPhone($chatId);

            return response()->json(['status' => 'phone_required']);
        }

        if (in_array($command, ['/start', '/start@'.strtolower((string) config('services.telegram.bot_username'))], true)) {
            $text = 'hola';
        } elseif ($command === '/help') {
            $telegram->sendText($chatId, 'Escribe hola para iniciar o continuar. Durante el proceso responde con el numero de cada opcion.');

            return response()->json(['status' => 'ok']);
        }

        $incoming = $this->incomingMetadata($chatId);
        $file = $this->extractFile($message);

        if ($file !== null) {
            $download = $telegram->downloadFile($file['file_id'], $file['filename'], $file['mime']);

            if ($download !== null) {
                $incoming = array_merge($incoming, [
                    'message_id' => (string) ($message['message_id'] ?? ''),
                    'attachment_url' => $download['url'],
                    'attachment_timestamp' => $message['date'] ?? null,
                    'attachment_extension' => $download['extension'],
                    'attachment_filename' => $download['filename'],
                    'attachment_mime' => $download['mime'],
                    'attachment_type' => $download['type'],
                ]);
            }
        }

        if ($text === '' && empty($incoming['attachment_url'])) {
            $telegram->sendText($chatId, 'Envia un mensaje de texto o una imagen valida.');

            return response()->json(['status' => 'unsupported_message']);
        }

        try {
            $result = $chatbot->handleIncoming((string) $session->phone, $text, $account, $incoming);
            $reply = trim((string) ($result['reply'] ?? ''));

            if ($reply !== '') {
                $telegram->sendText($chatId, $reply);
            }

            return response()->json(['status' => 'ok']);
        } catch (\Throwable $e) {
            Log::error('Telegram webhook error', [
                'update_id' => $updateId,
                'chat_id' => $chatId,
                'message' => $e->getMessage(),
            ]);

            return response()->json(['status' => 'error'], 500);
        }
    }

    private function incomingMetadata(string $chatId): array
    {
        return [
            'channel' => 'telegram',
            'channel_recipient' => $chatId,
        ];
    }

    private function extractFile(array $message): ?array
    {
        $photos = $message['photo'] ?? null;

        if (is_array($photos) && $photos !== []) {
            $photo = end($photos);

            if (is_array($photo) && isset($photo['file_id'])) {
                return [
                    'file_id' => (string) $photo['file_id'],
                    'filename' => 'telegram-photo.jpg',
                    'mime' => 'image/jpeg',
                ];
            }
        }

        $document = is_array($message['document'] ?? null) ? $message['document'] : null;

        if ($document !== null && isset($document['file_id'])) {
            return [
                'file_id' => (string) $document['file_id'],
                'filename' => isset($document['file_name']) ? (string) $document['file_name'] : null,
                'mime' => isset($document['mime_type']) ? (string) $document['mime_type'] : null,
            ];
        }

        return null;
    }

    private function normalizePhone(string $phone): string
    {
        return preg_replace('/\D+/', '', $phone) ?? '';
    }
}
