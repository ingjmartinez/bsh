<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ServicioGeneralRutaInspeccion extends Model
{
    protected $table = 'servicios_generales_rutas_inspeccion';

    protected $guarded = [];

    protected $casts = [
        'fecha' => 'date',
        'metadata' => 'array',
        'iniciado_at' => 'datetime',
        'cierre_solicitado_at' => 'datetime',
        'cerrado_at' => 'datetime',
        'check_in_at' => 'datetime',
        'check_out_at' => 'datetime',
        'generado_automaticamente' => 'boolean',
        'conforme' => 'boolean',
    ];

    public function creador(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function agencia(): BelongsTo
    {
        return $this->belongsTo(Agencia::class);
    }

    public function responsable(): BelongsTo
    {
        return $this->belongsTo(CoordinadorOperador::class, 'coordinador_operador_id');
    }

    public function cerradoPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'cerrado_por');
    }

    public function historial(): HasMany
    {
        return $this->hasMany(ServicioGeneralRutaInspeccionHistorial::class, 'ruta_inspeccion_id')
            ->orderByDesc('created_at');
    }

    public function respuestas(): HasMany
    {
        return $this->hasMany(ServicioGeneralVisitaRespuesta::class, 'ruta_inspeccion_id');
    }

    public function averiasDerivadas(): HasMany
    {
        return $this->hasMany(self::class, 'visita_origen_id');
    }

    public function getCodigoAttribute(): string
    {
        return 'SUP-' . str_pad((string) $this->id, 6, '0', STR_PAD_LEFT);
    }
}
