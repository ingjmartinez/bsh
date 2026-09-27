<?php

namespace Tests\Feature;

use Tests\TestCase;

class VentasDeltaViewTest extends TestCase
{
    public function test_report_uses_ventas_virtual_as_the_datatable_heading(): void
    {
        $this->withoutMiddleware()
            ->get('/ventas_delta')
            ->assertOk()
            ->assertSee('Ventas Virtual')
            ->assertSee('Premios Virtual.')
            ->assertDontSee('Ventas No Trad.')
            ->assertDontSee('Premios No Trad.')
            ->assertSee('item.VentasNoTrad', false)
            ->assertSee('item.PremiosPagadosNoTrad', false);
    }
}
