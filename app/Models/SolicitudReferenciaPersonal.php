<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SolicitudReferenciaPersonal extends Model
{
    protected $table = 'solicitud_referencias_personales';
    protected $primaryKey = 'ref_per_id';
    public $timestamps = false;

    protected $fillable = [
        'solicitud_id', 'nombre', 'ocupacion', 'lugar_trabajo', 'sector_residencia', 'telefono'
    ];

    public function solicitud()
    {
        return $this->belongsTo(RegistroEmpleado::class, 'solicitud_id', 'solicitud_id');
    }
}
