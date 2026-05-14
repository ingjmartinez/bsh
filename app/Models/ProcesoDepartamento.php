<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProcesoDepartamento extends Model
{
    protected $table = 'procesos_departamento';

    protected $fillable = [
        'departamento',
        'proceso_base',
        'nombre',
        'icono',
        'descripcion',
        'protocolo',
        'es_personalizado',
    ];

    protected $casts = [
        'es_personalizado' => 'boolean',
    ];
}
