<?php

namespace App\Console\Commands;

use App\Models\ChatbotSession;
use App\Services\WhatsAppChatbotService;
use App\Services\WhatsAppService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class ExpireInactiveChatbotSessions extends Command
{
    protected $signature = 'chatbot:sessions:expire';

    protected $description = 'Cierra las sesiones inactivas del chatbot de WhatsApp.';

    public function handle(WhatsAppService $whatsApp): int
    {
        $cutoff = now()->subMinute();
        $oldestPendingClose = now()->subMinutes(10);
        $expiredCount = 0;

        ChatbotSession::query()
            ->whereNotNull('last_interaction_at')
            ->where('last_interaction_at', '<', $cutoff)
            ->where('last_interaction_at', '>=', $oldestPendingClose)
            ->orderBy('id')
            ->chunkById(100, function ($sessions) use ($whatsApp, &$expiredCount): void {
                foreach ($sessions as $session) {
                    $result = $whatsApp->sendText(
                        $session->phone,
                        WhatsAppChatbotService::sessionClosedMessage(),
                        $session->account
                    );

                    Log::debug('WhatsApp chatbot sesion cerrada por inactividad', [
                        'session_id' => $session->id,
                        'phone' => $session->phone,
                        'account' => $session->account,
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
