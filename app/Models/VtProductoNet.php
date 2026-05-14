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
        'agencia_id',
        'producto_id',
        'descripcion',
        'monto',
        'fecha',
    ];
}
