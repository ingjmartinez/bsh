<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VtUsuarioNet extends Model
{
    protected $table = 'ventas_usuarios_net';
    public $timestamps = true;
    protected $fillable = [
        'consorcio_id',
        'agencia_id',
        'cedula',
        'producto_id',
        'descripcion',
        'tipo',
        'monto',
        'fecha',
    ];
}
