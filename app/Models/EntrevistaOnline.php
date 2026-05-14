<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EntrevistaOnline extends Model
{
    protected $table = 'entrevistas_online';

    protected $fillable = [
        'nombre_completo',
        'edad',
        'telefono',
        'direccion',
        'estado_civil',
        'hijos',
        'estudia_actualmente',
        'licencia_vehiculo',
        'laborando_actualmente',
        'ultimo_empleo_posicion',
        'tiempo',
        'salario',
        'fecha_salida_motivo',
        'comentarios',
        'fecha_llamada',
        'entrevistado_por',
        'vacante_aplica',
        'experiencia_demostrable',
        'conoce_del_area',
        'fortalezas',
        'debilidades',
        'manejo_excel',
        'user_id',
    ];

    protected $casts = [
        'fecha_llamada' => 'date',
        'salario' => 'decimal:2',
        'edad' => 'integer',
        'hijos' => 'integer',
        'manejo_excel' => 'integer',
        'user_id' => 'integer',
    ];
}
