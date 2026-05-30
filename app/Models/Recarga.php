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
        'consorcio_codigo',
        'consorcio_nombre',
        'banca_id',
        'banca_nombre',
        'producto_id',
        'producto_nombre',
        'monto',
        'agencia_id',
        'terminal_codigo',
        'terminal_nombre',
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
