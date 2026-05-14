<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CentroDeCosto extends Model
{
    protected $table = 'centros_de_costo';

    protected $fillable = [
        'id_centro_costo',
        'company_id',
        'descripcion',
        'cuenta',
        'inactivo',
        'id_grupo',
        'id_sub_grupo',
        'id_division',
        'id_sociedad',
        'id_viejo',
        'ocultar',
        'id_centro_costo_resumir_en',
        'com_recarga',
        'gasto_vta_tradicional',
        'varios_locales',
        'aplica_para_ponderar',
        'valor_ponderar',
        'creado_por',
        'fecha_grabado',
        'modificado_por',
        'fecha_modificado',
        'atributos',
    ];

    protected $casts = [
        'id_centro_costo' => 'integer',
        'inactivo' => 'boolean',
        'ocultar' => 'boolean',
        'com_recarga' => 'boolean',
        'gasto_vta_tradicional' => 'boolean',
        'varios_locales' => 'boolean',
        'aplica_para_ponderar' => 'boolean',
        'valor_ponderar' => 'decimal:4',
        'fecha_grabado' => 'datetime',
        'fecha_modificado' => 'datetime',
        'atributos' => 'array',
    ];

    protected $appends = ['activo'];

    public function getActivoAttribute(): bool
    {
        return ! $this->inactivo;
    }
}
