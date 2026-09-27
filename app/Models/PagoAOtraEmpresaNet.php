<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PagoAOtraEmpresaNet extends Model
{
    protected $table = 'pagos_aotra_empresa_net';

    public $timestamps = false;

    protected $primaryKey = 'id';

    protected $fillable = [
        'agencia_id',
        'monto',
        'fecha',
        'cedula',
        'tipo_pago',
    ];
}
