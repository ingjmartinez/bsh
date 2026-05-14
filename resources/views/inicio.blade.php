@extends('app')

@section('content')
    <link href="{{ asset('libs/swiper/swiper-bundle.min.css') }}" rel="stylesheet" />
    <style>
        .kpi-card {
            min-height: 142px;
        }

        .kpi-card-action {
            cursor: pointer;
            transition: transform 0.22s ease, box-shadow 0.22s ease, background-color 0.22s ease;
        }

        .kpi-card-action:hover {
            transform: translateY(-4px);
            box-shadow: inset 0 0 0 1px rgba(13, 110, 253, 0.08), 0 12px 24px rgba(15, 23, 42, 0.08);
            background: rgba(13, 110, 253, 0.02);
        }

        .kpi-card-action:focus-visible {
            outline: 2px solid rgba(13, 110, 253, 0.5);
            outline-offset: -2px;
        }

        .sorteo-card {
            position: relative;
            overflow: hidden;
            border: 1px solid rgba(148, 163, 184, 0.18);
            box-shadow: 0 10px 30px rgba(15, 23, 42, 0.06);
            transition: transform 0.28s ease, box-shadow 0.28s ease, border-color 0.28s ease;
        }

        .sorteo-card::after {
            content: "";
            position: absolute;
            inset: 0;
            background: linear-gradient(135deg, rgba(13, 110, 253, 0.06), rgba(32, 201, 151, 0.02));
            opacity: 0;
            transition: opacity 0.28s ease;
            pointer-events: none;
        }

        .sorteo-card:hover {
            transform: translateY(-8px);
            border-color: rgba(13, 110, 253, 0.28);
            box-shadow: 0 22px 48px rgba(15, 23, 42, 0.16);
        }

        .sorteo-card:hover::after {
            opacity: 1;
        }

        .sorteo-card .card-body {
            position: relative;
            z-index: 1;
        }

        .kpi-icon {
            font-size: 2.35rem;
            line-height: 1;
        }

        .kpi-value {
            font-size: clamp(1.85rem, 2vw, 2.6rem);
            line-height: 1.05;
            white-space: nowrap;
        }

        .kpi-currency {
            font-size: clamp(1.35rem, 1.55vw, 1.95rem);
            line-height: 1.05;
            white-space: nowrap;
        }

        .rank-badge {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 46px;
            height: 28px;
            border-radius: 999px;
            font-size: 0.8rem;
            font-weight: 700;
            background: rgba(148, 163, 184, 0.22);
            color: #e2e8f0;
            border: 1px solid rgba(148, 163, 184, 0.45);
            box-shadow: inset 0 0 0 1px rgba(255, 255, 255, 0.03);
        }

        .rank-badge.rank-1 {
            background: var(--vz-primary);
            color: #ffffff;
            border-color: var(--vz-primary);
        }

        .rank-badge.rank-2 {
            background: var(--vz-success);
            color: #ffffff;
            border-color: var(--vz-success);
        }

        .rank-badge.rank-3 {
            background: var(--vz-warning);
            color: #111827;
            border-color: var(--vz-warning);
        }

        html[data-layout-mode="dark"] .rank-badge,
        html[data-bs-theme="dark"] .rank-badge {
            color: #ffffff;
        }

        html[data-layout-mode="light"] .rank-badge,
        html[data-bs-theme="light"] .rank-badge {
            color: #111827;
        }

        @media (max-width: 1400px) {
            .kpi-value {
                font-size: clamp(1.55rem, 2vw, 2.1rem);
            }

            .kpi-currency {
                font-size: clamp(1.05rem, 1.35vw, 1.45rem);
            }
        }
    </style>
    <div class="main-content">
        <div class="page-content">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-12">
                        <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                            <h4 class="mb-sm-0">TABLERO DE INDICADORES CLAVES</h4>
                            <div class="page-title-right">
                                <ol class="breadcrumb m-0">
                                    <li class="breadcrumb-item"><a href="{{ route('inicio.index') }}">Inicio</a></li>
                                    <li class="breadcrumb-item"><a href="{{ route('dashboard.index') }}">Dashboard</a></li>
                                    <li class="breadcrumb-item active">Tablero</li>
                                </ol>
                            </div>
                        </div>
                    </div>
                </div>

                @php
                    $ventasTipos = $ventasInicio['tipos'] ?? [];
                    $ventaTradicional = (float) ($ventasTipos['tradicional']['total'] ?? 0);
                    $ventaNoTradicional = (float) ($ventasTipos['no_tradicional']['total'] ?? 0);
                    $ventaRecargas = (float) ($ventasTipos['recargas']['total'] ?? 0);
                    $agenciasTradicional = (int) ($ventasTipos['tradicional']['agencias'] ?? 0);
                    $agenciasNoTradicional = (int) ($ventasTipos['no_tradicional']['agencias'] ?? 0);
                    $agenciasRecargas = (int) ($ventasTipos['recargas']['agencias'] ?? 0);
                    $agenciasConVenta = (int) ($ventasInicio['agencias_con_venta'] ?? 0);
                    $agenciasSinVenta = (int) ($ventasInicio['agencias_sin_venta'] ?? 0);
                    $agenciasSinVentaListado = $ventasInicio['agencias_sin_ventas'] ?? [];
                    $productosNoTradicionales = $ventasInicio['productos_no_tradicionales'] ?? [];
                    $productosTradicionalesTop = $ventasInicio['productos_tradicionales_top'] ?? [];
                    $balanceMensual = $ventasInicio['balance_mensual'] ?? ['dias' => [], 'ingresos' => [], 'gastos' => [], 'margen' => [], 'periodo' => ['inicio' => null, 'fin' => null]];
                    $balanceIngresosTotal = array_sum($balanceMensual['ingresos'] ?? []);
                    $balanceGastosTotal = array_sum($balanceMensual['gastos'] ?? []);
                    $balanceMargenTotal = array_sum($balanceMensual['margen'] ?? []);
                    $balancePeriodoInicio = !empty($balanceMensual['periodo']['inicio'])
                        ? \Carbon\Carbon::parse($balanceMensual['periodo']['inicio'])->format('d/m/Y')
                        : '-';
                    $balancePeriodoFin = !empty($balanceMensual['periodo']['fin'])
                        ? \Carbon\Carbon::parse($balanceMensual['periodo']['fin'])->format('d/m/Y')
                        : '-';
                    $crmDashboardData = [
                        'ventasProductos' => [
                            'series' => [round($ventaTradicional, 2), round($ventaNoTradicional, 2), round($ventaRecargas, 2)],
                            'agencias' => [$agenciasTradicional, $agenciasNoTradicional, $agenciasRecargas],
                        ],
                        'agenciasSinVenta' => $agenciasSinVentaListado,
                        'resumenBalance' => [
                            'categories' => $balanceMensual['dias'] ?? [],
                            'ingresos' => $balanceMensual['ingresos'] ?? [],
                            'gastos' => $balanceMensual['gastos'] ?? [],
                            'margen' => $balanceMensual['margen'] ?? [],
                        ],
                    ];
                @endphp

                <div class="row mb-3">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-body">
                                <form id="inicioFiltroForm" method="GET" action="{{ route('inicio.index') }}" class="row g-3 align-items-end">
                                    <input type="hidden" name="cargar" value="1">
                                    <div class="col-12 col-md-4 col-lg-3">
                                        <label for="fecha" class="form-label">Fecha de ventas</label>
                                        <input
                                            type="date"
                                            id="fecha"
                                            name="fecha"
                                            class="form-control"
                                            value="{{ $fechaSeleccionadaVentas ?? now()->subDay()->format('Y-m-d') }}"
                                            max="{{ now()->format('Y-m-d') }}">
                                    </div>
                                    <div class="col-12 col-md-4 col-lg-3">
                                        <label for="empresa" class="form-label">Empresa</label>
                                        <select id="empresa" name="empresa" class="form-select">
                                            <option value="todos" {{ ($empresaSeleccionada ?? 'todos') === 'todos' ? 'selected' : '' }}>Todos</option>
                                            @foreach (($empresasFiltro ?? []) as $empresa)
                                                <option value="{{ $empresa }}" {{ ($empresaSeleccionada ?? 'todos') === $empresa ? 'selected' : '' }}>{{ $empresa }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-12 col-md-4 col-lg-2 d-grid d-md-flex gap-2">
                                        <button type="submit" class="btn btn-primary">
                                            <i class="ri-refresh-line align-bottom me-1"></i>Cargar datos
                                        </button>
                                    </div>
                                    <div class="col-12 col-lg-4">
                                        @if (!empty($datosCargados))
                                            <div class="alert alert-info mb-0 py-2">
                                                Mostrando ventas del dia <strong>{{ \Carbon\Carbon::parse($fechaSeleccionadaVentas ?? now()->subDay()->toDateString())->format('d/m/Y') }}</strong>.
                                            </div>
                                        @else
                                            <div class="alert alert-warning mb-0 py-2">
                                                Selecciona los filtros y presiona <strong>Cargar datos</strong> para consultar la informacion.
                                            </div>
                                        @endif
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-xl-12">
                        <div class="card crm-widget">
                            <div class="card-body p-0">
                                <div class="row row-cols-xxl-5 row-cols-xl-5 row-cols-md-2 row-cols-1 g-0">
                                    <div class="col">
                                        <div class="py-4 px-3 kpi-card">
                                            <h5 class="text-muted text-uppercase fs-13">Agencias con ventas <i class="ri-arrow-up-circle-line text-success fs-18 float-end align-middle"></i></h5>
                                            <div class="d-flex align-items-center">
                                                <div class="flex-shrink-0">
                                                    <i class="ri-store-2-line text-muted kpi-icon"></i>
                                                </div>
                                                <div class="flex-grow-1 ms-3">
                                                    <h2 class="mb-0 kpi-value"><span class="counter-value" data-target="{{ $agenciasConVenta }}">0</span></h2>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col">
                                        <div class="mt-3 mt-md-0 py-4 px-3 kpi-card kpi-card-action"
                                            id="cardAgenciasSinVenta"
                                            role="button"
                                            tabindex="0"
                                            aria-label="Ver agencias sin ventas">
                                            <h5 class="text-muted text-uppercase fs-13">Agencias sin ventas <i class="ri-arrow-up-circle-line text-success fs-18 float-end align-middle"></i></h5>
                                            <div class="d-flex align-items-center">
                                                <div class="flex-shrink-0">
                                                    <i class="ri-store-3-line text-muted kpi-icon"></i>
                                                </div>
                                                <div class="flex-grow-1 ms-3">
                                                    <h2 class="mb-0 kpi-value"><span class="counter-value" data-target="{{ $agenciasSinVenta }}">0</span></h2>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col">
                                        <div class="mt-3 mt-md-0 py-4 px-3 kpi-card">
                                            <h5 class="text-muted text-uppercase fs-13">Tradicional <i class="ri-arrow-down-circle-line text-danger fs-18 float-end align-middle"></i></h5>
                                            <div class="d-flex align-items-center">
                                                <div class="flex-shrink-0">
                                                    <i class="ri-line-chart-line text-muted kpi-icon"></i>
                                                </div>
                                                <div class="flex-grow-1 ms-3">
                                                    <h2 class="mb-0 kpi-currency">RD$ <span class="counter-value" data-target="{{ round($ventaTradicional, 2) }}">0</span></h2>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col">
                                        <div class="mt-3 mt-lg-0 py-4 px-3 kpi-card">
                                            <h5 class="text-muted text-uppercase fs-13">No Tradicional <i class="ri-arrow-up-circle-line text-success fs-18 float-end align-middle"></i></h5>
                                            <div class="d-flex align-items-center">
                                                <div class="flex-shrink-0">
                                                    <i class="ri-money-dollar-circle-line text-muted kpi-icon"></i>
                                                </div>
                                                <div class="flex-grow-1 ms-3">
                                                    <h2 class="mb-0 kpi-currency">RD$ <span class="counter-value" data-target="{{ round($ventaNoTradicional, 2) }}">0</span></h2>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col">
                                        <div class="mt-3 mt-lg-0 py-4 px-3 kpi-card">
                                            <h5 class="text-muted text-uppercase fs-13">Recargas <i class="ri-arrow-down-circle-line text-danger fs-18 float-end align-middle"></i></h5>
                                            <div class="d-flex align-items-center">
                                                <div class="flex-shrink-0">
                                                    <i class="ri-shopping-bag-3-line text-muted kpi-icon"></i>
                                                </div>
                                                <div class="flex-grow-1 ms-3">
                                                    <h2 class="mb-0 kpi-currency">RD$ <span class="counter-value" data-target="{{ round($ventaRecargas, 2) }}">0</span></h2>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-xxl-3 col-md-6">
                        <div class="card">
                            <div class="card-header align-items-center d-flex">
                                <h4 class="card-title mb-0 flex-grow-1">Ventas de Productos</h4>
                            </div>
                            <div class="card-body pb-0">
                                <div
                                    id="sales-forecast-chart"
                                    data-colors='["--vz-primary", "--vz-success", "--vz-warning"]'
                                    data-series='{{ json_encode([round($ventaTradicional, 2), round($ventaNoTradicional, 2), round($ventaRecargas, 2)]) }}'
                                    data-agencias='{{ json_encode([$agenciasTradicional, $agenciasNoTradicional, $agenciasRecargas]) }}'
                                    class="apex-charts"
                                    dir="ltr"></div>
                            </div>
                        </div>
                    </div>

                    <div class="col-xxl-9 col-md-6">
                        <div class="card card-height-100">
                            <div class="card-header align-items-center d-flex">
                                <h4 class="card-title mb-0 flex-grow-1">Resumen de balance</h4>
                                <span class="badge bg-info-subtle text-info">Periodo: {{ $balancePeriodoInicio }} - {{ $balancePeriodoFin }}</span>
                            </div>
                            <div class="card-body px-0">
                                <div
                                    id="revenue-expenses-charts"
                                    data-colors='["--vz-success", "--vz-danger", "--vz-warning"]'
                                    data-categories='{{ json_encode($balanceMensual["dias"] ?? []) }}'
                                    data-ingresos='{{ json_encode($balanceMensual["ingresos"] ?? []) }}'
                                    data-gastos='{{ json_encode($balanceMensual["gastos"] ?? []) }}'
                                    data-margen='{{ json_encode($balanceMensual["margen"] ?? []) }}'
                                    class="apex-charts"
                                    dir="ltr"></div>

                                <ul class="list-inline main-chart text-center mb-0 mt-2">
                                    <li class="list-inline-item chart-border-left me-0 border-0">
                                        <h4 class="text-primary">RD$ {{ number_format((float) $balanceIngresosTotal, 2) }} <span class="text-muted d-inline-block fs-13 align-middle ms-2"><i class="ri-checkbox-blank-circle-fill text-success me-1"></i>Tradicional</span></h4>
                                    </li>
                                    <li class="list-inline-item chart-border-left me-0">
                                        <h4>RD$ {{ number_format((float) $balanceGastosTotal, 2) }}<span class="text-muted d-inline-block fs-13 align-middle ms-2"><i class="ri-checkbox-blank-circle-fill text-danger me-1"></i>No Tradicional</span></h4>
                                    </li>
                                    <li class="list-inline-item chart-border-left me-0">
                                        <h4>RD$ {{ number_format((float) $balanceMargenTotal, 2) }}<span class="text-muted d-inline-block fs-13 align-middle ms-2"><i class="ri-checkbox-blank-circle-fill text-warning me-1"></i>Recargas</span></h4>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row mt-2">
                    <div class="col-12">
                        <h4 class="mb-3">Productos No Tradicionales</h4>
                    </div>
                    @if (count($productosNoTradicionales) === 0)
                        <div class="col-12">
                            <div class="alert alert-warning">No hay productos no tradicionales con ventas para la fecha/filtro seleccionado.</div>
                        </div>
                    @else
                        @foreach ($productosNoTradicionales as $producto)
                            @php
                                $rank = $loop->iteration;
                                $rankClass = $rank <= 3 ? 'rank-' . $rank : '';
                            @endphp
                            <div class="col-12 col-md-6 col-xl-4 col-xxl-3 mb-3">
                                <div class="card h-100 sorteo-card">
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between align-items-start mb-3">
                                            <h6 class="mb-0 fs-14 text-uppercase pe-2">{{ $producto['nombre'] }}</h6>
                                            <span class="rank-badge {{ $rankClass }}">#{{ $rank }}</span>
                                        </div>
                                        <h4 class="mb-2">RD$ {{ number_format((float) ($producto['total'] ?? 0), 2) }}</h4>
                                        <p class="text-muted mb-0">Agencias con venta: <span class="fw-semibold text-info">{{ (int) ($producto['agencias'] ?? 0) }}</span></p>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    @endif
                </div>

                <div class="row mt-2">
                    <div class="col-12">
                        <h4 class="mb-3">Productos Tradicionales (Top 10)</h4>
                    </div>
                    @if (count($productosTradicionalesTop) === 0)
                        <div class="col-12">
                            <div class="alert alert-warning">No hay productos tradicionales con ventas para la fecha/filtro seleccionado.</div>
                        </div>
                    @else
                        @foreach ($productosTradicionalesTop as $producto)
                            @php
                                $rank = $loop->iteration;
                                $rankClass = $rank <= 3 ? 'rank-' . $rank : '';
                            @endphp
                            <div class="col-12 col-md-6 col-xl-4 col-xxl-3 mb-3">
                                <div class="card h-100 sorteo-card">
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between align-items-start mb-3">
                                            <h6 class="mb-0 fs-14 text-uppercase pe-2">{{ $producto['nombre'] }}</h6>
                                            <span class="rank-badge {{ $rankClass }}">#{{ $rank }}</span>
                                        </div>
                                        <h4 class="mb-2">RD$ {{ number_format((float) ($producto['total'] ?? 0), 2) }}</h4>
                                        <p class="text-muted mb-0">Agencias con venta: <span class="fw-semibold text-info">{{ (int) ($producto['agencias'] ?? 0) }}</span></p>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    @endif
                </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modalAgenciasSinVentaInicio" tabindex="-1" aria-labelledby="modalAgenciasSinVentaInicioLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header d-flex justify-content-between align-items-center">
                    <h5 class="modal-title" id="modalAgenciasSinVentaInicioLabel">Agencias sin venta</h5>
                    <div class="d-flex align-items-center gap-2">
                        <button type="button" class="btn btn-success btn-sm" id="btnDescargarAgenciasSinVentaInicioExcel">
                            <i class="ri-file-excel-2-line me-1"></i>Descargar Excel
                        </button>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                </div>
                <div class="modal-body">
                    <div class="table-responsive">
                        <table id="tableModalAgenciasSinVentaInicio" class="table table-striped table-bordered w-100">
                            <thead>
                                <tr>
                                    <th>Agencia</th>
                                    <th>Terminal</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cerrar</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('script')
    <script>
        window.crmDashboardData = @json($crmDashboardData);
    </script>
    <script src="{{ asset('libs/apexcharts/apexcharts.min.js') }}"></script>
    <script src="{{ asset('libs/swiper/swiper-bundle.min.js') }}"></script>
    <script src="{{ asset('js/pages/dashboard-crm.init.js') }}?v={{ @filemtime(public_path('js/pages/dashboard-crm.init.js')) ?: time() }}"></script>
    <script src="{{ asset('js/pages/dashboard-crypto.init.js') }}?v={{ @filemtime(public_path('js/pages/dashboard-crypto.init.js')) ?: time() }}"></script>
    <script>
        (function () {
            const modalAgencyEntriesSinVentasInicio = Array.isArray(window.crmDashboardData?.agenciasSinVenta)
                ? window.crmDashboardData.agenciasSinVenta
                : [];
            let dtModalAgenciasSinVentaInicio = null;

            const escapeHtml = (value) => (value ?? '').toString()
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#39;');

            const bootFiltroConCarga = () => {
                const form = document.getElementById('inicioFiltroForm');

                if (!form) return;

                const showLoadingAlert = () => {
                    if (typeof Swal === 'undefined' || typeof Swal.fire !== 'function') return;

                    Swal.fire({
                        title: 'Cargando data...',
                        text: 'Espere un momento por favor.',
                        allowOutsideClick: false,
                        allowEscapeKey: false,
                        showConfirmButton: false,
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });
                };

                form.addEventListener('submit', () => {
                    showLoadingAlert();
                });
            };

            const bootExtraCards = () => {
                if (typeof ApexCharts === 'undefined' || typeof getChartColorsArray !== 'function') return;

                const renderExtraSparkline = (id, name, data) => {
                    const colors = getChartColorsArray(id);
                    if (!colors || !document.querySelector(`#${id}`)) return;

                    const options = {
                        series: [{ name, data }],
                        chart: {
                            width: 130,
                            height: 46,
                            type: 'area',
                            sparkline: { enabled: true },
                            toolbar: { show: false }
                        },
                        dataLabels: { enabled: false },
                        stroke: { curve: 'smooth', width: 1.5 },
                        fill: {
                            type: 'gradient',
                            gradient: {
                                shadeIntensity: 1,
                                inverseColors: false,
                                opacityFrom: 0.45,
                                opacityTo: 0.05,
                                stops: [50, 100, 100, 100]
                            }
                        },
                        colors
                    };

                    new ApexCharts(document.querySelector(`#${id}`), options).render();
                };

                renderExtraSparkline('nueva_sparkline_charts_1', 'doble chance express extraordinario', [85, 68, 35, 90, 8, 11, 26, 54]);
                renderExtraSparkline('nueva_sparkline_charts_2', 'triple chance express extraordinario', [25, 50, 41, 87, 12, 36, 9, 54]);
                renderExtraSparkline('nueva_sparkline_charts_3', 'extra lotto', [36, 21, 65, 22, 35, 50, 29, 44]);
                renderExtraSparkline('nueva_sparkline_charts_4', 'power lotto', [30, 58, 29, 89, 12, 36, 9, 54]);
            };

            const descargarAgenciasSinVentaExcel = () => {
                if (!modalAgencyEntriesSinVentasInicio.length) {
                    Swal.fire({ title: 'Sin datos', text: 'No hay agencias sin venta para descargar.', icon: 'info' });
                    return;
                }

                const filas = modalAgencyEntriesSinVentasInicio.map((item) => {
                    const nombre = escapeHtml(item?.nombre_agencia ?? item?.agencia_id ?? 'SIN AGENCIA');
                    const terminal = escapeHtml(item?.terminal ?? item?.agencia_id ?? 'SIN TERMINAL');

                    return `<tr><td>${nombre}</td><td>${terminal}</td></tr>`;
                }).join('');

                const tablaHtml = `
                    <table>
                        <thead>
                            <tr>
                                <th>Agencia</th>
                                <th>Terminal</th>
                            </tr>
                        </thead>
                        <tbody>${filas}</tbody>
                    </table>
                `;

                const blob = new Blob(['\ufeff', tablaHtml], { type: 'application/vnd.ms-excel;charset=utf-8;' });
                const url = URL.createObjectURL(blob);
                const enlace = document.createElement('a');
                const fecha = (document.getElementById('fecha')?.value || '').replace(/-/g, '') || 'sin_fecha';
                enlace.href = url;
                enlace.download = `agencias_sin_venta_inicio_${fecha}.xls`;
                document.body.appendChild(enlace);
                enlace.click();
                document.body.removeChild(enlace);
                URL.revokeObjectURL(url);
            };

            const abrirModalAgenciasSinVenta = () => {
                if (!modalAgencyEntriesSinVentasInicio.length) {
                    Swal.fire({ title: 'Sin datos', text: 'No hay agencias sin venta para mostrar.', icon: 'info' });
                    return;
                }

                if (dtModalAgenciasSinVentaInicio) {
                    dtModalAgenciasSinVentaInicio.destroy();
                    dtModalAgenciasSinVentaInicio = null;
                }

                const tbody = document.querySelector('#tableModalAgenciasSinVentaInicio tbody');

                if (!tbody) return;

                tbody.innerHTML = '';

                modalAgencyEntriesSinVentasInicio.forEach((item) => {
                    const nombre = (item?.nombre_agencia ?? item?.agencia_id ?? 'SIN AGENCIA').toString().trim() || 'SIN AGENCIA';
                    const terminal = (item?.terminal ?? item?.agencia_id ?? 'SIN TERMINAL').toString().trim() || 'SIN TERMINAL';
                    const tr = document.createElement('tr');
                    tr.innerHTML = `
                        <td>${escapeHtml(nombre)}</td>
                        <td>${escapeHtml(terminal)}</td>
                    `;
                    tbody.appendChild(tr);
                });

                if (typeof $ === 'function' && $.fn?.DataTable) {
                    dtModalAgenciasSinVentaInicio = $('#tableModalAgenciasSinVentaInicio').DataTable({
                        destroy: true,
                        responsive: true,
                        language: {
                            url: '/json/es-DO.json',
                            search: 'Buscar:',
                            lengthMenu: 'Mostrar _MENU_ registros',
                            info: 'Mostrando _START_ a _END_ de _TOTAL_ registros',
                            paginate: { first: 'Primera', last: 'Ultima', next: 'Siguiente', previous: 'Anterior' }
                        },
                        order: [[0, 'asc']],
                    });
                }

                const modal = new bootstrap.Modal(document.getElementById('modalAgenciasSinVentaInicio'));
                modal.show();
            };

            const bootAgenciasSinVentaCard = () => {
                const card = document.getElementById('cardAgenciasSinVenta');
                const btnExcel = document.getElementById('btnDescargarAgenciasSinVentaInicioExcel');

                if (card) {
                    card.addEventListener('click', abrirModalAgenciasSinVenta);
                    card.addEventListener('keydown', (event) => {
                        if (event.key !== 'Enter' && event.key !== ' ') return;
                        event.preventDefault();
                        abrirModalAgenciasSinVenta();
                    });
                }

                if (btnExcel) {
                    btnExcel.addEventListener('click', descargarAgenciasSinVentaExcel);
                }
            };

            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', () => {
                    bootFiltroConCarga();
                    bootExtraCards();
                    bootAgenciasSinVentaCard();
                });
            } else {
                bootFiltroConCarga();
                bootExtraCards();
                bootAgenciasSinVentaCard();
            }
        })();
    </script>
@endsection
