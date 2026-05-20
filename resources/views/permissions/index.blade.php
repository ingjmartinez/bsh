@extends('app')

@section('content')
    <div class="main-content">
        <div class="page-content">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-12">
                        <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                            <h4 class="mb-sm-0">Permisos</h4>
                            <div class="page-title-right">
                                <ol class="breadcrumb m-0">
                                    <li class="breadcrumb-item"><a href="{{ route('inicio.index') }}">Inicio</a></li>
                                    <li class="breadcrumb-item"><a href="{{ route('mantenimiento.index') }}">Mantenimientos</a></li>
                                    <li class="breadcrumb-item active">Permisos</li>
                                </ol>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-lg-12">
                        <div class="card">
                            <div class="card-header">
                                <div class="row align-items-center">
                                    <div class="col-12 col-md-6 mb-3 mb-md-0">
                                        <h5 class="card-title mb-0">Listado de Permisos</h5>
                                    </div>
                                    <div class="col-12 col-md-6">
                                        <div class="d-flex flex-column flex-sm-row gap-2 justify-content-md-end">
                                            @can('permissions.create')
                                                <a href="{{ route('permissions.create') }}" class="btn btn-primary">
                                                    <i class="ri-add-line align-bottom me-1"></i> Nuevo Permiso
                                                </a>
                                            @endcan
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-bordered table-striped align-middle">
                                        <thead class="table-light">
                                            <tr>
                                                <th class="text-center" style="min-width: 60px;">ID</th>
                                                <th style="min-width: 240px;">Nombre</th>
                                                <th class="text-center" style="min-width: 120px;">Acciones</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse($permissions as $permission)
                                                <tr>
                                                    <td class="text-center">{{ $permission->id }}</td>
                                                    <td>{{ $permission->name }}</td>
                                                    <td class="text-center">
                                                        <div class="d-flex gap-1 justify-content-center flex-nowrap">
                                                            @can('permissions.edit')
                                                                <a href="{{ route('permissions.edit', $permission->id) }}" class="btn btn-sm btn-success" title="Editar">
                                                                    <i class="ri-pencil-line"></i>
                                                                </a>
                                                            @endcan
                                                            @can('permissions.delete')
                                                                <form action="{{ route('permissions.destroy', $permission->id) }}" method="POST" onsubmit="return confirm('Deseas eliminar este permiso?');">
                                                                    @csrf
                                                                    @method('DELETE')
                                                                    <button type="submit" class="btn btn-sm btn-danger" title="Eliminar">
                                                                        <i class="ri-delete-bin-line"></i>
                                                                    </button>
                                                                </form>
                                                            @endcan
                                                        </div>
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="3" class="text-center">No hay permisos registrados.</td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                                <div class="mt-3">
                                    {{ $permissions->links() }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <footer class="footer">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-sm-6">
                        <script>
                            document.write(new Date().getFullYear())
                        </script> © ERP.
                    </div>
                </div>
            </div>
        </footer>
    </div>
@endsection

@section('script')
<script>
    @if(session('success'))
        Swal.fire({
            icon: 'success',
            title: 'Exito',
            text: '{{ session('success') }}',
            timer: 3000,
            showConfirmButton: false
        });
    @endif

    @if(session('error'))
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: '{{ session('error') }}'
        });
    @endif
</script>
@endsection

