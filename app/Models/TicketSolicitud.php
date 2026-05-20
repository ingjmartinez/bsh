<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TicketSolicitud extends Model
{
    protected $table = 'ticket_solicitudes';

    public const CATEGORIA_PAGAR = 'pagar_ticket';
    public const CATEGORIA_ANULAR = 'anular_ticket';

    public const ESTADO_PENDIENTE = 'pendiente';
    public const ESTADO_PAGADO = 'pagado';
    public const ESTADO_NULO = 'nulo';

    protected $fillable = [
        'phone',
        'categoria',
        'ticket_numero',
        'estado',
        'mensaje_original',
        'attachment_url',
        'attachment_message_id',
        'notas',
        'procesado_por_id',
        'procesado_at',
    ];

    protected function casts(): array
    {
        return [
            'procesado_at' => 'datetime',
        ];
    }

    public function procesadoPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'procesado_por_id');
    }

    public function scopeFiltro(Builder $query, array $filtros): Builder
    {
        return $query
            ->when($filtros['categoria'] ?? null, fn(Builder $q, string $categoria) => $q->where('categoria', $categoria))
            ->when($filtros['estado'] ?? null, fn(Builder $q, string $estado) => $q->where('estado', $estado))
            ->when($filtros['desde'] ?? null, fn(Builder $q, string $desde) => $q->whereDate('created_at', '>=', $desde))
            ->when($filtros['hasta'] ?? null, fn(Builder $q, string $hasta) => $q->whereDate('created_at', '<=', $hasta))
            ->when($filtros['buscar'] ?? null, function (Builder $q, string $buscar) {
                $buscar = trim($buscar);

                $q->where(function (Builder $subQuery) use ($buscar) {
                    $subQuery->where('ticket_numero', 'like', '%' . $buscar . '%')
                        ->orWhere('phone', 'like', '%' . $buscar . '%')
                        ->orWhere('mensaje_original', 'like', '%' . $buscar . '%');
                });
            });
    }

    public function getCodigoAttribute(): string
    {
        return 'TCK-' . str_pad((string) $this->id, 6, '0', STR_PAD_LEFT);
    }

    public function getCategoriaLabelAttribute(): string
    {
        return match ($this->categoria) {
            self::CATEGORIA_PAGAR => 'Pagar ticket',
            self::CATEGORIA_ANULAR => 'Anular ticket',
            default => ucfirst(str_replace('_', ' ', (string) $this->categoria)),
        };
    }

    public function getEstadoLabelAttribute(): string
    {
        return match ($this->estado) {
            self::ESTADO_PAGADO => 'Pagado',
            self::ESTADO_NULO => 'Nulo',
            default => 'Pendiente',
        };
    }

    public function getEstadoBadgeAttribute(): string
    {
        return match ($this->estado) {
            self::ESTADO_PAGADO => 'success',
            self::ESTADO_NULO => 'danger',
            default => 'warning',
        };
    }
}
