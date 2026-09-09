<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class IncentivoTerminalTipoPago extends Model
{
    use HasFactory;

    public const TIPOS_PAGO = [
        'tramos_60',
        'tramos_70',
        'tramos_80',
    ];

    public const SISTEMAS = [
        'Lotobet',
        'Lotonet',
    ];

    protected $fillable = [
        'sistema',
        'terminal',
        'fecha',
        'tipo_pago',
        'created_by',
        'updated_by',
    ];

    protected function casts(): array
    {
        return [
            'fecha' => 'date',
        ];
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
