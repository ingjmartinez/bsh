@extends('app')

@section('content')
    <style>
        .tech-solicitudes-page {
            font-size: 1.02rem;
        }

        .tech-solicitudes-page .page-title-box h4 {
            font-size: 1.45rem;
        }

        .tech-solicitudes-page .form-label,
        .tech-solicitudes-page .form-check-label,
        .tech-solicitudes-page .text-muted,
        .tech-solicitudes-page .table,
        .tech-solicitudes-page .btn,
        .tech-solicitudes-page .modal-body,
        .tech-solicitudes-page .modal-footer {
            font-size: 1rem;
        }

        .tech-solicitudes-page .form-control,
        .tech-solicitudes-page .form-select {
            font-size: 1rem;
            min-height: 42px;
        }

        .tech-stat-card {
            min-height: 118px;
        }

        .tech-stat-card .card-body {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 1.35rem 1.5rem;
        }

        .tech-stat-value {
            font-size: 2.5rem;
            font-weight: 700;
            line-height: 1;
            letter-spacing: -0.04em;
            color: #1f2b3d;
        }

        .tech-stat-label {
            display: block;
            margin-top: 0.55rem;
            font-size: 0.95rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            color: #6c757d;
        }

        .tech-stat-icon {
            width: 42px;
            height: 42px;
            border-radius: 14px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 1.1rem;
        }

        .tech-solicitudes-page .table thead th {
            font-size: 0.98rem;
            font-weight: 700;
        }

        .tech-solicitudes-page .table tbody td {
            font-size: 1rem;
            vertical-align: middle;
        }

        .tech-solicitudes-page .card-title {
            font-size: 1.2rem;
        }

        .tech-solicitudes-page .modal-title {
            font-size: 1.25rem;
        }

        .tech-progress-wrap {
            min-width: 160px;
        }

        .tech-progress-bar {
            height: 8px;
            border-radius: 999px;
            overflow: hidden;
            background: #e9edf4;
        }

        .tech-progress-bar > span {
            display: block;
            height: 100%;
            border-radius: 999px;
            background: linear-gradient(90deg, #0d6efd 0%, #20c997 100%);
        }

        .tech-progress-meta {
            font-size: 0.92rem;
            font-weight: 600;
            color: #495057;
        }

        .tech-action-stack {
            display: flex;
            gap: 0.75rem;
            flex-wrap: wrap;
            align-items: center;
            justify-content: space-between;
            width: 100%;
        }
    </style>

    <div class="main-content tech-solicitudes-page">
        <div class="page-content">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-12">
                        <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                            <h4 class="mb-sm-0">Servicios Generales - Requerimientos</h4>
                            <div class="page-title-right">
                                <ol class="breadcrumb m-0">
                                    <li class="breadcrumb-item"><a href="{{ route('inicio.index') }}">Inicio</a></li>
                                    <li class="breadcrumb-item"><a href="{{ route('servicios-generales.index') }}">Servicios Generales</a></li>
                                    <li class="breadcrumb-item active">Requerimientos</li>
                                </ol>
                            </div>
                        </div>
                    </div>
                </div>

                @if($setupPending)
                    <div class="row">
                        <div class="col-12">
                            <div class="alert alert-warning alert-border-left mb-4" role="alert">
                                <i class="ri-error-warning-line me-2 align-middle"></i>
                                El modulo esta creado, pero la tabla de base de datos aun no existe. Debes ejecutar la migracion pendiente para empezar a registrar requerimientos.
                            </div>
                        </div>
                    </div>
                @endif

                <div class="row">
                    <div class="col-xl-3 col-md-6">
                        <div class="card card-animate tech-stat-card" title="Total tickets">
                            <div class="card-body">
                                <div>
                                    <div class="tech-stat-value" id="stat-total">{{ $stats['total'] }}</div>
                                    <span class="tech-stat-label">Total tickets</span>
                                </div>
                                <span class="tech-stat-icon bg-primary-subtle text-primary">
                                    <i class="ri-ticket-2-line"></i>
                                </span>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-3 col-md-6">
                        <div class="card card-animate tech-stat-card" title="Pendientes">
                            <div class="card-body">
                                <div>
                                    <div class="tech-stat-value" id="stat-pendientes">{{ $stats['pendientes'] }}</div>
                                    <span class="tech-stat-label">Pendientes</span>
                                </div>
                                <span class="tech-stat-icon bg-warning-subtle text-warning">
                                    <i class="ri-alarm-warning-line"></i>
                                </span>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-3 col-md-6">
                        <div class="card card-animate tech-stat-card" title="En gestion">
                            <div class="card-body">
                                <div>
                                    <div class="tech-stat-value" id="stat-progreso">{{ $stats['en_progreso'] }}</div>
                                    <span class="tech-stat-label">En gestion</span>
                                </div>
                                <span class="tech-stat-icon bg-info-subtle text-info">
                                    <i class="ri-radar-line"></i>
                                </span>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-3 col-md-6">
                        <div class="card card-animate tech-stat-card" title="Resueltas">
                            <div class="card-body">
                                <div>
                                    <div class="tech-stat-value" id="stat-resueltas">{{ $stats['resueltas'] }}</div>
                                    <span class="tech-stat-label">Resueltas</span>
                                </div>
                                <span class="tech-stat-icon bg-success-subtle text-success">
                                    <i class="ri-checkbox-circle-line"></i>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-body">
                                <div class="row g-3 align-items-end">
                                    <div class="col-md-2">
                                        <label class="form-label">Tipo</label>
                                        <select id="filtro-tipo" class="form-select">
                                            <option value="">Todos</option>
                                            <option value="internet">No tengo internet</option>
                                            <option value="electricidad">No tengo luz</option>
                                            <option value="sistema_frizado">Se me friso el sistema</option>
                                            <option value="inversor">Cambiar el inversor</option>
                                        </select>
                                    </div>
                                    <div class="col-md-2">
                                        <label class="form-label">Estado</label>
                                        <select id="filtro-estado" class="form-select">
                                            <option value="">Todos</option>
                                            <option value="pendiente">Pendiente</option>
                                            <option value="asignada">Asignada</option>
                                            <option value="en_progreso">En progreso</option>
                                            <option value="en_espera">En espera</option>
                                            <option value="solicitud_cierre">Solicitud de cierre</option>
                                            <option value="resuelta">Resuelta</option>
                                            <option value="cancelada">Cancelada</option>
                                        </select>
                                    </div>
                                    <div class="col-md-2">
                                        <label class="form-label">Prioridad</label>
                                        <select id="filtro-prioridad" class="form-select">
                                            <option value="">Todas</option>
                                            <option value="baja">Baja</option>
                                            <option value="media">Media</option>
                                            <option value="alta">Alta</option>
                                            <option value="critica">Critica</option>
                                        </select>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label">Asignado</label>
                                        <select id="filtro-asignado" class="form-select">
                                            <option value="">Todos</option>
                                            @foreach($asignables as $user)
                                                <option value="{{ $user->id }}">{{ $user->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label">Buscar</label>
                                        <input type="text" id="filtro-search" class="form-control" placeholder="Ticket, titulo, descripcion o usuario">
                                    </div>
                                    <div class="col-md-2">
                                        <div class="form-check form-switch mt-4">
                                            <input class="form-check-input" type="checkbox" id="filtro-solo-mias">
                                            <label class="form-check-label" for="filtro-solo-mias">Solo mis tickets</label>
                                        </div>
                                    </div>
                                    <div class="col-md-10 text-end">
                                        <button class="btn btn-soft-secondary me-1" id="btnAplicarFiltros">
                                            <i class="ri-filter-3-line me-1"></i>Filtrar
                                        </button>
                                        <button class="btn btn-soft-dark me-1" id="btnLimpiarFiltros">
                                            <i class="ri-eraser-line me-1"></i>Limpiar
                                        </button>
                                        <button class="btn btn-primary" id="btnNuevaSolicitud">
                                            <i class="ri-add-line me-1"></i>Nuevo requerimiento
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header border-0">
                                <div class="d-flex flex-wrap gap-2 align-items-center justify-content-between">
                                    <div>
                                        <h5 class="card-title mb-1">Bandeja de requerimientos</h5>
                                        <p class="text-muted mb-0">Registro centralizado de requerimientos para los tecnicos de servicios generales.</p>
                                    </div>
                                    <div class="text-muted small">
                                        @if($puedeVerTodo)
                                            Visualizando tickets del modulo completo.
                                        @else
                                            Visualizando tus tickets y los que te fueron asignados.
                                        @endif
                                    </div>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-bordered table-striped align-middle mb-0">
                                        <thead class="table-light">
                                            <tr>
                                                <th style="width: 110px;">Ticket</th>
                                                <th>Requerimiento</th>
                                                <th style="width: 180px;">Tipo</th>
                                                <th style="width: 120px;">Prioridad</th>
                                                <th style="width: 140px;">Estado</th>
                                                <th style="width: 190px;">Progreso</th>
                                                <th style="width: 180px;">Solicitante</th>
                                                <th style="width: 180px;">Asignado</th>
                                                <th style="width: 150px;">Creado</th>
                                                <th style="width: 110px;">Accion</th>
                                            </tr>
                                        </thead>
                                        <tbody id="solicitudesTableBody">
                                            <tr>
                                                <td colspan="10" class="text-center text-muted py-4">Cargando requerimientos...</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modalSolicitudTecnologia" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <div>
                        <h5 class="modal-title mb-1" id="modalSolicitudTitulo">Nuevo requerimiento</h5>
                        <p class="text-muted small mb-0" id="modalSolicitudMeta">Completa el formulario para registrar un nuevo ticket.</p>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="formSolicitudTecnologia">
                        <input type="hidden" id="solicitud_id" name="solicitud_id">

                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Titulo</label>
                                <select class="form-select" id="titulo" name="titulo" required>
                                    <option value="">Seleccione...</option>
                                    <option value="Averia">Averia</option>
                                    <option value="Solicitud">Solicitud</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Tipo</label>
                                <select class="form-select" id="tipo" name="tipo" required>
                                    <option value="internet">No tengo internet</option>
                                    <option value="electricidad">No tengo luz</option>
                                    <option value="sistema_frizado">Se me friso el sistema</option>
                                    <option value="inversor">Cambiar el inversor</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Prioridad</label>
                                <select class="form-select" id="prioridad" name="prioridad" required>
                                    <option value="media">Media</option>
                                    <option value="baja">Baja</option>
                                    <option value="alta">Alta</option>
                                    <option value="critica">Critica</option>
                                </select>
                            </div>
                            <div class="col-md-12">
                                <label class="form-label">Descripcion</label>
                                <textarea class="form-control" id="descripcion" name="descripcion" rows="4" maxlength="5000" placeholder="Describe claramente la incidencia, necesidad o requerimiento." required></textarea>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Asignar a</label>
                                <select class="form-select" id="asignado_id" name="asignado_id" required>
                                    <option value="">Seleccione un responsable</option>
                                    @foreach($asignables as $user)
                                        <option value="{{ $user->id }}">{{ $user->name }}{{ $user->email ? ' - ' . $user->email : '' }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6" id="estadoContainer" style="display: none;">
                                <label class="form-label">Estado</label>
                                <select class="form-select" id="estado" name="estado">
                                    <option value="pendiente">Pendiente</option>
                                    <option value="asignada">Asignada</option>
                                    <option value="en_progreso">En progreso</option>
                                    <option value="en_espera">En espera</option>
                                    <option value="solicitud_cierre">Solicitud de cierre</option>
                                    <option value="cancelada">Cancelada</option>
                                </select>
                            </div>
                            <div class="col-md-12" id="progresoContainer" style="display: none;">
                                <div class="d-flex align-items-center justify-content-between mb-2">
                                    <label class="form-label mb-0">Progreso del requerimiento</label>
                                    <span class="badge bg-info-subtle text-info" id="progresoLabel">0%</span>
                                </div>
                                <input type="range" class="form-range" id="progreso" name="progreso" min="0" max="100" step="5" value="0">
                            </div>
                            <div class="col-md-12" id="detalleSolucionContainer" style="display: none;">
                                <label class="form-label">Detalle de gestion / solucion</label>
                                <textarea class="form-control" id="detalle_solucion" name="detalle_solucion" rows="4" maxlength="5000" placeholder="Documenta el diagnostico, avance, solucion aplicada o proximo paso."></textarea>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <div class="tech-action-stack">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cerrar</button>
                        <div class="d-flex gap-2 flex-wrap">
                            <button type="button" class="btn btn-warning" id="btnSolicitarCierre" style="display: none;">Solicitar cierre</button>
                            <button type="button" class="btn btn-success" id="btnFinalizarTicket" style="display: none;">Finalizar ticket</button>
                            <button type="button" class="btn btn-primary" id="btnGuardarSolicitud">Guardar requerimiento</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('script')
    <script>
        const TEC_SOLICITUDES_BASE_URL = '{{ url('/servicios-generales/requerimientos') }}';
        const TEC_SOLICITUDES_LIST_URL = '{{ route('servicios-generales.requerimientos.list') }}';
        const TEC_SOLICITUDES_REQUEST_CLOSE_BASE_URL = '{{ url('/servicios-generales/requerimientos') }}';
        const TEC_SOLICITUDES_FINALIZE_BASE_URL = '{{ url('/servicios-generales/requerimientos') }}';
        const TEC_CSRF = '{{ csrf_token() }}';
        const modalSolicitud = new bootstrap.Modal(document.getElementById('modalSolicitudTecnologia'));

        document.addEventListener('DOMContentLoaded', function() {
            bindTecnologiaEvents();
            cargarSolicitudes(getSolicitudIdFromUrl());
        });

        function bindTecnologiaEvents() {
            document.getElementById('btnAplicarFiltros').addEventListener('click', function() {
                cargarSolicitudes();
            });

            document.getElementById('btnLimpiarFiltros').addEventListener('click', limpiarFiltros);
            document.getElementById('btnNuevaSolicitud').addEventListener('click', abrirModalNuevaSolicitud);
            document.getElementById('btnGuardarSolicitud').addEventListener('click', guardarSolicitud);
            document.getElementById('btnSolicitarCierre').addEventListener('click', solicitarCierreTicket);
            document.getElementById('btnFinalizarTicket').addEventListener('click', finalizarTicket);
            document.getElementById('progreso').addEventListener('input', function() {
                updateProgressUI(this.value);
            });

            document.getElementById('filtro-search').addEventListener('keydown', function(event) {
                if (event.key === 'Enter') {
                    event.preventDefault();
                    cargarSolicitudes();
                }
            });
        }

        function getSolicitudIdFromUrl() {
            const params = new URLSearchParams(window.location.search);
            const solicitudId = params.get('requerimiento_id') || params.get('solicitud_id');
            return solicitudId && !Number.isNaN(Number(solicitudId)) ? Number(solicitudId) : null;
        }

        function limpiarFiltros() {
            document.getElementById('filtro-tipo').value = '';
            document.getElementById('filtro-estado').value = '';
            document.getElementById('filtro-prioridad').value = '';
            document.getElementById('filtro-asignado').value = '';
            document.getElementById('filtro-search').value = '';
            document.getElementById('filtro-solo-mias').checked = false;
            cargarSolicitudes();
        }

        function abrirModalNuevaSolicitud() {
            document.getElementById('formSolicitudTecnologia').reset();
            document.getElementById('solicitud_id').value = '';
            document.getElementById('modalSolicitudTitulo').textContent = 'Nuevo requerimiento';
            document.getElementById('modalSolicitudMeta').textContent = 'Completa el formulario para registrar un nuevo ticket.';
            document.getElementById('estadoContainer').style.display = 'none';
            document.getElementById('detalleSolucionContainer').style.display = 'none';
            document.getElementById('progresoContainer').style.display = 'none';
            document.getElementById('progreso').value = 0;
            updateProgressUI(0);
            document.getElementById('btnGuardarSolicitud').style.display = '';
            document.getElementById('btnSolicitarCierre').style.display = 'none';
            document.getElementById('btnFinalizarTicket').style.display = 'none';
            toggleFormDisabled(false);
            modalSolicitud.show();
        }

        function cargarSolicitudes(openSolicitudId = null) {
            const params = new URLSearchParams();

            const tipo = document.getElementById('filtro-tipo').value;
            const estado = document.getElementById('filtro-estado').value;
            const prioridad = document.getElementById('filtro-prioridad').value;
            const asignadoId = document.getElementById('filtro-asignado').value;
            const search = document.getElementById('filtro-search').value.trim();
            const soloMias = document.getElementById('filtro-solo-mias').checked;

            if (tipo) params.append('tipo', tipo);
            if (estado) params.append('estado', estado);
            if (prioridad) params.append('prioridad', prioridad);
            if (asignadoId) params.append('asignado_id', asignadoId);
            if (search) params.append('search', search);
            if (soloMias) params.append('solo_mias', '1');
            if (openSolicitudId) params.append('requerimiento_id', String(openSolicitudId));

            fetch(`${TEC_SOLICITUDES_LIST_URL}?${params.toString()}`, {
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
                .then(handleJsonResponse)
                .then(response => {
                    renderStats(response.stats || {});
                    renderSolicitudes(response.data || []);

                    if (openSolicitudId) {
                        const requerimiento = (response.data || []).find(item => item.id === openSolicitudId);
                        if (requerimiento) {
                            abrirModalEdicion(openSolicitudId);
                        }
                    }
                })
                .catch(showRequestError);
        }

        function renderStats(stats) {
            document.getElementById('stat-total').textContent = stats.total ?? 0;
            document.getElementById('stat-pendientes').textContent = stats.pendientes ?? 0;
            document.getElementById('stat-progreso').textContent = stats.en_progreso ?? 0;
            document.getElementById('stat-resueltas').textContent = stats.resueltas ?? 0;
        }

        function renderSolicitudes(items) {
            const tableBody = document.getElementById('solicitudesTableBody');

            if (!items.length) {
                tableBody.innerHTML = '<tr><td colspan="10" class="text-center text-muted py-4">No hay requerimientos para los filtros seleccionados.</td></tr>';
                return;
            }

            tableBody.innerHTML = items.map(item => {
                return `
                    <tr>
                        <td><span class="fw-semibold">${escapeHtml(item.ticket_codigo)}</span></td>
                        <td>
                            <div class="fw-semibold">${escapeHtml(item.titulo)}</div>
                            <div class="text-muted small">${escapeHtml(limitText(item.descripcion, 130))}</div>
                        </td>
                        <td><span class="badge bg-${escapeHtml(item.badge_tipo)}-subtle text-${escapeHtml(item.badge_tipo)}">${escapeHtml(item.tipo_label || formatLabel(item.tipo))}</span></td>
                        <td><span class="badge bg-${escapeHtml(item.badge_prioridad)}-subtle text-${escapeHtml(item.badge_prioridad)}">${escapeHtml(formatLabel(item.prioridad))}</span></td>
                        <td><span class="badge bg-${escapeHtml(item.badge_estado)}-subtle text-${escapeHtml(item.badge_estado)}">${escapeHtml(formatLabel(item.estado))}</span></td>
                        <td>${renderProgressCell(item)}</td>
                        <td>
                            <div>${escapeHtml(item.solicitante)}</div>
                            <div class="text-muted small">${escapeHtml(item.solicitante_email || '')}</div>
                        </td>
                        <td>
                            <div>${escapeHtml(item.asignado)}</div>
                            <div class="text-muted small">${escapeHtml(item.asignado_email || '')}</div>
                        </td>
                        <td>${escapeHtml(item.creado_en || '')}</td>
                        <td>
                            <button type="button" class="btn btn-sm btn-soft-primary" onclick="abrirModalEdicion(${item.id})">
                                ${item.can_edit ? 'Gestionar' : 'Ver'}
                            </button>
                        </td>
                    </tr>
                `;
            }).join('');
        }

        function abrirModalEdicion(id) {
            fetch(`${TEC_SOLICITUDES_BASE_URL}/${id}`, {
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
                .then(handleJsonResponse)
                .then(response => {
                    const requerimiento = response.requerimiento || response.solicitud;

                    document.getElementById('solicitud_id').value = requerimiento.id;
                    document.getElementById('titulo').value = requerimiento.titulo || '';
                    document.getElementById('tipo').value = requerimiento.tipo || 'internet';
                    document.getElementById('prioridad').value = requerimiento.prioridad || 'media';
                    document.getElementById('descripcion').value = requerimiento.descripcion || '';
                    document.getElementById('asignado_id').value = requerimiento.asignado_id || '';
                    document.getElementById('estado').value = requerimiento.estado || 'pendiente';
                    document.getElementById('progreso').value = requerimiento.progreso ?? 0;
                    updateProgressUI(requerimiento.progreso ?? 0);
                    document.getElementById('detalle_solucion').value = requerimiento.detalle_solucion || '';
                    document.getElementById('estadoContainer').style.display = '';
                    document.getElementById('detalleSolucionContainer').style.display = '';
                    document.getElementById('progresoContainer').style.display = '';
                    document.getElementById('modalSolicitudTitulo').textContent = `${requerimiento.ticket_codigo} - ${requerimiento.titulo}`;
                    document.getElementById('modalSolicitudMeta').textContent =
                        `${requerimiento.cierre_solicitado_at ? `Cierre solicitado por ${requerimiento.cierre_solicitado_por} el ${requerimiento.cierre_solicitado_at}` : `Solicitante: ${requerimiento.solicitante} | Asignado: ${requerimiento.asignado} | Creado: ${requerimiento.creado_en || ''}`}`;

                    toggleFormDisabled(!requerimiento.can_edit);
                    document.getElementById('progreso').disabled = !requerimiento.can_manage_progress;
                    document.getElementById('btnGuardarSolicitud').style.display = requerimiento.can_edit ? '' : 'none';
                    document.getElementById('btnSolicitarCierre').style.display = requerimiento.can_request_close ? '' : 'none';
                    document.getElementById('btnFinalizarTicket').style.display = requerimiento.can_finalize ? '' : 'none';
                    modalSolicitud.show();
                })
                .catch(showRequestError);
        }

        function toggleFormDisabled(disabled) {
            document.querySelectorAll('#formSolicitudTecnologia input, #formSolicitudTecnologia select, #formSolicitudTecnologia textarea')
                .forEach(element => {
                    element.disabled = disabled;
                });
        }

        function syncTipoUI() {
            // Para Servicios Generales el progreso aplica a todos los tipos.
        }

        function updateProgressUI(value) {
            document.getElementById('progresoLabel').textContent = `${Number(value || 0)}%`;
        }

        function guardarSolicitud() {
            const solicitudId = document.getElementById('solicitud_id').value;
            const isEdit = solicitudId !== '';

            const payload = {
                titulo: document.getElementById('titulo').value.trim(),
                tipo: document.getElementById('tipo').value,
                prioridad: document.getElementById('prioridad').value,
                descripcion: document.getElementById('descripcion').value.trim(),
                asignado_id: document.getElementById('asignado_id').value,
            };

            if (isEdit) {
                payload.estado = document.getElementById('estado').value;
                payload.progreso = Number(document.getElementById('progreso').value || 0);
                payload.detalle_solucion = document.getElementById('detalle_solucion').value.trim();
            }

            const url = isEdit ? `${TEC_SOLICITUDES_BASE_URL}/${solicitudId}` : TEC_SOLICITUDES_BASE_URL;
            const method = isEdit ? 'PUT' : 'POST';

            Swal.fire({
                title: isEdit ? 'Actualizando requerimiento...' : 'Registrando requerimiento...',
                allowOutsideClick: false,
                didOpen: () => Swal.showLoading()
            });

            fetch(url, {
                method,
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': TEC_CSRF,
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify(payload)
            })
                .then(handleJsonResponse)
                .then(response => {
                    Swal.fire('Proceso completado', response.message || 'Requerimiento guardado correctamente.', 'success');
                    modalSolicitud.hide();
                    cargarSolicitudes();
                })
                .catch(showRequestError);
        }

        function solicitarCierreTicket() {
            const solicitudId = document.getElementById('solicitud_id').value;
            if (!solicitudId) {
                return;
            }

            Swal.fire({
                title: 'Solicitar cierre del ticket?',
                text: 'La persona que abrio la solicitud recibira la notificacion para validar el trabajo realizado.',
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Solicitar cierre',
                cancelButtonText: 'Cancelar'
            }).then(result => {
                if (!result.isConfirmed) {
                    return;
                }

                postAction(`${TEC_SOLICITUDES_REQUEST_CLOSE_BASE_URL}/${solicitudId}/solicitar-cierre`, 'Enviando solicitud de cierre...');
            });
        }

        function finalizarTicket() {
            const solicitudId = document.getElementById('solicitud_id').value;
            if (!solicitudId) {
                return;
            }

            Swal.fire({
                title: 'Finalizar ticket?',
                text: 'Confirma que la solicitud fue completada correctamente antes de cerrarla.',
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Finalizar',
                cancelButtonText: 'Cancelar'
            }).then(result => {
                if (!result.isConfirmed) {
                    return;
                }

                postAction(`${TEC_SOLICITUDES_FINALIZE_BASE_URL}/${solicitudId}/finalizar`, 'Finalizando ticket...');
            });
        }

        function postAction(url, loadingTitle) {
            Swal.fire({
                title: loadingTitle,
                allowOutsideClick: false,
                didOpen: () => Swal.showLoading()
            });

            fetch(url, {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': TEC_CSRF,
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
                .then(handleJsonResponse)
                .then(response => {
                    Swal.fire('Proceso completado', response.message || 'Accion ejecutada correctamente.', 'success');
                    modalSolicitud.hide();
                    cargarSolicitudes();
                })
                .catch(showRequestError);
        }

        function handleJsonResponse(response) {
            return response.json().then(data => {
                if (!response.ok) {
                    const error = new Error(data.message || 'No se pudo completar la solicitud.');
                    error.payload = data;
                    throw error;
                }

                return data;
            });
        }

        function showRequestError(error) {
            const payload = error.payload || {};
            const validationErrors = payload.errors ? Object.values(payload.errors).flat() : [];
            const message = validationErrors[0] || payload.message || error.message || 'Ocurrio un error inesperado.';

            Swal.fire('Error', message, 'error');
        }

        function escapeHtml(value) {
            return String(value ?? '')
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#039;');
        }

        function formatLabel(value) {
            return String(value || '')
                .replace(/_/g, ' ')
                .replace(/\b\w/g, char => char.toUpperCase());
        }

        function limitText(value, maxLength) {
            const text = String(value || '');
            if (text.length <= maxLength) {
                return text;
            }

            return `${text.slice(0, maxLength - 3)}...`;
        }

        function renderProgressCell(item) {
            const progreso = Number(item.progreso || 0);

            return `
                <div class="tech-progress-wrap">
                    <div class="d-flex align-items-center justify-content-between mb-1">
                        <span class="tech-progress-meta">${progreso}%</span>
                        <span class="text-muted small">${progreso >= 100 ? 'Listo' : 'En avance'}</span>
                    </div>
                    <div class="tech-progress-bar">
                        <span style="width:${Math.max(0, Math.min(100, progreso))}%"></span>
                    </div>
                </div>
            `;
        }
    </script>
@endsection

