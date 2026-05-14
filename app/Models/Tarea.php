<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Tarea extends Model
{
    protected $table = 'tareas';

    protected $fillable = [
        'titulo',
        'descripcion',
        'adjunto_path',
        'adjunto_nombre',
        'departamento_id',
        'user_id',
        'asignado_id',
        'tarea_padre_id',
        'estado',
        'prioridad',
        'progreso',
        'fecha_inicio',
        'fecha_fin',
        'fecha_completada',
        'cierre_solicitado_at',
        'cierre_solicitado_por',
    ];

    protected $casts = [
        'fecha_inicio' => 'date',
        'fecha_fin' => 'date',
        'fecha_completada' => 'date',
        'cierre_solicitado_at' => 'datetime',
        'progreso' => 'integer',
    ];

    protected $appends = [
        'adjunto_url',
    ];

    /* ───────────── Relaciones ───────────── */

    public function departamento(): BelongsTo
    {
        return $this->belongsTo(DepartamentoCrm::class, 'departamento_id');
    }

    public function creador(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function asignado(): BelongsTo
    {
        return $this->belongsTo(User::class, 'asignado_id');
    }

    public function tareaPadre(): BelongsTo
    {
        return $this->belongsTo(Tarea::class, 'tarea_padre_id');
    }

    public function subtareas(): HasMany
    {
        return $this->hasMany(Tarea::class, 'tarea_padre_id');
    }

    public function comentarios(): HasMany
    {
        return $this->hasMany(TareaComentario::class, 'tarea_id');
    }

    public function cierreSolicitadoPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'cierre_solicitado_por');
    }

    /* ───────────── Accessors ───────────── */

    /**
     * Determina si la tarea está atrasada.
     */
    public function getAtrasadaAttribute(): bool
    {
        if (in_array($this->estado, ['completada', 'cancelada'])) {
            return false;
        }
        return $this->fecha_fin->lt(Carbon::today());
    }

    /**
     * Días de atraso (0 si no está atrasada).
     */
    public function getDiasAtrasoAttribute(): int
    {
        if (!$this->atrasada) {
            return 0;
        }
        return $this->fecha_fin->diffInDays(Carbon::today());
    }

    /**
     * Color según prioridad para la vista Gantt.
     */
    public function getColorPrioridadAttribute(): string
    {
        return match ($this->prioridad) {
            'baja' => '#0ab39c',
            'media' => '#405189',
            'alta' => '#f7b84b',
            'critica' => '#f06548',
            default => '#405189',
        };
    }

    /**
     * Badge CSS según estado.
     */
    public function getBadgeEstadoAttribute(): string
    {
        return match ($this->estado) {
            'pendiente' => 'warning',
            'en_progreso' => 'info',
            'completada' => 'success',
            'cancelada' => 'danger',
            default => 'secondary',
        };
    }

    public function getAdjuntoUrlAttribute(): ?string
    {
        if (empty($this->adjunto_path)) {
            return null;
        }

        return asset('storage/' . ltrim($this->adjunto_path, '/'));
    }

    /* ───────────── Scopes ───────────── */

    public function scopeAtrasadas($query)
    {
        return $query->whereNotIn('estado', ['completada', 'cancelada'])
                     ->where('fecha_fin', '<', Carbon::today());
    }

    public function scopeEnProgreso($query)
    {
        return $query->where('estado', 'en_progreso');
    }

    public function scopeDelDepartamento($query, $departamentoId)
    {
        return $query->where('departamento_id', $departamentoId);
    }

    public function scopePrincipales($query)
    {
        return $query->whereNull('tarea_padre_id');
    }
}
