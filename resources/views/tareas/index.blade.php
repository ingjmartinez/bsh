@extends('app')

@section('content')
    <div class="main-content">
        <div class="page-content">
            <div class="container-fluid">

                <!-- Page title -->
                <div class="row">
                    <div class="col-12">
                        <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                            <h4 class="mb-sm-0">Gestión de Tareas</h4>
                            <div class="page-title-right">
                                <ol class="breadcrumb m-0">
                                    <li class="breadcrumb-item"><a href="/">Inicio</a></li>
                                    <li class="breadcrumb-item active">Tareas</li>
                                </ol>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- STAT CARDS -->
                <div class="row">
                    <div class="col-xl-3 col-md-6">
                        <div class="card card-animate">
                            <div class="card-body">
                                <div class="d-flex align-items-center">
                                    <div class="flex-grow-1">
                                        <p class="text-uppercase fw-medium text-muted mb-0">Total Tareas</p>
                                    </div>
                                </div>
                                <div class="d-flex align-items-end justify-content-between mt-4">
                                    <div>
                                        <h4 class="fs-22 fw-semibold mb-0" id="stat-total">{{ $stats['total'] }}</h4>
                                    </div>
                                    <div class="avatar-sm flex-shrink-0">
                                        <span class="avatar-title bg-primary-subtle rounded fs-3">
                                            <i class="ri-task-line text-primary"></i>
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-3 col-md-6">
                        <div class="card card-animate">
                            <div class="card-body">
                                <div class="d-flex align-items-center">
                                    <div class="flex-grow-1">
                                        <p class="text-uppercase fw-medium text-muted mb-0">En Progreso</p>
                                    </div>
                                </div>
                                <div class="d-flex align-items-end justify-content-between mt-4">
                                    <div>
                                        <h4 class="fs-22 fw-semibold mb-0" id="stat-progreso">{{ $stats['en_progreso'] }}</h4>
                                    </div>
                                    <div class="avatar-sm flex-shrink-0">
                                        <span class="avatar-title bg-info-subtle rounded fs-3">
                                            <i class="ri-loader-4-line text-info"></i>
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-3 col-md-6">
                        <div class="card card-animate">
                            <div class="card-body">
                                <div class="d-flex align-items-center">
                                    <div class="flex-grow-1">
                                        <p class="text-uppercase fw-medium text-muted mb-0">Completadas</p>
                                    </div>
                                </div>
                                <div class="d-flex align-items-end justify-content-between mt-4">
                                    <div>
                                        <h4 class="fs-22 fw-semibold mb-0" id="stat-completadas">{{ $stats['completadas'] }}</h4>
                                    </div>
                                    <div class="avatar-sm flex-shrink-0">
                                        <span class="avatar-title bg-success-subtle rounded fs-3">
                                            <i class="ri-checkbox-circle-line text-success"></i>
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-3 col-md-6">
                        <div class="card card-animate">
                            <div class="card-body">
                                <div class="d-flex align-items-center">
                                    <div class="flex-grow-1">
                                        <p class="text-uppercase fw-medium text-muted mb-0">Atrasadas</p>
                                    </div>
                                </div>
                                <div class="d-flex align-items-end justify-content-between mt-4">
                                    <div>
                                        <h4 class="fs-22 fw-semibold mb-0" id="stat-atrasadas">
                                            <span class="{{ $stats['atrasadas'] > 0 ? 'text-danger' : '' }}">{{ $stats['atrasadas'] }}</span>
                                        </h4>
                                    </div>
                                    <div class="avatar-sm flex-shrink-0">
                                        <span class="avatar-title bg-danger-subtle rounded fs-3">
                                            <i class="ri-alarm-warning-line text-danger"></i>
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- FILTROS + ACCIONES -->
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-body">
                                <div class="row g-3 align-items-end">
                                    <div class="col-md-3">
                                        <label class="form-label">Departamento</label>
                                        <select id="filtro-departamento" class="form-select form-select-sm">
                                            <option value="">Todos</option>
                                            @foreach($departamentos as $depto)
                                                <option value="{{ $depto->id }}">{{ $depto->nombre }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-2">
                                        <label class="form-label">Estado</label>
                                        <select id="filtro-estado" class="form-select form-select-sm">
                                            <option value="">Todos</option>
                                            <option value="pendiente">Pendiente</option>
                                            <option value="en_progreso">En Progreso</option>
                                            <option value="completada">Completada</option>
                                            <option value="cancelada">Cancelada</option>
                                        </select>
                                    </div>
                                    <div class="col-md-2">
                                        <label class="form-label">Asignado</label>
                                        <select id="filtro-asignado" class="form-select form-select-sm">
                                            <option value="">Todos</option>
                                            @foreach($usuarios as $user)
                                                <option value="{{ $user->id }}">{{ $user->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-2">
                                        <div class="form-check form-switch mt-4">
                                            <input class="form-check-input" type="checkbox" id="filtro-atrasadas">
                                            <label class="form-check-label text-danger fw-medium" for="filtro-atrasadas">Solo atrasadas</label>
                                        </div>
                                    </div>
                                    <div class="col-md-3 text-end">
                                        <button class="btn btn-sm btn-soft-secondary me-1" onclick="aplicarFiltros()">
                                            <i class="ri-filter-3-line me-1"></i>Filtrar
                                        </button>
                                        <button class="btn btn-sm btn-soft-info me-1" data-bs-toggle="modal" data-bs-target="#modalDepartamento">
                                            <i class="ri-building-line me-1"></i>Deptos
                                        </button>
                                        <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#modalTarea" onclick="limpiarFormTarea()">
                                            <i class="ri-add-line me-1"></i>Nueva Tarea
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- TABS: GANTT / TABLA -->
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header">
                                <ul class="nav nav-tabs card-header-tabs" role="tablist">
                                    <li class="nav-item">
                                        <a class="nav-link active" data-bs-toggle="tab" href="#tab-gantt" role="tab">
                                            <i class="ri-bar-chart-horizontal-line me-1"></i> Diagrama Gantt
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link" data-bs-toggle="tab" href="#tab-lista" role="tab">
                                            <i class="ri-list-check-2 me-1"></i> Lista
                                        </a>
                                    </li>
                                </ul>
                            </div>
                            <div class="card-body">
                                <div class="tab-content">
                                    <!-- TAB GANTT -->
                                    <div class="tab-pane active" id="tab-gantt" role="tabpanel">
                                        <div id="gantt-chart" style="width:100%; height:500px; overflow-x:auto; position:relative;">
                                            <div class="text-center py-5 text-muted" id="gantt-loading">
                                                <div class="spinner-border text-primary mb-3" role="status"></div>
                                                <p>Cargando diagrama...</p>
                                            </div>
                                            <div id="gantt-container"></div>
                                        </div>
                                    </div>
                                    <!-- TAB LISTA -->
                                    <div class="tab-pane" id="tab-lista" role="tabpanel">
                                        <div class="table-responsive">
                                            <table id="tableTareas" class="table table-bordered table-striped align-middle" style="width:100%; font-size: 0.85rem;">
                                                <thead class="table-light">
                                                    <tr>
                                                        <th style="width:40px">ID</th>
                                                        <th>Título</th>
                                                        <th>Departamento</th>
                                                        <th>Asignado</th>
                                                        <th>Estado</th>
                                                        <th>Prioridad</th>
                                                        <th style="width:120px">Progreso</th>
                                                        <th>Inicio</th>
                                                        <th>Fin</th>
                                                        <th style="width:80px">Acciones</th>
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
        </div>

        <footer class="footer">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-sm-6"><script>document.write(new Date().getFullYear())</script> © ERP.</div>
                </div>
            </div>
        </footer>
    </div>

    <!-- MODAL: CREAR/EDITAR TAREA -->
    <div class="modal fade" id="modalTarea" tabindex="-1" aria-labelledby="modalTareaLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="modalTareaLabel"><i class="ri-task-line me-2"></i>Nueva Tarea</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="formTarea">
                        <input type="hidden" id="tarea-id" value="">
                        <div class="row g-3">
                            <div class="col-md-8">
                                <label class="form-label">Título <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="tarea-titulo" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Prioridad <span class="text-danger">*</span></label>
                                <select class="form-select" id="tarea-prioridad">
                                    <option value="baja">Baja</option>
                                    <option value="media" selected>Media</option>
                                    <option value="alta">Alta</option>
                                    <option value="critica">🔴 Crítica</option>
                                </select>
                            </div>
                            <div class="col-12">
                                <label class="form-label">Descripción</label>
                                <textarea class="form-control" id="tarea-descripcion" rows="3" placeholder="Describe la tarea..."></textarea>
                            </div>
                            <div class="col-12">
                                <label class="form-label">Adjunto (opcional)</label>
                                <input type="file" class="form-control" id="tarea-adjunto" accept=".pdf,.doc,.docx,.xls,.xlsx,.png,.jpg,.jpeg,.zip,.rar,.txt">
                                <small class="text-muted">Tamaño máximo: 10MB</small>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Departamento <span class="text-danger">*</span></label>
                                <select class="form-select" id="tarea-departamento" required>
                                    <option value="">Seleccionar...</option>
                                    @foreach($departamentos as $depto)
                                        <option value="{{ $depto->id }}">{{ $depto->nombre }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Asignar a</label>
                                <select class="form-select" id="tarea-asignado">
                                    <option value="">Sin asignar</option>
                                    @foreach($usuarios as $user)
                                        <option value="{{ $user->id }}">{{ $user->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-4" id="campo-estado-container" style="display:none;">
                                <label class="form-label">Estado</label>
                                <select class="form-select" id="tarea-estado">
                                    <option value="pendiente">Pendiente</option>
                                    <option value="en_progreso">En Progreso</option>
                                    <option value="completada">Completada</option>
                                    <option value="cancelada">Cancelada</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Fecha Inicio <span class="text-danger">*</span></label>
                                <input type="date" class="form-control" id="tarea-inicio" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Fecha Fin <span class="text-danger">*</span></label>
                                <input type="date" class="form-control" id="tarea-fin" required>
                            </div>
                            <div class="col-md-4" id="campo-progreso-container" style="display:none;">
                                <label class="form-label">Progreso: <span id="progreso-label">0</span>%</label>
                                <input type="range" class="form-range" id="tarea-progreso" min="0" max="100" step="5" value="0"
                                       oninput="document.getElementById('progreso-label').textContent = this.value">
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-primary" onclick="guardarTarea()">
                        <i class="ri-save-line me-1"></i> <span id="btn-guardar-text">Guardar</span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- MODAL: DETALLE TAREA -->
    <div class="modal fade" id="modalDetalle" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="ri-file-list-3-line me-2"></i>Detalle de Tarea</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="detalle-body">
                    <div class="text-center py-4">
                        <div class="spinner-border text-primary" role="status"></div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cerrar</button>
                </div>
            </div>
        </div>
    </div>

    <!-- MODAL: DEPARTAMENTOS -->
    <div class="modal fade" id="modalDepartamento" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-info text-white">
                    <h5 class="modal-title"><i class="ri-building-line me-2"></i>Departamentos</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-2 mb-3">
                        <div class="col-5">
                            <input type="text" class="form-control form-control-sm" id="depto-nombre" placeholder="Nombre">
                        </div>
                        <div class="col-4">
                            <input type="text" class="form-control form-control-sm" id="depto-descripcion" placeholder="Descripción">
                        </div>
                        <div class="col-1">
                            <input type="color" class="form-control form-control-sm form-control-color p-0" id="depto-color" value="#405189" style="height:31px;">
                        </div>
                        <div class="col-2">
                            <button class="btn btn-sm btn-primary w-100" onclick="guardarDepartamento()">
                                <i class="ri-add-line"></i>
                            </button>
                        </div>
                    </div>
                    <div id="lista-departamentos"></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal eliminar -->
    <div class="modal fade" id="modalEliminar" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-sm">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Confirmar eliminación</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">¿Está seguro que desea eliminar esta tarea?</div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light btn-sm" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-danger btn-sm" id="btn-confirmar-eliminar">Eliminar</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('script')
<style>
    /* GANTT CUSTOM CSS */
    .gantt-header { display:flex; position:sticky; top:0; z-index:2; background:#f3f6f9; border-bottom:2px solid #e9ebec; }
    .gantt-header-label { padding:6px 0; text-align:center; font-size:11px; font-weight:600; color:#878a99; border-right:1px solid #e9ebec; }
    .gantt-row { display:flex; align-items:center; border-bottom:1px solid #f3f3f9; min-height:38px; position:relative; }
    .gantt-row:hover { background:#f3f6f9; }
    .gantt-label { width:260px; min-width:260px; padding:4px 10px; font-size:12px; display:flex; align-items:center; gap:6px; border-right:2px solid #e9ebec; position:sticky; left:0; background:#fff; z-index:1; }
    .gantt-row:hover .gantt-label { background:#f3f6f9; }
    .gantt-bars { flex:1; position:relative; height:38px; }
    .gantt-bar { position:absolute; height:22px; top:8px; border-radius:4px; cursor:pointer; display:flex; align-items:center; padding:0 6px; font-size:10px; color:#fff; font-weight:600; transition: opacity .2s; min-width:4px; }
    .gantt-bar:hover { opacity:.85; box-shadow: 0 2px 8px rgba(0,0,0,.2); }
    .gantt-bar .bar-progress { position:absolute; left:0; top:0; height:100%; border-radius:4px; background:rgba(255,255,255,.25); }
    .gantt-today-line { position:absolute; top:0; bottom:0; width:2px; background:#f06548; z-index:1; }
    .gantt-today-label { position:absolute; top:-18px; font-size:9px; color:#f06548; font-weight:700; transform:translateX(-50%); }
    .badge-atrasada { animation: pulse-danger 1.5s infinite; }
    @keyframes pulse-danger { 0%,100%{opacity:1} 50%{opacity:.6} }
    .timeline-item { position:relative; padding-left:24px; padding-bottom:16px; border-left:2px solid #e9ebec; }
    .timeline-item:last-child { border-left-color:transparent; }
    .timeline-item::before { content:''; position:absolute; left:-5px; top:2px; width:8px; height:8px; border-radius:50%; background:#405189; }
    .timeline-item.tipo-cambio_estado::before { background:#f7b84b; }
    .timeline-item.tipo-cambio_progreso::before { background:#0ab39c; }
</style>

<script>
const CSRF = '{{ csrf_token() }}';
const URL_GANTT = '{{ url("/tareas/gantt-data") }}';
const URL_LIST = '{{ url("/tareas-list") }}';
const URL_TAREAS = '{{ url("/tareas") }}';
const URL_STATS = '{{ url("/tareas/stats") }}';
const URL_DEPTOS = '{{ url("/tareas/departamentos") }}';
const TAREA_ID_URL = new URLSearchParams(window.location.search).get('tarea_id');
const ES_ADMIN_SUPERIOR = @json($esAdminSuperior ?? false);

let dataTable;
let ganttTareas = [];

$(document).ready(function() {
    cargarGantt();
    initDataTable();
    cargarDepartamentosModal();
    aplicarFiltroTareaDesdeUrl();
});

function aplicarFiltroTareaDesdeUrl() {
    const tareaId = TAREA_ID_URL;

    if (!tareaId) return;

    $('#filtro-departamento').val('');
    $('#filtro-estado').val('');
    $('#filtro-asignado').val('');
    $('#filtro-atrasadas').prop('checked', false);

    const tabLista = document.querySelector('a[href="#tab-lista"]');
    if (tabLista && window.bootstrap?.Tab) {
        const instance = bootstrap.Tab.getOrCreateInstance(tabLista);
        instance.show();
    }

    if (dataTable) {
        dataTable.ajax.reload();
    }
}

/* FILTROS */
function getFiltros() {
    return {
        departamento_id: $('#filtro-departamento').val(),
        estado: $('#filtro-estado').val(),
        asignado_id: $('#filtro-asignado').val(),
        atrasadas: $('#filtro-atrasadas').is(':checked') ? 1 : 0,
    };
}

function aplicarFiltros() {
    cargarGantt();
    if (dataTable) dataTable.ajax.reload();
    actualizarStats();
}

function actualizarStats() {
    $.getJSON(URL_STATS, getFiltros(), function(data) {
        $('#stat-total').text(data.total);
        $('#stat-progreso').text(data.por_estado.en_progreso);
        $('#stat-completadas').text(data.por_estado.completadas);
        var atVal = data.atrasadas;
        $('#stat-atrasadas').html(atVal > 0 ? '<span class="text-danger">' + atVal + '</span>' : '0');
    });
}

/* GANTT CHART */
function cargarGantt() {
    $('#gantt-loading').show();
    $('#gantt-container').empty();
    $.getJSON(URL_GANTT, getFiltros(), function(data) {
        ganttTareas = data;
        renderGantt(data);
        $('#gantt-loading').hide();
    });
}

function renderGantt(tareas) {
    const container = $('#gantt-container');
    container.empty();

    if (tareas.length === 0) {
        container.html('<div class="text-center py-5 text-muted"><i class="ri-calendar-todo-line fs-1 d-block mb-2"></i>No hay tareas para mostrar</div>');
        return;
    }

    // Calcular rango de fechas
    let allDates = [];
    tareas.forEach(t => {
        allDates.push(new Date(t.start_date));
        allDates.push(new Date(t.end_date));
    });
    let minDate = new Date(Math.min(...allDates));
    let maxDate = new Date(Math.max(...allDates));

    // Extender rango ±7 días
    minDate.setDate(minDate.getDate() - 3);
    maxDate.setDate(maxDate.getDate() + 7);

    const totalDays = Math.ceil((maxDate - minDate) / (1000 * 60 * 60 * 24));
    const dayWidth = 28;
    const totalWidth = totalDays * dayWidth;

    // Header de fechas
    let headerHtml = '<div class="gantt-header"><div style="width:260px;min-width:260px;border-right:2px solid #e9ebec;padding:6px 10px;font-size:11px;font-weight:700;color:#405189;position:sticky;left:0;background:#f3f6f9;z-index:3;">Tarea</div><div style="display:flex;flex:1;width:'+totalWidth+'px;">';

    let currentDate = new Date(minDate);
    const today = new Date();
    today.setHours(0,0,0,0);
    let todayOffset = -1;

    for (let i = 0; i < totalDays; i++) {
        const d = new Date(currentDate);
        const isWeekend = d.getDay() === 0 || d.getDay() === 6;
        const isToday = d.toDateString() === today.toDateString();
        if (isToday) todayOffset = i;

        const bg = isToday ? 'background:#fff3cd;' : (isWeekend ? 'background:#f8f9fa;' : '');
        const dayStr = d.getDate();
        const monthNames = ['Ene','Feb','Mar','Abr','May','Jun','Jul','Ago','Sep','Oct','Nov','Dic'];
        const label = dayStr === 1 || i === 0 ? monthNames[d.getMonth()] + ' ' + dayStr : dayStr;

        headerHtml += '<div class="gantt-header-label" style="width:'+dayWidth+'px;min-width:'+dayWidth+'px;'+bg+'">' + label + '</div>';
        currentDate.setDate(currentDate.getDate() + 1);
    }
    headerHtml += '</div></div>';

    // Filas de tareas
    let rowsHtml = '';
    tareas.forEach(t => {
        const startOff = Math.max(0, Math.ceil((new Date(t.start_date) - minDate) / (1000*60*60*24)));
        const endOff = Math.ceil((new Date(t.end_date) - minDate) / (1000*60*60*24));
        const barWidth = Math.max((endOff - startOff) * dayWidth, 4);
        const barLeft = startOff * dayWidth;

        const prioIcons = {baja:'Baja',media:'Media',alta:'Alta',critica:'Critica'};
        const estadoLabels = {pendiente:'Pendiente',en_progreso:'En Progreso',completada:'Completada',cancelada:'Cancelada'};
        const barColor = t.atrasada ? '#f06548' : t.color;
        const atrasadaBadge = t.atrasada ? ' <span class="badge bg-danger badge-atrasada ms-1" style="font-size:9px">-' + t.dias_atraso + 'd</span>' : '';

        rowsHtml += '<div class="gantt-row">';
        rowsHtml += '<div class="gantt-label" title="' + t.text + '">';
        rowsHtml += '<span class="badge" style="background:'+t.depto_color+';font-size:8px;padding:2px 5px;">' + t.departamento + '</span>';
        rowsHtml += '<span class="text-truncate" style="max-width:150px">' + (prioIcons[t.prioridad]||'') + ' ' + t.text + '</span>';
        rowsHtml += atrasadaBadge;
        rowsHtml += '</div>';
        rowsHtml += '<div class="gantt-bars" style="width:'+totalWidth+'px;">';

        // Today line
        if (todayOffset >= 0) {
            rowsHtml += '<div class="gantt-today-line" style="left:'+(todayOffset*dayWidth + dayWidth/2)+'px;"></div>';
        }

        rowsHtml += '<div class="gantt-bar" style="left:'+barLeft+'px;width:'+barWidth+'px;background:'+barColor+';" ';
        rowsHtml += 'onclick="verDetalle('+t.id+')" title="'+t.text+' | '+estadoLabels[t.estado]+' | '+t.asignado+' | '+(t.progress*100).toFixed(0)+'%">';
        rowsHtml += '<div class="bar-progress" style="width:'+(t.progress*100)+'%"></div>';
        rowsHtml += '<span style="position:relative;z-index:1;">' + (t.progress*100).toFixed(0) + '%</span>';
        rowsHtml += '</div>';
        rowsHtml += '</div></div>';
    });

    container.html(headerHtml + '<div style="max-height:420px;overflow-y:auto;">' + rowsHtml + '</div>');
}

/* DATATABLE */
function initDataTable() {
    dataTable = $('#tableTareas').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: URL_LIST,
            data: function(d) {
                d.departamento_id = $('#filtro-departamento').val();
                d.estado = $('#filtro-estado').val();
                d.tarea_id = TAREA_ID_URL || '';
            }
        },
        responsive: true,
        scrollX: true,
        columnDefs: [
            { targets: [3, 4, 5, 6, 7, 8], visible: $(window).width() > 768 }
        ],
        columns: [
            { data: 'id', className: 'text-center' },
            { data: 'titulo', render: function(data, type, row) {
                let badge = row.atrasada ? ' <span class="badge bg-danger badge-atrasada" style="font-size:9px">Atrasada -'+row.dias_atraso+'d</span>' : '';
                return '<a href="javascript:void(0)" onclick="verDetalle('+row.id+')" class="text-primary fw-medium">'+data+'</a>' + badge;
            }},
            { data: 'departamento', render: function(data, type, row) {
                return '<span class="badge" style="background:'+row.depto_color+'">'+data+'</span>';
            }},
            { data: 'asignado' },
            { data: 'estado', render: function(data, type, row) {
                const labels = {pendiente:'Pendiente',en_progreso:'En Progreso',completada:'Completada',cancelada:'Cancelada'};
                return '<span class="badge bg-'+row.badge_estado+'">'+labels[data]+'</span>';
            }},
            { data: 'prioridad', render: function(data) {
                const icons = {baja:'Baja',media:'Media',alta:'Alta',critica:'Critica'};
                return icons[data] || data;
            }},
            { data: 'progreso', render: function(data) {
                const color = data >= 100 ? 'bg-success' : (data >= 50 ? 'bg-info' : 'bg-warning');
                return '<div class="progress" style="height:16px"><div class="progress-bar '+color+'" style="width:'+data+'%;font-size:10px">'+data+'%</div></div>';
            }},
            { data: 'fecha_inicio' },
            { data: 'fecha_fin' },
            { data: null, orderable:false, searchable:false, className:'text-center', render: function(data,type,row) {
                let botonCierre = '';

                if (ES_ADMIN_SUPERIOR && row.estado !== 'completada' && row.estado !== 'cancelada') {
                    botonCierre = '<button class="btn btn-sm btn-soft-success" onclick="finalizarTarea('+row.id+')" title="Finalizar tarea"><i class="ri-checkbox-circle-line"></i></button>';
                } else if (!ES_ADMIN_SUPERIOR && row.progreso >= 100 && row.estado !== 'completada' && row.estado !== 'cancelada') {
                    botonCierre = '<button class="btn btn-sm btn-soft-warning" onclick="solicitarCierreTarea('+row.id+')" title="Solicitar cierre al admin"><i class="ri-mail-send-line"></i></button>';
                }

                return '<div class="d-flex gap-1 justify-content-center">' +
                    '<button class="btn btn-sm btn-soft-info" onclick="verDetalle('+row.id+')" title="Ver detalle y conversación"><i class="ri-chat-3-line"></i></button>' +
                    botonCierre +
                    '<button class="btn btn-sm btn-soft-primary" onclick="editarTarea('+row.id+')" title="Editar"><i class="ri-pencil-line"></i></button>' +
                    '<button class="btn btn-sm btn-soft-danger" onclick="confirmarEliminar('+row.id+')" title="Eliminar"><i class="ri-delete-bin-line"></i></button>' +
                '</div>';
            }}
        ],
        language: { url: '//cdn.datatables.net/plug-ins/1.11.5/i18n/es-ES.json' },
        dom: '<"row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6"f>>rtip'
    });
}

/* CRUD TAREAS */
function limpiarFormTarea() {
    $('#tarea-id').val('');
    $('#formTarea')[0].reset();
    $('#tarea-adjunto').val('');
    $('#tarea-prioridad').val('media');
    $('#tarea-progreso').val(0);
    $('#progreso-label').text('0');
    $('#modalTareaLabel').html('<i class="ri-task-line me-2"></i>Nueva Tarea');
    $('#btn-guardar-text').text('Guardar');
    $('#campo-estado-container, #campo-progreso-container').hide();
}

function guardarTarea() {
    const id = $('#tarea-id').val();
    const data = {
        titulo: $('#tarea-titulo').val(),
        descripcion: $('#tarea-descripcion').val(),
        departamento_id: $('#tarea-departamento').val(),
        asignado_id: $('#tarea-asignado').val() || null,
        prioridad: $('#tarea-prioridad').val(),
        fecha_inicio: $('#tarea-inicio').val(),
        fecha_fin: $('#tarea-fin').val(),
    };

    if (!data.titulo || !data.departamento_id || !data.fecha_inicio || !data.fecha_fin) {
        Swal.fire('Campos requeridos', 'Completa todos los campos obligatorios.', 'warning');
        return;
    }

    const formData = new FormData();
    formData.append('titulo', data.titulo);
    formData.append('descripcion', data.descripcion || '');
    formData.append('departamento_id', data.departamento_id);
    formData.append('prioridad', data.prioridad);
    formData.append('fecha_inicio', data.fecha_inicio);
    formData.append('fecha_fin', data.fecha_fin);

    if (data.asignado_id) {
        formData.append('asignado_id', data.asignado_id);
    }

    const archivoAdjunto = $('#tarea-adjunto')[0]?.files?.[0];
    if (archivoAdjunto) {
        formData.append('adjunto', archivoAdjunto);
    }

    let url = URL_TAREAS;
    let method = 'POST';

    if (id) {
        url = URL_TAREAS + '/' + id;
        method = 'POST';
        formData.append('_method', 'PUT');
        formData.append('estado', $('#tarea-estado').val());
        formData.append('progreso', $('#tarea-progreso').val());
    }

    $.ajax({
        url: url,
        method: method,
        headers: { 'X-CSRF-TOKEN': CSRF },
        processData: false,
        contentType: false,
        data: formData,
        success: function(res) {
            $('#modalTarea').modal('hide');
            Swal.fire({ icon:'success', title:'¡Éxito!', text:res.message, timer:2000, showConfirmButton:false });
            cargarGantt();
            if (dataTable) dataTable.ajax.reload(null, false);
            actualizarStats();
        },
        error: function(xhr) {
            const errors = xhr.responseJSON?.errors;
            if (errors) {
                let msg = Object.values(errors).flat().join('<br>');
                Swal.fire({ icon:'error', title:'Error de validación', html:msg });
            } else {
                Swal.fire('Error', xhr.responseJSON?.message || 'Error al guardar.', 'error');
            }
        }
    });
}

function editarTarea(id) {
    $.getJSON(URL_TAREAS + '/' + id, function(res) {
        const t = res.tarea;
        $('#tarea-id').val(t.id);
        $('#tarea-titulo').val(t.titulo);
        $('#tarea-descripcion').val(t.descripcion);
        $('#tarea-departamento').val(t.departamento_id);
        $('#tarea-asignado').val(t.asignado_id || '');
        $('#tarea-prioridad').val(t.prioridad);
        $('#tarea-estado').val(t.estado);
        $('#tarea-progreso').val(t.progreso);
        $('#progreso-label').text(t.progreso);
        $('#tarea-inicio').val(t.fecha_inicio?.split('T')[0]);
        $('#tarea-fin').val(t.fecha_fin?.split('T')[0]);
        $('#modalTareaLabel').html('<i class="ri-pencil-line me-2"></i>Editar Tarea #' + t.id);
        $('#btn-guardar-text').text('Actualizar');
        $('#campo-estado-container, #campo-progreso-container').show();
        $('#modalTarea').modal('show');
    });
}

function solicitarCierreTarea(id) {
    Swal.fire({
        title: '¿Solicitar cierre?',
        text: 'Se enviará una notificación al admin/superior para finalizar esta tarea.',
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Sí, solicitar',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (!result.isConfirmed) return;

        $.ajax({
            url: URL_TAREAS + '/' + id + '/solicitar-cierre',
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': CSRF },
            success: function(res) {
                Swal.fire({ icon:'success', title:'Solicitud enviada', text:res.message, timer:2000, showConfirmButton:false });
                if (dataTable) dataTable.ajax.reload(null, false);
                cargarGantt();
            },
            error: function(xhr) {
                Swal.fire('Error', xhr.responseJSON?.message || 'No se pudo solicitar el cierre.', 'error');
            }
        });
    });
}

function finalizarTarea(id) {
    Swal.fire({
        title: 'Finalizar tarea',
        text: 'Esta acción cerrará definitivamente la tarea.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Sí, finalizar',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (!result.isConfirmed) return;

        $.ajax({
            url: URL_TAREAS + '/' + id + '/finalizar-admin',
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': CSRF },
            success: function(res) {
                Swal.fire({ icon:'success', title:'Tarea finalizada', text:res.message, timer:2000, showConfirmButton:false });
                if (dataTable) dataTable.ajax.reload(null, false);
                cargarGantt();
                actualizarStats();
            },
            error: function(xhr) {
                Swal.fire('Error', xhr.responseJSON?.message || 'No se pudo finalizar la tarea.', 'error');
            }
        });
    });
}

function verDetalle(id) {
    $('#detalle-body').html('<div class="text-center py-4"><div class="spinner-border text-primary" role="status"></div></div>');
    $('#modalDetalle').modal('show');

    $.getJSON(URL_TAREAS + '/' + id, function(res) {
        const t = res.tarea;
        const estadoLabels = {pendiente:'Pendiente',en_progreso:'En Progreso',completada:'Completada',cancelada:'Cancelada'};
        const prioIcons = {baja:'Baja',media:'Media',alta:'Alta',critica:'Critica'};
        const badgeColors = {pendiente:'warning',en_progreso:'info',completada:'success',cancelada:'danger'};

        let html = '<div class="row g-3">';
        html += '<div class="col-md-8"><h5 class="mb-1">' + t.titulo + '</h5>';
        if (res.atrasada) html += '<span class="badge bg-danger badge-atrasada me-1">Atrasada -' + res.dias_atraso + ' días</span>';
        html += '<span class="badge bg-' + badgeColors[t.estado] + '">' + estadoLabels[t.estado] + '</span>';
        html += ' <span>' + prioIcons[t.prioridad] + '</span>';
        html += '</div>';
        html += '<div class="col-md-4 text-end d-flex gap-2 justify-content-end">';
        if (ES_ADMIN_SUPERIOR && t.estado !== 'completada' && t.estado !== 'cancelada') {
            html += '<button class="btn btn-sm btn-success" onclick="$(\'#modalDetalle\').modal(\'hide\');finalizarTarea('+t.id+')"><i class="ri-checkbox-circle-line me-1"></i>Finalizar</button>';
        }
        html += '<button class="btn btn-sm btn-primary" onclick="$(\'#modalDetalle\').modal(\'hide\');editarTarea('+t.id+')"><i class="ri-pencil-line me-1"></i>Editar</button>';
        html += '</div>';

        if (t.descripcion) {
            html += '<div class="col-12"><div class="bg-light rounded p-3"><p class="mb-0 text-muted">' + t.descripcion + '</p></div></div>';
        }

        if (t.adjunto_nombre && t.adjunto_url) {
            html += '<div class="col-12"><small class="text-muted d-block">Adjunto</small><a href="' + t.adjunto_url + '" target="_blank" class="fw-medium"><i class="ri-attachment-2 me-1"></i>' + t.adjunto_nombre + '</a></div>';
        }

        html += '<div class="col-md-3"><small class="text-muted d-block">Departamento</small><span class="badge" style="background:'+(t.departamento?.color||'#405189')+'">'+(t.departamento?.nombre||'-')+'</span></div>';
        html += '<div class="col-md-3"><small class="text-muted d-block">Asignado</small><strong>'+(t.asignado?.name||'Sin asignar')+'</strong></div>';
        html += '<div class="col-md-3"><small class="text-muted d-block">Inicio</small><strong>' + formatDate(t.fecha_inicio) + '</strong></div>';
        html += '<div class="col-md-3"><small class="text-muted d-block">Fin</small><strong>' + formatDate(t.fecha_fin) + '</strong></div>';

        if (t.cierre_solicitado_at) {
            html += '<div class="col-md-6"><small class="text-muted d-block">Solicitud de cierre</small><strong>' + formatDateTime(t.cierre_solicitado_at) + '</strong></div>';
            html += '<div class="col-md-6"><small class="text-muted d-block">Solicitado por</small><strong>' + (t.cierre_solicitado_por?.name || 'Usuario') + '</strong></div>';
        }

        html += '<div class="col-12"><label class="form-label mb-1">Progreso '+t.progreso+'%</label>';
        const pColor = t.progreso >= 100 ? 'bg-success' : (t.progreso >= 50 ? 'bg-info' : 'bg-warning');
        html += '<div class="progress" style="height:20px"><div class="progress-bar '+pColor+'" style="width:'+t.progreso+'%">'+t.progreso+'%</div></div></div>';

        // Subtareas
        if (t.subtareas && t.subtareas.length > 0) {
            html += '<div class="col-12"><h6 class="mb-2"><i class="ri-git-branch-line me-1"></i>Subtareas ('+t.subtareas.length+')</h6>';
            html += '<div class="list-group">';
            t.subtareas.forEach(s => {
                html += '<div class="list-group-item d-flex align-items-center justify-content-between py-2">';
                html += '<span>' + s.titulo + '</span>';
                html += '<div><span class="badge bg-'+badgeColors[s.estado]+' me-1">'+estadoLabels[s.estado]+'</span><span class="badge bg-light text-dark">'+s.progreso+'%</span></div>';
                html += '</div>';
            });
            html += '</div></div>';
        }

        // Comentarios
        html += '<div class="col-12"><h6 class="mb-2"><i class="ri-chat-3-line me-1"></i>Historial / Comentarios</h6>';
        if (t.comentarios && t.comentarios.length > 0) {
            html += '<div style="max-height:200px;overflow-y:auto;">';
            t.comentarios.reverse().forEach(c => {
                html += '<div class="timeline-item tipo-'+c.tipo+'">';
                html += '<div class="d-flex justify-content-between"><strong class="fs-12">'+(c.usuario?.name||'Sistema')+'</strong><small class="text-muted">'+formatDateTime(c.created_at)+'</small></div>';
                html += '<p class="mb-0 text-muted fs-12">' + c.comentario + '</p></div>';
            });
            html += '</div>';
        } else {
            html += '<p class="text-muted fs-12">Sin historial aún.</p>';
        }

        // Formulario comentario
        html += '<div class="input-group mt-2"><input type="text" class="form-control form-control-sm" id="nuevo-comentario" placeholder="Agregar comentario...">';
        html += '<button class="btn btn-sm btn-primary" onclick="agregarComentario('+t.id+')"><i class="ri-send-plane-line"></i></button></div>';
        html += '</div></div>';

        $('#detalle-body').html(html);
    });
}

function confirmarEliminar(id) {
    $('#btn-confirmar-eliminar').off('click').on('click', function() {
        $.ajax({
            url: URL_TAREAS + '/' + id,
            method: 'DELETE',
            headers: { 'X-CSRF-TOKEN': CSRF },
            success: function(res) {
                $('#modalEliminar').modal('hide');
                Swal.fire({ icon:'success', title:'Eliminada', text:res.message, timer:2000, showConfirmButton:false });
                cargarGantt();
                if (dataTable) dataTable.ajax.reload(null, false);
                actualizarStats();
            }
        });
    });
    $('#modalEliminar').modal('show');
}

function agregarComentario(tareaId) {
    const texto = $('#nuevo-comentario').val();
    if (!texto) return;

    $.ajax({
        url: URL_TAREAS + '/' + tareaId + '/comentario',
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': CSRF },
        contentType: 'application/json',
        data: JSON.stringify({ comentario: texto }),
        success: function() {
            verDetalle(tareaId); // Recargar detalle
        }
    });
}

/* DEPARTAMENTOS */
function cargarDepartamentosModal() {
    $.getJSON(URL_DEPTOS, function(data) {
        let html = '<div class="list-group">';
        data.forEach(d => {
            html += '<div class="list-group-item d-flex align-items-center justify-content-between py-2">';
            html += '<div class="d-flex align-items-center gap-2"><span class="rounded-circle d-inline-block" style="width:14px;height:14px;background:'+d.color+'"></span>';
            html += '<strong class="fs-13">'+d.nombre+'</strong>';
            if (d.descripcion) html += ' <small class="text-muted">— '+d.descripcion+'</small>';
            html += '</div>';
            html += '<button class="btn btn-sm btn-soft-danger" onclick="eliminarDepartamento('+d.id+')"><i class="ri-delete-bin-line"></i></button>';
            html += '</div>';
        });
        html += '</div>';
        $('#lista-departamentos').html(data.length > 0 ? html : '<p class="text-muted text-center">No hay departamentos.</p>');
    });
}

function guardarDepartamento() {
    const data = {
        nombre: $('#depto-nombre').val(),
        descripcion: $('#depto-descripcion').val(),
        color: $('#depto-color').val(),
    };
    if (!data.nombre) return;

    $.ajax({
        url: URL_DEPTOS,
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': CSRF },
        contentType: 'application/json',
        data: JSON.stringify(data),
        success: function() {
            $('#depto-nombre').val('');
            $('#depto-descripcion').val('');
            cargarDepartamentosModal();
            Swal.fire({ icon:'success', title:'Departamento creado', timer:1500, showConfirmButton:false });
            // Recargar la página para actualizar selects
            setTimeout(() => location.reload(), 1600);
        },
        error: function(xhr) {
            Swal.fire('Error', xhr.responseJSON?.errors?.nombre?.[0] || 'Error al guardar.', 'error');
        }
    });
}

function eliminarDepartamento(id) {
    Swal.fire({
        title: '¿Eliminar departamento?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#f06548',
        confirmButtonText: 'Eliminar',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: URL_DEPTOS + '/' + id,
                method: 'DELETE',
                headers: { 'X-CSRF-TOKEN': CSRF },
                success: function(res) {
                    cargarDepartamentosModal();
                    Swal.fire({ icon:'success', title:'Eliminado', timer:1500, showConfirmButton:false });
                    setTimeout(() => location.reload(), 1600);
                },
                error: function(xhr) {
                    Swal.fire('Error', xhr.responseJSON?.message || 'No se pudo eliminar.', 'error');
                }
            });
        }
    });
}

/* HELPERS */
function formatDate(dateStr) {
    if (!dateStr) return '-';
    const d = new Date(dateStr);
    return d.toLocaleDateString('es-DO', { day:'2-digit', month:'2-digit', year:'numeric' });
}
function formatDateTime(dateStr) {
    if (!dateStr) return '';
    const d = new Date(dateStr);
    return d.toLocaleDateString('es-DO', { day:'2-digit', month:'short', year:'numeric', hour:'2-digit', minute:'2-digit' });
}
</script>
@endsection

