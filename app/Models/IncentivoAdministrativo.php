<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class IncentivoAdministrativo extends Model
{
    use HasFactory;

    protected $fillable = [
        'grupo',
        'nombre',
        'empresa',
        'pct_total',
    ];

    protected $casts = [
        'pct_total' => 'float',
    ];
}
