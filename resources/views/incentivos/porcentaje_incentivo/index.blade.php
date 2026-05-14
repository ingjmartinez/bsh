@extends('app')

@section('content')
    <div class="main-content">
        <div class="page-content">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-12">
                        <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                            <h4 class="mb-sm-0">Porcentaje Incentivo</h4>
                            <div class="page-title-right">
                                <ol class="breadcrumb m-0">
                                    <li class="breadcrumb-item"><a href="{{ route('inicio.index') }}">Inicio</a></li>
                                    <li class="breadcrumb-item"><a href="{{ route('incentivos.index') }}">Incentivos</a></li>
                                    <li class="breadcrumb-item active">Porcentaje Incentivo</li>
                                </ol>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-lg-12">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="card-title mb-0">Crear registro</h5>
                            </div>
                            <div class="card-body">
                                @if (session('success'))
                                    <div class="alert alert-success">{{ session('success') }}</div>
                                @endif

                                @if ($errors->any())
                                    <div class="alert alert-danger">
                                        <ul class="mb-0">
                                            @foreach ($errors->all() as $error)
                                                <li>{{ $error }}</li>
                                            @endforeach
                                        </ul>
                                    </div>
                                @endif

                                <form action="{{ route('incentivos.porcentaje-incentivo.store') }}" method="POST" class="row g-2">
                                    @csrf
                                    <div class="col-md-3">
                                        <label class="form-label mb-1">Posicion</label>
                                        <input type="text" name="posicion" class="form-control" value="{{ old('posicion') }}" required>
                                    </div>
                                    <div class="col-md-2">
                                        <label class="form-label mb-1">Bono %</label>
                                        <div class="input-group">
                                            <input type="number" name="bono_pct" min="0" max="100" step="0.01" class="form-control" value="{{ old('bono_pct', 0) }}" required>
                                            <span class="input-group-text">%</span>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label mb-1">Notas</label>
                                        <input type="text" name="notas" class="form-control" value="{{ old('notas') }}">
                                    </div>
                                    <div class="col-md-1 d-grid">
                                        <label class="form-label mb-1">&nbsp;</label>
                                        <button type="submit" class="btn btn-primary">Guardar</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-lg-12">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="card-title mb-0">Listado</h5>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-bordered table-sm align-middle mb-0">
                                        <thead class="table-light">
                                            <tr>
                                                <th style="min-width: 200px;">Posicion</th>
                                                <th style="min-width: 140px;">Bono %</th>
                                                <th style="min-width: 280px;">Notas</th>
                                                <th style="min-width: 180px;">Acciones</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse($registros as $registro)
                                                <tr>
                                                    <td>
                                                        <form id="form-pct-{{ $registro->id }}" action="{{ route('incentivos.porcentaje-incentivo.update', $registro->id) }}" method="POST">
                                                            @csrf
                                                            @method('PUT')
                                                        </form>
                                                        <input type="text" name="posicion" form="form-pct-{{ $registro->id }}" class="form-control form-control-sm" value="{{ $registro->posicion }}" required>
                                                    </td>
                                                    <td>
                                                        <div class="input-group input-group-sm">
                                                            <input type="number" name="bono_pct" form="form-pct-{{ $registro->id }}" min="0" max="100" step="0.01" class="form-control" value="{{ number_format((float) $registro->bono_pct, 2, '.', '') }}" required>
                                                            <span class="input-group-text">%</span>
                                                        </div>
                                                    </td>
                                                    <td>
                                                        <input type="text" name="notas" form="form-pct-{{ $registro->id }}" class="form-control form-control-sm" value="{{ $registro->notas }}">
                                                    </td>
                                                    <td>
                                                        <div class="d-flex gap-1">
                                                            <button type="submit" form="form-pct-{{ $registro->id }}" class="btn btn-success btn-sm">Actualizar</button>
                                                            <form action="{{ route('incentivos.porcentaje-incentivo.destroy', $registro->id) }}" method="POST" onsubmit="return confirm('Seguro que deseas eliminar este registro?');">
                                                                @csrf
                                                                @method('DELETE')
                                                                <button type="submit" class="btn btn-danger btn-sm">Eliminar</button>
                                                            </form>
                                                        </div>
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="4" class="text-center text-muted">No hay registros disponibles.</td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>

                                <div class="mt-3">
                                    {{ $registros->links() }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
