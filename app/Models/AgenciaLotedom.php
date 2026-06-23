<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AgenciaLotedom extends Model
{
    use HasFactory;

    protected $table = 'agencias_lotedom';

    protected $primaryKey = 'id';

    protected $fillable = [
        'agencia',
        'codigo',
        'nombre_agencia',
        'nombre',
        'terminal',
        'horario_am',
        'horario_pm',
        'sistema',
        'empresa',
        'ciudad',
        'ruta',
        'operador',
        'coordinador',
        'estatus',
        'aplica_incentivo',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'estatus' => 'integer',
        'aplica_incentivo' => 'boolean',
    ];
}
