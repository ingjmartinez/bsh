<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VtUsuarioNet extends Model
{
    protected $table = 'ventas_usuarios_net';
    public $timestamps = true;
    protected $fillable = ['agencia_id', 'cedula', 'monto', 'fecha'];
}
