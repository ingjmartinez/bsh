<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PorcentajeIncentivo extends Model
{
    use HasFactory;

    protected $fillable = [
        'posicion',
        'bono_pct',
        'notas',
    ];

    protected $casts = [
        'bono_pct' => 'float',
    ];
}
