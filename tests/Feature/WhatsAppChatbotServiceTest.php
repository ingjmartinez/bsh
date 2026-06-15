<?php

namespace Tests\Feature;

use App\Models\ChatbotSession;
use App\Models\TicketSolicitud;
use App\Services\WhatsAppChatbotService;
use Illuminate\Support\Carbon;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
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

        Schema::dropIfExists('ticket_solicitudes');
        Schema::create('ticket_solicitudes', function (Blueprint $table): void {
            $table->id();
            $table->string('phone', 32)->index();
            $table->string('categoria', 40)->index();
            $table->string('ticket_numero', 80)->index();
            $table->string('estado', 30)->default('pendiente')->index();
            $table->text('mensaje_original')->nullable();
            $table->text('notas')->nullable();
            $table->text('attachment_url')->nullable();
            $table->string('attachment_message_id', 120)->nullable();
            $table->unsignedBigInteger('procesado_por_id')->nullable();
            $table->timestamp('procesado_at')->nullable();
            $table->timestamps();
        });

        Schema::dropIfExists('agencias');
        Schema::create('agencias', function (Blueprint $table): void {
            $table->id();
            $table->string('terminal', 25)->nullable();
            $table->unsignedTinyInteger('estatus')->default(1);
            $table->timestamps();
        });

        DB::table('agencias')->insert([
            [
                'terminal' => '07068888',
                'estatus' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'terminal' => 'TERM-123',
                'estatus' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }

    protected function tearDown(): void
    {
        Schema::dropIfExists('agencias');
        Schema::dropIfExists('chatbot_sessions');
        Schema::dropIfExists('ticket_solicitudes');

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

    public function test_opcion_uno_en_selector_de_sistema_muestra_menu_aunque_exista_token_pendiente(): void
    {
        TicketSolicitud::create([
            'phone' => '8095550110',
            'categoria' => TicketSolicitud::CATEGORIA_PAGAR,
            'ticket_numero' => '07068888',
            'estado' => TicketSolicitud::ESTADO_TOKEN_ENVIADO,
            'mensaje_original' => 'Pagar ticket: 07068888',
        ]);

        $service = new WhatsAppChatbotService();

        $service->handleIncoming('8095550110', 'hola');
        $menu = $service->handleIncoming('8095550110', '1');

        $this->assertStringContainsString('1-Consultar horario de servicio', $menu['reply']);
        $this->assertStringContainsString('6-Reportar averia', $menu['reply']);
        $this->assertSame('consulta_hora_menu', $menu['session']->step);
        $this->assertSame([
            'sistema' => 'real',
            'label' => 'Real',
        ], $menu['session']->context);
        $this->assertSame(TicketSolicitud::ESTADO_TOKEN_ENVIADO, TicketSolicitud::first()->estado);
    }

    public function test_hola_en_sesion_abierta_pide_confirmar_cierre_y_puede_continuar(): void
    {
        $service = new WhatsAppChatbotService();

        $service->handleIncoming('8095550106', 'hola');
        $service->handleIncoming('8095550106', '1');
        $service->handleIncoming('8095550106', '3');

        $confirmacion = $service->handleIncoming('8095550106', 'hola');

        $this->assertStringContainsString('Quieres cerrar la sesion actual o retomar donde te quedaste', $confirmacion['reply']);
        $this->assertStringContainsString('2- Retomar', $confirmacion['reply']);
        $this->assertSame('confirmar_cierre_sesion', $confirmacion['session']->step);

        $continuar = $service->handleIncoming('8095550106', '2');

        $this->assertStringContainsString('Retomamos tu solicitud donde la dejaste', $continuar['reply']);
        $this->assertStringContainsString('Estas creando una solicitud de Pagar ticket', $continuar['reply']);
        $this->assertStringContainsString('Indica el codigo del terminal', $continuar['reply']);
        $this->assertSame('ticket_numero', $continuar['session']->step);
        $this->assertSame('pagar_ticket', $continuar['session']->context['categoria']);
    }

    public function test_hola_en_sesion_abierta_retoma_ticket_pidiendo_imagen_del_terminal_actual(): void
    {
        $service = new WhatsAppChatbotService();

        $service->handleIncoming('8095550108', 'hola');
        $service->handleIncoming('8095550108', '1');
        $service->handleIncoming('8095550108', '3');
        $service->handleIncoming('8095550108', '07068888');
        $service->handleIncoming('8095550108', 'hola');

        $retomar = $service->handleIncoming('8095550108', '2');

        $this->assertStringContainsString('Retomamos tu solicitud donde la dejaste', $retomar['reply']);
        $this->assertStringContainsString('Pagar ticket', $retomar['reply']);
        $this->assertStringContainsString('07068888', $retomar['reply']);
        $this->assertStringContainsString('Envia la imagen del comprobante', $retomar['reply']);
        $this->assertSame('ticket_imagen', $retomar['session']->step);
    }

    public function test_real_rechaza_terminal_inexistente_en_ticket_y_mantiene_el_paso(): void
    {
        $service = new WhatsAppChatbotService();

        $service->handleIncoming('8095550111', 'hola');
        $service->handleIncoming('8095550111', '1');
        $service->handleIncoming('8095550111', '3');

        $reply = $service->handleIncoming('8095550111', '999999');

        $this->assertSame('Ese id no existe, por favor escribir el id de tu agencia.', $reply['reply']);
        $this->assertSame('ticket_numero', $reply['session']->step);
        $this->assertArrayNotHasKey('ticket_numero', $reply['session']->context);
    }

    public function test_real_rechaza_terminal_inexistente_en_averia_y_mantiene_el_paso(): void
    {
        $service = new WhatsAppChatbotService();

        $service->handleIncoming('8095550112', 'hola');
        $service->handleIncoming('8095550112', '1');
        $service->handleIncoming('8095550112', '6');
        $service->handleIncoming('8095550112', '1');

        $reply = $service->handleIncoming('8095550112', '999999');

        $this->assertSame('Ese id no existe, por favor escribir el id de tu agencia.', $reply['reply']);
        $this->assertSame('servicios_generales_terminal', $reply['session']->step);
        $this->assertArrayNotHasKey('terminal_codigo', $reply['session']->context);
    }

    public function test_hola_en_sesion_abierta_puede_cerrar_sesion(): void
    {
        $service = new WhatsAppChatbotService();

        $service->handleIncoming('8095550107', 'hola');
        $service->handleIncoming('8095550107', '1');
        $service->handleIncoming('8095550107', '3');
        $service->handleIncoming('8095550107', 'hola');

        $cerrar = $service->handleIncoming('8095550107', '1');

        $this->assertStringContainsString('Sesion cerrada correctamente', $cerrar['reply']);
        $this->assertSame('inicio', $cerrar['session']->step);
        $this->assertSame([], $cerrar['session']->context);
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

    public function test_respuesta_token_funciono_marca_ticket_pagado_para_cierre_manual(): void
    {
        $ticket = TicketSolicitud::create([
            'phone' => '8095550104',
            'categoria' => TicketSolicitud::CATEGORIA_PAGAR,
            'ticket_numero' => '07068888',
            'estado' => TicketSolicitud::ESTADO_TOKEN_ENVIADO,
            'mensaje_original' => 'Pagar ticket: 07068888',
        ]);

        $reply = (new WhatsAppChatbotService())->handleIncoming('8095550104', '1');

        $ticket->refresh();

        $this->assertStringContainsString('Ticket pagado Por otra Terminal', $reply['reply']);
        $this->assertSame(TicketSolicitud::ESTADO_TICKET_PAGADO, $ticket->estado);
        $this->assertNull($ticket->procesado_por_id);
        $this->assertNull($ticket->procesado_at);
        $this->assertStringContainsString('Cliente confirmo que el token funciono', (string) $ticket->notas);
    }

    public function test_respuesta_token_funciono_se_procesa_aunque_la_sesion_este_expirada(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-06-12 10:00:00'));

        ChatbotSession::create([
            'account' => 'default',
            'phone' => '8095550109',
            'step' => 'consulta_hora_menu',
            'context' => [],
            'last_interaction_at' => now()->subMinutes(5),
            'message_count' => 3,
        ]);

        $ticket = TicketSolicitud::create([
            'phone' => '8095550109',
            'categoria' => TicketSolicitud::CATEGORIA_PAGAR,
            'ticket_numero' => '07068888',
            'estado' => TicketSolicitud::ESTADO_TOKEN_ENVIADO,
            'mensaje_original' => 'Pagar ticket: 07068888',
        ]);

        $reply = (new WhatsAppChatbotService())->handleIncoming('8095550109', '1');

        $ticket->refresh();

        $this->assertStringContainsString('Ticket pagado Por otra Terminal', $reply['reply']);
        $this->assertStringNotContainsString('Consultar horario de servicio', $reply['reply']);
        $this->assertSame(TicketSolicitud::ESTADO_TICKET_PAGADO, $ticket->estado);
        $this->assertSame('inicio', $reply['session']->step);

        Carbon::setTestNow();
    }

    public function test_respuesta_token_no_funciono_marca_estado_para_nuevo_token(): void
    {
        $ticket = TicketSolicitud::create([
            'phone' => '8095550105',
            'categoria' => TicketSolicitud::CATEGORIA_PAGAR,
            'ticket_numero' => '07068888',
            'estado' => TicketSolicitud::ESTADO_TOKEN_ENVIADO,
            'mensaje_original' => 'Pagar ticket: 07068888',
            'procesado_por_id' => 1,
            'procesado_at' => now(),
        ]);

        $reply = (new WhatsAppChatbotService())->handleIncoming('8095550105', '2');

        $ticket->refresh();

        $this->assertStringContainsString('Token No Funciono', $reply['reply']);
        $this->assertSame(TicketSolicitud::ESTADO_TOKEN_NO_FUNCIONO, $ticket->estado);
        $this->assertNull($ticket->procesado_por_id);
        $this->assertNull($ticket->procesado_at);
        $this->assertStringContainsString('Cliente indico que el token no funciono', (string) $ticket->notas);
    }
}
