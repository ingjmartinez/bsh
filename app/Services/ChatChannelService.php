<?php

namespace App\Services;

class ChatChannelService
{
    public function __construct(
        private readonly WhatsAppService $whatsApp,
        private readonly TelegramService $telegram,
    ) {}

    public function sendText(
        string $channel,
        string $recipient,
        string $message,
        ?string $account = null
    ): array {
        if ($channel === 'telegram') {
            return $this->telegram->sendText($recipient, $message);
        }

        return $this->whatsApp->sendText($recipient, $message, $account);
    }
}
