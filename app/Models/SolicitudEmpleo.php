<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SolicitudEmpleo extends Model
{
    protected $table = 'solicitud_empleos';
    protected $primaryKey = 'empleo_id';
    public $timestamps = false;

    protected $fillable = [
        'solicitud_id', 'empresa_nombre', 'telefono', 'puesto', 'tiempo_en_puesto', 'fecha_desde', 'fecha_hasta',
        'ultimo_sueldo', 'funciones', 'motivo_salida', 'supervisor_inmediato'
    ];

    protected $casts = [
        'fecha_desde' => 'date',
        'fecha_hasta' => 'date',
        'ultimo_sueldo' => 'decimal:2',
    ];

    public function solicitud()
    {
        return $this->belongsTo(RegistroEmpleado::class, 'solicitud_id', 'solicitud_id');
    }
}
