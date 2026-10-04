@extends('app')

@section('content')
    <div class="main-content">
        <div class="page-content">
            <div class="container-fluid">
                <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                    <h4 class="mb-sm-0">Premios pagados por productos</h4>
                    <a href="{{ route('reportes.index') }}" class="btn btn-soft-primary btn-sm">Volver a reportes</a>
                </div>

                <div class="card"><div class="card-body">
                    <form id="formPremiosPagados" method="GET" action="{{ route('reportes.premios-pagados-productos') }}" class="row g-3 align-items-end">
                        <div class="col-md-2">
                            <label for="fecha_inicio" class="form-label">Fecha de inicio</label>
                            <input type="date" id="fecha_inicio" name="fecha_inicio" class="form-control" value="{{ $fechaInicio }}" required>
                        </div>
                        <div class="col-md-2">
                            <label for="fecha_fin" class="form-label">Fecha final</label>
                            <input type="date" id="fecha_fin" name="fecha_fin" class="form-control" value="{{ $fechaFin }}" required>
                        </div>
                        <div class="col-md-3">
                            <label for="grupo" class="form-label">Grupo</label>
                            <select id="grupo" name="grupo" class="form-select">
                                <option value="">Todos los grupos</option>
                                @foreach ($gruposAgencia as $grupoAgencia)
                                    <option value="{{ $grupoAgencia }}" @selected($grupoSeleccionado === $grupoAgencia)>{{ $grupoAgencia }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label for="vista" class="form-label">Vista</label>
                            <select id="vista" name="vista" class="form-select">
                                <option value="consolidado" @selected($vistaSeleccionada === 'consolidado')>Consolidado</option>
                                <option value="terminal" @selected($vistaSeleccionada === 'terminal')>Por terminal</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label for="categoria" class="form-label">Categoría</label>
                            <select id="categoria" name="categoria" class="form-select">
                                <option value="todos" @selected($categoriaSeleccionada === 'todos')>Todos</option>
                                <option value="tradicional" @selected($categoriaSeleccionada === 'tradicional')>Tradicional</option>
                                <option value="no_tradicional" @selected($categoriaSeleccionada === 'no_tradicional')>No tradicional</option>
                            </select>
                        </div>
                        <div class="col-md-1"><button class="btn btn-primary" type="submit">Consultar</button></div>
                    </form>
                    @if ($errors->any())
                        <div class="alert alert-danger mt-3" role="alert">{{ $errors->first() }}</div>
                    @endif
                </div></div>

                @if ($reporte['disponibilidad'] !== [])
                    <div class="alert alert-info" role="status">
                        No se encontraron pagos para las terminales del grupo en el período seleccionado.
                        @foreach ($reporte['disponibilidad'] as $fuente)
                            <div>{{ $fuente['nombre'] }}: {{ $fuente['fecha_inicio'] === null ? 'sin datos guardados' : 'datos disponibles del '.$fuente['fecha_inicio'].' al '.$fuente['fecha_fin'] }}</div>
                        @endforeach
                    </div>
                @endif
                @if (! $reporte['clasificacion_disponible'] || $reporte['sin_clasificar'] != 0)
                    <div class="alert alert-warning" role="status">
                        Hay RD$ {{ number_format($reporte['sin_clasificar'], 2) }} en pagos sin producto identificado. El total de «Todos» incluye estos pagos; las columnas por categoría pueden estar incompletas hasta volver a importar los datos con producto.
                    </div>
                @endif

                <div class="card">
                    <div class="card-header"><h5 class="card-title mb-0">Resultados</h5></div>
                    <div class="card-body table-responsive">
                        <table id="tablePremiosPagados" class="table table-bordered table-striped align-middle" style="width:100%">
                            <thead><tr>
                                <th>{{ $vistaSeleccionada === 'consolidado' ? 'Nombre de grupo' : 'Terminal' }}</th>
                                @if ($categoriaSeleccionada !== 'no_tradicional')
                                    <th class="text-end">Pagado tradicional</th>
                                @endif
                                @if ($categoriaSeleccionada !== 'tradicional')
                                    <th class="text-end">Pagado no tradicional</th>
                                @endif
                                <th class="text-end">Total</th>
                            </tr></thead>
                            <tbody>
                                @forelse ($reporte['filas'] as $fila)
                                    <tr>
                                        <td>{{ $fila['nombre'] }}</td>
                                        @if ($categoriaSeleccionada !== 'no_tradicional')
                                            <td class="text-end">{{ $fila['tradicional'] === null ? '—' : 'RD$ '.number_format($fila['tradicional'], 2) }}</td>
                                        @endif
                                        @if ($categoriaSeleccionada !== 'tradicional')
                                            <td class="text-end">{{ $fila['no_tradicional'] === null ? '—' : 'RD$ '.number_format($fila['no_tradicional'], 2) }}</td>
                                        @endif
                                        <td class="text-end">{{ $fila['total'] === null ? '—' : 'RD$ '.number_format($fila['total'], 2) }}</td>
                                    </tr>
                                @empty
                                    <tr><td colspan="{{ $categoriaSeleccionada === 'todos' ? 4 : 3 }}" class="text-center text-muted">No hay pagos para los filtros seleccionados.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('script')
    <script>
        $(function () {
            @if ($reporte['filas'] !== [])
                $('#tablePremiosPagados').DataTable({
                    dom: 'Bfrtip',
                    buttons: ['copy', 'csv', 'excel', 'pdf', 'print'],
                    pageLength: 25,
                    order: [[0, 'asc']],
                    language: { url: '//cdn.datatables.net/plug-ins/1.13.7/i18n/es-ES.json' }
                });
            @endif
        });

        const premiosForm = document.getElementById('formPremiosPagados');
        const consultarButton = premiosForm.querySelector('button[type="submit"]');

        premiosForm.addEventListener('submit', function (event) {
            if (consultarButton.disabled) {
                event.preventDefault();
                return;
            }

            if (premiosForm.elements.fecha_inicio.value > premiosForm.elements.fecha_fin.value) {
                event.preventDefault();
                Swal.fire({
                    icon: 'error',
                    title: 'Revise las fechas',
                    text: 'La fecha final debe ser igual o posterior a la fecha de inicio.'
                });
                return;
            }

            consultarButton.disabled = true;
            Swal.fire({
                title: 'Consultando...',
                text: 'Estamos preparando el reporte de premios pagados.',
                allowOutsideClick: false,
                allowEscapeKey: false,
                showConfirmButton: false,
                didOpen: () => Swal.showLoading()
            });
        });

        window.addEventListener('pageshow', function (event) {
            if (event.persisted) {
                consultarButton.disabled = false;
                Swal.close();
            }
        });
    </script>
@endsection
