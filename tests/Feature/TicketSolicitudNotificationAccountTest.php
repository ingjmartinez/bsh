<?php

namespace Tests\Feature;

use App\Http\Controllers\TicketSolicitudController;
use App\Models\ChatbotSession;
use App\Models\TicketSolicitud;
use App\Models\User;
use App\Services\ChatChannelService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use ReflectionMethod;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class TicketSolicitudNotificationAccountTest extends TestCase
{
    public function test_solo_superadmin_puede_gestionar_un_ticket_tomado_por_otro_usuario(): void
    {
        $controller = new TicketSolicitudController($this->createMock(ChatChannelService::class));
        $ticket = new TicketSolicitud(['tomado_por_id' => 2]);
        $method = new ReflectionMethod($controller, 'isTomadoPorOtroUsuario');
        $user = User::factory()->make();
        $user->id = 1;
        $user->setRelation('roles', collect([new Role(['name' => 'superadmin'])]));
        $this->be($user);

        $this->assertFalse($method->invoke($controller, $ticket));

        $user->setRelation('roles', collect([new Role(['name' => 'admin'])]));

        $this->assertTrue($method->invoke($controller, $ticket));
    }

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

    public function test_prioriza_la_cuenta_predeterminada_de_zender_para_enviar_el_token(): void
    {
        config(['services.whatsapp.default_account' => 'cuenta-principal-zender']);

        ChatbotSession::query()->create([
            'account' => 'cuenta-anterior-del-chat',
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

        $this->assertSame('cuenta-principal-zender', $account);
    }

    public function test_usa_la_cuenta_de_la_sesion_como_respaldo_si_no_hay_cuenta_predeterminada(): void
    {
        config(['services.whatsapp.default_account' => null]);

        ChatbotSession::query()->create([
            'account' => 'cuenta-del-chat',
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

        $this->assertSame('cuenta-del-chat', $account);
    }

    public function test_deja_que_el_proveedor_resuelva_la_cuenta_si_no_hay_una_cuenta_real(): void
    {
        config(['services.whatsapp.default_account' => null]);

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
