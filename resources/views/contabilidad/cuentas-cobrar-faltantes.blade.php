@extends('app')

@section('content')
    <style>
        #detalleCxCModal .modal-dialog,
        #abonosCxCModal .modal-dialog {
            max-width: min(1320px, calc(100vw - 1.5rem));
        }

        #detalleCxCModal .detalle-table-shell,
        #abonosCxCModal .detalle-table-shell {
            overflow: hidden;
        }

        #detalleCxCModal .dataTables_scrollHead table,
        #detalleCxCModal .dataTables_scrollBody table,
        #abonosCxCModal .dataTables_scrollHead table,
        #abonosCxCModal .dataTables_scrollBody table {
            margin-bottom: 0 !important;
        }

        #detalleCxCModal .dataTables_scrollBody thead,
        #abonosCxCModal .dataTables_scrollBody thead {
            visibility: collapse;
        }

        #detalleCxCModal .dataTables_wrapper .dt-buttons,
        #abonosCxCModal .dataTables_wrapper .dt-buttons {
            margin-bottom: .75rem;
        }
    </style>

    <div class="main-content">
        <div class="page-content">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-12">
                        <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                            <h4 class="mb-sm-0">Cuentas por Cobrar Faltantes</h4>
                            <div class="page-title-right">
                                <ol class="breadcrumb m-0">
                                    <li class="breadcrumb-item"><a href="{{ route('inicio.index') }}">Inicio</a></li>
                                    <li class="breadcrumb-item"><a href="{{ route('contabilidad.index') }}">Contabilidad</a></li>
                                    <li class="breadcrumb-item active">Cuentas por Cobrar Faltantes</li>
                                </ol>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row g-3 mb-3">
                    <div class="col-xl-3 col-md-6">
                        <div class="card mb-0">
                            <div class="card-body">
                                <p class="text-uppercase fw-medium text-muted mb-1">Faltantes generados</p>
                                <h4 class="mb-0" id="kpiFaltantes">0.00</h4>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-3 col-md-6">
                        <div class="card mb-0">
                            <div class="card-body">
                                <p class="text-uppercase fw-medium text-muted mb-1">Abonos aplicados</p>
                                <h4 class="mb-0 text-success" id="kpiAbonos">0.00</h4>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-3 col-md-6">
                        <div class="card mb-0">
                            <div class="card-body">
                                <p class="text-uppercase fw-medium text-muted mb-1">Balance pendiente</p>
                                <h4 class="mb-0 text-danger" id="kpiBalance">0.00</h4>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-3 col-md-6">
                        <div class="card mb-0">
                            <div class="card-body">
                                <p class="text-uppercase fw-medium text-muted mb-1">Centros pendientes</p>
                                <h4 class="mb-0" id="kpiCentros">0</h4>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header">
                                <div class="d-flex flex-wrap align-items-end gap-2">
                                    <div>
                                        <label class="form-label mb-1" for="fechaInicio">Desde</label>
                                        <input type="date" class="form-control form-control-sm" id="fechaInicio">
                                    </div>
                                    <div>
                                        <label class="form-label mb-1" for="fechaFin">Hasta</label>
                                        <input type="date" class="form-control form-control-sm" id="fechaFin">
                                    </div>
                                    <div>
                                        <label class="form-label mb-1" for="estado">Estado</label>
                                        <select class="form-select form-select-sm" id="estado">
                                            <option value="pendientes" selected>Pendientes</option>
                                            <option value="todos">Todos</option>
                                            <option value="saldados">Saldados</option>
                                            <option value="sobregiro">Sobregiro</option>
                                        </select>
                                    </div>
                                    <div class="flex-grow-1" style="min-width: 220px;">
                                        <label class="form-label mb-1" for="buscar">Buscar</label>
                                        <input type="text" class="form-control form-control-sm" id="buscar" placeholder="Centro costo, empleado, agencia">
                                    </div>
                                    <button type="button" class="btn btn-primary btn-sm" id="btnConsultar">
                                        Consultar
                                    </button>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
                                    <div class="text-muted small">
                                        Cuenta de abonos: <span class="fw-semibold">{{ $cuentaFaltantes }}</span>
                                    </div>
                                    <div class="text-muted small" id="periodoTexto"></div>
                                </div>

                                <div class="table-responsive">
                                    <table id="tablaCuentasCobrar" class="table table-bordered table-striped align-middle nowrap" style="width:100%">
                                        <thead>
                                            <tr>
                                                <th>Accion</th>
                                                <th>Id CC Empleado</th>
                                                <th>Agencias faltantes</th>
                                                <th class="text-end">Cant. faltantes</th>
                                                <th class="text-end">Total faltantes</th>
                                                <th class="text-end">Credito</th>
                                                <th class="text-end">Balance</th>
                                                <th class="text-end">% abonado</th>
                                                <th>Estado</th>
                                                <th>Ult. abono</th>
                                            </tr>
                                        </thead>
                                        <tbody></tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="detalleCxCModal" tabindex="-1" aria-labelledby="detalleCxCModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <div>
                        <h5 class="modal-title" id="detalleCxCModalLabel">Detalle CxC Faltantes</h5>
                        <div class="text-muted small" id="detalleCxCSubtitulo"></div>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3 mb-3">
                        <div class="col-md-3">
                            <div class="border rounded p-2">
                                <div class="text-muted small text-uppercase">Faltantes</div>
                                <div class="fw-semibold" id="detalleTotalFaltantes">0.00</div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="border rounded p-2">
                                <div class="text-muted small text-uppercase">Abonos</div>
                                <div class="fw-semibold text-success" id="detalleTotalAbonos">0.00</div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="border rounded p-2">
                                <div class="text-muted small text-uppercase">Restante</div>
                                <div class="fw-semibold text-danger" id="detalleBalance">0.00</div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="border rounded p-2">
                                <div class="text-muted small text-uppercase">Agencias</div>
                                <div class="fw-semibold" id="detalleAgencias">0</div>
                            </div>
                        </div>
                    </div>

                    <div class="detalle-table-shell">
                        <table id="tablaDetalleCxC" class="table table-bordered table-striped align-middle nowrap" style="width:100%">
                            <thead>
                                <tr>
                                    <th>Agencia</th>
                                    <th>Empleado</th>
                                    <th class="text-end">Cant. faltantes</th>
                                    <th class="text-end">Total faltantes</th>
                                    <th class="text-end">Credito</th>
                                    <th class="text-end">Abono neto</th>
                                    <th class="text-end">Restante</th>
                                    <th class="text-end">% abonado</th>
                                    <th>Estado</th>
                                    <th>Ult. faltante</th>
                                    <th>Ult. abono</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="abonosCxCModal" tabindex="-1" aria-labelledby="abonosCxCModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <div>
                        <h5 class="modal-title" id="abonosCxCModalLabel">Movimientos de abonos</h5>
                        <div class="text-muted small" id="abonosCxCSubtitulo"></div>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3 mb-3">
                        <div class="col-md-3">
                            <div class="border rounded p-2">
                                <div class="text-muted small text-uppercase">Movimientos</div>
                                <div class="fw-semibold" id="abonosTotalMovimientos">0</div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="border rounded p-2">
                                <div class="text-muted small text-uppercase">Debito</div>
                                <div class="fw-semibold text-warning" id="abonosTotalDebito">0.00</div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="border rounded p-2">
                                <div class="text-muted small text-uppercase">Credito</div>
                                <div class="fw-semibold text-success" id="abonosTotalCredito">0.00</div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="border rounded p-2">
                                <div class="text-muted small text-uppercase">Abono neto</div>
                                <div class="fw-semibold" id="abonosTotalNeto">0.00</div>
                            </div>
                        </div>
                    </div>

                    <div class="detalle-table-shell">
                        <table id="tablaAbonosCxC" class="table table-bordered table-striped align-middle nowrap" style="width:100%">
                            <thead>
                                <tr>
                                    <th>Fecha</th>
                                    <th>Agencia</th>
                                    <th>No. asiento</th>
                                    <th>Referencia</th>
                                    <th>No. ref</th>
                                    <th class="text-end">Debito</th>
                                    <th class="text-end">Credito</th>
                                    <th class="text-end">Abono neto</th>
                                    <th>Modulo</th>
                                    <th>Creado por</th>
                                    <th>Fecha grabado</th>
                                    <th>Descripcion</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('script')
    <script>
        const dataUrl = "{{ route('contabilidad.cuentas-cobrar-faltantes.data') }}";
        const detalleUrl = "{{ route('contabilidad.cuentas-cobrar-faltantes.detalle') }}";
        const abonosUrl = "{{ route('contabilidad.cuentas-cobrar-faltantes.abonos') }}";

        document.addEventListener('DOMContentLoaded', function () {
            inicializarFechas();
            document.getElementById('btnConsultar').addEventListener('click', cargarReporte);
            document.getElementById('buscar').addEventListener('keydown', function (event) {
                if (event.key === 'Enter') {
                    event.preventDefault();
                    cargarReporte();
                }
            });
            renderTabla([]);
        });

        function inicializarFechas() {
            const hoy = new Date();
            const inicio = new Date(hoy.getFullYear(), hoy.getMonth(), 1);
            document.getElementById('fechaInicio').value = formatoInput(inicio);
            document.getElementById('fechaFin').value = formatoInput(hoy);
        }

        function formatoInput(fecha) {
            return `${fecha.getFullYear()}-${String(fecha.getMonth() + 1).padStart(2, '0')}-${String(fecha.getDate()).padStart(2, '0')}`;
        }

        async function cargarReporte() {
            const params = new URLSearchParams({
                fecha_inicio: document.getElementById('fechaInicio').value,
                fecha_fin: document.getElementById('fechaFin').value,
                estado: document.getElementById('estado').value,
                buscar: document.getElementById('buscar').value.trim(),
                limit: 1000
            });

            const boton = document.getElementById('btnConsultar');
            const textoOriginal = boton.innerText;
            boton.disabled = true;
            boton.innerText = 'Consultando...';

            try {
                const response = await fetch(`${dataUrl}?${params.toString()}`, {
                    headers: { 'Accept': 'application/json' }
                });
                const payload = await response.json();

                if (!response.ok) {
                    throw new Error(payload.message || 'No se pudo consultar el reporte.');
                }

                renderResumen(payload.summary || {});
                renderTabla(payload.data || []);
                document.getElementById('periodoTexto').textContent = `${payload.filters.fecha_inicio} a ${payload.filters.fecha_fin}`;
            } catch (error) {
                if (typeof Swal !== 'undefined') {
                    Swal.fire('Error', error.message || 'No se pudo consultar el reporte.', 'error');
                } else {
                    alert(error.message || 'No se pudo consultar el reporte.');
                }
            } finally {
                boton.disabled = false;
                boton.innerText = textoOriginal;
            }
        }

        function renderResumen(summary) {
            document.getElementById('kpiFaltantes').textContent = formatoMonto(summary.total_faltantes);
            document.getElementById('kpiAbonos').textContent = formatoMonto(summary.total_abonos);
            document.getElementById('kpiBalance').textContent = formatoMonto(summary.balance_pendiente);
            document.getElementById('kpiCentros').textContent = Number(summary.centros_pendientes || 0).toLocaleString('es-DO');
        }

        function renderTabla(items) {
            const tbody = document.querySelector('#tablaCuentasCobrar tbody');
            tbody.innerHTML = '';

            if ($.fn.DataTable.isDataTable('#tablaCuentasCobrar')) {
                $('#tablaCuentasCobrar').DataTable().clear().destroy();
            }

            items.forEach(item => {
                const row = document.createElement('tr');
                row.innerHTML = `
                    <td>
                        <div class="d-flex gap-1">
                            <button type="button" class="btn btn-sm btn-info" onclick="verDetalleCxC('${escapeJs(item.id_cc_empleado)}')">
                                Ver
                            </button>
                            <button type="button" class="btn btn-sm btn-success" onclick="verAbonosCxC('${escapeJs(item.id_cc_empleado)}')">
                                Abono
                            </button>
                        </div>
                    </td>
                    <td>${escapeHtml(item.id_cc_empleado)}</td>
                    <td>
                        <div>${escapeHtml(item.agencias_faltantes || '-')}</div>
                        <div class="text-muted small">Abonos en agencias: ${escapeHtml(item.agencias_abonos || '-')}</div>
                    </td>
                    <td class="text-end">${Number(item.cantidad_faltantes || 0).toLocaleString('es-DO')}</td>
                    <td class="text-end">${formatoMonto(item.total_faltantes)}</td>
                    <td class="text-end text-success">${formatoMonto(item.total_credito)}</td>
                    <td class="text-end ${Number(item.balance_pendiente || 0) > 0 ? 'text-danger' : 'text-success'} fw-semibold">${formatoMonto(item.balance_pendiente)}</td>
                    <td class="text-end">${Number(item.porcentaje_abonado || 0).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}%</td>
                    <td><span class="badge ${badgeClass(item.estado)}">${escapeHtml(item.estado)}</span></td>
                    <td>${escapeHtml(item.ultima_fecha_abono || '-')}</td>
                `;
                tbody.appendChild(row);
            });

            $('#tablaCuentasCobrar').DataTable({
                destroy: true,
                responsive: false,
                scrollX: true,
                pageLength: 25,
                order: [[6, 'desc']],
                columnDefs: [
                    { targets: [3, 4, 5, 6, 7], className: 'text-end' },
                    { targets: '_all', className: 'align-middle' }
                ],
                dom: 'Bfrtip',
                buttons: ['copy', 'csv', 'excel', 'pdf', 'print']
            });
        }

        async function verDetalleCxC(idCcEmpleado) {
            const params = new URLSearchParams({
                id_cc_empleado: idCcEmpleado,
                fecha_inicio: document.getElementById('fechaInicio').value,
                fecha_fin: document.getElementById('fechaFin').value,
            });

            try {
                const response = await fetch(`${detalleUrl}?${params.toString()}`, {
                    headers: { 'Accept': 'application/json' }
                });
                const payload = await response.json();

                if (!response.ok) {
                    throw new Error(payload.message || 'No se pudo consultar el detalle.');
                }

                document.getElementById('detalleCxCModalLabel').textContent = `Detalle CxC - CC ${idCcEmpleado}`;
                document.getElementById('detalleCxCSubtitulo').textContent = `${payload.filters.fecha_inicio} a ${payload.filters.fecha_fin}`;
                renderDetalleResumen(payload.summary || {});
                renderDetalleTabla(payload.data || []);

                const modalElement = document.getElementById('detalleCxCModal');
                const modal = bootstrap.Modal.getOrCreateInstance(modalElement);
                modalElement.addEventListener('shown.bs.modal', ajustarTablaDetalleModal, { once: true });
                modal.show();
            } catch (error) {
                if (typeof Swal !== 'undefined') {
                    Swal.fire('Error', error.message || 'No se pudo consultar el detalle.', 'error');
                } else {
                    alert(error.message || 'No se pudo consultar el detalle.');
                }
            }
        }

        function renderDetalleResumen(summary) {
            document.getElementById('detalleTotalFaltantes').textContent = formatoMonto(summary.total_faltantes);
            document.getElementById('detalleTotalAbonos').textContent = formatoMonto(summary.total_credito);
            document.getElementById('detalleBalance').textContent = formatoMonto(summary.balance_pendiente);
            document.getElementById('detalleAgencias').textContent = Number(summary.agencias || 0).toLocaleString('es-DO');
        }

        function renderDetalleTabla(items) {
            const tbody = document.querySelector('#tablaDetalleCxC tbody');
            tbody.innerHTML = '';

            if ($.fn.DataTable.isDataTable('#tablaDetalleCxC')) {
                $('#tablaDetalleCxC').DataTable().clear().destroy();
            }

            items.forEach(item => {
                const row = document.createElement('tr');
                row.innerHTML = `
                    <td>${escapeHtml(item.agencia_id)}</td>
                    <td>
                        <div class="fw-semibold">${escapeHtml(item.nombre_empleado)}</div>
                        <div class="text-muted small">Empleado: ${escapeHtml(item.empleadoid || '-')} / Empresa: ${escapeHtml(item.companyid || '-')}</div>
                    </td>
                    <td class="text-end">${Number(item.cantidad_faltantes || 0).toLocaleString('es-DO')}</td>
                    <td class="text-end">${formatoMonto(item.total_faltantes)}</td>
                    <td class="text-end text-success">${formatoMonto(item.total_credito)}</td>
                    <td class="text-end">${formatoMonto(item.total_abonos)}</td>
                    <td class="text-end ${Number(item.balance_pendiente || 0) > 0 ? 'text-danger' : 'text-success'} fw-semibold">${formatoMonto(item.balance_pendiente)}</td>
                    <td class="text-end">${Number(item.porcentaje_abonado || 0).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}%</td>
                    <td><span class="badge ${badgeClass(item.estado)}">${escapeHtml(item.estado)}</span></td>
                    <td>${escapeHtml(item.ultima_fecha_faltante || '-')}</td>
                    <td>${escapeHtml(item.ultima_fecha_abono || '-')}</td>
                `;
                tbody.appendChild(row);
            });

            const detalleTable = $('#tablaDetalleCxC').DataTable({
                destroy: true,
                responsive: false,
                scrollX: true,
                scrollY: '45vh',
                scrollCollapse: true,
                pageLength: 10,
                order: [[6, 'desc']],
                columnDefs: [
                    { targets: [2, 3, 4, 5, 6, 7], className: 'text-end' },
                    { targets: '_all', className: 'align-middle' }
                ],
                dom: 'Bfrtip',
                buttons: ['copy', 'csv', 'excel', 'pdf', 'print']
            });

            return detalleTable;
        }

        function ajustarTablaDetalleModal() {
            if ($.fn.DataTable.isDataTable('#tablaDetalleCxC')) {
                $('#tablaDetalleCxC').DataTable().columns.adjust().draw(false);
            }
        }

        async function verAbonosCxC(idCcEmpleado) {
            const params = new URLSearchParams({
                id_cc_empleado: idCcEmpleado,
                fecha_inicio: document.getElementById('fechaInicio').value,
                fecha_fin: document.getElementById('fechaFin').value,
            });

            try {
                const response = await fetch(`${abonosUrl}?${params.toString()}`, {
                    headers: { 'Accept': 'application/json' }
                });
                const payload = await response.json();

                if (!response.ok) {
                    throw new Error(payload.message || 'No se pudo consultar los abonos.');
                }

                document.getElementById('abonosCxCModalLabel').textContent = `Abonos - CC ${idCcEmpleado}`;
                document.getElementById('abonosCxCSubtitulo').textContent = `${payload.filters.fecha_inicio} a ${payload.filters.fecha_fin} / Cuenta ${payload.filters.cuenta_abonos}`;
                renderAbonosResumen(payload.summary || {});
                renderAbonosTabla(payload.data || []);

                const modalElement = document.getElementById('abonosCxCModal');
                const modal = bootstrap.Modal.getOrCreateInstance(modalElement);
                modalElement.addEventListener('shown.bs.modal', ajustarTablaAbonosModal, { once: true });
                modal.show();
            } catch (error) {
                if (typeof Swal !== 'undefined') {
                    Swal.fire('Error', error.message || 'No se pudo consultar los abonos.', 'error');
                } else {
                    alert(error.message || 'No se pudo consultar los abonos.');
                }
            }
        }

        function renderAbonosResumen(summary) {
            document.getElementById('abonosTotalMovimientos').textContent = Number(summary.movimientos || 0).toLocaleString('es-DO');
            document.getElementById('abonosTotalDebito').textContent = formatoMonto(summary.total_debito);
            document.getElementById('abonosTotalCredito').textContent = formatoMonto(summary.total_credito);
            document.getElementById('abonosTotalNeto').textContent = formatoMonto(summary.total_abonos);
        }

        function renderAbonosTabla(items) {
            const tbody = document.querySelector('#tablaAbonosCxC tbody');
            tbody.innerHTML = '';

            if ($.fn.DataTable.isDataTable('#tablaAbonosCxC')) {
                $('#tablaAbonosCxC').DataTable().clear().destroy();
            }

            items.forEach(item => {
                const row = document.createElement('tr');
                row.innerHTML = `
                    <td>${escapeHtml(item.fecha || '-')}</td>
                    <td>${escapeHtml(item.agencia_id || '-')}</td>
                    <td>${escapeHtml(item.no_asiento || '-')}</td>
                    <td>${escapeHtml(item.ref || '-')}</td>
                    <td>${escapeHtml(item.no_ref || '-')}</td>
                    <td class="text-end text-warning">${formatoMonto(item.debito)}</td>
                    <td class="text-end text-success">${formatoMonto(item.credito)}</td>
                    <td class="text-end fw-semibold ${Number(item.abono_neto || 0) < 0 ? 'text-danger' : 'text-success'}">${formatoMonto(item.abono_neto)}</td>
                    <td>${escapeHtml(item.modulo || '-')}</td>
                    <td>${escapeHtml(item.creado_por || '-')}</td>
                    <td>${escapeHtml(item.fecha_grabado || '-')}</td>
                    <td>${escapeHtml(item.descripcion || '-')}</td>
                `;
                tbody.appendChild(row);
            });

            const abonosTable = $('#tablaAbonosCxC').DataTable({
                destroy: true,
                responsive: false,
                scrollX: true,
                scrollY: '45vh',
                scrollCollapse: true,
                pageLength: 10,
                order: [[0, 'asc'], [2, 'asc']],
                columnDefs: [
                    { targets: [5, 6, 7], className: 'text-end' },
                    { targets: '_all', className: 'align-middle' }
                ],
                dom: 'Bfrtip',
                buttons: ['copy', 'csv', 'excel', 'pdf', 'print']
            });

            return abonosTable;
        }

        function ajustarTablaAbonosModal() {
            if ($.fn.DataTable.isDataTable('#tablaAbonosCxC')) {
                $('#tablaAbonosCxC').DataTable().columns.adjust().draw(false);
            }
        }

        function badgeClass(estado) {
            if (estado === 'Saldado') return 'bg-success';
            if (estado === 'Sobregiro') return 'bg-warning text-dark';
            return 'bg-danger';
        }

        function formatoMonto(value) {
            return Number(value || 0).toLocaleString('en-US', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2,
            });
        }

        function escapeHtml(value) {
            return String(value ?? '')
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#039;');
        }

        function escapeJs(value) {
            return String(value ?? '').replace(/\\/g, '\\\\').replace(/'/g, "\\'");
        }
    </script>
@endsection
