<?php

namespace Database\Factories;

use App\Models\Empleado;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\MovimientoUsuario>
 */
class MovimientoUsuarioFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'empleado_id' => Empleado::query()->inRandomOrder()->value('id'),
            'cedula' => fake()->numerify('###########'),
            'nombre_empleado' => fake()->name(),
            'terminal_origen' => fake()->numerify('######'),
            'centro_costo_origen' => fake()->numerify('####'),
            'grupo_origen' => fake()->word(),
            'terminal_destino' => fake()->numerify('######'),
            'centro_costo_destino' => fake()->numerify('####'),
            'grupo_destino' => fake()->word(),
            'observacion' => fake()->optional()->sentence(),
        ];
    }
}
