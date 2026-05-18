@extends('app')

@section('content')
    <div class="main-content">
        <div class="page-content">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-12">
                        <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                            <h4 class="mb-sm-0">Venta Gerencial</h4>
                            <div class="page-title-right">
                                <ol class="breadcrumb m-0">
                                    <li class="breadcrumb-item"><a href="{{ route('inicio.index') }}">Inicio</a></li>
                                    <li class="breadcrumb-item"><a href="{{ route('gerencia.index') }}">Gerencia</a></li>
                                    <li class="breadcrumb-item active">Venta Gerencial</li>
                                </ol>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-body">
                                <form method="GET" action="{{ route('gerencia.venta-gerencial') }}" class="row g-2 align-items-end" id="form-venta-gerencial">
                                    <input type="hidden" name="consultar" value="1">
                                    <div class="col-12 col-md-4 col-lg-2">
                                        <label class="form-label">Fecha</label>
                                        <input type="date" name="fecha" class="form-control" value="{{ $fechaSeleccionada ?? now()->format('Y-m-d') }}">
                                    </div>
                                    <div class="col-12 col-md-4 col-lg-2">
                                        <label class="form-label">Sistema</label>
                                        <select name="sistema" class="form-select">
                                            <option value="todos" {{ ($sistemaSeleccionado ?? 'todos') === 'todos' ? 'selected' : '' }}>Todos</option>
                                            <option value="lotobet" {{ ($sistemaSeleccionado ?? 'todos') === 'lotobet' ? 'selected' : '' }}>Lotobet</option>
                                            <option value="lotonet" {{ ($sistemaSeleccionado ?? 'todos') === 'lotonet' ? 'selected' : '' }}>Lotonet</option>
                                        </select>
                                    </div>
                                    <div class="col-12 col-md-4 col-lg-3">
                                        <label class="form-label">Buscar</label>
                                        <input type="text" id="buscar-agencia-gerencial" class="form-control" placeholder="Nombre o terminal">
                                    </div>
                                    <div class="col-12 col-lg-5">
                                        <label class="form-label d-none d-lg-block">Acciones</label>
                                        <div class="d-flex flex-wrap flex-lg-nowrap gap-2">
                                            <button type="submit" class="btn btn-primary" id="btn-buscar-venta-gerencial">
                                            <i class="ri-search-line me-1"></i>Buscar
                                            </button>
                                            <a href="{{ route('gerencia.venta-gerencial') }}" class="btn btn-light">Limpiar</a>
                                            @if($debeConsultar ?? false)
                                            <a href="{{ route('gerencia.venta-gerencial.export.excel', ['fecha' => ($fechaSeleccionada ?? now()->format('Y-m-d')), 'sistema' => ($sistemaSeleccionado ?? 'todos')]) }}" class="btn btn-success" id="btn-excel-venta-gerencial">
                                            <i class="ri-file-excel-2-line me-1"></i>Excel
                                            </a>
                                            @else
                                            <button type="button" class="btn btn-success" disabled>
                                            <i class="ri-file-excel-2-line me-1"></i>Excel
                                            </button>
                                            @endif
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-12">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="card-title mb-0">Reporte de Venta Gerencial ({{ strtoupper($sistemaSeleccionado ?? 'todos') }})</h5>
                            </div>
                            <div class="card-body">
                                @php
                                    $totalVendido = collect($resumenAgencias ?? [])->sum('total_vendido');
                                    $totalPremiosSacados = collect($resumenAgencias ?? [])->sum('premios_sacados');
                                    $totalUtilidad = $totalVendido - $totalPremiosSacados;
                                    $hayResultados = collect($resumenAgencias ?? [])->count() > 0;
                                @endphp
                                @if(!($debeConsultar ?? false))
                                    <div class="alert alert-info py-2 mb-3" role="alert">
                                        Selecciona los filtros y presiona <strong>Buscar</strong> para cargar el reporte.
                                    </div>
                                @elseif(!$hayResultados)
                                    <div class="alert alert-warning py-2 mb-3" role="alert">
                                        No hay datos para el filtro seleccionado.
                                    </div>
                                @endif
                                <div class="table-responsive">
                                    <table class="table table-bordered table-striped align-middle mb-0" id="table-rentabilidad-agencias">
                                        <thead>
                                            <tr>
                                                <th>Agencia</th>
                                                <th>Ventas</th>
                                                <th>Premios Sacados</th>
                                                <th>Utilidad Bruta</th>
                                            </tr>
                                        </thead>
                                        <tbody id="tbody-rentabilidad-agencias">
                                            @if($hayResultados)
                                            @foreach (($resumenAgencias ?? []) as $item)
                                                <tr>
                                                    <td>
                                                        <div class="fw-medium">{{ $item['nombre_agencia'] ?? ($item['agencia'] ?? 'SIN AGENCIA') }}</div>
                                                        <small class="text-muted">Terminal: {{ $item['terminal'] ?? ($item['agencia'] ?? '-') }}</small>
                                                    </td>
                                                    <td>RD$ {{ number_format((float) ($item['total_vendido'] ?? 0), 2) }}</td>
                                                    <td>RD$ {{ number_format((float) ($item['premios_sacados'] ?? 0), 2) }}</td>
                                                    <td class="{{ ((float) ($item['utilidad_bruta'] ?? 0)) >= 0 ? 'text-success' : 'text-danger' }} fw-medium">
                                                        RD$ {{ number_format((float) ($item['utilidad_bruta'] ?? 0), 2) }}
                                                    </td>
                                                </tr>
                                            @endforeach
                                            @endif
                                        </tbody>
                                        <tfoot>
                                            <tr class="fw-semibold table-light">
                                                <td>Total</td>
                                                <td>RD$ {{ number_format((float) $totalVendido, 2) }}</td>
                                                <td>RD$ {{ number_format((float) $totalPremiosSacados, 2) }}</td>
                                                <td class="{{ $totalUtilidad >= 0 ? 'text-success' : 'text-danger' }}">RD$ {{ number_format((float) $totalUtilidad, 2) }}</td>
                                            </tr>
                                        </tfoot>
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
        const form = document.getElementById('form-venta-gerencial');
        const btnBuscar = document.getElementById('btn-buscar-venta-gerencial');
        const btnExcel = document.getElementById('btn-excel-venta-gerencial');

        if (form && btnBuscar) {
            form.addEventListener('submit', function () {
                btnBuscar.disabled = true;

                Swal.fire({
                    title: 'Cargando...',
                    text: 'Generando venta gerencial, por favor espera.',
                    allowOutsideClick: false,
                    allowEscapeKey: false,
                    didOpen: () => Swal.showLoading(),
                });
            });
        }

        if (btnExcel) {
            btnExcel.addEventListener('click', function () {
                Swal.fire({
                    title: 'Preparando Excel...',
                    text: 'Generando archivo, por favor espera.',
                    allowOutsideClick: false,
                    allowEscapeKey: false,
                    didOpen: () => Swal.showLoading(),
                });
            });
        }

        if (!(window.$ && $.fn.DataTable)) {
            return;
        }

        const tableElement = $('#table-rentabilidad-agencias');
        if (!tableElement.length) {
            return;
        }

        const dataTable = tableElement.DataTable({
            responsive: true,
            pageLength: 25,
            order: [[1, 'desc']],
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
            },
            dom: 'lrtip'
        });

        const buscarInput = document.getElementById('buscar-agencia-gerencial');
        if (buscarInput) {
            buscarInput.addEventListener('input', function () {
                dataTable.search(this.value || '').draw();
            });
        }
    });
</script>
@endsection

