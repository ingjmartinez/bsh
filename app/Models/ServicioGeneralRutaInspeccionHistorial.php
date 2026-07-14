<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ServicioGeneralRutaInspeccionHistorial extends Model
{
    protected $table = 'servicios_generales_rutas_inspeccion_historial';

    public const UPDATED_AT = null;

    protected $guarded = [];

    protected $casts = [
        'cambios' => 'array',
        'created_at' => 'datetime',
    ];

    public function ruta(): BelongsTo
    {
        return $this->belongsTo(ServicioGeneralRutaInspeccion::class, 'ruta_inspeccion_id');
    }

    public function actor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
