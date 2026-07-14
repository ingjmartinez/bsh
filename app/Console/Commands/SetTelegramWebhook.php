<?php

namespace App\Console\Commands;

use App\Services\TelegramService;
use Illuminate\Console\Command;

class SetTelegramWebhook extends Command
{
    protected $signature = 'telegram:webhook:set {--url= : URL HTTPS completa del webhook}';

    protected $description = 'Registra en Telegram el webhook configurado para este CRM.';

    public function handle(TelegramService $telegram): int
    {
        $token = trim((string) config('services.telegram.bot_token'));
        $secret = trim((string) config('services.telegram.webhook_secret'));
        $url = trim((string) ($this->option('url') ?: url('/api/telegram/webhook')));

        if ($token === '' || $secret === '') {
            $this->error('Configura TELEGRAM_BOT_TOKEN y TELEGRAM_WEBHOOK_SECRET antes de registrar el webhook.');

            return self::FAILURE;
        }

        if (! str_starts_with(strtolower($url), 'https://')) {
            $this->error("El webhook debe usar una URL publica HTTPS. URL actual: {$url}");

            return self::FAILURE;
        }

        $result = $telegram->setWebhook($url);

        if (! ($result['success'] ?? false)) {
            $this->error('Telegram rechazo el webhook: '.($result['message'] ?? 'error desconocido'));

            return self::FAILURE;
        }

        $this->info("Webhook de Telegram registrado: {$url}");

        return self::SUCCESS;
    }
}
