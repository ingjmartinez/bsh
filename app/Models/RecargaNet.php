<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RecargaNet extends Model
{
    protected $table = 'recargas_net';
    public $timestamps = false;
    protected $primaryKey = 'recarga_id';
    protected $fillable = [
        'recarga_id',
        'fecha',
        'consorcio_id',
        'producto_id',
        'descripcion',
        'agencia_id',
        'identificacion',
        'monto',
        'proveedor_nombre',
        'proveedor_id',
        'distribuidora_id',
        'distribuidora_nombre',
    ];
}
