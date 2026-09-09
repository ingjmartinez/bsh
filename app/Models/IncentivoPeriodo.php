<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class IncentivoPeriodo extends Model
{
    /** @use HasFactory<\Database\Factories\IncentivoPeriodoFactory> */
    use HasFactory;

    protected $fillable = [
        'anio',
        'mes',
        'fecha_inicio',
        'fecha_fin',
        'sistema',
        'modo_calculo',
        'tipo_pago_defecto',
        'min_dias_venta',
        'rangos_pago_por_tipo',
        'terminales_excluidas',
        'resumen',
        'revision',
        'created_by',
        'updated_by',
    ];

    protected function casts(): array
    {
        return [
            'fecha_inicio' => 'date',
            'fecha_fin' => 'date',
            'rangos_pago_por_tipo' => 'array',
            'terminales_excluidas' => 'array',
            'resumen' => 'array',
        ];
    }

    public function detalles(): HasMany
    {
        return $this->hasMany(IncentivoPeriodoDetalle::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
