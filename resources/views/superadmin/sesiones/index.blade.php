@extends('app')

@section('content')
    <div class="main-content">
        <div class="page-content">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-12">
                        <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                            <h4 class="mb-sm-0">Monitoreo de Sesiones</h4>
                            <div class="page-title-right">
                                <ol class="breadcrumb m-0">
                                    <li class="breadcrumb-item"><a href="{{ route('inicio.index') }}">Inicio</a></li>
                                    <li class="breadcrumb-item"><a href="{{ route('mantenimiento.index') }}">Mantenimientos</a></li>
                                    <li class="breadcrumb-item active">Sesiones</li>
                                </ol>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="card-title mb-0">Inicio de sesion de usuarios</h5>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-bordered align-middle mb-0" id="tabla-sesiones-superadmin">
                                        <thead class="table-light">
                                            <tr>
                                                <th>Usuario</th>
                                                <th>Email</th>
                                                <th>Estado</th>
                                                <th>Activo desde</th>
                                                <th>Ultimo inicio de sesion</th>
                                                <th>Ultima actividad</th>
                                                <th>Inicio anterior</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse($usuarios as $item)
                                                <tr>
                                                    <td>{{ $item['name'] }}</td>
                                                    <td>{{ $item['email'] }}</td>
                                                    <td>
                                                        @if($item['esta_activo'])
                                                            <span class="badge bg-success-subtle text-success">Activo</span>
                                                        @else
                                                            <span class="badge bg-secondary-subtle text-secondary">Inactivo</span>
                                                        @endif
                                                    </td>
                                                    <td>{{ optional($item['activo_desde'])->format('d/m/Y h:i:s A') ?? '-' }}</td>
                                                    <td>{{ optional($item['ultimo_inicio_sesion'])->format('d/m/Y h:i:s A') ?? '-' }}</td>
                                                    <td>{{ optional($item['ultima_actividad'])->format('d/m/Y h:i:s A') ?? '-' }}</td>
                                                    <td>{{ optional($item['inicio_anterior'])->format('d/m/Y h:i:s A') ?? '-' }}</td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="7" class="text-center text-muted">No hay usuarios para mostrar.</td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('script')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            if (window.$ && $.fn.DataTable) {
                $('#tabla-sesiones-superadmin').DataTable({
                    pageLength: 25,
                    order: [[2, 'desc'], [0, 'asc']],
                    language: {
                        search: 'Buscar:',
                        lengthMenu: 'Mostrar _MENU_ registros',
                        info: 'Mostrando _START_ a _END_ de _TOTAL_ registros',
                        infoEmpty: 'Mostrando 0 a 0 de 0 registros',
                        emptyTable: 'No hay datos disponibles',
                        paginate: {
                            first: 'Primero',
                            last: 'Ultimo',
                            next: 'Siguiente',
                            previous: 'Anterior'
                        }
                    }
                });
            }
        });
    </script>
@endsection

