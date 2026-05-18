@extends('app')

@section('content')
    <style>
        .electricidad-casos-page {
            font-size: 1rem;
        }

        .electricidad-stat-card {
            min-height: 118px;
            background: #fff;
            border: 0;
            border-radius: 1rem;
            box-shadow: 0 12px 30px rgba(15, 23, 42, 0.08);
            transition: transform 0.22s ease, box-shadow 0.22s ease;
        }

        .electricidad-stat-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 18px 38px rgba(15, 23, 42, 0.12);
        }

        .electricidad-stat-card .card-body {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 1.35rem 1.5rem;
        }

        .electricidad-stat-value {
            font-size: 2.45rem;
            font-weight: 700;
            line-height: 1;
            letter-spacing: -0.04em;
            color: #1f2b3d;
        }

        .electricidad-stat-label {
            display: block;
            margin-top: 0.55rem;
            font-size: 0.95rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            color: #6c757d;
        }

        .electricidad-stat-icon {
            width: 42px;
            height: 42px;
            border-radius: 14px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 1.1rem;
        }

        .electricidad-status-badge {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 118px;
            padding: 0.42rem 0.78rem;
            border-radius: 999px;
            font-size: 0.86rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.04em;
        }

        .electricidad-status-pendiente {
            background: #fff3cd;
            color: #9a6700;
        }

        .electricidad-status-en_gestion {
            background: #dbeafe;
            color: #1d4ed8;
        }

        .electricidad-status-resuelta {
            background: #dcfce7;
            color: #15803d;
        }

        .electricidad-status-cancelada {
            background: #f1f5f9;
            color: #475569;
        }

        .electricidad-toolbar {
            display: flex;
            align-items: center;
            justify-content: flex-end;
            gap: 0.75rem;
            flex-wrap: nowrap;
        }

        .electricidad-toolbar .btn,
        .electricidad-toolbar .form-control {
            min-height: 42px;
            flex: 0 0 auto;
        }

        .electricidad-toolbar .btn {
            padding: 0.62rem 1rem;
            font-size: 0.98rem;
            font-weight: 600;
            white-space: nowrap;
        }

        .electricidad-toolbar .form-control {
            width: 170px;
            min-width: 170px;
            font-size: 0.95rem;
        }

        @media (max-width: 991.98px) {
            .electricidad-toolbar {
                flex-wrap: wrap;
                justify-content: flex-start;
            }

            .electricidad-toolbar .btn,
            .electricidad-toolbar .form-control {
                width: 100%;
                min-width: 0;
            }
        }

        .excel-head {
            background-color: #9ea3a8;
            color: #111;
            font-weight: 700;
            text-transform: uppercase;
        }

        .excel-band {
            background: #fff200;
            color: #111;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.4px;
            text-align: center;
            padding: 0.45rem;
            border: 1px solid #dee2e6;
            margin-bottom: 0.75rem;
        }
    </style>
    <div class="main-content electricidad-casos-page">
        <div class="page-content">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-12">
                        <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                            <h4 class="mb-sm-0">Electricidad</h4>
                            <div class="page-title-right">
                                <ol class="breadcrumb m-0">
                                    <li class="breadcrumb-item"><a href="{{ route('inicio.index') }}">Inicio</a></li>
                                    <li class="breadcrumb-item"><a href="{{ route('contabilidad.index') }}">Contabilidad</a></li>
                                    <li class="breadcrumb-item active">Electricidad</li>
                                </ol>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-xl-3 col-md-6">
                        <div class="card electricidad-stat-card" title="Total de casos">
                            <div class="card-body">
                                <div>
                                    <div class="electricidad-stat-value" id="stat-total-casos">{{ $stats['total'] ?? 0 }}</div>
                                    <span class="electricidad-stat-label">Total de casos</span>
                                </div>
                                <span class="electricidad-stat-icon bg-primary-subtle text-primary">
                                    <i class="ri-file-list-3-line"></i>
                                </span>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-3 col-md-6">
                        <div class="card electricidad-stat-card" title="Pendientes">
                            <div class="card-body">
                                <div>
                                    <div class="electricidad-stat-value" id="stat-pendientes">{{ $stats['pendientes'] ?? 0 }}</div>
                                    <span class="electricidad-stat-label">Pendientes</span>
                                </div>
                                <span class="electricidad-stat-icon bg-warning-subtle text-warning">
                                    <i class="ri-alarm-warning-line"></i>
                                </span>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-3 col-md-6">
                        <div class="card electricidad-stat-card" title="En gestion">
                            <div class="card-body">
                                <div>
                                    <div class="electricidad-stat-value" id="stat-en-gestion">{{ $stats['en_gestion'] ?? 0 }}</div>
                                    <span class="electricidad-stat-label">En gestion</span>
                                </div>
                                <span class="electricidad-stat-icon bg-info-subtle text-info">
                                    <i class="ri-radar-line"></i>
                                </span>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-3 col-md-6">
                        <div class="card electricidad-stat-card" title="Resueltas">
                            <div class="card-body">
                                <div>
                                    <div class="electricidad-stat-value" id="stat-resueltas">{{ $stats['resueltas'] ?? 0 }}</div>
                                    <span class="electricidad-stat-label">Resueltas</span>
                                </div>
                                <span class="electricidad-stat-icon bg-success-subtle text-success">
                                    <i class="ri-checkbox-circle-line"></i>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-12 mb-3">
                        <div class="card">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h5 class="card-title mb-0">Seguimiento por dia</h5>
                                <div class="electricidad-toolbar">
                                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalSeguimientoDia">
                                        Captura de evento
                                    </button>
                                    <input type="date" class="form-control" id="filtroSegDesde" placeholder="Desde">
                                    <input type="date" class="form-control" id="filtroSegHasta" placeholder="Hasta">
                                    <button type="button" class="btn btn-info" id="btnFiltrarSeguimiento">Filtrar</button>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="row mb-3">
                                    <div class="col-12 col-md-5">
                                        <label class="form-label">Buscar por codigo o nombre de agencia</label>
                                        <input type="text" class="form-control" id="buscarSegAgencia" placeholder="Ej: AG001 o Los Mina">
                                    </div>
                                </div>

                                <div class="table-responsive">
                                    <table id="tablaSeguimientoDia" class="table table-bordered table-striped align-middle mb-0" style="width:100%">
                                        <thead>
                                            <tr class="excel-head">
                                                <th>Fecha de solicitud</th>
                                                <th>Distribuidoras</th>
                                                <th>NIC</th>
                                                <th>Agencia</th>
                                                <th>Ruta</th>
                                                <th>Estatus</th>
                                                <th>Observaciones</th>
                                            </tr>
                                        </thead>
                                        <tbody></tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-12">
                        <div class="card">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h5 class="card-title mb-0">Reporte de Averias por dia</h5>
                                <div class="electricidad-toolbar">
                                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalAveriasDia">
                                        Captura de evento
                                    </button>
                                    <input type="date" class="form-control" id="filtroAveDesde" placeholder="Desde">
                                    <input type="date" class="form-control" id="filtroAveHasta" placeholder="Hasta">
                                    <button type="button" class="btn btn-info" id="btnFiltrarAverias">Filtrar</button>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="excel-band">REPORTES DE AVERIA X DIAS</div>
                                <div class="row mb-3">
                                    <div class="col-12 col-md-5">
                                        <label class="form-label">Buscar por codigo o nombre de agencia</label>
                                        <input type="text" class="form-control" id="buscarAveAgencia" placeholder="Ej: AG001 o Los Mina">
                                    </div>
                                </div>

                                <div class="table-responsive">
                                    <table id="tablaAveriasDia" class="table table-bordered table-striped align-middle mb-0" style="width:100%">
                                        <thead>
                                            <tr class="excel-head">
                                                <th>Fecha del reporte</th>
                                                <th>Reporte</th>
                                                <th>Distribuidoras</th>
                                                <th>NIC</th>
                                                <th>Agencia</th>
                                                <th>Ruta</th>
                                                <th>Coordinadores</th>
                                                <th>Agente de venta AM</th>
                                                <th>Agente de venta PM</th>
                                                <th>Estatus</th>
                                                <th>Observaciones</th>
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

    <div class="modal fade" id="modalSeguimientoDia" tabindex="-1" aria-labelledby="modalSeguimientoDiaLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalSeguimientoDiaLabel">Captura de evento - Seguimiento por dia</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="formSeguimientoDia">
                    <div class="modal-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Fecha de solicitud *</label>
                                <input type="date" class="form-control" id="segFechaSolicitud" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Distribuidora *</label>
                                <input type="text" class="form-control" id="segDistribuidora" maxlength="120" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">NIC *</label>
                                <input type="text" class="form-control" id="segNic" maxlength="80" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Agencia *</label>
                                <input type="text" class="form-control" id="segAgencia" maxlength="150" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Ruta *</label>
                                <input type="text" class="form-control" id="segRuta" maxlength="150" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Estatus *</label>
                                <select class="form-select" id="segEstatus" required>
                                    <option value="pendiente">Pendiente</option>
                                    <option value="en_gestion">En gestion</option>
                                    <option value="resuelta">Resuelta</option>
                                    <option value="cancelada">Cancelada</option>
                                </select>
                            </div>
                            <div class="col-md-8">
                                <label class="form-label">Observaciones</label>
                                <input type="text" class="form-control" id="segObservaciones" maxlength="1000">
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cerrar</button>
                        <button type="submit" class="btn btn-primary">Guardar seguimiento</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modalAveriasDia" tabindex="-1" aria-labelledby="modalAveriasDiaLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalAveriasDiaLabel">Captura de evento - Reporte de averias por dia</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="formAveriasDia">
                    <div class="modal-body">
                        <div class="row g-3">
                            <div class="col-md-3">
                                <label class="form-label">Fecha del reporte *</label>
                                <input type="date" class="form-control" id="aveFechaReporte" required>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Reporte *</label>
                                <input type="text" class="form-control" id="aveReporte" maxlength="120" required>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Distribuidoras *</label>
                                <input type="text" class="form-control" id="aveDistribuidora" maxlength="120" required>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">NIC *</label>
                                <input type="text" class="form-control" id="aveNic" maxlength="80" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Agencia *</label>
                                <input type="text" class="form-control" id="aveAgencia" maxlength="150" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Ruta *</label>
                                <input type="text" class="form-control" id="aveRuta" maxlength="150" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Estatus *</label>
                                <select class="form-select" id="aveEstatus" required>
                                    <option value="pendiente">Pendiente</option>
                                    <option value="en_gestion">En gestion</option>
                                    <option value="resuelta">Resuelta</option>
                                    <option value="cancelada">Cancelada</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Coordinadores</label>
                                <input type="text" class="form-control" id="aveCoordinadores" maxlength="180">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Agente de venta AM</label>
                                <input type="text" class="form-control" id="aveAgenteAm" maxlength="180">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Agente de venta PM</label>
                                <input type="text" class="form-control" id="aveAgentePm" maxlength="180">
                            </div>
                            <div class="col-12">
                                <label class="form-label">Observaciones</label>
                                <input type="text" class="form-control" id="aveObservaciones" maxlength="1000">
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cerrar</button>
                        <button type="submit" class="btn btn-primary">Guardar averia</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@section('script')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const csrfToken = '{{ csrf_token() }}';

            const segDesde = document.getElementById('filtroSegDesde');
            const segHasta = document.getElementById('filtroSegHasta');
            const btnFiltrarSeguimiento = document.getElementById('btnFiltrarSeguimiento');
            const formSeguimiento = document.getElementById('formSeguimientoDia');
            const tablaSeguimientoBody = document.querySelector('#tablaSeguimientoDia tbody');
            const buscarSegAgencia = document.getElementById('buscarSegAgencia');
            const modalSeguimientoDia = document.getElementById('modalSeguimientoDia');

            const aveDesde = document.getElementById('filtroAveDesde');
            const aveHasta = document.getElementById('filtroAveHasta');
            const btnFiltrarAverias = document.getElementById('btnFiltrarAverias');
            const formAverias = document.getElementById('formAveriasDia');
            const tablaAveriasBody = document.querySelector('#tablaAveriasDia tbody');
            const buscarAveAgencia = document.getElementById('buscarAveAgencia');
            const modalAveriasDia = document.getElementById('modalAveriasDia');

            let dtSeguimiento = null;
            let dtAverias = null;
            let resumenSeguimiento = {
                total: 0,
                pendientes: 0,
                en_gestion: 0,
                resueltas: 0,
                canceladas: 0,
            };
            let resumenAverias = {
                total: 0,
                pendientes: 0,
                en_gestion: 0,
                resueltas: 0,
                canceladas: 0,
            };

            function normalizeResumen(resumen) {
                return {
                    total: Number(resumen?.total || 0),
                    pendientes: Number(resumen?.pendientes || 0),
                    en_gestion: Number(resumen?.en_gestion || 0),
                    resueltas: Number(resumen?.resueltas || 0),
                    canceladas: Number(resumen?.canceladas || 0),
                };
            }

            function updateStatusCards() {
                const total = resumenSeguimiento.total + resumenAverias.total;
                const pendientes = resumenSeguimiento.pendientes + resumenAverias.pendientes;
                const enGestion = resumenSeguimiento.en_gestion + resumenAverias.en_gestion;
                const resueltas = resumenSeguimiento.resueltas + resumenAverias.resueltas;

                document.getElementById('stat-total-casos').textContent = total;
                document.getElementById('stat-pendientes').textContent = pendientes;
                document.getElementById('stat-en-gestion').textContent = enGestion;
                document.getElementById('stat-resueltas').textContent = resueltas;
            }

            function estatusLabel(estatus) {
                const labels = {
                    pendiente: 'Pendiente',
                    en_gestion: 'En gestion',
                    resuelta: 'Resuelta',
                    cancelada: 'Cancelada',
                };

                return labels[estatus] || 'Pendiente';
            }

            function estatusBadge(estatus) {
                const current = estatus || 'pendiente';
                return `<span class="electricidad-status-badge electricidad-status-${current}">${estatusLabel(current)}</span>`;
            }

            function estatusOptions() {
                return {
                    pendiente: 'Pendiente',
                    en_gestion: 'En gestion',
                    resuelta: 'Resuelta',
                    cancelada: 'Cancelada',
                };
            }

            function estatusEditableCell(tipo, id, estatus) {
                const current = estatus || 'pendiente';
                return `
                    <button
                        type="button"
                        class="btn btn-link p-0 border-0 text-decoration-none js-editar-estatus"
                        data-tipo="${tipo}"
                        data-id="${id}"
                        data-estatus="${current}"
                        title="Haz clic para cambiar estatus"
                    >
                        ${estatusBadge(current)}
                    </button>
                `;
            }

            async function actualizarEstatusOrden(tipo, id, estatus) {
                const endpoint = tipo === 'seguimiento'
                    ? `/contabilidad/electricidad/seguimiento-dia/${id}/estatus`
                    : `/contabilidad/electricidad/averias-dia/${id}/estatus`;

                const response = await fetch(endpoint, {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    body: JSON.stringify({ estatus }),
                });

                const json = await response.json().catch(function () { return {}; });

                if (!response.ok) {
                    const firstValidation = json?.errors ? Object.values(json.errors)[0]?.[0] : '';
                    throw new Error(firstValidation || json.message || 'No se pudo actualizar el estatus.');
                }

                return json;
            }

            async function seleccionarYActualizarEstatus(tipo, id, estatusActual, triggerElement) {
                const selected = await Swal.fire({
                    title: 'Cambiar estatus',
                    input: 'select',
                    inputOptions: estatusOptions(),
                    inputValue: estatusActual || 'pendiente',
                    showCancelButton: true,
                    confirmButtonText: 'Guardar',
                    cancelButtonText: 'Cancelar',
                });

                if (!selected.isConfirmed || !selected.value || selected.value === estatusActual) {
                    return;
                }

                await actualizarEstatusOrden(tipo, id, selected.value);

                if (triggerElement) {
                    triggerElement.setAttribute('data-estatus', selected.value);
                    triggerElement.innerHTML = estatusBadge(selected.value);
                }

                if (tipo === 'seguimiento') {
                    await cargarSeguimientoDia();
                } else {
                    await cargarAveriasDia();
                }

                Swal.fire({
                    icon: 'success',
                    title: 'Estatus actualizado',
                    timer: 1100,
                    showConfirmButton: false,
                });
            }

            function closeModal(modalElement) {
                if (!modalElement || !window.bootstrap?.Modal) {
                    return;
                }

                const instance = window.bootstrap.Modal.getInstance(modalElement) || new window.bootstrap.Modal(modalElement);
                instance.hide();
            }

            function initDataTable(selector) {
                if ($.fn.DataTable.isDataTable(selector)) {
                    $(selector).DataTable().destroy();
                }

                return $(selector).DataTable({
                    responsive: true,
                    scrollX: true,
                    order: [[0, 'desc']],
                    pageLength: 10,
                    lengthMenu: [[10, 25, 50, 100], [10, 25, 50, 100]],
                    dom: 'Bfrtip',
                    buttons: ['copy', 'csv', 'excel', 'pdf', 'print'],
                    language: {
                        lengthMenu: 'Mostrar _MENU_ registros por pagina',
                        info: 'Mostrando _START_ a _END_ de _TOTAL_ registros',
                        infoEmpty: 'Mostrando 0 a 0 de 0 registros',
                        infoFiltered: '(filtrado de _MAX_ registros totales)',
                        search: 'Buscar:',
                        zeroRecords: 'No se encontraron registros',
                        emptyTable: 'No hay datos disponibles',
                        paginate: {
                            first: 'Primera',
                            last: 'Ultima',
                            next: 'Siguiente',
                            previous: 'Anterior',
                        },
                    },
                });
            }

            function getQuery(fechaDesde, fechaHasta) {
                const params = new URLSearchParams();
                if ((fechaDesde || '').trim() !== '') {
                    params.set('fecha_desde', fechaDesde.trim());
                }
                if ((fechaHasta || '').trim() !== '') {
                    params.set('fecha_hasta', fechaHasta.trim());
                }
                // Evita que el navegador reutilice una respuesta vieja al refrescar la tabla.
                params.set('_t', String(Date.now()));
                const query = params.toString();
                return query !== '' ? ('?' + query) : '';
            }

            async function cargarSeguimientoDia() {
                const url = '/contabilidad/electricidad/seguimiento-dia/data' + getQuery(segDesde.value, segHasta.value);

                const response = await fetch(url, {
                    cache: 'no-store',
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                });

                if (!response.ok) {
                    throw new Error('No se pudo cargar seguimiento por dia.');
                }

                const payload = await response.json();
                tablaSeguimientoBody.innerHTML = '';
                resumenSeguimiento = normalizeResumen(payload.resumen);

                (payload.data || []).forEach(function (item) {
                    const row = document.createElement('tr');
                    row.innerHTML = `
                        <td>${item.fecha_solicitud || ''}</td>
                        <td>${item.distribuidora || ''}</td>
                        <td>${item.nic || ''}</td>
                        <td>${item.agencia || ''}</td>
                        <td>${item.ruta || ''}</td>
                        <td>${estatusEditableCell('seguimiento', item.id, item.estatus)}</td>
                        <td>${item.observaciones || ''}</td>
                    `;

                    tablaSeguimientoBody.appendChild(row);
                });

                dtSeguimiento = initDataTable('#tablaSeguimientoDia');
                updateStatusCards();

                const termino = (buscarSegAgencia?.value || '').trim();
                dtSeguimiento.column(3).search(termino).draw();
            }

            async function cargarAveriasDia() {
                const url = '/contabilidad/electricidad/averias-dia/data' + getQuery(aveDesde.value, aveHasta.value);

                const response = await fetch(url, {
                    cache: 'no-store',
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                });

                if (!response.ok) {
                    throw new Error('No se pudo cargar reporte de averias por dia.');
                }

                const payload = await response.json();
                tablaAveriasBody.innerHTML = '';
                resumenAverias = normalizeResumen(payload.resumen);

                (payload.data || []).forEach(function (item) {
                    const row = document.createElement('tr');
                    row.innerHTML = `
                        <td>${item.fecha_reporte || ''}</td>
                        <td>${item.reporte || ''}</td>
                        <td>${item.distribuidora || ''}</td>
                        <td>${item.nic || ''}</td>
                        <td>${item.agencia || ''}</td>
                        <td>${item.ruta || ''}</td>
                        <td>${item.coordinadores || ''}</td>
                        <td>${item.agente_venta_am || ''}</td>
                        <td>${item.agente_venta_pm || ''}</td>
                        <td>${estatusEditableCell('averia', item.id, item.estatus)}</td>
                        <td>${item.observaciones || ''}</td>
                    `;

                    tablaAveriasBody.appendChild(row);
                });

                dtAverias = initDataTable('#tablaAveriasDia');
                updateStatusCards();

                const termino = (buscarAveAgencia?.value || '').trim();
                dtAverias.column(4).search(termino).draw();
            }

            formSeguimiento?.addEventListener('submit', async function (event) {
                event.preventDefault();

                const payload = {
                    fecha_solicitud: document.getElementById('segFechaSolicitud').value,
                    distribuidora: document.getElementById('segDistribuidora').value.trim(),
                    nic: document.getElementById('segNic').value.trim(),
                    agencia: document.getElementById('segAgencia').value.trim(),
                    ruta: document.getElementById('segRuta').value.trim(),
                    estatus: document.getElementById('segEstatus').value,
                    observaciones: document.getElementById('segObservaciones').value.trim(),
                };

                if (!payload.fecha_solicitud || !payload.distribuidora || !payload.nic || !payload.agencia || !payload.ruta || !payload.estatus) {
                    alert('Completa todos los campos obligatorios de seguimiento.');
                    return;
                }

                const response = await fetch('/contabilidad/electricidad/seguimiento-dia', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    body: JSON.stringify(payload),
                });

                const json = await response.json().catch(function () { return {}; });
                if (!response.ok) {
                    const firstValidation = json?.errors ? Object.values(json.errors)[0]?.[0] : '';
                    throw new Error(firstValidation || json.message || 'No se pudo guardar el seguimiento.');
                }

                formSeguimiento.reset();
                closeModal(modalSeguimientoDia);
                await cargarSeguimientoDia();
            });

            formAverias?.addEventListener('submit', async function (event) {
                event.preventDefault();

                const payload = {
                    fecha_reporte: document.getElementById('aveFechaReporte').value,
                    reporte: document.getElementById('aveReporte').value.trim(),
                    distribuidora: document.getElementById('aveDistribuidora').value.trim(),
                    nic: document.getElementById('aveNic').value.trim(),
                    agencia: document.getElementById('aveAgencia').value.trim(),
                    ruta: document.getElementById('aveRuta').value.trim(),
                    coordinadores: document.getElementById('aveCoordinadores').value.trim(),
                    agente_venta_am: document.getElementById('aveAgenteAm').value.trim(),
                    agente_venta_pm: document.getElementById('aveAgentePm').value.trim(),
                    estatus: document.getElementById('aveEstatus').value,
                    observaciones: document.getElementById('aveObservaciones').value.trim(),
                };

                if (!payload.fecha_reporte || !payload.reporte || !payload.distribuidora || !payload.nic || !payload.agencia || !payload.ruta || !payload.estatus) {
                    alert('Completa todos los campos obligatorios de averias.');
                    return;
                }

                const response = await fetch('/contabilidad/electricidad/averias-dia', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    body: JSON.stringify(payload),
                });

                const json = await response.json().catch(function () { return {}; });
                if (!response.ok) {
                    const firstValidation = json?.errors ? Object.values(json.errors)[0]?.[0] : '';
                    throw new Error(firstValidation || json.message || 'No se pudo guardar el reporte de averia.');
                }

                formAverias.reset();
                closeModal(modalAveriasDia);
                await cargarAveriasDia();
            });

            btnFiltrarSeguimiento?.addEventListener('click', function () {
                cargarSeguimientoDia().catch(function (error) {
                    alert(error.message || 'No se pudo filtrar seguimiento.');
                });
            });

            btnFiltrarAverias?.addEventListener('click', function () {
                cargarAveriasDia().catch(function (error) {
                    alert(error.message || 'No se pudo filtrar averias.');
                });
            });

            buscarSegAgencia?.addEventListener('input', function () {
                if (!dtSeguimiento) {
                    return;
                }

                dtSeguimiento.column(3).search((this.value || '').trim()).draw();
            });

            buscarAveAgencia?.addEventListener('input', function () {
                if (!dtAverias) {
                    return;
                }

                dtAverias.column(4).search((this.value || '').trim()).draw();
            });

            document.addEventListener('click', function (event) {
                const btn = event.target.closest('.js-editar-estatus');
                if (!btn) {
                    return;
                }

                const tipo = btn.getAttribute('data-tipo');
                const id = btn.getAttribute('data-id');
                const estatus = btn.getAttribute('data-estatus') || 'pendiente';

                if (!tipo || !id) {
                    return;
                }

                seleccionarYActualizarEstatus(tipo, id, estatus, btn).catch(function (error) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: error.message || 'No fue posible actualizar el estatus.',
                    });
                });
            });

            cargarSeguimientoDia().catch(function (error) {
                alert(error.message || 'No se pudo cargar seguimiento.');
            });

            cargarAveriasDia().catch(function (error) {
                alert(error.message || 'No se pudo cargar averias.');
            });
        });
    </script>
@endsection

