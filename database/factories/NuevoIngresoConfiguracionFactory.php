<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\NuevoIngresoConfiguracion>
 */
class NuevoIngresoConfiguracionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'dias_habiles' => fake()->numberBetween(1, 20),
        ];
    }
}
