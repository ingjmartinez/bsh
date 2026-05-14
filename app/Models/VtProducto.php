<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VtProducto extends Model
{
    protected $table = 'ventas_producto_bet';
    public $timestamps = true;
    protected $primaryKey = 'id';
    protected $fillable = [
        'consorcio_id',
        'agencia_id',
        'producto_id',
        'descripcion',
        'monto',
        'comision',
        'comision_supervisor',
        'fecha',
        'sorteo_id',
        'fecha_sorteo',
        'source_hash',
    ];
}
