<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ContabilidadElectricidadSeguimientoDia extends Model
{
    use HasFactory;

    protected $table = 'contabilidad_electricidad_seguimiento_dia';

    protected $fillable = [
        'fecha_solicitud',
        'distribuidora',
        'nic',
        'agencia',
        'ruta',
        'estatus',
        'observaciones',
    ];

    protected $casts = [
        'fecha_solicitud' => 'date',
    ];
}
