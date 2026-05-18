@extends('app')

@section('content')
<div class="main-content">
    <div class="page-content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                        <h4 class="mb-sm-0">Editar Empleado</h4>
                        <div class="page-title-right">
                            <ol class="breadcrumb m-0">
                                <li class="breadcrumb-item"><a href="{{ route('inicio.index') }}">Inicio</a></li>
                                <li class="breadcrumb-item"><a href="{{ route('recursos-humanos.index') }}">Recursos Humanos</a></li>
                                <li class="breadcrumb-item"><a href="{{ route('registro-empleados.index') }}">Registro Empleados</a></li>
                                <li class="breadcrumb-item active">Editar</li>
                            </ol>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-lg-12">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title mb-0">Formulario de Edición de Empleado</h5>
                        </div>
                        <div class="card-body">
                            <form action="{{ route('registro-empleados.update', $empleado->solicitud_id) }}" method="POST">
                                @csrf
                                @method('PUT')

                                <!-- Datos Personales -->
                                <h5>Datos Personales</h5>
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="mb-3">
                                            <label for="apellidos" class="form-label">Apellidos *</label>
                                            <input type="text" class="form-control" id="apellidos" name="apellidos" value="{{ $empleado->apellidos }}" required>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="mb-3">
                                            <label for="nombres" class="form-label">Nombres *</label>
                                            <input type="text" class="form-control" id="nombres" name="nombres" value="{{ $empleado->nombres }}" required>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="mb-3">
                                            <label for="apodo" class="form-label">Apodo</label>
                                            <input type="text" class="form-control" id="apodo" name="apodo" value="{{ $empleado->apodo }}">
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="mb-3">
                                            <label for="cedula_pasaporte" class="form-label">Cédula/Pasaporte *</label>
                                            <input type="text" class="form-control" id="cedula_pasaporte" name="cedula_pasaporte" value="{{ $empleado->cedula_pasaporte }}" required>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="mb-3">
                                            <label for="fecha_nacimiento" class="form-label">Fecha de Nacimiento</label>
                                            <input type="date" class="form-control" id="fecha_nacimiento" name="fecha_nacimiento" value="{{ $empleado->fecha_nacimiento }}">
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="mb-3">
                                            <label for="edad" class="form-label">Edad</label>
                                            <input type="number" class="form-control" id="edad" name="edad" value="{{ $empleado->edad }}">
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="lugar_nacimiento" class="form-label">Lugar de Nacimiento</label>
                                            <input type="text" class="form-control" id="lugar_nacimiento" name="lugar_nacimiento" value="{{ $empleado->lugar_nacimiento }}">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="nacionalidad" class="form-label">Nacionalidad</label>
                                            <input type="text" class="form-control" id="nacionalidad" name="nacionalidad" value="{{ $empleado->nacionalidad }}">
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="estado_civil" class="form-label">Estado Civil</label>
                                            <select class="form-control" id="estado_civil" name="estado_civil">
                                                <option value="">Seleccionar</option>
                                                <option value="SOLTERO" {{ $empleado->estado_civil == 'SOLTERO' ? 'selected' : '' }}>Soltero</option>
                                                <option value="CASADO" {{ $empleado->estado_civil == 'CASADO' ? 'selected' : '' }}>Casado</option>
                                                <option value="DIVORCIADO" {{ $empleado->estado_civil == 'DIVORCIADO' ? 'selected' : '' }}>Divorciado</option>
                                                <option value="UNION_LIBRE" {{ $empleado->estado_civil == 'UNION_LIBRE' ? 'selected' : '' }}>Unión Libre</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="tipo_sangre" class="form-label">Tipo de Sangre</label>
                                            <input type="text" class="form-control" id="tipo_sangre" name="tipo_sangre" value="{{ $empleado->tipo_sangre }}">
                                        </div>
                                    </div>
                                </div>

                                <!-- Dirección -->
                                <h5>Dirección</h5>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="direccion" class="form-label">Dirección</label>
                                            <input type="text" class="form-control" id="direccion" name="direccion" value="{{ $empleado->direccion }}">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="sector" class="form-label">Sector</label>
                                            <input type="text" class="form-control" id="sector" name="sector" value="{{ $empleado->sector }}">
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="ciudad" class="form-label">Ciudad</label>
                                            <input type="text" class="form-control" id="ciudad" name="ciudad" value="{{ $empleado->ciudad }}">
                                        </div>
                                    </div>
                                </div>

                                <!-- Contacto -->
                                <h5>Contacto</h5>
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="mb-3">
                                            <label for="telefono_residencial" class="form-label">Teléfono Residencial</label>
                                            <input type="text" class="form-control" id="telefono_residencial" name="telefono_residencial" value="{{ $empleado->telefono_residencial }}">
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="mb-3">
                                            <label for="celular" class="form-label">Celular</label>
                                            <input type="text" class="form-control" id="celular" name="celular" value="{{ $empleado->celular }}">
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="mb-3">
                                            <label for="email" class="form-label">Email</label>
                                            <input type="email" class="form-control" id="email" name="email" value="{{ $empleado->email }}">
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="instagram" class="form-label">Instagram</label>
                                            <input type="text" class="form-control" id="instagram" name="instagram" value="{{ $empleado->instagram }}">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="facebook" class="form-label">Facebook</label>
                                            <input type="text" class="form-control" id="facebook" name="facebook" value="{{ $empleado->facebook }}">
                                        </div>
                                    </div>
                                </div>

                                <!-- Educación Actual -->
                                <h5>Educación Actual</h5>
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="mb-3">
                                            <label class="form-label">¿Estudia Actualmente?</label><br>
                                            <input type="radio" id="estudia_si" name="estudia_actualmente" value="1" {{ $empleado->estudia_actualmente ? 'checked' : '' }}> Sí
                                            <input type="radio" id="estudia_no" name="estudia_actualmente" value="0" {{ !$empleado->estudia_actualmente ? 'checked' : '' }}> No
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="mb-3">
                                            <label for="que_estudia" class="form-label">¿Qué Estudia?</label>
                                            <input type="text" class="form-control" id="que_estudia" name="que_estudia" value="{{ $empleado->que_estudia }}">
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="mb-3">
                                            <label for="horario_estudio" class="form-label">Horario de Estudio</label>
                                            <input type="text" class="form-control" id="horario_estudio" name="horario_estudio" value="{{ $empleado->horario_estudio }}">
                                        </div>
                                    </div>
                                </div>

                                <!-- Herramientas que Domina -->
                                <h5>Herramientas que Domina</h5>
                                <div class="row">
                                    <div class="col-md-2">
                                        <div class="mb-3">
                                            <input type="checkbox" id="domina_computadora" name="domina_computadora" value="1" {{ $empleado->domina_computadora ? 'checked' : '' }}>
                                            <label for="domina_computadora">Computadora</label>
                                        </div>
                                    </div>
                                    <div class="col-md-2">
                                        <div class="mb-3">
                                            <input type="checkbox" id="domina_fax" name="domina_fax" value="1" {{ $empleado->domina_fax ? 'checked' : '' }}>
                                            <label for="domina_fax">Fax</label>
                                        </div>
                                    </div>
                                    <div class="col-md-2">
                                        <div class="mb-3">
                                            <input type="checkbox" id="domina_impresora" name="domina_impresora" value="1" {{ $empleado->domina_impresora ? 'checked' : '' }}>
                                            <label for="domina_impresora">Impresora</label>
                                        </div>
                                    </div>
                                    <div class="col-md-2">
                                        <div class="mb-3">
                                            <input type="checkbox" id="domina_scanner" name="domina_scanner" value="1" {{ $empleado->domina_scanner ? 'checked' : '' }}>
                                            <label for="domina_scanner">Scanner</label>
                                        </div>
                                    </div>
                                    <div class="col-md-2">
                                        <div class="mb-3">
                                            <input type="checkbox" id="domina_maquinas_elec" name="domina_maquinas_elec" value="1" {{ $empleado->domina_maquinas_elec ? 'checked' : '' }}>
                                            <label for="domina_maquinas_elec">Máquinas Eléctricas</label>
                                        </div>
                                    </div>
                                    <div class="col-md-2">
                                        <div class="mb-3">
                                            <input type="checkbox" id="domina_calculadoras" name="domina_calculadoras" value="1" {{ $empleado->domina_calculadoras ? 'checked' : '' }}>
                                            <label for="domina_calculadoras">Calculadoras</label>
                                        </div>
                                    </div>
                                </div>

                                <!-- Preguntas -->
                                <h5>Preguntas</h5>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label">¿Ha trabajado antes en la empresa?</label><br>
                                            <input type="radio" name="ha_trabajado_antes_en_empresa" value="1" {{ $empleado->ha_trabajado_antes_en_empresa ? 'checked' : '' }}> Sí
                                            <input type="radio" name="ha_trabajado_antes_en_empresa" value="0" {{ !$empleado->ha_trabajado_antes_en_empresa ? 'checked' : '' }}> No
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label">¿Tiene familiares en la empresa?</label><br>
                                            <input type="radio" name="familiares_en_empresa" value="1" {{ $empleado->familiares_en_empresa ? 'checked' : '' }}> Sí
                                            <input type="radio" name="familiares_en_empresa" value="0" {{ !$empleado->familiares_en_empresa ? 'checked' : '' }}> No
                                        </div>
                                    </div>
                                </div>

                                <!-- Competencias y Fortalezas -->
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="competencias_laborales" class="form-label">Competencias Laborales</label>
                                            <textarea class="form-control" id="competencias_laborales" name="competencias_laborales" rows="3">{{ $empleado->competencias_laborales }}</textarea>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="fortalezas_profesionales" class="form-label">Fortalezas Profesionales</label>
                                            <textarea class="form-control" id="fortalezas_profesionales" name="fortalezas_profesionales" rows="3">{{ $empleado->fortalezas_profesionales }}</textarea>
                                        </div>
                                    </div>
                                </div>

                                <!-- Salud y Seguro -->
                                <h5>Salud y Seguro</h5>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label">¿Tiene impedimento para trabajar sábados, domingos o feriados?</label><br>
                                            <input type="radio" name="impedimento_sab_dom_fer" value="1" {{ $empleado->impedimento_sab_dom_fer ? 'checked' : '' }}> Sí
                                            <input type="radio" name="impedimento_sab_dom_fer" value="0" {{ !$empleado->impedimento_sab_dom_fer ? 'checked' : '' }}> No
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="problemas_salud_detalle" class="form-label">Detalle de Problemas de Salud</label>
                                            <textarea class="form-control" id="problemas_salud_detalle" name="problemas_salud_detalle" rows="2">{{ $empleado->problemas_salud_detalle }}</textarea>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="afp" class="form-label">AFP</label>
                                            <input type="text" class="form-control" id="afp" name="afp" value="{{ $empleado->afp }}">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="ars" class="form-label">ARS</label>
                                            <input type="text" class="form-control" id="ars" name="ars" value="{{ $empleado->ars }}">
                                        </div>
                                    </div>
                                </div>

                                <!-- Licencia de Conducir -->
                                <h5>Licencia de Conducir</h5>
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="mb-3">
                                            <label class="form-label">¿Sabe Conducir?</label><br>
                                            <input type="radio" name="sabe_conducir" value="1" {{ $empleado->sabe_conducir ? 'checked' : '' }}> Sí
                                            <input type="radio" name="sabe_conducir" value="0" {{ !$empleado->sabe_conducir ? 'checked' : '' }}> No
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="mb-3">
                                            <label for="licencia_categoria" class="form-label">Categoría</label>
                                            <input type="text" class="form-control" id="licencia_categoria" name="licencia_categoria" value="{{ $empleado->licencia_categoria }}">
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="mb-3">
                                            <label for="licencia_vencimiento" class="form-label">Vencimiento</label>
                                            <input type="date" class="form-control" id="licencia_vencimiento" name="licencia_vencimiento" value="{{ $empleado->licencia_vencimiento }}">
                                        </div>
                                    </div>
                                </div>

                                <!-- Disponibilidad -->
                                <h5>Disponibilidad</h5>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="fecha_disponible" class="form-label">Fecha Disponible</label>
                                            <input type="date" class="form-control" id="fecha_disponible" name="fecha_disponible" value="{{ $empleado->fecha_disponible }}">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label">Acepta Cambio de Horario</label><br>
                                            <input type="radio" name="acepta_cambio_horario" value="1" {{ $empleado->acepta_cambio_horario ? 'checked' : '' }}> Sí
                                            <input type="radio" name="acepta_cambio_horario" value="0" {{ !$empleado->acepta_cambio_horario ? 'checked' : '' }}> No
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label">Acepta Cambio de Lugar</label><br>
                                            <input type="radio" name="acepta_cambio_lugar" value="1" {{ $empleado->acepta_cambio_lugar ? 'checked' : '' }}> Sí
                                            <input type="radio" name="acepta_cambio_lugar" value="0" {{ !$empleado->acepta_cambio_lugar ? 'checked' : '' }}> No
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-12">
                                        <label class="form-label">Disponibilidad de Horario</label><br>
                                        <input type="checkbox" id="disp_diurno" name="disp_diurno" value="1" {{ $empleado->disp_diurno ? 'checked' : '' }}> Diurno
                                        <input type="checkbox" id="disp_nocturno" name="disp_nocturno" value="1" {{ $empleado->disp_nocturno ? 'checked' : '' }}> Nocturno
                                        <input type="checkbox" id="disp_rotativo" name="disp_rotativo" value="1" {{ $empleado->disp_rotativo ? 'checked' : '' }}> Rotativo
                                        <input type="checkbox" id="disp_domingos" name="disp_domingos" value="1" {{ $empleado->disp_domingos ? 'checked' : '' }}> Domingos
                                        <input type="checkbox" id="disp_feriados" name="disp_feriados" value="1" {{ $empleado->disp_feriados ? 'checked' : '' }}> Feriados
                                    </div>
                                </div>

                                <!-- Información Adicional -->
                                <h5>Información Adicional</h5>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label">¿Tiene cuenta en Banco Caribe/BHD?</label><br>
                                            <input type="radio" name="cuenta_banco_caribe_bhd" value="1" {{ $empleado->cuenta_banco_caribe_bhd ? 'checked' : '' }}> Sí
                                            <input type="radio" name="cuenta_banco_caribe_bhd" value="0" {{ !$empleado->cuenta_banco_caribe_bhd ? 'checked' : '' }}> No
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label">¿Está incluido en Buró de Crédito?</label><br>
                                            <input type="radio" name="incluido_buro_credito" value="1" {{ $empleado->incluido_buro_credito ? 'checked' : '' }}> Sí
                                            <input type="radio" name="incluido_buro_credito" value="0" {{ !$empleado->incluido_buro_credito ? 'checked' : '' }}> No
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="referido_por" class="form-label">Referido Por</label>
                                            <input type="text" class="form-control" id="referido_por" name="referido_por" value="{{ $empleado->referido_por }}">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="referido_parentesco" class="form-label">Parentesco</label>
                                            <input type="text" class="form-control" id="referido_parentesco" name="referido_parentesco" value="{{ $empleado->referido_parentesco }}">
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="emergencia_contacto_nombre" class="form-label">Contacto de Emergencia - Nombre</label>
                                            <input type="text" class="form-control" id="emergencia_contacto_nombre" name="emergencia_contacto_nombre" value="{{ $empleado->emergencia_contacto_nombre }}">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="emergencia_parentesco" class="form-label">Parentesco</label>
                                            <input type="text" class="form-control" id="emergencia_parentesco" name="emergencia_parentesco" value="{{ $empleado->emergencia_parentesco }}">
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="emergencia_telefonos" class="form-label">Teléfonos de Emergencia</label>
                                            <input type="text" class="form-control" id="emergencia_telefonos" name="emergencia_telefonos" value="{{ $empleado->emergencia_telefonos }}">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="medio_informo_vacante" class="form-label">Medio por el que se informó de la vacante</label>
                                            <input type="text" class="form-control" id="medio_informo_vacante" name="medio_informo_vacante" value="{{ $empleado->medio_informo_vacante }}">
                                        </div>
                                    </div>
                                </div>

                                <!-- Firma -->
                                <h5>Firma</h5>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="firma_nombre" class="form-label">Nombre de Firma</label>
                                            <input type="text" class="form-control" id="firma_nombre" name="firma_nombre" value="{{ $empleado->firma_nombre }}">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="fecha_firma" class="form-label">Fecha de Firma</label>
                                            <input type="date" class="form-control" id="fecha_firma" name="fecha_firma" value="{{ $empleado->fecha_firma }}">
                                        </div>
                                    </div>
                                </div>

                                <!-- Gestión Humana -->
                                <h5>Uso Exclusivo Gestión Humana</h5>
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="mb-3">
                                            <label class="form-label">Seleccionado</label><br>
                                            <input type="radio" name="seleccionado" value="1" {{ $empleado->seleccionado ? 'checked' : '' }}> Sí
                                            <input type="radio" name="seleccionado" value="0" {{ !$empleado->seleccionado ? 'checked' : '' }}> No
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="mb-3">
                                            <label for="puesto_aplicado" class="form-label">Puesto Aplicado</label>
                                            <input type="text" class="form-control" id="puesto_aplicado" name="puesto_aplicado" value="{{ $empleado->puesto_aplicado }}">
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="mb-3">
                                            <label for="banca" class="form-label">Banca</label>
                                            <input type="text" class="form-control" id="banca" name="banca" value="{{ $empleado->banca }}">
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="mb-3">
                                            <label for="horario_trabajo" class="form-label">Horario de Trabajo</label>
                                            <input type="text" class="form-control" id="horario_trabajo" name="horario_trabajo" value="{{ $empleado->horario_trabajo }}">
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="mb-3">
                                            <label for="fecha_ingreso" class="form-label">Fecha de Ingreso</label>
                                            <input type="date" class="form-control" id="fecha_ingreso" name="fecha_ingreso" value="{{ $empleado->fecha_ingreso }}">
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="mb-3">
                                            <label for="salario" class="form-label">Salario</label>
                                            <input type="number" step="0.01" class="form-control" id="salario" name="salario" value="{{ $empleado->salario }}">
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="mb-3">
                                            <label for="aprobado_por" class="form-label">Aprobado Por</label>
                                            <input type="text" class="form-control" id="aprobado_por" name="aprobado_por" value="{{ $empleado->aprobado_por }}">
                                        </div>
                                    </div>
                                </div>

                                <button type="submit" class="btn btn-primary">Actualizar Solicitud</button>
                                <a href="{{ route('registro-empleados.index') }}" class="btn btn-secondary">Cancelar</a>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

