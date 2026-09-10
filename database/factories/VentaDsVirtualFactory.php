<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\VentaDsVirtual>
 */
class VentaDsVirtualFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'consorcio_id' => 7,
            'fecha' => fake()->date(),
            'agencia_id' => fake()->unique()->numerify('07#####'),
            'ventas' => fake()->randomFloat(2, 0, 50000),
            'premios_pagados' => fake()->randomFloat(2, 0, 50000),
            'proveedor_id' => 2,
            'premios' => fake()->randomFloat(2, 0, 50000),
            'proveedor_nombre' => 'DS Virtual',
        ];
    }
}
