<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ReporteFaltantesAlertaConfiguracion>
 */
class ReporteFaltantesAlertaConfiguracionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'minimo_faltantes' => fake()->numberBetween(1, 10),
            'maximo_monto' => fake()->randomFloat(2, 100, 10000),
            'actualizado_por' => null,
        ];
    }
}
