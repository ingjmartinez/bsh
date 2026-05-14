<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AutoProcesoConfig extends Model
{
    protected $table = 'auto_proceso_configs';

    protected $fillable = [
        'sistema',
        'enabled',
        'hora',
        'correo',
        'max_seconds',
        'process_day_offset',
        'process_date',
        'last_run_at',
        'last_status',
        'last_summary',
    ];

    protected $casts = [
        'enabled' => 'boolean',
        'max_seconds' => 'integer',
        'process_day_offset' => 'integer',
        'process_date' => 'date',
        'last_run_at' => 'datetime',
        'last_summary' => 'array',
    ];
}
