<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SolicitudReferenciaLaboral extends Model
{
    protected $table = 'solicitud_referencias_laborales';
    protected $primaryKey = 'ref_lab_id';
    public $timestamps = false;

    protected $fillable = [
        'solicitud_id', 'nombre', 'ocupacion', 'lugar_trabajo', 'telefono'
    ];

    public function solicitud()
    {
        return $this->belongsTo(RegistroEmpleado::class, 'solicitud_id', 'solicitud_id');
    }
}
