<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MonitoreoVentaAsignacion extends Model
{
    protected $table = 'monitoreo_venta_asignaciones';

    protected $guarded = [];

    public function asesor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'asesor_id');
    }
}
