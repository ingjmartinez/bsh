<?php

namespace Database\Factories;

use App\Models\IncentivoTerminalTipoPago;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<IncentivoTerminalTipoPago>
 */
class IncentivoTerminalTipoPagoFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'sistema' => fake()->randomElement(IncentivoTerminalTipoPago::SISTEMAS),
            'terminal' => fake()->unique()->numerify('#####'),
            'fecha' => fake()->dateTimeBetween('-1 month', '+1 month')->format('Y-m-d'),
            'tipo_pago' => fake()->randomElement(IncentivoTerminalTipoPago::TIPOS_PAGO),
        ];
    }
}
