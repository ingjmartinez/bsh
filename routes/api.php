<?php

use App\Http\Controllers\WhatsAppWebhookController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::post('/whatsapp/webhook/{account?}', [WhatsAppWebhookController::class, 'handle']);
Route::get('/whatsapp/webhook/{account?}', [WhatsAppWebhookController::class, 'verify']);
