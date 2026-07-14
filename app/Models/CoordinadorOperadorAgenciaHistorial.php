<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CoordinadorOperadorAgenciaHistorial extends Model
{
    protected $table = 'coordinador_operador_agencia_historial';

    public const UPDATED_AT = null;

    protected $guarded = [];

    protected $casts = [
        'metadata' => 'array',
        'created_at' => 'datetime',
    ];

    public function agencia(): BelongsTo
    {
        return $this->belongsTo(Agencia::class);
    }

    public function actor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
