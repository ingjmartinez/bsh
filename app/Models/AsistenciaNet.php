<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AsistenciaNet extends Model
{
    protected $table = 'asistencias_net';
    public $timestamps = false;
    protected $primaryKey = 'asistencia_id';
    protected $fillable = [
        'consorcio_id',
        'agencia_id',
        'fecha',
        'cedula',
        'usuario',
        'entrada',
        'salida',
        'identificacion',
        'username',
        'banca',
        'terminal',
        'salida_inactividad',
        'turno',
    ];
}
