<?php

namespace Tests\Feature;

use App\Models\ChatbotSession;
use App\Services\TelegramService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class TelegramWebhookControllerTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config([
            'services.telegram.bot_token' => 'test-token',
            'services.telegram.webhook_secret' => 'test-secret',
            'services.telegram.bot_username' => 'bsh_test_bot',
            'services.telegram.api_url' => 'https://api.telegram.org',
        ]);

        Cache::flush();

        Schema::dropIfExists('chatbot_sessions');
        Schema::create('chatbot_sessions', function (Blueprint $table): void {
            $table->id();
            $table->string('account', 120)->default('default');
            $table->string('phone', 32);
            $table->string('channel', 20)->default('whatsapp')->index();
            $table->string('channel_recipient', 120)->nullable()->index();
            $table->string('step', 80)->default('inicio')->index();
            $table->json('context')->nullable();
            $table->text('last_message')->nullable();
            $table->timestamp('last_interaction_at')->nullable()->index();
            $table->unsignedInteger('message_count')->default(0);
            $table->timestamps();
            $table->unique(['account', 'phone']);
            $table->unique(['channel', 'channel_recipient']);
        });
    }

    protected function tearDown(): void
    {
        Cache::flush();
        Schema::dropIfExists('chatbot_sessions');

        parent::tearDown();
    }

    public function test_rechaza_webhook_sin_secreto_valido(): void
    {
        $this->postJson('/api/telegram/webhook', ['update_id' => 100])
            ->assertUnauthorized()
            ->assertJson(['status' => 'unauthorized']);
    }

    public function test_solicita_contacto_antes_de_iniciar_el_chatbot(): void
    {
        $this->fakeSuccessfulTelegram();

        $this->withHeader('X-Telegram-Bot-Api-Secret-Token', 'test-secret')
            ->postJson('/api/telegram/webhook', $this->messageUpdate(101, ['text' => '/start']))
            ->assertOk()
            ->assertJson(['status' => 'phone_required']);

        Http::assertSent(function ($request): bool {
            $keyboard = $request->data()['reply_markup']['keyboard'] ?? [];

            return str_ends_with($request->url(), '/sendMessage')
                && ($request->data()['chat_id'] ?? null) === '900100200'
                && ($keyboard[0][0]['request_contact'] ?? false) === true;
        });
    }

    public function test_contacto_propio_crea_sesion_telegram_y_muestra_menu_inicial(): void
    {
        $this->fakeSuccessfulTelegram();

        $payload = $this->messageUpdate(102, [
            'contact' => [
                'phone_number' => '+1 (809) 555-0199',
                'first_name' => 'Usuario',
                'user_id' => 700800900,
            ],
        ]);

        $this->withHeader('X-Telegram-Bot-Api-Secret-Token', 'test-secret')
            ->postJson('/api/telegram/webhook', $payload)
            ->assertOk()
            ->assertJson(['status' => 'ok']);

        $session = ChatbotSession::firstOrFail();

        $this->assertSame('18095550199', $session->phone);
        $this->assertSame('telegram', $session->channel);
        $this->assertSame('900100200', $session->channel_recipient);
        $this->assertSame('seleccion_sistema', $session->step);

        Http::assertSent(fn ($request): bool => str_ends_with($request->url(), '/sendMessage')
            && str_contains((string) ($request->data()['text'] ?? ''), '1- Real')
            && ($request->data()['reply_markup']['remove_keyboard'] ?? false) === true
        );
    }

    public function test_rechaza_contacto_de_otra_persona(): void
    {
        $this->fakeSuccessfulTelegram();

        $payload = $this->messageUpdate(103, [
            'contact' => [
                'phone_number' => '+1 809 555 0100',
                'first_name' => 'Otra persona',
                'user_id' => 111222333,
            ],
        ]);

        $this->withHeader('X-Telegram-Bot-Api-Secret-Token', 'test-secret')
            ->postJson('/api/telegram/webhook', $payload)
            ->assertOk()
            ->assertJson(['status' => 'invalid_contact']);

        $this->assertSame(0, ChatbotSession::count());
    }

    public function test_descarga_imagen_sin_persistir_un_enlace_con_el_token(): void
    {
        Storage::fake('public');
        Http::fake(function ($request) {
            if (str_contains(strtolower($request->url()), 'getfile')) {
                return Http::response([
                    'ok' => true,
                    'result' => ['file_path' => 'photos/comprobante.jpg'],
                ]);
            }

            return Http::response('imagen-binaria', 200, ['Content-Type' => 'image/jpeg']);
        });

        $file = app(TelegramService::class)->downloadFile('telegram-file-id', 'comprobante.jpg', 'image/jpeg');

        $urls = collect(Http::recorded())->map(fn ($record) => $record[0]->url())->all();
        $this->assertSame([
            'https://api.telegram.org/bottest-token/getFile',
            'https://api.telegram.org/file/bottest-token/photos/comprobante.jpg',
        ], $urls);
        $this->assertNotNull($file);
        $this->assertStringNotContainsString('test-token', $file['url']);
        $this->assertSame('jpg', $file['extension']);

        $storagePath = ltrim((string) parse_url($file['url'], PHP_URL_PATH), '/');
        Storage::disk('public')->assertExists(substr($storagePath, strlen('storage/')));
    }

    private function fakeSuccessfulTelegram(): void
    {
        Http::fake([
            'https://api.telegram.org/bottest-token/*' => Http::response([
                'ok' => true,
                'result' => ['message_id' => 1],
            ]),
        ]);
    }

    private function messageUpdate(int $updateId, array $message): array
    {
        return [
            'update_id' => $updateId,
            'message' => array_merge([
                'message_id' => $updateId,
                'date' => now()->timestamp,
                'from' => [
                    'id' => 700800900,
                    'is_bot' => false,
                    'first_name' => 'Usuario',
                ],
                'chat' => [
                    'id' => 900100200,
                    'first_name' => 'Usuario',
                    'type' => 'private',
                ],
            ], $message),
        ];
    }
}
