<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FaltantesBet extends Model
{
    protected $table = 'faltantes_bet';
    public $timestamps = false;
    protected $primaryKey = 'faltante_id';
    protected $fillable = ['consorcio_id', 'agencia_id', 'identificacion', 'monto', 'fecha', 'abono', 'balance'];
}
