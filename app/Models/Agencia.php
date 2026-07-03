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
        'codigo',
        'nombre_agencia',
        'nombre',
        'terminal',
        'ciudad_id',
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

    public function getAgenciaAttribute($value): ?string
    {
        return $value ?? $this->attributes['codigo'] ?? null;
    }

    public function getNombreAgenciaAttribute($value): ?string
    {
        return $value ?? $this->attributes['nombre'] ?? null;
    }

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
