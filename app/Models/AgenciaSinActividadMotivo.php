<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AgenciaSinActividadMotivo extends Model
{
    /** @use HasFactory<\Database\Factories\AgenciaSinActividadMotivoFactory> */
    use HasFactory;

    protected $fillable = [
        'sistema',
        'terminal',
        'motivo',
        'observacion',
        'registrado_por',
    ];
}
