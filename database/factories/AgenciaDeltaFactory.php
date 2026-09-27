<?php

namespace Database\Factories;

use App\Models\AgenciaDelta;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AgenciaDelta>
 */
class AgenciaDeltaFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'agencia' => fake()->unique()->numerify('D####'),
            'codigo' => fake()->numerify('C####'),
            'nombre_agencia' => fake()->company(),
            'terminal' => fake()->unique()->numerify('########'),
            'sistema' => 'delta',
            'empresa' => 'Delta',
            'estatus' => 1,
            'aplica_incentivo' => true,
        ];
    }
}
