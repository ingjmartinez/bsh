<?php

namespace App\Console\Commands;

use App\Models\ChatbotSession;
use App\Services\ChatChannelService;
use App\Services\WhatsAppChatbotService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class ExpireInactiveChatbotSessions extends Command
{
    protected $signature = 'chatbot:sessions:expire';

    protected $description = 'Cierra las sesiones inactivas del chatbot en todos los canales.';

    public function handle(ChatChannelService $channels): int
    {
        $cutoff = now()->subMinute();
        $oldestPendingClose = now()->subMinutes(10);
        $expiredCount = 0;

        ChatbotSession::query()
            ->whereNotNull('last_interaction_at')
            ->where('last_interaction_at', '<', $cutoff)
            ->where('last_interaction_at', '>=', $oldestPendingClose)
            ->orderBy('id')
            ->chunkById(100, function ($sessions) use ($channels, &$expiredCount): void {
                foreach ($sessions as $session) {
                    $channel = (string) ($session->channel ?: 'whatsapp');
                    $recipient = (string) ($session->channel_recipient ?: $session->phone);
                    $result = $channels->sendText(
                        $channel,
                        $recipient,
                        WhatsAppChatbotService::sessionClosedMessage(),
                        $session->account
                    );

                    Log::debug('Chatbot sesion cerrada por inactividad', [
                        'session_id' => $session->id,
                        'phone' => $session->phone,
                        'account' => $session->account,
                        'channel' => $channel,
                        'sent' => (bool) ($result['success'] ?? false),
                        'status' => $result['status'] ?? null,
                    ]);

                    $session->step = 'inicio';
                    $session->context = [];
                    $session->last_interaction_at = null;
                    $session->save();

                    $expiredCount++;
                }
            });

        $this->info("Sesiones cerradas por inactividad: {$expiredCount}");

        return self::SUCCESS;
    }
}
