<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SolicitudEducacion extends Model
{
    protected $table = 'solicitud_educacion';
    protected $primaryKey = 'educacion_id';
    public $timestamps = false;

    protected $fillable = [
        'solicitud_id', 'nivel', 'centro_docente', 'lugar', 'fecha_termino', 'nivel_alcanzado'
    ];

    protected $casts = [
        'fecha_termino' => 'date',
    ];

    public function solicitud()
    {
        return $this->belongsTo(RegistroEmpleado::class, 'solicitud_id', 'solicitud_id');
    }
}
