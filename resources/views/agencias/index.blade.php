@extends('app')

@section('content')
    <div class="main-content">
        <div class="page-content">
            <div class="container-fluid">

                <!-- start page title -->
                <div class="row">
                    <div class="col-12">
                        <div class="page-title-box d-flex flex-column flex-md-row align-items-start align-md-items-center justify-content-between">
                            <h4 class="mb-3 mb-md-0">Mantenimiento de Agencias</h4>

                            <div class="page-title-right w-100 w-md-auto">
                                <ol class="breadcrumb m-0">
                                    <li class="breadcrumb-item"><a href="{{ route('inicio.index') }}">Inicio</a></li>
                                    <li class="breadcrumb-item"><a href="{{ route('mantenimiento.index') }}">Mantenimientos</a></li>
                                    <li class="breadcrumb-item active">Agencias</li>
                                </ol>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- end page title -->

                <div class="row">
                    <div class="col-lg-12">
                        <div class="card">
                            <div class="card-header">
                                <div class="row align-items-start align-md-items-center">
                                    <div class="col-12 col-md-6 mb-3 mb-md-0">
                                        <h5 class="card-title mb-0">Lista de Agencias</h5>
                                    </div>
                                    <div class="col-12 col-md-6">
                                        <div class="row g-2">
                                            <div class="col-6 col-md-3 d-grid">
                                                <a href="{{ route('agencias.incumplimientos') }}" class="btn btn-warning btn-sm">
                                                    <i class="ri-alarm-warning-line align-bottom me-1"></i><span class="d-none d-md-inline">Incumplimientos</span><span class="d-md-none">Incump.</span>
                                                </a>
                                            </div>
                                            <div class="col-6 col-md-3 d-grid">
                                                <button type="button" class="btn btn-success btn-sm" data-bs-toggle="modal" data-bs-target="#importModal">
                                                    <i class="ri-upload-2-line align-bottom me-1"></i><span class="d-none d-md-inline">Importar</span><span class="d-md-none">Imp.</span>
                                                </button>
                                            </div>
                                            <div class="col-6 col-md-3 d-grid">
                                                <button type="button" class="btn btn-secondary btn-sm" data-bs-toggle="modal" data-bs-target="#massUpdateModal">
                                                    <i class="ri-refresh-line align-bottom me-1"></i><span class="d-none d-md-inline">Actualizar masiva</span><span class="d-md-none">Act.</span>
                                                </button>
                                            </div>
                                            <div class="col-6 col-md-3 d-grid">
                                                <a href="{{ route('agencias.export') }}" class="btn btn-info btn-sm">
                                                    <i class="ri-download-2-line align-bottom me-1"></i><span class="d-none d-md-inline">Exportar</span><span class="d-md-none">Exp.</span>
                                                </a>
                                            </div>
                                            <div class="col-6 col-md-3 d-grid">
                                                <a href="{{ route('agencias.create') }}" class="btn btn-primary btn-sm">
                                                    <i class="ri-add-line align-bottom me-1"></i><span class="d-none d-md-inline">Nueva</span><span class="d-md-none">+</span>
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="card-body p-2 p-md-3">
                                <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
                                    <div class="d-flex align-items-center gap-3">
                                        <div>
                                            <small class="text-muted d-block">Agencias activas</small>
                                            <h5 class="mb-0 text-success" id="countAgenciasActivas">0</h5>
                                        </div>
                                        <div>
                                            <small class="text-muted d-block">Agencias inactivas</small>
                                            <h5 class="mb-0 text-danger" id="countAgenciasInactivas" role="button" title="Ver detalle" style="cursor:pointer; text-decoration: underline; text-decoration-style: dotted;">0</h5>
                                        </div>
                                        <div>
                                            <small class="text-muted d-block">Agencias BSH Support</small>
                                            <h5 class="mb-0 text-info" id="countAgenciasBshSupport">0</h5>
                                        </div>
                                        <div>
                                            <small class="text-muted d-block">Agencias Negosur</small>
                                            <h5 class="mb-0 text-primary" id="countAgenciasNegosur">0</h5>
                                        </div>
                                        <div>
                                            <small class="text-muted d-block">Agencia no registrada</small>
                                            <h5 class="mb-0 text-warning" id="countAgenciasNoRegistradas" role="button" title="Ver detalle" style="cursor:pointer; text-decoration: underline; text-decoration-style: dotted;">0</h5>
                                        </div>
                                        <div>
                                            <small class="text-muted d-block">Agencias para actualizar</small>
                                            <h5 class="mb-0 text-primary" id="countAgenciasParaActualizar" role="button" title="Ver detalle" style="cursor:pointer; text-decoration: underline; text-decoration-style: dotted;">0</h5>
                                        </div>
                                    </div>
                                    <div class="d-flex gap-2 flex-wrap">
                                        <button type="button" class="btn btn-sm btn-outline-secondary" id="btnFiltroEstadoTodos">Todos</button>
                                        <button type="button" class="btn btn-sm btn-outline-success" id="btnFiltroEstadoActivos">Activas</button>
                                        <button type="button" class="btn btn-sm btn-outline-danger" id="btnFiltroEstadoInactivos">Inactivas</button>
                                    </div>
                                    <div class="d-flex gap-2 flex-wrap">
                                        <button type="button" class="btn btn-sm btn-outline-secondary" id="btnFiltroEmpresaTodas">Todas empresas</button>
                                        <button type="button" class="btn btn-sm btn-outline-info" id="btnFiltroEmpresaBshSupport">BSH Support</button>
                                        <button type="button" class="btn btn-sm btn-outline-primary" id="btnFiltroEmpresaNegosur">Negosur</button>
                                    </div>
                                </div>
                                <div class="table-responsive">
                                    <table id="tableAgencias" class="table table-bordered table-striped align-middle table-sm" style="width:100%;">
                                        <thead class="table-light">
                                            <tr>
                                                <th class="text-center" style="min-width: 50px;">ID</th>
                                                <th style="min-width: 80px;">Agencia</th>
                                                <th style="min-width: 80px;">Terminal</th>
                                                <th style="min-width: 130px;">Horario AM</th>
                                                <th style="min-width: 130px;">Horario PM</th>
                                                <th style="min-width: 120px;">Nombre</th>
                                                <th style="min-width: 80px;">Sistema</th>
                                                <th style="min-width: 110px;">Empresa</th>
                                                <th style="min-width: 100px;">Ciudad</th>
                                                <th style="min-width: 100px;">Ruta</th>
                                                <th style="min-width: 100px;">Operador</th>
                                                <th style="min-width: 100px;">Coordinador</th>
                                                <th style="min-width: 90px;">Estatus</th>
                                                <th style="min-width: 80px;">Incentivo</th>
                                                <th class="text-center" style="min-width: 80px;">Acciones</th>
                                            </tr>
                                        </thead>
                                        <tbody></tbody>
                                    </table>
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
                        </script> &copy; ERP.
                    </div>
                </div>
            </div>
        </footer>
    </div>

    <!-- Modal para eliminar -->
    <div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="deleteModalLabel">Confirmar eliminaci&oacute;n</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    &iquest;Est&aacute; seguro que desea eliminar esta agencia?
                </div>
                <div class="modal-footer d-flex gap-2">
                    <button type="button" class="btn btn-secondary flex-grow-1" data-bs-dismiss="modal">Cancelar</button>
                    <form id="deleteForm" method="POST" style="display:inline; flex-grow: 1;" class="w-100">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger w-100">Eliminar</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal para importar -->
    <div class="modal fade" id="importModal" tabindex="-1" aria-labelledby="importModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <form action="{{ route('agencias.import') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title" id="importModalLabel">Importar Agencias desde Excel</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="file" class="form-label">Seleccione el archivo Excel</label>
                            <input type="file" class="form-control" id="file" name="file" accept=".xlsx,.xls,.csv" required>
                            <div class="form-text">Formatos aceptados: .xlsx, .xls, .csv</div>
                        </div>
                        <div class="alert alert-info mb-0">
                            <strong class="d-block mb-2">Formato del archivo:</strong>
                            <ul class="mb-0 ps-3">
                                <li>Columna A: Agencia</li>
                                <li>Columna B: Terminal</li>
                                <li>Columna C: Horario AM</li>
                                <li>Columna D: Horario PM</li>
                                <li>Columna E: Nombre Agencia</li>
                                <li>Columna F: Sistema</li>
                                <li>Columna G: Empresa</li>
                                <li>Columna H: Ciudad</li>
                                <li>Columna I: Ruta</li>
                                <li>Columna J: Operador</li>
                                <li>Columna K: Coordinador</li>
                                <li>Columna L: Estatus (1 Activo / 0 Inactivo)</li>
                                <li>Columna M: Aplica Incentivo (SI/NO)</li>
                                <li>Si la terminal ya existe, la fila se omite. Para modificarla use Actualizar masiva.</li>
                            </ul>
                        </div>
                    </div>
                    <div class="modal-footer d-flex gap-2">
                        <a href="{{ route('agencias.template') }}" class="btn btn-outline-primary btn-sm">
                            <i class="ri-download-line me-1"></i>Plantilla
                        </a>
                        <button type="button" class="btn btn-secondary flex-grow-1" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-success flex-grow-1">
                            <i class="ri-upload-2-line me-1"></i>Importar
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal para actualizacion masiva -->
    <div class="modal fade" id="massUpdateModal" tabindex="-1" aria-labelledby="massUpdateModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <form action="{{ route('agencias.mass-update') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title" id="massUpdateModalLabel">Actualizaci&oacute;n masiva de Agencias</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="mass_update_file" class="form-label">Seleccione el archivo Excel</label>
                            <input type="file" class="form-control" id="mass_update_file" name="file" accept=".xlsx,.xls,.csv" required>
                            <div class="form-text">Formatos aceptados: .xlsx, .xls, .csv</div>
                            <div class="d-flex flex-wrap gap-2 mt-2">
                                <button type="button" class="btn btn-outline-info btn-sm" id="btnPreviewTerminalesMasivo">
                                    <i class="ri-search-eye-line me-1"></i>Reconocer terminales
                                </button>
                            </div>
                        </div>

                        <div class="border rounded p-2 mb-3" id="resultadoPreviewMasivo" style="display:none;"></div>

                        <div class="mb-3">
                            <a href="{{ route('agencias.mass-update-template') }}" class="btn btn-outline-primary btn-sm">
                                <i class="ri-download-line me-1"></i>Descargar plantilla de actualizaci&oacute;n
                            </a>
                            <div class="form-text mt-1">Use esta plantilla para actualizar solo los campos que necesite por agencia.</div>
                        </div>

                        <div class="alert alert-warning mb-0">
                            <strong class="d-block mb-2">Reglas de actualizaci&oacute;n:</strong>
                            <ul class="mb-2 ps-3">
                                <li>Para ubicar la agencia, incluya al menos una columna: ID, Terminal o Agencia.</li>
                                <li>Solo se actualizan los campos que tengan valor en cada fila.</li>
                                <li>Si una celda viene vac&iacute;a, ese campo no se modifica.</li>
                                <li>Puede actualizar 1, 2 o m&aacute;s campos en el mismo archivo.</li>
                            </ul>
                            <small class="text-muted">Campos soportados: Agencia, Terminal, Horario AM, Horario PM, Nombre Agencia, Sistema, Empresa, Ciudad, Ruta, Operador, Coordinador, Estatus, Aplica Incentivo.</small>
                        </div>
                    </div>
                    <div class="modal-footer d-flex gap-2">
                        <button type="button" class="btn btn-secondary flex-grow-1" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary flex-grow-1" id="btnEjecutarUpdateMasivo" disabled>
                            <i class="ri-refresh-line me-1"></i>Actualizar masiva
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="noRegistradasModal" tabindex="-1" aria-labelledby="noRegistradasModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="noRegistradasModalLabel">Terminales no registradas con venta fija</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="d-flex justify-content-between align-items-center mb-2 flex-wrap gap-2">
                        <span class="text-muted small" id="noRegistradasRangoTexto">Ultima semana</span>
                        <div class="d-flex align-items-center gap-2 flex-wrap">
                            <span class="badge bg-warning-subtle text-warning-emphasis" id="noRegistradasTotalTexto">0 terminales</span>
                            <button type="button" class="btn btn-sm btn-warning" id="btnRegistrarNoRegistradasMasivo">
                                <i class="ri-add-box-line me-1"></i>Registrar terminales masivo
                            </button>
                        </div>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-sm table-bordered align-middle mb-0" id="tablaNoRegistradas">
                            <thead class="table-light">
                                <tr>
                                    <th style="min-width: 130px;">Terminal</th>
                                    <th class="text-end" style="min-width: 120px;">Dias con venta</th>
                                    <th style="min-width: 120px;">Ultima fecha</th>
                                    <th class="text-center" style="min-width: 90px;">Accion</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                    <div class="small text-muted mt-2" id="sinNoRegistradasTexto" style="display:none;">
                        No hay terminales no registradas para el rango consultado.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="inactivasModal" tabindex="-1" aria-labelledby="inactivasModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="inactivasModalLabel">Agencias inactivas</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="d-flex justify-content-between align-items-start mb-2 flex-wrap gap-2">
                        <span class="text-muted small mt-1" id="inactivasRangoTexto">Agencias desactivadas actualmente</span>
                        <div class="d-flex align-items-center gap-2 flex-wrap">
                            <span class="badge bg-danger-subtle text-danger-emphasis" id="inactivasTotalTexto">0 agencias</span>
                            <button type="button" class="btn btn-sm btn-outline-secondary" id="btnConsultarInactivas">
                                <i class="ri-list-check-2 me-1"></i>Agencias desactivadas
                            </button>
                            <button type="button" class="btn btn-sm btn-outline-danger" id="btnConsultarSinVentas">
                                <i class="ri-search-eye-line me-1"></i>Agencias sin ventas
                            </button>
                            <button type="button" class="btn btn-sm btn-outline-warning" id="btnConsultarInactivasConVentas">
                                <i class="ri-funds-line me-1"></i>Inactivas con ventas
                            </button>
                            <button type="button" class="btn btn-sm btn-outline-info" id="btnConsultarNoRegistradasConVentas">
                                <i class="ri-file-search-line me-1"></i>No registradas con ventas
                            </button>
                            <button type="button" class="btn btn-sm btn-danger" id="btnDesactivarInactivasMasivo" style="display:none;">
                                <i class="ri-forbid-2-line me-1"></i>Desactivar masivo
                            </button>
                        </div>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-sm table-bordered align-middle mb-0" id="tablaInactivas">
                            <thead class="table-light">
                                <tr>
                                    <th style="min-width: 90px;">Agencia</th>
                                    <th style="min-width: 100px;">Terminal</th>
                                    <th style="min-width: 160px;">Nombre</th>
                                    <th style="min-width: 120px;">Empresa</th>
                                    <th style="min-width: 120px;">Ciudad</th>
                                    <th class="text-center" style="min-width: 90px;">Estatus</th>
                                    <th class="text-center" style="min-width: 130px;">Accion</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                    <div class="small text-muted mt-2" id="sinInactivasTexto" style="display:none;">
                        No hay agencias para mostrar en esta consulta.
                    </div>
                    <div class="small text-muted mt-2">
                        Total de agencias en esta consulta: <strong id="inactivasTotalInferior">0</strong>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="paraActualizarModal" tabindex="-1" aria-labelledby="paraActualizarModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="paraActualizarModalLabel">Agencias para actualizar</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="d-flex justify-content-between align-items-center mb-2 flex-wrap gap-2">
                        <span class="text-muted small">Agencias con campos en blanco o incompletos</span>
                        <span class="badge bg-primary-subtle text-primary-emphasis" id="paraActualizarTotalTexto">0 agencias</span>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-sm table-bordered align-middle mb-0" id="tablaParaActualizar">
                            <thead class="table-light">
                                <tr>
                                    <th style="min-width: 90px;">Agencia</th>
                                    <th style="min-width: 110px;">Terminal</th>
                                    <th style="min-width: 160px;">Nombre</th>
                                    <th style="min-width: 260px;">Campos faltantes</th>
                                    <th class="text-center" style="min-width: 90px;">Accion</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                    <div class="small text-muted mt-2" id="sinParaActualizarTexto" style="display:none;">
                        No hay agencias con campos pendientes de actualizar.
                    </div>
                    <div class="small text-muted mt-2">
                        Total de agencias que se van a actualizar: <strong id="paraActualizarTotalInferior">0</strong>
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
    $(document).ready(function() {
        var estadoFiltro = 'todos';
        var empresaFiltro = 'todas';
        var countAgenciasActivas = $('#countAgenciasActivas');
        var countAgenciasInactivas = $('#countAgenciasInactivas');
        var countAgenciasBshSupport = $('#countAgenciasBshSupport');
        var countAgenciasNegosur = $('#countAgenciasNegosur');
        var countAgenciasNoRegistradas = $('#countAgenciasNoRegistradas');
        var countAgenciasParaActualizar = $('#countAgenciasParaActualizar');
        var massUpdateModal = $('#massUpdateModal');
        var massUpdateFile = $('#mass_update_file');
        var btnPreviewTerminalesMasivo = $('#btnPreviewTerminalesMasivo');
        var btnEjecutarUpdateMasivo = $('#btnEjecutarUpdateMasivo');
        var resultadoPreviewMasivo = $('#resultadoPreviewMasivo');
        var formMassUpdate = $('#massUpdateModal form');
        var noRegistradasModal = $('#noRegistradasModal');
        var tablaNoRegistradasBody = $('#tablaNoRegistradas tbody');
        var noRegistradasRangoTexto = $('#noRegistradasRangoTexto');
        var noRegistradasTotalTexto = $('#noRegistradasTotalTexto');
        var sinNoRegistradasTexto = $('#sinNoRegistradasTexto');
        var btnRegistrarNoRegistradasMasivo = $('#btnRegistrarNoRegistradasMasivo');
        var inactivasModal = $('#inactivasModal');
        var inactivasModalLabel = $('#inactivasModalLabel');
        var tablaInactivasBody = $('#tablaInactivas tbody');
        var inactivasRangoTexto = $('#inactivasRangoTexto');
        var inactivasTotalTexto = $('#inactivasTotalTexto');
        var inactivasTotalInferior = $('#inactivasTotalInferior');
        var sinInactivasTexto = $('#sinInactivasTexto');
        var btnConsultarInactivas = $('#btnConsultarInactivas');
        var btnConsultarSinVentas = $('#btnConsultarSinVentas');
        var btnConsultarInactivasConVentas = $('#btnConsultarInactivasConVentas');
        var btnConsultarNoRegistradasConVentas = $('#btnConsultarNoRegistradasConVentas');
        var btnDesactivarInactivasMasivo = $('#btnDesactivarInactivasMasivo');
        var paraActualizarModal = $('#paraActualizarModal');
        var tablaParaActualizarBody = $('#tablaParaActualizar tbody');
        var paraActualizarTotalTexto = $('#paraActualizarTotalTexto');
        var paraActualizarTotalInferior = $('#paraActualizarTotalInferior');
        var sinParaActualizarTexto = $('#sinParaActualizarTexto');
        var previewMasivoStats = null;
        var enviandoMassUpdate = false;
        var noRegistradasStats = null;
        var noRegistradasCargadas = false;
        var inactivasStats = null;
        var inactivasModo = 'inactivas';
        var cargandoInactivas = false;
        var paraActualizarStats = null;
        var paraActualizarCargadas = false;

        function escapeHtml(value) {
            return $('<div>').text(value ?? '').html();
        }

        function renderNoRegistradasModal() {
            var items = (noRegistradasStats && noRegistradasStats.terminales) ? noRegistradasStats.terminales : [];
            var total = Number(noRegistradasStats && noRegistradasStats.total ? noRegistradasStats.total : 0);
            var desde = noRegistradasStats && noRegistradasStats.desde ? noRegistradasStats.desde : '';
            var hasta = noRegistradasStats && noRegistradasStats.hasta ? noRegistradasStats.hasta : '';

            noRegistradasRangoTexto.text(desde && hasta ? ('Rango: ' + desde + ' a ' + hasta) : 'Ultima semana');
            noRegistradasTotalTexto.text(total.toLocaleString('es-DO') + ' terminales');

            if (!items.length) {
                tablaNoRegistradasBody.html('');
                sinNoRegistradasTexto.show();
                btnRegistrarNoRegistradasMasivo.prop('disabled', true);
                return;
            }

            sinNoRegistradasTexto.hide();
            btnRegistrarNoRegistradasMasivo.prop('disabled', false);

            var rowsHtml = items.map(function(item) {
                var terminal = item.terminal || '';
                return `
                    <tr>
                        <td>${escapeHtml(terminal || '-')}</td>
                        <td class="text-end">${Number(item.dias_con_venta || 0).toLocaleString('es-DO')}</td>
                        <td>${escapeHtml(item.ultima_fecha || '-')}</td>
                        <td class="text-center">
                            <button type="button" class="btn btn-sm btn-success btn-registrar-terminal-no-registrada" data-terminal="${escapeHtml(terminal)}" title="Registrar terminal">
                                <i class="ri-add-line"></i>
                            </button>
                        </td>
                    </tr>
                `;
            }).join('');

            tablaNoRegistradasBody.html(rowsHtml);
        }

        function renderParaActualizarModal() {
            var items = (paraActualizarStats && paraActualizarStats.agencias) ? paraActualizarStats.agencias : [];
            var total = Number(paraActualizarStats && paraActualizarStats.total ? paraActualizarStats.total : 0);

            paraActualizarTotalTexto.text(total.toLocaleString('es-DO') + ' agencias');
            paraActualizarTotalInferior.text(total.toLocaleString('es-DO'));

            if (!items.length) {
                tablaParaActualizarBody.html('');
                sinParaActualizarTexto.show();
                return;
            }

            sinParaActualizarTexto.hide();

            var rowsHtml = items.map(function(item) {
                var campos = Array.isArray(item.campos_faltantes) ? item.campos_faltantes : [];
                var camposHtml = campos.length
                    ? campos.map(function(campo) {
                        return '<span class="badge bg-warning-subtle text-warning-emphasis me-1 mb-1">' + escapeHtml(campo) + '</span>';
                    }).join('')
                    : '<span class="text-muted">-</span>';

                return `
                    <tr>
                        <td>${escapeHtml(item.agencia || '-')}</td>
                        <td>${escapeHtml(item.terminal || '-')}</td>
                        <td>${escapeHtml(item.nombre_agencia || '-')}</td>
                        <td>${camposHtml}</td>
                        <td class="text-center">
                            <a href="${escapeHtml(item.edit_url || '#')}" class="btn btn-sm btn-success" title="Editar">
                                <i class="ri-pencil-line"></i>
                            </a>
                        </td>
                    </tr>
                `;
            }).join('');

            tablaParaActualizarBody.html(rowsHtml);
        }

        function renderInactivasModal() {
            var items = (inactivasStats && inactivasStats.agencias) ? inactivasStats.agencias : [];
            var total = Number(inactivasStats && inactivasStats.total ? inactivasStats.total : 0);
            var activas = Number(inactivasStats && inactivasStats.activas ? inactivasStats.activas : 0);
            var desde = inactivasStats && inactivasStats.desde ? inactivasStats.desde : '';
            var hasta = inactivasStats && inactivasStats.hasta ? inactivasStats.hasta : '';

            var config = {
                inactivas: {
                    titulo: 'Agencias inactivas',
                    descripcion: 'Agencias desactivadas actualmente',
                    vacio: 'No hay agencias desactivadas actualmente.',
                    badge: 'bg-danger-subtle text-danger-emphasis'
                },
                sin_venta: {
                    titulo: 'Agencias activas sin venta',
                    descripcion: 'Agencias activas sin venta positiva en los ultimos 30 dias',
                    vacio: 'No hay agencias activas sin venta en los ultimos 30 dias.',
                    badge: 'bg-warning-subtle text-warning-emphasis'
                },
                inactivas_con_venta: {
                    titulo: 'Inactivas con ventas',
                    descripcion: 'Agencias desactivadas con venta positiva en los ultimos 30 dias',
                    vacio: 'No hay agencias inactivas con ventas en los ultimos 30 dias.',
                    badge: 'bg-info-subtle text-info-emphasis'
                },
                no_registradas_con_venta: {
                    titulo: 'No registradas con ventas',
                    descripcion: 'Terminales con venta positiva que no existen en agencias en los ultimos 30 dias',
                    vacio: 'No hay terminales no registradas con ventas en los ultimos 30 dias.',
                    badge: 'bg-warning-subtle text-warning-emphasis'
                }
            }[inactivasModo] || {};

            inactivasModalLabel.text(config.titulo || 'Agencias inactivas');
            inactivasRangoTexto.text(desde && hasta ? ((config.descripcion || 'Consulta') + ' | Rango: ' + desde + ' a ' + hasta) : (config.descripcion || 'Agencias desactivadas actualmente'));
            inactivasTotalTexto.text(total.toLocaleString('es-DO') + ' agencias');
            inactivasTotalTexto
                .removeClass('bg-danger-subtle text-danger-emphasis bg-warning-subtle text-warning-emphasis bg-info-subtle text-info-emphasis')
                .addClass(config.badge || 'bg-danger-subtle text-danger-emphasis');
            inactivasTotalInferior.text(total.toLocaleString('es-DO'));
            sinInactivasTexto.text(config.vacio || 'No hay agencias para mostrar en esta consulta.');
            btnDesactivarInactivasMasivo
                .toggle(inactivasModo === 'sin_venta')
                .prop('disabled', inactivasModo !== 'sin_venta' || activas <= 0 || cargandoInactivas);

            btnConsultarInactivas.toggleClass('active', inactivasModo === 'inactivas');
            btnConsultarSinVentas.toggleClass('active', inactivasModo === 'sin_venta');
            btnConsultarInactivasConVentas.toggleClass('active', inactivasModo === 'inactivas_con_venta');
            btnConsultarNoRegistradasConVentas.toggleClass('active', inactivasModo === 'no_registradas_con_venta');

            if (cargandoInactivas && !items.length) {
                tablaInactivasBody.html('<tr><td colspan="7" class="text-center text-muted py-3">Consultando datos...</td></tr>');
                sinInactivasTexto.hide();
                return;
            }

            if (!items.length) {
                tablaInactivasBody.html('');
                sinInactivasTexto.show();
                return;
            }

            sinInactivasTexto.hide();

            var rowsHtml = items.map(function(item) {
                if (item.no_registrada) {
                    var totalVenta = Number(item.total_venta || 0).toLocaleString('es-DO', {
                        minimumFractionDigits: 2,
                        maximumFractionDigits: 2
                    });
                    var diasVenta = Number(item.dias_con_venta || 0).toLocaleString('es-DO');
                    var ultimaFecha = item.ultima_fecha || '-';

                    return `
                        <tr>
                            <td>${escapeHtml(item.agencia || '-')}</td>
                            <td>${escapeHtml(item.terminal || '-')}</td>
                            <td>
                                ${escapeHtml(item.nombre_agencia || 'Terminal no registrada')}
                                <div class="small text-muted">Venta: RD$ ${totalVenta} | Dias: ${diasVenta} | Ultima: ${escapeHtml(ultimaFecha)}</div>
                            </td>
                            <td>${escapeHtml(item.empresa || '-')}</td>
                            <td>${escapeHtml(item.ciudad || '-')}</td>
                            <td class="text-center"><span class="badge bg-warning">No registrada</span></td>
                            <td class="text-center">
                                <button type="button" class="btn btn-sm btn-warning btn-registrar-terminal-no-registrada" data-terminal="${escapeHtml(item.terminal || '')}">
                                    Registrar
                                </button>
                            </td>
                        </tr>
                    `;
                }

                var estatus = Number(item.estatus || 0);
                var nuevoEstatus = estatus === 1 ? 0 : 1;
                var accionTexto = estatus === 1 ? 'Desactivar' : 'Activar';
                var accionClase = estatus === 1 ? 'btn-danger' : 'btn-success';
                var badgeClase = estatus === 1 ? 'bg-success' : 'bg-danger';
                var badgeTexto = estatus === 1 ? 'Activa' : 'Inactiva';

                return `
                    <tr>
                        <td>${escapeHtml(item.agencia || '-')}</td>
                        <td>${escapeHtml(item.terminal || '-')}</td>
                        <td>${escapeHtml(item.nombre_agencia || '-')}</td>
                        <td>${escapeHtml(item.empresa || '-')}</td>
                        <td>${escapeHtml(item.ciudad || '-')}</td>
                        <td class="text-center"><span class="badge ${badgeClase}">${badgeTexto}</span></td>
                        <td class="text-center">
                            <button type="button" class="btn btn-sm ${accionClase} btn-actualizar-estatus-agencia" data-agencia-id="${escapeHtml(item.id || '')}" data-estatus="${nuevoEstatus}">
                                ${accionTexto}
                            </button>
                        </td>
                    </tr>
                `;
            }).join('');

            tablaInactivasBody.html(rowsHtml);
        }

        function cargarNoRegistradasVentaFija(mostrarError) {
            $.ajax({
                url: '{{ route('agencias.no-registradas-venta-fija-semana') }}',
                method: 'GET',
                success: function(response) {
                    noRegistradasStats = response || null;
                    var total = Number(response && response.total ? response.total : 0);
                    countAgenciasNoRegistradas.text(total.toLocaleString('es-DO'));

                    if (noRegistradasModal.hasClass('show')) {
                        renderNoRegistradasModal();
                    }
                },
                error: function() {
                    noRegistradasStats = null;
                    countAgenciasNoRegistradas.text('0');

                    if (!mostrarError) {
                        return;
                    }

                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'No se pudo cargar el listado de terminales no registradas.'
                    });
                }
            });
        }

        function cargarAgenciasParaActualizar(mostrarError) {
            $.ajax({
                url: '{{ route('agencias.para-actualizar') }}',
                method: 'GET',
                success: function(response) {
                    paraActualizarStats = response || null;
                    var total = Number(response && response.total ? response.total : 0);
                    countAgenciasParaActualizar.text(total.toLocaleString('es-DO'));

                    if (paraActualizarModal.hasClass('show')) {
                        renderParaActualizarModal();
                    }
                },
                error: function() {
                    paraActualizarStats = null;
                    countAgenciasParaActualizar.text('0');

                    if (!mostrarError) {
                        return;
                    }

                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'No se pudo cargar el listado de agencias para actualizar.'
                    });
                }
            });
        }

        function prepararCargaInactivas(modo) {
            inactivasModo = modo;
            cargandoInactivas = true;
            inactivasStats = null;

            if (inactivasModal.hasClass('show')) {
                renderInactivasModal();
            }
        }

        function finalizarCargaInactivas(response, modo) {
            if (modo && inactivasModo !== modo) {
                return;
            }

            cargandoInactivas = false;
            inactivasStats = response || null;

            if (inactivasModal.hasClass('show')) {
                renderInactivasModal();
            }
        }

        function manejarErrorCargaInactivas(mostrarError, mensaje, modo) {
            if (modo && inactivasModo !== modo) {
                return;
            }

            cargandoInactivas = false;
            inactivasStats = null;

            if (inactivasModal.hasClass('show')) {
                renderInactivasModal();
            }

            if (!mostrarError) {
                return;
            }

            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: mensaje
            });
        }

        function cargarAgenciasInactivas(mostrarError) {
            prepararCargaInactivas('inactivas');

            $.ajax({
                url: '{{ route('agencias.inactivas') }}',
                method: 'GET',
                success: function(response) {
                    finalizarCargaInactivas(response, 'inactivas');
                },
                error: function() {
                    manejarErrorCargaInactivas(mostrarError, 'No se pudo cargar el listado de agencias desactivadas.', 'inactivas');
                }
            });
        }

        function cargarAgenciasSinVentaTreintaDias(mostrarError) {
            prepararCargaInactivas('sin_venta');

            $.ajax({
                url: '{{ route('agencias.sin-venta-30-dias') }}',
                method: 'GET',
                success: function(response) {
                    finalizarCargaInactivas(response, 'sin_venta');
                },
                error: function() {
                    manejarErrorCargaInactivas(mostrarError, 'No se pudo cargar el listado de agencias sin venta.', 'sin_venta');
                }
            });
        }

        function cargarAgenciasInactivasConVentas(mostrarError) {
            prepararCargaInactivas('inactivas_con_venta');

            $.ajax({
                url: '{{ route('agencias.inactivas-con-venta-30-dias') }}',
                method: 'GET',
                success: function(response) {
                    finalizarCargaInactivas(response, 'inactivas_con_venta');
                },
                error: function() {
                    manejarErrorCargaInactivas(mostrarError, 'No se pudo cargar el listado de agencias inactivas con ventas.', 'inactivas_con_venta');
                }
            });
        }

        function cargarAgenciasNoRegistradasConVentas(mostrarError) {
            prepararCargaInactivas('no_registradas_con_venta');

            $.ajax({
                url: '{{ route('agencias.no-registradas-con-venta-30-dias') }}',
                method: 'GET',
                success: function(response) {
                    finalizarCargaInactivas(response, 'no_registradas_con_venta');
                },
                error: function() {
                    manejarErrorCargaInactivas(mostrarError, 'No se pudo cargar el listado de terminales no registradas con ventas.', 'no_registradas_con_venta');
                }
            });
        }

        function refrescarDespuesRegistroNoRegistradas(terminalRegistrada) {
            cargarNoRegistradasVentaFija(false);
            if (inactivasModo === 'no_registradas_con_venta') {
                if (terminalRegistrada) {
                    eliminarTerminalInactivasLocal(terminalRegistrada);
                } else {
                    cargarAgenciasNoRegistradasConVentas(false);
                }
            }
            cargarAgenciasParaActualizar(false);
            table.ajax.reload(null, false);
        }

        function eliminarTerminalInactivasLocal(terminal) {
            if (!inactivasStats || !Array.isArray(inactivasStats.agencias)) {
                return;
            }

            var terminalStr = String(terminal || '').trim();
            if (!terminalStr) {
                return;
            }

            inactivasStats.agencias = inactivasStats.agencias.filter(function(item) {
                return String(item.terminal || '').trim() !== terminalStr;
            });
            inactivasStats.total = inactivasStats.agencias.length;

            renderInactivasModal();
        }

        function actualizarConteoInactivasLocal(estatusNuevo, cantidad) {
            cantidad = Number(cantidad || 1);
            var conteoActual = Number((countAgenciasInactivas.text() || '0').replace(/[^\d]/g, '')) || 0;
            var nuevoConteo = Number(estatusNuevo) === 1
                ? Math.max(conteoActual - cantidad, 0)
                : conteoActual + cantidad;

            countAgenciasInactivas.text(nuevoConteo.toLocaleString('es-DO'));
        }

        function actualizarListadoInactivasLocal(agenciaId, estatusNuevo) {
            if (!inactivasStats || !Array.isArray(inactivasStats.agencias)) {
                return;
            }

            agenciaId = Number(agenciaId);
            estatusNuevo = Number(estatusNuevo);
            inactivasStats.agencias = inactivasStats.agencias.filter(function(item) {
                return Number(item.id) !== agenciaId;
            });
            inactivasStats.total = inactivasStats.agencias.length;
            inactivasStats.activas = inactivasStats.agencias.filter(function(item) {
                return Number(item.estatus) === 1;
            }).length;
            inactivasStats.inactivas = inactivasStats.agencias.filter(function(item) {
                return Number(item.estatus) === 0;
            }).length;

            actualizarConteoInactivasLocal(estatusNuevo);
            renderInactivasModal();
        }

        function desactivarAgenciasSinVentaMasivo() {
            if (inactivasModo !== 'sin_venta') {
                Swal.fire({
                    icon: 'info',
                    title: 'Consulta requerida',
                    text: 'Primero ejecuta la consulta de agencias sin ventas.'
                });
                return;
            }

            var activas = Number(inactivasStats && inactivasStats.activas ? inactivasStats.activas : 0);

            if (!activas) {
                Swal.fire({
                    icon: 'info',
                    title: 'Sin agencias activas',
                    text: 'No hay agencias activas para desactivar en este listado.'
                });
                return;
            }

            Swal.fire({
                icon: 'warning',
                title: 'Desactivar agencias',
                text: 'Se desactivaran ' + activas.toLocaleString('es-DO') + ' agencias activas sin venta en los ultimos 30 dias.',
                showCancelButton: true,
                confirmButtonText: 'Desactivar',
                cancelButtonText: 'Cancelar',
                confirmButtonColor: '#f06548'
            }).then(function(result) {
                if (!result.isConfirmed) {
                    return;
                }

                btnDesactivarInactivasMasivo.prop('disabled', true);

                Swal.fire({
                    title: 'Desactivando agencias',
                    text: 'Procesando agencias sin venta...',
                    allowOutsideClick: false,
                    allowEscapeKey: false,
                    didOpen: function() {
                        Swal.showLoading();
                    }
                });

                $.ajax({
                    url: '{{ route('agencias.sin-venta-30-dias.desactivar') }}',
                    method: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}'
                    },
                    success: function(response) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Proceso finalizado',
                            html: `
                                <div class="text-start">
                                    <div><strong>Agencias desactivadas:</strong> ${Number(response.desactivadas || 0).toLocaleString('es-DO')}</div>
                                    <div><strong>Omitidas:</strong> ${Number(response.omitidas || 0).toLocaleString('es-DO')}</div>
                                </div>
                            `,
                            confirmButtonText: 'Entendido'
                        });
                        if (inactivasStats && Array.isArray(inactivasStats.agencias)) {
                            inactivasStats.agencias = [];
                            inactivasStats.total = 0;
                            inactivasStats.activas = 0;
                            inactivasStats.inactivas = 0;
                            actualizarConteoInactivasLocal(0, Number(response.desactivadas || 0));
                            renderInactivasModal();
                        }
                    },
                    error: function(xhr) {
                        var msg = xhr?.responseJSON?.message || 'No se pudieron desactivar las agencias.';
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: msg
                        });
                        btnDesactivarInactivasMasivo.prop('disabled', false);
                    }
                });
            });
        }

        function ejecutarActualizacionEstatusAgencia(agenciaId, estatus, button) {
            var activar = Number(estatus) === 1;
            var btn = $(button);
            btn.prop('disabled', true);

            $.ajax({
                url: '{{ route('agencias.actualizar-estatus') }}',
                method: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    agencia_id: agenciaId,
                    estatus: estatus
                },
                success: function() {
                    actualizarListadoInactivasLocal(agenciaId, estatus);
                    Swal.fire({
                        icon: 'success',
                        title: activar ? 'Agencia activada' : 'Agencia desactivada',
                        timer: 1200,
                        showConfirmButton: false
                    });
                },
                error: function(xhr) {
                    var msg = xhr?.responseJSON?.message || 'No se pudo actualizar el estatus de la agencia.';
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: msg
                    });
                    btn.prop('disabled', false);
                }
            });
        }

        function actualizarEstatusAgenciaDesdeModal(agenciaId, estatus, button) {
            var activar = Number(estatus) === 1;

            if (activar) {
                ejecutarActualizacionEstatusAgencia(agenciaId, estatus, button);
                return;
            }

            Swal.fire({
                icon: 'question',
                title: 'Desactivar agencia',
                text: 'Esta agencia quedara inactiva.',
                showCancelButton: true,
                confirmButtonText: 'Desactivar',
                cancelButtonText: 'Cancelar',
                confirmButtonColor: '#f06548'
            }).then(function(result) {
                if (!result.isConfirmed) {
                    return;
                }

                ejecutarActualizacionEstatusAgencia(agenciaId, estatus, button);
            });
        }

        function registrarNoRegistradasMasivo() {
            var total = Number(noRegistradasStats && noRegistradasStats.total ? noRegistradasStats.total : 0);

            if (!total) {
                Swal.fire({
                    icon: 'info',
                    title: 'Sin terminales',
                    text: 'No hay terminales no registradas para registrar.'
                });
                return;
            }

            Swal.fire({
                icon: 'question',
                title: 'Registrar terminales masivo',
                text: 'Se registraran ' + total.toLocaleString('es-DO') + ' terminales pendientes sin alterar el codigo de agencia.',
                showCancelButton: true,
                confirmButtonText: 'Registrar',
                cancelButtonText: 'Cancelar',
                confirmButtonColor: '#f7b84b'
            }).then(function(result) {
                if (!result.isConfirmed) {
                    return;
                }

                btnRegistrarNoRegistradasMasivo.prop('disabled', true);

                Swal.fire({
                    title: 'Registrando agencias',
                    text: 'Procesando terminales no registradas...',
                    allowOutsideClick: false,
                    allowEscapeKey: false,
                    didOpen: function() {
                        Swal.showLoading();
                    }
                });

                $.ajax({
                    url: '{{ route('agencias.no-registradas.registrar') }}',
                    method: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}'
                    },
                    success: function(response) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Registro masivo finalizado',
                            html: `
                                <div class="text-start">
                                    <div><strong>Agencias registradas:</strong> ${Number(response.registradas || 0).toLocaleString('es-DO')}</div>
                                    <div><strong>Omitidas:</strong> ${Number(response.omitidas || 0).toLocaleString('es-DO')}</div>
                                </div>
                            `,
                            confirmButtonText: 'Entendido'
                        });
                        refrescarDespuesRegistroNoRegistradas();
                    },
                    error: function(xhr) {
                        var msg = xhr?.responseJSON?.message || 'No se pudieron registrar las terminales.';
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: msg
                        });
                        btnRegistrarNoRegistradasMasivo.prop('disabled', false);
                    }
                });
            });
        }

        function registrarTerminalNoRegistrada(terminal, button) {
            if (!terminal) {
                return;
            }

            Swal.fire({
                icon: 'question',
                title: 'Registrar terminal',
                text: 'Se registrara la terminal ' + terminal + ' sin asignar codigo de agencia.',
                showCancelButton: true,
                confirmButtonText: 'Registrar',
                cancelButtonText: 'Cancelar',
                confirmButtonColor: '#0ab39c'
            }).then(function(result) {
                if (!result.isConfirmed) {
                    return;
                }

                var btn = $(button);
                btn.prop('disabled', true);

                $.ajax({
                    url: '{{ route('agencias.no-registradas.registrar-terminal') }}',
                    method: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}',
                        terminal: terminal
                    },
                    success: function(response) {
                        var registradas = Number(response.registradas || 0);
                        Swal.fire({
                            icon: registradas > 0 ? 'success' : 'info',
                            title: registradas > 0 ? 'Terminal registrada' : 'Terminal omitida',
                            text: registradas > 0
                                ? 'La terminal ' + terminal + ' fue registrada sin codigo de agencia.'
                                : 'La terminal ' + terminal + ' ya existe o no pudo registrarse.'
                        });
                        refrescarDespuesRegistroNoRegistradas(terminal);
                    },
                    error: function(xhr) {
                        var msg = xhr?.responseJSON?.message || 'No se pudo registrar la terminal.';
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: msg
                        });
                        btn.prop('disabled', false);
                    }
                });
            });
        }

        function aplicarEstadoBotones() {
            $('#btnFiltroEstadoTodos').removeClass('btn-secondary').addClass('btn-outline-secondary');
            $('#btnFiltroEstadoActivos').removeClass('btn-success').addClass('btn-outline-success');
            $('#btnFiltroEstadoInactivos').removeClass('btn-danger').addClass('btn-outline-danger');

            if (estadoFiltro === 'activo') {
                $('#btnFiltroEstadoActivos').removeClass('btn-outline-success').addClass('btn-success');
                return;
            }

            if (estadoFiltro === 'inactivo') {
                $('#btnFiltroEstadoInactivos').removeClass('btn-outline-danger').addClass('btn-danger');
                return;
            }

            $('#btnFiltroEstadoTodos').removeClass('btn-outline-secondary').addClass('btn-secondary');
        }

        function aplicarEmpresaBotones() {
            $('#btnFiltroEmpresaTodas').removeClass('btn-secondary').addClass('btn-outline-secondary');
            $('#btnFiltroEmpresaBshSupport').removeClass('btn-info').addClass('btn-outline-info');
            $('#btnFiltroEmpresaNegosur').removeClass('btn-primary').addClass('btn-outline-primary');

            if (empresaFiltro === 'bsh_support') {
                $('#btnFiltroEmpresaBshSupport').removeClass('btn-outline-info').addClass('btn-info');
                return;
            }

            if (empresaFiltro === 'negosur') {
                $('#btnFiltroEmpresaNegosur').removeClass('btn-outline-primary').addClass('btn-primary');
                return;
            }

            $('#btnFiltroEmpresaTodas').removeClass('btn-outline-secondary').addClass('btn-secondary');
        }

        // Configuracion responsive de DataTables
        var responsiveColumns = [
            { targets: 3, visible: false },  // Horario AM (oculta para ganar espacio)
            { targets: 4, visible: false },  // Horario PM
            { targets: 10, visible: false },  // Operador
            { targets: 11, visible: false }  // Coordinador
        ];

        // En movil, ocultar mas columnas
        if ($(window).width() < 768) {
            responsiveColumns = [
                { targets: 3, visible: false },  // Horario AM
                { targets: 4, visible: false },  // Horario PM
                { targets: 6, visible: false },  // Sistema
                { targets: 9, visible: false },  // Ruta
                { targets: 10, visible: false },  // Operador
                { targets: 11, visible: false }  // Coordinador
            ];
        }

        var table = $('#tableAgencias').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: '{{ route('agencias.list') }}',
                data: function(d) {
                    d.estatus_filter = estadoFiltro;
                    d.empresa_filter = empresaFiltro;
                },
                dataSrc: function(json) {
                    countAgenciasActivas.text((json.total_activas || 0).toLocaleString('es-DO'));
                    countAgenciasInactivas.text((json.total_inactivas || 0).toLocaleString('es-DO'));
                    countAgenciasBshSupport.text((json.total_bsh_support || 0).toLocaleString('es-DO'));
                    countAgenciasNegosur.text((json.total_negosur || 0).toLocaleString('es-DO'));
                    return json.data || [];
                }
            },
            responsive: true,
            columnDefs: responsiveColumns,
            scrollX: true,
            columns: [
                { data: 'id', name: 'id', className: 'text-center' },
                { data: 'agencia', name: 'agencia' },
                { data: 'terminal', name: 'terminal', defaultContent: '-' },
                { data: 'horario_am', name: 'horario_am', defaultContent: '-' },
                { data: 'horario_pm', name: 'horario_pm', defaultContent: '-' },
                { data: 'nombre_agencia', name: 'nombre_agencia', defaultContent: '-' },
                { data: 'sistema', name: 'sistema', defaultContent: '-' },
                { data: 'empresa', name: 'empresa', defaultContent: '-' },
                { data: 'ciudad', name: 'ciudad', defaultContent: '-' },
                { data: 'ruta', name: 'ruta', defaultContent: '-' },
                { data: 'operador', name: 'operador', defaultContent: '-' },
                { data: 'coordinador', name: 'coordinador', defaultContent: '-' },
                {
                    data: 'estatus',
                    name: 'estatus',
                    className: 'text-center',
                    render: function(data) {
                        return Number(data) === 1
                            ? '<span class="badge bg-success">Activo</span>'
                            : '<span class="badge bg-danger">Inactivo</span>';
                    }
                },
                {
                    data: 'aplica_incentivo',
                    name: 'aplica_incentivo',
                    className: 'text-center',
                    render: function(data) {
                        return data ? '<span class="badge bg-success">S&iacute;</span>' : '<span class="badge bg-secondary">No</span>';
                    }
                },
                {
                    data: null,
                    orderable: false,
                    searchable: false,
                    className: 'text-center',
                    render: function(data, type, row) {
                        return `
                            <div class="d-flex gap-1 justify-content-center flex-nowrap">
                                <a href="/agencias/${row.id}/edit" class="btn btn-sm btn-success" title="Editar">
                                    <i class="ri-pencil-line"></i>
                                </a>
                                <button class="btn btn-sm btn-danger btn-delete" data-id="${row.id}" title="Eliminar">
                                    <i class="ri-delete-bin-line"></i>
                                </button>
                            </div>
                        `;
                    }
                }
            ],
            language: {
                url: '//cdn.datatables.net/plug-ins/1.11.5/i18n/es-ES.json'
            },
            dom: '<"row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6"f>>rtip',
            initComplete: function() {
                // Carga secuencial: primero DataTable, luego no registradas para evitar sensacion de bloqueo.
                if (!noRegistradasCargadas) {
                    noRegistradasCargadas = true;
                    window.setTimeout(function() {
                        cargarNoRegistradasVentaFija(false);
                    }, 120);
                }

                if (!paraActualizarCargadas) {
                    paraActualizarCargadas = true;
                    window.setTimeout(function() {
                        cargarAgenciasParaActualizar(false);
                    }, 220);
                }
            }
        });

        aplicarEstadoBotones();
        aplicarEmpresaBotones();

        countAgenciasInactivas.on('click', function() {
            inactivasModal.modal('show');
        });

        countAgenciasNoRegistradas.on('click', function() {
            if (!noRegistradasStats) {
                Swal.fire({
                    icon: 'info',
                    title: 'Consultando datos',
                    text: 'Se esta cargando el detalle. Intente nuevamente en unos segundos.'
                });
                cargarNoRegistradasVentaFija(false);
                return;
            }

            renderNoRegistradasModal();
            noRegistradasModal.modal('show');
        });

        btnRegistrarNoRegistradasMasivo.on('click', function() {
            registrarNoRegistradasMasivo();
        });

        tablaNoRegistradasBody.on('click', '.btn-registrar-terminal-no-registrada', function() {
            registrarTerminalNoRegistrada($(this).data('terminal'), this);
        });

        btnConsultarInactivas.on('click', function() {
            cargarAgenciasInactivas(true);
        });

        btnConsultarSinVentas.on('click', function() {
            cargarAgenciasSinVentaTreintaDias(true);
        });

        btnConsultarInactivasConVentas.on('click', function() {
            cargarAgenciasInactivasConVentas(true);
        });

        btnConsultarNoRegistradasConVentas.on('click', function() {
            cargarAgenciasNoRegistradasConVentas(true);
        });

        btnDesactivarInactivasMasivo.on('click', function() {
            desactivarAgenciasSinVentaMasivo();
        });

        tablaInactivasBody.on('click', '.btn-actualizar-estatus-agencia', function() {
            actualizarEstatusAgenciaDesdeModal($(this).data('agencia-id'), $(this).data('estatus'), this);
        });

        tablaInactivasBody.on('click', '.btn-registrar-terminal-no-registrada', function() {
            registrarTerminalNoRegistrada($(this).data('terminal'), this);
        });

        countAgenciasParaActualizar.on('click', function() {
            if (!paraActualizarStats) {
                Swal.fire({
                    icon: 'info',
                    title: 'Consultando datos',
                    text: 'Se esta cargando el detalle. Intente nuevamente en unos segundos.'
                });
                cargarAgenciasParaActualizar(false);
                return;
            }

            renderParaActualizarModal();
            paraActualizarModal.modal('show');
        });

        $('#btnFiltroEstadoTodos').on('click', function() {
            estadoFiltro = 'todos';
            aplicarEstadoBotones();
            table.ajax.reload();
        });

        $('#btnFiltroEstadoActivos').on('click', function() {
            estadoFiltro = 'activo';
            aplicarEstadoBotones();
            table.ajax.reload();
        });

        $('#btnFiltroEstadoInactivos').on('click', function() {
            estadoFiltro = 'inactivo';
            aplicarEstadoBotones();
            table.ajax.reload();
        });

        $('#btnFiltroEmpresaTodas').on('click', function() {
            empresaFiltro = 'todas';
            aplicarEmpresaBotones();
            table.ajax.reload();
        });

        $('#btnFiltroEmpresaBshSupport').on('click', function() {
            empresaFiltro = 'bsh_support';
            aplicarEmpresaBotones();
            table.ajax.reload();
        });

        $('#btnFiltroEmpresaNegosur').on('click', function() {
            empresaFiltro = 'negosur';
            aplicarEmpresaBotones();
            table.ajax.reload();
        });

        noRegistradasModal.on('show.bs.modal', function() {
            if (!noRegistradasStats) {
                cargarNoRegistradasVentaFija(false);
                return;
            }

            renderNoRegistradasModal();
        });

        inactivasModal.on('show.bs.modal', function() {
            cargarAgenciasInactivas(false);
        });

        paraActualizarModal.on('show.bs.modal', function() {
            if (!paraActualizarStats) {
                cargarAgenciasParaActualizar(false);
                return;
            }

            renderParaActualizarModal();
        });

        // Manejar eliminacion
        $('#tableAgencias').on('click', '.btn-delete', function() {
            var id = $(this).data('id');
            var form = $('#deleteForm');
            form.attr('action', '/agencias/' + id);
            $('#deleteModal').modal('show');
        });

        // Mostrar mensaje de exito si existe
        @if(session('success') && !session('mass_update_result') && !session('import_result'))
            Swal.fire({
                icon: 'success',
                title: '\u00A1\u00C9xito!',
                text: '{{ session('success') }}',
                timer: 3000,
                showConfirmButton: false
            });
        @endif

        // Resumen de actualizacion masiva con conteo
        @if(session('import_result'))
            const importResult = @json(session('import_result'));
            Swal.fire({
                icon: Number(importResult.omitidas || 0) > 0 ? 'info' : 'success',
                title: 'Importacion finalizada',
                html: `
                    <div class="text-start">
                        <p class="mb-2">Resumen del archivo procesado:</p>
                        <ul class="mb-0 ps-3">
                            <li>Agencias creadas: <strong>${Number(importResult.importadas || 0).toLocaleString('es-DO')}</strong></li>
                            <li>Omitidas por terminal existente: <strong>${Number(importResult.omitidas_existentes || 0).toLocaleString('es-DO')}</strong></li>
                            <li>Omitidas por terminal repetida en el archivo: <strong>${Number(importResult.omitidas_duplicadas_archivo || 0).toLocaleString('es-DO')}</strong></li>
                            <li>Omitidas sin terminal: <strong>${Number(importResult.omitidas_sin_terminal || 0).toLocaleString('es-DO')}</strong></li>
                        </ul>
                    </div>
                `,
                confirmButtonText: 'Entendido',
                confirmButtonColor: '#0ab39c'
            });
        @endif

        @if(session('mass_update_result'))
            const massUpdateResult = @json(session('mass_update_result'));
            Swal.fire({
                icon: 'info',
                title: 'Actualizaci&oacute;n masiva finalizada',
                html: `
                    <div class="text-start">
                        <p class="mb-2">Resumen del archivo procesado:</p>
                        <ul class="mb-0 ps-3">
                            <li>Filas procesadas: <strong>${Number(massUpdateResult.procesadas || 0).toLocaleString('es-DO')}</strong></li>
                            <li>Agencias actualizadas: <strong>${Number(massUpdateResult.actualizadas || 0).toLocaleString('es-DO')}</strong></li>
                            <li>Sin cambios: <strong>${Number(massUpdateResult.sin_cambios || 0).toLocaleString('es-DO')}</strong></li>
                            <li>No encontradas: <strong>${Number(massUpdateResult.no_encontradas || 0).toLocaleString('es-DO')}</strong></li>
                            <li>Filas inv&aacute;lidas: <strong>${Number(massUpdateResult.invalidas || 0).toLocaleString('es-DO')}</strong></li>
                        </ul>
                    </div>
                `,
                confirmButtonText: 'Entendido',
                confirmButtonColor: '#0ab39c'
            });
        @endif

        // Mostrar mensaje de error si existe
        @if(session('error'))
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: '{{ session('error') }}'
            });
        @endif

        // Mostrar errores de validacion
        @if($errors->any())
            Swal.fire({
                icon: 'error',
                title: 'Error de validaci\u00F3n',
                html: `{!! implode('<br>', $errors->all()) !!}`
            });
        @endif

        // Reajustar columnas en resize
        $(window).on('resize', function() {
            table.columns.adjust().draw();
        });

        massUpdateModal.on('show.bs.modal', function() {
            btnEjecutarUpdateMasivo.prop('disabled', true);
            resultadoPreviewMasivo.hide().html('');
            previewMasivoStats = null;
            enviandoMassUpdate = false;
        });

        massUpdateFile.on('change', function() {
            btnEjecutarUpdateMasivo.prop('disabled', true);
            resultadoPreviewMasivo.hide().html('');
            previewMasivoStats = null;
            enviandoMassUpdate = false;
        });

        btnPreviewTerminalesMasivo.on('click', function() {
            var fileInput = massUpdateFile[0];
            if (!fileInput || !fileInput.files || !fileInput.files.length) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Archivo requerido',
                    text: 'Selecciona un archivo antes de reconocer terminales.'
                });
                return;
            }

            var formData = new FormData();
            formData.append('file', fileInput.files[0]);
            formData.append('_token', '{{ csrf_token() }}');

            Swal.fire({
                title: 'Reconociendo terminales',
                text: 'Analizando archivo, por favor espere...',
                allowOutsideClick: false,
                didOpen: function() {
                    Swal.showLoading();
                }
            });

            $.ajax({
                url: '{{ route('agencias.mass-update-preview') }}',
                method: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    Swal.close();
                    previewMasivoStats = response || null;

                    var noEncontradas = response.terminales_no_encontradas || [];
                    var listado = noEncontradas.length
                        ? `<details><summary class="text-danger" style="cursor:pointer;">Detalle de no encontradas (${noEncontradas.length})</summary><div class="small mt-2" style="max-height: 120px; overflow-y:auto;"><ul class="mb-0 ps-3">${noEncontradas.map(function(t){ return `<li>${$('<div>').text(t).html()}</li>`; }).join('')}</ul></div></details>`
                        : '<small class="text-success">Todas las terminales le&iacute;das existen en la tabla de agencias.</small>';

                    resultadoPreviewMasivo
                        .html(`
                            <div class="small">
                                <div><strong>Filas en archivo:</strong> ${Number(response.total_filas || 0).toLocaleString('es-DO')}</div>
                                <div><strong>Terminales le&iacute;das:</strong> ${Number(response.terminales_leidas || 0).toLocaleString('es-DO')}</div>
                                <div><strong>Terminales &uacute;nicas:</strong> ${Number(response.terminales_unicas || 0).toLocaleString('es-DO')}</div>
                                <div><strong>Coinciden en agencias:</strong> ${Number(response.encontradas || 0).toLocaleString('es-DO')}</div>
                                <div><strong>No existen en agencias:</strong> ${Number(response.no_encontradas || 0).toLocaleString('es-DO')}</div>
                                <div class="mt-2">${listado}</div>
                            </div>
                        `)
                        .show();

                    btnEjecutarUpdateMasivo.prop('disabled', false);
                },
                error: function(xhr) {
                    Swal.close();
                    previewMasivoStats = null;
                    var msg = xhr?.responseJSON?.message || 'No se pudo reconocer terminales del archivo.';
                    Swal.fire({
                        icon: 'error',
                        title: 'Error de reconocimiento',
                        text: msg
                    });
                    btnEjecutarUpdateMasivo.prop('disabled', true);
                    resultadoPreviewMasivo.hide().html('');
                }
            });
        });

        formMassUpdate.on('submit', function(event) {
            if (enviandoMassUpdate) {
                return;
            }

            if (!previewMasivoStats) {
                event.preventDefault();
                Swal.fire({
                    icon: 'warning',
                    title: 'Reconocimiento requerido',
                    text: 'Primero debes reconocer terminales antes de ejecutar la actualizaci&oacute;n masiva.'
                });
                return;
            }

            event.preventDefault();
            enviandoMassUpdate = true;

            const totalFilas = Number(previewMasivoStats.total_filas || 0);
            const terminalesUnicas = Number(previewMasivoStats.terminales_unicas || 0);
            const noEncontradas = Number(previewMasivoStats.no_encontradas || 0);

            Swal.fire({
                title: 'Procesando actualizaci&oacute;n masiva',
                html: `
                    <div class="text-start">
                        <div><strong>Filas:</strong> ${totalFilas.toLocaleString('es-DO')}</div>
                        <div><strong>Terminales &uacute;nicas:</strong> ${terminalesUnicas.toLocaleString('es-DO')}</div>
                        <div><strong>No encontradas:</strong> ${noEncontradas.toLocaleString('es-DO')}</div>
                        <div class="mt-3">
                            <div class="progress" style="height: 10px;">
                                <div id="massUpdateProgressBar" class="progress-bar progress-bar-striped progress-bar-animated bg-info" role="progressbar" style="width: 0%"></div>
                            </div>
                            <small class="text-muted d-block mt-2" id="massUpdateProgressText">Preparando proceso... 0%</small>
                        </div>
                    </div>
                `,
                allowOutsideClick: false,
                allowEscapeKey: false,
                showConfirmButton: false,
                didOpen: function() {
                    Swal.showLoading();

                    var progress = 0;
                    var progressBar = document.getElementById('massUpdateProgressBar');
                    var progressText = document.getElementById('massUpdateProgressText');
                    var fakeProgressInterval = window.setInterval(function() {
                        progress = Math.min(progress + 4, 92);

                        if (progressBar) {
                            progressBar.style.width = progress + '%';
                        }

                        if (progressText) {
                            progressText.textContent = 'Procesando actualizaci&oacute;n... ' + progress + '%';
                        }

                        if (progress >= 92) {
                            window.clearInterval(fakeProgressInterval);
                        }
                    }, 160);

                    Swal.getPopup().__fakeProgressInterval = fakeProgressInterval;
                },
                willClose: function() {
                    var popup = Swal.getPopup();
                    if (popup && popup.__fakeProgressInterval) {
                        window.clearInterval(popup.__fakeProgressInterval);
                    }
                }
            });

            this.submit();
        });
    });
</script>
@endsection


