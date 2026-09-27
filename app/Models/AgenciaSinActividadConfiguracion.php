<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AgenciaSinActividadConfiguracion extends Model
{
    /** @use HasFactory<\Database\Factories\AgenciaSinActividadConfiguracionFactory> */
    use HasFactory;

    protected $table = 'agencia_sin_actividad_configuraciones';

    protected $fillable = [
        'porcentaje_minimo',
        'actualizado_por',
    ];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'porcentaje_minimo' => 'decimal:2',
        ];
    }
}
