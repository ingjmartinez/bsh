<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VtUsuarioBet extends Model
{
    protected $table = 'ventas_usuarios_bet';
    public $timestamps = true;
    protected $fillable = ['agencia_id', 'cedula', 'monto', 'fecha'];
}
