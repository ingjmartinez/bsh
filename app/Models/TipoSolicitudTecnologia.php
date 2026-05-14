<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TipoSolicitudTecnologia extends Model
{
    protected $table = 'tipos_solicitud_tecnologia';

    protected $fillable = [
        'nombre',
        'activo',
        'requiere_progreso',
    ];

    protected $casts = [
        'activo' => 'boolean',
        'requiere_progreso' => 'boolean',
    ];

    public function solicitudes(): HasMany
    {
        return $this->hasMany(TecnologiaSolicitud::class, 'tipo_solicitud_id');
    }
}
