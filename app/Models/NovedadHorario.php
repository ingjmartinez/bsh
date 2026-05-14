<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NovedadHorario extends Model
{
    protected $table = 'novedades_horario';

    protected $fillable = [
        'terminal',
        'nombre_agencia',
        'ruta',
        'nombre_empleado',
        'cedula',
        'fecha',
        'primer_login',
        'ultimo_login',
        'horas_acumuladas',
    ];

    protected $casts = [
        'fecha' => 'date',
        'primer_login' => 'datetime',
        'ultimo_login' => 'datetime',
        'horas_acumuladas' => 'decimal:2',
    ];
}
