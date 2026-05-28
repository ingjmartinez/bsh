@extends('app')

@section('content')
    <div class="main-content">

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
                                    <li class="breadcrumb-item"><a href="{{ route('reportes.index') }}">Reportes</a></li>
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
                            <div class="card-header d-flex align-items-center justify-content-between">
                                <h5 class="card-title mb-0" id="cardTitle">Reporte de Faltantes por Cedula - Todos los sistemas</h5>

                                <div class="d-flex gap-3 align-items-center justify-content-between flex-wrap">
                                    <div>
                                        <label class="mb-0" for="tipo_faltante">Sistema</label>
                                        <select class="form-select" id="tipo_faltante">
                                            <option value="all" selected>Todos</option>
                                            <option value="bet">Lotobet Real</option>
                                            <option value="net">Lotonet</option>
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

                                    <button id="btnFiltrar" class="btn btn-primary">
                                        Filtrar
                                    </button>

                                    <button id="btnExportarExcel" class="btn btn-success">
                                        Exportar Excel
                                    </button>

                                    <button id="btnExportarPdf" class="btn btn-danger">
                                        Exportar PDF
                                    </button>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive" style="width:100%; height:525px; max-height:525px; overflow-y:scroll;">
                                    <table id="tableFaltantes"
                                        class="table table-bordered dt-responsive nowrap table-striped align-middle"
                                        style="width:100%;">
                                        <thead>
                                            <tr>
                                                <th>Cedula</th>
                                                <th>Agencia ID</th>
                                                <th>Nombre Empleado</th>
                                                <th>Cantidad de Faltantes</th>
                                                <th>Monto Total</th>
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

    <div class="modal fade" id="detalleFaltantesModal" tabindex="-1" aria-labelledby="detalleFaltantesModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="detalleFaltantesModalLabel">Detalle de Faltantes</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label text-muted mb-1">Nombre</label>
                            <div class="fw-semibold" id="detalleNombre">Sin especificar</div>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label text-muted mb-1">Cedula</label>
                            <div class="fw-semibold" id="detalleCedula">-</div>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label text-muted mb-1">Agencia</label>
                            <div class="fw-semibold" id="detalleAgencia">-</div>
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
        const urlBase = '/reportes-faltantes-lotobet';
        const nombresSistema = {
            all: 'Todos los sistemas',
            bet: 'Lotobet Real',
            net: 'Lotonet'
        };

        document.addEventListener('DOMContentLoaded', function() {
            actualizarTitulos();
            cargarDatos(1);
        });

        function obtenerTipoFaltante() {
            return document.getElementById('tipo_faltante').value || 'all';
        }

        function actualizarTitulos() {
            const sistema = nombresSistema[obtenerTipoFaltante()] || 'Todos los sistemas';
            document.getElementById('pageTitle').textContent = `Informe de Faltantes ${sistema}`;
            document.getElementById('breadcrumbTitle').textContent = `Faltantes ${sistema}`;
            document.getElementById('cardTitle').textContent = `Reporte de Faltantes por Cedula - ${sistema}`;
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

        function cargarDatos(page = 1, mostrarProcesando = false) {
            const fechaInicio = document.getElementById('fecha_inicio').value;
            const fechaFin = document.getElementById('fecha_fin').value;
            const tipo = obtenerTipoFaltante();

            const params = new URLSearchParams({
                tipo: tipo,
                fecha_inicio: fechaInicio,
                fecha_fin: fechaFin,
                page: page
            });

            if (mostrarProcesando) {
                mostrarProcesandoDatos();
            }

            fetch(`${urlBase}/list?${params}`)
                .then(response => response.json())
                .then(data => {
                    mostrarDatos(data);
                    generarPaginacion(data);
                })
                .catch(error => console.error('Error:', error))
                .finally(() => {
                    if (mostrarProcesando) {
                        cerrarProcesandoDatos();
                    }
                });
        }

        function mostrarDatos(data) {
            const tbody = document.querySelector('#tableFaltantes tbody');
            tbody.innerHTML = '';

            data.data.forEach(registro => {
                const row = document.createElement('tr');
                const nombreEmpleado = registro.nombre_empleado || 'Sin especificar';
                const agenciaId = registro.agencia_id ?? '';
                const detallesFaltantes = registro.detalles_faltantes || '';
                const totalMonto = parseFloat(registro.total_monto || 0);

                row.innerHTML = `
                    <td>${escapeHtml(registro.identificacion)}</td>
                    <td class="text-center">${escapeHtml(agenciaId)}</td>
                    <td>${escapeHtml(nombreEmpleado)}</td>
                    <td class="text-center">${escapeHtml(registro.cantidad_faltantes)}</td>
                    <td class="text-end">$${totalMonto.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}</td>
                    <td class="text-center">
                        <button type="button"
                            class="btn btn-sm btn-info"
                            data-nombre="${escapeAttribute(nombreEmpleado)}"
                            data-cedula="${escapeAttribute(registro.identificacion)}"
                            data-agencia="${escapeAttribute(agenciaId)}"
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

            const modal = new bootstrap.Modal(document.getElementById('detalleFaltantesModal'));
            modal.show();
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

        document.getElementById('tipo_faltante').addEventListener('change', function() {
            actualizarTitulos();
        });

        document.getElementById('btnExportarExcel').addEventListener('click', function() {
            const fechaInicio = document.getElementById('fecha_inicio').value;
            const fechaFin = document.getElementById('fecha_fin').value;
            const tipo = obtenerTipoFaltante();

            const params = new URLSearchParams({
                tipo: tipo,
                fecha_inicio: fechaInicio,
                fecha_fin: fechaFin
            });

            window.location.href = `${urlBase}/excel?${params}`;
        });

        document.getElementById('btnExportarPdf').addEventListener('click', function() {
            const fechaInicio = document.getElementById('fecha_inicio').value;
            const fechaFin = document.getElementById('fecha_fin').value;
            const tipo = obtenerTipoFaltante();

            const params = new URLSearchParams({
                tipo: tipo,
                fecha_inicio: fechaInicio,
                fecha_fin: fechaFin
            });

            window.location.href = `${urlBase}/pdf?${params}`;
        });
    </script>
@endsection

