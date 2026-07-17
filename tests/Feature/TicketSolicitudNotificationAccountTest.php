<?php

namespace Tests\Feature;

use App\Http\Controllers\TicketSolicitudController;
use App\Models\ChatbotSession;
use App\Models\TicketSolicitud;
use App\Services\ChatChannelService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use ReflectionMethod;
use Tests\TestCase;

class TicketSolicitudNotificationAccountTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Schema::create('chatbot_sessions', function (Blueprint $table): void {
            $table->id();
            $table->string('account', 120)->default('default');
            $table->string('phone', 32);
            $table->string('channel', 20)->default('whatsapp');
            $table->timestamp('last_interaction_at')->nullable();
            $table->timestamps();
        });
    }

    protected function tearDown(): void
    {
        Schema::dropIfExists('chatbot_sessions');

        parent::tearDown();
    }

    public function test_usa_la_cuenta_que_recibio_el_chat_para_enviar_el_token(): void
    {
        ChatbotSession::query()->create([
            'account' => 'cuenta-del-chat',
            'phone' => '18095169172',
            'channel' => 'whatsapp',
            'last_interaction_at' => now(),
        ]);

        $controller = new TicketSolicitudController($this->createMock(ChatChannelService::class));
        $ticket = new TicketSolicitud([
            'phone' => '+1 (809) 516-9172',
            'source_channel' => 'whatsapp',
        ]);
        $method = new ReflectionMethod($controller, 'notificationAccount');

        $account = $method->invoke($controller, $ticket);

        $this->assertSame('cuenta-del-chat', $account);
    }

    public function test_deja_que_el_proveedor_use_el_respaldo_si_la_sesion_no_tiene_cuenta_real(): void
    {
        ChatbotSession::query()->create([
            'account' => 'default',
            'phone' => '18095169172',
            'channel' => 'whatsapp',
            'last_interaction_at' => now(),
        ]);

        $controller = new TicketSolicitudController($this->createMock(ChatChannelService::class));
        $ticket = new TicketSolicitud([
            'phone' => '18095169172',
            'source_channel' => 'whatsapp',
        ]);
        $method = new ReflectionMethod($controller, 'notificationAccount');

        $account = $method->invoke($controller, $ticket);

        $this->assertNull($account);
    }
}
