<?php

use App\Http\Controllers\TelegramWebhookController;
use App\Http\Controllers\WhatsAppWebhookController;
use Illuminate\Support\Facades\Route;

Route::post('/whatsapp/webhook/{account?}', [WhatsAppWebhookController::class, 'handle']);
Route::get('/whatsapp/webhook/{account?}', [WhatsAppWebhookController::class, 'verify']);
Route::post('/telegram/webhook', [TelegramWebhookController::class, 'handle']);
