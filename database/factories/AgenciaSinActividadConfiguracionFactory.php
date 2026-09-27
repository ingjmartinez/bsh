<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\AgenciaSinActividadConfiguracion>
 */
class AgenciaSinActividadConfiguracionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'porcentaje_minimo' => fake()->randomFloat(2, 70, 100),
        ];
    }
}
