<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Asistencia extends Model
{
    protected $table = 'asistencias_bet';
    public $timestamps = false;
    protected $primaryKey = 'asistencia_id';
    protected $fillable = [
        'consorcio_id',
        'agencia_id',
        'fecha',
        'cedula',
        'usuario',
        'primer_login',
        'ultimo_logout',
    ];
}
