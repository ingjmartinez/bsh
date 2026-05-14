<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SolicitudFamiliar extends Model
{
    protected $table = 'solicitud_familiares';
    protected $primaryKey = 'familiar_id';
    public $timestamps = false;

    protected $fillable = [
        'solicitud_id', 'parentesco', 'nombre', 'edad', 'telefono', 'ocupacion', 'lugar_trabajo'
    ];

    public function solicitud()
    {
        return $this->belongsTo(RegistroEmpleado::class, 'solicitud_id', 'solicitud_id');
    }
}
