@extends('app')

@section('title', 'KPI Lotobet - Metas y Severidad')

@section('content')
    <div class="main-content">
        <div class="page-content">
            <div class="container-fluid">
                <!-- Page Title -->
                <div class="row">
                    <div class="col-12">
                        <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                            <h4 class="mb-sm-0">KPI Lotobet - Metas y Severidad</h4>
                            <div class="page-title-right">
                                <ol class="breadcrumb m-0">
                                    <li class="breadcrumb-item"><a href="{{ route('inicio.index') }}">Inicio</a></li>
                                    <li class="breadcrumb-item"><a href="{{ route('dashboard.index') }}">Dashboard</a></li>
                                    <li class="breadcrumb-item active">KPI Lotobet</li>
                                </ol>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Filtros -->
                <div class="row mb-4 g-3">
                    <div class="col-12 col-md-4 col-lg-3">
                        <label for="anio" class="form-label">Año</label>
                        <input type="number" id="anio" class="form-control" value="{{ date('Y') }}" min="2020" max="2030">
                    </div>
                    <div class="col-12 col-md-4 col-lg-3">
                        <label for="mes" class="form-label">Mes</label>
                        <select id="mes" class="form-control">
                            <option value="1" {{ date('m') == '01' ? 'selected' : '' }}>Enero</option>
                            <option value="2" {{ date('m') == '02' ? 'selected' : '' }}>Febrero</option>
                            <option value="3" {{ date('m') == '03' ? 'selected' : '' }}>Marzo</option>
                            <option value="4" {{ date('m') == '04' ? 'selected' : '' }}>Abril</option>
                            <option value="5" {{ date('m') == '05' ? 'selected' : '' }}>Mayo</option>
                            <option value="6" {{ date('m') == '06' ? 'selected' : '' }}>Junio</option>
                            <option value="7" {{ date('m') == '07' ? 'selected' : '' }}>Julio</option>
                            <option value="8" {{ date('m') == '08' ? 'selected' : '' }}>Agosto</option>
                            <option value="9" {{ date('m') == '09' ? 'selected' : '' }}>Septiembre</option>
                            <option value="10" {{ date('m') == '10' ? 'selected' : '' }}>Octubre</option>
                            <option value="11" {{ date('m') == '11' ? 'selected' : '' }}>Noviembre</option>
                            <option value="12" {{ date('m') == '12' ? 'selected' : '' }}>Diciembre</option>
                        </select>
                    </div>
                    <div class="col-12 col-md-4 col-lg-3 d-flex align-items-end">
                        <button id="filtrar-btn" class="btn btn-primary w-100">
                            <i class="ri-search-line align-bottom me-1"></i> Filtrar
                        </button>
                    </div>
                </div>

                <!-- Parámetros de Meta -->
                <div class="row mb-3">
                    <div class="col-12">
                        <div class="card shadow-sm" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                            <div class="card-body">
                                <h5 class="card-title mb-3 text-white"><i class="ri-settings-3-line me-2"></i>Parámetros de Meta</h5>
                                <div class="row text-center">
                                    <div class="col-6 col-md-3">
                                        <p class="text-white-50 mb-1">Meta Total</p>
                                        <h5 id="param-meta-total" class="text-white fw-bold mb-0">RD$ 0</h5>
                                    </div>
                                    <div class="col-6 col-md-3">
                                        <p class="text-white-50 mb-1">Meta Tradicional (100%)</p>
                                        <h6 id="param-meta-trad" class="text-white mb-1">RD$ 0</h6>
                                        <p class="text-white-50 mb-0" style="font-size: 0.75rem;">Diario: <span id="param-meta-trad-d" class="text-white fw-semibold">RD$ 0</span></p>
                                    </div>
                                    <div class="col-6 col-md-3">
                                        <p class="text-white-50 mb-1">Meta No Tradicional (20%)</p>
                                        <h6 id="param-meta-notrad" class="text-white mb-1">RD$ 0</h6>
                                        <p class="text-white-50 mb-0" style="font-size: 0.75rem;">Diario: <span id="param-meta-notrad-d" class="text-white fw-semibold">RD$ 0</span></p>
                                    </div>
                                    <div class="col-6 col-md-3">
                                        <p class="text-white-50 mb-1">Meta Recargas (10%)</p>
                                        <h6 id="param-meta-rec" class="text-white mb-1">RD$ 0</h6>
                                        <p class="text-white-50 mb-0" style="font-size: 0.75rem;">Diario: <span id="param-meta-rec-d" class="text-white fw-semibold">RD$ 0</span></p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- KPIs de Metas Mensuales -->
                <div class="row mb-3">
                    <div class="col-12">
                        <h5 class="mb-3"><i class="ri-trophy-line me-2"></i>Cumplimiento de Metas Mensuales</h5>
                    </div>
                </div>
                <div class="row mb-4 g-3">
                    <div class="col-12 col-sm-6 col-lg-4">
                        <div class="card shadow-sm" style="border-left: 5px solid #FF6384;">
                            <div class="card-body">
                                <div class="d-flex align-items-center justify-content-between">
                                    <div>
                                        <h6 class="card-title text-muted mb-2">Meta Tradicional</h6>
                                        <h3 id="kpi-meta-trad" style="color: #FF6384;" class="mb-0">0</h3>
                                        <p class="text-muted mb-0"><small id="kpi-meta-trad-pct">0%</small> de agencias</p>
                                    </div>
                                    <div class="avatar-sm">
                                        <div class="avatar-title rounded-circle fs-3" style="background-color: rgba(255, 99, 132, 0.2); color: #FF6384;">
                                            <i class="ri-check-line"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-12 col-sm-6 col-lg-4">
                        <div class="card shadow-sm" style="border-left: 5px solid #36A2EB;">
                            <div class="card-body">
                                <div class="d-flex align-items-center justify-content-between">
                                    <div>
                                        <h6 class="card-title text-muted mb-2">Meta No Tradicional</h6>
                                        <h3 id="kpi-meta-notrad" style="color: #36A2EB;" class="mb-0">0</h3>
                                        <p class="text-muted mb-0"><small id="kpi-meta-notrad-pct">0%</small> de agencias</p>
                                    </div>
                                    <div class="avatar-sm">
                                        <div class="avatar-title rounded-circle fs-3" style="background-color: rgba(54, 162, 235, 0.2); color: #36A2EB;">
                                            <i class="ri-check-line"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-12 col-sm-6 col-lg-4">
                        <div class="card shadow-sm" style="border-left: 5px solid #FFCE56;">
                            <div class="card-body">
                                <div class="d-flex align-items-center justify-content-between">
                                    <div>
                                        <h6 class="card-title text-muted mb-2">Meta Recargas</h6>
                                        <h3 id="kpi-meta-rec" style="color: #FFCE56;" class="mb-0">0</h3>
                                        <p class="text-muted mb-0"><small id="kpi-meta-rec-pct">0%</small> de agencias</p>
                                    </div>
                                    <div class="avatar-sm">
                                        <div class="avatar-title rounded-circle fs-3" style="background-color: rgba(255, 206, 86, 0.2); color: #FFCE56;">
                                            <i class="ri-check-line"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- KPIs de Días Cumplidos -->
                <div class="row mb-3">
                    <div class="col-12">
                        <h5 class="mb-3"><i class="ri-calendar-check-line me-2"></i>Agencias por Cumplimiento</h5>
                    </div>
                </div>
                <div class="row mb-4 g-3">
                    <div class="col-12 col-sm-6 col-md-3">
                        <div class="card shadow-sm" style="border-left: 5px solid #4BC0C0; cursor: pointer;" id="card-cumplieron">
                            <div class="card-body text-center">
                                <div class="avatar-sm mx-auto mb-3">
                                    <div class="avatar-title rounded-circle fs-3" style="background-color: rgba(75, 192, 192, 0.2); color: #4BC0C0;">
                                        <i class="ri-calendar-check-line"></i>
                                    </div>
                                </div>
                                <h6 class="text-muted mb-2">Agencias que Cumplieron</h6>
                                <h3 id="kpi-agencias-cumplieron" style="color: #4BC0C0;">0</h3>
                                <p class="text-muted mb-0"><small>Con al menos 1 día cumplido</small></p>
                                <small class="text-primary"><i class="ri-eye-line"></i> Click para ver detalle</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-12 col-sm-6 col-md-3">
                        <div class="card shadow-sm" style="border-left: 5px solid #FF6384; cursor: pointer;" id="card-no-cumplieron">
                            <div class="card-body text-center">
                                <div class="avatar-sm mx-auto mb-3">
                                    <div class="avatar-title rounded-circle fs-3" style="background-color: rgba(255, 99, 132, 0.2); color: #FF6384;">
                                        <i class="ri-calendar-close-line"></i>
                                    </div>
                                </div>
                                <h6 class="text-muted mb-2">Agencias que No Cumplieron</h6>
                                <h3 id="kpi-agencias-no-cumplieron" style="color: #FF6384;">0</h3>
                                <p class="text-muted mb-0"><small>Sin días cumplidos</small></p>
                                <small class="text-primary"><i class="ri-eye-line"></i> Click para ver detalle</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-12 col-sm-6 col-md-3">
                        <div class="card shadow-sm" style="border-left: 5px solid #9966FF;">
                            <div class="card-body text-center">
                                <div class="avatar-sm mx-auto mb-3">
                                    <div class="avatar-title rounded-circle fs-3" style="background-color: rgba(153, 102, 255, 0.2); color: #9966FF;">
                                        <i class="ri-line-chart-line"></i>
                                    </div>
                                </div>
                                <h6 class="text-muted mb-2">Promedio Días Cumplidos</h6>
                                <h3 id="kpi-promedio-cumplidos" style="color: #9966FF;">0</h3>
                            </div>
                        </div>
                    </div>
                    <div class="col-12 col-sm-6 col-md-3">
                        <div class="card shadow-sm" style="border-left: 5px solid #FF9F40;">
                            <div class="card-body text-center">
                                <div class="avatar-sm mx-auto mb-3">
                                    <div class="avatar-title rounded-circle fs-3" style="background-color: rgba(255, 159, 64, 0.2); color: #FF9F40;">
                                        <i class="ri-bar-chart-line"></i>
                                    </div>
                                </div>
                                <h6 class="text-muted mb-2">Promedio Días No Cumplidos</h6>
                                <h3 id="kpi-promedio-no-cumplidos" style="color: #FF9F40;">0</h3>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- KPIs de Severidad -->
                <div class="row mb-3">
                    <div class="col-12">
                        <h5 class="mb-3"><i class="ri-bar-chart-box-line me-2"></i>Severidad por Agencia</h5>
                    </div>
                </div>
                <div class="row mb-4 g-3">
                    <div class="col-12 col-sm-6 col-lg-3">
                        <div class="card shadow-sm" style="border-left: 5px solid #4BC0C0;">
                            <div class="card-body">
                                <div class="d-flex align-items-center justify-content-between">
                                    <div>
                                        <h6 class="card-title text-muted mb-2">Excelencia</h6>
                                        <h2 id="kpi-excelencia" class="mb-0" style="color: #4BC0C0;">0</h2>
                                        <p class="text-muted mb-0"><small>21-31 días</small></p>
                                    </div>
                                    <div class="avatar-sm">
                                        <div class="avatar-title rounded-circle fs-2" style="background-color: rgba(75, 192, 192, 0.2); color: #4BC0C0;">
                                            <i class="ri-star-fill"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-12 col-sm-6 col-lg-3">
                        <div class="card shadow-sm" style="border-left: 5px solid #36A2EB;">
                            <div class="card-body">
                                <div class="d-flex align-items-center justify-content-between">
                                    <div>
                                        <h6 class="card-title text-muted mb-2">Estable</h6>
                                        <h2 id="kpi-estable" class="mb-0" style="color: #36A2EB;">0</h2>
                                        <p class="text-muted mb-0"><small>11-20 días</small></p>
                                    </div>
                                    <div class="avatar-sm">
                                        <div class="avatar-title rounded-circle fs-2" style="background-color: rgba(54, 162, 235, 0.2); color: #36A2EB;">
                                            <i class="ri-shield-check-line"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-12 col-sm-6 col-lg-3">
                        <div class="card shadow-sm" style="border-left: 5px solid #FFCE56;">
                            <div class="card-body">
                                <div class="d-flex align-items-center justify-content-between">
                                    <div>
                                        <h6 class="card-title text-muted mb-2">En Riesgo</h6>
                                        <h2 id="kpi-riesgo" class="mb-0" style="color: #FFCE56;">0</h2>
                                        <p class="text-muted mb-0"><small>3-10 días</small></p>
                                    </div>
                                    <div class="avatar-sm">
                                        <div class="avatar-title rounded-circle fs-2" style="background-color: rgba(255, 206, 86, 0.2); color: #FFCE56;">
                                            <i class="ri-alert-line"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-12 col-sm-6 col-lg-3">
                        <div class="card shadow-sm" style="border-left: 5px solid #FF6384;">
                            <div class="card-body">
                                <div class="d-flex align-items-center justify-content-between">
                                    <div>
                                        <h6 class="card-title text-muted mb-2">Crítica</h6>
                                        <h2 id="kpi-critica" class="mb-0" style="color: #FF6384;">0</h2>
                                        <p class="text-muted mb-0"><small>0-2 días</small></p>
                                    </div>
                                    <div class="avatar-sm">
                                        <div class="avatar-title rounded-circle fs-2" style="background-color: rgba(255, 99, 132, 0.2); color: #FF6384;">
                                            <i class="ri-error-warning-line"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Tabla de Detalle -->
                <div class="row">
                    <div class="col-12">
                        <div class="card shadow-sm">
                            <div class="card-header">
                                <h5 class="card-title mb-0"><i class="ri-table-line me-2"></i>Detalle por Agencia</h5>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table id="tabla-kpi" class="table table-bordered table-striped align-middle" style="width:100%; font-size: 0.875rem;">
                                        <thead class="table-light">
                                            <tr>
                                                <th>Agencia</th>
                                                <th>Meta Tradicional</th>
                                                <th>Meta No Tradicional</th>
                                                <th>Meta Recargas</th>
                                                <th>Días Cumplidos</th>
                                                <th>Días No Cumplidos</th>
                                                <th>Severidad</th>
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

    <!-- Modal Agencias Cumplieron -->
    <div class="modal fade" id="modalAgenciasCumplieron" tabindex="-1" aria-labelledby="modalAgenciasCumplieronLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title" id="modalAgenciasCumplieronLabel">
                        <i class="ri-calendar-check-line me-2"></i>Agencias que Cumplieron - Detalle
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="table-responsive">
                        <table id="tabla-cumplieron" class="table table-bordered table-striped table-sm" style="width:100%">
                            <thead class="table-light">
                                <tr>
                                    <th>Agencia</th>
                                    <th>Días Cumplidos</th>
                                    <th>Cumplimiento Tradicional</th>
                                    <th>Cumplimiento No Tradicional</th>
                                    <th>Cumplimiento Recargas</th>
                                    <th>Severidad</th>
                                    <th>Acciones</th>
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

    <!-- Modal Agencias No Cumplieron -->
    <div class="modal fade" id="modalAgenciasNoCumplieron" tabindex="-1" aria-labelledby="modalAgenciasNoCumplieronLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title" id="modalAgenciasNoCumplieronLabel">
                        <i class="ri-calendar-close-line me-2"></i>Agencias que No Cumplieron - Detalle
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="table-responsive">
                        <table id="tabla-no-cumplieron" class="table table-bordered table-striped table-sm" style="width:100%">
                            <thead class="table-light">
                                <tr>
                                    <th>Agencia</th>
                                    <th>Días No Cumplidos</th>
                                    <th>Meta Tradicional</th>
                                    <th>Meta No Tradicional</th>
                                    <th>Meta Recargas</th>
                                    <th>Severidad</th>
                                    <th>Acciones</th>
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

    <!-- Modal Detalle de Productos por Agencia -->
    <div class="modal fade" id="modalProductosAgencia" tabindex="-1" aria-labelledby="modalProductosAgenciaLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title text-white" id="modalProductosAgenciaLabel">
                        <i class="ri-bar-chart-box-line me-2"></i>Detalle de Productos Por Agencia <span id="nombre-agencia-productos" class="text-white"></span>
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-12 col-lg-4 mb-3">
                            <div class="card shadow-sm border-success">
                                <div class="card-header bg-success text-white">
                                    <h6 class="mb-0"><i class="ri-fire-line me-2"></i>Top 10 Más Vendidos</h6>
                                </div>
                                <div class="card-body p-0">
                                    <div class="table-responsive" style="max-height: 400px; overflow-y: auto;">
                                        <table class="table table-sm table-hover mb-0">
                                            <thead class="table-light sticky-top">
                                                <tr>
                                                    <th>#</th>
                                                    <th>Producto</th>
                                                    <th class="text-end">Ventas</th>
                                                </tr>
                                            </thead>
                                            <tbody id="tabla-mas-vendidos"></tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-12 col-lg-4 mb-3">
                            <div class="card shadow-sm border-info">
                                <div class="card-header bg-info text-white">
                                    <h6 class="mb-0"><i class="ri-line-chart-line me-2"></i>Top 10 Regulares</h6>
                                </div>
                                <div class="card-body p-0">
                                    <div class="table-responsive" style="max-height: 400px; overflow-y: auto;">
                                        <table class="table table-sm table-hover mb-0">
                                            <thead class="table-light sticky-top">
                                                <tr>
                                                    <th>#</th>
                                                    <th>Producto</th>
                                                    <th class="text-end">Ventas</th>
                                                </tr>
                                            </thead>
                                            <tbody id="tabla-regulares"></tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-12 col-lg-4 mb-3">
                            <div class="card shadow-sm border-warning">
                                <div class="card-header bg-warning text-white">
                                    <h6 class="mb-0"><i class="ri-arrow-down-line me-2"></i>Top 10 Menos Vendidos</h6>
                                </div>
                                <div class="card-body p-0">
                                    <div class="table-responsive" style="max-height: 400px; overflow-y: auto;">
                                        <table class="table table-sm table-hover mb-0">
                                            <thead class="table-light sticky-top">
                                                <tr>
                                                    <th>#</th>
                                                    <th>Producto</th>
                                                    <th class="text-end">Ventas</th>
                                                </tr>
                                            </thead>
                                            <tbody id="tabla-menos-vendidos"></tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Volver</button>
                </div>
            </div>
        </div>
    </div>

    <footer class="footer">
        <div class="container-fluid">
            <div class="row">
                <div class="col-sm-6">
                    <script>document.write(new Date().getFullYear())</script> © ERP.
                </div>
            </div>
        </div>
    </footer>
@endsection

@section('script')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js"></script>
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css">

<script>
    let tableInstance = null;
    let tableCumplieron = null;
    let tableNoCumplieron = null;
    let currentData = null;

    function formatCurrency(value) {
        return new Intl.NumberFormat('es-DO', {
            style: 'currency',
            currency: 'DOP',
            minimumFractionDigits: 0,
            maximumFractionDigits: 0
        }).format(value || 0);
    }

    function loadData() {
        const anio = document.getElementById('anio').value;
        const mes = document.getElementById('mes').value;

        Swal.fire({
            title: 'Cargando datos...',
            text: 'Por favor espere.',
            allowOutsideClick: false,
            showConfirmButton: false,
            didOpen: () => Swal.showLoading()
        });

        fetch(`/kpi-lotobet/data?anio=${anio}&mes=${mes}`)
            .then(response => {
                if (!response.ok) {
                    throw new Error('Error al consultar datos');
                }
                return response.json();
            })
            .then(data => {
                Swal.close();
                currentData = data;

                // Actualizar parámetros
                document.getElementById('param-meta-total').textContent = formatCurrency(data.parametros.meta_total);
                document.getElementById('param-meta-trad').textContent = formatCurrency(data.parametros.meta_trad);
                document.getElementById('param-meta-notrad').textContent = formatCurrency(data.parametros.meta_notrad);
                document.getElementById('param-meta-rec').textContent = formatCurrency(data.parametros.meta_rec);
                
                // Actualizar parámetros diarios
                document.getElementById('param-meta-trad-d').textContent = formatCurrency(data.parametros.meta_trad_d);
                document.getElementById('param-meta-notrad-d').textContent = formatCurrency(data.parametros.meta_notrad_d);
                document.getElementById('param-meta-rec-d').textContent = formatCurrency(data.parametros.meta_rec_d);

                // Actualizar KPIs de metas
                document.getElementById('kpi-meta-trad').textContent = data.kpis.cumplio_trad;
                document.getElementById('kpi-meta-trad-pct').textContent = data.kpis.pct_cumplio_trad + '%';
                document.getElementById('kpi-meta-notrad').textContent = data.kpis.cumplio_notrad;
                document.getElementById('kpi-meta-notrad-pct').textContent = data.kpis.pct_cumplio_notrad + '%';
                document.getElementById('kpi-meta-rec').textContent = data.kpis.cumplio_rec;
                document.getElementById('kpi-meta-rec-pct').textContent = data.kpis.pct_cumplio_rec + '%';

                // Actualizar KPIs de días
                document.getElementById('kpi-agencias-cumplieron').textContent = data.kpis.agencias_cumplieron;
                document.getElementById('kpi-agencias-no-cumplieron').textContent = data.kpis.agencias_no_cumplieron;
                document.getElementById('kpi-promedio-cumplidos').textContent = data.kpis.promedio_dias_cumplidos;
                document.getElementById('kpi-promedio-no-cumplidos').textContent = data.kpis.promedio_dias_no_cumplidos;

                // Actualizar KPIs de severidad
                document.getElementById('kpi-excelencia').textContent = data.severidad.Excelencia;
                document.getElementById('kpi-estable').textContent = data.severidad.Estable;
                document.getElementById('kpi-riesgo').textContent = data.severidad['En riesgo'];
                document.getElementById('kpi-critica').textContent = data.severidad['Crítica'];

                // Actualizar tabla
                if (tableInstance) {
                    tableInstance.destroy();
                }

                const tableData = data.tabla.map(row => [
                    row.agencia,
                    `<span class="badge ${row.MetaMensual_Tra === 'Cumplio' ? 'bg-success' : 'bg-warning'}">${row.MetaMensual_Tra}</span>`,
                    `<span class="badge ${row.MetaMensual_NoTra === 'Cumplio' ? 'bg-success' : 'bg-warning'}">${row.MetaMensual_NoTra}</span>`,
                    `<span class="badge ${row.MetaMensual_Rec === 'Cumplio' ? 'bg-success' : 'bg-warning'}">${row.MetaMensual_Rec}</span>`,
                    parseInt(row.Cant_Dias_Cumplido),
                    parseInt(row.Cant_No_Cumplido),
                    `<span class="badge ${
                        row.Severidad === 'Excelencia' ? 'bg-success' :
                        row.Severidad === 'Estable' ? 'bg-info' :
                        row.Severidad === 'En riesgo' ? 'bg-warning' :
                        'bg-danger'
                    }">${row.Severidad}</span>`
                ]);

                tableInstance = $('#tabla-kpi').DataTable({
                    data: tableData,
                    responsive: true,
                    scrollX: true,
                    language: {
                        url: '//cdn.datatables.net/plug-ins/1.11.5/i18n/es-ES.json'
                    },
                    order: [[4, 'desc']]
                });
            })
            .catch(error => {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: error.message
                });
            });
    }

    function showModalCumplieron() {
        if (!currentData || !currentData.tabla) return;

        const agenciasCumplieron = currentData.tabla.filter(row => parseInt(row.Cant_Dias_Cumplido) > 0);

        if (tableCumplieron) {
            tableCumplieron.destroy();
        }

        const tableData = agenciasCumplieron.map(row => [
            row.agencia,
            parseInt(row.Cant_Dias_Cumplido),
            `<span class="badge ${row.MetaMensual_Tra === 'Cumplio' ? 'bg-success' : 'bg-warning'}">${row.MetaMensual_Tra}</span>`,
            `<span class="badge ${row.MetaMensual_NoTra === 'Cumplio' ? 'bg-success' : 'bg-warning'}">${row.MetaMensual_NoTra}</span>`,
            `<span class="badge ${row.MetaMensual_Rec === 'Cumplio' ? 'bg-success' : 'bg-warning'}">${row.MetaMensual_Rec}</span>`,
            `<span class="badge ${
                row.Severidad === 'Excelencia' ? 'bg-success' :
                row.Severidad === 'Estable' ? 'bg-info' :
                row.Severidad === 'En riesgo' ? 'bg-warning' :
                'bg-danger'
            }">${row.Severidad}</span>`,
            `<button class="btn btn-sm btn-primary ver-productos" data-agencia="${row.agencia}">
                <i class="ri-eye-line"></i> Ver
            </button>`
        ]);

        tableCumplieron = $('#tabla-cumplieron').DataTable({
            data: tableData,
            responsive: true,
            scrollY: '400px',
            scrollCollapse: true,
            pageLength: 50,
            language: {
                url: '//cdn.datatables.net/plug-ins/1.11.5/i18n/es-ES.json'
            },
            order: [[1, 'desc']],
            drawCallback: function() {
                // Adjuntar eventos a los botones después del renderizado
                $('#tabla-cumplieron .ver-productos').off('click').on('click', function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    const agencia = $(this).data('agencia');
                    console.log('Click en ver productos, agencia:', agencia);
                    verProductosAgencia(agencia);
                });
            }
        });

        new bootstrap.Modal(document.getElementById('modalAgenciasCumplieron')).show();
    }

    function showModalNoCumplieron() {
        if (!currentData || !currentData.tabla) return;

        const agenciasNoCumplieron = currentData.tabla.filter(row => parseInt(row.Cant_Dias_Cumplido) === 0);

        if (tableNoCumplieron) {
            tableNoCumplieron.destroy();
        }

        const tableData = agenciasNoCumplieron.map(row => [
            row.agencia,
            parseInt(row.Cant_No_Cumplido),
            `<span class="badge ${row.MetaMensual_Tra === 'Cumplio' ? 'bg-success' : 'bg-warning'}">${row.MetaMensual_Tra}</span>`,
            `<span class="badge ${row.MetaMensual_NoTra === 'Cumplio' ? 'bg-success' : 'bg-warning'}">${row.MetaMensual_NoTra}</span>`,
            `<span class="badge ${row.MetaMensual_Rec === 'Cumplio' ? 'bg-success' : 'bg-warning'}">${row.MetaMensual_Rec}</span>`,
            `<span class="badge ${
                row.Severidad === 'Excelencia' ? 'bg-success' :
                row.Severidad === 'Estable' ? 'bg-info' :
                row.Severidad === 'En riesgo' ? 'bg-warning' :
                'bg-danger'
            }">${row.Severidad}</span>`,
            `<button class="btn btn-sm btn-primary ver-productos" data-agencia="${row.agencia}">
                <i class="ri-eye-line"></i> Ver
            </button>`
        ]);

        tableNoCumplieron = $('#tabla-no-cumplieron').DataTable({
            data: tableData,
            responsive: true,
            scrollY: '400px',
            scrollCollapse: true,
            pageLength: 50,
            language: {
                url: '//cdn.datatables.net/plug-ins/1.11.5/i18n/es-ES.json'
            },
            order: [[1, 'desc']],
            drawCallback: function() {
                // Adjuntar eventos a los botones después del renderizado
                $('#tabla-no-cumplieron .ver-productos').off('click').on('click', function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    const agencia = $(this).data('agencia');
                    console.log('Click en ver productos, agencia:', agencia);
                    verProductosAgencia(agencia);
                });
            }
        });

        new bootstrap.Modal(document.getElementById('modalAgenciasNoCumplieron')).show();
    }

    function verProductosAgencia(agencia) {
        console.log('verProductosAgencia llamado con:', agencia);
        const anio = document.getElementById('anio').value;
        const mes = document.getElementById('mes').value;

        document.getElementById('nombre-agencia-productos').textContent = agencia;

        Swal.fire({
            title: 'Cargando productos...',
            text: 'Por favor espere.',
            allowOutsideClick: false,
            showConfirmButton: false,
            didOpen: () => Swal.showLoading()
        });

        fetch(`/kpi-lotobet/productos-agencia?agencia=${encodeURIComponent(agencia)}&anio=${anio}&mes=${mes}`)
            .then(response => {
                if (!response.ok) {
                    throw new Error('Error al consultar productos');
                }
                return response.json();
            })
            .then(data => {
                Swal.close();

                // Llenar tabla de más vendidos
                const tablaMasVendidos = document.getElementById('tabla-mas-vendidos');
                tablaMasVendidos.innerHTML = '';
                data.mas_vendidos.forEach((prod, index) => {
                    tablaMasVendidos.innerHTML += `
                        <tr>
                            <td><span class="badge bg-success">${index + 1}</span></td>
                            <td>${prod.producto}</td>
                            <td class="text-end fw-bold">${formatCurrency(prod.ventas)}</td>
                        </tr>
                    `;
                });

                // Llenar tabla de regulares
                const tablaRegulares = document.getElementById('tabla-regulares');
                tablaRegulares.innerHTML = '';
                data.regulares.forEach((prod, index) => {
                    tablaRegulares.innerHTML += `
                        <tr>
                            <td><span class="badge bg-info">${index + 1}</span></td>
                            <td>${prod.producto}</td>
                            <td class="text-end fw-bold">${formatCurrency(prod.ventas)}</td>
                        </tr>
                    `;
                });

                // Llenar tabla de menos vendidos
                const tablaMenosVendidos = document.getElementById('tabla-menos-vendidos');
                tablaMenosVendidos.innerHTML = '';
                data.menos_vendidos.forEach((prod, index) => {
                    tablaMenosVendidos.innerHTML += `
                        <tr>
                            <td><span class="badge bg-warning">${index + 1}</span></td>
                            <td>${prod.producto}</td>
                            <td class="text-end fw-bold">${formatCurrency(prod.ventas)}</td>
                        </tr>
                    `;
                });

                new bootstrap.Modal(document.getElementById('modalProductosAgencia')).show();
            })
            .catch(error => {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: error.message
                });
            });
    }

    document.addEventListener('DOMContentLoaded', function() {
        document.getElementById('filtrar-btn').addEventListener('click', loadData);
        document.getElementById('card-cumplieron').addEventListener('click', showModalCumplieron);
        document.getElementById('card-no-cumplieron').addEventListener('click', showModalNoCumplieron);
        // loadData();
    });
</script>
@endsection

