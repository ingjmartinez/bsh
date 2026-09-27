<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NuevoIngresoConfiguracion extends Model
{
    /** @use HasFactory<\Database\Factories\NuevoIngresoConfiguracionFactory> */
    use HasFactory;

    protected $table = 'nuevo_ingreso_configuraciones';

    protected $fillable = [
        'dias_habiles',
        'actualizado_por',
    ];
}
