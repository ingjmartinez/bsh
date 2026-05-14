<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PagoPorOtraEmpresaNet extends Model
{
    protected $table = 'pagos_porotra_empresa_net';
    public $timestamps = false;
    protected $primaryKey = 'pago_id';
    protected $fillable = [
        'consorcio_id',
        'agencia_id',
        'producto_id',
        'descripcion',
        'monto',
        'fecha',
        'pagado_consorcio_id',
        'plataforma'
    ];
}
