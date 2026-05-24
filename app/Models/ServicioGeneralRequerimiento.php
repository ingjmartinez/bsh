<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ServicioGeneralRequerimiento extends Model
{
    protected $table = 'servicios_generales_requerimientos';

    protected $fillable = [
        'user_id',
        'whatsapp_phone',
        'asignado_id',
        'tipo',
        'titulo',
        'descripcion',
        'prioridad',
        'estado',
        'progreso',
        'detalle_solucion',
        'attachment_url',
        'attachment_message_id',
        'cierre_solicitado_at',
        'cierre_solicitado_por',
        'asignado_at',
        'resuelto_at',
        'cerrado_por',
    ];

    protected $casts = [
        'progreso' => 'integer',
        'cierre_solicitado_at' => 'datetime',
        'asignado_at' => 'datetime',
        'resuelto_at' => 'datetime',
    ];

    public function creador(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function asignado(): BelongsTo
    {
        return $this->belongsTo(User::class, 'asignado_id');
    }

    public function cierreSolicitadoPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'cierre_solicitado_por');
    }

    public function cerradoPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'cerrado_por');
    }

    public function getTicketCodigoAttribute(): string
    {
        return 'REQ-' . str_pad((string) $this->id, 6, '0', STR_PAD_LEFT);
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
            'internet' => 'warning',
            'electricidad' => 'danger',
            'sistema_frizado' => 'info',
            'inversor' => 'primary',
            default => 'dark',
        };
    }

    public function getTipoLabelAttribute(): string
    {
        return match ($this->tipo) {
            'internet' => 'No tengo internet',
            'electricidad' => 'No tengo luz',
            'sistema_frizado' => 'Se me friso el sistema',
            'inversor' => 'Cambiar el inversor',
            default => ucfirst((string) $this->tipo),
        };
    }
}
