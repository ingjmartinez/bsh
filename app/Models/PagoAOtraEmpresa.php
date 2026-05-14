<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PagoAOtraEmpresa extends Model
{
    protected $table = 'pagos_aotra_empresa_bet';
    public $timestamps = false;
    protected $primaryKey = 'pago_id';
    protected $fillable = [
        'consorcio_id',
        'agencia_id',
        'producto_id',
        'descripcion',
        'monto',
        'fecha',
        'importe',
        'pagado_consorcio_id',
        'plataforma_pago'
    ];
}
