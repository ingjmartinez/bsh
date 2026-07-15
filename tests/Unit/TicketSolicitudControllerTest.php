<?php

namespace Tests\Unit;

use App\Http\Controllers\TicketSolicitudController;
use App\Models\TicketSolicitud;
use App\Services\ChatChannelService;
use PHPUnit\Framework\TestCase;
use ReflectionMethod;

class TicketSolicitudControllerTest extends TestCase
{
    public function test_usa_destinatario_con_prefijo_para_notificaciones_de_wamundo(): void
    {
        $controller = new TicketSolicitudController($this->createMock(ChatChannelService::class));
        $ticket = new TicketSolicitud([
            'phone' => '+1 (809) 516-9172',
            'source_channel' => 'whatsapp',
        ]);
        $method = new ReflectionMethod($controller, 'notificationRecipient');

        $recipient = $method->invoke($controller, $ticket);

        $this->assertSame('+18095169172', $recipient);
    }
}
