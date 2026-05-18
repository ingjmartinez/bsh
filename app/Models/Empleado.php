<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Empleado extends Model
{
    public $timestamps = false;
    protected $primaryKey = 'id';
    protected $fillable = [
        'companyid',
        'empleadoid',
        'cedula',
        'tipo_documento_id',
        'nombres',
        'apellidos',
        'departamento_id',
        'posicion_id',
        'ciudad_id',
        'estado_civil_id',
        'fecha_nacimiento',
        'fecha_ingreso',
        'fecha_egreso',
        'turno_id',
        'tipo_contrato',
        'estatus',
        'salario',
        'banco_id',
        'numero_cuenta',
        'tipo_cuenta',
        'aplica_incentivo',
        'porcentaje_incentivo',
        'tipo_empleado_incentivo',
        'telefono',
        'email',
        'fuente_sync',
        'ultima_sync_at',
    ];
}
