@extends('app')

@section('content')
<div class="main-content">
    <div class="page-content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                        <h4 class="mb-sm-0">Detalles del Empleado</h4>
                        <div class="page-title-right">
                            <ol class="breadcrumb m-0">
                                <li class="breadcrumb-item"><a href="{{ route('inicio.index') }}">Inicio</a></li>
                                <li class="breadcrumb-item"><a href="{{ route('recursos-humanos.index') }}">Recursos Humanos</a></li>
                                <li class="breadcrumb-item"><a href="{{ route('registro-empleados.index') }}">Registro Empleados</a></li>
                                <li class="breadcrumb-item active">Detalles</li>
                            </ol>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-lg-12">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title mb-0">Información del Empleado</h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <p><strong>ID Solicitud:</strong> {{ $empleado->solicitud_id }}</p>
                                    <p><strong>Apellidos:</strong> {{ $empleado->apellidos }}</p>
                                    <p><strong>Nombres:</strong> {{ $empleado->nombres }}</p>
                                    <p><strong>Cédula/Pasaporte:</strong> {{ $empleado->cedula_pasaporte }}</p>
                                    <p><strong>Email:</strong> {{ $empleado->email }}</p>
                                    <p><strong>Teléfono Residencial:</strong> {{ $empleado->telefono_residencial }}</p>
                                    <p><strong>Celular:</strong> {{ $empleado->celular }}</p>
                                    <p><strong>Dirección:</strong> {{ $empleado->direccion }}</p>
                                    <p><strong>Fecha de Nacimiento:</strong> {{ $empleado->fecha_nacimiento }}</p>
                                    <p><strong>Estado Civil:</strong> {{ $empleado->estado_civil }}</p>
                                    <p><strong>Seleccionado:</strong> {{ $empleado->seleccionado ? 'Sí' : 'No' }}</p>
                                </div>
                                <div class="col-md-6">
                                    <p><strong>Puesto Aplicado:</strong> {{ $empleado->puesto_aplicado }}</p>
                                    <p><strong>Fecha de Ingreso:</strong> {{ $empleado->fecha_ingreso }}</p>
                                    <p><strong>Salario:</strong> {{ $empleado->salario }}</p>
                                    <p><strong>Fecha de Solicitud:</strong> {{ $empleado->created_at }}</p>
                                </div>
                            </div>
                            <a href="{{ route('registro-empleados.index') }}" class="btn btn-secondary">Volver</a>
                            <a href="{{ route('registro-empleados.edit', $empleado->solicitud_id) }}" class="btn btn-warning">Editar</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
