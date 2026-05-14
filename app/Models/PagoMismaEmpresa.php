<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PagoMismaEmpresa extends Model
{
    protected $table = 'pagos_misma_empresa_bet';
    public $timestamps = false;
    protected $primaryKey = 'pago_id';
    protected $fillable = [
        'consorcio_id',
        'agencia_id',
        'producto_id',
        'descripcion',
        'monto',
        'fecha',
        'pagado_agencia_id',
        'plataforma_pago'
    ];
}
