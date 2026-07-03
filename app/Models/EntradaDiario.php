<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EntradaDiario extends Model
{
    protected $table = 'entradas_diario';

    protected $fillable = [
        'external_key',
        'payload_hash',
        'no_asiento',
        'company_id',
        'fecha',
        'fecha_raw',
        'ref',
        'no_ref',
        'cuenta',
        'debito',
        'credito',
        'descripcion',
        'id_centro_costo',
        'id_grupo',
        'id_sub_grupo',
        'id_division',
        'id_sociedad',
        'conciliado',
        'modulo',
        'fecha_grabado',
        'fecha_modificado',
        'id_viejo',
        'centro_costo',
        'grupo',
        'sub_grupo',
        'division',
        'creado_por',
        'modificado_por',
        'ref_desc',
        'sociedad',
    ];

    protected $casts = [
        'fecha' => 'date',
        'debito' => 'decimal:2',
        'credito' => 'decimal:2',
        'conciliado' => 'boolean',
        'fecha_grabado' => 'datetime',
        'fecha_modificado' => 'datetime',
    ];
}
