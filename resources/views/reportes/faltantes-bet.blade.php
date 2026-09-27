@extends('app')

@section('content')
    <div class="main-content">
        <style>
            #tableFaltantes thead th.sortable-column {
                cursor: pointer;
                user-select: none;
                white-space: nowrap;
            }

            #tableFaltantes thead th.sortable-column .sort-indicator {
                display: inline-block;
                min-width: 1rem;
                margin-left: 0.25rem;
                color: #94a3b8;
            }

            #tableFaltantes thead th.sortable-column.active {
                background-color: #eef4ff;
            }

            #tableFaltantes tbody tr.empleado-inactivo > td {
                background-color: #fff8db;
            }

            #tableFaltantes .terminal-secundaria {
                display: block;
                margin-top: 0.15rem;
                color: #64748b;
                font-size: 0.72rem;
                line-height: 1.15;
                white-space: normal;
            }

            .alerta-faltantes-card {
                cursor: pointer;
                transition: transform 0.2s ease, box-shadow 0.2s ease;
            }

            .alerta-faltantes-card:hover {
                transform: translateY(-2px);
                box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.12);
            }
        </style>

        <div class="page-content">
            <div class="container-fluid">

                <!-- start page title -->
                <div class="row">
                    <div class="col-12">
                        <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                            <h4 class="mb-sm-0" id="pageTitle">Informe de Faltantes Todos los sistemas</h4>

                            <div class="page-title-right">
                                <ol class="breadcrumb m-0">
                                    <li class="breadcrumb-item"><a href="{{ route('inicio.index') }}">Inicio</a></li>
                                    <li class="breadcrumb-item"><a href="{{ route('recursos-humanos.index') }}">Recursos Humanos</a></li>
                                    <li class="breadcrumb-item active" id="breadcrumbTitle">Faltantes Todos los sistemas</li>
                                </ol>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- end page title -->

                <div class="row">
                    <div class="col-lg-12">
                        <div class="card">
                            <div class="card-header d-flex align-items-center justify-content-end">
                                <div class="d-flex gap-3 align-items-center justify-content-between flex-wrap">
                                    <div>
                                        <label class="mb-0" for="tipo_faltante">Sistema</label>
                                        <select class="form-select" id="tipo_faltante">
                                            <option value="all" selected>Todos</option>
                                            <option value="bet">Lotobet Real</option>
                                            <option value="net">Lotedom</option>
                                        </select>
                                    </div>

                                    <div>
                                        <label class="mb-0" for="estado_empleado">Estado</label>
                                        <select class="form-select" id="estado_empleado">
                                            <option value="" selected>Todos</option>
                                            <option value="activo">Activos</option>
                                            <option value="inactivo">Inactivos</option>
                                        </select>
                                    </div>

                                    <div>
                                        <label class="mb-0" for="fecha_inicio">Desde</label>
                                        <input type="date" class="form-control" id="fecha_inicio">
                                    </div>

                                    <div>
                                        <label class="mb-0" for="fecha_fin">Hasta</label>
                                        <input type="date" class="form-control" id="fecha_fin">
                                    </div>

                                    <div>
                                        <label class="mb-0" for="buscar">Buscar</label>
                                        <input
                                            type="text"
                                            class="form-control"
                                            id="buscar"
                                            placeholder="Cedula, agencia, grupo, division o nombre">
                                    </div>

                                    <button id="btnFiltrar" class="btn btn-primary">
                                        Filtrar
                                    </button>

                                    <button id="btnExportarExcel" class="btn btn-success">
                                        Exportar Excel
                                    </button>

                                    <button id="btnExportarPdf" class="btn btn-danger">
                                        Exportar PDF
                                    </button>

                                    <button id="btnConfigurarAlerta" class="btn btn-outline-warning">
                                        <i class="ri-settings-3-line me-1"></i> Configurar alerta
                                    </button>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="row mb-4">
                                    <div class="col-md-4 col-xl-3">
                                        <div id="cardAlertasFaltantes" class="card border border-warning alerta-faltantes-card mb-0" role="button" tabindex="0">
                                            <div class="card-body d-flex align-items-center justify-content-between">
                                                <div>
                                                    <p class="text-uppercase fw-medium text-muted mb-2">Alertas de faltantes</p>
                                                    <h3 id="totalAlertasFaltantes" class="mb-1">0</h3>
                                                    <small id="resumenConfiguracionAlerta" class="text-muted">3+ faltantes o monto desde $5,000.00</small>
                                                </div>
                                                <div class="avatar-sm flex-shrink-0">
                                                    <span class="avatar-title bg-warning-subtle text-warning rounded-circle fs-3">
                                                        <i class="ri-alarm-warning-line"></i>
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="table-responsive" style="width:100%; height:525px; max-height:525px; overflow-y:scroll;">
                                    <table id="tableFaltantes"
                                        class="table table-bordered dt-responsive nowrap table-striped align-middle"
                                        style="width:100%;">
                                        <thead>
                                            <tr>
                                                <th class="sortable-column" data-sort="companyid">Empresa <span class="sort-indicator"></span></th>
                                                <th class="sortable-column" data-sort="empleadoid">Id Empleado <span class="sort-indicator"></span></th>
                                                <th class="sortable-column" data-sort="idcentrocosto">Id CC Empleado <span class="sort-indicator"></span></th>
                                                <th class="sortable-column" data-sort="identificacion">Cedula <span class="sort-indicator"></span></th>
                                                <th class="sortable-column" data-sort="nombre_empleado">Nombre Empleado <span class="sort-indicator"></span></th>
                                                <th>Estado</th>
                                                <th class="sortable-column" data-sort="id_grupo">Grupo <span class="sort-indicator"></span></th>
                                                <th class="sortable-column" data-sort="id_division">Id Division <span class="sort-indicator"></span></th>
                                                <th class="sortable-column" data-sort="agencia_id">Agencia ID <span class="sort-indicator"></span></th>
                                                <th class="sortable-column" data-sort="cantidad_faltantes">Cantidad de Faltantes <span class="sort-indicator"></span></th>
                                                <th class="sortable-column" data-sort="total_monto">Monto Total <span class="sort-indicator"></span></th>
                                                <th>Acciones</th>
                                            </tr>
                                        </thead>
                                        <tbody></tbody>
                                    </table>
                                </div>
                                <div class="d-flex flex-sm-fill d-sm-flex align-items-sm-center justify-content-sm-between mt-3">
                                    <div>
                                        <p class="small text-muted">
                                            Mostrando
                                            <span id="fromPage" class="fw-semibold">0</span>
                                            de
                                            <span id="toPage" class="fw-semibold">0</span>
                                            entradas. Total
                                            <span id="totalRegistros" class="fw-semibold">0</span>
                                            entradas.
                                        </p>
                                    </div>

                                    <div>
                                        <ul id="pagination" class="pagination justify-content-end mb-0"></ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div><!--end row-->
            </div>
            <!-- container-fluid -->
        </div>
        <!-- End Page-content -->

        <footer class="footer">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-sm-6">
                        <script>
                            document.write(new Date().getFullYear())
                        </script> © ERP.
                    </div>
                </div>
            </div>
        </footer>
    </div>

    <div class="modal fade" id="configurarAlertaFaltantesModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Configurar alertas de faltantes</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="minimoFaltantesAlerta" class="form-label">Mínimo de faltantes</label>
                        <input id="minimoFaltantesAlerta" type="number" min="1" step="1" class="form-control">
                    </div>
                    <div>
                        <label for="maximoMontoAlerta" class="form-label">Monto de alerta desde</label>
                        <input id="maximoMontoAlerta" type="number" min="0" step="0.01" class="form-control">
                    </div>
                    <p class="text-muted small mt-3 mb-0">La alerta aparece cuando se cumple cualquiera de las dos condiciones.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancelar</button>
                    <button id="btnGuardarConfiguracionAlerta" type="button" class="btn btn-warning">Guardar configuración</button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="alertasFaltantesModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-fullscreen-xl-down modal-xl modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <div>
                        <h5 class="modal-title">Alertas de faltantes</h5>
                        <small id="descripcionAlertasFaltantes" class="text-muted"></small>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        <button id="btnAlternarColumnasAlerta" type="button" class="btn btn-sm btn-outline-secondary">
                            <i class="ri-eye-line me-1"></i> Mostrar más información
                        </button>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                    </div>
                </div>
                <div class="modal-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped align-middle" style="min-width: 1500px;">
                            <thead>
                                <tr>
                                    <th>Empresa</th>
                                    <th>Cédula</th>
                                    <th>Nombre Empleado</th>
                                    <th>Cantidad de Faltantes</th>
                                    <th>Monto Total</th>
                                    <th>Acción</th>
                                    <th class="alerta-col-extra d-none">Id Empleado</th>
                                    <th class="alerta-col-extra d-none">Id CC Empleado</th>
                                    <th class="alerta-col-extra d-none">Estado</th>
                                    <th class="alerta-col-extra d-none">Grupo</th>
                                    <th class="alerta-col-extra d-none">Id División</th>
                                    <th class="alerta-col-extra d-none">Agencia ID</th>
                                </tr>
                            </thead>
                            <tbody id="tablaAlertasFaltantes"></tbody>
                        </table>
                    </div>
                    <ul id="paginacionAlertasFaltantes" class="pagination justify-content-end mt-3 mb-0"></ul>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="detalleFaltantesModal" tabindex="-1" aria-labelledby="detalleFaltantesModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="detalleFaltantesModalLabel">Detalle de Faltantes</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3 mb-3">
                        <div class="col-md-4">
                            <label class="form-label text-muted mb-1">Nombre</label>
                            <div class="fw-semibold" id="detalleNombre">Sin especificar</div>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label text-muted mb-1">Cedula</label>
                            <div class="fw-semibold" id="detalleCedula">-</div>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label text-muted mb-1">Agencia</label>
                            <div class="fw-semibold" id="detalleAgencia">-</div>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label text-muted mb-1">Id Centro Costo</label>
                            <div class="fw-semibold" id="detalleCentroCosto">-</div>
                        </div>
                        <div class="w-100"></div>
                        <div class="col-md-3">
                            <label class="form-label text-muted mb-1">Grupo</label>
                            <div class="fw-semibold" id="detalleGrupo">-</div>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label text-muted mb-1">Sub Grupo</label>
                            <div class="fw-semibold" id="detalleSubGrupo">-</div>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label text-muted mb-1">Id Division</label>
                            <div class="fw-semibold" id="detalleDivision">-</div>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label text-muted mb-1">Total de Faltantes</label>
                            <div class="fw-semibold" id="detalleTotalFaltantes">0</div>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label text-muted mb-1">Monto Total</label>
                            <div class="fw-semibold" id="detalleMontoTotal">$0.00</div>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-sm table-bordered align-middle mb-0">
                            <thead>
                                <tr>
                                    <th>Fecha</th>
                                    <th class="text-end">Monto del Dia</th>
                                </tr>
                            </thead>
                            <tbody id="detalleFechasFaltantes"></tbody>
                        </table>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                </div>
            </div>
        </div>
    </div>

    <script>
        const urlBase = @json(route('recursos-humanos.faltantes.index'));
        const nombresSistema = {
            all: 'Todos los sistemas',
            bet: 'Lotobet Real',
            net: 'Lotedom'
        };
        const currentSort = {
            by: 'total_monto',
            dir: 'desc'
        };
        let configuracionAlerta = {
            minimo_faltantes: 3,
            maximo_monto: 5000
        };
        let mostrarColumnasExtraAlerta = false;

        document.addEventListener('DOMContentLoaded', function() {
            inicializarFechasPorDefecto();
            actualizarTitulos();
            actualizarIndicadoresOrden();
            cargarDatos(1);
            cargarConfiguracionAlerta();
        });

        function obtenerTipoFaltante() {
            return document.getElementById('tipo_faltante').value || 'all';
        }

        function actualizarTitulos() {
            const sistema = nombresSistema[obtenerTipoFaltante()] || 'Todos los sistemas';
            document.getElementById('pageTitle').textContent = `Informe de Faltantes ${sistema}`;
            document.getElementById('breadcrumbTitle').textContent = `Faltantes ${sistema}`;
        }

        function inicializarFechasPorDefecto() {
            const hoy = new Date();
            const primerDiaMes = new Date(hoy.getFullYear(), hoy.getMonth(), 1);
            document.getElementById('fecha_inicio').value = formatearFechaInput(primerDiaMes);
            document.getElementById('fecha_fin').value = formatearFechaInput(hoy);
        }

        function formatearFechaInput(fecha) {
            const year = fecha.getFullYear();
            const month = String(fecha.getMonth() + 1).padStart(2, '0');
            const day = String(fecha.getDate()).padStart(2, '0');
            return `${year}-${month}-${day}`;
        }

        function mostrarProcesandoDatos() {
            if (typeof Swal === 'undefined') {
                return;
            }

            Swal.fire({
                title: 'Procesando datos',
                text: 'Por favor espere mientras se consulta el reporte.',
                allowOutsideClick: false,
                allowEscapeKey: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });
        }

        function cerrarProcesandoDatos() {
            if (typeof Swal !== 'undefined' && Swal.isVisible()) {
                Swal.close();
            }
        }

        function escapeHtml(value) {
            return String(value ?? '')
                .replaceAll('&', '&amp;')
                .replaceAll('<', '&lt;')
                .replaceAll('>', '&gt;')
                .replaceAll('"', '&quot;')
                .replaceAll("'", '&#039;');
        }

        function escapeAttribute(value) {
            return escapeHtml(value).replaceAll('`', '&#096;');
        }

        function actualizarIndicadoresOrden() {
            document.querySelectorAll('#tableFaltantes thead th.sortable-column').forEach(th => {
                const indicator = th.querySelector('.sort-indicator');
                const isActive = th.dataset.sort === currentSort.by;

                th.classList.toggle('active', isActive);

                if (indicator) {
                    indicator.textContent = isActive
                        ? (currentSort.dir === 'asc' ? '▲' : '▼')
                        : '↕';
                }
            });
        }

        function alternarOrden(sortBy) {
            if (currentSort.by === sortBy) {
                currentSort.dir = currentSort.dir === 'asc' ? 'desc' : 'asc';
            } else {
                currentSort.by = sortBy;
                currentSort.dir = 'asc';
            }

            actualizarIndicadoresOrden();
            cargarDatos(1, true);
        }

        function cargarDatos(page = 1, mostrarProcesando = false) {
            const fechaInicio = document.getElementById('fecha_inicio').value;
            const fechaFin = document.getElementById('fecha_fin').value;
            const tipo = obtenerTipoFaltante();
            const estadoEmpleado = document.getElementById('estado_empleado').value;
            const buscar = document.getElementById('buscar').value.trim();

            const params = new URLSearchParams({
                tipo: tipo,
                estado_empleado: estadoEmpleado,
                fecha_inicio: fechaInicio,
                fecha_fin: fechaFin,
                page: page,
                sort_by: currentSort.by,
                sort_dir: currentSort.dir
            });

            if (buscar) {
                params.append('buscar', buscar);
            }

            if (mostrarProcesando) {
                mostrarProcesandoDatos();
            }

            fetch(`${urlBase}/list?${params}`)
                .then(response => response.json())
                .then(data => {
                    mostrarDatos(data);
                    generarPaginacion(data);
                    cargarAlertas(1, false);
                })
                .catch(error => console.error('Error:', error))
                .finally(() => {
                    if (mostrarProcesando) {
                        cerrarProcesandoDatos();
                    }
                });
        }

        function parametrosReporte(page = 1, soloAlertas = false) {
            const params = new URLSearchParams({
                tipo: obtenerTipoFaltante(),
                estado_empleado: document.getElementById('estado_empleado').value,
                fecha_inicio: document.getElementById('fecha_inicio').value,
                fecha_fin: document.getElementById('fecha_fin').value,
                page: page,
                sort_by: currentSort.by,
                sort_dir: currentSort.dir
            });
            const buscar = document.getElementById('buscar').value.trim();

            if (buscar) {
                params.append('buscar', buscar);
            }

            if (soloAlertas) {
                params.append('solo_alertas', '1');
            }

            return params;
        }

        function cargarConfiguracionAlerta() {
            return fetch(`${urlBase}/configuracion-alerta`)
                .then(response => response.json())
                .then(data => {
                    configuracionAlerta = data;
                    document.getElementById('minimoFaltantesAlerta').value = data.minimo_faltantes;
                    document.getElementById('maximoMontoAlerta').value = data.maximo_monto;
                    actualizarResumenAlerta();
                });
        }

        function actualizarResumenAlerta() {
            const monto = formatearMonto(configuracionAlerta.maximo_monto);
            document.getElementById('resumenConfiguracionAlerta').textContent =
                `${configuracionAlerta.minimo_faltantes}+ faltantes o monto desde ${monto}`;
            document.getElementById('descripcionAlertasFaltantes').textContent =
                `Personas con al menos ${configuracionAlerta.minimo_faltantes} faltantes o monto acumulado desde ${monto}.`;
        }

        function cargarAlertas(page = 1, mostrarModal = true) {
            return fetch(`${urlBase}/list?${parametrosReporte(page, true)}`)
                .then(response => response.json())
                .then(data => {
                    document.getElementById('totalAlertasFaltantes').textContent = data.total || 0;
                    mostrarTablaAlertas(data);
                    generarPaginacionAlertas(data);

                    if (mostrarModal) {
                        bootstrap.Modal.getOrCreateInstance(document.getElementById('alertasFaltantesModal')).show();
                    }
                });
        }

        function mostrarTablaAlertas(data) {
            const tbody = document.getElementById('tablaAlertasFaltantes');

            tbody.innerHTML = data.data.length
                ? data.data.map(registro => {
                    const totalMonto = Number(registro.total_monto || 0);
                    const cantidadFaltantes = Number(registro.cantidad_faltantes || 0);
                    const claseExtra = mostrarColumnasExtraAlerta ? 'alerta-col-extra' : 'alerta-col-extra d-none';
                    const botonVer = cantidadFaltantes > 2
                        ? `<button type="button"
                                class="btn btn-sm btn-info"
                                data-nombre="${escapeAttribute(registro.nombre_empleado || 'Sin especificar')}"
                                data-cedula="${escapeAttribute(registro.identificacion ?? '')}"
                                data-agencia="${escapeAttribute(registro.agencia_id ?? '')}"
                                data-centro-costo="${escapeAttribute(registro.id_centro_costo ?? '')}"
                                data-grupo="${escapeAttribute(registro.id_grupo || '')}"
                                data-sub-grupo="${escapeAttribute(registro.id_sub_grupo || '')}"
                                data-division="${escapeAttribute(registro.id_division || '')}"
                                data-total="${escapeAttribute(cantidadFaltantes)}"
                                data-monto="${escapeAttribute(totalMonto)}"
                                data-detalles="${escapeAttribute(registro.detalles_faltantes || '')}"
                                onclick="mostrarDetalleFaltantes(this)">Ver</button>`
                        : '<span class="text-muted">-</span>';

                    return `<tr>
                        <td>${escapeHtml(registro.companyid ?? '')}</td>
                        <td>${escapeHtml(registro.identificacion ?? '')}</td>
                        <td>${escapeHtml(registro.nombre_empleado || 'Sin especificar')}</td>
                        <td class="text-center">${escapeHtml(cantidadFaltantes)}</td>
                        <td class="text-end">${formatearMonto(totalMonto)}</td>
                        <td class="text-center">${botonVer}</td>
                        <td class="${claseExtra}">${escapeHtml(registro.empleadoid ?? '')}</td>
                        <td class="${claseExtra}">${escapeHtml(registro.idcentrocosto ?? '')}</td>
                        <td class="${claseExtra}">${escapeHtml(registro.estado_empleado || '-')}</td>
                        <td class="${claseExtra}">${escapeHtml(registro.id_grupo || '')}</td>
                        <td class="${claseExtra}">${escapeHtml(registro.id_division || '')}</td>
                        <td class="${claseExtra}">${escapeHtml(registro.agencia_id ?? '')}</td>
                    </tr>`;
                }).join('')
                : '<tr><td colspan="12" class="text-center text-muted">No hay registros que cumplan la alerta.</td></tr>';
        }

        function generarPaginacionAlertas(data) {
            const pagination = document.getElementById('paginacionAlertasFaltantes');
            pagination.innerHTML = '';

            for (let page = 1; page <= data.last_page; page++) {
                const item = document.createElement('li');
                item.className = `page-item ${page === data.current_page ? 'active' : ''}`;
                item.innerHTML = `<button type="button" class="page-link" onclick="cargarAlertas(${page}, false)">${page}</button>`;
                pagination.appendChild(item);
            }
        }

        function mostrarDatos(data) {
            const tbody = document.querySelector('#tableFaltantes tbody');
            tbody.innerHTML = '';

            data.data.forEach(registro => {
                const row = document.createElement('tr');
                const nombreEmpleado = registro.nombre_empleado || 'Sin especificar';
                const agenciaId = registro.agencia_id ?? '';
                const idCentroCosto = registro.id_centro_costo ?? '';
                const grupo = registro.id_grupo || '';
                const subGrupo = registro.id_sub_grupo || '';
                const division = registro.id_division || '';
                const detallesFaltantes = registro.detalles_faltantes || '';
                const totalMonto = parseFloat(registro.total_monto || 0);
                const estadoEmpleado = registro.estado_empleado || '';
                const empresasTerminal = Number(registro.empresas_terminal || 0);
                const detalleTerminal = empresasTerminal > 1
                    ? `<small class="terminal-secundaria">Terminal empresa ${escapeHtml(registro.terminal_companyid ?? registro.companyid ?? '')} · agencia compartida en ${empresasTerminal} empresas</small>`
                    : '';

                row.classList.toggle('empleado-inactivo', estadoEmpleado === 'Inactivo');

                row.innerHTML = `
                    <td class="text-center">${escapeHtml(registro.companyid ?? '')}</td>
                    <td class="text-center">${escapeHtml(registro.empleadoid ?? '')}</td>
                    <td class="text-center">${escapeHtml(registro.idcentrocosto ?? '')}${detalleTerminal}</td>
                    <td>${escapeHtml(registro.identificacion)}</td>
                    <td>${escapeHtml(nombreEmpleado)}</td>
                    <td class="text-center">${escapeHtml(estadoEmpleado || '-')}</td>
                    <td>${escapeHtml(grupo)}</td>
                    <td>${escapeHtml(division)}</td>
                    <td class="text-center">${escapeHtml(agenciaId)}</td>
                    <td class="text-center">${escapeHtml(registro.cantidad_faltantes)}</td>
                    <td class="text-end">$${totalMonto.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}</td>
                    <td class="text-center">
                        <button type="button"
                            class="btn btn-sm btn-info"
                            data-nombre="${escapeAttribute(nombreEmpleado)}"
                            data-cedula="${escapeAttribute(registro.identificacion)}"
                            data-agencia="${escapeAttribute(agenciaId)}"
                            data-centro-costo="${escapeAttribute(idCentroCosto)}"
                            data-grupo="${escapeAttribute(grupo)}"
                            data-sub-grupo="${escapeAttribute(subGrupo)}"
                            data-division="${escapeAttribute(division)}"
                            data-total="${escapeAttribute(registro.cantidad_faltantes)}"
                            data-monto="${escapeAttribute(totalMonto)}"
                            data-detalles="${escapeAttribute(detallesFaltantes)}"
                            onclick="mostrarDetalleFaltantes(this)">
                            Ver
                        </button>
                    </td>
                `;
                tbody.appendChild(row);
            });

            document.getElementById('fromPage').textContent = (data.from || 0);
            document.getElementById('toPage').textContent = (data.to || 0);
            document.getElementById('totalRegistros').textContent = (data.total || 0);
        }

        function formatearMonto(monto) {
            return `$${Number(monto || 0).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`;
        }

        function agruparDetallesPorFecha(detalles) {
            return (detalles || '')
                .split(';;')
                .map(detalle => detalle.trim())
                .filter(Boolean)
                .reduce((fechas, detalle) => {
                    const [fecha, monto] = detalle.split('|');

                    if (!fecha) {
                        return fechas;
                    }

                    fechas[fecha] = (fechas[fecha] || 0) + Number(monto || 0);
                    return fechas;
                }, {});
        }

        function mostrarDetalleFaltantes(button) {
            const fechas = agruparDetallesPorFecha(button.dataset.detalles);
            const fechasBody = document.getElementById('detalleFechasFaltantes');

            document.getElementById('detalleNombre').textContent = button.dataset.nombre || 'Sin especificar';
            document.getElementById('detalleCedula').textContent = button.dataset.cedula || '-';
            document.getElementById('detalleAgencia').textContent = button.dataset.agencia || '-';
            document.getElementById('detalleCentroCosto').textContent = button.dataset.centroCosto || '-';
            document.getElementById('detalleGrupo').textContent = button.dataset.grupo || '-';
            document.getElementById('detalleSubGrupo').textContent = button.dataset.subGrupo || '-';
            document.getElementById('detalleDivision').textContent = button.dataset.division || '-';
            document.getElementById('detalleTotalFaltantes').textContent = button.dataset.total || '0';
            document.getElementById('detalleMontoTotal').textContent = formatearMonto(button.dataset.monto);

            const filasFechas = Object.entries(fechas);
            fechasBody.innerHTML = filasFechas.length
                ? filasFechas.map(([fecha, monto]) => `
                    <tr>
                        <td>${escapeHtml(fecha)}</td>
                        <td class="text-end">${formatearMonto(monto)}</td>
                    </tr>
                `).join('')
                : '<tr><td colspan="2" class="text-muted">Sin fechas disponibles</td></tr>';

            const alertasModalElement = document.getElementById('alertasFaltantesModal');
            const detalleModalElement = document.getElementById('detalleFaltantesModal');
            const abrirDetalle = () => bootstrap.Modal.getOrCreateInstance(detalleModalElement).show();

            if (alertasModalElement.classList.contains('show')) {
                alertasModalElement.addEventListener('hidden.bs.modal', abrirDetalle, { once: true });
                bootstrap.Modal.getInstance(alertasModalElement)?.hide();
                return;
            }

            abrirDetalle();
        }

        function generarPaginacion(data) {
            const pagination = document.getElementById('pagination');
            pagination.innerHTML = '';
            const paginasVisibles = 10;
            const mitadPaginasVisibles = Math.floor(paginasVisibles / 2);
            let paginaInicio = Math.max(data.current_page - mitadPaginasVisibles, 1);
            let paginaFin = paginaInicio + paginasVisibles - 1;

            if (paginaFin > data.last_page) {
                paginaFin = data.last_page;
                paginaInicio = Math.max(paginaFin - paginasVisibles + 1, 1);
            }

            if (data.prev_page_url) {
                const li = document.createElement('li');
                li.className = 'page-item';
                li.innerHTML = `<a class="page-link" href="#" onclick="cargarDatos(${data.current_page - 1}); return false;">Anterior</a>`;
                pagination.appendChild(li);
            }

            for (let i = paginaInicio; i <= paginaFin; i++) {
                const li = document.createElement('li');
                li.className = data.current_page === i ? 'page-item active' : 'page-item';
                li.innerHTML = `<a class="page-link" href="#" onclick="cargarDatos(${i}); return false;">${i}</a>`;
                pagination.appendChild(li);
            }

            if (data.next_page_url) {
                const li = document.createElement('li');
                li.className = 'page-item';
                li.innerHTML = `<a class="page-link" href="#" onclick="cargarDatos(${data.current_page + 1}); return false;">Siguiente</a>`;
                pagination.appendChild(li);
            }
        }

        document.getElementById('btnFiltrar').addEventListener('click', function() {
            actualizarTitulos();
            cargarDatos(1, true);
        });

        document.getElementById('btnConfigurarAlerta').addEventListener('click', function() {
            bootstrap.Modal.getOrCreateInstance(document.getElementById('configurarAlertaFaltantesModal')).show();
        });

        document.getElementById('btnGuardarConfiguracionAlerta').addEventListener('click', function() {
            const button = this;
            button.disabled = true;

            fetch(`${urlBase}/configuracion-alerta`, {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({
                    minimo_faltantes: document.getElementById('minimoFaltantesAlerta').value,
                    maximo_monto: document.getElementById('maximoMontoAlerta').value
                })
            })
                .then(async response => {
                    const data = await response.json();

                    if (!response.ok) {
                        throw new Error(Object.values(data.errors || {}).flat()[0] || data.message || 'No fue posible guardar la configuración.');
                    }

                    configuracionAlerta = data;
                    actualizarResumenAlerta();
                    bootstrap.Modal.getInstance(document.getElementById('configurarAlertaFaltantesModal')).hide();
                    cargarAlertas(1, false);
                    Swal.fire('Configuración guardada', data.message, 'success');
                })
                .catch(error => Swal.fire('Error', error.message, 'error'))
                .finally(() => button.disabled = false);
        });

        document.getElementById('cardAlertasFaltantes').addEventListener('click', function() {
            cargarAlertas(1, true);
        });

        document.getElementById('cardAlertasFaltantes').addEventListener('keydown', function(event) {
            if (event.key === 'Enter' || event.key === ' ') {
                event.preventDefault();
                cargarAlertas(1, true);
            }
        });

        document.getElementById('btnAlternarColumnasAlerta').addEventListener('click', function() {
            mostrarColumnasExtraAlerta = !mostrarColumnasExtraAlerta;
            document.querySelectorAll('.alerta-col-extra').forEach(column => {
                column.classList.toggle('d-none', !mostrarColumnasExtraAlerta);
            });
            this.innerHTML = mostrarColumnasExtraAlerta
                ? '<i class="ri-eye-off-line me-1"></i> Ocultar información adicional'
                : '<i class="ri-eye-line me-1"></i> Mostrar más información';
        });

        document.getElementById('tipo_faltante').addEventListener('change', function() {
            actualizarTitulos();
        });

        document.getElementById('btnExportarExcel').addEventListener('click', function() {
            const fechaInicio = document.getElementById('fecha_inicio').value;
            const fechaFin = document.getElementById('fecha_fin').value;
            const tipo = obtenerTipoFaltante();
            const estadoEmpleado = document.getElementById('estado_empleado').value;
            const buscar = document.getElementById('buscar').value.trim();

            const params = new URLSearchParams({
                tipo: tipo,
                estado_empleado: estadoEmpleado,
                fecha_inicio: fechaInicio,
                fecha_fin: fechaFin,
                sort_by: currentSort.by,
                sort_dir: currentSort.dir
            });

            if (buscar) {
                params.append('buscar', buscar);
            }

            window.location.href = `${urlBase}/excel?${params}`;
        });

        document.getElementById('btnExportarPdf').addEventListener('click', function() {
            const fechaInicio = document.getElementById('fecha_inicio').value;
            const fechaFin = document.getElementById('fecha_fin').value;
            const tipo = obtenerTipoFaltante();
            const estadoEmpleado = document.getElementById('estado_empleado').value;
            const buscar = document.getElementById('buscar').value.trim();

            const params = new URLSearchParams({
                tipo: tipo,
                estado_empleado: estadoEmpleado,
                fecha_inicio: fechaInicio,
                fecha_fin: fechaFin,
                sort_by: currentSort.by,
                sort_dir: currentSort.dir
            });

            if (buscar) {
                params.append('buscar', buscar);
            }

            window.location.href = `${urlBase}/pdf?${params}`;
        });

        document.getElementById('buscar').addEventListener('keydown', function(event) {
            if (event.key === 'Enter') {
                event.preventDefault();
                actualizarTitulos();
                cargarDatos(1, true);
            }
        });

        document.querySelectorAll('#tableFaltantes thead th.sortable-column').forEach(th => {
            th.addEventListener('click', function() {
                alternarOrden(this.dataset.sort);
            });
        });
    </script>
@endsection

