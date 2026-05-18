@extends('app')

@section('content')
<div class="main-content">
    <div class="page-content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                        <h4 class="mb-sm-0">Registro de Empleados</h4>
                        <div class="page-title-right">
                            <ol class="breadcrumb m-0">
                                <li class="breadcrumb-item"><a href="{{ route('inicio.index') }}">Inicio</a></li>
                                <li class="breadcrumb-item"><a href="{{ route('recursos-humanos.index') }}">Recursos Humanos</a></li>
                                <li class="breadcrumb-item active">Registro Empleados</li>
                            </ol>
                        </div>
                    </div>
                </div>
            </div>

            @if(session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif

            <div class="row">
                <div class="col-lg-12">
                    <div class="card">
                        <div class="card-header d-flex align-items-center justify-content-between">
                            <h5 class="card-title mb-0">Empleados Registrados</h5>
                            <a href="{{ route('registro-empleados.create') }}" class="btn btn-success">Crear Empleado</a>
                        </div>
                        <div class="card-body">
                            <table class="table table-bordered table-striped">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Nombres</th>
                                        <th>Apellidos</th>
                                        <th>Cédula</th>
                                        <th>Email</th>
                                        <th>Fecha Solicitud</th>
                                        <th>Seleccionado</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($empleados as $empleado)
                                    <tr>
                                        <td>{{ $empleado->solicitud_id }}</td>
                                        <td>{{ $empleado->nombres }}</td>
                                        <td>{{ $empleado->apellidos }}</td>
                                        <td>{{ $empleado->cedula_pasaporte }}</td>
                                        <td>{{ $empleado->email }}</td>
                                        <td>{{ $empleado->created_at }}</td>
                                        <td>{{ $empleado->seleccionado ? 'Sí' : 'No' }}</td>
                                        <td>
                                            <a href="{{ route('registro-empleados.show', $empleado->solicitud_id) }}" class="btn btn-sm btn-info">Ver</a>
                                            <a href="{{ route('registro-empleados.edit', $empleado->solicitud_id) }}" class="btn btn-sm btn-warning">Editar</a>
                                            <form action="{{ route('registro-empleados.destroy', $empleado->solicitud_id) }}" method="POST" style="display:inline;">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('¿Estás seguro?')">Eliminar</button>
                                            </form>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

