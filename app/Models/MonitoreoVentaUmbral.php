<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MonitoreoVentaUmbral extends Model
{
    protected $guarded = [];

    public function asesor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'asesor_id');
    }

    protected function casts(): array
    {
        return ['monto' => 'decimal:2'];
    }
}
