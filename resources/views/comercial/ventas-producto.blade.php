@extends('app')

@section('content')
    <div class="main-content">
        <div class="page-content">
            <div class="container-fluid">

                <div class="row">
                    <div class="col-12">
                        <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                            <h4 class="mb-sm-0">VENTAS DE NO TRADICIONALES</h4>
                            <div class="page-title-right">
                                <ol class="breadcrumb m-0">
                                    <li class="breadcrumb-item"><a href="{{ route('inicio.index') }}">Inicio</a></li>
                                    <li class="breadcrumb-item"><a href="{{ route('comercial.index') }}">Comercial</a></li>
                                    <li class="breadcrumb-item active">VENTAS DE NO TRADICIONALES</li>
                                </ol>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-body">
                                <div class="row g-3 align-items-end">
                                    <div class="col-md-3">
                                        <label class="form-label">Fecha</label>
                                        <input type="date" id="inputFecha" class="form-control"
                                            value="{{ now('America/Santo_Domingo')->format('Y-m-d') }}">
                                    </div>
                                    <div class="col-md-9 d-flex gap-2 flex-wrap">
                                        <button id="btnGenerarToken" class="btn btn-secondary">
                                            <i class="ri-key-line me-1"></i>Generar Token
                                        </button>
                                        <button id="btnGenerarData" class="btn btn-info text-white">
                                            <i class="ri-download-cloud-line me-1"></i>Generar Data
                                        </button>
                                        <button id="btnConsultar" class="btn btn-primary">
                                            <i class="ri-search-line me-1"></i>Consultar
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-12 d-none" id="seccionResultados">
                        <div class="row mb-3">
                            <div class="col-xl-4 col-md-6">
                                <div class="card card-animate mb-0">
                                    <div class="card-body">
                                        <div class="d-flex align-items-center">
                                            <div class="flex-grow-1">
                                                <p class="text-uppercase fw-medium text-muted mb-0">Ventas No Tradicionales</p>
                                            </div>
                                        </div>
                                        <div class="d-flex align-items-end justify-content-between mt-4">
                                            <div>
                                                <h4 class="fs-22 fw-semibold mb-0" id="cardMontoTotal">RD$ 0.00</h4>
                                                <small class="text-muted d-block" id="cardPromedioAgencia">Promedio por agencia: RD$ 0.00</small>
                                            </div>
                                            <div class="avatar-sm flex-shrink-0">
                                                <span class="avatar-title bg-success-subtle rounded fs-3">
                                                    <i class="ri-money-dollar-circle-line text-success"></i>
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-xl-4 col-md-6">
                                <div class="card card-animate mb-0">
                                    <div class="card-body">
                                        <div class="d-flex align-items-center">
                                            <div class="flex-grow-1">
                                                <p class="text-uppercase fw-medium text-muted mb-0">Agencias con ventas</p>
                                            </div>
                                        </div>
                                        <div class="d-flex align-items-end justify-content-between mt-4">
                                            <div>
                                                <div class="d-flex gap-4">
                                                    <div>
                                                        <small class="text-muted d-block">Con ventas</small>
                                                        <h4 class="fs-22 fw-semibold mb-0" id="cardTotalAgencias">0</h4>
                                                    </div>
                                                    <div>
                                                        <small class="text-muted d-block">Sin ventas</small>
                                                        <h4 class="fs-22 fw-semibold mb-0" id="cardAgenciasSinVentas">0</h4>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="avatar-sm flex-shrink-0">
                                                <span class="avatar-title bg-info-subtle rounded fs-3">
                                                    <i class="ri-building-2-line text-info"></i>
                                                </span>
                                            </div>
                                        </div>
                                        <div class="mt-2">
                                            <button id="btnVerAgencias" class="btn btn-sm btn-outline-info">Ver Detalle</button>
                                            <button id="btnVerAgenciasSinVenta" class="btn btn-sm btn-outline-warning">Agencias sin venta</button>
                                            <button id="btnConfigVentasMinimo" class="btn btn-sm btn-outline-primary">Ventas mínimo</button>
                                            <button id="btnLimpiarAgencia" class="btn btn-sm btn-outline-secondary d-none">Limpiar Filtro</button>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-xl-4 col-md-12">
                                <div class="card card-animate mb-0">
                                    <div class="card-body">
                                        <div class="d-flex align-items-center justify-content-between mb-2">
                                            <p class="text-uppercase fw-medium text-muted mb-0">Cumplimiento de Agencias</p>
                                            <span class="badge bg-light text-dark" id="labelFiltroCumplimientoAgencia">Filtro: Todos</span>
                                        </div>
                                        <div class="mb-2">
                                            <p class="text-uppercase fw-medium text-muted mb-0" id="labelParametroVentasExigida">Ventas exigidas por agencia: <span class="text-primary fw-semibold">RD$ 0.00</span></p>
                                        </div>
                                        <div class="d-flex gap-4 mt-3">
                                            <div>
                                                <small class="text-muted d-block">Cumplen</small>
                                                <h5 class="mb-0 text-success" id="cardAgenciasCumplen">0</h5>
                                            </div>
                                            <div>
                                                <small class="text-muted d-block">No cumplen</small>
                                                <h5 class="mb-0 text-danger" id="cardAgenciasNoCumplen">0</h5>
                                            </div>
                                            <div>
                                                <small class="text-muted d-block">No registradas</small>
                                                <h5 class="mb-0 text-warning" id="cardTerminalesNoRegistradas">0</h5>
                                            </div>
                                        </div>
                                        <div class="mt-3 d-flex gap-2 flex-wrap">
                                            <button id="btnFiltroAgenciasTodos" class="btn btn-sm btn-outline-secondary">Todos</button>
                                            <button id="btnFiltroAgenciasCumplen" class="btn btn-sm btn-outline-success">Cumplen</button>
                                            <button id="btnFiltroAgenciasNoCumplen" class="btn btn-sm btn-outline-danger">No cumplen</button>
                                            <button id="btnVerTerminalesNoRegistradas" class="btn btn-sm btn-outline-warning d-none">Ver no registradas</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="card">
                            <div class="card-header d-flex align-items-center justify-content-between">
                                <h5 class="card-title mb-0">Resumen por Producto</h5>
                                <span class="badge bg-primary-subtle text-primary fs-12" id="labelFechaAplicada"></span>
                            </div>
                            <div class="card-body">
                                <table id="tableProductos"
                                    class="table table-bordered dt-responsive nowrap table-striped align-middle"
                                    style="width:100%">
                                    <thead>
                                        <tr>
                                            <th>Descripción (Producto)</th>
                                            <th>Total Vendido</th>
                                        </tr>
                                    </thead>
                                    <tbody></tbody>
                                </table>
                            </div>
                        </div>

                        <div class="card mt-3">
                            <div class="card-header d-flex align-items-center justify-content-between">
                                <h5 class="card-title mb-0">Resumen por Agencia</h5>
                                <span class="badge bg-light text-dark" id="labelMinimoVentas">Mínimo: RD$ 0.00</span>
                            </div>
                            <div class="card-body">
                                <table id="tableAgencias"
                                    class="table table-bordered dt-responsive nowrap table-striped align-middle"
                                    style="width:100%">
                                    <thead>
                                        <tr>
                                            <th>Agencia</th>
                                            <th>Total Vendido</th>
                                            <th>Cumplimiento</th>
                                        </tr>
                                    </thead>
                                    <tbody></tbody>
                                </table>
                            </div>
                        </div>

                        <div class="card mt-3">
                            <div class="card-header">
                                <h5 class="card-title mb-0">Ventas por Ciudad</h5>
                            </div>
                            <div class="card-body">
                                <table id="tableCiudades"
                                    class="table table-bordered dt-responsive nowrap table-striped align-middle"
                                    style="width:100%">
                                    <thead>
                                        <tr>
                                            <th>Ciudad</th>
                                            <th>Total Vendido</th>
                                        </tr>
                                    </thead>
                                    <tbody></tbody>
                                </table>
                            </div>
                        </div>

                        <div class="card mt-3">
                            <div class="card-header">
                                <h5 class="card-title mb-0">Ventas por Ruta</h5>
                            </div>
                            <div class="card-body">
                                <table id="tableRutas"
                                    class="table table-bordered dt-responsive nowrap table-striped align-middle"
                                    style="width:100%">
                                    <thead>
                                        <tr>
                                            <th>Ruta</th>
                                            <th>Total Vendido</th>
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
@endsection

<div class="modal fade" id="modalAgencias" tabindex="-1" aria-labelledby="modalAgenciasLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalAgenciasLabel">Agencias con Ventas</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="d-flex justify-content-between align-items-center mb-2 flex-wrap gap-2">
                    <div class="d-flex gap-2 flex-wrap">
                        <button id="btnModalAgenciasTodos" class="btn btn-sm btn-outline-secondary">Todos</button>
                        <button id="btnModalAgenciasCumplen" class="btn btn-sm btn-outline-success">Cumplen</button>
                        <button id="btnModalAgenciasNoCumplen" class="btn btn-sm btn-outline-danger">No cumplen</button>
                        <div class="dropdown">
                            <button class="btn btn-sm btn-outline-primary dropdown-toggle" type="button" id="btnDropdownCiudades" data-bs-toggle="dropdown" aria-expanded="false">
                                Ciudades
                            </button>
                            <ul class="dropdown-menu" id="menuCiudadesModal" aria-labelledby="btnDropdownCiudades" style="max-height: 260px; overflow-y: auto;"></ul>
                        </div>
                        <div class="dropdown">
                            <button class="btn btn-sm btn-outline-primary dropdown-toggle" type="button" id="btnDropdownRutas" data-bs-toggle="dropdown" aria-expanded="false">
                                Rutas
                            </button>
                            <ul class="dropdown-menu" id="menuRutasModal" aria-labelledby="btnDropdownRutas" style="max-height: 260px; overflow-y: auto;"></ul>
                        </div>
                    </div>
                    <span class="badge bg-light text-dark" id="labelModalAgenciasFiltro">Filtro: Todos</span>
                </div>
                <div class="table-responsive">
                    <table id="tableModalAgencias" class="table table-striped table-bordered w-100">
                        <thead>
                            <tr>
                                <th>Agencia</th>
                                <th>Total Vendido</th>
                                <th>Acción</th>
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

<div class="modal fade" id="modalAgenciasSinVenta" tabindex="-1" aria-labelledby="modalAgenciasSinVentaLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header d-flex justify-content-between align-items-center">
                <h5 class="modal-title" id="modalAgenciasSinVentaLabel">Agencias sin venta</h5>
                <div class="d-flex align-items-center gap-2">
                    <button type="button" class="btn btn-success btn-sm" id="btnDescargarAgenciasSinVentaExcel">
                        <i class="ri-file-excel-2-line me-1"></i>Descargar Excel
                    </button>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
            </div>
            <div class="modal-body">
                <div class="table-responsive">
                    <table id="tableModalAgenciasSinVenta" class="table table-striped table-bordered w-100">
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

<div class="modal fade" id="modalVentasMinimo" tabindex="-1" aria-labelledby="modalVentasMinimoLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalVentasMinimoLabel">Configurar Ventas Mínimo</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <label for="inputVentasMinimo" class="form-label">Monto mínimo para cumplimiento</label>
                <input type="number" class="form-control" id="inputVentasMinimo" min="0" step="0.01" value="0">
                <small class="text-muted">Ejemplo: si configuras 1000, agencia con 1001 cumple; con 999 no cumple.</small>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" id="btnGuardarVentasMinimo">Guardar</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modalTerminalesNoRegistradas" tabindex="-1" aria-labelledby="modalTerminalesNoRegistradasLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalTerminalesNoRegistradasLabel">Terminales no registradas en Agencias</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="table-responsive">
                    <table id="tableModalTerminalesNoRegistradas" class="table table-striped table-bordered w-100">
                        <thead>
                            <tr>
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

@section('script')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const btnConsultar   = document.getElementById('btnConsultar');
    const inputFecha     = document.getElementById('inputFecha');
    const seccion        = document.getElementById('seccionResultados');
    const labelFecha     = document.getElementById('labelFechaAplicada');
    const cardMontoTotal = document.getElementById('cardMontoTotal');
    const cardPromedioAgencia = document.getElementById('cardPromedioAgencia');
    const cardTotalAgencias = document.getElementById('cardTotalAgencias');
    const cardAgenciasSinVentas = document.getElementById('cardAgenciasSinVentas');
    const btnVerAgencias = document.getElementById('btnVerAgencias');
    const btnVerAgenciasSinVenta = document.getElementById('btnVerAgenciasSinVenta');
    const btnConfigVentasMinimo = document.getElementById('btnConfigVentasMinimo');
    const btnLimpiarAgencia = document.getElementById('btnLimpiarAgencia');
    const labelMinimoVentas = document.getElementById('labelMinimoVentas');
    const labelParametroVentasExigida = document.getElementById('labelParametroVentasExigida');
    const cardAgenciasCumplen = document.getElementById('cardAgenciasCumplen');
    const cardAgenciasNoCumplen = document.getElementById('cardAgenciasNoCumplen');
    const cardTerminalesNoRegistradas = document.getElementById('cardTerminalesNoRegistradas');
    const labelFiltroCumplimientoAgencia = document.getElementById('labelFiltroCumplimientoAgencia');
    const btnFiltroAgenciasTodos = document.getElementById('btnFiltroAgenciasTodos');
    const btnFiltroAgenciasCumplen = document.getElementById('btnFiltroAgenciasCumplen');
    const btnFiltroAgenciasNoCumplen = document.getElementById('btnFiltroAgenciasNoCumplen');
    const btnVerTerminalesNoRegistradas = document.getElementById('btnVerTerminalesNoRegistradas');
    const btnModalAgenciasTodos = document.getElementById('btnModalAgenciasTodos');
    const btnModalAgenciasCumplen = document.getElementById('btnModalAgenciasCumplen');
    const btnModalAgenciasNoCumplen = document.getElementById('btnModalAgenciasNoCumplen');
    const btnDescargarAgenciasSinVentaExcel = document.getElementById('btnDescargarAgenciasSinVentaExcel');
    const labelModalAgenciasFiltro = document.getElementById('labelModalAgenciasFiltro');
    const btnDropdownCiudades = document.getElementById('btnDropdownCiudades');
    const menuCiudadesModal = document.getElementById('menuCiudadesModal');
    const btnDropdownRutas = document.getElementById('btnDropdownRutas');
    const menuRutasModal = document.getElementById('menuRutasModal');
    let dtProductos      = null;
    let dtAgencias       = null;
    let dtCiudades       = null;
    let dtRutas          = null;
    let dtModalAgencias  = null;
    let dtModalAgenciasSinVenta = null;
    let dtModalTerminalesNoRegistradas = null;
    let ventasFuente     = [];
    let agenciaSeleccionada = null;
    let ciudadSeleccionada = null;
    let rutaSeleccionada = null;
    let ventasMinimoConfig = 0;
    let filtroCumplimientoAgencia = 'todos';
    let resumenAgenciaVisibleActual = new Map();
    let modalAgencyEntries = [];
    let modalAgencyEntriesSinVentas = [];
    let modalAgencyFilter = 'todos';
    let resumenEstadoAgencias = { activas: 0, con_ventas: 0, sin_ventas: 0 };
    let terminalesNoRegistradas = [];

    const formatMoney = (value) => Number(value ?? 0).toLocaleString('es-DO', { minimumFractionDigits: 2 });
    const renderVentasExigidaLabel = () => {
        labelParametroVentasExigida.innerHTML = `Ventas exigidas por agencia: <span class="text-primary fw-semibold">RD$ ${formatMoney(ventasMinimoConfig)}</span>`;
    };
    const normalizarClaveAgencia = (value) => (value ?? '').toString().trim().replace(/^0+/, '');
    const getAgencyDisplayData = (agenciaId) => {
        const agenciaIdRaw = (agenciaId ?? '').toString().trim();
        const agenciaIdNormalized = normalizarClaveAgencia(agenciaIdRaw);

        const venta = (ventasFuente ?? []).find(item => {
            const itemAgenciaIdRaw = (item?.agencia_id ?? '').toString().trim();
            return itemAgenciaIdRaw === agenciaIdRaw
                || normalizarClaveAgencia(itemAgenciaIdRaw) === agenciaIdNormalized;
        });

        const nombre = (venta?.nombre_agencia ?? venta?.agencia ?? agenciaIdRaw).toString().trim() || agenciaIdRaw;
        const terminal = (venta?.terminal ?? agenciaIdRaw).toString().trim() || agenciaIdRaw;

        return { nombre, terminal };
    };
    const cumpleMinimo = (total) => Number(total ?? 0) >= Number(ventasMinimoConfig ?? 0);

    const updateFiltroLabel = () => {
        const texto = filtroCumplimientoAgencia === 'cumplen'
            ? 'Filtro: Cumplen'
            : filtroCumplimientoAgencia === 'no_cumplen'
                ? 'Filtro: No cumplen'
                : 'Filtro: Todos';
        labelFiltroCumplimientoAgencia.textContent = texto;
    };

    const filtrarResumenAgenciaPorCumplimiento = (resumenMap) => {
        const entries = [...resumenMap.entries()];
        if (filtroCumplimientoAgencia === 'cumplen') {
            return entries.filter(([, total]) => cumpleMinimo(total));
        }
        if (filtroCumplimientoAgencia === 'no_cumplen') {
            return entries.filter(([, total]) => !cumpleMinimo(total));
        }
        return entries;
    };

    const updateModalFiltroLabel = () => {
        const texto = modalAgencyFilter === 'cumplen'
            ? 'Filtro: Cumplen'
            : modalAgencyFilter === 'no_cumplen'
                ? 'Filtro: No cumplen'
                : 'Filtro: Todos';
        labelModalAgenciasFiltro.textContent = texto;
    };

    const filtrarModalAgencias = () => {
        if (modalAgencyFilter === 'cumplen') {
            return modalAgencyEntries.filter(([, total]) => cumpleMinimo(total));
        }
        if (modalAgencyFilter === 'no_cumplen') {
            return modalAgencyEntries.filter(([, total]) => !cumpleMinimo(total));
        }
        return modalAgencyEntries;
    };

    const renderModalAgencias = () => {
        if (dtModalAgencias) {
            dtModalAgencias.destroy();
            dtModalAgencias = null;
        }

        const tbodyModal = document.querySelector('#tableModalAgencias tbody');
        tbodyModal.innerHTML = '';

        const agenciasFiltradas = filtrarModalAgencias();
        agenciasFiltradas.forEach(([agencia, total]) => {
            const { nombre, terminal } = getAgencyDisplayData(agencia);
            const tr = document.createElement('tr');
            tr.innerHTML = `
                <td>
                    <div class="fw-medium">${nombre}</div>
                    <small class="text-muted">Terminal: ${terminal}</small>
                </td>
                <td>${formatMoney(total)}</td>
                <td><button class="btn btn-sm btn-primary btnFiltrarAgencia" data-agencia="${agencia}">Ver</button></td>
            `;
            tbodyModal.appendChild(tr);
        });

        dtModalAgencias = $('#tableModalAgencias').DataTable({
            destroy: true,
            responsive: true,
            language: {
                url: '/json/es-DO.json',
                search: 'Buscar:',
                lengthMenu: 'Mostrar _MENU_ registros',
                info: 'Mostrando _START_ a _END_ de _TOTAL_ registros',
                paginate: { first: 'Primera', last: 'Última', next: 'Siguiente', previous: 'Anterior' }
            },
            order: [[1, 'desc']],
        });

        updateModalFiltroLabel();
    };

    const abrirModalAgenciasSinVenta = () => {
        if (dtModalAgenciasSinVenta) {
            dtModalAgenciasSinVenta.destroy();
            dtModalAgenciasSinVenta = null;
        }

        const tbody = document.querySelector('#tableModalAgenciasSinVenta tbody');
        tbody.innerHTML = '';

        modalAgencyEntriesSinVentas.forEach((item) => {
            const nombre = (item?.nombre_agencia ?? item?.agencia_id ?? 'SIN AGENCIA').toString().trim() || 'SIN AGENCIA';
            const terminal = (item?.terminal ?? item?.agencia_id ?? 'SIN TERMINAL').toString().trim() || 'SIN TERMINAL';
            const tr = document.createElement('tr');
            tr.innerHTML = `
                <td>${nombre}</td>
                <td>${terminal}</td>
            `;
            tbody.appendChild(tr);
        });

        dtModalAgenciasSinVenta = $('#tableModalAgenciasSinVenta').DataTable({
            destroy: true,
            responsive: true,
            language: {
                url: '/json/es-DO.json',
                search: 'Buscar:',
                lengthMenu: 'Mostrar _MENU_ registros',
                info: 'Mostrando _START_ a _END_ de _TOTAL_ registros',
                paginate: { first: 'Primera', last: 'Última', next: 'Siguiente', previous: 'Anterior' }
            },
            order: [[0, 'asc']],
        });

        const modal = new bootstrap.Modal(document.getElementById('modalAgenciasSinVenta'));
        modal.show();
    };

    const descargarAgenciasSinVentaExcel = () => {
        if (!modalAgencyEntriesSinVentas.length) {
            Swal.fire({ title: 'Sin datos', text: 'No hay agencias sin venta para descargar.', icon: 'info' });
            return;
        }

        const escapeHtml = (value) => (value ?? '').toString()
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#39;');

        const filas = modalAgencyEntriesSinVentas.map((item) => {
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
        const fecha = (inputFecha?.value || '').replace(/-/g, '') || 'sin_fecha';
        enlace.href = url;
        enlace.download = `agencias_sin_venta_${fecha}.xls`;
        document.body.appendChild(enlace);
        enlace.click();
        document.body.removeChild(enlace);
        URL.revokeObjectURL(url);
    };

    const getCumplimientoBadge = (total) => {
        const cumple = cumpleMinimo(total);
        return cumple
            ? '<span class="badge bg-success">CUMPLIÓ</span>'
            : '<span class="badge bg-danger">NO CUMPLE</span>';
    };

    const aplicarResumenAgencias = (resumen) => {
        const activas = Number(resumen?.activas ?? 0);
        const conVentas = Number(resumen?.con_ventas ?? 0);
        const sinVentas = Number(resumen?.sin_ventas ?? 0);
        const countNoRegistradas = Number(resumen?.terminales_no_registradas_count ?? 0);
        terminalesNoRegistradas = Array.isArray(resumen?.terminales_no_registradas)
            ? [...new Set(resumen.terminales_no_registradas.map(item => (item ?? '').toString().trim()).filter(Boolean))]
            : [];

        resumenEstadoAgencias = {
            activas: activas >= 0 ? activas : 0,
            con_ventas: conVentas >= 0 ? conVentas : 0,
            sin_ventas: sinVentas >= 0 ? sinVentas : 0,
        };

        modalAgencyEntriesSinVentas = Array.isArray(resumen?.agencias_sin_ventas)
            ? resumen.agencias_sin_ventas
            : [];

        cardTotalAgencias.textContent = resumenEstadoAgencias.con_ventas.toLocaleString('es-DO');
        cardAgenciasSinVentas.textContent = resumenEstadoAgencias.sin_ventas.toLocaleString('es-DO');
        cardTerminalesNoRegistradas.textContent = (countNoRegistradas >= 0 ? countNoRegistradas : 0).toLocaleString('es-DO');
        btnVerTerminalesNoRegistradas.classList.toggle('d-none', terminalesNoRegistradas.length === 0);
    };

    const abrirModalTerminalesNoRegistradas = () => {
        if (dtModalTerminalesNoRegistradas) {
            dtModalTerminalesNoRegistradas.destroy();
            dtModalTerminalesNoRegistradas = null;
        }

        const tbody = document.querySelector('#tableModalTerminalesNoRegistradas tbody');
        tbody.innerHTML = '';

        terminalesNoRegistradas.forEach((terminal) => {
            const tr = document.createElement('tr');
            tr.innerHTML = `<td>${terminal}</td>`;
            tbody.appendChild(tr);
        });

        dtModalTerminalesNoRegistradas = $('#tableModalTerminalesNoRegistradas').DataTable({
            destroy: true,
            responsive: true,
            language: {
                url: '/json/es-DO.json',
                search: 'Buscar:',
                lengthMenu: 'Mostrar _MENU_ registros',
                info: 'Mostrando _START_ a _END_ de _TOTAL_ registros',
                paginate: { first: 'Primera', last: 'Última', next: 'Siguiente', previous: 'Anterior' }
            },
            order: [[0, 'asc']],
        });

        const modal = new bootstrap.Modal(document.getElementById('modalTerminalesNoRegistradas'));
        modal.show();
    };

    const destroyTables = () => {
        if (dtProductos) {
            dtProductos.destroy();
            dtProductos = null;
        }
        if (dtAgencias) {
            dtAgencias.destroy();
            dtAgencias = null;
        }
        if (dtCiudades) {
            dtCiudades.destroy();
            dtCiudades = null;
        }
        if (dtRutas) {
            dtRutas.destroy();
            dtRutas = null;
        }
        document.querySelector('#tableProductos tbody').innerHTML = '';
        document.querySelector('#tableAgencias tbody').innerHTML = '';
        document.querySelector('#tableCiudades tbody').innerHTML = '';
        document.querySelector('#tableRutas tbody').innerHTML = '';
        cardMontoTotal.textContent = 'RD$ 0.00';
        cardPromedioAgencia.textContent = 'Promedio por agencia: RD$ 0.00';
        cardTotalAgencias.textContent = '0';
        cardAgenciasSinVentas.textContent = '0';
        cardAgenciasCumplen.textContent = '0';
        cardAgenciasNoCumplen.textContent = '0';
        cardTerminalesNoRegistradas.textContent = '0';
        labelFecha.textContent = '';
        labelMinimoVentas.textContent = `Mínimo: RD$ ${formatMoney(ventasMinimoConfig)}`;
        renderVentasExigidaLabel();
        resumenAgenciaVisibleActual = new Map();
        agenciaSeleccionada = null;
        ciudadSeleccionada = null;
        rutaSeleccionada = null;
        filtroCumplimientoAgencia = 'todos';
        resumenEstadoAgencias = { activas: 0, con_ventas: 0, sin_ventas: 0 };
        modalAgencyEntriesSinVentas = [];
        terminalesNoRegistradas = [];
        btnVerTerminalesNoRegistradas.classList.add('d-none');
        updateFiltroLabel();
        btnLimpiarAgencia.classList.add('d-none');
        btnDropdownCiudades.textContent = 'Ciudades';
        btnDropdownRutas.textContent = 'Rutas';
    };

    const clearMainTables = () => {
        if (dtProductos) { dtProductos.destroy(); dtProductos = null; }
        if (dtAgencias) { dtAgencias.destroy(); dtAgencias = null; }
        if (dtCiudades) { dtCiudades.destroy(); dtCiudades = null; }
        if (dtRutas) { dtRutas.destroy(); dtRutas = null; }
        document.querySelector('#tableProductos tbody').innerHTML = '';
        document.querySelector('#tableAgencias tbody').innerHTML = '';
        document.querySelector('#tableCiudades tbody').innerHTML = '';
        document.querySelector('#tableRutas tbody').innerHTML = '';
    };

    const cargarCiudadesEnModal = () => {
        menuCiudadesModal.innerHTML = '';

        const liTodos = document.createElement('li');
        const btnTodos = document.createElement('button');
        btnTodos.type = 'button';
        btnTodos.className = 'dropdown-item btnSelectCiudad';
        btnTodos.setAttribute('data-ciudad', '');
        btnTodos.textContent = 'Todas las ciudades';
        liTodos.appendChild(btnTodos);
        menuCiudadesModal.appendChild(liTodos);

        const ciudades = [...new Set((ventasFuente ?? []).map(item => {
            return (item.ciudad ?? 'SIN CIUDAD').toString().trim() || 'SIN CIUDAD';
        }))].sort((a, b) => a.localeCompare(b, 'es'));

        ciudades.forEach(ciudad => {
            const li = document.createElement('li');
            const btn = document.createElement('button');
            btn.type = 'button';
            btn.className = 'dropdown-item btnSelectCiudad';
            btn.setAttribute('data-ciudad', ciudad);
            btn.textContent = ciudad;
            li.appendChild(btn);
            menuCiudadesModal.appendChild(li);
        });
    };

    const cargarRutasEnModal = () => {
        menuRutasModal.innerHTML = '';

        const liTodos = document.createElement('li');
        const btnTodos = document.createElement('button');
        btnTodos.type = 'button';
        btnTodos.className = 'dropdown-item btnSelectRuta';
        btnTodos.setAttribute('data-ruta', '');
        btnTodos.textContent = 'Todas las rutas';
        liTodos.appendChild(btnTodos);
        menuRutasModal.appendChild(liTodos);

        const rutas = [...new Set((ventasFuente ?? []).map(item => {
            return (item.ruta ?? 'SIN RUTA').toString().trim() || 'SIN RUTA';
        }))].sort((a, b) => a.localeCompare(b, 'es'));

        rutas.forEach(ruta => {
            const li = document.createElement('li');
            const btn = document.createElement('button');
            btn.type = 'button';
            btn.className = 'dropdown-item btnSelectRuta';
            btn.setAttribute('data-ruta', ruta);
            btn.textContent = ruta;
            li.appendChild(btn);
            menuRutasModal.appendChild(li);
        });
    };

    const renderResumen = (ventas) => {
        const resumenProducto = new Map();
        const resumenAgencia = new Map();
        const resumenAgenciaActiva = new Map();
        const resumenCiudad = new Map();
        const resumenRuta = new Map();
        let totalVendido = 0;

        (ventas ?? []).forEach(item => {
            const descripcion = (item.descripcion ?? 'SIN DESCRIPCIÓN').toString().trim() || 'SIN DESCRIPCIÓN';
            const agencia = (item.agencia_id ?? 'SIN AGENCIA').toString().trim() || 'SIN AGENCIA';
            const ciudad = (item.ciudad ?? 'SIN CIUDAD').toString().trim() || 'SIN CIUDAD';
            const ruta = (item.ruta ?? 'SIN RUTA').toString().trim() || 'SIN RUTA';
            const monto = Number(item.monto ?? 0);

            resumenProducto.set(descripcion, (resumenProducto.get(descripcion) ?? 0) + monto);
            resumenAgencia.set(agencia, (resumenAgencia.get(agencia) ?? 0) + monto);
            if (Number(item.estatus ?? 0) === 1) {
                resumenAgenciaActiva.set(agencia, (resumenAgenciaActiva.get(agencia) ?? 0) + monto);
            }
            resumenCiudad.set(ciudad, (resumenCiudad.get(ciudad) ?? 0) + monto);
            resumenRuta.set(ruta, (resumenRuta.get(ruta) ?? 0) + monto);
            totalVendido += monto;
        });

        cardMontoTotal.textContent = `RD$ ${formatMoney(totalVendido)}`;
        const agenciasCumplen = [...resumenAgenciaActiva.entries()].filter(([, total]) => cumpleMinimo(total)).length;
        const agenciasNoCumplen = [...resumenAgenciaActiva.entries()].length - agenciasCumplen;
        cardAgenciasCumplen.textContent = agenciasCumplen.toLocaleString('es-DO');
        cardAgenciasNoCumplen.textContent = agenciasNoCumplen.toLocaleString('es-DO');

        const agenciasFiltradas = filtrarResumenAgenciaPorCumplimiento(resumenAgenciaActiva)
            .sort((a, b) => b[1] - a[1]);

        const agenciasPermitidas = new Set(agenciasFiltradas.map(([agencia]) => agencia));
        const productosOrdenados = [...resumenProducto.entries()]
            .sort((a, b) => b[1] - a[1]);
        let ciudadesOrdenadas = [...resumenCiudad.entries()]
            .sort((a, b) => b[1] - a[1]);
        let rutasOrdenadas = [...resumenRuta.entries()]
            .sort((a, b) => b[1] - a[1]);

        if (filtroCumplimientoAgencia !== 'todos') {
            const resumenProductoFiltrado = new Map();
            const resumenCiudadFiltrado = new Map();
            const resumenRutaFiltrado = new Map();
            (ventas ?? []).forEach(item => {
                const agencia = (item.agencia_id ?? 'SIN AGENCIA').toString().trim() || 'SIN AGENCIA';
                if (!agenciasPermitidas.has(agencia)) return;
                const descripcion = (item.descripcion ?? 'SIN DESCRIPCIÓN').toString().trim() || 'SIN DESCRIPCIÓN';
                const ciudad = (item.ciudad ?? 'SIN CIUDAD').toString().trim() || 'SIN CIUDAD';
                const ruta = (item.ruta ?? 'SIN RUTA').toString().trim() || 'SIN RUTA';
                const monto = Number(item.monto ?? 0);
                resumenProductoFiltrado.set(descripcion, (resumenProductoFiltrado.get(descripcion) ?? 0) + monto);
                resumenCiudadFiltrado.set(ciudad, (resumenCiudadFiltrado.get(ciudad) ?? 0) + monto);
                resumenRutaFiltrado.set(ruta, (resumenRutaFiltrado.get(ruta) ?? 0) + monto);
            });
            productosOrdenados.splice(0, productosOrdenados.length, ...[...resumenProductoFiltrado.entries()].sort((a, b) => b[1] - a[1]));
            ciudadesOrdenadas = [...resumenCiudadFiltrado.entries()].sort((a, b) => b[1] - a[1]);
            rutasOrdenadas = [...resumenRutaFiltrado.entries()].sort((a, b) => b[1] - a[1]);
            totalVendido = [...agenciasFiltradas].reduce((sum, [, total]) => sum + Number(total), 0);
            cardMontoTotal.textContent = `RD$ ${formatMoney(totalVendido)}`;
        }

        if (resumenEstadoAgencias.con_ventas > 0 || resumenEstadoAgencias.sin_ventas > 0) {
            cardTotalAgencias.textContent = resumenEstadoAgencias.con_ventas.toLocaleString('es-DO');
            cardAgenciasSinVentas.textContent = resumenEstadoAgencias.sin_ventas.toLocaleString('es-DO');
        } else {
            cardTotalAgencias.textContent = agenciasFiltradas.length.toLocaleString('es-DO');
            cardAgenciasSinVentas.textContent = '0';
        }
        const promedioPorAgencia = agenciasFiltradas.length > 0 ? totalVendido / agenciasFiltradas.length : 0;
        cardPromedioAgencia.textContent = `Promedio por agencia: RD$ ${formatMoney(promedioPorAgencia)}`;
        resumenAgenciaVisibleActual = new Map(agenciasFiltradas);

        const tbodyProductos = document.querySelector('#tableProductos tbody');
        productosOrdenados.forEach(([descripcion, total]) => {
            const tr = document.createElement('tr');
            tr.innerHTML = `
                <td>${descripcion}</td>
                <td>${formatMoney(total)}</td>
            `;
            tbodyProductos.appendChild(tr);
        });

        const tbodyAgencias = document.querySelector('#tableAgencias tbody');
        agenciasFiltradas.forEach(([agencia, total]) => {
            const { nombre, terminal } = getAgencyDisplayData(agencia);
            const tr = document.createElement('tr');
            tr.innerHTML = `
                <td>
                    <div class="fw-medium">${nombre}</div>
                    <small class="text-muted">Terminal: ${terminal}</small>
                </td>
                <td>${formatMoney(total)}</td>
                <td>${getCumplimientoBadge(total)}</td>
            `;
            tbodyAgencias.appendChild(tr);
        });

        const tbodyCiudades = document.querySelector('#tableCiudades tbody');
        ciudadesOrdenadas.forEach(([ciudad, total]) => {
            const tr = document.createElement('tr');
            tr.innerHTML = `
                <td>${ciudad}</td>
                <td>${formatMoney(total)}</td>
            `;
            tbodyCiudades.appendChild(tr);
        });

        const tbodyRutas = document.querySelector('#tableRutas tbody');
        rutasOrdenadas.forEach(([ruta, total]) => {
            const tr = document.createElement('tr');
            tr.innerHTML = `
                <td>${ruta}</td>
                <td>${formatMoney(total)}</td>
            `;
            tbodyRutas.appendChild(tr);
        });

        dtProductos = $('#tableProductos').DataTable({
            destroy: true,
            responsive: true,
            language: {
                url: '/json/es-DO.json',
                search: 'Buscar:',
                lengthMenu: 'Mostrar _MENU_ registros',
                info: 'Mostrando _START_ a _END_ de _TOTAL_ registros',
                paginate: { first: 'Primera', last: 'Última', next: 'Siguiente', previous: 'Anterior' }
            },
            dom: 'Bfrtip',
            buttons: ['copy', 'csv', 'excel', 'pdf', 'print'],
            order: [[1, 'desc']],
        });

        dtAgencias = $('#tableAgencias').DataTable({
            destroy: true,
            responsive: true,
            language: {
                url: '/json/es-DO.json',
                search: 'Buscar:',
                lengthMenu: 'Mostrar _MENU_ registros',
                info: 'Mostrando _START_ a _END_ de _TOTAL_ registros',
                paginate: { first: 'Primera', last: 'Última', next: 'Siguiente', previous: 'Anterior' }
            },
            dom: 'Bfrtip',
            buttons: ['copy', 'csv', 'excel', 'pdf', 'print'],
            order: [[1, 'desc']],
        });

        dtCiudades = $('#tableCiudades').DataTable({
            destroy: true,
            responsive: true,
            language: {
                url: '/json/es-DO.json',
                search: 'Buscar:',
                lengthMenu: 'Mostrar _MENU_ registros',
                info: 'Mostrando _START_ a _END_ de _TOTAL_ registros',
                paginate: { first: 'Primera', last: 'Última', next: 'Siguiente', previous: 'Anterior' }
            },
            dom: 'Bfrtip',
            buttons: ['copy', 'csv', 'excel', 'pdf', 'print'],
            order: [[1, 'desc']],
        });

        dtRutas = $('#tableRutas').DataTable({
            destroy: true,
            responsive: true,
            language: {
                url: '/json/es-DO.json',
                search: 'Buscar:',
                lengthMenu: 'Mostrar _MENU_ registros',
                info: 'Mostrando _START_ a _END_ de _TOTAL_ registros',
                paginate: { first: 'Primera', last: 'Última', next: 'Siguiente', previous: 'Anterior' }
            },
            dom: 'Bfrtip',
            buttons: ['copy', 'csv', 'excel', 'pdf', 'print'],
            order: [[1, 'desc']],
        });

        updateFiltroLabel();

        return { resumenAgencia: resumenAgenciaVisibleActual };
    };

    const abrirModalAgencias = (resumenAgencia) => {
        modalAgencyEntries = [...resumenAgencia.entries()].sort((a, b) => b[1] - a[1]);
        modalAgencyFilter = 'todos';
        cargarCiudadesEnModal();
        cargarRutasEnModal();
        btnDropdownCiudades.textContent = ciudadSeleccionada ? `Ciudad: ${ciudadSeleccionada}` : 'Ciudades';
        btnDropdownRutas.textContent = rutaSeleccionada ? `Ruta: ${rutaSeleccionada}` : 'Rutas';
        renderModalAgencias();

        const modal = new bootstrap.Modal(document.getElementById('modalAgencias'));
        modal.show();
    };

    const aplicarFiltroAgencia = (agencia) => {
        agenciaSeleccionada = agencia;
        ciudadSeleccionada = null;
        rutaSeleccionada = null;
        const ventasFiltradas = (ventasFuente ?? []).filter(item => {
            return (item.agencia_id ?? '').toString().trim() === agencia.toString().trim();
        });

        clearMainTables();

        renderResumen(ventasFiltradas);
        labelFecha.textContent = `Fecha: ${inputFecha.value} | Agencia: ${agencia}`;
        btnLimpiarAgencia.classList.remove('d-none');
        btnDropdownCiudades.textContent = 'Ciudades';
        btnDropdownRutas.textContent = 'Rutas';
    };

    const aplicarFiltroCiudad = (ciudad) => {
        ciudadSeleccionada = (ciudad ?? '').toString().trim() || null;
        agenciaSeleccionada = null;
        rutaSeleccionada = null;

        const ventasFiltradas = ciudadSeleccionada
            ? (ventasFuente ?? []).filter(item => {
                const ciudadItem = (item.ciudad ?? 'SIN CIUDAD').toString().trim() || 'SIN CIUDAD';
                return ciudadItem === ciudadSeleccionada;
            })
            : ventasFuente;

        clearMainTables();
        renderResumen(ventasFiltradas);

        if (ciudadSeleccionada) {
            labelFecha.textContent = `Fecha: ${inputFecha.value} | Ciudad: ${ciudadSeleccionada}`;
            btnLimpiarAgencia.classList.remove('d-none');
            btnDropdownCiudades.textContent = `Ciudad: ${ciudadSeleccionada}`;
            btnDropdownRutas.textContent = 'Rutas';
        } else {
            labelFecha.textContent = 'Fecha: ' + inputFecha.value;
            btnLimpiarAgencia.classList.add('d-none');
            btnDropdownCiudades.textContent = 'Ciudades';
            btnDropdownRutas.textContent = 'Rutas';
        }
    };

    const aplicarFiltroRuta = (ruta) => {
        rutaSeleccionada = (ruta ?? '').toString().trim() || null;
        agenciaSeleccionada = null;
        ciudadSeleccionada = null;

        const ventasFiltradas = rutaSeleccionada
            ? (ventasFuente ?? []).filter(item => {
                const rutaItem = (item.ruta ?? 'SIN RUTA').toString().trim() || 'SIN RUTA';
                return rutaItem === rutaSeleccionada;
            })
            : ventasFuente;

        clearMainTables();
        renderResumen(ventasFiltradas);

        if (rutaSeleccionada) {
            labelFecha.textContent = `Fecha: ${inputFecha.value} | Ruta: ${rutaSeleccionada}`;
            btnLimpiarAgencia.classList.remove('d-none');
            btnDropdownRutas.textContent = `Ruta: ${rutaSeleccionada}`;
            btnDropdownCiudades.textContent = 'Ciudades';
        } else {
            labelFecha.textContent = 'Fecha: ' + inputFecha.value;
            btnLimpiarAgencia.classList.add('d-none');
            btnDropdownRutas.textContent = 'Rutas';
            btnDropdownCiudades.textContent = 'Ciudades';
        }
    };

    const refrescarVistaActual = () => {
        if (!ventasFuente.length) return;

        clearMainTables();

        const dataRender = agenciaSeleccionada
            ? ventasFuente.filter(item => (item.agencia_id ?? '').toString().trim() === agenciaSeleccionada.toString().trim())
            : ciudadSeleccionada
                ? ventasFuente.filter(item => {
                    const ciudadItem = (item.ciudad ?? 'SIN CIUDAD').toString().trim() || 'SIN CIUDAD';
                    return ciudadItem === ciudadSeleccionada;
                })
            : rutaSeleccionada
                ? ventasFuente.filter(item => {
                    const rutaItem = (item.ruta ?? 'SIN RUTA').toString().trim() || 'SIN RUTA';
                    return rutaItem === rutaSeleccionada;
                })
            : ventasFuente;

        renderResumen(dataRender);
        if (agenciaSeleccionada) {
            labelFecha.textContent = `Fecha: ${inputFecha.value} | Agencia: ${agenciaSeleccionada}`;
            btnLimpiarAgencia.classList.remove('d-none');
            btnDropdownCiudades.textContent = 'Ciudades';
            btnDropdownRutas.textContent = 'Rutas';
        } else if (ciudadSeleccionada) {
            labelFecha.textContent = `Fecha: ${inputFecha.value} | Ciudad: ${ciudadSeleccionada}`;
            btnLimpiarAgencia.classList.remove('d-none');
            btnDropdownCiudades.textContent = `Ciudad: ${ciudadSeleccionada}`;
            btnDropdownRutas.textContent = 'Rutas';
        } else if (rutaSeleccionada) {
            labelFecha.textContent = `Fecha: ${inputFecha.value} | Ruta: ${rutaSeleccionada}`;
            btnLimpiarAgencia.classList.remove('d-none');
            btnDropdownRutas.textContent = `Ruta: ${rutaSeleccionada}`;
            btnDropdownCiudades.textContent = 'Ciudades';
        } else {
            labelFecha.textContent = 'Fecha: ' + inputFecha.value;
            btnLimpiarAgencia.classList.add('d-none');
            btnDropdownCiudades.textContent = 'Ciudades';
            btnDropdownRutas.textContent = 'Rutas';
        }
    };

    const isSuccessCode = (code) => {
        if (code === undefined || code === null) return false;
        const normalized = String(code).trim().toLowerCase();
        return normalized === '0' || normalized === '200' || normalized === 'success' || normalized === 'ok';
    };

    const getApiError = (data) => {
        if (data?.error) return data.error;
        if (data?.message && !data?.success && !data?.ventas) return data.message;
        if (data?.code !== undefined && !isSuccessCode(data.code)) {
            return data?.message || `Código inesperado: ${data.code}`;
        }
        return null;
    };

    const fetchJson = async (url, options = {}) => {
        const response = await fetch(url, {
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                ...(options.headers || {}),
            },
            ...options,
        });

        const contentType = response.headers.get('content-type') || '';
        if (!contentType.includes('application/json')) {
            const raw = await response.text();
            const titleMatch = raw.match(/<title>(.*?)<\/title>/i);
            const title = titleMatch?.[1]?.trim();
            throw new Error(title || `El servidor devolvió una respuesta no JSON (HTTP ${response.status}).`);
        }

        const data = await response.json();
        if (!response.ok) {
            throw new Error(data?.error || data?.message || `Solicitud fallida (HTTP ${response.status}).`);
        }

        return data;
    };

    // --- Generar Token ---
    document.getElementById('btnGenerarToken').addEventListener('click', function () {
        this.disabled = true;
        Swal.fire({
            title: 'Generando token...',
            allowOutsideClick: false,
            allowEscapeKey: false,
            didOpen: () => Swal.showLoading(),
        });
        fetchJson('/generar-token')
            .then(data => {
                Swal.fire({ title: 'Listo', text: data.success ?? 'Token generado.', icon: 'success' });
            })
            .catch(err => {
                Swal.fire({ title: 'Error', text: err.message, icon: 'error' });
            })
            .finally(() => { document.getElementById('btnGenerarToken').disabled = false; });
    });

    // --- Generar Data (solo trae y muestra, no guarda) ---
    document.getElementById('btnGenerarData').addEventListener('click', function () {
        const fecha = inputFecha.value;
        if (!fecha) {
            Swal.fire({ title: 'Requerido', text: 'Selecciona una fecha primero.', icon: 'warning' });
            return;
        }

        this.disabled = true;
        Swal.fire({
            title: 'Generando data...',
            allowOutsideClick: false,
            allowEscapeKey: false,
            didOpen: () => Swal.showLoading(),
        });

        destroyTables();

        fetchJson(`/ventas-producto-lotobet?fecha=${encodeURIComponent(fecha)}`)
            .then(data => {
                Swal.close();
                document.getElementById('btnGenerarData').disabled = false;

                const apiError = getApiError(data);
                if (apiError) {
                    Swal.fire({ title: 'Error', text: apiError, icon: 'error' });
                    return;
                }

                if (!Array.isArray(data.ventas)) {
                    Swal.fire({ title: 'Error', text: 'La API no devolvió un listado de ventas válido.', icon: 'error' });
                    return;
                }

                ventasFuente = data.ventas;
                aplicarResumenAgencias(data.resumen_agencias);
                const { resumenAgencia } = renderResumen(data.ventas);

                labelFecha.textContent = 'Fecha: ' + fecha;
                seccion.classList.remove('d-none');

                btnVerAgencias.onclick = () => {
                    if (!resumenAgencia || resumenAgencia.size === 0) {
                        Swal.fire({ title: 'Sin datos', text: 'No hay agencias con ventas para mostrar.', icon: 'info' });
                        return;
                    }
                    abrirModalAgencias(resumenAgencia);
                };

                btnVerAgenciasSinVenta.onclick = () => {
                    if (!modalAgencyEntriesSinVentas.length) {
                        Swal.fire({ title: 'Sin datos', text: 'No hay agencias sin venta para mostrar.', icon: 'info' });
                        return;
                    }

                    abrirModalAgenciasSinVenta();
                };

                Swal.fire({ title: 'Listo', text: 'Datos obtenidos correctamente.', icon: 'success', timer: 1500, showConfirmButton: false });
            })
            .catch(err => {
                Swal.close();
                document.getElementById('btnGenerarData').disabled = false;
                Swal.fire({ title: 'Error de red', text: err.message, icon: 'error' });
            });
    });

    // --- Consultar (usa datos ya guardados en BD) ---
    btnConsultar.addEventListener('click', function () {
        const fecha = inputFecha.value;
        if (!fecha) {
            Swal.fire({ title: 'Requerido', text: 'Selecciona una fecha para consultar.', icon: 'warning' });
            return;
        }

        btnConsultar.disabled = true;
        Swal.fire({
            title: 'Consultando...',
            allowOutsideClick: false,
            allowEscapeKey: false,
            didOpen: () => Swal.showLoading(),
        });

        destroyTables();

        fetchJson(`/ventas-producto-lotobet?fecha=${encodeURIComponent(fecha)}`)
            .then(data => {
                Swal.close();
                btnConsultar.disabled = false;

                const apiError = getApiError(data);
                if (apiError) {
                    Swal.fire({ title: 'Error', text: apiError, icon: 'error' });
                    return;
                }

                if (!Array.isArray(data.ventas)) {
                    Swal.fire({ title: 'Error', text: 'La API no devolvió un listado de ventas válido.', icon: 'error' });
                    return;
                }

                ventasFuente = data.ventas;
                aplicarResumenAgencias(data.resumen_agencias);
                const { resumenAgencia } = renderResumen(data.ventas);

                labelFecha.textContent = 'Fecha: ' + fecha;
                seccion.classList.remove('d-none');

                btnVerAgencias.onclick = () => {
                    if (!resumenAgencia || resumenAgencia.size === 0) {
                        Swal.fire({ title: 'Sin datos', text: 'No hay agencias con ventas para mostrar.', icon: 'info' });
                        return;
                    }
                    abrirModalAgencias(resumenAgencia);
                };

                btnVerAgenciasSinVenta.onclick = () => {
                    if (!modalAgencyEntriesSinVentas.length) {
                        Swal.fire({ title: 'Sin datos', text: 'No hay agencias sin venta para mostrar.', icon: 'info' });
                        return;
                    }

                    abrirModalAgenciasSinVenta();
                };
            })
            .catch(err => {
                Swal.close();
                btnConsultar.disabled = false;
                Swal.fire({ title: 'Error de red', text: err.message, icon: 'error' });
            });
    });

    document.addEventListener('click', function (event) {
        const target = event.target;
        if (!target.classList.contains('btnFiltrarAgencia')) return;

        const agencia = target.getAttribute('data-agencia');
        if (!agencia) return;

        const modalInstance = bootstrap.Modal.getInstance(document.getElementById('modalAgencias'));
        if (modalInstance) modalInstance.hide();

        aplicarFiltroAgencia(agencia);
    });

    document.addEventListener('click', function (event) {
        const target = event.target;
        if (!target.classList.contains('btnSelectCiudad')) return;

        const ciudad = target.getAttribute('data-ciudad') ?? '';
        const modalInstance = bootstrap.Modal.getInstance(document.getElementById('modalAgencias'));
        if (modalInstance) modalInstance.hide();

        aplicarFiltroCiudad(ciudad);
    });

    document.addEventListener('click', function (event) {
        const target = event.target;
        if (!target.classList.contains('btnSelectRuta')) return;

        const ruta = target.getAttribute('data-ruta') ?? '';
        const modalInstance = bootstrap.Modal.getInstance(document.getElementById('modalAgencias'));
        if (modalInstance) modalInstance.hide();

        aplicarFiltroRuta(ruta);
    });

    btnLimpiarAgencia.addEventListener('click', function () {
        if (!ventasFuente.length) return;

        clearMainTables();

        renderResumen(ventasFuente);
        labelFecha.textContent = 'Fecha: ' + inputFecha.value;
        agenciaSeleccionada = null;
        ciudadSeleccionada = null;
        rutaSeleccionada = null;
        btnDropdownCiudades.textContent = 'Ciudades';
        btnDropdownRutas.textContent = 'Rutas';
        btnLimpiarAgencia.classList.add('d-none');
    });

    btnConfigVentasMinimo.addEventListener('click', function () {
        document.getElementById('inputVentasMinimo').value = ventasMinimoConfig;
        const modal = new bootstrap.Modal(document.getElementById('modalVentasMinimo'));
        modal.show();
    });

    document.getElementById('btnGuardarVentasMinimo').addEventListener('click', function () {
        const value = Number(document.getElementById('inputVentasMinimo').value ?? 0);
        ventasMinimoConfig = value >= 0 ? value : 0;
        labelMinimoVentas.textContent = `Mínimo: RD$ ${formatMoney(ventasMinimoConfig)}`;
        renderVentasExigidaLabel();

        const modalInstance = bootstrap.Modal.getInstance(document.getElementById('modalVentasMinimo'));
        if (modalInstance) modalInstance.hide();

        refrescarVistaActual();

        if (modalAgencyEntries.length) {
            renderModalAgencias();
        }
    });

    btnFiltroAgenciasTodos.addEventListener('click', function () {
        filtroCumplimientoAgencia = 'todos';
        refrescarVistaActual();
    });

    btnFiltroAgenciasCumplen.addEventListener('click', function () {
        filtroCumplimientoAgencia = 'cumplen';
        refrescarVistaActual();
    });

    btnFiltroAgenciasNoCumplen.addEventListener('click', function () {
        filtroCumplimientoAgencia = 'no_cumplen';
        refrescarVistaActual();
    });

    btnModalAgenciasTodos.addEventListener('click', function () {
        modalAgencyFilter = 'todos';
        renderModalAgencias();
    });

    btnModalAgenciasCumplen.addEventListener('click', function () {
        modalAgencyFilter = 'cumplen';
        renderModalAgencias();
    });

    btnModalAgenciasNoCumplen.addEventListener('click', function () {
        modalAgencyFilter = 'no_cumplen';
        renderModalAgencias();
    });

    btnVerAgenciasSinVenta.addEventListener('click', function () {
        if (!modalAgencyEntriesSinVentas.length) {
            Swal.fire({ title: 'Sin datos', text: 'No hay agencias sin venta para mostrar.', icon: 'info' });
            return;
        }

        abrirModalAgenciasSinVenta();
    });

    btnDescargarAgenciasSinVentaExcel.addEventListener('click', function () {
        descargarAgenciasSinVentaExcel();
    });

    btnVerTerminalesNoRegistradas.addEventListener('click', function () {
        if (!terminalesNoRegistradas.length) {
            Swal.fire({ title: 'Sin datos', text: 'No hay terminales no registradas para mostrar.', icon: 'info' });
            return;
        }

        abrirModalTerminalesNoRegistradas();
    });

    labelMinimoVentas.textContent = `Mínimo: RD$ ${formatMoney(ventasMinimoConfig)}`;
    renderVentasExigidaLabel();
    updateFiltroLabel();
    updateModalFiltroLabel();
});
</script>
@endsection
