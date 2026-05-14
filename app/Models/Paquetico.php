<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Paquetico extends Model
{
    protected $table = 'paquetico_net';
    public $timestamps = false;
    protected $primaryKey = 'paquetico_id';
    protected $fillable = [
        "fecha",
        "consorcio_id",
        "producto_id",
        "descripcion",
        "agencia_id",
        "identificacion",
        "monto_pagado",
        "cargo_servicio",
        "cantidad",
        "proveedor_nombre",
        "proveedor_id",
        "distribuidora_id",
        "distribuidora_nombre",
    ];
}
