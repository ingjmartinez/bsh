<?php

namespace Tests\Feature;

use App\Models\ChatbotSession;
use App\Services\WhatsAppChatbotService;
use Illuminate\Support\Carbon;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class WhatsAppChatbotServiceTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Schema::dropIfExists('chatbot_sessions');
        Schema::create('chatbot_sessions', function (Blueprint $table): void {
            $table->id();
            $table->string('account', 120)->default('default');
            $table->string('phone', 32);
            $table->string('step', 80)->default('inicio')->index();
            $table->json('context')->nullable();
            $table->text('last_message')->nullable();
            $table->timestamp('last_interaction_at')->nullable()->index();
            $table->unsignedInteger('message_count')->default(0);
            $table->timestamps();
            $table->unique(['account', 'phone']);
        });
    }

    protected function tearDown(): void
    {
        Schema::dropIfExists('chatbot_sessions');

        parent::tearDown();
    }

    public function test_hola_muestra_selector_de_sistema_y_luego_menu_actual(): void
    {
        $service = new WhatsAppChatbotService();

        $greeting = $service->handleIncoming('+1 (809) 555-0101', 'hola');

        $this->assertStringContainsString('1- Real', $greeting['reply']);
        $this->assertStringContainsString('2- Delta', $greeting['reply']);
        $this->assertStringContainsString('3- Lotedom', $greeting['reply']);
        $this->assertSame('seleccion_sistema', $greeting['session']->step);

        $menu = $service->handleIncoming('+1 (809) 555-0101', '3');

        $this->assertStringContainsString('1-Consultar horario de servicio', $menu['reply']);
        $this->assertStringContainsString('6-Reportar averia', $menu['reply']);
        $this->assertSame('consulta_hora_menu', $menu['session']->step);
        $this->assertSame([
            'sistema' => 'lotedom',
            'label' => 'Lotedom',
        ], $menu['session']->context);
    }

    public function test_opcion_invalida_de_sistema_repite_selector(): void
    {
        $service = new WhatsAppChatbotService();

        $service->handleIncoming('8095550102', 'hola');
        $reply = $service->handleIncoming('8095550102', '9');

        $this->assertStringContainsString('Selecciona el sistema', $reply['reply']);
        $this->assertSame('seleccion_sistema', ChatbotSession::first()->step);
    }

    public function test_rechaza_foto_con_fecha_de_ayer_antes_de_registrar_ticket(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-06-12 10:00:00'));

        $service = new WhatsAppChatbotService();

        $service->handleIncoming('8095550103', 'hola');
        $service->handleIncoming('8095550103', '1');
        $service->handleIncoming('8095550103', '3');
        $service->handleIncoming('8095550103', 'TERM-123');

        $reply = $service->handleIncoming('8095550103', '', null, [
            'attachment_url' => 'https://example.com/foto.jpg',
            'attachment_timestamp' => '2026-06-11 18:30:00',
        ]);

        $this->assertSame('Foto no valida. Debes enviar una foto tomada hoy.', $reply['reply']);
        $this->assertSame('ticket_imagen', $reply['session']->step);

        Carbon::setTestNow();
    }
}
