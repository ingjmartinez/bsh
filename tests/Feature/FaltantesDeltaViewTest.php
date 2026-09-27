<?php

namespace Tests\Feature;

use Tests\TestCase;

class FaltantesDeltaViewTest extends TestCase
{
    public function test_debit_and_credit_are_rendered_as_money_with_two_decimals(): void
    {
        $this->withoutMiddleware()
            ->get('/faltantes-delta')
            ->assertOk()
            ->assertSee('minimumFractionDigits: 2', false)
            ->assertSee('maximumFractionDigits: 2', false)
            ->assertSee('formatMoney(item.Debito)', false)
            ->assertSee('formatMoney(item.Credito)', false);
    }
}
