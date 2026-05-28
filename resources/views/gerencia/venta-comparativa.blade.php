@extends('app')

@section('content')
    <div class="main-content">
        <div class="page-content">
            <div class="container-fluid">
                <style>
                    .kpi-shell {
                        border: 1px solid rgba(148, 163, 184, 0.25);
                        border-radius: 12px;
                        overflow: hidden;
                        background: linear-gradient(135deg, #f8fafc 0%, #eef2ff 100%);
                        box-shadow: 0 8px 24px rgba(15, 23, 42, 0.08);
                    }

                    .kpi-card {
                        position: relative;
                        border: 0;
                        border-right: 1px dashed rgba(148, 163, 184, 0.35);
                        background: transparent;
                        padding: 1rem 1rem 0.85rem;
                        transform: translateY(6px);
                        opacity: 0;
                        animation: kpiFadeIn 0.55s ease forwards;
                    }

                    .kpi-col:last-child .kpi-card {
                        border-right: 0;
                    }

                    .kpi-card:hover {
                        background: rgba(255, 255, 255, 0.6);
                    }

                    .kpi-title {
                        font-size: 0.82rem;
                        font-weight: 700;
                        text-transform: uppercase;
                        letter-spacing: 0.04em;
                        margin-bottom: 0.6rem;
                    }

                    .kpi-metric {
                        line-height: 1.1;
                        margin-bottom: 0.45rem;
                    }

                    .kpi-label {
                        color: #64748b;
                        font-size: 0.73rem;
                        text-transform: uppercase;
                        letter-spacing: 0.03em;
                    }

                    .kpi-value {
                        font-size: 1.02rem;
                        font-weight: 700;
                        color: #0f172a;
                    }

                    .kpi-col:nth-child(1) .kpi-card { animation-delay: 0.02s; }
                    .kpi-col:nth-child(2) .kpi-card { animation-delay: 0.1s; }
                    .kpi-col:nth-child(3) .kpi-card { animation-delay: 0.18s; }
                    .kpi-col:nth-child(4) .kpi-card { animation-delay: 0.26s; }

                    @keyframes kpiFadeIn {
                        to {
                            transform: translateY(0);
                            opacity: 1;
                        }
                    }

                    @media (max-width: 991px) {
                        .kpi-card {
                            border-right: 0;
                            border-bottom: 1px dashed rgba(148, 163, 184, 0.35);
                        }

                        .kpi-col:last-child .kpi-card {
                            border-bottom: 0;
                        }
                    }
                </style>
                <div class="row">
                    <div class="col-12">
                        <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                            <h4 class="mb-sm-0">Venta Comparativa</h4>
                            <div class="page-title-right">
                                <ol class="breadcrumb m-0">
                                    <li class="breadcrumb-item"><a href="{{ route('inicio.index') }}">Inicio</a></li>
                                    <li class="breadcrumb-item"><a href="{{ route('gerencia.index') }}">Gerencia</a></li>
                                    <li class="breadcrumb-item active">Venta Comparativa</li>
                                </ol>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-body">
                                <form method="GET" action="{{ route('gerencia.venta-comparativa') }}" class="row g-2 align-items-end" id="form-filtro-comparativa">
                                    <div class="col-12 col-md-4 col-xl-2">
                                        <label class="form-label">Fecha</label>
                                        <input type="date" name="fecha" class="form-control form-control-sm" value="{{ $fechaSeleccionada ?? now()->format('Y-m-d') }}">
                                    </div>
                                    <div class="col-12 col-md-4 col-xl-2">
                                        <label class="form-label">Sistema</label>
                                        <select name="sistema" class="form-select form-select-sm">
                                            <option value="todos" {{ ($sistemaSeleccionado ?? 'todos') === 'todos' ? 'selected' : '' }}>Todos</option>
                                            <option value="lotobet" {{ ($sistemaSeleccionado ?? 'todos') === 'lotobet' ? 'selected' : '' }}>Lotobet Real</option>
                                            <option value="lotonet" {{ ($sistemaSeleccionado ?? 'todos') === 'lotonet' ? 'selected' : '' }}>Lotonet</option>
                                        </select>
                                    </div>
                                    <div class="col-12 col-md-4 col-xl-2">
                                        <label class="form-label">Terminal</label>
                                        <select name="agencia" class="form-select form-select-sm">
                                            <option value="">Todas</option>
                                            @foreach(($agenciasDisponibles ?? []) as $agenciaItem)
                                                <option value="{{ $agenciaItem['agencia'] }}" {{ (string) ($agenciaSeleccionada ?? '') === (string) ($agenciaItem['agencia'] ?? '') ? 'selected' : '' }}>
                                                    {{ $agenciaItem['agencia'] }} - {{ $agenciaItem['nombre'] }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-12 col-md-4 col-xl-2">
                                        <label class="form-label">Buscar terminal</label>
                                        <input
                                            type="text"
                                            name="terminal"
                                            id="buscar-agencia-comparativa"
                                            class="form-control form-control-sm"
                                            value="{{ $terminalBuscada ?? '' }}"
                                            placeholder="Escribe una terminal">
                                    </div>
                                    <div class="col-12 col-md-4 col-xl-1">
                                        <label class="form-label">Tendencia</label>
                                        <select name="tendencia_rango" class="form-select form-select-sm">
                                            <option value="semanal" {{ ($tendenciaRango ?? 'semanal') === 'semanal' ? 'selected' : '' }}>Semanal</option>
                                            <option value="quincenal" {{ ($tendenciaRango ?? 'semanal') === 'quincenal' ? 'selected' : '' }}>Quincenal</option>
                                            <option value="mensual" {{ ($tendenciaRango ?? 'semanal') === 'mensual' ? 'selected' : '' }}>Un mes</option>
                                        </select>
                                    </div>
                                    <div class="col-12 col-md-8 col-xl-3">
                                        <label class="form-label d-none d-lg-block">Acciones</label>
                                        <div class="d-flex flex-wrap flex-lg-nowrap gap-2">
                                            <button type="submit" class="btn btn-primary" id="btn-filtrar-comparativa">
                                                <i class="ri-search-line me-1"></i>Buscar
                                            </button>
                                            <a href="{{ route('gerencia.venta-comparativa') }}" class="btn btn-light">Limpiar</a>
                                            <a href="{{ route('gerencia.venta-comparativa.export.excel', ['fecha' => ($fechaSeleccionada ?? now()->format('Y-m-d')), 'sistema' => ($sistemaSeleccionado ?? 'todos'), 'agencia' => ($agenciaSeleccionada ?? ''), 'terminal' => ($terminalBuscada ?? '')]) }}" class="btn btn-success">
                                                <i class="ri-file-excel-2-line me-1"></i>Excel
                                            </a>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-12">
                        @php
                            $fechaBase = \Carbon\Carbon::parse($fechaSeleccionada ?? now()->format('Y-m-d'));
                            $fechaHoyLabel = $fechaBase->format('d-m-Y');
                            $fechaAyerLabel = $fechaBase->copy()->subDay()->format('d-m-Y');
                            $fechaDosDiasLabel = $fechaBase->copy()->subDays(2)->format('d-m-Y');
                            $fechaTresDiasLabel = $fechaBase->copy()->subDays(3)->format('d-m-Y');

                            $totalHoy = collect($resumenComparativo ?? [])->sum('ventas_hoy');
                            $totalAyer = collect($resumenComparativo ?? [])->sum('ventas_ayer');
                            $total2Dias = collect($resumenComparativo ?? [])->sum('ventas_hace_2_dias');
                            $total3Dias = collect($resumenComparativo ?? [])->sum('ventas_hace_3_dias');

                            $agenciasHoy = collect($resumenComparativo ?? [])->filter(fn ($item) => (float) ($item['ventas_hoy'] ?? 0) > 0)->count();
                            $agenciasAyer = collect($resumenComparativo ?? [])->filter(fn ($item) => (float) ($item['ventas_ayer'] ?? 0) > 0)->count();
                            $agencias2Dias = collect($resumenComparativo ?? [])->filter(fn ($item) => (float) ($item['ventas_hace_2_dias'] ?? 0) > 0)->count();
                            $agencias3Dias = collect($resumenComparativo ?? [])->filter(fn ($item) => (float) ($item['ventas_hace_3_dias'] ?? 0) > 0)->count();

                            $promedioHoy = $agenciasHoy > 0 ? $totalHoy / $agenciasHoy : 0;
                            $promedioAyer = $agenciasAyer > 0 ? $totalAyer / $agenciasAyer : 0;
                            $promedio2Dias = $agencias2Dias > 0 ? $total2Dias / $agencias2Dias : 0;
                            $promedio3Dias = $agencias3Dias > 0 ? $total3Dias / $agencias3Dias : 0;
                        @endphp

                        <div class="kpi-shell mb-3">
                            <div class="row g-0">
                                <div class="col-xl-3 col-md-6 kpi-col">
                                    <div class="kpi-card">
                                        <div class="kpi-title text-primary">Ventas {{ $fechaHoyLabel }}</div>
                                        <div class="kpi-metric">
                                            <div class="kpi-label">Cantidad de agencias</div>
                                            <div class="kpi-value">{{ number_format($agenciasHoy, 0) }}</div>
                                        </div>
                                        <div class="kpi-metric">
                                            <div class="kpi-label">Ventas</div>
                                            <div class="kpi-value">RD$ {{ number_format((float) $totalHoy, 2) }}</div>
                                        </div>
                                        <div class="kpi-metric mb-0">
                                            <div class="kpi-label">Promedio</div>
                                            <div class="kpi-value">RD$ {{ number_format((float) $promedioHoy, 2) }}</div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-xl-3 col-md-6 kpi-col">
                                    <div class="kpi-card">
                                        <div class="kpi-title text-info">Ventas {{ $fechaAyerLabel }}</div>
                                        <div class="kpi-metric">
                                            <div class="kpi-label">Cantidad de agencias</div>
                                            <div class="kpi-value">{{ number_format($agenciasAyer, 0) }}</div>
                                        </div>
                                        <div class="kpi-metric">
                                            <div class="kpi-label">Ventas</div>
                                            <div class="kpi-value">RD$ {{ number_format((float) $totalAyer, 2) }}</div>
                                        </div>
                                        <div class="kpi-metric mb-0">
                                            <div class="kpi-label">Promedio</div>
                                            <div class="kpi-value">RD$ {{ number_format((float) $promedioAyer, 2) }}</div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-xl-3 col-md-6 kpi-col">
                                    <div class="kpi-card">
                                        <div class="kpi-title text-success">Ventas {{ $fechaDosDiasLabel }}</div>
                                        <div class="kpi-metric">
                                            <div class="kpi-label">Cantidad de agencias</div>
                                            <div class="kpi-value">{{ number_format($agencias2Dias, 0) }}</div>
                                        </div>
                                        <div class="kpi-metric">
                                            <div class="kpi-label">Ventas</div>
                                            <div class="kpi-value">RD$ {{ number_format((float) $total2Dias, 2) }}</div>
                                        </div>
                                        <div class="kpi-metric mb-0">
                                            <div class="kpi-label">Promedio</div>
                                            <div class="kpi-value">RD$ {{ number_format((float) $promedio2Dias, 2) }}</div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-xl-3 col-md-6 kpi-col">
                                    <div class="kpi-card">
                                        <div class="kpi-title text-warning">Ventas {{ $fechaTresDiasLabel }}</div>
                                        <div class="kpi-metric">
                                            <div class="kpi-label">Cantidad de agencias</div>
                                            <div class="kpi-value">{{ number_format($agencias3Dias, 0) }}</div>
                                        </div>
                                        <div class="kpi-metric">
                                            <div class="kpi-label">Ventas</div>
                                            <div class="kpi-value">RD$ {{ number_format((float) $total3Dias, 2) }}</div>
                                        </div>
                                        <div class="kpi-metric mb-0">
                                            <div class="kpi-label">Promedio</div>
                                            <div class="kpi-value">RD$ {{ number_format((float) $promedio3Dias, 2) }}</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="card mb-3">
                            <div class="card-header align-items-center d-flex">
                                <h5 class="card-title mb-0 flex-grow-1">Tendencia de Ventas ({{ $tendenciaRangoLabel ?? 'Semanal' }})</h5>
                                <span class="badge bg-primary-subtle text-primary">
                                    {{ ($agenciaSeleccionada ?? '') !== '' ? 'Terminal: ' . $agenciaSeleccionada : 'Todas las terminales' }}
                                </span>
                            </div>
                            <div class="card-body py-2">
                                <div id="chart-tendencia-comparativa" style="height: 210px;"></div>
                            </div>
                        </div>

                        <div class="card">
                            <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
                                <h5 class="card-title mb-0">Reporte Venta Comparativa ({{ strtoupper($sistemaSeleccionado ?? 'todos') }})</h5>
                                <div class="d-flex align-items-center flex-wrap gap-2">
                                    <select class="form-select form-select-sm" id="select-dia-cero" style="min-width: 190px;">
                                        <option value="ventasHoy">Validar cero en Hoy</option>
                                        <option value="ventasAyer">Validar cero en Ayer</option>
                                        <option value="ventasHace2Dias">Validar cero en Hace 2 Dias</option>
                                        <option value="ventasHace3Dias">Validar cero en Hace 3 Dias</option>
                                    </select>
                                    <button type="button" class="btn btn-soft-warning btn-sm" id="btn-filtro-ceros">Solo ceros</button>
                                    <button type="button" class="btn btn-soft-info btn-sm" id="btn-detalle-ceros">Detalle ceros</button>
                                    <span class="badge bg-warning-subtle text-warning" id="badge-conteo-ceros">Agencias en cero: 0</span>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-bordered table-striped align-middle mb-0" id="table-venta-comparativa-agencias">
                                        <thead>
                                            <tr>
                                                <th>Agencia</th>
                                                <th>Ventas Hoy</th>
                                                <th>Ventas de Ayer</th>
                                                <th>Ventas Hace 2 Dias</th>
                                                <th>Ventas Hace 3 Dias</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse (($resumenComparativo ?? []) as $item)
                                                <tr
                                                    data-agencia="{{ $item['agencia'] ?? '' }}"
                                                    data-terminal="{{ $item['terminal'] ?? ($item['agencia'] ?? '') }}"
                                                    data-nombre-agencia="{{ $item['nombre_agencia'] ?? '' }}"
                                                    data-ventas-hoy="{{ (float) ($item['ventas_hoy'] ?? 0) }}"
                                                    data-ventas-ayer="{{ (float) ($item['ventas_ayer'] ?? 0) }}"
                                                    data-ventas-hace-2-dias="{{ (float) ($item['ventas_hace_2_dias'] ?? 0) }}"
                                                    data-ventas-hace-3-dias="{{ (float) ($item['ventas_hace_3_dias'] ?? 0) }}">
                                                    <td>
                                                        <div class="fw-medium">{{ $item['nombre_agencia'] ?? ($item['agencia'] ?? 'SIN AGENCIA') }}</div>
                                                        <small class="text-muted">Terminal: {{ $item['terminal'] ?? ($item['agencia'] ?? '-') }}</small>
                                                    </td>
                                                    <td>RD$ {{ number_format((float) ($item['ventas_hoy'] ?? 0), 2) }}</td>
                                                    <td>RD$ {{ number_format((float) ($item['ventas_ayer'] ?? 0), 2) }}</td>
                                                    <td>RD$ {{ number_format((float) ($item['ventas_hace_2_dias'] ?? 0), 2) }}</td>
                                                    <td>RD$ {{ number_format((float) ($item['ventas_hace_3_dias'] ?? 0), 2) }}</td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="5" class="text-center text-muted">No hay datos para el filtro seleccionado.</td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                        <tfoot>
                                            <tr class="fw-semibold table-light">
                                                <td>Total</td>
                                                <td>RD$ {{ number_format((float) $totalHoy, 2) }}</td>
                                                <td>RD$ {{ number_format((float) $totalAyer, 2) }}</td>
                                                <td>RD$ {{ number_format((float) $total2Dias, 2) }}</td>
                                                <td>RD$ {{ number_format((float) $total3Dias, 2) }}</td>
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
<script src="{{ asset('libs/apexcharts/apexcharts.min.js') }}"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const formFiltroComparativa = document.getElementById('form-filtro-comparativa');
        const btnFiltrarComparativa = document.getElementById('btn-filtrar-comparativa');

        if (formFiltroComparativa && btnFiltrarComparativa) {
            formFiltroComparativa.addEventListener('submit', function () {
                btnFiltrarComparativa.disabled = true;

                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        title: 'Generando datos...',
                        text: 'Estamos procesando la consulta, por favor espera.',
                        allowOutsideClick: false,
                        allowEscapeKey: false,
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });
                }
            });
        }

        const tendenciaLabels = @json($tendenciaSemanal['labels'] ?? []);
        const tendenciaSeries = @json($tendenciaSemanal['series'] ?? []);

        if (typeof ApexCharts !== 'undefined' && document.querySelector('#chart-tendencia-comparativa')) {
            const chartOptions = {
                series: [{
                    name: 'Ventas',
                    data: tendenciaSeries
                }],
                chart: {
                    type: 'area',
                    height: 210,
                    toolbar: { show: false },
                    zoom: { enabled: false }
                },
                stroke: {
                    curve: 'smooth',
                    width: 3
                },
                fill: {
                    type: 'gradient',
                    gradient: {
                        shadeIntensity: 1,
                        inverseColors: false,
                        opacityFrom: 0.35,
                        opacityTo: 0.05,
                        stops: [0, 90, 100]
                    }
                },
                dataLabels: { enabled: false },
                xaxis: {
                    categories: tendenciaLabels,
                    labels: { style: { colors: '#64748b' } }
                },
                yaxis: {
                    labels: {
                        formatter: function (value) {
                            return 'RD$ ' + Number(value || 0).toLocaleString('en-US', {
                                minimumFractionDigits: 0,
                                maximumFractionDigits: 0
                            });
                        }
                    }
                },
                colors: ['#0ab39c'],
                grid: {
                    borderColor: '#e2e8f0',
                    strokeDashArray: 4
                },
                tooltip: {
                    y: {
                        formatter: function (value) {
                            return 'RD$ ' + Number(value || 0).toLocaleString('en-US', {
                                minimumFractionDigits: 2,
                                maximumFractionDigits: 2
                            });
                        }
                    }
                },
                legend: { show: false }
            };

            const tendenciaChart = new ApexCharts(document.querySelector('#chart-tendencia-comparativa'), chartOptions);
            tendenciaChart.render();
        }

        if (!(window.$ && $.fn.DataTable)) {
            return;
        }

        const tableElement = $('#table-venta-comparativa-agencias');
        if (!tableElement.length) {
            return;
        }

        const selectDiaCero = document.getElementById('select-dia-cero');
        const btnFiltroCeros = document.getElementById('btn-filtro-ceros');
        const btnDetalleCeros = document.getElementById('btn-detalle-ceros');
        const badgeConteoCeros = document.getElementById('badge-conteo-ceros');
        let filtroCerosActivo = false;

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

        function indiceMetrica(metrica) {
            if (metrica === 'ventasAyer') return 2;
            if (metrica === 'ventasHace2Dias') return 3;
            if (metrica === 'ventasHace3Dias') return 4;
            return 1;
        }

        function parseMonto(texto) {
            const normalizado = String(texto || '').replace(/[^0-9.-]/g, '');
            const valor = Number(normalizado);
            return Number.isFinite(valor) ? valor : 0;
        }

        function filasConDatosAplicados() {
            return dataTable.rows({ search: 'applied' }).nodes().toArray().filter(function (fila) {
                const celdas = fila.querySelectorAll('td');
                return celdas && celdas.length >= 5;
            });
        }

        function valorFilaDesdeCeldas(fila, metrica) {
            const indice = indiceMetrica(metrica);
            const celdas = fila.querySelectorAll('td');
            return parseMonto(celdas[indice]?.textContent || '0');
        }

        function actualizarConteoCeros() {
            if (!badgeConteoCeros) return;
            const metrica = selectDiaCero?.value || 'ventasHoy';
            const conteo = filasConDatosAplicados().filter(function (fila) {
                return valorFilaDesdeCeldas(fila, metrica) <= 0;
            }).length;
            badgeConteoCeros.textContent = 'Agencias en cero: ' + conteo.toLocaleString('es-DO');
        }

        $.fn.dataTable.ext.search.push(function (settings, _data, dataIndex) {
            if (settings.nTable !== tableElement[0]) return true;
            if (!filtroCerosActivo) return true;

            const metrica = selectDiaCero?.value || 'ventasHoy';
            const indice = indiceMetrica(metrica);
            const valor = parseMonto(_data?.[indice] || '0');
            if (!Number.isFinite(valor)) {
                return false;
            }

            return valor <= 0;
        });

        const buscarInput = document.getElementById('buscar-agencia-comparativa');
        if (buscarInput) {
            buscarInput.addEventListener('input', function () {
                dataTable.search(this.value || '').draw();
            });
        }

        if (selectDiaCero) {
            selectDiaCero.addEventListener('change', function () {
                dataTable.draw();
                actualizarConteoCeros();
            });
        }

        if (btnFiltroCeros) {
            btnFiltroCeros.addEventListener('click', function () {
                filtroCerosActivo = !filtroCerosActivo;
                this.classList.remove('btn-soft-warning', 'btn-warning');
                this.classList.add(filtroCerosActivo ? 'btn-warning' : 'btn-soft-warning');
                this.textContent = filtroCerosActivo ? 'Mostrando ceros' : 'Solo ceros';
                dataTable.draw();
                actualizarConteoCeros();
            });
        }

        if (btnDetalleCeros) {
            btnDetalleCeros.addEventListener('click', function () {
                const metrica = selectDiaCero?.value || 'ventasHoy';
                const ceros = filasConDatosAplicados().filter(function (fila) {
                    return valorFilaDesdeCeldas(fila, metrica) <= 0;
                });

                const tituloMetrica = {
                    ventasHoy: 'Hoy',
                    ventasAyer: 'Ayer',
                    ventasHace2Dias: 'Hace 2 dias',
                    ventasHace3Dias: 'Hace 3 dias'
                }[metrica] || 'Hoy';

                if (typeof Swal !== 'undefined') {
                    if (!ceros.length) {
                        Swal.fire({
                            icon: 'info',
                            title: 'Sin agencias en cero',
                            text: 'No hay agencias en cero para ' + tituloMetrica + '.',
                        });
                        return;
                    }

                    const html = ceros.map(function (fila) {
                        const terminal = fila.dataset.agencia || '-';
                        const nombre = fila.dataset.nombreAgencia || terminal;
                        return '<div style="text-align:left;border-bottom:1px solid #e2e8f0;padding:6px 0;">' +
                            '<strong>' + terminal + '</strong> - ' + nombre +
                            '</div>';
                    }).join('');

                    Swal.fire({
                        title: 'Agencias en cero (' + tituloMetrica + ')',
                        html: '<div style="max-height:300px;overflow:auto;">' + html + '</div>',
                        width: 640,
                        confirmButtonText: 'Cerrar'
                    });
                }
            });
        }

        dataTable.on('draw', actualizarConteoCeros);
        actualizarConteoCeros();
    });
</script>
@endsection

