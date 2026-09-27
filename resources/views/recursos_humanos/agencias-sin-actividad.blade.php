@extends('app')

@section('content')
    <div class="main-content">
        <div class="page-content">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-12">
                        <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                            <h4 class="mb-sm-0">Agencias sin actividad</h4>
                            <a href="{{ route('recursos-humanos.index') }}" class="btn btn-light">
                                <i class="ri-arrow-left-line me-1"></i> Recursos Humanos
                            </a>
                        </div>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header"><h5 class="card-title mb-0">Filtros del reporte</h5></div>
                    <div class="card-body">
                        <div class="row g-3 align-items-end">
                            <div class="col-md-3 col-xl-2">
                                <label for="filtroSistema" class="form-label">Sistema de venta</label>
                                <select id="filtroSistema" class="form-select">
                                    <option value="lotobet">Lotobet Real</option>
                                    <option value="lotedom">Lotedom</option>
                                    <option value="delta">Delta</option>
                                    <option value="ds_virtual">DS Virtual</option>
                                </select>
                            </div>
                            <div class="col-md-3 col-xl-2">
                                <label for="fechaInicio" class="form-label">Fecha inicio</label>
                                <input type="date" id="fechaInicio" class="form-control" value="{{ now()->toDateString() }}">
                            </div>
                            <div class="col-md-3 col-xl-2">
                                <label for="fechaFin" class="form-label">Fecha fin</label>
                                <input type="date" id="fechaFin" class="form-control" value="{{ now()->toDateString() }}">
                            </div>
                            <div class="col-md-3 col-xl-2">
                                <label for="filtroCentral" class="form-label">Central</label>
                                <select id="filtroCentral" class="form-select"><option value="">Todas</option></select>
                            </div>
                            <div class="col-md-3 col-xl-2">
                                <label for="filtroGerente" class="form-label">Gerente</label>
                                <select id="filtroGerente" class="form-select"><option value="">Todos</option></select>
                            </div>
                            <div class="col-md-3 col-xl-2">
                                <label for="filtroGrupo" class="form-label">Grupo</label>
                                <select id="filtroGrupo" class="form-select"><option value="">Todos</option></select>
                            </div>
                            <div class="col-12 d-flex justify-content-end gap-2">
                                <button id="btnModoPantalla" class="btn btn-dark">
                                    <i class="ri-fullscreen-line me-1"></i> Modo pantalla
                                </button>
                                <button id="btnAbrirConfiguracion" class="btn btn-light" data-bs-toggle="modal"
                                    data-bs-target="#modalConfiguracion">
                                    <i class="ri-settings-3-line me-1"></i> Configuración
                                </button>
                                <button id="btnConsultarReporte" class="btn btn-primary">
                                    <i class="ri-search-line me-1"></i> Consultar
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row g-3">
                    @foreach ([['totalAgencias', 'Total de agencias', 'ri-store-2-line', 'primary'], ['agenciasConVentas', 'Agencias con ventas', 'ri-line-chart-line', 'success'], ['agenciasSinVentas', 'Agencias sin ventas', 'ri-alert-line', 'danger'], ['porcentajeActividad', 'Porcentaje de actividad', 'ri-percent-line', 'info']] as [$id, $label, $icon, $color])
                        <div class="col-sm-6 col-xl">
                            <div class="card card-animate h-100"><div class="card-body d-flex align-items-center justify-content-between">
                                <div><p class="text-uppercase fw-medium text-muted mb-2">{{ $label }}</p><h3 id="{{ $id }}" class="mb-0">0</h3></div>
                                <div class="avatar-sm"><span class="avatar-title bg-{{ $color }}-subtle text-{{ $color }} rounded-circle fs-4"><i class="{{ $icon }}"></i></span></div>
                            </div></div>
                        </div>
                    @endforeach
                    <div class="col-sm-6 col-xl">
                        <button type="button" id="tarjetaMotivos" class="card card-animate h-100 w-100 border-0 text-start"
                            data-bs-toggle="modal" data-bs-target="#modalTerminalesMotivo">
                            <span class="card-body d-flex align-items-center justify-content-between">
                                <span><span class="text-uppercase fw-medium text-muted mb-2 d-block">Motivos</span><span id="terminalesConMotivo" class="h3 mb-0 d-block">0</span></span>
                                <span class="avatar-sm"><span class="avatar-title bg-warning-subtle text-warning rounded-circle fs-4"><i class="ri-file-list-3-line"></i></span></span>
                            </span>
                        </button>
                    </div>
                </div>

                <div class="row mt-4">
                    <div class="col-xl-4"><div class="card"><div class="card-header"><h5 class="card-title mb-0">Actividad de agencias</h5></div><div class="card-body"><div id="graficoActividad"></div></div></div></div>
                    <div class="col-xl-8"><div class="card"><div class="card-header"><h5 class="card-title mb-0">Actividad por central</h5><small id="textoUmbralCentrales" class="text-muted">Centrales por debajo del 90% de cumplimiento</small></div><div class="card-body"><div id="graficoCentrales"></div></div></div></div>
                </div>

                <div class="card" id="contenedorTablaPrincipal">
                    <div class="card-header"><h5 class="card-title mb-0">Detalle de agencias</h5></div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table id="tablaAgenciasActividad" class="table table-bordered table-striped align-middle nowrap" style="width:100%">
                                <thead><tr><th>Terminal</th><th>Agencia</th><th>Nombre</th><th>Grupo</th><th>Central</th><th>Gerente</th><th>Ventas</th><th>Estado</th><th>Motivo</th><th>Acción</th></tr></thead>
                                <tbody></tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modalMotivo" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog"><div class="modal-content">
            <div class="modal-header"><h5 class="modal-title">Motivo de la falta de actividad</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body">
                <input type="hidden" id="motivoTerminal">
                <div class="mb-3"><label for="motivoTipo" class="form-label">Motivo</label><select id="motivoTipo" class="form-select">
                    <option value="">Seleccione</option><option>Sin internet</option><option>Terminal dañada</option><option>Agencia cerrada</option><option>Falta de balance</option><option>Bloqueo administrativo</option><option>No desea operar</option><option>Cambio de propietario</option><option>Sin contactar</option><option>Otro</option>
                </select></div>
                <div><label for="motivoObservacion" class="form-label">Observación</label><textarea id="motivoObservacion" class="form-control" rows="3" maxlength="1000"></textarea></div>
            </div>
            <div class="modal-footer"><button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancelar</button><button type="button" id="btnGuardarMotivo" class="btn btn-primary">Guardar motivo</button></div>
        </div></div>
    </div>

    <div class="modal fade" id="modalConfiguracion" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog"><div class="modal-content">
            <div class="modal-header"><h5 class="modal-title">Configuración del gráfico</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body">
                <label for="porcentajeMinimo" class="form-label">Mostrar centrales con cumplimiento menor a</label>
                <div class="input-group"><input type="number" id="porcentajeMinimo" class="form-control" min="0" max="100" step="0.01" value="90"><span class="input-group-text">%</span></div>
                <div class="form-text">Ejemplo: con 90%, el gráfico solo mostrará centrales cuyo cumplimiento sea inferior a 90%.</div>
            </div>
            <div class="modal-footer"><button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancelar</button><button type="button" id="btnGuardarConfiguracion" class="btn btn-primary">Guardar configuración</button></div>
        </div></div>
    </div>

    <div class="modal fade" id="modalTerminalesMotivo" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl"><div class="modal-content">
            <div class="modal-header"><h5 class="modal-title">Terminales con motivo registrado</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body">
                <div class="table-responsive">
                    <table id="tablaTerminalesMotivo" class="table table-bordered table-striped align-middle nowrap" style="width:100%">
                        <thead><tr><th>Terminal</th><th>Grupo</th><th>Central</th><th>Gerente de servicio</th><th>Motivo</th></tr></thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer"><button type="button" class="btn btn-light" data-bs-dismiss="modal">Cerrar</button></div>
        </div></div>
    </div>
@endsection

@section('script')
    <script src="{{ asset('libs/apexcharts/apexcharts.min.js') }}"></script>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const urls = {
                data: @json(route('recursos-humanos.agencias-sin-actividad.data')),
                options: @json(route('recursos-humanos.agencias-sin-actividad.options')),
                reason: @json(route('recursos-humanos.agencias-sin-actividad.motivo.store')),
                configuration: @json(route('recursos-humanos.agencias-sin-actividad.configuration')),
                updateConfiguration: @json(route('recursos-humanos.agencias-sin-actividad.configuration.update')),
            };
            const csrfToken = @json(csrf_token());
            let table;
            let reasonsTable;
            let activityChart;
            let centralChart;
            let currentRows = [];
            let currentReport = null;
            let minimumCompliance = 90;

            const escapeHtml = value => String(value ?? '').replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;').replace(/'/g, '&#039;');
            const filters = () => ({sistema: document.getElementById('filtroSistema').value, fecha_inicio: document.getElementById('fechaInicio').value, fecha_fin: document.getElementById('fechaFin').value, central: document.getElementById('filtroCentral').value, gerente: document.getElementById('filtroGerente').value, grupo: document.getElementById('filtroGrupo').value});

            async function request(url, options = {}) {
                const response = await fetch(url, {...options, headers: {'Accept': 'application/json', 'X-CSRF-TOKEN': csrfToken, ...(options.headers || {})}});
                const data = await response.json();
                if (!response.ok) throw new Error(data.message || Object.values(data.errors || {}).flat()[0] || 'No se pudo completar la solicitud.');
                return data;
            }

            function fillSelect(id, values, allLabel) {
                const select = document.getElementById(id);
                select.innerHTML = `<option value="">${allLabel}</option>` + values.map(value => `<option value="${escapeHtml(value)}">${escapeHtml(value)}</option>`).join('');
            }

            async function loadOptions() {
                const url = new URL(urls.options, window.location.origin);
                url.searchParams.set('sistema', document.getElementById('filtroSistema').value);
                const data = await request(url);
                fillSelect('filtroCentral', data.centrales, 'Todas');
                fillSelect('filtroGerente', data.gerentes, 'Todos');
                fillSelect('filtroGrupo', data.grupos, 'Todos');
            }

            async function loadConfiguration() {
                const data = await request(urls.configuration);
                minimumCompliance = Number(data.porcentaje_minimo ?? 90);
                document.getElementById('porcentajeMinimo').value = minimumCompliance;
                document.getElementById('textoUmbralCentrales').textContent = `Centrales por debajo del ${minimumCompliance}% de cumplimiento`;
            }

            function renderTable(rows) {
                if (table) table.destroy();
                const body = document.querySelector('#tablaAgenciasActividad tbody');
                body.innerHTML = rows.map((row, index) => `<tr><td>${escapeHtml(row.terminal)}</td><td>${escapeHtml(row.agencia)}</td><td>${escapeHtml(row.nombre)}</td><td>${escapeHtml(row.grupo)}</td><td>${escapeHtml(row.central)}</td><td>${escapeHtml(row.gerente)}</td><td class="text-end">${Number(row.ventas).toLocaleString('es-DO', {minimumFractionDigits: 2})}</td><td><span class="badge ${row.estado === 'Con ventas' ? 'bg-success' : 'bg-danger'}">${row.estado}</span></td><td>${escapeHtml(row.motivo || '')}</td><td>${row.estado === 'Sin ventas' ? `<button class="btn btn-sm btn-soft-primary btn-motivo" data-index="${index}"><i class="ri-edit-line"></i> Motivo</button>` : ''}</td></tr>`).join('');
                table = $('#tablaAgenciasActividad').DataTable({responsive: true, scrollX: true, pageLength: 25, order: [[7, 'desc']], dom: 'Bfrtip', buttons: ['copy', 'csv', 'excel', 'pdf', 'print']});
            }

            function visibleSummary(data) {
                const visibleCentrals = data.por_central
                    .filter(item => Number(item.cumplimiento) < minimumCompliance);
                const withSales = visibleCentrals.reduce((total, item) => total + Number(item.con_ventas), 0);
                const withoutSales = visibleCentrals.reduce((total, item) => total + Number(item.sin_ventas), 0);
                const total = withSales + withoutSales;

                return {
                    total,
                    con_ventas: withSales,
                    sin_ventas: withoutSales,
                    porcentaje_actividad: total === 0 ? 0 : Number(((withSales / total) * 100).toFixed(2)),
                };
            }

            function visibleRows(data) {
                const visibleCentrals = new Set(data.por_central
                    .filter(item => Number(item.cumplimiento) < minimumCompliance)
                    .map(item => item.central));

                return data.agencias.filter(row => visibleCentrals.has(row.central || 'Sin central'));
            }

            function renderReasons(data) {
                const rows = visibleRows(data).filter(row => row.motivo);
                document.getElementById('terminalesConMotivo').textContent = rows.length.toLocaleString('es-DO');

                if (reasonsTable) reasonsTable.destroy();
                document.querySelector('#tablaTerminalesMotivo tbody').innerHTML = rows.map(row => `<tr><td>${escapeHtml(row.terminal)}</td><td>${escapeHtml(row.grupo)}</td><td>${escapeHtml(row.central)}</td><td>${escapeHtml(row.gerente)}</td><td>${escapeHtml(row.motivo)}</td></tr>`).join('');
                reasonsTable = $('#tablaTerminalesMotivo').DataTable({responsive: true, scrollX: true, pageLength: 10, order: [[2, 'asc']], dom: 'Bfrtip', buttons: ['copy', 'csv', 'excel', 'pdf', 'print']});
            }

            function renderSummary(data) {
                const summary = visibleSummary(data);
                document.getElementById('totalAgencias').textContent = summary.total.toLocaleString('es-DO');
                document.getElementById('agenciasConVentas').textContent = summary.con_ventas.toLocaleString('es-DO');
                document.getElementById('agenciasSinVentas').textContent = summary.sin_ventas.toLocaleString('es-DO');
                document.getElementById('porcentajeActividad').textContent = `${summary.porcentaje_actividad}%`;
            }

            function renderCharts(data) {
                activityChart?.destroy(); centralChart?.destroy();
                const filteredCentrals = data.por_central.filter(item => Number(item.cumplimiento) < minimumCompliance);
                const summary = visibleSummary(data);
                activityChart = new ApexCharts(document.querySelector('#graficoActividad'), {chart: {type: 'donut', height: 310}, series: [summary.con_ventas, summary.sin_ventas], labels: ['Con ventas', 'Sin ventas'], colors: ['#0ab39c', '#f06548'], legend: {position: 'bottom'}, noData: {text: 'Sin datos'}});
                activityChart.render();
                centralChart = new ApexCharts(document.querySelector('#graficoCentrales'), {
                    chart: {type: 'bar', height: 310, stacked: false},
                    series: [
                        {name: 'Con ventas', data: filteredCentrals.map(item => item.con_ventas)},
                        {name: 'Sin ventas', data: filteredCentrals.map(item => item.sin_ventas)},
                    ],
                    colors: ['#0ab39c', '#f06548'],
                    xaxis: {categories: filteredCentrals.map(item => item.central)},
                    yaxis: {title: {text: 'Cantidad de terminales'}},
                    plotOptions: {bar: {horizontal: false, columnWidth: '55%', borderRadius: 3, dataLabels: {position: 'top'}}},
                    dataLabels: {
                        enabled: true,
                        formatter: value => value > 0 ? value : '',
                        offsetY: -18,
                        style: {fontSize: '12px', colors: ['#495057']},
                    },
                    legend: {position: 'top'},
                    noData: {text: `No hay centrales por debajo del ${minimumCompliance}%`},
                });
                centralChart.render();
            }

            async function loadReport() {
                const values = filters();
                const url = new URL(urls.data, window.location.origin);
                Object.entries(values).forEach(([key, value]) => value && url.searchParams.set(key, value));
                Swal.fire({title: 'Generando reporte...', allowOutsideClick: false, didOpen: () => Swal.showLoading()});
                try {
                    const data = await request(url);
                    currentReport = data;
                    currentRows = data.agencias;
                    renderSummary(data); renderReasons(data); renderTable(data.agencias); renderCharts(data); Swal.close();
                } catch (error) { Swal.fire('Error', error.message, 'error'); }
            }

            document.getElementById('filtroSistema').addEventListener('change', loadOptions);
            document.getElementById('btnConsultarReporte').addEventListener('click', loadReport);
            document.getElementById('btnGuardarConfiguracion').addEventListener('click', async () => {
                try {
                    const percentage = Number(document.getElementById('porcentajeMinimo').value);
                    const data = await request(urls.updateConfiguration, {method: 'PUT', headers: {'Content-Type': 'application/json'}, body: JSON.stringify({porcentaje_minimo: percentage})});
                    minimumCompliance = Number(data.porcentaje_minimo);
                    document.getElementById('textoUmbralCentrales').textContent = `Centrales por debajo del ${minimumCompliance}% de cumplimiento`;
                    bootstrap.Modal.getOrCreateInstance(document.getElementById('modalConfiguracion')).hide();
                    if (currentReport) {
                        renderSummary(currentReport);
                        renderReasons(currentReport);
                        renderCharts(currentReport);
                    }
                    Swal.fire('Listo', data.message, 'success');
                } catch (error) { Swal.fire('Error', error.message, 'error'); }
            });
            document.getElementById('tablaAgenciasActividad').addEventListener('click', event => {
                const button = event.target.closest('.btn-motivo'); if (!button) return;
                const row = currentRows[Number(button.dataset.index)];
                document.getElementById('motivoTerminal').value = row.terminal;
                document.getElementById('motivoTipo').value = row.motivo || '';
                document.getElementById('motivoObservacion').value = row.observacion || '';
                bootstrap.Modal.getOrCreateInstance(document.getElementById('modalMotivo')).show();
            });
            document.getElementById('modalTerminalesMotivo').addEventListener('shown.bs.modal', () => {
                reasonsTable?.columns.adjust().responsive?.recalc();
            });
            document.getElementById('btnModoPantalla').addEventListener('click', async event => {
                const tableContainer = document.getElementById('contenedorTablaPrincipal');
                const entering = !tableContainer.classList.contains('d-none');
                tableContainer.classList.toggle('d-none', entering);
                event.currentTarget.innerHTML = entering
                    ? '<i class="ri-fullscreen-exit-line me-1"></i> Salir de modo pantalla'
                    : '<i class="ri-fullscreen-line me-1"></i> Modo pantalla';

                if (entering && !document.fullscreenElement) {
                    await document.documentElement.requestFullscreen().catch(() => {});
                } else if (!entering && document.fullscreenElement) {
                    await document.exitFullscreen().catch(() => {});
                }
            });
            document.getElementById('btnGuardarMotivo').addEventListener('click', async () => {
                try {
                    const data = await request(urls.reason, {method: 'POST', headers: {'Content-Type': 'application/json'}, body: JSON.stringify({sistema: document.getElementById('filtroSistema').value, terminal: document.getElementById('motivoTerminal').value, motivo: document.getElementById('motivoTipo').value, observacion: document.getElementById('motivoObservacion').value})});
                    bootstrap.Modal.getOrCreateInstance(document.getElementById('modalMotivo')).hide();
                    await loadReport(); Swal.fire('Listo', data.message, 'success');
                } catch (error) { Swal.fire('Error', error.message, 'error'); }
            });

            Promise.all([loadOptions(), loadConfiguration()]);
        });
    </script>
@endsection
