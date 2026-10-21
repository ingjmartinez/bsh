<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MonitoreoVentaCaso extends Model
{
    protected $table = 'monitoreo_venta_casos';

    protected $guarded = [];

    protected function casts(): array
    {
        return ['fecha' => 'date', 'cerrado_at' => 'datetime'];
    }

    public function agencia(): BelongsTo
    {
        return $this->belongsTo(Agencia::class);
    }

    public function asesor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'asesor_id');
    }

    public function seguimientos(): HasMany
    {
        return $this->hasMany(MonitoreoVentaSeguimiento::class, 'caso_id')->latest();
    }
}
