<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ContabilidadElectricidadAveriaDia extends Model
{
    use HasFactory;

    protected $table = 'contabilidad_electricidad_averia_dia';

    protected $fillable = [
        'fecha_reporte',
        'reporte',
        'distribuidora',
        'nic',
        'agencia',
        'ruta',
        'coordinadores',
        'agente_venta_am',
        'agente_venta_pm',
        'estatus',
        'observaciones',
    ];

    protected $casts = [
        'fecha_reporte' => 'date',
    ];
}
