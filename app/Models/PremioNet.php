<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PremioNet extends Model
{
    protected $table = 'premios_net';
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
