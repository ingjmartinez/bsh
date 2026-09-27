<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AsistenciaNet extends Model
{
    protected $table = 'asistencias_net';

    public $timestamps = false;

    protected $primaryKey = 'id';

    protected $fillable = [
        'consorcio',
        'agencia',
        'fecha',
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
