<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FaltantesNet extends Model
{
    protected $table = 'faltantes_net';
    public $timestamps = false;
    protected $primaryKey = 'faltante_id';
    protected $fillable = ['consorcio_id', 'agencia_id', 'identificacion', 'monto', 'fecha', 'abono', 'balance'];
}
