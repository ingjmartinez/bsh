<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class IncentivoPeriodoDetalle extends Model
{
    /** @use HasFactory<\Database\Factories\IncentivoPeriodoDetalleFactory> */
    use HasFactory;

    protected $fillable = [
        'incentivo_periodo_id',
        'cedula',
        'empleadoid',
        'nombre',
        'empresa',
        'ultima_terminal',
        'ultima_agencia_nombre',
        'ventas_ultimo_mes',
        'ventas_mes_actual',
        'dias_ventas',
        'horas_total',
        'incentivo_generado',
        'monto_pagado',
        'monto_no_pagado',
        'estado',
        'motivos',
        'tipos_pago_detalle',
    ];

    protected function casts(): array
    {
        return [
            'horas_total' => 'decimal:2',
            'motivos' => 'array',
            'tipos_pago_detalle' => 'array',
        ];
    }

    public function periodo(): BelongsTo
    {
        return $this->belongsTo(IncentivoPeriodo::class, 'incentivo_periodo_id');
    }
}
