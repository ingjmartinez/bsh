<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MonitoreoVentaSeguimiento extends Model
{
    protected $table = 'monitoreo_venta_seguimientos';

    protected $guarded = [];

    public function usuario(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
