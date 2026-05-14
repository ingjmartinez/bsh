<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Premio extends Model
{
    protected $table = 'premios_bet';
    public $timestamps = false;
    protected $primaryKey = 'premio_id';
    protected $fillable = [
        'consorcio_id',
        'producto_id',
        'monto',
        'agencia_id',
        'descripcion',
        'fecha',
    ];
}
