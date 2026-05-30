<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VtProductoNet extends Model
{
    protected $table = 'ventas_producto_net';
    public $timestamps = false;
    protected $primaryKey = 'venta_id';
    protected $fillable = [
        'consorcio_id',
        'consorcio_codigo',
        'consorcio_desc',
        'banca_id',
        'banca_desc',
        'agencia_id',
        'terminal_codigo',
        'terminal_desc',
        'loteria_id',
        'loteria_desc',
        'producto_id',
        'juego_id',
        'juego_prefijo',
        'juego_desc',
        'descripcion',
        'monto',
        'monto_jugado',
        'monto_pagado',
        'monto_premiado',
        'impuesto_retenido',
        'fecha',
    ];
}
