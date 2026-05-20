@extends('app')

@section('content')
    <div class="main-content">
        <div class="page-content">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-12">
                        <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                            <h4 class="mb-sm-0">Comercial</h4>
                            <div class="page-title-right">
                                <ol class="breadcrumb m-0">
                                    <li class="breadcrumb-item"><a href="{{ route('inicio.index') }}">Inicio</a></li>
                                    <li class="breadcrumb-item"><a href="{{ route('comercial.index') }}">Comercial</a></li>
                                    <li class="breadcrumb-item active">KPI Ventas</li>
                                </ol>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-body">
                                <form method="GET" action="{{ route('comercial.kpi-ventas') }}" class="row g-3 align-items-end" id="form-filtro-mes-kpi">
                                    <input type="hidden" name="meta_tradicional" id="meta-tradicional-hidden" value="{{ $metasDiarias['tradicional'] ?? 0 }}">
                                    <input type="hidden" name="meta_no_tradicional" id="meta-no-tradicional-hidden" value="{{ $metasDiarias['no_tradicional'] ?? 0 }}">
                                    <input type="hidden" name="meta_recargas" id="meta-recargas-hidden" value="{{ $metasDiarias['recargas'] ?? 0 }}">
                                    <div class="col-md-3">
                                        <label class="form-label">Mes</label>
                                        <input type="month" name="mes" class="form-control" value="{{ $mesSeleccionado ?? now()->format('Y-m') }}">
                                    </div>
                                    <div class="col-md-9">
                                        <button type="submit" class="btn btn-primary me-2" id="btn-filtrar-kpi">
                                            <i class="ri-filter-3-line me-1"></i>Filtrar
                                        </button>
                                        <a href="{{ route('comercial.kpi-ventas') }}" class="btn btn-light">Mes actual</a>
                                        <button type="button" class="btn btn-soft-info ms-2" data-bs-toggle="modal" data-bs-target="#modalMetaDiaria">
                                            <i class="ri-settings-3-line me-1"></i>Configurar meta diaria
                                        </button>
                                        <button type="button" class="btn btn-soft-primary ms-2" data-bs-toggle="modal" data-bs-target="#modalRentabilidad">
                                            <i class="ri-funds-line me-1"></i>Rentabilidad
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>

                    <div class="col-xl-4 col-md-6">
                        <div class="card card-animate">
                            <div class="card-body">
                                <div class="d-flex align-items-center">
                                    <div class="flex-grow-1">
                                        <p class="text-uppercase fw-medium text-muted mb-0">Tradicional Acumulado</p>
                                    </div>
                                </div>
                                <div class="d-flex align-items-end justify-content-between mt-4">
                                    <div>
                                        <h4 class="fs-22 fw-semibold mb-0">RD$ {{ number_format($kpis['tradicional'] ?? 0, 2) }}</h4>
                                        <small class="text-muted d-block mt-1">Meta diaria: <span id="meta-tradicional">RD$ 0.00</span></small>
                                    </div>
                                    <div class="avatar-sm flex-shrink-0">
                                        <span class="avatar-title bg-primary-subtle rounded fs-3">
                                            <i class="ri-line-chart-line text-primary"></i>
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-xl-4 col-md-6">
                        <div class="card card-animate">
                            <div class="card-body">
                                <div class="d-flex align-items-center">
                                    <div class="flex-grow-1">
                                        <p class="text-uppercase fw-medium text-muted mb-0">No Tradicional Acumulado</p>
                                    </div>
                                </div>
                                <div class="d-flex align-items-end justify-content-between mt-4">
                                    <div>
                                        <h4 class="fs-22 fw-semibold mb-0">RD$ {{ number_format($kpis['no_tradicional'] ?? 0, 2) }}</h4>
                                        <small class="text-muted d-block mt-1">Meta diaria: <span id="meta-no-tradicional">RD$ 0.00</span></small>
                                    </div>
                                    <div class="avatar-sm flex-shrink-0">
                                        <span class="avatar-title bg-info-subtle rounded fs-3">
                                            <i class="ri-bar-chart-grouped-line text-info"></i>
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-xl-4 col-md-6">
                        <div class="card card-animate">
                            <div class="card-body">
                                <div class="d-flex align-items-center">
                                    <div class="flex-grow-1">
                                        <p class="text-uppercase fw-medium text-muted mb-0">Recargas Acumulado</p>
                                    </div>
                                </div>
                                <div class="d-flex align-items-end justify-content-between mt-4">
                                    <div>
                                        <h4 class="fs-22 fw-semibold mb-0">RD$ {{ number_format($kpis['recargas'] ?? 0, 2) }}</h4>
                                        <small class="text-muted d-block mt-1">Meta diaria: <span id="meta-recargas">RD$ 0.00</span></small>
                                    </div>
                                    <div class="avatar-sm flex-shrink-0">
                                        <span class="avatar-title bg-success-subtle rounded fs-3">
                                            <i class="ri-exchange-dollar-line text-success"></i>
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-12">
                        <div class="card">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
                                    <div>
                                        <p class="text-muted mb-1">Fase 1: acumulado por mes</p>
                                        <small class="text-muted">Resumen de cumplimiento por agencia según gasto configurado.</small>
                                    </div>
                                    <div class="d-flex align-items-center gap-4">
                                        <div>
                                            <small class="text-muted d-block">Agencias que cumplen</small>
                                            <h5 class="mb-0 text-success" id="card-agencias-cumplen-rentabilidad">0</h5>
                                        </div>
                                        <div>
                                            <small class="text-muted d-block">Agencias que no cumplen</small>
                                            <h5 class="mb-0 text-danger" id="card-agencias-no-cumplen-rentabilidad">0</h5>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-12 mt-2">
                        <div class="card">
                            <div class="card-body">
                                <div class="d-flex align-items-center gap-4 flex-wrap">
                                    <div>
                                        <small class="text-muted d-block">Total Ventas</small>
                                        <h5 class="mb-0" id="card-total-ventas-rentabilidad">RD$ 0.00</h5>
                                    </div>
                                    <div>
                                        <small class="text-muted d-block">Total Premios</small>
                                        <h5 class="mb-0" id="card-total-premios-rentabilidad">RD$ 0.00</h5>
                                    </div>
                                    <div>
                                        <small class="text-muted d-block">Total Gastos</small>
                                        <h5 class="mb-0" id="card-total-gastos-rentabilidad">RD$ 0.00</h5>
                                    </div>
                                    <div>
                                        <small class="text-muted d-block">Total Ganancia Neta</small>
                                        <h5 class="mb-0" id="card-total-ganancia-neta-rentabilidad">RD$ 0.00</h5>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-xl-4 col-md-6">
                        <div class="card border-primary border-opacity-25">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <h6 class="mb-0 text-primary">Meta mensual Tradicional Por Agencia</h6>
                                    <span class="badge bg-primary-subtle text-primary" id="pct-faltante-tradicional">{{ number_format($agenciasPorTipo['tradicional'] ?? 0, 0) }} agencias</span>
                                </div>
                                <h5 class="fw-semibold mb-2" id="meta-mensual-tradicional">RD$ {{ number_format($cumplimiento['tradicional']['meta_mensual'] ?? 0, 2) }}</h5>
                                <small class="text-muted d-block">Meta Mensual</small>
                                <small class="fw-medium text-primary" id="monto-faltante-tradicional">RD$ {{ number_format((($cumplimiento['tradicional']['meta_mensual'] ?? 0) * ($agenciasPorTipo['tradicional'] ?? 0)), 2) }}</small>
                                @php
                                    $deltaTradicional = ($kpis['tradicional'] ?? 0) - ($cumplimiento['tradicional']['meta_mensual'] ?? 0);
                                @endphp
                                <small class="text-muted d-block mt-2">Acumulado - Meta</small>
                                <small class="fw-medium {{ $deltaTradicional >= 0 ? 'text-success' : 'text-danger' }}" id="delta-meta-tradicional">RD$ {{ number_format($deltaTradicional, 2) }}</small>
                            </div>
                        </div>
                    </div>

                    <div class="col-xl-4 col-md-6">
                        <div class="card border-info border-opacity-25">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <h6 class="mb-0 text-info">Meta mensual No Tradicional Por Agencia</h6>
                                    <span class="badge bg-info-subtle text-info" id="pct-faltante-no-tradicional">{{ number_format($agenciasPorTipo['no_tradicional'] ?? 0, 0) }} agencias</span>
                                </div>
                                <h5 class="fw-semibold mb-2" id="meta-mensual-no-tradicional">RD$ {{ number_format($cumplimiento['no_tradicional']['meta_mensual'] ?? 0, 2) }}</h5>
                                <small class="text-muted d-block">Meta Mensual</small>
                                <small class="fw-medium text-primary" id="monto-faltante-no-tradicional">RD$ {{ number_format((($cumplimiento['no_tradicional']['meta_mensual'] ?? 0) * ($agenciasPorTipo['no_tradicional'] ?? 0)), 2) }}</small>
                                @php
                                    $deltaNoTradicional = ($kpis['no_tradicional'] ?? 0) - ($cumplimiento['no_tradicional']['meta_mensual'] ?? 0);
                                @endphp
                                <small class="text-muted d-block mt-2">Acumulado - Meta</small>
                                <small class="fw-medium {{ $deltaNoTradicional >= 0 ? 'text-success' : 'text-danger' }}" id="delta-meta-no-tradicional">RD$ {{ number_format($deltaNoTradicional, 2) }}</small>
                            </div>
                        </div>
                    </div>

                    <div class="col-xl-4 col-md-6">
                        <div class="card border-success border-opacity-25">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <h6 class="mb-0 text-success">Meta mensual Recargas Por Agencia</h6>
                                    <span class="badge bg-success-subtle text-success" id="pct-faltante-recargas">{{ number_format($agenciasPorTipo['recargas'] ?? 0, 0) }} agencias</span>
                                </div>
                                <h5 class="fw-semibold mb-2" id="meta-mensual-recargas">RD$ {{ number_format($cumplimiento['recargas']['meta_mensual'] ?? 0, 2) }}</h5>
                                <small class="text-muted d-block">Meta Mensual</small>
                                <small class="fw-medium text-primary" id="monto-faltante-recargas">RD$ {{ number_format((($cumplimiento['recargas']['meta_mensual'] ?? 0) * ($agenciasPorTipo['recargas'] ?? 0)), 2) }}</small>
                                @php
                                    $deltaRecargas = ($kpis['recargas'] ?? 0) - ($cumplimiento['recargas']['meta_mensual'] ?? 0);
                                @endphp
                                <small class="text-muted d-block mt-2">Acumulado - Meta</small>
                                <small class="fw-medium {{ $deltaRecargas >= 0 ? 'text-success' : 'text-danger' }}" id="delta-meta-recargas">RD$ {{ number_format($deltaRecargas, 2) }}</small>
                            </div>
                        </div>
                    </div>

                    <div class="col-12 mt-2">
                        <div class="card">
                            <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-3">
                                <h5 class="card-title mb-0">Rentabilidad por Agencia (Ventas Mensuales)</h5>
                                <div class="d-flex align-items-center gap-2 flex-wrap justify-content-end">
                                    <div class="d-flex flex-column gap-2" style="min-width: 240px;">
                                        <input type="text" class="form-control form-control-sm" id="buscar-nombre-rentabilidad" placeholder="Buscar por nombre o terminal">
                                        <select class="form-select form-select-sm" id="filtro-cumplimiento-rentabilidad">
                                            <option value="todos">Todos</option>
                                            <option value="cumple">Cumple</option>
                                            <option value="no_cumple">No cumple</option>
                                        </select>
                                    </div>
                                    <span class="badge bg-primary-subtle text-primary" id="label-meta-rentabilidad">Gasto por agencia: RD$ 0.00</span>
                                    <button type="button" class="btn btn-soft-primary btn-sm" id="btn-generar-rentabilidad">
                                        <i class="ri-refresh-line me-1"></i>Generar data
                                    </button>
                                    <button type="button" class="btn btn-success btn-sm" id="btn-exportar-rentabilidad" disabled>
                                        <i class="ri-file-excel-2-line me-1"></i>Descargar Excel
                                    </button>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-bordered table-striped align-middle mb-0" id="table-rentabilidad-agencias">
                                        <thead>
                                            <tr>
                                                <th>Agencia</th>
                                                <th>Ventas</th>
                                                <th>Premios Pagados</th>
                                                <th>Utilidad Bruta</th>
                                                <th>Gasto Agencia</th>
                                                <th>Ganancia Neta</th>
                                                <th>Cumplimiento</th>
                                            </tr>
                                        </thead>
                                        <tbody id="tbody-rentabilidad-agencias"></tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="modal fade" id="modalMetaDiaria" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title"><i class="ri-settings-3-line me-2"></i>Configurar Meta Diaria por Producto</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <div class="mb-3">
                                    <label class="form-label">Tradicional (RD$)</label>
                                    <input type="number" step="0.01" min="0" class="form-control" id="input-meta-tradicional" placeholder="0.00">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">No Tradicional (RD$)</label>
                                    <input type="number" step="0.01" min="0" class="form-control" id="input-meta-no-tradicional" placeholder="0.00">
                                </div>
                                <div class="mb-0">
                                    <label class="form-label">Recargas (RD$)</label>
                                    <input type="number" step="0.01" min="0" class="form-control" id="input-meta-recargas" placeholder="0.00">
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancelar</button>
                                <button type="button" class="btn btn-primary" id="btn-guardar-meta-diaria">
                                    <i class="ri-save-line me-1"></i>Guardar configuración
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="modal fade" id="modalRentabilidad" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title"><i class="ri-funds-line me-2"></i>Configurar Rentabilidad</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <label class="form-label">Gasto mensual por agencia a cumplir (RD$)</label>
                                <input type="number" step="0.01" min="0" class="form-control" id="input-meta-rentabilidad" placeholder="0.00">
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancelar</button>
                                <button type="button" class="btn btn-primary" id="btn-guardar-rentabilidad">
                                    <i class="ri-save-line me-1"></i>Guardar
                                </button>
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
        ['modalMetaDiaria', 'modalRentabilidad'].forEach(function (modalId) {
            const modalEl = document.getElementById(modalId);
            if (modalEl && modalEl.parentElement !== document.body) {
                document.body.appendChild(modalEl);
            }
        });

        const STORAGE_KEY = 'comercial_kpi_meta_diaria_v1';
        const STORAGE_KEY_RENTABILIDAD = 'comercial_kpi_meta_rentabilidad_v1';
        const formFiltro = document.getElementById('form-filtro-mes-kpi');
        const botonFiltrar = document.getElementById('btn-filtrar-kpi');
        const btnGuardarMeta = document.getElementById('btn-guardar-meta-diaria');
        const btnGuardarRentabilidad = document.getElementById('btn-guardar-rentabilidad');
        const btnGenerarRentabilidad = document.getElementById('btn-generar-rentabilidad');
        const btnExportarRentabilidad = document.getElementById('btn-exportar-rentabilidad');

        const inputTrad = document.getElementById('input-meta-tradicional');
        const inputNoTrad = document.getElementById('input-meta-no-tradicional');
        const inputRec = document.getElementById('input-meta-recargas');
        const inputMetaRentabilidad = document.getElementById('input-meta-rentabilidad');

        const inputTradHidden = document.getElementById('meta-tradicional-hidden');
        const inputNoTradHidden = document.getElementById('meta-no-tradicional-hidden');
        const inputRecHidden = document.getElementById('meta-recargas-hidden');

        const lblTrad = document.getElementById('meta-tradicional');
        const lblNoTrad = document.getElementById('meta-no-tradicional');
        const lblRec = document.getElementById('meta-recargas');

        const lblMetaMensualTrad = document.getElementById('meta-mensual-tradicional');
        const lblMetaMensualNoTrad = document.getElementById('meta-mensual-no-tradicional');
        const lblMetaMensualRec = document.getElementById('meta-mensual-recargas');

        const lblFaltanteTrad = document.getElementById('monto-faltante-tradicional');
        const lblFaltanteNoTrad = document.getElementById('monto-faltante-no-tradicional');
        const lblFaltanteRec = document.getElementById('monto-faltante-recargas');

        const lblPctTrad = document.getElementById('pct-faltante-tradicional');
        const lblPctNoTrad = document.getElementById('pct-faltante-no-tradicional');
        const lblPctRec = document.getElementById('pct-faltante-recargas');
        const agenciasPorTipo = {
            tradicional: Number(@json($agenciasPorTipo['tradicional'] ?? 0)),
            no_tradicional: Number(@json($agenciasPorTipo['no_tradicional'] ?? 0)),
            recargas: Number(@json($agenciasPorTipo['recargas'] ?? 0)),
        };
        const lblDeltaTrad = document.getElementById('delta-meta-tradicional');
        const lblDeltaNoTrad = document.getElementById('delta-meta-no-tradicional');
        const lblDeltaRec = document.getElementById('delta-meta-recargas');
        const lblMetaRentabilidad = document.getElementById('label-meta-rentabilidad');
        const tbodyRentabilidad = document.getElementById('tbody-rentabilidad-agencias');
        const buscarNombreRentabilidad = document.getElementById('buscar-nombre-rentabilidad');
        const filtroCumplimientoRentabilidad = document.getElementById('filtro-cumplimiento-rentabilidad');
        const cardAgenciasCumplenRentabilidad = document.getElementById('card-agencias-cumplen-rentabilidad');
        const cardAgenciasNoCumplenRentabilidad = document.getElementById('card-agencias-no-cumplen-rentabilidad');
        const cardTotalVentasRentabilidad = document.getElementById('card-total-ventas-rentabilidad');
        const cardTotalPremiosRentabilidad = document.getElementById('card-total-premios-rentabilidad');
        const cardTotalGastosRentabilidad = document.getElementById('card-total-gastos-rentabilidad');
        const cardTotalGananciaNetaRentabilidad = document.getElementById('card-total-ganancia-neta-rentabilidad');

        const resumenAgencias = @json($resumenAgencias ?? []);
        const rentabilidadCargada = @json($rentabilidadCargada ?? false);
        const mesSeleccionado = @json($mesSeleccionado ?? now()->format('Y-m'));
        let estadoFiltroCumplimientoRentabilidad = 'todos';
        let estadoBusquedaNombreRentabilidad = '';
        let rentabilidadRenderizada = rentabilidadCargada;

        const acumulados = {
            tradicional: Number(@json($kpis['tradicional'] ?? 0)),
            no_tradicional: Number(@json($kpis['no_tradicional'] ?? 0)),
            recargas: Number(@json($kpis['recargas'] ?? 0)),
        };

        function formatCurrency(value) {
            return 'RD$ ' + Number(value || 0).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        }

        function escapeHtml(value) {
            return String(value ?? '')
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#039;');
        }

        function setResumenRentabilidad(totalVentas, totalPremios, totalGastos, totalGananciaNeta, totalCumplen, totalNoCumplen) {
            if (cardTotalVentasRentabilidad) {
                cardTotalVentasRentabilidad.textContent = formatCurrency(totalVentas);
            }
            if (cardTotalPremiosRentabilidad) {
                cardTotalPremiosRentabilidad.textContent = formatCurrency(totalPremios);
            }
            if (cardTotalGastosRentabilidad) {
                cardTotalGastosRentabilidad.textContent = formatCurrency(totalGastos);
            }
            if (cardTotalGananciaNetaRentabilidad) {
                cardTotalGananciaNetaRentabilidad.textContent = formatCurrency(totalGananciaNeta);
                cardTotalGananciaNetaRentabilidad.classList.remove('text-success', 'text-danger');
                cardTotalGananciaNetaRentabilidad.classList.add(totalGananciaNeta >= 0 ? 'text-success' : 'text-danger');
            }

            if (cardAgenciasCumplenRentabilidad) {
                cardAgenciasCumplenRentabilidad.textContent = Number(totalCumplen || 0).toLocaleString('es-DO');
            }
            if (cardAgenciasNoCumplenRentabilidad) {
                cardAgenciasNoCumplenRentabilidad.textContent = Number(totalNoCumplen || 0).toLocaleString('es-DO');
            }
        }

        function actualizarBotonExportarRentabilidad() {
            if (!btnExportarRentabilidad || !tbodyRentabilidad) return;

            const tieneFilas = tbodyRentabilidad.querySelectorAll('tr[data-exportable="1"]').length > 0;
            btnExportarRentabilidad.disabled = !rentabilidadRenderizada || !tieneFilas;
        }

        function renderTablaRentabilidadPendiente(metaMensual) {
            if (!tbodyRentabilidad) return;

            rentabilidadRenderizada = false;
            if (lblMetaRentabilidad) {
                lblMetaRentabilidad.textContent = 'Gasto por agencia: ' + formatCurrency(metaMensual);
            }

            tbodyRentabilidad.innerHTML = '<tr><td colspan="7" class="text-center text-muted">Configura la rentabilidad y presiona Generar data para cargar la tabla.</td></tr>';
            setResumenRentabilidad(0, 0, 0, 0, 0, 0);
            actualizarBotonExportarRentabilidad();
        }

        function cargarRentabilidadDesdeFormulario() {
            if (!formFiltro) return;

            let inputCargar = formFiltro.querySelector('input[name="cargar_rentabilidad"]');
            if (!inputCargar) {
                inputCargar = document.createElement('input');
                inputCargar.type = 'hidden';
                inputCargar.name = 'cargar_rentabilidad';
                formFiltro.appendChild(inputCargar);
            }
            inputCargar.value = '1';

            if (botonFiltrar) botonFiltrar.disabled = true;
            if (btnGenerarRentabilidad) btnGenerarRentabilidad.disabled = true;

            Swal.fire({
                title: 'Cargando...',
                text: 'Generando data de rentabilidad, por favor espera.',
                allowOutsideClick: false,
                allowEscapeKey: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            formFiltro.submit();
        }

        function getConfig() {
            try {
                const raw = localStorage.getItem(STORAGE_KEY);
                if (!raw) {
                    return { tradicional: 0, no_tradicional: 0, recargas: 0 };
                }

                const parsed = JSON.parse(raw);
                return {
                    tradicional: Number(parsed.tradicional || 0),
                    no_tradicional: Number(parsed.no_tradicional || 0),
                    recargas: Number(parsed.recargas || 0),
                };
            } catch (_) {
                return { tradicional: 0, no_tradicional: 0, recargas: 0 };
            }
        }

        function setConfig(config) {
            localStorage.setItem(STORAGE_KEY, JSON.stringify(config));
        }

        function getMetaRentabilidad() {
            const raw = localStorage.getItem(STORAGE_KEY_RENTABILIDAD);
            const value = Number(raw || 0);
            return Number.isFinite(value) && value >= 0 ? value : 0;
        }

        function setMetaRentabilidad(value) {
            localStorage.setItem(STORAGE_KEY_RENTABILIDAD, String(Math.max(0, Number(value || 0))));
        }

        function renderTablaRentabilidad(metaMensual) {
            if (!tbodyRentabilidad) return;

            rentabilidadRenderizada = true;
            tbodyRentabilidad.innerHTML = '';
            if (lblMetaRentabilidad) {
                lblMetaRentabilidad.textContent = 'Gasto por agencia: ' + formatCurrency(metaMensual);
            }

            let totalCumplen = 0;
            let totalNoCumplen = 0;
            let totalVentas = 0;
            let totalPremios = 0;
            let totalGastos = 0;
            let totalGananciaNeta = 0;

            (resumenAgencias || []).forEach(item => {
                const agencia = (item?.agencia ?? 'SIN AGENCIA').toString();
                const terminal = (item?.terminal ?? agencia).toString();
                const nombreAgencia = (item?.nombre_agencia ?? agencia).toString();
                const ventas = Number(item?.total_vendido ?? 0);
                const premiosPagados = Number(item?.premios_pagados ?? 0);
                const utilidadBruta = ventas - premiosPagados;
                const gastoAgencia = Number(metaMensual || 0);
                const gananciaNeta = utilidadBruta - gastoAgencia;
                const cumple = utilidadBruta >= gastoAgencia;
                const textoBusqueda = estadoBusquedaNombreRentabilidad.trim().toLowerCase();
                const coincideBusqueda = !textoBusqueda
                    || nombreAgencia.toLowerCase().includes(textoBusqueda)
                    || terminal.toLowerCase().includes(textoBusqueda);

                if (cumple) {
                    totalCumplen += 1;
                } else {
                    totalNoCumplen += 1;
                }

                totalVentas += ventas;
                totalPremios += premiosPagados;
                totalGastos += gastoAgencia;
                totalGananciaNeta += gananciaNeta;

                if (!coincideBusqueda) return;
                if (estadoFiltroCumplimientoRentabilidad === 'cumple' && !cumple) return;
                if (estadoFiltroCumplimientoRentabilidad === 'no_cumple' && cumple) return;

                const cumplimientoTexto = cumple ? 'Cumple' : 'No cumple';

                const tr = document.createElement('tr');
                tr.setAttribute('data-exportable', '1');
                tr.innerHTML = `
                    <td>
                        <div class="fw-medium">${escapeHtml(nombreAgencia)}</div>
                        <small class="text-muted">Terminal: ${escapeHtml(terminal)}</small>
                    </td>
                    <td>${formatCurrency(ventas)}</td>
                    <td>${formatCurrency(premiosPagados)}</td>
                    <td>${formatCurrency(utilidadBruta)}</td>
                    <td>${formatCurrency(gastoAgencia)}</td>
                    <td class="${gananciaNeta >= 0 ? 'text-success' : 'text-danger'} fw-medium">${formatCurrency(gananciaNeta)}</td>
                    <td>${cumplimientoTexto}</td>
                `;
                tbodyRentabilidad.appendChild(tr);
            });

            setResumenRentabilidad(totalVentas, totalPremios, totalGastos, totalGananciaNeta, totalCumplen, totalNoCumplen);

            if (!tbodyRentabilidad.children.length) {
                const tr = document.createElement('tr');
                tr.innerHTML = '<td colspan="7" class="text-center text-muted">No hay agencias para el filtro seleccionado.</td>';
                tbodyRentabilidad.appendChild(tr);
            }

            actualizarBotonExportarRentabilidad();
        }

        function descargarExcelRentabilidad() {
            if (!tbodyRentabilidad) return;

            const filas = Array.from(tbodyRentabilidad.querySelectorAll('tr[data-exportable="1"]'));
            if (!rentabilidadRenderizada || !filas.length) {
                Swal.fire({
                    icon: 'info',
                    title: 'Sin datos para exportar',
                    text: 'Primero genera la data de rentabilidad.'
                });
                return;
            }

            const headers = ['Agencia', 'Ventas', 'Premios Pagados', 'Utilidad Bruta', 'Gasto Agencia', 'Ganancia Neta', 'Cumplimiento'];
            const headerHtml = headers.map(header => `<th>${escapeHtml(header)}</th>`).join('');
            const rowsHtml = filas.map(row => {
                const cells = Array.from(row.children).map(cell => {
                    const value = (cell.innerText || cell.textContent || '').replace(/\s+/g, ' ').trim();
                    return `<td>${escapeHtml(value)}</td>`;
                }).join('');

                return `<tr>${cells}</tr>`;
            }).join('');

            const html = `
                <html>
                    <head><meta charset="UTF-8"></head>
                    <body>
                        <table border="1">
                            <thead><tr>${headerHtml}</tr></thead>
                            <tbody>${rowsHtml}</tbody>
                        </table>
                    </body>
                </html>
            `;

            const blob = new Blob(['\ufeff', html], { type: 'application/vnd.ms-excel;charset=utf-8;' });
            const url = URL.createObjectURL(blob);
            const link = document.createElement('a');
            link.href = url;
            link.download = `rentabilidad_agencias_${mesSeleccionado}.xls`;
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
            URL.revokeObjectURL(url);
        }

        function renderMetas(config) {
            if (lblTrad) lblTrad.textContent = formatCurrency(config.tradicional);
            if (lblNoTrad) lblNoTrad.textContent = formatCurrency(config.no_tradicional);
            if (lblRec) lblRec.textContent = formatCurrency(config.recargas);

            if (inputTrad) inputTrad.value = config.tradicional;
            if (inputNoTrad) inputNoTrad.value = config.no_tradicional;
            if (inputRec) inputRec.value = config.recargas;

            if (inputTradHidden) inputTradHidden.value = config.tradicional;
            if (inputNoTradHidden) inputNoTradHidden.value = config.no_tradicional;
            if (inputRecHidden) inputRecHidden.value = config.recargas;

            renderCumplimiento(config);
        }

        function renderCumplimiento(config) {
            const metasMensualesPorAgencia = {
                tradicional: Number(config.tradicional || 0) * 30,
                no_tradicional: Number(config.no_tradicional || 0) * 30,
                recargas: Number(config.recargas || 0) * 30,
            };

            const metasMensuales = {
                tradicional: metasMensualesPorAgencia.tradicional * Number(agenciasPorTipo.tradicional || 0),
                no_tradicional: metasMensualesPorAgencia.no_tradicional * Number(agenciasPorTipo.no_tradicional || 0),
                recargas: metasMensualesPorAgencia.recargas * Number(agenciasPorTipo.recargas || 0),
            };

            const faltantes = {
                tradicional: Math.max(0, metasMensuales.tradicional - acumulados.tradicional),
                no_tradicional: Math.max(0, metasMensuales.no_tradicional - acumulados.no_tradicional),
                recargas: Math.max(0, metasMensuales.recargas - acumulados.recargas),
            };

            const pctFaltantes = {
                tradicional: metasMensuales.tradicional > 0 ? (faltantes.tradicional / metasMensuales.tradicional) * 100 : 0,
                no_tradicional: metasMensuales.no_tradicional > 0 ? (faltantes.no_tradicional / metasMensuales.no_tradicional) * 100 : 0,
                recargas: metasMensuales.recargas > 0 ? (faltantes.recargas / metasMensuales.recargas) * 100 : 0,
            };

            const deltas = {
                tradicional: acumulados.tradicional - metasMensuales.tradicional,
                no_tradicional: acumulados.no_tradicional - metasMensuales.no_tradicional,
                recargas: acumulados.recargas - metasMensuales.recargas,
            };

            if (lblMetaMensualTrad) lblMetaMensualTrad.textContent = formatCurrency(metasMensualesPorAgencia.tradicional);
            if (lblMetaMensualNoTrad) lblMetaMensualNoTrad.textContent = formatCurrency(metasMensualesPorAgencia.no_tradicional);
            if (lblMetaMensualRec) lblMetaMensualRec.textContent = formatCurrency(metasMensualesPorAgencia.recargas);

            if (lblFaltanteTrad) lblFaltanteTrad.textContent = formatCurrency(metasMensuales.tradicional);
            if (lblFaltanteNoTrad) lblFaltanteNoTrad.textContent = formatCurrency(metasMensuales.no_tradicional);
            if (lblFaltanteRec) lblFaltanteRec.textContent = formatCurrency(metasMensuales.recargas);

            if (lblPctTrad) lblPctTrad.textContent = agenciasPorTipo.tradicional.toLocaleString('es-DO') + ' agencias';
            if (lblPctNoTrad) lblPctNoTrad.textContent = agenciasPorTipo.no_tradicional.toLocaleString('es-DO') + ' agencias';
            if (lblPctRec) lblPctRec.textContent = agenciasPorTipo.recargas.toLocaleString('es-DO') + ' agencias';

            if (lblDeltaTrad) {
                lblDeltaTrad.textContent = formatCurrency(deltas.tradicional);
                lblDeltaTrad.classList.remove('text-success', 'text-danger');
                lblDeltaTrad.classList.add(deltas.tradicional >= 0 ? 'text-success' : 'text-danger');
            }
            if (lblDeltaNoTrad) {
                lblDeltaNoTrad.textContent = formatCurrency(deltas.no_tradicional);
                lblDeltaNoTrad.classList.remove('text-success', 'text-danger');
                lblDeltaNoTrad.classList.add(deltas.no_tradicional >= 0 ? 'text-success' : 'text-danger');
            }
            if (lblDeltaRec) {
                lblDeltaRec.textContent = formatCurrency(deltas.recargas);
                lblDeltaRec.classList.remove('text-success', 'text-danger');
                lblDeltaRec.classList.add(deltas.recargas >= 0 ? 'text-success' : 'text-danger');
            }
        }

        const configInicial = getConfig();
        renderMetas(configInicial);
        const metaRentabilidadInicial = getMetaRentabilidad();
        if (inputMetaRentabilidad) inputMetaRentabilidad.value = metaRentabilidadInicial;
        if (rentabilidadCargada) {
            renderTablaRentabilidad(metaRentabilidadInicial);
        } else {
            renderTablaRentabilidadPendiente(metaRentabilidadInicial);
        }

        if (filtroCumplimientoRentabilidad) {
            filtroCumplimientoRentabilidad.addEventListener('change', function () {
                estadoFiltroCumplimientoRentabilidad = this.value || 'todos';
                if (rentabilidadRenderizada) {
                    renderTablaRentabilidad(getMetaRentabilidad());
                }
            });
        }

        if (buscarNombreRentabilidad) {
            buscarNombreRentabilidad.addEventListener('input', function () {
                estadoBusquedaNombreRentabilidad = this.value || '';
                if (rentabilidadRenderizada) {
                    renderTablaRentabilidad(getMetaRentabilidad());
                }
            });
        }

        if (btnGenerarRentabilidad) {
            btnGenerarRentabilidad.addEventListener('click', function () {
                setMetaRentabilidad(Math.max(0, Number(inputMetaRentabilidad?.value || getMetaRentabilidad())));
                cargarRentabilidadDesdeFormulario();
            });
        }

        if (btnExportarRentabilidad) {
            btnExportarRentabilidad.addEventListener('click', descargarExcelRentabilidad);
        }

        if (!formFiltro || !botonFiltrar) return;

        formFiltro.addEventListener('submit', function () {
            botonFiltrar.disabled = true;

            Swal.fire({
                title: 'Cargando...',
                text: 'Procesando filtro, por favor espera.',
                allowOutsideClick: false,
                allowEscapeKey: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });
        });

        if (btnGuardarMeta) {
            btnGuardarMeta.addEventListener('click', function () {
                const config = {
                    tradicional: Math.max(0, Number(inputTrad?.value || 0)),
                    no_tradicional: Math.max(0, Number(inputNoTrad?.value || 0)),
                    recargas: Math.max(0, Number(inputRec?.value || 0)),
                };

                setConfig(config);
                renderMetas(config);

                const modalEl = document.getElementById('modalMetaDiaria');
                const modal = bootstrap.Modal.getInstance(modalEl);
                if (modal) modal.hide();

                Swal.fire({
                    icon: 'success',
                    title: 'Configuración guardada',
                    text: 'Las metas diarias por producto fueron actualizadas.',
                    timer: 1700,
                    showConfirmButton: false
                });
            });
        }

        if (btnGuardarRentabilidad) {
            btnGuardarRentabilidad.addEventListener('click', function () {
                const meta = Math.max(0, Number(inputMetaRentabilidad?.value || 0));
                setMetaRentabilidad(meta);

                const modalEl = document.getElementById('modalRentabilidad');
                const modal = bootstrap.Modal.getInstance(modalEl);
                if (modal) modal.hide();

                if (!rentabilidadCargada && !rentabilidadRenderizada) {
                    cargarRentabilidadDesdeFormulario();
                    return;
                }

                renderTablaRentabilidad(meta);

                Swal.fire({
                    icon: 'success',
                    title: 'Rentabilidad actualizada',
                    text: 'El gasto por agencia fue aplicado a la tabla.',
                    timer: 1700,
                    showConfirmButton: false
                });
            });
        }
    });
</script>
@endsection

