<?php

namespace Database\Factories;

use App\Models\IncentivoPeriodo;
use App\Models\IncentivoPeriodoDetalle;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<IncentivoPeriodoDetalle>
 */
class IncentivoPeriodoDetalleFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $incentivo = fake()->numberBetween(500, 10000);

        return [
            'incentivo_periodo_id' => IncentivoPeriodo::factory(),
            'cedula' => fake()->unique()->numerify('###########'),
            'empleadoid' => (string) fake()->numberBetween(1, 99999),
            'nombre' => fake()->name(),
            'empresa' => fake()->randomElement(['Grupo Joselito', 'Negosur']),
            'ventas_ultimo_mes' => fake()->numberBetween(100001, 1000000),
            'ventas_mes_actual' => fake()->numberBetween(100001, 1000000),
            'dias_ventas' => fake()->numberBetween(1, 31),
            'horas_total' => fake()->randomFloat(2, 1, 300),
            'incentivo_generado' => $incentivo,
            'monto_pagado' => $incentivo,
            'monto_no_pagado' => 0,
            'estado' => 'pagado',
            'motivos' => [],
            'tipos_pago_detalle' => [],
        ];
    }
}
