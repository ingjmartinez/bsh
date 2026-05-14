<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RegistroEmpleado extends Model
{
    protected $table = 'solicitudes_empleo';
    protected $primaryKey = 'solicitud_id';
    public $timestamps = false;

    protected $fillable = [
        'apellidos', 'nombres', 'apodo', 'cedula_pasaporte', 'fecha_nacimiento', 'lugar_nacimiento',
        'nacionalidad', 'edad', 'direccion', 'sector', 'ciudad', 'telefono_residencial', 'celular', 'email',
        'estado_civil', 'tipo_sangre', 'instagram', 'facebook', 'estudia_actualmente', 'que_estudia',
        'horario_estudio', 'domina_computadora', 'domina_fax', 'domina_impresora', 'domina_scanner',
        'domina_maquinas_elec', 'domina_calculadoras', 'ha_trabajado_antes_en_empresa', 'familiares_en_empresa',
        'competencias_laborales', 'fortalezas_profesionales', 'impedimento_sab_dom_fer', 'problemas_salud_detalle',
        'afp', 'ars', 'sabe_conducir', 'licencia_categoria', 'licencia_vencimiento', 'fecha_disponible',
        'acepta_cambio_horario', 'acepta_cambio_lugar', 'disp_diurno', 'disp_nocturno', 'disp_rotativo',
        'disp_domingos', 'disp_feriados', 'cuenta_banco_caribe_bhd', 'incluido_buro_credito', 'referido_por',
        'referido_parentesco', 'emergencia_contacto_nombre', 'emergencia_parentesco', 'emergencia_telefonos',
        'medio_informo_vacante', 'firma_nombre', 'fecha_firma', 'seleccionado', 'puesto_aplicado', 'banca',
        'horario_trabajo', 'fecha_ingreso', 'salario', 'aprobado_por'
    ];

    protected $casts = [
        'fecha_nacimiento' => 'date',
        'licencia_vencimiento' => 'date',
        'fecha_disponible' => 'date',
        'fecha_firma' => 'date',
        'fecha_ingreso' => 'date',
        'estudia_actualmente' => 'boolean',
        'domina_computadora' => 'boolean',
        'domina_fax' => 'boolean',
        'domina_impresora' => 'boolean',
        'domina_scanner' => 'boolean',
        'domina_maquinas_elec' => 'boolean',
        'domina_calculadoras' => 'boolean',
        'ha_trabajado_antes_en_empresa' => 'boolean',
        'familiares_en_empresa' => 'boolean',
        'impedimento_sab_dom_fer' => 'boolean',
        'sabe_conducir' => 'boolean',
        'acepta_cambio_horario' => 'boolean',
        'acepta_cambio_lugar' => 'boolean',
        'disp_diurno' => 'boolean',
        'disp_nocturno' => 'boolean',
        'disp_rotativo' => 'boolean',
        'disp_domingos' => 'boolean',
        'disp_feriados' => 'boolean',
        'cuenta_banco_caribe_bhd' => 'boolean',
        'incluido_buro_credito' => 'boolean',
        'seleccionado' => 'boolean',
    ];

    public function familiares()
    {
        return $this->hasMany(SolicitudFamiliar::class, 'solicitud_id', 'solicitud_id');
    }

    public function educacion()
    {
        return $this->hasMany(SolicitudEducacion::class, 'solicitud_id', 'solicitud_id');
    }

    public function empleos()
    {
        return $this->hasMany(SolicitudEmpleo::class, 'solicitud_id', 'solicitud_id');
    }

    public function referenciasLaborales()
    {
        return $this->hasMany(SolicitudReferenciaLaboral::class, 'solicitud_id', 'solicitud_id');
    }

    public function referenciasPersonales()
    {
        return $this->hasMany(SolicitudReferenciaPersonal::class, 'solicitud_id', 'solicitud_id');
    }
}
