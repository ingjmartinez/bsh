<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TecnologiaSolicitud extends Model
{
    protected $table = 'tecnologia_solicitudes';

    protected $fillable = [
        'solicitante_id',
        'asignado_a_id',
        'tipo_solicitud_id',
        'titulo',
        'descripcion',
        'prioridad',
        'estado',
        'progreso',
        'detalle_solucion',
        'cierre_solicitado_at',
        'cierre_solicitado_por_id',
        'asignado_at',
        'fecha_completada',
        'cerrado_por_id',
    ];

    protected $casts = [
        'progreso' => 'integer',
        'cierre_solicitado_at' => 'datetime',
        'asignado_at' => 'datetime',
        'fecha_completada' => 'date',
    ];

    public function creador(): BelongsTo
    {
        return $this->belongsTo(User::class, 'solicitante_id');
    }

    public function asignado(): BelongsTo
    {
        return $this->belongsTo(User::class, 'asignado_a_id');
    }

    public function tipoSolicitud(): BelongsTo
    {
        return $this->belongsTo(TipoSolicitudTecnologia::class, 'tipo_solicitud_id');
    }

    public function cierreSolicitadoPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'cierre_solicitado_por_id');
    }

    public function cerradoPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'cerrado_por_id');
    }

    public function getTicketCodigoAttribute(): string
    {
        return 'TEC-' . str_pad((string) $this->id, 6, '0', STR_PAD_LEFT);
    }

    public function getBadgeEstadoAttribute(): string
    {
        return match ($this->estado) {
            'pendiente' => 'warning',
            'asignada' => 'primary',
            'en_progreso' => 'info',
            'en_espera' => 'secondary',
            'solicitud_cierre' => 'warning',
            'resuelta' => 'success',
            'cancelada' => 'danger',
            default => 'dark',
        };
    }

    public function getBadgePrioridadAttribute(): string
    {
        return match ($this->prioridad) {
            'baja' => 'success',
            'media' => 'primary',
            'alta' => 'warning',
            'critica' => 'danger',
            default => 'dark',
        };
    }

    public function getBadgeTipoAttribute(): string
    {
        return match ($this->tipo) {
            'averia' => 'danger',
            'desarrollo' => 'info',
            default => 'dark',
        };
    }

    public function getTipoAttribute(): string
    {
        $nombre = strtolower((string) ($this->tipoSolicitud->nombre ?? ''));

        return str_contains($nombre, 'desarrollo') ? 'desarrollo' : 'averia';
    }

    public function getAsignadoIdAttribute(): ?int
    {
        return $this->asignado_a_id;
    }

    public function getUserIdAttribute(): ?int
    {
        return $this->solicitante_id;
    }

    public function getCierreSolicitadoPorAttribute(): ?int
    {
        return $this->cierre_solicitado_por_id;
    }

    public function getResueltoAtAttribute()
    {
        return $this->fecha_completada;
    }
}
