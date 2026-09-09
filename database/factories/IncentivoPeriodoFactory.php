<?php

namespace Database\Factories;

use App\Models\IncentivoPeriodo;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<IncentivoPeriodo>
 */
class IncentivoPeriodoFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $fechaInicio = fake()->dateTimeBetween('-1 year', 'now')->modify('first day of this month');

        return [
            'anio' => (int) $fechaInicio->format('Y'),
            'mes' => (int) $fechaInicio->format('n'),
            'fecha_inicio' => $fechaInicio->format('Y-m-d'),
            'fecha_fin' => $fechaInicio->format('Y-m-t'),
            'sistema' => 'Todos',
            'modo_calculo' => 'separado_empresa',
            'tipo_pago_defecto' => 'tramos_60',
            'min_dias_venta' => 1,
            'rangos_pago_por_tipo' => [],
            'terminales_excluidas' => [],
            'resumen' => [],
            'revision' => 1,
        ];
    }
}
