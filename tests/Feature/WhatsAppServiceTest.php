<?php

namespace Tests\Feature;

use App\Services\WhatsAppService;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class WhatsAppServiceTest extends TestCase
{
    public function test_no_trata_como_exitoso_un_error_interno_del_proveedor(): void
    {
        config()->set('services.whatsapp.send_endpoint', 'https://wamundo.test/api/send/whatsapp');
        config()->set('services.whatsapp.api_key', 'test-secret');
        config()->set('services.whatsapp.default_account', 'missing-account');
        config()->set('services.whatsapp.verify_ssl', true);

        Http::fake([
            'wamundo.test/*' => Http::response([
                'status' => 404,
                'message' => "WhatsApp account doesn't exist!",
                'data' => false,
            ], 200),
        ]);

        $result = app(WhatsAppService::class)->sendText('18095169172', 'Prueba');

        $this->assertFalse($result['success']);
        $this->assertSame(200, $result['status']);
        $this->assertSame("WhatsApp account doesn't exist!", $result['message']);
    }
}
