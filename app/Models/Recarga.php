<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Recarga extends Model
{
    protected $table = 'recargas_bet';
    public $timestamps = false;
    protected $primaryKey = 'recarga_id';
    protected $fillable = [
        'consorcio_id',
        'producto_id',
        'monto',
        'agencia_id',
        'descripcion',
        'distribuidora_id',
        'distribuidora_nombre',
        'fecha',
        'proveedor_id',
        'proveedor_nombre',
        'comision',
        'comision_supervisor',
    ];
}
