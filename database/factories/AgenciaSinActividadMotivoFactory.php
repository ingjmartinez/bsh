<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\AgenciaSinActividadMotivo>
 */
class AgenciaSinActividadMotivoFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'sistema' => fake()->randomElement(['lotobet', 'lotedom', 'delta', 'ds_virtual']),
            'terminal' => fake()->unique()->numerify('######'),
            'motivo' => fake()->randomElement(['Sin internet', 'Terminal dañada', 'Agencia cerrada']),
            'observacion' => fake()->optional()->sentence(),
        ];
    }
}
