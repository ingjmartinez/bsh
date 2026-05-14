<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Agencia extends Model
{
    use HasFactory;

    protected $table = 'agencias';

    protected $primaryKey = 'id';

    protected $fillable = [
        'agencia',
        'nombre_agencia',
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

    public function coordinadoresOperadores()
    {
        return $this->belongsToMany(
            CoordinadorOperador::class,
            'coordinador_operador_agencia',
            'agencia_id',
            'coordinador_operador_id'
        )->withTimestamps();
    }
}
