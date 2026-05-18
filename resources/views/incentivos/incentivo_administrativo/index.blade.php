@extends('app')

@section('content')
    <div class="main-content">
        <div class="page-content">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-12">
                        <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                            <h4 class="mb-sm-0">Incentivo Administrativo</h4>
                            <div class="page-title-right">
                                <ol class="breadcrumb m-0">
                                    <li class="breadcrumb-item"><a href="{{ route('inicio.index') }}">Inicio</a></li>
                                    <li class="breadcrumb-item"><a href="{{ route('incentivos.index') }}">Incentivos</a></li>
                                    <li class="breadcrumb-item active">Incentivo Administrativo</li>
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

                                <form action="{{ route('incentivos.incentivo-administrativo.store') }}" method="POST" class="row g-2">
                                    @csrf
                                    <div class="col-md-3">
                                        <label class="form-label mb-1">Grupo</label>
                                        <select name="grupo" id="grupo_create" class="form-select js-grupo-select" data-pct-target="pct_total_create" required>
                                            <option value="">Seleccione</option>
                                            @foreach($posiciones as $posicion)
                                                <option
                                                    value="{{ $posicion->posicion }}"
                                                    data-bono="{{ number_format((float) $posicion->bono_pct, 2, '.', '') }}"
                                                    {{ old('grupo') === $posicion->posicion ? 'selected' : '' }}>
                                                    {{ $posicion->posicion }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label mb-1">Nombre</label>
                                        <input type="text" name="nombre" class="form-control" value="{{ old('nombre') }}" required>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label mb-1">Empresa</label>
                                        <input type="text" name="empresa" class="form-control" value="{{ old('empresa') }}" required>
                                    </div>
                                    <div class="col-md-2">
                                        <label class="form-label mb-1">% Total</label>
                                        <div class="input-group">
                                            <input type="number" id="pct_total_create" name="pct_total" min="0" max="100" step="0.01" class="form-control" value="{{ old('pct_total', 0) }}" required>
                                            <span class="input-group-text">%</span>
                                        </div>
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
                                                <th style="min-width: 180px;">Grupo</th>
                                                <th style="min-width: 260px;">Nombre</th>
                                                <th style="min-width: 160px;">Empresa</th>
                                                <th style="min-width: 140px;">% Total</th>
                                                <th style="min-width: 180px;">Acciones</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse($registros as $registro)
                                                <tr>
                                                    <td>
                                                        <form id="form-adm-{{ $registro->id }}" action="{{ route('incentivos.incentivo-administrativo.update', $registro->id) }}" method="POST">
                                                            @csrf
                                                            @method('PUT')
                                                        </form>
                                                        <select
                                                            name="grupo"
                                                            form="form-adm-{{ $registro->id }}"
                                                            class="form-select form-select-sm js-grupo-select"
                                                            data-pct-target="pct_total_{{ $registro->id }}"
                                                            required>
                                                            @php
                                                                $grupoExiste = $posiciones->contains('posicion', $registro->grupo);
                                                            @endphp
                                                            @if(!$grupoExiste)
                                                                <option value="{{ $registro->grupo }}" selected>{{ $registro->grupo }}</option>
                                                            @endif
                                                            @foreach($posiciones as $posicion)
                                                                <option
                                                                    value="{{ $posicion->posicion }}"
                                                                    data-bono="{{ number_format((float) $posicion->bono_pct, 2, '.', '') }}"
                                                                    {{ $registro->grupo === $posicion->posicion ? 'selected' : '' }}>
                                                                    {{ $posicion->posicion }}
                                                                </option>
                                                            @endforeach
                                                        </select>
                                                    </td>
                                                    <td>
                                                        <input type="text" name="nombre" form="form-adm-{{ $registro->id }}" class="form-control form-control-sm" value="{{ $registro->nombre }}" required>
                                                    </td>
                                                    <td>
                                                        <input type="text" name="empresa" form="form-adm-{{ $registro->id }}" class="form-control form-control-sm" value="{{ $registro->empresa }}" required>
                                                    </td>
                                                    <td>
                                                        <div class="input-group input-group-sm">
                                                            <input type="number" id="pct_total_{{ $registro->id }}" name="pct_total" form="form-adm-{{ $registro->id }}" min="0" max="100" step="0.01" class="form-control" value="{{ number_format((float) $registro->pct_total, 2, '.', '') }}" required>
                                                            <span class="input-group-text">%</span>
                                                        </div>
                                                    </td>
                                                    <td>
                                                        <div class="d-flex gap-1">
                                                            <button type="submit" form="form-adm-{{ $registro->id }}" class="btn btn-success btn-sm">Actualizar</button>
                                                            <form action="{{ route('incentivos.incentivo-administrativo.destroy', $registro->id) }}" method="POST" onsubmit="return confirm('Seguro que deseas eliminar este registro?');">
                                                                @csrf
                                                                @method('DELETE')
                                                                <button type="submit" class="btn btn-danger btn-sm">Eliminar</button>
                                                            </form>
                                                        </div>
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="5" class="text-center text-muted">No hay registros disponibles.</td>
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

@section('script')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        function syncPctFromGrupo(selectEl) {
            const targetId = selectEl.dataset.pctTarget;
            if (!targetId) {
                return;
            }

            const targetInput = document.getElementById(targetId);
            if (!targetInput) {
                return;
            }

            const option = selectEl.selectedOptions && selectEl.selectedOptions.length > 0
                ? selectEl.selectedOptions[0]
                : null;
            const bono = option ? parseFloat(option.dataset.bono || '0') : 0;

            if (!Number.isNaN(bono)) {
                targetInput.value = bono.toFixed(2);
            }
        }

        document.querySelectorAll('.js-grupo-select').forEach(function (selectEl) {
            selectEl.addEventListener('change', function () {
                syncPctFromGrupo(selectEl);
            });
        });
    });
</script>
@endsection

