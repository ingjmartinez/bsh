<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DetalleCuenta extends Model
{
    use HasFactory;

    protected $table = 'detalle_cuentas';

    protected $fillable = [
        'external_key',
        'cuenta',
        'no_asiento',
        'fecha',
        'fecha_raw',
        'ref',
        'no_ref',
        'debito',
        'credito',
        'descripcion',
        'grupo',
        'sub_grupo',
        'division',
        'centro_costo',
        'conciliado',
        'modulo',
        'fecha_grabado',
        'fecha_modificado',
        'creado_por',
        'modificado_por',
        'ref_desc',
        'sociedad',
    ];

    protected $casts = [
        'fecha' => 'date',
        'debito' => 'decimal:2',
        'credito' => 'decimal:2',
    ];
}
