@extends('app')

@section('content')
    <div class="main-content">
        <div class="page-content">
            <div class="container-fluid">
                <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                    <h4 class="mb-sm-0">Premios pagados por productos</h4>
                    <a href="{{ route('reportes.index') }}" class="btn btn-soft-primary btn-sm">Volver a reportes</a>
                </div>
                <div class="card">
                    <div class="card-body">
                        <p class="text-muted">Terminales Real registradas en agencias. Pagos de la misma empresa y pagos a otra empresa.</p>
                        <form id="formPremiosPagados" method="GET" action="{{ route('reportes.premios-pagados-productos') }}" class="row g-3 align-items-end">
                            <div class="col-md-4">
                                <label for="fecha_inicio" class="form-label">Fecha de inicio</label>
                                <input type="date" id="fecha_inicio" name="fecha_inicio" class="form-control" value="{{ old('fecha_inicio', $fechaInicio) }}" required>
                            </div>
                            <div class="col-md-4">
                                <label for="fecha_fin" class="form-label">Fecha final</label>
                                <input type="date" id="fecha_fin" name="fecha_fin" class="form-control" value="{{ old('fecha_fin', $fechaFin) }}" required>
                            </div>
                            <div class="col-md-4"><button class="btn btn-primary" type="submit"><i class="ri-search-line"></i> Consultar</button></div>
                        </form>
                        @if ($errors->any())
                            <div class="alert alert-danger mt-3" role="alert">{{ $errors->first() }}</div>
                        @endif
                    </div>
                </div>
                @if ($reporte['disponibilidad'] !== [])
                    <div class="alert alert-info" role="status">
                        <strong>No se encontraron pagos para las terminales Real en el período seleccionado.</strong>
                        <ul class="mb-0 mt-2">
                            @foreach ($reporte['disponibilidad'] as $fuente)
                                <li>
                                    <strong>{{ $fuente['nombre'] }}:</strong>
                                    @if ($fuente['fecha_inicio'] === null)
                                        No hay pagos guardados en esta fuente.
                                    @else
                                        Rango de fechas disponible: {{ $fuente['fecha_inicio'] }} al {{ $fuente['fecha_fin'] }}.
                                        @if ($fuente['registros_periodo'] === 0)
                                            No hay pagos guardados para las fechas consultadas.
                                        @else
                                            Hay {{ $fuente['registros_periodo'] }} registros en esas fechas, pero sus terminales no coinciden con las registradas en agencias Real.
                                        @endif
                                    @endif
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @elseif ($reporte['fuentes_sin_producto'] !== [] || $reporte['grupos'][2]['productos'] !== [])
                    <div class="alert alert-warning" role="status">
                        Hay pagos cuya información no permite identificar el producto. Sus montos se muestran en «Sin producto identificado».
                        Un guion (—) indica que el desglose por producto no está disponible; no equivale a cero.
                    </div>
                @endif
                <div class="row">
                    @foreach (['misma_empresa' => 'Pagos misma empresa', 'otra_empresa' => 'Pagos a otra empresa', 'total' => 'Total pagado'] as $campo => $etiqueta)
                        <div class="col-md-4">
                            <div class="card"><div class="card-body">
                                <p class="text-muted mb-2">{{ $etiqueta }}</p>
                                <h4 class="mb-0">RD$ {{ number_format($reporte['resumen'][$campo], 2) }}</h4>
                            </div></div>
                        </div>
                    @endforeach
                </div>
                <p class="text-muted">Despliegue una categoría, luego un producto para ver sus terminales y los montos pagados del {{ $fechaInicio }} al {{ $fechaFin }}.</p>
                @foreach ($reporte['grupos'] as $grupo)
                    <details class="card" @if ($grupo['nombre'] === 'Sin producto identificado' && $grupo['productos'] !== []) open @endif>
                        <summary class="card-header py-3" style="cursor: pointer;">
                            <strong>{{ $grupo['nombre'] }}</strong>
                            <span class="text-muted ms-2">{{ count($grupo['productos']) }} productos · Total: {{ $grupo['total'] === null ? '—' : 'RD$ '.number_format($grupo['total'], 2) }}</span>
                        </summary>
                        <div class="card-body">
                            @forelse ($grupo['productos'] as $producto)
                                <details class="border rounded mb-3 producto-detalle">
                                    <summary class="p-3" style="cursor: pointer;">
                                        <strong>{{ $producto['nombre'] }}</strong>
                                        <span class="text-muted ms-2">{{ count($producto['terminales']) }} terminales</span>
                                        <span class="row g-2 mt-1">
                                            @foreach (['misma_empresa' => 'Misma empresa', 'otra_empresa' => 'A otra empresa', 'total' => 'Total pagado'] as $campo => $etiqueta)
                                                <span class="col-md-4"><span class="text-muted">{{ $etiqueta }}:</span> {{ $producto[$campo] === null ? '—' : 'RD$ '.number_format($producto[$campo], 2) }}</span>
                                            @endforeach
                                        </span>
                                    </summary>
                                    <div class="table-responsive border-top">
                                        <table class="table table-striped align-middle mb-0">
                                            <thead><tr><th scope="col">Terminal</th><th scope="col" class="text-end">Pagos misma empresa</th><th scope="col" class="text-end">Pagos a otra empresa</th><th scope="col" class="text-end">Total pagado</th></tr></thead>
                                            <tbody>
                                                @forelse ($producto['terminales'] as $terminal)
                                                    <tr>
                                                        <td>{{ $terminal['terminal'] }}</td>
                                                        @foreach (['misma_empresa', 'otra_empresa', 'total'] as $campo)
                                                            <td class="text-end text-nowrap">{{ $terminal[$campo] === null ? '—' : 'RD$ '.number_format($terminal[$campo], 2) }}</td>
                                                        @endforeach
                                                    </tr>
                                                @empty
                                                    <tr><td colspan="4" class="text-center text-muted">{{ $reporte['fuentes_sin_producto'] !== [] ? 'El detalle de terminales por producto no está disponible.' : 'No hay pagos de terminales para este producto en el período seleccionado.' }}</td></tr>
                                                @endforelse
                                            </tbody>
                                            <tfoot><tr><th scope="row">Total del producto</th>
                                                @foreach (['misma_empresa', 'otra_empresa', 'total'] as $campo)
                                                    <th class="text-end text-nowrap">{{ $producto[$campo] === null ? '—' : 'RD$ '.number_format($producto[$campo], 2) }}</th>
                                                @endforeach
                                            </tr></tfoot>
                                        </table>
                                    </div>
                                </details>
                            @empty
                                <p class="text-center text-muted mb-0">No hay productos ni pagos en esta categoría.</p>
                            @endforelse
                        </div>
                    </details>
                @endforeach
            </div>
        </div>
    </div>
@endsection

@section('script')
    <script>
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
