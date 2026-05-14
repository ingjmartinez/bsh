@extends('app')

@section('content')
    @php
        $agenciasCumplen = collect($filas ?? [])->filter(function ($item) {
            return (bool) ($item->aplica ?? false) === true;
        })->count();

        $agenciasNoCumplen = collect($filas ?? [])->filter(function ($item) {
            return (bool) ($item->aplica ?? false) === false;
        })->count();
    @endphp

    <div class="main-content">
        <div class="page-content">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-12">
                        <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                            <h4 class="mb-sm-0">agencia_plan</h4>
                            <div class="page-title-right">
                                <ol class="breadcrumb m-0">
                                    <li class="breadcrumb-item"><a href="{{ route('inicio.index') }}">Inicio</a></li>
                                    <li class="breadcrumb-item"><a href="{{ route('comercial.index') }}">Comercial</a></li>
                                    <li class="breadcrumb-item active">agencia_plan</li>
                                </ol>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-body">
                                <form method="GET" action="{{ route('comercial.agencia-plan') }}" class="row g-2 align-items-end" id="form-filtro-agencia-plan">
                                    <input type="hidden" name="aplicar" value="1">
                                    <div class="col-12 col-md-4 col-xl-3">
                                        <label class="form-label">Mes</label>
                                        <input type="month" name="mes" class="form-control" value="{{ $mes }}" required>
                                    </div>
                                    <div class="col-12 col-md-4 col-xl-3">
                                        <label class="form-label">Sistema</label>
                                        <select name="sistema" class="form-select" required>
                                            @foreach($sistemas as $itemSistema)
                                                <option value="{{ $itemSistema }}" {{ $sistema === $itemSistema ? 'selected' : '' }}>{{ $itemSistema }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-12 col-md-4 col-xl-2 d-grid">
                                        <button type="submit" class="btn btn-primary" id="btn-generar-data-agencia-plan">
                                            <i class="ri-filter-3-line me-1"></i>Generar Data
                                        </button>
                                    </div>
                                    <div class="col-12 col-md-4 col-xl-3 d-grid">
                                        @if(($filas ?? collect())->count() > 0)
                                            <a href="{{ route('comercial.agencia-plan.export', ['mes' => $mes, 'sistema' => $sistema]) }}" class="btn btn-success" id="btn-exportar-agencia-plan">
                                                <i class="ri-file-excel-2-line me-1"></i>Exportar a Excel
                                            </a>
                                        @else
                                            <button type="button" class="btn btn-success" disabled>
                                                <i class="ri-file-excel-2-line me-1"></i>Exportar a Excel
                                            </button>
                                        @endif
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>

                    <div class="col-12">
                        @if($filtrosAplicados ?? false)
                            @if($fechaCorteData)
                                <div class="alert alert-info py-2 mb-3" role="alert">
                                    <strong>Tabla origen:</strong> {{ $tablaOrigen }} | <strong>Fecha corte con data:</strong> {{ $fechaCorteData }} |
                                    <strong>Conteo por agencia:</strong> hacia atrás desde la fecha corte hasta {{ $diasObjetivo }} días efectivos de venta.
                                </div>
                            @else
                                <div class="alert alert-warning py-2 mb-3" role="alert">
                                    No hay data para el mes seleccionado en {{ $tablaOrigen }}.
                                </div>
                            @endif
                        @else
                            <div class="alert alert-info py-2 mb-3" role="alert">
                                <strong>Sin resultados cargados:</strong> selecciona filtros y presiona <strong>Generar Data</strong>.
                            </div>
                        @endif
                    </div>

                    <div class="col-12">
                        <div class="row g-2 mb-3">
                            <div class="col-12 col-md-6 col-xl-3">
                                <div class="card border border-success-subtle mb-0">
                                    <div class="card-body py-2 px-3">
                                        <small class="text-muted d-block">Agencias que cumplen</small>
                                        <h6 class="mb-0 text-success">{{ number_format($agenciasCumplen, 0) }}</h6>
                                    </div>
                                </div>
                            </div>
                            <div class="col-12 col-md-6 col-xl-3">
                                <div class="card border border-danger-subtle mb-0">
                                    <div class="card-body py-2 px-3">
                                        <small class="text-muted d-block">Agencias que no cumplen</small>
                                        <h6 class="mb-0 text-danger">{{ number_format($agenciasNoCumplen, 0) }}</h6>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-12">
                        <div class="card">
                            <div class="card-header d-flex align-items-center justify-content-between">
                                <h5 class="card-title mb-0">Agencias con {{ $diasObjetivo }} días con venta</h5>
                                <span class="badge bg-primary-subtle text-primary">{{ number_format(($filas ?? collect())->count(), 0) }} registros</span>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-bordered table-striped align-middle mb-0" id="table-agencia-plan">
                                        <thead>
                                            <tr>
                                                <th>Agencia</th>
                                                <th>Nombre Agencia</th>
                                                <th>Sistema</th>
                                                <th>Días Efectivos</th>
                                                <th>Días Faltantes</th>
                                                <th>Aplica</th>
                                                <th>Monto 90 días</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse($filas as $row)
                                                <tr>
                                                    <td>{{ $row->agencia_id }}</td>
                                                    <td>{{ $row->nombre_agencia }}</td>
                                                    <td>{{ $sistema }}</td>
                                                    <td>{{ number_format((int) ($row->dias_con_venta ?? 0), 0) }}</td>
                                                    <td>{{ number_format((int) ($row->dias_faltantes ?? 0), 0) }}</td>
                                                    <td>
                                                        @if(($row->aplica ?? false) === true)
                                                            <span class="badge bg-success">Sí</span>
                                                        @else
                                                            <span class="badge bg-danger">No</span>
                                                        @endif
                                                    </td>
                                                    <td>RD$ {{ number_format((float) ($row->monto_90_dias ?? 0), 2) }}</td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="7" class="text-center text-muted">
                                                        {{ ($filtrosAplicados ?? false) ? 'No hay agencias que cumplan los ' . $diasObjetivo . ' días con venta para el filtro aplicado.' : 'Presiona Generar Data para cargar la información.' }}
                                                    </td>
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
        const formFiltro = document.getElementById('form-filtro-agencia-plan');
        const btnGenerarData = document.getElementById('btn-generar-data-agencia-plan');
        const btnExportar = document.getElementById('btn-exportar-agencia-plan');

        if (formFiltro && btnGenerarData) {
            formFiltro.addEventListener('submit', function () {
                btnGenerarData.disabled = true;

                Swal.fire({
                    title: 'Procesando datos...',
                    html: `
                        <div class="d-flex align-items-center justify-content-center gap-2">
                            <div class="form-check form-switch mb-0">
                                <input class="form-check-input" type="checkbox" role="switch" checked disabled>
                            </div>
                            <span>Ejecutando consulta de agencias, por favor espere.</span>
                        </div>
                    `,
                    allowOutsideClick: false,
                    allowEscapeKey: false,
                    didOpen: () => Swal.showLoading(),
                });
            });
        }

        if (btnExportar) {
            btnExportar.addEventListener('click', function (event) {
                event.preventDefault();
                const exportUrl = this.getAttribute('href');
                if (!exportUrl) {
                    return;
                }

                Swal.fire({
                    title: 'Procesando datos...',
                    html: `
                        <div class="d-flex align-items-center justify-content-center gap-2">
                            <div class="form-check form-switch mb-0">
                                <input class="form-check-input" type="checkbox" role="switch" checked disabled>
                            </div>
                            <span>Preparando archivo Excel, por favor espere.</span>
                        </div>
                    `,
                    allowOutsideClick: false,
                    allowEscapeKey: false,
                    didOpen: () => Swal.showLoading(),
                });

                window.location.href = exportUrl;
            });
        }

        if (window.$ && $.fn.DataTable && $('#table-agencia-plan').length) {
            $('#table-agencia-plan').DataTable({
                destroy: true,
                responsive: true,
                language: {
                    url: '/json/es-DO.json',
                    search: 'Buscar:',
                    lengthMenu: 'Mostrar _MENU_ registros',
                    info: 'Mostrando _START_ a _END_ de _TOTAL_ registros',
                    paginate: { first: 'Primera', last: 'Última', next: 'Siguiente', previous: 'Anterior' }
                },
                order: [[6, 'desc']],
            });
        }
    });
</script>
@endsection
