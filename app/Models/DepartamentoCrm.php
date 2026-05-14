<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DepartamentoCrm extends Model
{
    protected $table = 'departamentos';

    protected $fillable = [
        'nombre',
        'descripcion',
        'color',
        'activo',
    ];

    protected $casts = [
        'activo' => 'boolean',
    ];

    /**
     * Tareas del departamento.
     */
    public function tareas(): HasMany
    {
        return $this->hasMany(Tarea::class, 'departamento_id');
    }

    /**
     * Scope para departamentos activos.
     */
    public function scopeActivos($query)
    {
        return $query->where('activo', true);
    }
}
