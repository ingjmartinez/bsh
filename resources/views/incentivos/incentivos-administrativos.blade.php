@extends('app')

@section('content')
    @php
        $canConfigAdminPct = auth()->check()
            && method_exists(auth()->user(), 'hasRole')
            && auth()->user()->hasRole('superadmin');
    @endphp

    <style>
        .empleado-maestro-pendiente {
            background: #fff7df;
            border: 1px solid #fde8ae;
            border-radius: 999px;
            color: #9a6a12;
            display: inline-block;
            font-size: .78rem;
            font-weight: 600;
            padding: .22rem .55rem;
        }

        #tableNuevoIncentivo th,
        #tableNuevoIncentivo td {
            vertical-align: middle;
        }

        #tableNuevoIncentivo thead th {
            font-size: .78rem;
            line-height: 1.2;
            white-space: nowrap !important;
        }

        #tableNuevoIncentivo td {
            white-space: nowrap;
        }

        #tableNuevoIncentivo .col-cedula {
            min-width: 105px;
        }

        #tableNuevoIncentivo .col-nombre {
            min-width: 190px;
        }

        #tableNuevoIncentivo .col-empresa {
            min-width: 120px;
        }

        #tableNuevoIncentivo .col-monto {
            min-width: 118px;
        }

        #tableNuevoIncentivo .col-dias {
            min-width: 68px;
        }

        #tableNuevoIncentivo .col-horas {
            min-width: 82px;
        }

        #tableNuevoIncentivo .col-regla {
            min-width: 145px;
        }

        #tableNuevoIncentivo .cell-nombre {
            white-space: normal;
            min-width: 190px;
        }

        .ni-count-action {
            cursor: pointer;
            transition: transform .15s ease, color .15s ease;
        }

        .ni-count-action:hover {
            color: #d97706 !important;
            transform: translateY(-1px);
        }

        .calendar-payment-grid {
            contain: layout paint;
            max-height: 68vh;
            overflow: auto;
        }

        .calendar-payment-grid th {
            background: var(--vz-light);
            position: sticky;
            top: 0;
            vertical-align: middle;
            z-index: 3;
        }

        .calendar-payment-grid .calendar-terminal-column {
            background: var(--vz-card-bg);
            left: 0;
            min-width: 260px;
            position: sticky;
            z-index: 2;
        }

        .calendar-payment-grid th.calendar-terminal-column {
            background: var(--vz-light);
            z-index: 4;
        }

        .calendar-payment-cell {
            min-width: 112px;
        }

        .calendar-payment-cell.payment-60 {
            background-color: #d1e7dd;
        }

        .calendar-payment-cell.payment-70 {
            background-color: #cff4fc;
        }

        .calendar-payment-cell.payment-80 {
            background-color: #fff3cd;
        }

        .calendar-payment-cell.payment-default {
            background-color: #f3f6f9;
        }

        .agencia-formato-comparativa .agencia-nombre {
            color: #172033;
            font-weight: 600;
            line-height: 1.15;
        }

        .agencia-formato-comparativa .agencia-terminal {
            color: #64748b;
            display: block;
            font-size: .78rem;
            margin-top: .15rem;
        }

        .hora-total-badge {
            border-radius: 999px;
            display: inline-block;
            font-weight: 700;
            min-width: 74px;
            padding: .22rem .55rem;
        }

        .hora-total-cumple {
            background: #dcfce7;
            color: #166534;
        }

        .hora-total-no-cumple {
            background: #fee2e2;
            color: #991b1b;
        }

        .terminal-excluida-list {
            max-height: 220px;
            overflow-y: auto;
        }
    </style>

    <div class="main-content">
        <div class="page-content">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-12">
                        <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                            <h4 class="mb-sm-0">Cálculo de Incentivos</h4>
                            <div class="page-title-right">
                                <ol class="breadcrumb m-0">
                                    <li class="breadcrumb-item"><a href="{{ route('inicio.index') }}">Inicio</a></li>
                                    <li class="breadcrumb-item"><a href="{{ route('incentivos.index') }}">Incentivos</a></li>
                                    <li class="breadcrumb-item active">Cálculo de Incentivos</li>
                                </ol>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-lg-4 col-md-6">
                        <div class="card card-animate">
                            <div class="card-body">
                                <p class="text-uppercase fw-medium text-muted mb-1">Total Vendido Bruto</p>
                                <h4 class="mb-0" id="ni_total_vendido">0</h4>
                            </div>
                        </div>
                        <div class="card card-animate mt-3">
                            <div class="card-body d-flex justify-content-between align-items-center gap-3 flex-wrap">
                                <div>
                                    <p class="text-uppercase fw-medium text-muted mb-1">Usuarios que Cumplieron</p>
                                    <h4 class="mb-0 text-success" id="ni_count_cumplen">0</h4>
                                </div>
                                <div>
                                    <p class="text-uppercase fw-medium text-muted mb-1">Usuarios que No Cumplieron</p>
                                    <h4 class="mb-0 text-danger" id="ni_count_no_cumplen">0</h4>
                                </div>
                                <div>
                                    <p class="text-uppercase fw-medium text-muted mb-1">Usuarios por Actualizar</p>
                                    <h4 class="mb-0 text-warning ni-count-action" id="ni_count_por_actualizar" title="Ver cedulas por actualizar">0</h4>
                                </div>
                                <div>
                                    <p class="text-uppercase fw-medium text-muted mb-1">Agencias sin Empresa</p>
                                    <h4 class="mb-0 text-info ni-count-action" id="ni_count_agencias_sin_empresa" title="Ver terminales pendientes de empresa">0</h4>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-4 col-md-6">
                        <div class="card card-animate">
                            <div class="card-body text-start" style="min-height: 178px;">
                                <p class="text-uppercase fw-medium text-muted mb-1">Desglose de porcentajes</p>
                                <div class="table-responsive mt-2">
                                    <table class="table table-sm table-bordered mb-0 align-middle">
                                        <thead class="table-light">
                                            <tr>
                                                <th>Categoria</th>
                                                <th class="text-end">% Configurado</th>
                                                <th class="text-end">Monto</th>
                                            </tr>
                                        </thead>
                                        <tbody id="ni_pct_puesto_resumen">
                                            <tr>
                                                <td>1 Gtes. y Encarg.</td>
                                                <td class="text-end">0.00%</td>
                                                <td class="text-end">0</td>
                                            </tr>
                                            <tr>
                                                <td>2 Monitoreo</td>
                                                <td class="text-end">0.00%</td>
                                                <td class="text-end">0</td>
                                            </tr>
                                            <tr>
                                                <td>4 Operadores + 5 Servs. Tecnicos + 6 Seguridad</td>
                                                <td class="text-end">0.00%</td>
                                                <td class="text-end">0</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-4 col-md-6">
                        <div class="card card-animate">
                            <div class="card-body text-start" style="min-height: 178px;">
                                <p class="text-uppercase fw-medium text-muted mb-1">Total Incentivo Bruto</p>
                                <h4 class="mb-0" id="ni_total_incentivo">0</h4>
                                <div class="d-block mt-1 fw-semibold fs-5 text-primary text-start" id="ni_admin_resumen">
                                    <div>Porcentaje (10%): 0</div>
                                    <div>Administrativo: 0</div>
                                    <div>Coordinador: 0</div>
                                </div>
                                <div class="mt-2 fw-bold fs-4 text-success text-start" id="ni_total_con_admin">Total a Pagar Final: 0</div>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-12">
                        <div class="card" id="cardCalculoIncentivos">
                            <div class="card-header d-flex align-items-center justify-content-between">
                                <div>
                                    <h5 class="card-title mb-0">Calculo por sistema y tipo de pago</h5>
                                    <small class="text-muted">Configura tramos de venta mensual por pago a 60, 70 u 80.</small>
                                </div>
                                <div class="d-flex gap-3 align-items-end flex-wrap">
                                    <div>
                                        <label class="mb-0" for="ni_sistema">Sistema</label>
                                        <select id="ni_sistema" class="form-select">
                                            <option value="Todos">Todos</option>
                                            <option value="Lotobet">Lotobet</option>
                                            <option value="Lotonet">Lotonet</option>
                                        </select>
                                    </div>
                                    <div>
                                        <label class="mb-0" for="ni_filtro_cumplimiento">Cumplimiento</label>
                                        <select id="ni_filtro_cumplimiento" class="form-select">
                                            <option value="todos">Todos</option>
                                            <option value="cumplidos">Cumplidos</option>
                                            <option value="no_cumplidos">No cumplidos</option>
                                        </select>
                                    </div>
                                    <div>
                                        <label class="mb-0" for="ni_filtro_empresa">Empresa</label>
                                        <select id="ni_filtro_empresa" class="form-select">
                                            <option value="todos">Todas</option>
                                        </select>
                                    </div>
                                    <div>
                                        <label class="mb-0" for="ni_modo_calculo">Modo de calculo</label>
                                        <select id="ni_modo_calculo" class="form-select">
                                            <option value="general">General consolidado</option>
                                            <option value="separado_empresa">Separado por empresa</option>
                                        </select>
                                    </div>
                                    <input type="hidden" id="ni_alcance_productos" value="completo">
                                    <button type="button" class="btn btn-info" id="btnFiltrarCumplimiento">Filtrar</button>
                                    <div>
                                        <label class="mb-0" for="ni_fecha_ini">Fecha inicio</label>
                                        <input type="date" id="ni_fecha_ini" class="form-control">
                                    </div>
                                    <div>
                                        <label class="mb-0" for="ni_fecha_fin">Fecha fin</label>
                                        <input type="date" id="ni_fecha_fin" class="form-control">
                                    </div>
                                    <div>
                                        <label class="mb-0" for="ni_min_dias">Min. dias venta</label>
                                        <input type="number" id="ni_min_dias" class="form-control" value="1" min="1" step="1">
                                    </div>
                                    <div>
                                        <label class="mb-0" for="ni_tipo_pago">Tipo de pago</label>
                                        <select id="ni_tipo_pago" class="form-select">
                                            <option value="tramos_60">Pagos a 60</option>
                                            <option value="tramos_70">Pagos a 70</option>
                                            <option value="tramos_80">Pagos a 80</option>
                                        </select>
                                    </div>
                                    <button type="button" class="btn btn-soft-secondary" id="btnConfigPct">Configurar Tipo de Pago</button>
                                    <button type="button" class="btn btn-soft-secondary" id="btnConfigPuestoPct">Configurar % de puesto</button>
                                    <button
                                        type="button"
                                        class="btn btn-soft-secondary"
                                        id="btnConfigAdminPct"
                                        @if(!$canConfigAdminPct) disabled title="Solo superadmin puede modificar este porcentaje" @endif>
                                        Porcentaje
                                    </button>
                                    <button type="button" class="btn btn-soft-secondary" id="btnConfigAdministrativos">Administrativo</button>
                                    <button type="button" class="btn btn-soft-secondary" id="btnConfigCoordinadores">Coordinador</button>
                                    <button type="button" class="btn btn-soft-secondary" id="btnConfigHorasTotal">Configurar Horas</button>
                                    <button type="button" class="btn btn-soft-primary" id="btnCalendarioTiposPago">
                                        <i class="ri-calendar-check-line me-1"></i>Calendario de pagos
                                    </button>
                                    <button type="button" class="btn btn-soft-secondary" id="btnExcluirTerminales">
                                        Excluir Terminales <span class="badge bg-danger ms-1" id="terminalesExcluidasCount">0</span>
                                    </button>
                                    <button type="button" class="btn btn-primary" id="btnGenerarNuevoIncentivo">Generar Reporte</button>
                                    <button type="button" class="btn btn-success" id="btnGuardarPeriodoIncentivo" disabled>
                                        <i class="ri-save-3-line me-1"></i>Guardar período
                                    </button>
                                    <button type="button" class="btn btn-dark" id="btnGenerarExcelPago">Generar Excel de pago</button>
                                    <button type="button" class="btn btn-danger" id="btnPreNomina">
                                        <i class="ri-file-pdf-2-line me-1"></i>Pre nómina
                                    </button>
                                    <button type="button" class="btn btn-info" id="btnValidacionGerencial">Validacion Gerencial</button>
                                    <button type="button" class="btn btn-info" id="btnInformeGerencialProceso">Informe Gerencial PDF</button>
                                    <button type="button" class="btn btn-info" id="btnDetalleCalendarioPdf">Detalle Calendario PDF</button>
                                    <button type="button" class="btn btn-warning" id="btnConsultarFaltantes">
                                        Faltantes <span class="badge bg-secondary ms-1" id="estadoFaltantesPeriodo">Pendiente</span>
                                    </button>
                                    <button type="button" class="btn btn-success" id="btnConsultarDesvinculados">
                                        Usu. Desvinculados <span class="badge bg-secondary ms-1" id="estadoDesvinculadosPeriodo">Pendiente</span>
                                    </button>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="mb-2 text-muted" id="ni_rango_evaluado"></div>
                                <table id="tableNuevoIncentivo" class="table table-bordered dt-responsive nowrap table-striped align-middle" style="width:100%">
                                    <thead>
                                        <tr>
                                            <th class="col-cedula">Cedula</th>
                                            <th>IdEmpleado</th>
                                            <th class="col-nombre">Nombre</th>
                                            <th class="col-empresa">Empresa</th>
                                            <th>Tipos de pago</th>
                                            <th class="col-monto text-end">Ventas Ult. Mes</th>
                                            <th class="col-monto text-end">Ventas Mes Actual</th>
                                            <th class="col-dias text-center">Dias</th>
                                            <th class="col-horas text-center">Hora Total</th>
                                            <th class="col-regla">Cumple Regla</th>
                                            <th class="col-monto text-end">Total a Pagar</th>
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

    <div id="modalAlcanceReporte" class="modal fade" tabindex="-1" aria-labelledby="modalAlcanceReporteTitulo" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <div>
                        <h5 class="modal-title" id="modalAlcanceReporteTitulo">Generar reporte</h5>
                        <small class="text-muted">Selecciona el alcance que deseas consultar.</small>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body">
                    <div class="d-grid gap-3">
                        <button type="button" class="btn btn-primary btn-lg text-start d-flex align-items-center js-generar-reporte-alcance" data-alcance="completo">
                            <i class="ri-file-list-3-line me-2"></i>
                            <span>
                                <strong class="d-block">Generar completo</strong>
                                <small class="fw-normal">Incluye todos los productos del reporte.</small>
                            </span>
                        </button>
                        <button type="button" class="btn btn-outline-primary btn-lg text-start d-flex align-items-center js-generar-reporte-alcance" data-alcance="no_tradicionales">
                            <i class="ri-filter-3-line me-2"></i>
                            <span>
                                <strong class="d-block">Generar no tradicional</strong>
                                <small class="fw-normal">Incluye exclusivamente productos no tradicionales.</small>
                            </span>
                        </button>
                    </div>
                    <p class="small text-muted mb-0 mt-3">
                        Esta selecci&oacute;n solo afecta la generaci&oacute;n actual y no modifica exclusiones guardadas.
                    </p>
                </div>
            </div>
        </div>
    </div>

    <div id="modalPreNomina" class="modal fade" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <div>
                        <h5 class="modal-title">Pre nómina</h5>
                        <small class="text-muted">Genera los PDF separados por tipo de personal y empresa.</small>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body bg-light">
                    <div class="row g-3">
                        <div class="col-md-6 col-xl-4">
                            <div class="card h-100 border-primary shadow-sm">
                                <div class="card-body d-flex flex-column">
                                    <span class="badge bg-primary-subtle text-primary align-self-start mb-3">Administrativos</span>
                                    <h5 class="card-title">Joselito</h5>
                                    <p class="text-muted mb-3">Personal administrativo y operativo de Consorcio Joselito.</p>
                                    <div class="small mb-3">
                                        <div><strong id="preNominaAdminJoselitoCount">0</strong> registros</div>
                                        <div>RD$<strong id="preNominaAdminJoselitoTotal">0</strong></div>
                                    </div>
                                    <div class="d-flex gap-2 mt-auto">
                                        <button type="button" class="btn btn-primary flex-fill btn-pre-nomina-pdf"
                                            data-tipo="administrativos" data-empresa="joselito" data-formato="pdf">
                                            <i class="ri-file-pdf-2-line me-1"></i>PDF
                                        </button>
                                        <button type="button" class="btn btn-outline-success flex-fill btn-pre-nomina-excel"
                                            data-tipo="administrativos" data-empresa="joselito" data-formato="excel">
                                            <i class="ri-file-excel-2-line me-1"></i>Excel
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 col-xl-4">
                            <div class="card h-100 border-info shadow-sm">
                                <div class="card-body d-flex flex-column">
                                    <span class="badge bg-info-subtle text-info align-self-start mb-3">Administrativos</span>
                                    <h5 class="card-title">Negosur</h5>
                                    <p class="text-muted mb-3">Personal administrativo y operativo de Negosur.</p>
                                    <div class="small mb-3">
                                        <div><strong id="preNominaAdminNegosurCount">0</strong> registros</div>
                                        <div>RD$<strong id="preNominaAdminNegosurTotal">0</strong></div>
                                    </div>
                                    <div class="d-flex gap-2 mt-auto">
                                        <button type="button" class="btn btn-info flex-fill btn-pre-nomina-pdf"
                                            data-tipo="administrativos" data-empresa="negosur" data-formato="pdf">
                                            <i class="ri-file-pdf-2-line me-1"></i>PDF
                                        </button>
                                        <button type="button" class="btn btn-outline-success flex-fill btn-pre-nomina-excel"
                                            data-tipo="administrativos" data-empresa="negosur" data-formato="excel">
                                            <i class="ri-file-excel-2-line me-1"></i>Excel
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 col-xl-4">
                            <div class="card h-100 border-success shadow-sm">
                                <div class="card-body d-flex flex-column">
                                    <span class="badge bg-success-subtle text-success align-self-start mb-3">Coordinadores</span>
                                    <h5 class="card-title">Joselito</h5>
                                    <p class="text-muted mb-3">Coordinadores asignados al grupo Joselito.</p>
                                    <div class="small mb-3">
                                        <div><strong id="preNominaCoordJoselitoCount">0</strong> registros</div>
                                        <div>RD$<strong id="preNominaCoordJoselitoTotal">0</strong></div>
                                    </div>
                                    <div class="d-flex gap-2 mt-auto">
                                        <button type="button" class="btn btn-success flex-fill btn-pre-nomina-pdf"
                                            data-tipo="coordinadores" data-empresa="joselito" data-formato="pdf">
                                            <i class="ri-file-pdf-2-line me-1"></i>PDF
                                        </button>
                                        <button type="button" class="btn btn-outline-success flex-fill btn-pre-nomina-excel"
                                            data-tipo="coordinadores" data-empresa="joselito" data-formato="excel">
                                            <i class="ri-file-excel-2-line me-1"></i>Excel
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 col-xl-4">
                            <div class="card h-100 border-warning shadow-sm">
                                <div class="card-body d-flex flex-column">
                                    <span class="badge bg-warning-subtle text-warning align-self-start mb-3">Coordinadores</span>
                                    <h5 class="card-title">Negosur</h5>
                                    <p class="text-muted mb-3">Coordinadores asignados a Negosur.</p>
                                    <div class="small mb-3">
                                        <div><strong id="preNominaCoordNegosurCount">0</strong> registros</div>
                                        <div>RD$<strong id="preNominaCoordNegosurTotal">0</strong></div>
                                    </div>
                                    <div class="d-flex gap-2 mt-auto">
                                        <button type="button" class="btn btn-warning flex-fill btn-pre-nomina-pdf"
                                            data-tipo="coordinadores" data-empresa="negosur" data-formato="pdf">
                                            <i class="ri-file-pdf-2-line me-1"></i>PDF
                                        </button>
                                        <button type="button" class="btn btn-outline-success flex-fill btn-pre-nomina-excel"
                                            data-tipo="coordinadores" data-empresa="negosur" data-formato="excel">
                                            <i class="ri-file-excel-2-line me-1"></i>Excel
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 col-xl-4">
                            <div class="card h-100 border-secondary shadow-sm">
                                <div class="card-body d-flex flex-column">
                                    <span class="badge bg-secondary-subtle text-secondary align-self-start mb-3">Agentes de ventas</span>
                                    <h5 class="card-title">Con ID de empleado</h5>
                                    <p class="text-muted mb-3">Agentes pagables con su coordinador asignado para revisión de pre nómina.</p>
                                    <div class="small mb-3">
                                        <div><strong id="preNominaAgentesConIdCount">0</strong> registros</div>
                                    </div>
                                    <div class="d-flex gap-2 mt-auto">
                                        <button type="button" class="btn btn-secondary flex-fill btn-pre-nomina-pdf"
                                            data-tipo="agentes_con_id" data-empresa="" data-formato="pdf">
                                            <i class="ri-file-pdf-2-line me-1"></i>PDF
                                        </button>
                                        <button type="button" class="btn btn-outline-success flex-fill btn-pre-nomina-excel"
                                            data-tipo="agentes_con_id" data-empresa="" data-formato="excel">
                                            <i class="ri-file-excel-2-line me-1"></i>Excel
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 col-xl-4">
                            <div class="card h-100 border-danger shadow-sm">
                                <div class="card-body d-flex flex-column">
                                    <span class="badge bg-danger-subtle text-danger align-self-start mb-3">Revisión requerida</span>
                                    <h5 class="card-title">Usuarios sin ID de empleado</h5>
                                    <p class="text-muted mb-3">Usuarios del incentivo que no pueden incluirse en la nómina automática por no tener IdEmpleado.</p>
                                    <div class="small mb-3">
                                        <div><strong id="preNominaSinIdCount">0</strong> registros</div>
                                    </div>
                                    <div class="d-flex gap-2 mt-auto">
                                        <button type="button" class="btn btn-danger flex-fill btn-pre-nomina-pdf"
                                            data-tipo="sin_id" data-empresa="" data-formato="pdf">
                                            <i class="ri-file-pdf-2-line me-1"></i>PDF
                                        </button>
                                        <button type="button" class="btn btn-outline-success flex-fill btn-pre-nomina-excel"
                                            data-tipo="sin_id" data-empresa="" data-formato="excel">
                                            <i class="ri-file-excel-2-line me-1"></i>Excel
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cerrar</button>
                </div>
            </div>
        </div>
    </div>

    <div id="modalExcluirTerminales" class="modal fade" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Excluir agencias por terminal</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="terminales_excluir_file" class="form-label">Seleccione el archivo Excel</label>
                        <input type="file" class="form-control" id="terminales_excluir_file" accept=".xlsx,.xls,.csv">
                        <div class="form-text">Formatos aceptados: .xlsx, .xls, .csv. La plantilla solo necesita una columna: Terminal.</div>
                    </div>

                    <div class="mb-3">
                        <label for="terminales_excluir_manual" class="form-label">Terminales manuales</label>
                        <textarea id="terminales_excluir_manual" class="form-control" rows="3" placeholder="Escribe o pega terminales, una por linea o separadas por coma"></textarea>
                    </div>

                    <div class="d-flex flex-wrap gap-2 mb-3">
                        <button type="button" class="btn btn-outline-info btn-sm" id="btnReconocerTerminalesExcluidas">
                            <i class="ri-search-eye-line me-1"></i>Reconocer terminales
                        </button>
                        <a href="/incentivos/calculo-de-incentivos/terminales-excluidas/plantilla" class="btn btn-outline-primary btn-sm">
                            <i class="ri-download-line me-1"></i>Descargar plantilla
                        </a>
                        <button type="button" class="btn btn-outline-danger btn-sm ms-auto" id="btnLimpiarTerminalesExcluidas">
                            Limpiar exclusiones
                        </button>
                    </div>

                    <div class="border rounded p-2 mb-3" id="resultadoTerminalesExcluidas" style="display:none;"></div>

                    <div class="alert alert-warning mb-0">
                        <strong class="d-block mb-2">Reglas de exclusion:</strong>
                        <ul class="mb-2 ps-3">
                            <li>Solo se usara la columna Terminal del archivo.</li>
                            <li>Las terminales seleccionadas se excluyen del calculo de ventas e incentivo.</li>
                            <li>Tambien puedes escribir terminales manualmente y reconocerlas antes de aplicar.</li>
                        </ul>
                        <small class="text-muted">Luego de aplicar, genera el reporte para recalcular sin esas terminales.</small>
                    </div>
                </div>
                <div class="modal-footer d-flex gap-2">
                    <button type="button" class="btn btn-secondary flex-grow-1" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-primary flex-grow-1" id="btnAplicarTerminalesExcluidas" disabled>
                        Aplicar exclusion
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div id="modalConfigHorasTotal" class="modal fade" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Configurar Hora Total</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <label class="form-label" for="horas_total_minimo">Minimo de horas total</label>
                    <div class="input-group">
                        <input type="number" id="horas_total_minimo" class="form-control" value="0" min="0" step="0.01">
                        <span class="input-group-text">horas</span>
                    </div>
                    <small class="text-muted d-block mt-2">Las cedulas que lleguen o pasen este minimo se marcaran en verde; las demas en rojo.</small>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-primary" id="btnGuardarHorasTotal">Guardar</button>
                </div>
            </div>
        </div>
    </div>

    <div id="modalConfigPct" class="modal fade" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalConfigPctTitle">Configurar Tramos de Pago</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-info py-2 mb-3">Desde 1,000,001 se aplica porcentaje sobre ventas con tope maximo de 50,000.</div>
                    <div class="table-responsive">
                        <table class="table table-bordered table-sm align-middle mb-0">
                            <thead>
                                <tr>
                                    <th>Ventas Mensual Desde</th>
                                    <th>Hasta</th>
                                    <th>Valor</th>
                                </tr>
                            </thead>
                            <tbody id="tbodyTramosPago"></tbody>
                        </table>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-soft-secondary" id="btnRestaurarTramos">Restaurar por defecto</button>
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-primary" id="btnGuardarPct">Guardar</button>
                </div>
            </div>
        </div>
    </div>

    <div id="modalConfigAdminPct" class="modal fade" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Configurar Porcentaje</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <label class="form-label" for="admin_pct_bruto">% bruto (ejemplo: 9 = 9%)</label>
                    <input type="number" id="admin_pct_bruto" class="form-control" value="10" min="0" step="0.01" @if(!$canConfigAdminPct) disabled @endif>
                    @if(!$canConfigAdminPct)
                        <small class="text-muted d-block mt-2">Solo superadmin puede modificar este porcentaje.</small>
                    @endif
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-primary" id="btnGuardarAdminPct" @if(!$canConfigAdminPct) disabled @endif>Guardar</button>
                </div>
            </div>
        </div>
    </div>

    <div id="modalAdministrativos" class="modal fade" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Desglose Administrativo</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3 mb-3">
                        <div class="col-md-4">
                            <div class="border rounded p-2">
                                <small class="text-muted d-block">Bolsa administrativa total</small>
                                <strong id="admin_base_total">0</strong>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="border rounded p-2">
                                <small class="text-muted d-block" id="admin_distribuido_label">Monto por filtro activo</small>
                                <strong id="admin_distribuido_total">0</strong>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="border rounded p-2">
                                <small class="text-muted d-block">Total distribuido en tabla</small>
                                <strong id="admin_tabla_total">0</strong>
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="row g-2">
                                <div class="col-md-4">
                                    <div class="border rounded p-2 h-100">
                                        <small class="text-muted d-block">1 Gtes. y Encarg.</small>
                                        <div class="fw-semibold" id="admin_cat_g1">27.00% | 0</div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="border rounded p-2 h-100">
                                        <small class="text-muted d-block">2 Monitoreo</small>
                                        <div class="fw-semibold" id="admin_cat_g2">13.00% | 0</div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="border rounded p-2 h-100">
                                        <small class="text-muted d-block">4 Operadores + 5 Servs. Tecnicos + 6 Seguridad</small>
                                        <div class="fw-semibold" id="admin_cat_g45">60.00% | 0</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-12">
                            <label class="form-label mb-1 d-block">Filtrar grupo</label>
                            <div class="btn-group w-100" role="group">
                                <button type="button" class="btn btn-primary" id="btnAdminFiltroTodos">Todo</button>
                                <button type="button" class="btn btn-outline-primary" id="btnAdminFiltroG1">1 Gtes. y Encarg.</button>
                                <button type="button" class="btn btn-outline-primary" id="btnAdminFiltroG2">2 Monitoreo</button>
                                <button type="button" class="btn btn-outline-primary" id="btnAdminFiltroG45">4 Operadores + 5 Servs. Tecnicos + 6 Seguridad</button>
                            </div>
                        </div>
                    </div>
                    <div class="table-responsive" style="max-height: 520px;">
                        <table class="table table-bordered table-sm align-middle mb-0">
                            <thead class="table-light sticky-top">
                                <tr>
                                    <th style="min-width: 180px;">Grupo</th>
                                    <th style="min-width: 260px;">Nombre</th>
                                    <th style="min-width: 140px;">Empresa</th>
                                    <th style="min-width: 150px;" id="admin_valor_col_label">% Total / Monto fijo</th>
                                    <th style="min-width: 160px;" id="admin_monto_col_label">Monto</th>
                                    <th style="min-width: 110px;">Accion</th>
                                </tr>
                            </thead>
                            <tbody id="tbodyAdministrativos"></tbody>
                            <tfoot class="table-light">
                                <tr>
                                    <th colspan="4" class="text-end">Total</th>
                                    <th class="text-end" id="admin_col_total">0</th>
                                    <th></th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-success" id="btnExportAdministrativosExcel">Excel</button>
                    <button type="button" class="btn btn-danger" id="btnDesvinculadosAdministrativos">Desvinculados</button>
                    <button type="button" class="btn btn-primary" id="btnAgregarAdministrativoFila">Agregar fila</button>
                    <button type="button" class="btn btn-warning" id="btnGuardarAdministrativosPlantilla">Guardar cambios</button>
                    <button type="button" class="btn btn-soft-secondary" id="btnRestaurarAdministrativos">Restaurar plantilla</button>
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cerrar</button>
                </div>
            </div>
        </div>
    </div>

    <div id="modalConfigPuestoPct" class="modal fade" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Configurar % de puesto</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-sm align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th style="min-width: 260px;">Categoria</th>
                                    <th style="min-width: 160px;">% Manual</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>1 Gtes. y Encarg.</td>
                                    <td>
                                        <div class="input-group input-group-sm">
                                            <input type="number" id="puesto_pct_g1" class="form-control" value="27" min="0" step="0.01">
                                            <span class="input-group-text">%</span>
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <td>2 Monitoreo</td>
                                    <td>
                                        <div class="input-group input-group-sm">
                                            <input type="number" id="puesto_pct_g2" class="form-control" value="13" min="0" step="0.01">
                                            <span class="input-group-text">%</span>
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <td>4 Operadores + 5 Servs. Tecnicos + 6 Seguridad</td>
                                    <td>
                                        <div class="input-group input-group-sm">
                                            <input type="number" id="puesto_pct_g45" class="form-control" value="60" min="0" step="0.01">
                                            <span class="input-group-text">%</span>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-primary" id="btnGuardarPuestoPct">Guardar</button>
                </div>
            </div>
        </div>
    </div>

    <div id="modalCoordinadores" class="modal fade" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Desglose Coordinador</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <div class="border rounded p-2">
                                <small class="text-muted d-block">Monto administrativo base</small>
                                <strong id="coord_base_total">0</strong>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="border rounded p-2">
                                <small class="text-muted d-block">Total distribuido</small>
                                <strong id="coord_distribuido_total">0</strong>
                            </div>
                        </div>
                    </div>
                    <div class="table-responsive" style="max-height: 520px;">
                        <table class="table table-bordered table-sm align-middle mb-0">
                            <thead class="table-light sticky-top">
                                <tr>
                                    <th style="min-width: 280px;">Nombre</th>
                                    <th style="min-width: 100px;">Agencias</th>
                                    <th style="min-width: 100px;">Validas</th>
                                    <th style="min-width: 130px;">Monto en retención</th>
                                    <th style="min-width: 160px;">Monto</th>
                                    <th style="min-width: 120px;">Detalle</th>
                                    <th style="min-width: 120px;">% Total</th>
                                    <th style="min-width: 160px;">Monto Coordinador</th>
                                </tr>
                            </thead>
                            <tbody id="tbodyCoordinadores"></tbody>
                        </table>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-warning" id="btnAplicarCoordinadoresBolsa">Aplicar retención</button>
                    <button type="button" class="btn btn-danger" id="btnDesvinculadosCoordinadores">Desvinculados</button>
                    <button type="button" class="btn btn-info" id="btnInformeCoordinadoresPdf">Informe PDF</button>
                    <button type="button" class="btn btn-success" id="btnExportCoordinadoresExcel">Excel</button>
                    <button type="button" class="btn btn-soft-secondary" id="btnRestaurarCoordinadores">Restaurar plantilla</button>
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cerrar</button>
                </div>
            </div>
        </div>
    </div>

    <div id="modalCoordinadorDetalle" class="modal fade" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="coordinatorDetailTitle">Detalle de Usuarios</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="table-responsive" style="max-height: 420px;">
                        <table class="table table-bordered table-sm align-middle mb-0">
                            <thead class="table-light sticky-top">
                                <tr>
                                    <th style="min-width: 140px;">Cedula</th>
                                    <th style="min-width: 260px;">Usuario</th>
                                    <th style="min-width: 160px;">Incentivo</th>
                                </tr>
                            </thead>
                            <tbody id="tbodyCoordinadorDetalle"></tbody>
                        </table>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-success" id="btnExportCoordinadorDetalleExcel">
                        <i class="ri-file-excel-2-line me-1"></i>Excel
                    </button>
                    <button type="button" class="btn btn-danger" id="btnExportCoordinadorDetallePdf">
                        <i class="ri-file-pdf-2-line me-1"></i>PDF
                    </button>
                    <button type="button" class="btn btn-soft-secondary" id="btnBackToCoordinadores">Atras</button>
                </div>
            </div>
        </div>
    </div>

    <div id="modalFaltantesIncentivo" class="modal fade" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <div>
                        <h5 class="modal-title">Faltantes de usuarios generados</h5>
                        <small class="text-muted" id="faltantesIncentivoRango">Consulta basada en las cedulas del reporte actual.</small>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3 mb-3">
                        <div class="col-md-3">
                            <div class="border rounded p-3 h-100 bg-light">
                                <small class="text-muted d-block text-uppercase fw-semibold">Total monto faltantes</small>
                                <strong class="fs-4 text-danger" id="faltantesIncentivoTotalMonto">0</strong>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="border rounded p-3 h-100 bg-light">
                                <small class="text-muted d-block text-uppercase fw-semibold">Cantidad de faltantes</small>
                                <strong class="fs-4" id="faltantesIncentivoTotalCantidad">0</strong>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="border rounded p-3 h-100 bg-light">
                                <small class="text-muted d-block text-uppercase fw-semibold">Cedulas consultadas</small>
                                <strong class="fs-4" id="faltantesIncentivoTotalCedulas">0</strong>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="border rounded p-3 h-100 bg-light">
                                <small class="text-muted d-block text-uppercase fw-semibold">Monto incentivos con faltantes</small>
                                <strong class="fs-4 text-warning" id="faltantesIncentivoMontoIncentivos">0</strong>
                            </div>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table id="tableFaltantesIncentivo" class="table table-bordered table-striped align-middle" style="width:100%">
                            <thead>
                                <tr>
                                    <th>Cedula</th>
                                    <th>Nombre</th>
                                    <th class="text-center">Cantidad de faltantes</th>
                                    <th class="text-end">Monto</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-warning" id="btnAplicarFaltantesIncentivo">Aplicar</button>
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cerrar</button>
                </div>
            </div>
        </div>
    </div>

    <div id="modalDesvinculadosIncentivo" class="modal fade" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <div>
                        <h5 class="modal-title">Usuarios desvinculados en maestra</h5>
                        <small class="text-muted" id="desvinculadosIncentivoRango">Consulta basada en las cedulas e ids de empleado del reporte actual.</small>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3 mb-3">
                        <div class="col-md-3">
                            <div class="border rounded p-3 h-100 bg-light">
                                <small class="text-muted d-block text-uppercase fw-semibold">Total desvinculados</small>
                                <strong class="fs-4 text-danger" id="desvinculadosIncentivoTotalUsuarios">0</strong>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="border rounded p-3 h-100 bg-light">
                                <small class="text-muted d-block text-uppercase fw-semibold">Desactivados</small>
                                <strong class="fs-4" id="desvinculadosIncentivoTotalDesactivados">0</strong>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="border rounded p-3 h-100 bg-light">
                                <small class="text-muted d-block text-uppercase fw-semibold">Con fecha de salida</small>
                                <strong class="fs-4" id="desvinculadosIncentivoTotalFechaSalida">0</strong>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="border rounded p-3 h-100 bg-light">
                                <small class="text-muted d-block text-uppercase fw-semibold">Monto incentivos desvinculados</small>
                                <strong class="fs-4 text-warning" id="desvinculadosIncentivoMontoIncentivos">0</strong>
                            </div>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table id="tableDesvinculadosIncentivo" class="table table-bordered table-striped align-middle" style="width:100%">
                            <thead>
                                <tr>
                                    <th>Cedula</th>
                                    <th>IdEmpleado</th>
                                    <th>Nombre</th>
                                    <th>Estatus</th>
                                    <th>Fecha salida</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-warning" id="btnAplicarDesvinculadosIncentivo">Aplicar</button>
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cerrar</button>
                </div>
            </div>
        </div>
    </div>

    <div id="modalUsuariosActualizar" class="modal fade" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <div>
                        <h5 class="modal-title">Usuarios por actualizar en maestro de empleados</h5>
                        <small class="text-muted">Cedulas con ventas en el rango seleccionado que no tienen nombre asociado.</small>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="table-responsive">
                        <table id="tableUsuariosActualizar" class="table table-bordered table-striped align-middle" style="width:100%">
                            <thead>
                                <tr>
                                    <th>Cedula</th>
                                    <th>Nombre</th>
                                    <th>Empresa</th>
                                    <th>Nombre agencia</th>
                                    <th>Ultimo dia con venta</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-success" id="btnExportUsuariosActualizarExcel">Excel</button>
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cerrar</button>
                </div>
            </div>
        </div>
    </div>

    <div id="modalAgenciasSinEmpresa" class="modal fade" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <div>
                        <h5 class="modal-title">Agencias por asignar empresa</h5>
                        <small class="text-muted">Terminales con ventas en el rango seleccionado que no tienen empresa asignada en agencias.</small>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="table-responsive">
                        <table id="tableAgenciasSinEmpresa" class="table table-bordered table-striped align-middle" style="width:100%">
                            <thead>
                                <tr>
                                    <th>Terminal</th>
                                    <th>Nombre agencia</th>
                                    <th class="text-center">Usuarios</th>
                                    <th class="text-end">Ventas</th>
                                    <th>Ultimo dia con venta</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-success" id="btnExportAgenciasSinEmpresaExcel">Excel</button>
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cerrar</button>
                </div>
            </div>
        </div>
    </div>

    <div id="modalCalendarioTiposPago" class="modal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-fullscreen">
            <div class="modal-content">
                <div class="modal-header">
                    <div>
                        <h5 class="modal-title">Calendario diario de tipos de pago</h5>
                        <small class="text-muted">Cada cambio queda vigente desde ese día en adelante. “General” finaliza la asignación y usa el tipo seleccionado en el reporte.</small>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-2 align-items-end mb-3">
                        <div class="col-md-4">
                            <label class="form-label" for="calendarioBuscarTerminal">Buscar terminal o agencia</label>
                            <input type="search" class="form-control" id="calendarioBuscarTerminal" placeholder="Terminal, agencia, empresa...">
                        </div>
                        <div class="col-md-2">
                            <button type="button" class="btn btn-outline-primary w-100" id="btnCargarCalendarioPago">
                                <i class="ri-refresh-line me-1"></i>Cargar período
                            </button>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label" for="calendarioTipoMasivo">Tipo para selección</label>
                            <select class="form-select" id="calendarioTipoMasivo">
                                <option value="tramos_60">Pago 60</option>
                                <option value="tramos_70">Pago 70</option>
                                <option value="tramos_80">Pago 80</option>
                                <option value="">General</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <button type="button" class="btn btn-primary w-100" id="btnAplicarTipoMasivo">
                                Aplicar en masa
                            </button>
                        </div>
                        <div class="col-md-2">
                            <button type="button" class="btn btn-success w-100" id="btnGuardarCalendarioPago">
                                <i class="ri-save-line me-1"></i>Guardar cambios
                            </button>
                        </div>
                    </div>

                    <div class="card border-primary-subtle mb-3">
                        <div class="card-header bg-primary-subtle py-2">
                            <div class="fw-semibold">Carga masiva por terminales</div>
                            <small class="text-muted">Carga un archivo o pega las terminales para configurarlas sin buscarlas pagina por pagina.</small>
                        </div>
                        <div class="card-body">
                            <div class="row g-2 align-items-end">
                                <div class="col-lg-3">
                                    <label class="form-label" for="calendarioTerminalesArchivo">Archivo Excel o CSV</label>
                                    <input type="file" class="form-control" id="calendarioTerminalesArchivo" accept=".xlsx,.xls,.csv">
                                </div>
                                <div class="col-lg-4">
                                    <label class="form-label" for="calendarioTerminalesManual">Terminales manuales</label>
                                    <textarea class="form-control" id="calendarioTerminalesManual" rows="2" placeholder="Una por linea o separadas por coma"></textarea>
                                </div>
                                <div class="col-lg-2">
                                    <button type="button" class="btn btn-outline-info w-100" id="btnReconocerTerminalesCalendario">
                                        <i class="ri-search-eye-line me-1"></i>Reconocer
                                    </button>
                                </div>
                                <div class="col-lg-2">
                                    <a href="/incentivos/calculo-de-incentivos/terminales-excluidas/plantilla" class="btn btn-outline-secondary w-100">
                                        <i class="ri-download-line me-1"></i>Descargar plantilla
                                    </a>
                                </div>
                                <div class="col-lg-1">
                                    <button type="button" class="btn btn-outline-danger w-100 px-1" id="btnLimpiarTerminalesCalendario">
                                        <i class="ri-delete-bin-line me-1"></i>Limpiar
                                    </button>
                                </div>
                            </div>

                            <div class="mt-3 d-none" id="calendarioTerminalesResultado">
                                <div class="alert alert-light border py-2 mb-2" id="calendarioTerminalesResumen"></div>
                                <div class="table-responsive border rounded" style="max-height: 240px;">
                                    <table class="table table-sm table-hover align-middle mb-0">
                                        <thead class="table-light sticky-top">
                                            <tr>
                                                <th style="width: 42px;"><input type="checkbox" class="form-check-input" id="calendarioSeleccionarTerminalesReconocidas" checked></th>
                                                <th>Terminal</th>
                                                <th>Agencia</th>
                                                <th>Empresa</th>
                                            </tr>
                                        </thead>
                                        <tbody id="calendarioTerminalesReconocidasBody"></tbody>
                                    </table>
                                </div>
                                <div class="row g-2 align-items-end mt-1">
                                    <div class="col-md-3">
                                        <label class="form-label" for="calendarioAlcanceTerminales">Aplicar en</label>
                                        <select class="form-select" id="calendarioAlcanceTerminales">
                                            <option value="periodo">Todos los dias del periodo</option>
                                            <option value="desde">Desde una fecha hasta el final</option>
                                            <option value="rango">Rango de fechas</option>
                                            <option value="seleccionados">Solo los dias marcados arriba</option>
                                        </select>
                                    </div>
                                    <div class="col-md-3 d-none" id="calendarioFechaInicioContainer">
                                        <label class="form-label" for="calendarioFechaInicioMasiva">Fecha inicial</label>
                                        <input type="date" class="form-control" id="calendarioFechaInicioMasiva">
                                    </div>
                                    <div class="col-md-3 d-none" id="calendarioFechaFinContainer">
                                        <label class="form-label" for="calendarioFechaFinMasiva">Fecha final</label>
                                        <input type="date" class="form-control" id="calendarioFechaFinMasiva">
                                    </div>
                                    <div class="col-md-3 ms-auto">
                                        <button type="button" class="btn btn-primary w-100" id="btnAplicarTerminalesReconocidas">
                                            Aplicar a terminales cargadas
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex flex-wrap gap-3 mb-2 small">
                        <span><span class="badge bg-success-subtle text-success">60</span> Pago 60</span>
                        <span><span class="badge bg-info-subtle text-info">70</span> Pago 70</span>
                        <span><span class="badge bg-warning-subtle text-warning">80</span> Pago 80</span>
                        <span><span class="badge bg-light text-muted">General</span> Tipo general del reporte</span>
                        <span class="ms-auto fw-semibold" id="calendarioPagoResumen"></span>
                    </div>

                    <div class="calendar-payment-grid border rounded">
                        <table class="table table-bordered table-sm align-middle mb-0" id="tablaCalendarioTiposPago">
                            <thead></thead>
                            <tbody></tbody>
                        </table>
                    </div>
                    <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mt-2" id="calendarioPaginacion">
                        <div class="d-flex align-items-center gap-2 small text-muted">
                            <label for="calendarioPorPagina" class="mb-0">Mostrar</label>
                            <select class="form-select form-select-sm" id="calendarioPorPagina" style="width: 82px">
                                <option value="25">25</option>
                                <option value="50" selected>50</option>
                                <option value="100">100</option>
                            </select>
                            <span>agencias por pagina</span>
                        </div>
                        <div class="d-flex align-items-center gap-2">
                            <button type="button" class="btn btn-sm btn-outline-secondary" id="calendarioPaginaAnterior">
                                <i class="ri-arrow-left-s-line"></i> Anterior
                            </button>
                            <span class="small fw-semibold" id="calendarioPaginaEstado">Pagina 1 de 1</span>
                            <button type="button" class="btn btn-sm btn-outline-secondary" id="calendarioPaginaSiguiente">
                                Siguiente <i class="ri-arrow-right-s-line"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection

@section('script')
<script>
    const XML_DECL = '<' + '?xml version="1.0" encoding="UTF-8" standalone="yes"?' + '>';
    const CAN_CONFIG_ADMIN_PCT = @json($canConfigAdminPct);
    const CALENDARIO_V6_URL = @json(route('incentivos.calculo.calendario'));
    const CALENDARIO_V6_GUARDAR_URL = @json(route('incentivos.calculo.calendario.guardar'));
    const CALENDARIO_V6_RECONOCER_TERMINALES_URL = @json(route('incentivos.calculo.calendario.terminales.reconocer'));

    function buildRanges(percent, pagos) {
        return [
            { desde: 100001, hasta: 250000, pago: pagos[0], tipo: 'fijo' },
            { desde: 250001, hasta: 400000, pago: pagos[1], tipo: 'fijo' },
            { desde: 400001, hasta: 550000, pago: pagos[2], tipo: 'fijo' },
            { desde: 550001, hasta: 700000, pago: pagos[3], tipo: 'fijo' },
            { desde: 700001, hasta: 850000, pago: pagos[4], tipo: 'fijo' },
            { desde: 850001, hasta: 1000000, pago: pagos[5], tipo: 'fijo' },
            { desde: 1000001, hasta: 5000000, pago: percent, tipo: 'porcentaje' },
            { desde: 5000001, hasta: null, pago: percent, tipo: 'porcentaje' },
        ];
    }

    function getDefaultRanges() {
        return {
            tramos_60: buildRanges(1, [1000, 2000, 4000, 6000, 8000, 9000]),
            tramos_70: buildRanges(0.75, [750, 1500, 3000, 4500, 6000, 6750]),
            tramos_80: buildRanges(0.5, [500, 1000, 2000, 3000, 4000, 4500]),
        };
    }

    function normalizeAdministrativeRowsFromPayload(rows) {
        return (Array.isArray(rows) ? rows : []).map(function (row) {
            const grupo = normalizeAdministrativeGroup(row?.grupo);

            return {
                id: row?.id ?? null,
                grupo,
                nombre: String(row?.nombre ?? '').trim(),
                cedula: String(row?.cedula ?? '').trim(),
                empleadoid: String(row?.empleadoid ?? '').trim(),
                viapago: String(row?.viapago ?? '').trim(),
                ciudad: String(row?.ciudad ?? '').trim(),
                empresa: String(row?.empresa ?? '').trim(),
                pct: isFixedAdministrativeGroup(grupo)
                    ? Math.max(0, toNumber(row?.pct))
                    : Math.max(0, toNumber(row?.pct) / 100),
            };
        });
    }

    let administrativeRowsConfig = @json($administrativosConfig ?? []);

        function getAdministrativeRowsFromConfig() {
        return normalizeAdministrativeRowsFromPayload(administrativeRowsConfig);
    }

    function splitAdministrativeRowsByGroup(rows) {
        const allRows = Array.isArray(rows) ? rows : [];

        return {
            administrativos: allRows.filter((row) => !isFixedAdministrativeGroup(row.grupo)),
            operadores: allRows.filter((row) => isFixedAdministrativeGroup(row.grupo)),
        };
    }

    function getDefaultAdministrativeRows() {
        return splitAdministrativeRowsByGroup(getAdministrativeRowsFromConfig()).administrativos;
    }

    function getDefaultOperatorRows() {
        return splitAdministrativeRowsByGroup(getAdministrativeRowsFromConfig()).operadores;
    }

    function getDefaultCoordinatorRows() {
        return @json($coordinadores ?? []);
    }
    let payoutRangesByType = getDefaultRanges();
    let administrativeRows = getDefaultAdministrativeRows();
    let operatorRows = getDefaultOperatorRows();
    let coordinatorRows = getDefaultCoordinatorRows();
    let cachedRows = [];
    let currentFilteredRows = [];
    let cachedMeta = {};
    let cachedSistema = null;
    let cachedTipoPago = null;
    let cachedModoCalculo = null;
    let cachedAlcanceProductos = null;
    let cachedFechaInicio = null;
    let cachedFechaFin = null;
    let cachedReportSignature = null;
    let calendarPaymentDates = [];
    let calendarPaymentRows = [];
    let calendarRecognizedTerminals = [];
    let calendarPaymentPagination = {
        pagina_actual: 1,
        ultima_pagina: 1,
        por_pagina: 50,
        total: 0,
        desde: 0,
        hasta: 0,
    };
    const calendarDirtyAssignments = new Map();
    let tableFaltantesIncentivo = null;
    let tableDesvinculadosIncentivo = null;
    let tableUsuariosActualizar = null;
    let tableAgenciasSinEmpresa = null;
    let lastFaltantesCedulas = new Set();
    let excludedFaltantesCedulas = new Set();
    let lastDesvinculadosCedulas = new Set();
    let lastDesvinculadosEmpleadoIds = new Set();
    let excludedDesvinculadosCedulas = new Set();
    let excludedDesvinculadosEmpleadoIds = new Set();
    let faltantesPeriodoConsultados = false;
    let faltantesPeriodoAplicados = false;
    let desvinculadosPeriodoConsultados = false;
    let desvinculadosPeriodoAplicados = false;
    let excludedAdministrativeDesvinculadosCedulas = new Set();
    let excludedAdministrativeDesvinculadosEmpleadoIds = new Set();
    let excludedCoordinatorDesvinculadosCedulas = new Set();
    let excludedCoordinatorDesvinculadosEmpleadoIds = new Set();
    let excludedCoordinatorIds = new Set();
    let appliedCoordinatorAmounts = {};
    let adminPctBruto = 10;
    const ADMINISTRATIVE_SHARE = 0.40;
    let administrativeGroupFilter = 'todos';
    const ADMIN_GROUP_OPTIONS = [
        '1. Gtes. Y Encarg.',
        '2. Monitoreo',
        '4. Operadores',
        '5. Servs. Tecnicos',
        '6. Seguridad',
    ];
    const ADMIN_EMPRESA_OPTIONS = [
        'Consorcio Joselito',
        'Negosur',
    ];
    let puestoPctConfig = {
        g1: 27,
        g2: 13,
        g45: 60,
    };
    let currentDistributionBase = 0;
    let currentAdministrativePoolBase = 0;
    let currentAdministrativePoolByEmpresa = {};
    let currentAdministrativeBase = 0;
    let currentOperatorBase = 0;
    let currentCoordinatorBase = 0;
    let currentCoordinatorDetailRow = null;
    let currentFixedBagTopUp = 0;
    let horasTotalMinimo = toNumber(localStorage.getItem('incentivos_administrativos_horas_total_minimo') || 0);
    let excludedTerminales = new Set(@json($terminalesExcluidasIncentivo ?? []));
    let recognizedTerminalesExcluidas = [];
    let currentExcludedApplication = {
        faltantesDisponible: 0,
        desvinculadosDisponible: 0,
        coordinadoresDisponible: 0,
        aplicadoFaltantes: 0,
        aplicadoDesvinculados: 0,
        aplicadoCoordinadores: 0,
        rebajadoFaltantes: 0,
        rebajadoDesvinculados: 0,
        rebajadoCoordinadores: 0,
        totalAplicado: 0,
        totalRebajado: 0,
    };
    let coordinatorUserDetailsByCoordinator = {};
    const META_MINIMA_VENTA = 100001;

    function toNumber(value) {
        if (value === null || value === undefined) return 0;
        return parseFloat(String(value).replace(/,/g, '')) || 0;
    }

    function toIntegerAmount(value) {
        return Math.round(toNumber(value));
    }

    function calendarPaymentKey(sistema, terminal, fecha) {
        return `${String(sistema).trim().toLowerCase()}|${String(terminal).trim()}|${fecha}`;
    }

    function updateCalendarPaymentCell(select) {
        const cell = select.closest('.calendar-payment-cell');
        if (!cell) {
            return;
        }

        cell.classList.remove('payment-60', 'payment-70', 'payment-80', 'payment-default');
        cell.classList.add({
            tramos_60: 'payment-60',
            tramos_70: 'payment-70',
            tramos_80: 'payment-80',
        }[select.value] || 'payment-default');
    }

    function updateCalendarPaymentSummary() {
        const summary = document.getElementById('calendarioPagoResumen');
        if (!summary) {
            return;
        }

        const total = Number(calendarPaymentPagination.total || calendarPaymentRows.length);
        const from = Number(calendarPaymentPagination.desde || 0);
        const to = Number(calendarPaymentPagination.hasta || 0);
        summary.textContent = `${from.toLocaleString('en-US')}-${to.toLocaleString('en-US')} de ${total.toLocaleString('en-US')} terminales | ${calendarDirtyAssignments.size.toLocaleString('en-US')} cambios pendientes`;
    }

    function markCalendarPaymentDirty(select) {
        const assignment = {
            sistema: select.dataset.sistema,
            terminal: select.dataset.terminal,
            fecha: select.dataset.fecha,
            tipo_pago: select.value || null,
        };

        calendarDirtyAssignments.set(
            calendarPaymentKey(assignment.sistema, assignment.terminal, assignment.fecha),
            assignment
        );
        updateCalendarPaymentCell(select);
        updateCalendarPaymentSummary();
    }

    function createCalendarPaymentOption(value, label, selectedValue) {
        const option = document.createElement('option');
        option.value = value;
        option.textContent = label;
        option.selected = value === selectedValue;

        return option;
    }

    function getCalendarPaymentValue(row, fecha) {
        const dirtyAssignment = calendarDirtyAssignments.get(
            calendarPaymentKey(row.sistema, row.terminal, fecha)
        );

        if (dirtyAssignment) {
            return dirtyAssignment.tipo_pago || '';
        }

        return row.tipos_por_fecha?.[fecha] || '';
    }

    function updateCalendarPaymentPagination() {
        const currentPage = Number(calendarPaymentPagination.pagina_actual || 1);
        const lastPage = Number(calendarPaymentPagination.ultima_pagina || 1);
        const state = document.getElementById('calendarioPaginaEstado');
        const previous = document.getElementById('calendarioPaginaAnterior');
        const next = document.getElementById('calendarioPaginaSiguiente');

        if (state) {
            state.textContent = `Pagina ${currentPage.toLocaleString('en-US')} de ${lastPage.toLocaleString('en-US')}`;
        }
        if (previous) {
            previous.disabled = currentPage <= 1;
        }
        if (next) {
            next.disabled = currentPage >= lastPage;
        }
    }

    function renderCalendarPaymentGrid() {
        const table = document.getElementById('tablaCalendarioTiposPago');
        const thead = table.querySelector('thead');
        const tbody = table.querySelector('tbody');
        const headerRow = document.createElement('tr');
        const terminalHeader = document.createElement('th');
        terminalHeader.className = 'calendar-terminal-column';
        terminalHeader.innerHTML = `
            <div class="d-flex align-items-center justify-content-between gap-2">
                <label class="mb-0"><input type="checkbox" class="form-check-input me-1" id="calendarSelectAllRows"> Terminales</label>
                <label class="mb-0 small"><input type="checkbox" class="form-check-input me-1" id="calendarSelectAllDates"> Dias</label>
            </div>`;
        headerRow.appendChild(terminalHeader);

        calendarPaymentDates.forEach((fecha) => {
            const th = document.createElement('th');
            const date = new Date(`${fecha}T00:00:00`);
            th.className = 'text-center';
            th.innerHTML = `
                <label class="mb-0 d-block">
                    <input type="checkbox" class="form-check-input calendar-date-check" value="${fecha}">
                    <span class="d-block mt-1">${date.toLocaleDateString('es-DO', { weekday: 'short', day: '2-digit', month: '2-digit' })}</span>
                </label>`;
            headerRow.appendChild(th);
        });
        thead.replaceChildren(headerRow);
        const bodyFragment = document.createDocumentFragment();

        calendarPaymentRows.forEach((row) => {
            const tr = document.createElement('tr');
            const terminalCell = document.createElement('td');
            terminalCell.className = 'calendar-terminal-column';
            terminalCell.innerHTML = `
                <div class="d-flex align-items-start gap-2">
                    <input type="checkbox" class="form-check-input calendar-row-check mt-1">
                    <div>
                        <div class="fw-bold">
                            ${escapeHtml(row.terminal)}
                            <span class="badge bg-light text-dark">${escapeHtml(row.sistema)}</span>
                            ${row.tiene_configuracion ? '<span class="badge bg-primary-subtle text-primary">Configurado</span>' : ''}
                        </div>
                        <div class="small">${escapeHtml(row.agencia)}</div>
                        <div class="small text-muted">${escapeHtml(row.empresa)} | Ventas: ${formatMoney(row.ventas)}</div>
                    </div>
                </div>`;
            tr.appendChild(terminalCell);

            calendarPaymentDates.forEach((fecha) => {
                const td = document.createElement('td');
                td.className = 'calendar-payment-cell p-1';
                const select = document.createElement('select');
                const selectedValue = getCalendarPaymentValue(row, fecha);
                select.className = 'form-select form-select-sm calendar-payment-select';
                select.dataset.sistema = row.sistema;
                select.dataset.terminal = row.terminal;
                select.dataset.fecha = fecha;
                select.appendChild(createCalendarPaymentOption('', 'General', selectedValue));
                select.appendChild(createCalendarPaymentOption('tramos_60', '60', selectedValue));
                select.appendChild(createCalendarPaymentOption('tramos_70', '70', selectedValue));
                select.appendChild(createCalendarPaymentOption('tramos_80', '80', selectedValue));
                td.appendChild(select);
                tr.appendChild(td);
                updateCalendarPaymentCell(select);
            });

            bodyFragment.appendChild(tr);
        });
        tbody.replaceChildren(bodyFragment);

        if (table.dataset.calendarEventsBound !== '1') {
            table.addEventListener('change', function(event) {
                const target = event.target;

                if (target.matches('.calendar-payment-select')) {
                    markCalendarPaymentDirty(target);
                } else if (target.id === 'calendarSelectAllRows') {
                    table.querySelectorAll('.calendar-row-check').forEach((checkbox) => {
                        checkbox.checked = target.checked;
                    });
                } else if (target.id === 'calendarSelectAllDates') {
                    table.querySelectorAll('.calendar-date-check').forEach((checkbox) => {
                        checkbox.checked = target.checked;
                    });
                }
            });
            table.dataset.calendarEventsBound = '1';
        }

        updateCalendarPaymentSummary();
        updateCalendarPaymentPagination();
    }

    function loadCalendarPaymentGrid(page = 1, preserveDirtyAssignments = false, showBlockingLoader = true) {
        const fechaInicio = document.getElementById('ni_fecha_ini').value;
        const fechaFin = document.getElementById('ni_fecha_fin').value;
        const sistema = document.getElementById('ni_sistema').value;
        const buscar = document.getElementById('calendarioBuscarTerminal').value.trim();
        const perPage = Number(document.getElementById('calendarioPorPagina')?.value || 50);

        if (!fechaInicio || !fechaFin) {
            Swal.fire({ title: 'Informacion', text: 'Selecciona las fechas del reporte antes de abrir el calendario.', icon: 'warning' });
            return;
        }

        if (showBlockingLoader) {
            Swal.fire({
                title: 'Cargando calendario...',
                allowOutsideClick: false,
                showConfirmButton: false,
                didOpen: () => Swal.showLoading(),
            });
        } else {
            const summary = document.getElementById('calendarioPagoResumen');
            if (summary) {
                summary.textContent = 'Cargando pagina...';
            }
        }
        const params = new URLSearchParams({
            fecha_ini: fechaInicio,
            fecha_fin: fechaFin,
            sistema,
            buscar,
            page: String(page),
            per_page: String(perPage),
        });

        fetch(`${CALENDARIO_V6_URL}?${params.toString()}`, {
            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
        })
            .then(response => parseResponseAsJson(response, 'No se pudo cargar el calendario de pagos'))
            .then((response) => {
                calendarPaymentDates = Array.isArray(response.fechas) ? response.fechas : [];
                calendarPaymentRows = Array.isArray(response.terminales) ? response.terminales : [];
                calendarPaymentPagination = response.paginacion || calendarPaymentPagination;
                syncCalendarBulkDateBounds();
                if (!preserveDirtyAssignments) {
                    calendarDirtyAssignments.clear();
                }
                renderCalendarPaymentGrid();
                if (showBlockingLoader) {
                    Swal.close();
                }
            })
            .catch((error) => Swal.fire({ title: 'Error', text: error.message || String(error), icon: 'error' }));
    }

    function applyBulkCalendarPaymentType() {
        const selectedRows = [...document.querySelectorAll('.calendar-row-check:checked')]
            .map(checkbox => checkbox.closest('tr'));
        const selectedDates = new Set(
            [...document.querySelectorAll('.calendar-date-check:checked')].map(checkbox => checkbox.value)
        );
        const tipoPago = document.getElementById('calendarioTipoMasivo').value;

        if (!selectedRows.length || !selectedDates.size) {
            Swal.fire({ title: 'Seleccion incompleta', text: 'Selecciona al menos una terminal y un dia.', icon: 'warning' });
            return;
        }

        selectedRows.forEach((row) => {
            row.querySelectorAll('.calendar-payment-select').forEach((select) => {
                if (selectedDates.has(select.dataset.fecha)) {
                    select.value = tipoPago;
                    markCalendarPaymentDirty(select);
                }
            });
        });
    }

    function renderRecognizedCalendarTerminals(response) {
        calendarRecognizedTerminals = Array.isArray(response.terminales) ? response.terminales : [];
        const result = document.getElementById('calendarioTerminalesResultado');
        const summary = document.getElementById('calendarioTerminalesResumen');
        const tbody = document.getElementById('calendarioTerminalesReconocidasBody');
        const missing = Array.isArray(response.terminales_no_encontradas) ? response.terminales_no_encontradas : [];
        const found = Number(response.encontradas || 0);

        summary.innerHTML = `
            <strong>${found.toLocaleString('en-US')} terminales encontradas</strong>
            de ${Number(response.terminales_unicas || 0).toLocaleString('en-US')} unicas.
            ${missing.length ? `<span class="text-danger ms-2">No encontradas: ${escapeHtml(missing.join(', '))}</span>` : '<span class="text-success ms-2">Todas fueron reconocidas.</span>'}
        `;
        tbody.innerHTML = calendarRecognizedTerminals.map((terminal, index) => `
            <tr>
                <td><input type="checkbox" class="form-check-input calendario-terminal-reconocida" value="${index}" checked></td>
                <td class="fw-semibold">${escapeHtml(terminal.terminal)}</td>
                <td>${escapeHtml(terminal.agencia)}</td>
                <td>${escapeHtml(terminal.empresa)}</td>
            </tr>
        `).join('');
        document.getElementById('calendarioSeleccionarTerminalesReconocidas').checked = true;
        result.classList.remove('d-none');
    }

    function recognizeCalendarTerminals() {
        const fileInput = document.getElementById('calendarioTerminalesArchivo');
        const manualInput = document.getElementById('calendarioTerminalesManual');
        const hasFile = fileInput.files && fileInput.files.length > 0;
        const manualText = manualInput.value.trim();

        if (!hasFile && !manualText) {
            Swal.fire({ title: 'Datos requeridos', text: 'Selecciona un archivo o escribe al menos una terminal.', icon: 'warning' });
            return;
        }

        const formData = new FormData();
        if (hasFile) {
            formData.append('file', fileInput.files[0]);
        }
        formData.append('terminales_manual', manualText);
        formData.append('sistema', document.getElementById('ni_sistema').value || 'Todos');

        Swal.fire({
            title: 'Reconociendo terminales...',
            allowOutsideClick: false,
            showConfirmButton: false,
            didOpen: () => Swal.showLoading(),
        });

        fetch(CALENDARIO_V6_RECONOCER_TERMINALES_URL, {
            method: 'POST',
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': csrfToken(),
            },
            body: formData,
        })
            .then(response => parseResponseAsJson(response, 'No se pudieron reconocer las terminales'))
            .then((response) => {
                Swal.close();
                renderRecognizedCalendarTerminals(response);
            })
            .catch((error) => Swal.fire({ title: 'Error', text: error.message || String(error), icon: 'error' }));
    }

    function clearRecognizedCalendarTerminalScenario() {
        document.getElementById('calendarioTerminalesArchivo').value = '';
        document.getElementById('calendarioTerminalesManual').value = '';
        document.getElementById('calendarioTerminalesResumen').innerHTML = '';
        document.getElementById('calendarioTerminalesReconocidasBody').innerHTML = '';
        document.getElementById('calendarioTerminalesResultado').classList.add('d-none');
        document.getElementById('calendarioSeleccionarTerminalesReconocidas').checked = true;
        calendarRecognizedTerminals = [];
    }

    function updateCalendarBulkDateControls() {
        const scope = document.getElementById('calendarioAlcanceTerminales').value;
        document.getElementById('calendarioFechaInicioContainer').classList.toggle(
            'd-none',
            !['desde', 'rango'].includes(scope)
        );
        document.getElementById('calendarioFechaFinContainer').classList.toggle('d-none', scope !== 'rango');
    }

    function syncCalendarBulkDateBounds() {
        const firstDate = calendarPaymentDates[0] || '';
        const lastDate = calendarPaymentDates[calendarPaymentDates.length - 1] || '';
        const startInput = document.getElementById('calendarioFechaInicioMasiva');
        const endInput = document.getElementById('calendarioFechaFinMasiva');

        [startInput, endInput].forEach((input) => {
            input.min = firstDate;
            input.max = lastDate;
        });
        if (!startInput.value || startInput.value < firstDate || startInput.value > lastDate) {
            startInput.value = firstDate;
        }
        if (!endInput.value || endInput.value < firstDate || endInput.value > lastDate) {
            endInput.value = lastDate;
        }
        updateCalendarBulkDateControls();
    }

    function getRecognizedCalendarDates() {
        const scope = document.getElementById('calendarioAlcanceTerminales').value;
        if (scope === 'seleccionados') {
            return [...document.querySelectorAll('.calendar-date-check:checked')].map(checkbox => checkbox.value);
        }
        if (scope === 'periodo') {
            return [...calendarPaymentDates];
        }

        const startDate = document.getElementById('calendarioFechaInicioMasiva').value;
        const endDate = scope === 'rango'
            ? document.getElementById('calendarioFechaFinMasiva').value
            : calendarPaymentDates[calendarPaymentDates.length - 1];

        if (!startDate || !endDate) {
            Swal.fire({ title: 'Fechas requeridas', text: 'Selecciona las fechas que deseas aplicar.', icon: 'warning' });
            return null;
        }
        if (startDate > endDate) {
            Swal.fire({ title: 'Rango invalido', text: 'La fecha inicial no puede ser posterior a la fecha final.', icon: 'warning' });
            return null;
        }

        return calendarPaymentDates.filter(date => date >= startDate && date <= endDate);
    }

    function applyRecognizedCalendarTerminals() {
        const selectedTerminals = [...document.querySelectorAll('.calendario-terminal-reconocida:checked')]
            .map(checkbox => calendarRecognizedTerminals[Number(checkbox.value)])
            .filter(Boolean);
        const dates = getRecognizedCalendarDates();

        if (!selectedTerminals.length) {
            Swal.fire({ title: 'Seleccion requerida', text: 'Selecciona al menos una terminal reconocida.', icon: 'warning' });
            return;
        }
        if (dates === null) {
            return;
        }
        if (!dates.length) {
            Swal.fire({ title: 'Seleccion requerida', text: 'Marca al menos un dia del calendario.', icon: 'warning' });
            return;
        }

        const type = document.getElementById('calendarioTipoMasivo').value;
        const projectedKeys = new Set(calendarDirtyAssignments.keys());
        selectedTerminals.forEach((terminal) => {
            const systems = Array.isArray(terminal.sistemas) && terminal.sistemas.length
                ? terminal.sistemas
                : [terminal.sistema].filter(Boolean);
            systems.forEach((system) => {
                dates.forEach((date) => projectedKeys.add(calendarPaymentKey(system, terminal.terminal, date)));
            });
        });

        if (projectedKeys.size > 10000) {
            Swal.fire({ title: 'Demasiadas configuraciones', text: 'El guardado permite hasta 10,000 combinaciones de terminal y dia. Reduce el periodo o divide la carga.', icon: 'warning' });
            return;
        }

        selectedTerminals.forEach((terminal) => {
            const systems = Array.isArray(terminal.sistemas) && terminal.sistemas.length
                ? terminal.sistemas
                : [terminal.sistema].filter(Boolean);
            systems.forEach((system) => {
                dates.forEach((date) => {
                    const assignment = {
                        sistema: system,
                        terminal: terminal.terminal,
                        fecha: date,
                        tipo_pago: type || null,
                    };
                    calendarDirtyAssignments.set(
                        calendarPaymentKey(assignment.sistema, assignment.terminal, assignment.fecha),
                        assignment
                    );
                });
            });
        });

        document.querySelectorAll('.calendar-payment-select').forEach((select) => {
            const assignment = calendarDirtyAssignments.get(
                calendarPaymentKey(select.dataset.sistema, select.dataset.terminal, select.dataset.fecha)
            );
            if (assignment) {
                select.value = assignment.tipo_pago || '';
                updateCalendarPaymentCell(select);
            }
        });
        updateCalendarPaymentSummary();

        const typeLabel = type ? type.replace('tramos_', 'Pago ') : 'General';
        Swal.fire({
            title: 'Cambios preparados',
            text: `${selectedTerminals.length.toLocaleString('en-US')} terminales por ${dates.length.toLocaleString('en-US')} dias quedaron en ${typeLabel}. Presiona Guardar cambios para confirmar.`,
            icon: 'success',
        });
    }

    function saveCalendarPaymentChanges() {
        const assignments = [...calendarDirtyAssignments.values()];
        if (!assignments.length) {
            Swal.fire({ title: 'Informacion', text: 'No hay cambios pendientes para guardar.', icon: 'info' });
            return;
        }

        Swal.fire({
            title: 'Guardando calendario...',
            allowOutsideClick: false,
            showConfirmButton: false,
            didOpen: () => Swal.showLoading(),
        });

        fetch(CALENDARIO_V6_GUARDAR_URL, {
            method: 'PUT',
            headers: {
                'Accept': 'application/json',
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': csrfToken(),
            },
            body: JSON.stringify({ asignaciones: assignments }),
        })
            .then(response => parseResponseAsJson(response, 'No se pudo guardar el calendario de pagos'))
            .then((response) => {
                calendarDirtyAssignments.clear();
                loadCalendarPaymentGrid(calendarPaymentPagination.pagina_actual || 1, false, false);
                Swal.fire({ title: 'Calendario actualizado', text: response.message, icon: 'success' });
            })
            .catch((error) => Swal.fire({ title: 'Error', text: error.message || String(error), icon: 'error' }));
    }

    async function parseResponseAsJson(response, contexto) {
        const contentType = String(response.headers.get('content-type') || '').toLowerCase();
        const bodyText = await response.text();
        let payload = null;

        if (bodyText !== '') {
            try {
                payload = JSON.parse(bodyText);
            } catch (_parseError) {
                payload = null;
            }
        }

        if (!response.ok) {
            const serverMessage = payload?.message || payload?.error || '';
            const nonJsonHint = !contentType.includes('application/json')
                ? 'El servidor devolvio una respuesta no JSON.'
                : '';
            const detail = [serverMessage, nonJsonHint].filter(Boolean).join(' | ');
            throw new Error(`${contexto} (HTTP ${response.status})${detail ? ': ' + detail : ''}`);
        }

        if (!payload) {
            throw new Error(`${contexto}: Respuesta vacia o JSON invalido.`);
        }

        return payload;
    }

    function csrfToken() {
        return document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '{{ csrf_token() }}';
    }

    function formatPercentDisplay(value) {
        const number = parseFloat(value);
        if (Number.isNaN(number)) return '0';
        return Number.isInteger(number) ? String(number) : String(number);
    }

    function formatMoney(value) {
        return toIntegerAmount(value).toLocaleString('en-US', { minimumFractionDigits: 0, maximumFractionDigits: 0 });
    }

    function formatHours(value) {
        return toNumber(value).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }

    function renderHorasTotal(value) {
        const horas = toNumber(value);
        const cumple = horas >= horasTotalMinimo;
        const className = cumple ? 'hora-total-cumple' : 'hora-total-no-cumple';
        const title = cumple
            ? `Cumple minimo de ${formatHours(horasTotalMinimo)} horas`
            : `No cumple minimo de ${formatHours(horasTotalMinimo)} horas`;

        return `<span class="hora-total-badge ${className}" title="${escapeHtml(title)}">${formatHours(horas)}</span>`;
    }

    function normalizeIntegerReportRows(rows) {
        return (Array.isArray(rows) ? rows : []).map((row) => ({
            ...row,
            ventas_ultimo_mes: toIntegerAmount(row?.ventas_ultimo_mes),
            ventas_mes_actual: toIntegerAmount(row?.ventas_mes_actual),
            pago_escala: toIntegerAmount(row?.pago_escala),
            nuevo_incentivo: toIntegerAmount(row?.nuevo_incentivo),
        }));
    }

    function calculateAdministrativeDistribution(totalIncentivo) {
        const base = toIntegerAmount(totalIncentivo);
        const totalAdministrativoCoordinador = toIntegerAmount(base * (adminPctBruto / 100));
        const administrativo = toIntegerAmount(totalAdministrativoCoordinador * ADMINISTRATIVE_SHARE);

        return {
            totalAdministrativoCoordinador,
            administrativo,
            coordinador: totalAdministrativoCoordinador - administrativo,
        };
    }

    function getEmptyExcludedApplication() {
        return {
            faltantesDisponible: 0,
            desvinculadosDisponible: 0,
            coordinadoresDisponible: 0,
            aplicadoFaltantes: 0,
            aplicadoDesvinculados: 0,
            aplicadoCoordinadores: 0,
            rebajadoFaltantes: 0,
            rebajadoDesvinculados: 0,
            rebajadoCoordinadores: 0,
            totalAplicado: 0,
            totalRebajado: 0,
        };
    }

    function getCedulaKey(value) {
        const digits = String(value ?? '').replace(/\D+/g, '');
        const normalized = digits.replace(/^0+/, '');

        return normalized || (digits ? '0' : '');
    }

    function getPeriodoDetalleKey(row) {
        return `${getCedulaKey(row?.cedula)}|${normalizeEmpresaValue(row?.empresa)}`;
    }

    function getReportConfigurationSignature() {
        return JSON.stringify({
            sistema: document.getElementById('ni_sistema').value,
            fecha_inicio: document.getElementById('ni_fecha_ini').value,
            fecha_fin: document.getElementById('ni_fecha_fin').value,
            min_dias: document.getElementById('ni_min_dias').value,
            tipo_pago: document.getElementById('ni_tipo_pago').value,
            modo_calculo: document.getElementById('ni_modo_calculo').value,
            alcance_productos: document.getElementById('ni_alcance_productos').value,
            rangos_pago: payoutRangesByType,
            terminales_excluidas: getExcludedTerminalesArray().sort(),
        });
    }

    function buildPeriodoDetalles(baselineRows) {
        const finalRowsByKey = new Map(cachedRows.map((row) => [getPeriodoDetalleKey(row), row]));

        return (Array.isArray(baselineRows) ? baselineRows : [])
            .map((baselineRow) => {
                const finalRow = finalRowsByKey.get(getPeriodoDetalleKey(baselineRow));
                const incentivoGenerado = toIntegerAmount(baselineRow?.nuevo_incentivo);
                const incentivoDespuesAgencias = toIntegerAmount(finalRow?.nuevo_incentivo);
                const cedula = getCedulaKey(baselineRow?.cedula);
                const empleadoId = getEmpleadoIdKey(baselineRow?.empleadoid);
                const motivos = [];

                if (incentivoGenerado <= 0) {
                    motivos.push('meta_no_alcanzada');
                } else {
                    if (incentivoDespuesAgencias < incentivoGenerado) {
                        motivos.push('agencia_excluida');
                    }
                    if (cedula && excludedFaltantesCedulas.has(cedula)) {
                        motivos.push('faltante');
                    }
                    if (
                        (cedula && excludedDesvinculadosCedulas.has(cedula))
                        || (empleadoId && excludedDesvinculadosEmpleadoIds.has(empleadoId))
                    ) {
                        motivos.push('desvinculado');
                    }
                }

                const tieneRetencionTotal = motivos.includes('faltante') || motivos.includes('desvinculado');
                const montoPagado = tieneRetencionTotal ? 0 : incentivoDespuesAgencias;

                return {
                    cedula: String(baselineRow?.cedula || ''),
                    empleadoid: String(baselineRow?.empleadoid || ''),
                    nombre: String(baselineRow?.nombre || 'Actualizar en maestro de empleados'),
                    empresa: normalizeEmpresaLabel(baselineRow?.empresa),
                    ultima_terminal: String(baselineRow?.ultima_terminal || ''),
                    ultima_agencia_nombre: String(baselineRow?.ultima_agencia_nombre || ''),
                    ventas_ultimo_mes: toIntegerAmount(baselineRow?.ventas_ultimo_mes),
                    ventas_mes_actual: toIntegerAmount(baselineRow?.ventas_mes_actual),
                    dias_ventas: toNumber(baselineRow?.dias_ventas_mes_actual),
                    horas_total: toNumber(baselineRow?.horas_total),
                    incentivo_generado: incentivoGenerado,
                    monto_pagado: montoPagado,
                    motivos,
                    tipos_pago_detalle: Array.isArray(baselineRow?.tipos_pago_detalle)
                        ? baselineRow.tipos_pago_detalle
                        : [],
                };
            });
    }

    function updatePeriodoValidationState() {
        const saveButton = document.getElementById('btnGuardarPeriodoIncentivo');
        const faltantesBadge = document.getElementById('estadoFaltantesPeriodo');
        const desvinculadosBadge = document.getElementById('estadoDesvinculadosPeriodo');

        const updateBadge = (badge, consulted, applied) => {
            badge.className = 'badge ms-1';

            if (applied) {
                badge.classList.add('bg-success');
                badge.textContent = 'Aplicado';
                return;
            }

            if (consulted) {
                badge.classList.add('bg-info');
                badge.textContent = 'Consultado';
                return;
            }

            badge.classList.add('bg-secondary');
            badge.textContent = 'Pendiente';
        };

        updateBadge(faltantesBadge, faltantesPeriodoConsultados, faltantesPeriodoAplicados);
        updateBadge(desvinculadosBadge, desvinculadosPeriodoConsultados, desvinculadosPeriodoAplicados);

        const validationsCompleted = faltantesPeriodoAplicados && desvinculadosPeriodoAplicados;
        const completeReport = document.getElementById('ni_alcance_productos').value === 'completo';
        saveButton.disabled = !cachedRows.length || !validationsCompleted || !completeReport;
        saveButton.title = !completeReport
            ? 'El cierre mensual solo puede guardarse desde Reporte completo'
            : validationsCompleted
            ? 'Guardar el cierre mensual validado'
            : 'Primero consulta y aplica faltantes y usuarios desvinculados';
    }

    function resetPeriodoValidationState() {
        faltantesPeriodoConsultados = false;
        faltantesPeriodoAplicados = false;
        desvinculadosPeriodoConsultados = false;
        desvinculadosPeriodoAplicados = false;
        updatePeriodoValidationState();
    }

    async function guardarPeriodoIncentivo() {
        if (!cachedRows.length) {
            Swal.fire({ title: 'Información', text: 'Primero debes generar el reporte.', icon: 'warning' });
            return;
        }

        if (!faltantesPeriodoAplicados || !desvinculadosPeriodoAplicados) {
            Swal.fire({
                title: 'Validaciones pendientes',
                text: 'Antes de guardar debes consultar y aplicar faltantes y usuarios desvinculados.',
                icon: 'warning',
            });
            return;
        }

        const sistema = document.getElementById('ni_sistema').value;
        const fechaInicio = document.getElementById('ni_fecha_ini').value;
        const fechaFin = document.getElementById('ni_fecha_fin').value;
        const tipoPago = document.getElementById('ni_tipo_pago').value;
        const modoCalculo = document.getElementById('ni_modo_calculo').value;
        const alcanceProductos = document.getElementById('ni_alcance_productos').value;
        const minDias = document.getElementById('ni_min_dias').value;

        if (alcanceProductos !== 'completo') {
            Swal.fire({
                title: 'Cierre mensual incompleto',
                text: 'El cierre mensual solo puede guardarse desde Reporte completo.',
                icon: 'warning',
            });
            return;
        }

        if (sistema !== 'Todos') {
            Swal.fire({
                title: 'Cierre mensual incompleto',
                text: 'Selecciona el sistema Todos y genera nuevamente el reporte antes de guardar.',
                icon: 'warning',
            });
            return;
        }

        if (
            cachedSistema !== sistema
            || cachedTipoPago !== tipoPago
            || cachedModoCalculo !== modoCalculo
            || cachedAlcanceProductos !== alcanceProductos
            || cachedFechaInicio !== fechaInicio
            || cachedFechaFin !== fechaFin
            || cachedReportSignature !== getReportConfigurationSignature()
        ) {
            Swal.fire({
                title: 'Reporte desactualizado',
                text: 'Los parámetros cambiaron. Genera nuevamente el reporte antes de guardar el período.',
                icon: 'warning',
            });
            return;
        }

        const confirmation = await Swal.fire({
            title: 'Guardar período de incentivos',
            text: 'Si este mes ya fue guardado, sus datos serán actualizados sin crear un duplicado.',
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Guardar período',
            cancelButtonText: 'Cancelar',
        });

        if (!confirmation.isConfirmed) {
            return;
        }

        Swal.fire({
            title: 'Preparando cierre mensual...',
            text: 'Comparando el cálculo generado con las exclusiones aplicadas.',
            allowOutsideClick: false,
            showConfirmButton: false,
            didOpen: () => Swal.showLoading(),
        });

        try {
            let baselineRows = cachedRows;
            const terminalesExcluidas = getExcludedTerminalesArray();

            if (terminalesExcluidas.length) {
                const baselineParams = new URLSearchParams({
                    sistema,
                    filtro_cumplimiento: 'todos',
                    fecha_ini: fechaInicio,
                    fecha_fin: fechaFin,
                    min_dias_venta: minDias,
                    tipo_pago: tipoPago,
                    modo_calculo: modoCalculo,
                    alcance_productos: 'completo',
                    rangos_pago: JSON.stringify(payoutRangesByType[tipoPago]),
                    rangos_pago_por_tipo: JSON.stringify(payoutRangesByType),
                    terminales_excluidas: JSON.stringify([]),
                });
                const baselineResponse = await fetch('/incentivos/calculo-de-incentivos/reporte?' + baselineParams.toString(), {
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                });
                const baselinePayload = await parseResponseAsJson(
                    baselineResponse,
                    'No se pudo calcular la base sin agencias excluidas'
                );
                baselineRows = normalizeIntegerReportRows(baselinePayload.data || []);
            }

            const detalles = buildPeriodoDetalles(baselineRows);
            if (!detalles.length) {
                throw new Error('No hay personas en el reporte para guardar en este período.');
            }

            const response = await fetch(@json(route('incentivos.calculo.periodo.guardar')), {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': csrfToken(),
                },
                body: JSON.stringify({
                    fecha_inicio: fechaInicio,
                    fecha_fin: fechaFin,
                    sistema,
                    alcance_productos: alcanceProductos,
                    modo_calculo: modoCalculo,
                    tipo_pago_defecto: tipoPago,
                    min_dias_venta: Number(minDias),
                    rangos_pago_por_tipo: payoutRangesByType,
                    terminales_excluidas: terminalesExcluidas,
                    faltantes_aplicados: faltantesPeriodoAplicados,
                    desvinculados_aplicados: desvinculadosPeriodoAplicados,
                    detalles,
                }),
            });
            const payload = await parseResponseAsJson(response, 'No se pudo guardar el período');
            const resumen = payload.resumen || {};

            Swal.fire({
                title: payload.actualizado ? 'Período actualizado' : 'Período guardado',
                html: `
                    <div class="text-start">
                        <div><strong>Revisión:</strong> ${toNumber(payload?.periodo?.revision)}</div>
                        <div><strong>Pagados:</strong> ${toNumber(resumen.pagados).toLocaleString('en-US')}</div>
                        <div><strong>Pagados parcialmente:</strong> ${toNumber(resumen.pagados_parciales).toLocaleString('en-US')}</div>
                        <div><strong>No pagados:</strong> ${toNumber(resumen.no_pagados).toLocaleString('en-US')}</div>
                        <div><strong>Monto pagado:</strong> ${formatMoney(resumen.monto_pagado)}</div>
                        <div><strong>Monto no pagado:</strong> ${formatMoney(resumen.monto_no_pagado)}</div>
                    </div>
                `,
                icon: 'success',
            });
        } catch (error) {
            Swal.fire({ title: 'Error', text: error?.message || String(error), icon: 'error' });
        }
    }

    function getEmpleadoIdKey(value) {
        return String(value ?? '').trim();
    }

    function getFixedBagBaseBudget(administrativePoolBase) {
        return toIntegerAmount(toIntegerAmount(administrativePoolBase) * (getPuestoPctByCategoryKey('g45') / 100));
    }

    function getFixedBagMissingBeforeTopUp(administrativePoolBase) {
        const fixedTotal = getFixedAdministrativeAmountTotal();
        const baseBudget = getFixedBagBaseBudget(administrativePoolBase);

        return Math.max(fixedTotal - baseBudget, 0);
    }

    function sumIncentivesByCedulas(rows, cedulasSet) {
        return (Array.isArray(rows) ? rows : [])
            .filter((row) => cedulasSet.has(getCedulaKey(row?.cedula)))
            .reduce((sum, row) => sum + toIntegerAmount(row?.nuevo_incentivo), 0);
    }

    function sumIncentivesByDesvinculados(rows, cedulasSet, empleadoIdsSet, excludedCedulasSet = new Set()) {
        return (Array.isArray(rows) ? rows : [])
            .filter((row) => {
                const cedula = getCedulaKey(row?.cedula);
                if (cedula && excludedCedulasSet.has(cedula)) {
                    return false;
                }

                return (cedula && cedulasSet.has(cedula))
                    || empleadoIdsSet.has(getEmpleadoIdKey(row?.empleadoid));
            })
            .reduce((sum, row) => sum + toIntegerAmount(row?.nuevo_incentivo), 0);
    }

    function isExcludedFromMainIncentiveTable(row) {
        const cedula = getCedulaKey(row?.cedula);
        const empleadoId = getEmpleadoIdKey(row?.empleadoid);

        return (cedula && excludedFaltantesCedulas.has(cedula))
            || (cedula && excludedDesvinculadosCedulas.has(cedula))
            || (empleadoId && excludedDesvinculadosEmpleadoIds.has(empleadoId));
    }

    function getCoordinatorIdKey(row) {
        const value = row?.id ?? row?.nombre ?? '';
        return String(value).trim();
    }

    function isRowExcludedByModuleDesvinculados(row, cedulasSet, empleadoIdsSet) {
        const empleadoId = getEmpleadoIdKey(row?.empleadoid);
        if (empleadoId) {
            return empleadoIdsSet.has(empleadoId);
        }

        const cedula = getCedulaKey(row?.cedula);
        return cedula ? cedulasSet.has(cedula) : false;
    }

    function isAdministrativeDesvinculadoExcluded(row) {
        return isRowExcludedByModuleDesvinculados(
            row,
            excludedAdministrativeDesvinculadosCedulas,
            excludedAdministrativeDesvinculadosEmpleadoIds
        );
    }

    function isCoordinatorDesvinculadoExcluded(row) {
        return isRowExcludedByModuleDesvinculados(
            row,
            excludedCoordinatorDesvinculadosCedulas,
            excludedCoordinatorDesvinculadosEmpleadoIds
        );
    }

    function getCoordinatorDisplayRows() {
        return coordinatorRows
            .map((row, idx) => ({ ...row, __idx: idx }))
            .filter((row) => !isCoordinatorDesvinculadoExcluded(row));
    }

    function getCoordinatorAppliedAmount(row) {
        const key = getCoordinatorIdKey(row);
        if (!key || !excludedCoordinatorIds.has(key)) {
            return 0;
        }

        return toIntegerAmount(appliedCoordinatorAmounts[key] ?? getCoordinatorAmount(row));
    }

    function getCoordinatorExcludedTotal() {
        return coordinatorRows.reduce((sum, row) => sum + getCoordinatorAppliedAmount(row), 0);
    }

    function calculateExcludedApplication(shortage) {
        const baseRows = getBaseFilteredRows();
        const faltantesCedulas = new Set([...excludedFaltantesCedulas].map(getCedulaKey).filter(Boolean));
        const desvinculadosCedulas = new Set([...excludedDesvinculadosCedulas].map(getCedulaKey).filter(Boolean));
        const desvinculadosEmpleadoIds = new Set([...excludedDesvinculadosEmpleadoIds].map(getEmpleadoIdKey).filter(Boolean));
        const faltantesDisponible = sumIncentivesByCedulas(baseRows, faltantesCedulas);
        const desvinculadosDisponible = sumIncentivesByDesvinculados(baseRows, desvinculadosCedulas, desvinculadosEmpleadoIds, faltantesCedulas);
        const coordinadoresDisponible = getCoordinatorExcludedTotal();
        const faltantesUsados = Math.min(toIntegerAmount(shortage), faltantesDisponible);
        const restante = Math.max(toIntegerAmount(shortage) - faltantesUsados, 0);
        const desvinculadosUsados = Math.min(restante, desvinculadosDisponible);
        const restanteDespuesDesvinculados = Math.max(restante - desvinculadosUsados, 0);
        const coordinadoresUsados = Math.min(restanteDespuesDesvinculados, coordinadoresDisponible);

        return {
            faltantesDisponible,
            desvinculadosDisponible,
            coordinadoresDisponible,
            aplicadoFaltantes: faltantesUsados,
            aplicadoDesvinculados: desvinculadosUsados,
            aplicadoCoordinadores: coordinadoresUsados,
            rebajadoFaltantes: Math.max(faltantesDisponible - faltantesUsados, 0),
            rebajadoDesvinculados: Math.max(desvinculadosDisponible - desvinculadosUsados, 0),
            rebajadoCoordinadores: Math.max(coordinadoresDisponible - coordinadoresUsados, 0),
            totalAplicado: faltantesUsados + desvinculadosUsados + coordinadoresUsados,
            totalRebajado: Math.max(faltantesDisponible - faltantesUsados, 0)
                + Math.max(desvinculadosDisponible - desvinculadosUsados, 0)
                + Math.max(coordinadoresDisponible - coordinadoresUsados, 0),
        };
    }

    function renderResumenEmpresas(meta) {
        const resumen = Array.isArray(meta?.resumen_empresas) ? meta.resumen_empresas : [];
        if (!resumen.length) {
            return '';
        }

        const resumenText = ' | ' + resumen
            .map((row) => {
                const empresa = normalizeEmpresaLabel(row?.empresa);
                const vendido = formatMoney(row?.total_vendido);
                const incentivo = formatMoney(row?.total_incentivo);
                return `${empresa}: ventas ${vendido}, incentivo ${incentivo}`;
            })
            .join(' | ');
        const aviso = String(meta?.aviso_agencias_por_asignar_empresa || '').trim();

        return aviso ? `${resumenText} | Nota: ${aviso}` : resumenText;
    }

    function evaluateMetaMinima(row) {
        const ventasMesActual = toIntegerAmount(row?.ventas_mes_actual);
        const cumplio = ventasMesActual >= META_MINIMA_VENTA;
        const faltante = cumplio ? 0 : (META_MINIMA_VENTA - ventasMesActual);
        const faltantePct = META_MINIMA_VENTA > 0
            ? Math.max((faltante / META_MINIMA_VENTA) * 100, 0)
            : 0;

        return {
            cumplio,
            ventasMesActual,
            faltante,
            faltantePct,
        };
    }

    function buildAdministrativePoolByEmpresa(data) {
        const resumenEmpresas = Array.isArray(cachedMeta?.resumen_empresas) ? cachedMeta.resumen_empresas : [];
        const source = resumenEmpresas.length
            ? resumenEmpresas.map(row => ({
                empresa: row?.empresa,
                incentivo: row?.total_incentivo,
            }))
            : (Array.isArray(data) ? data : []).map(row => ({
                empresa: row?.empresa,
                incentivo: row?.nuevo_incentivo,
            }));

        return source.reduce((acc, item) => {
            const key = normalizeAdministrativeEmpresaKey(item?.empresa);
            const incentivo = toIntegerAmount(item?.incentivo);
            if (!acc[key]) {
                acc[key] = 0;
            }
            acc[key] += calculateAdministrativeDistribution(incentivo).administrativo;
            return acc;
        }, {});
    }

    function normalizeEmpresaValue(value) {
        const text = String(value ?? '').trim().toLowerCase();
        return text === '' ? 'sin empresa' : text;
    }

    function normalizeEmpresaLabel(value) {
        const text = String(value ?? '').trim();
        return text === '' ? 'Sin empresa' : text;
    }

    function esAgenciaPorAsignarEmpresa(row) {
        return normalizeEmpresaValue(row?.empresa) === 'agencias por asignar empresa';
    }

    function normalizeInformeEmpresaKey(value) {
        const text = normalizeEmpresaValue(value);
        if (text.includes('joselito')) return 'joselito';
        if (text.includes('negosur')) return 'negosur';
        return text;
    }

    function getInformeEmpresaLabel(key) {
        if (key === 'joselito') return 'Grupo Joselito';
        if (key === 'negosur') return 'Negosur';
        return 'Completo';
    }

    function normalizeAdministrativeEmpresaKey(value) {
        const text = String(value ?? '').trim().toLowerCase();
        if (text === '') return 'sin empresa';
        if (text.includes('joselito') || text.includes('cjoselito')) return 'consorcio joselito';
        if (text.includes('negosur')) return 'negosur';
        return text;
    }

    function normalizeAdministrativeEmpresaLabel(value) {
        const key = normalizeAdministrativeEmpresaKey(value);
        if (key === 'consorcio joselito') return 'Consorcio Joselito';
        if (key === 'negosur') return 'Negosur';
        if (key === 'sin empresa') return 'Sin empresa';
        return String(value ?? '').trim();
    }

    function normalizePagoEmpresaGroupKey(value) {
        const text = normalizeEmpresaValue(value);
        if (text.includes('joselito') || text.includes('cjoselito')) return 'joselito';
        if (text.includes('negosur')) return 'negosur';
        return text;
    }

    function getAdministrativeEmpresaFilterKey() {
        const selected = document.getElementById('ni_filtro_empresa')?.value || 'todos';
        if (selected === 'todos') return 'todos';
        if (selected.includes('joselito')) return 'consorcio joselito';
        if (selected.includes('negosur')) return 'negosur';
        if (selected === 'sin empresa') return 'sin empresa';
        return selected;
    }

    function populateEmpresaFilterOptions(rows) {
        const select = document.getElementById('ni_filtro_empresa');
        if (!select) return;

        const currentValue = select.value || 'todos';
        const optionsByKey = new Map();

        (Array.isArray(rows) ? rows : []).forEach((row) => {
            const label = normalizeEmpresaLabel(row?.empresa);
            const key = normalizeEmpresaValue(label);
            if (!optionsByKey.has(key)) {
                optionsByKey.set(key, label);
            }
        });

        const options = Array.from(optionsByKey.entries())
            .sort((a, b) => a[1].localeCompare(b[1], 'es', { sensitivity: 'base' }));

        select.innerHTML = '<option value="todos">Todas</option>' + options
            .map(([value, label]) => `<option value="${escapeHtml(value)}">${escapeHtml(label)}</option>`)
            .join('');

        select.value = optionsByKey.has(currentValue) ? currentValue : 'todos';
    }

    function updatePuestoPctSummaryCard() {
        const target = document.getElementById('ni_pct_puesto_resumen');
        if (!target) {
            return;
        }

        const montoBase = toIntegerAmount(currentAdministrativePoolBase);
        const montoG1 = toIntegerAmount(montoBase * (toNumber(puestoPctConfig.g1) / 100));
        const montoG2 = toIntegerAmount(montoBase * (toNumber(puestoPctConfig.g2) / 100));
        const montoG45 = toIntegerAmount(montoBase * (toNumber(puestoPctConfig.g45) / 100));
        const fixedBalance = getFixedAdministrativeBalance();
        const fixedBalanceText = fixedBalance.missing > 0
            ? `Faltan ${formatMoney(fixedBalance.missing)}`
            : `Restante ${formatMoney(fixedBalance.remaining)}`;

        target.innerHTML = `
            <tr>
                <td>1 Gtes. y Encarg.</td>
                <td class="text-end">${toNumber(puestoPctConfig.g1).toFixed(2)}%</td>
                <td class="text-end">${formatMoney(montoG1)}</td>
            </tr>
            <tr>
                <td>2 Monitoreo</td>
                <td class="text-end">${toNumber(puestoPctConfig.g2).toFixed(2)}%</td>
                <td class="text-end">${formatMoney(montoG2)}</td>
            </tr>
            <tr>
                <td>4 Operadores + 5 Servs. Tecnicos + 6 Seguridad</td>
                <td class="text-end">${toNumber(puestoPctConfig.g45).toFixed(2)}%</td>
                <td class="text-end">${formatMoney(montoG45)}<br><small class="${fixedBalance.missing > 0 ? 'text-danger' : 'text-muted'}">${fixedBalanceText}</small></td>
            </tr>
        `;
    }

    function escapeHtml(value) {
        return String(value ?? '')
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    function normalizeTerminalValue(value) {
        return String(value ?? '').trim();
    }

    function getExcludedTerminalesArray() {
        return [...excludedTerminales].map(normalizeTerminalValue).filter(Boolean).sort();
    }

    function persistExcludedTerminales() {
        updateExcludedTerminalesCount();
    }

    function renderTerminalesExcluidasActuales() {
        const terminalesActuales = getExcludedTerminalesArray();
        const resultado = document.getElementById('resultadoTerminalesExcluidas');
        const aplicarBtn = document.getElementById('btnAplicarTerminalesExcluidas');

        if (terminalesActuales.length) {
            resultado.innerHTML = `
                <div class="small">
                    <div><strong>Terminales excluidas actualmente:</strong> ${terminalesActuales.length.toLocaleString('es-DO')}</div>
                    <div class="terminal-excluida-list border rounded p-2 mt-2">
                        ${terminalesActuales.map((terminal, idx) => `
                            <div class="form-check">
                                <input class="form-check-input terminal-excluida-check" type="checkbox" value="${escapeHtml(terminal)}" id="terminal_excluida_actual_${idx}" checked>
                                <label class="form-check-label" for="terminal_excluida_actual_${idx}">${escapeHtml(terminal)}</label>
                            </div>
                        `).join('')}
                    </div>
                    <div class="text-muted mt-2">Desmarca una terminal y aplica para quitarla de la configuracion fija.</div>
                </div>
            `;
            resultado.style.display = 'block';
            aplicarBtn.disabled = false;
            return;
        }

        resultado.style.display = 'none';
        resultado.innerHTML = '';
        aplicarBtn.disabled = true;
    }

    async function guardarTerminalesExcluidasIncentivo(terminales) {
        const response = await fetch('/incentivos/calculo-de-incentivos/terminales-excluidas', {
            method: 'POST',
            headers: {
                'Accept': 'application/json',
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': csrfToken(),
            },
            credentials: 'same-origin',
            body: JSON.stringify({
                terminales,
                _token: csrfToken(),
            }),
        });

        return parseResponseAsJson(response, 'Error guardando terminales excluidas');
    }

    function updateExcludedTerminalesCount() {
        const count = getExcludedTerminalesArray().length;
        const target = document.getElementById('terminalesExcluidasCount');
        if (target) {
            target.textContent = count;
        }
    }

    function renderTerminalesExcluidasResultado(response) {
        const container = document.getElementById('resultadoTerminalesExcluidas');
        const button = document.getElementById('btnAplicarTerminalesExcluidas');
        const encontradas = Array.isArray(response?.terminales_encontradas) ? response.terminales_encontradas : [];
        const noEncontradas = Array.isArray(response?.terminales_no_encontradas) ? response.terminales_no_encontradas : [];
        recognizedTerminalesExcluidas = encontradas.map(normalizeTerminalValue).filter(Boolean);
        const actuales = getExcludedTerminalesArray();
        const nuevas = recognizedTerminalesExcluidas.filter(terminal => !actuales.includes(terminal));

        const noEncontradasHtml = noEncontradas.length
            ? `<details class="mt-2"><summary class="text-danger" style="cursor:pointer;">No encontradas (${noEncontradas.length})</summary><div class="small mt-2" style="max-height: 120px; overflow-y:auto;"><ul class="mb-0 ps-3">${noEncontradas.map(t => `<li>${escapeHtml(t)}</li>`).join('')}</ul></div></details>`
            : '<small class="text-success">Todas las terminales reconocidas existen en agencias.</small>';

        const actualesHtml = actuales.length
            ? `<div class="terminal-excluida-list border rounded p-2 mt-2">
                ${actuales.map((terminal, idx) => `
                    <div class="form-check">
                        <input class="form-check-input terminal-excluida-check" type="checkbox" value="${escapeHtml(terminal)}" id="terminal_excluida_actual_reconocida_${idx}" checked>
                        <label class="form-check-label" for="terminal_excluida_actual_reconocida_${idx}">${escapeHtml(terminal)}</label>
                    </div>
                `).join('')}
            </div>`
            : '<div class="text-muted small mt-2">No hay terminales excluidas actualmente.</div>';

        const terminalesHtml = nuevas.length
            ? `<div class="terminal-excluida-list border rounded p-2 mt-2">
                ${nuevas.map((terminal, idx) => `
                    <div class="form-check">
                        <input class="form-check-input terminal-excluida-check" type="checkbox" value="${escapeHtml(terminal)}" id="terminal_excluida_nueva_${idx}" checked>
                        <label class="form-check-label" for="terminal_excluida_nueva_${idx}">${escapeHtml(terminal)}</label>
                    </div>
                `).join('')}
            </div>`
            : '<div class="text-muted small mt-2">No hay terminales nuevas para agregar.</div>';

        container.innerHTML = `
            <div class="small">
                <div><strong>Filas en archivo:</strong> ${Number(response?.total_filas || 0).toLocaleString('es-DO')}</div>
                <div><strong>Terminales leidas:</strong> ${Number(response?.terminales_leidas || 0).toLocaleString('es-DO')}</div>
                <div><strong>Terminales unicas:</strong> ${Number(response?.terminales_unicas || 0).toLocaleString('es-DO')}</div>
                <div><strong>Coinciden en agencias:</strong> ${Number(response?.encontradas || 0).toLocaleString('es-DO')}</div>
                <div><strong>No existen en agencias:</strong> ${Number(response?.no_encontradas || 0).toLocaleString('es-DO')}</div>
                <div class="mt-2">${noEncontradasHtml}</div>
                <div class="mt-3"><strong>Terminales que quedaran excluidas:</strong></div>
                ${actualesHtml}
                <div class="mt-3"><strong>Nuevas terminales reconocidas:</strong></div>
                ${terminalesHtml}
            </div>
        `;
        container.style.display = 'block';
        button.disabled = actuales.length === 0 && nuevas.length === 0;
    }

    function renderNombreEmpleado(value) {
        const nombre = String(value || '').trim() || 'Actualizar en maestro de empleados';

        if (nombre === 'Actualizar en maestro de empleados') {
            return `<span class="empleado-maestro-pendiente">${escapeHtml(nombre)}</span>`;
        }

        return escapeHtml(nombre);
    }

    function esNombrePendiente(row) {
        return String(row?.nombre || '').trim() === 'Actualizar en maestro de empleados';
    }

    function normalizeCedulaValue(value) {
        return String(value ?? '').replace(/\D+/g, '');
    }

    function hasEmpleadoId(row) {
        return String(row?.empleadoid ?? '').trim() !== '';
    }

    function getEmpleadoIdPagoSummary(sourceRows = getPagoIncentivoExportRows()) {
        const rows = (Array.isArray(sourceRows) ? sourceRows : [])
            .map((row) => ({
                ...row,
                __importe: toIntegerAmount(row?.nuevo_incentivo),
                __hasEmpleadoId: hasEmpleadoId(row),
            }))
            .filter((row) => row.__importe > 0);

        const conIdRows = rows.filter(row => row.__hasEmpleadoId);
        const sinIdRows = rows.filter(row => !row.__hasEmpleadoId);
        const countUniqueCedulas = (items) => new Set(items
            .map(row => normalizeCedulaValue(row?.cedula) || String(row?.cedula ?? '').trim())
            .filter(Boolean)).size;

        return {
            totalUsuarios: countUniqueCedulas(rows),
            totalMonto: rows.reduce((sum, row) => sum + row.__importe, 0),
            usuariosConId: countUniqueCedulas(conIdRows),
            montoConId: conIdRows.reduce((sum, row) => sum + row.__importe, 0),
            usuariosSinId: countUniqueCedulas(sinIdRows),
            montoSinId: sinIdRows.reduce((sum, row) => sum + row.__importe, 0),
        };
    }

    function getUsuariosPorActualizarRows(sourceRows = currentFilteredRows) {
        const rows = Array.isArray(sourceRows) ? sourceRows : [];
        const grouped = new Map();

        rows
            .filter(row => esNombrePendiente(row))
            .forEach((row) => {
                const cedulaNormalizada = normalizeCedulaValue(row?.cedula);
                const cedulaOriginal = String(row?.cedula ?? '').trim();
                const key = cedulaNormalizada || cedulaOriginal;

                if (!key) {
                    return;
                }

                const empresa = normalizeEmpresaLabel(row?.empresa);
                const fecha = String(row?.ultimo_dia_venta || '').trim();
                const current = grouped.get(key) || {
                    cedula: cedulaOriginal || cedulaNormalizada,
                    nombre: 'Actualizar en maestro de empleados',
                    empresaSet: new Set(),
                    ultima_terminal: String(row?.ultima_terminal || '').trim(),
                    ultima_agencia_nombre: String(row?.ultima_agencia_nombre || 'SIN AGENCIA').trim() || 'SIN AGENCIA',
                    ultimo_dia_venta: fecha,
                };

                if (empresa) {
                    current.empresaSet.add(empresa);
                }

                if (!current.ultimo_dia_venta || (fecha && fecha > current.ultimo_dia_venta)) {
                    current.ultimo_dia_venta = fecha;
                    current.ultima_terminal = String(row?.ultima_terminal || '').trim();
                    current.ultima_agencia_nombre = String(row?.ultima_agencia_nombre || 'SIN AGENCIA').trim() || 'SIN AGENCIA';
                }

                grouped.set(key, current);
            });

        return Array.from(grouped.values())
            .map((row) => ({
                cedula: row.cedula,
                nombre: row.nombre,
                empresa: Array.from(row.empresaSet).join(' | '),
                ultima_terminal: row.ultima_terminal,
                ultima_agencia_nombre: row.ultima_agencia_nombre,
                ultimo_dia_venta: row.ultimo_dia_venta,
            }))
            .sort((a, b) => String(b.ultimo_dia_venta || '').localeCompare(String(a.ultimo_dia_venta || '')));
    }

    function getUsuariosCumplimientoSummary(sourceRows = currentFilteredRows) {
        const rows = Array.isArray(sourceRows) ? sourceRows : [];
        const grouped = new Map();

        rows.forEach((row) => {
            const cedulaNormalizada = normalizeCedulaValue(row?.cedula);
            const cedulaOriginal = String(row?.cedula ?? '').trim();
            const key = cedulaNormalizada || cedulaOriginal;

            if (!key) {
                return;
            }

            const current = grouped.get(key) || {
                cumplio: false,
                tieneFila: false,
            };

            current.tieneFila = true;
            if (evaluateMetaMinima(row).cumplio) {
                current.cumplio = true;
            }

            grouped.set(key, current);
        });

        let cumplen = 0;
        let noCumplen = 0;

        grouped.forEach((row) => {
            if (!row.tieneFila) {
                return;
            }

            if (row.cumplio) {
                cumplen += 1;
            } else {
                noCumplen += 1;
            }
        });

        return {
            cumplen,
            noCumplen,
        };
    }

    function formatDateDisplay(value) {
        const text = String(value || '').trim();
        if (!text) return '-';
        const parts = text.split('-');
        return parts.length === 3 ? `${parts[2]}/${parts[1]}/${parts[0]}` : text;
    }

    function renderAgenciaFormato(row) {
        const nombreAgencia = String(row?.ultima_agencia_nombre || 'SIN AGENCIA').trim() || 'SIN AGENCIA';
        const terminal = String(row?.ultima_terminal || '-').trim() || '-';

        return `
            <div class="agencia-formato-comparativa">
                <div class="agencia-nombre">${escapeHtml(nombreAgencia)}</div>
                <small class="agencia-terminal">Terminal: ${escapeHtml(terminal)}</small>
            </div>
        `;
    }

    function toCsvValue(value) {
        const text = String(value ?? '');
        if (/[",\n]/.test(text)) {
            return `"${text.replace(/"/g, '""')}"`;
        }
        return text;
    }

    function exportRowsToCsv(rows, headers, mapper, filename) {
        if (!Array.isArray(rows) || !rows.length) {
            Swal.fire({ title: 'Sin datos', text: 'No hay registros para exportar.', icon: 'warning' });
            return;
        }

        const csvRows = [
            headers.map(toCsvValue).join(','),
            ...rows.map(row => mapper(row).map(toCsvValue).join(','))
        ];
        const blob = new Blob(['\ufeff' + csvRows.join('\n')], { type: 'text/csv;charset=utf-8;' });
        const url = URL.createObjectURL(blob);
        const link = document.createElement('a');
        link.href = url;
        link.download = filename;
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
        URL.revokeObjectURL(url);
    }

    function exportRowsToExcelCsv(filename, headers, rows) {
        const csvLines = [];
        csvLines.push(headers.map(toCsvValue).join(','));
        rows.forEach((row) => {
            csvLines.push(row.map(toCsvValue).join(','));
        });

        const csvContent = '\uFEFF' + csvLines.join('\n');
        const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
        const url = URL.createObjectURL(blob);
        const link = document.createElement('a');
        link.href = url;
        link.download = filename;
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
        URL.revokeObjectURL(url);
    }

    function toTxtPagoValue(value) {
        return String(value ?? '')
            .replace(/\t/g, ' ')
            .replace(/\r?\n/g, ' ')
            .trim();
    }

    function getComentarioPagoIncentivo() {
        const fechaFin = document.getElementById('ni_fecha_fin')?.value || '';
        const fecha = fechaFin ? new Date(`${fechaFin}T00:00:00`) : new Date();
        const mes = fecha.toLocaleDateString('es-DO', { month: 'long', year: 'numeric' });

        return `pago de incentivo ${mes}`;
    }

    function getFechaFinPagoIncentivo() {
        return document.getElementById('ni_fecha_fin')?.value || new Date().toISOString().slice(0, 10);
    }

    const PAGO_INCENTIVO_HEADERS = ['IdEmpleado', 'Empresa', 'ViaPago', 'Ciudad', 'IdNovedad', 'Importe', 'Aplicar A', 'IdSucursal', 'Id Doc', 'Comentario', 'IdViejo'];

    function buildPagoIncentivoData(rows, options = {}) {
        const excludeSinEmpleadoId = Boolean(options.excludeSinEmpleadoId);
        const includeNombre = Boolean(options.includeNombre);
        const emptyMessage = options.emptyMessage || 'No hay registros con importe mayor a cero para generar el archivo de pago.';
        const mappedRows = (Array.isArray(rows) ? rows : [])
            .map((row) => ({
                ...row,
                __importe: toIntegerAmount(row?.importe ?? row?.nuevo_incentivo ?? row?.monto),
                __idEmpleado: String(row?.empleadoid ?? '').trim(),
                __nombre: String(row?.nombre ?? '').trim(),
                __empresa: String(row?.empresa ?? '').trim(),
                __viapago: String(row?.viapago ?? '').trim(),
                __ciudad: String(row?.ciudad ?? '').trim(),
            }))
            .filter((row) => !excludeSinEmpleadoId || row.__idEmpleado !== '')
            .filter((row) => row.__importe > 0);

        if (!mappedRows.length) {
            Swal.fire({ title: 'Sin datos', text: emptyMessage, icon: 'warning' });
            return null;
        }

        const comentario = getComentarioPagoIncentivo();
        const headers = includeNombre
            ? ['IdEmpleado', 'Nombre', ...PAGO_INCENTIVO_HEADERS.slice(1)]
            : PAGO_INCENTIVO_HEADERS;

        return {
            headers,
            rows: mappedRows.map((row) => [
                row.__idEmpleado,
                ...(includeNombre ? [row.__nombre] : []),
                row.__empresa,
                row.__viapago,
                row.__ciudad,
                'INC',
                String(row.__importe),
                '',
                '',
                '',
                comentario,
                '',
            ].map(toTxtPagoValue)),
            fechaFin: getFechaFinPagoIncentivo(),
            suffix: options.suffix || '',
        };
    }

    function getPagoIncentivoExportRows() {
        let rows = getBaseFilteredRows({ includeEmpresaFilter: false });

        if (excludedFaltantesCedulas.size) {
            rows = rows.filter(item => !excludedFaltantesCedulas.has(getCedulaKey(item?.cedula)));
        }

        if (excludedDesvinculadosCedulas.size) {
            rows = rows.filter(item => !excludedDesvinculadosCedulas.has(getCedulaKey(item?.cedula)));
        }

        if (excludedDesvinculadosEmpleadoIds.size) {
            rows = rows.filter(item => !excludedDesvinculadosEmpleadoIds.has(getEmpleadoIdKey(item?.empleadoid)));
        }

        return rows;
    }

    function getPagoIncentivoExportData(options = {}) {
        const excludeSinEmpleadoId = Boolean(options.excludeSinEmpleadoId);
        const rows = getPagoIncentivoExportRows()
            .map((row) => ({
                empleadoid: row?.empleadoid,
                empresa: normalizeEmpresaLabel(row?.empresa),
                viapago: row?.viapago,
                ciudad: row?.ciudad,
                importe: row?.nuevo_incentivo,
            }))
            .filter((row) => toIntegerAmount(row.importe) > 0);

        return buildPagoIncentivoData(rows, {
            excludeSinEmpleadoId,
            suffix: excludeSinEmpleadoId ? '_sin_usuarios_sin_id' : '_completo',
            emptyMessage: excludeSinEmpleadoId
                ? 'No hay incentivos con IdEmpleado e importe mayor a cero para generar el archivo de pago.'
                : 'No hay incentivos con importe mayor a cero para generar el archivo de pago.',
        });
    }

    function getAdministrativosPagoExportData() {
        const rows = getAdministrativeDisplayRows()
            .map((row) => ({
                empleadoid: row?.empleadoid,
                empresa: normalizeAdministrativeEmpresaLabel(row?.empresa),
                viapago: row?.viapago,
                ciudad: row?.ciudad,
                importe: getAdministrativeDisplayAmount(row),
            }))
            .filter((row) => toIntegerAmount(row.importe) > 0);

        return buildPagoIncentivoData(rows, {
            suffix: '_administrativo',
            emptyMessage: 'No hay registros administrativos con importe mayor a cero para generar el TXT de pago.',
        });
    }

    function getCoordinadoresPagoExportData() {
        const rows = getCoordinatorDisplayRows()
            .map((row) => ({
                empleadoid: row?.empleadoid,
                empresa: String(row?.empresa ?? '').trim(),
                viapago: row?.viapago,
                ciudad: row?.ciudad,
                importe: getCoordinatorDisplayAmount(row),
            }))
            .filter((row) => toIntegerAmount(row.importe) > 0);

        return buildPagoIncentivoData(rows, {
            suffix: '_coordinadores',
            emptyMessage: 'No hay coordinadores con importe mayor a cero para generar el TXT de pago.',
        });
    }

    function getAdmiCoorPagoExportData() {
        const rows = [
            ...getAdministrativeDisplayRows().map((row) => ({
                empleadoid: row?.empleadoid,
                nombre: row?.nombre,
                empresa: normalizeAdministrativeEmpresaLabel(row?.empresa),
                viapago: row?.viapago,
                ciudad: row?.ciudad,
                importe: getAdministrativeDisplayAmount(row),
            })),
            ...getCoordinatorDisplayRows().map((row) => ({
                empleadoid: row?.empleadoid,
                nombre: row?.nombre,
                empresa: String(row?.empresa ?? '').trim(),
                viapago: row?.viapago,
                ciudad: row?.ciudad,
                importe: getCoordinatorDisplayAmount(row),
            })),
        ].filter((row) => toIntegerAmount(row.importe) > 0);

        return buildPagoIncentivoData(rows, {
            includeNombre: true,
            suffix: '_admi_coor',
            emptyMessage: 'No hay registros administrativos ni coordinadores con importe mayor a cero para generar el Excel de pago.',
        });
    }

    function getAdmiCoorPagoExportWorkbookData(options = {}) {
        const combineByCompany = Boolean(options.combineByCompany);
        const buildRows = (rows) => buildPagoIncentivoData(rows, {
            includeNombre: true,
            suffix: combineByCompany ? '_admi_coor_por_empresa' : '_admi_coor',
            emptyMessage: 'No hay registros administrativos ni coordinadores con importe mayor a cero para generar el Excel de pago.',
        });

        const administrativos = getAdministrativeDisplayRows()
            .map((row) => ({
                empleadoid: row?.empleadoid,
                nombre: row?.nombre,
                empresa: normalizeAdministrativeEmpresaLabel(row?.empresa),
                viapago: row?.viapago,
                ciudad: row?.ciudad,
                importe: getAdministrativeDisplayAmount(row),
            }))
            .filter((row) => toIntegerAmount(row.importe) > 0);

        const coordinadores = getCoordinatorDisplayRows()
            .map((row) => ({
                empleadoid: row?.empleadoid,
                nombre: row?.nombre,
                empresa: String(row?.empresa ?? '').trim(),
                viapago: row?.viapago,
                ciudad: row?.ciudad,
                importe: getCoordinatorDisplayAmount(row),
            }))
            .filter((row) => toIntegerAmount(row.importe) > 0);

        const groups = [
            {
                key: 'joselito',
                label: 'Joselito',
            },
            {
                key: 'negosur',
                label: 'Negosur',
            },
        ];

        const sheets = groups.flatMap((group) => {
            const adminRows = administrativos.filter((row) => normalizePagoEmpresaGroupKey(row.empresa) === group.key);
            const coordRows = coordinadores.filter((row) => normalizePagoEmpresaGroupKey(row.empresa) === group.key);

            if (combineByCompany) {
                const companyData = [...adminRows, ...coordRows].length
                    ? buildRows([...adminRows, ...coordRows])
                    : null;

                return companyData ? [{
                    name: group.label,
                    headers: companyData.headers,
                    rows: companyData.rows,
                }] : [];
            }

            const adminData = adminRows.length ? buildRows(adminRows) : null;
            const coordData = coordRows.length ? buildRows(coordRows) : null;

            return [
                adminData ? {
                    name: `${group.label} Administrativo`,
                    headers: adminData.headers,
                    rows: adminData.rows,
                } : null,
                coordData ? {
                    name: `${group.label} Coordinadores`,
                    headers: coordData.headers,
                    rows: coordData.rows,
                } : null,
            ].filter(Boolean);
        });

        if (!sheets.length) {
            Swal.fire({
                title: 'Sin datos',
                text: 'No hay registros administrativos ni coordinadores de Joselito o Negosur con importe mayor a cero para generar el Excel de pago.',
                icon: 'warning',
            });
            return null;
        }

        return {
            sheets,
            fechaFin: getFechaFinPagoIncentivo(),
            suffix: combineByCompany ? '_admi_coor_por_empresa' : '_admi_coor',
        };
    }

    function getPreNominaConfigurations() {
        return [
            {
                tipo: 'administrativos',
                empresa: 'joselito',
                titulo: 'Administrativos - Consorcio Joselito',
                countId: 'preNominaAdminJoselitoCount',
                totalId: 'preNominaAdminJoselitoTotal',
                filename: 'administrativos_joselito',
            },
            {
                tipo: 'administrativos',
                empresa: 'negosur',
                titulo: 'Administrativos - Negosur',
                countId: 'preNominaAdminNegosurCount',
                totalId: 'preNominaAdminNegosurTotal',
                filename: 'administrativos_negosur',
            },
            {
                tipo: 'coordinadores',
                empresa: 'joselito',
                titulo: 'Coordinadores - Consorcio Joselito',
                countId: 'preNominaCoordJoselitoCount',
                totalId: 'preNominaCoordJoselitoTotal',
                filename: 'coordinadores_joselito',
            },
            {
                tipo: 'coordinadores',
                empresa: 'negosur',
                titulo: 'Coordinadores - Negosur',
                countId: 'preNominaCoordNegosurCount',
                totalId: 'preNominaCoordNegosurTotal',
                filename: 'coordinadores_negosur',
            },
            {
                tipo: 'agentes_con_id',
                empresa: '',
                titulo: 'Agentes de ventas con ID de empleado',
                countId: 'preNominaAgentesConIdCount',
                totalId: null,
                filename: 'agentes_ventas_con_id_empleado',
            },
            {
                tipo: 'sin_id',
                empresa: '',
                titulo: 'Usuarios sin ID de empleado',
                countId: 'preNominaSinIdCount',
                totalId: null,
                filename: 'usuarios_sin_id_empleado',
            },
        ];
    }

    function getAdministrativePreNominaGroupLabel(value) {
        const group = normalizeAdministrativeGroup(value).toLowerCase();

        if (group.startsWith('1') && group.includes('gtes')) {
            return '1. Gtes. y Encarg.';
        }
        if (group.startsWith('2') && group.includes('monitoreo')) {
            return '2. Monitoreo';
        }
        if (group.startsWith('4') && group.includes('operadores')) {
            return '4. Operadores';
        }
        if (group.startsWith('5') && group.includes('servs')) {
            return '5. Servs. Tecnicos';
        }
        if (group.startsWith('6') && group.includes('seguridad')) {
            return '6. Seguridad';
        }

        return '';
    }

    function getAllAdministrativePreNominaRows() {
        const administrativos = administrativeRows.map((row, idx) => ({
            ...row,
            grupo: normalizeAdministrativeGroup(row.grupo),
            empresa: normalizeAdministrativeEmpresaLabel(row.empresa),
            __tipo: 'admin',
            __idx: idx,
        }));
        const operadores = operatorRows.map((row, idx) => ({
            ...row,
            grupo: normalizeAdministrativeGroup(row.grupo),
            empresa: normalizeAdministrativeEmpresaLabel(row.empresa),
            __tipo: 'operador',
            __idx: idx,
        }));

        return [...administrativos, ...operadores]
            .filter((row) => !isAdministrativeDesvinculadoExcluded(row))
            .map((row) => ({
                grupo: getAdministrativePreNominaGroupLabel(row.grupo),
                empleadoid: getEmpleadoIdKey(row.empleadoid),
                nombre: String(row.nombre ?? '').trim(),
                cedula: String(row.cedula ?? '').trim(),
                empresa: normalizeAdministrativeEmpresaLabel(row.empresa),
                monto: getAdministrativeDisplayAmount(row),
            }))
            .filter((row) => row.grupo !== '')
            .filter((row) => toIntegerAmount(row.monto) > 0);
    }

    function getPreNominaRows(tipo, empresa = '') {
        if (tipo === 'administrativos') {
            return getAllAdministrativePreNominaRows()
                .filter((row) => normalizePagoEmpresaGroupKey(row.empresa) === empresa);
        }

        if (tipo === 'coordinadores') {
            return getCoordinatorDisplayRows()
                .map((row) => ({
                    empleadoid: getEmpleadoIdKey(row.empleadoid),
                    nombre: String(row.nombre ?? '').trim(),
                    cedula: String(row.cedula ?? '').trim(),
                    empresa: normalizeAdministrativeEmpresaLabel(row.empresa),
                    agencias: toNumber(row.agencias_validas ?? row.agencias),
                    monto: getCoordinatorDisplayAmount(row),
                }))
                .filter((row) => normalizePagoEmpresaGroupKey(row.empresa) === empresa)
                .filter((row) => toIntegerAmount(row.monto) > 0);
        }

        if (tipo === 'agentes_con_id') {
            const coordinatorNamesByCedula = new Map();

            coordinatorRows.forEach((coordinator) => {
                const coordinatorName = String(coordinator.nombre ?? '').trim();

                getCoordinatorDetailUsers(coordinator).forEach((user) => {
                    const cedulaKey = getCedulaKey(user?.cedula);
                    if (!cedulaKey || !coordinatorName) {
                        return;
                    }

                    const names = coordinatorNamesByCedula.get(cedulaKey) || new Set();
                    names.add(coordinatorName);
                    coordinatorNamesByCedula.set(cedulaKey, names);
                });
            });

            return getPagoIncentivoExportRows()
                .filter((row) => hasEmpleadoId(row))
                .map((row) => ({
                    empleadoid: getEmpleadoIdKey(row.empleadoid),
                    cedula: String(row.cedula ?? '').trim(),
                    nombre: String(row.nombre ?? '').trim(),
                    empresa: normalizeEmpresaLabel(row.empresa),
                    coordinador: Array.from(coordinatorNamesByCedula.get(getCedulaKey(row.cedula)) || []).join(', ')
                        || 'Sin coordinador asignado',
                    monto: toIntegerAmount(row.nuevo_incentivo),
                }))
                .filter((row) => row.monto > 0);
        }

        return getPagoIncentivoExportRows()
            .filter((row) => !hasEmpleadoId(row))
            .map((row) => ({
                cedula: String(row.cedula ?? '').trim(),
                nombre: String(row.nombre ?? 'Actualizar en maestro de empleados').trim(),
                empresa: normalizeEmpresaLabel(row.empresa),
                terminal: String(row.ultima_terminal ?? '').trim(),
                agencia: String(row.ultima_agencia_nombre ?? '').trim(),
                monto: toIntegerAmount(row.nuevo_incentivo),
            }))
            .filter((row) => row.monto > 0);
    }

    function actualizarResumenPreNomina() {
        getPreNominaConfigurations().forEach((configuration) => {
            const rows = getPreNominaRows(configuration.tipo, configuration.empresa);
            const total = rows.reduce((sum, row) => sum + toIntegerAmount(row.monto), 0);
            const countElement = document.getElementById(configuration.countId);
            const totalElement = document.getElementById(configuration.totalId);

            if (countElement) countElement.textContent = rows.length.toLocaleString('en-US');
            if (totalElement) totalElement.textContent = formatMoney(total);
        });
    }

    function abrirModalPreNomina() {
        if (!cachedRows.length) {
            Swal.fire({
                title: 'Reporte pendiente',
                text: 'Primero debes generar el reporte para preparar la pre nómina.',
                icon: 'warning',
            });
            return;
        }

        actualizarResumenPreNomina();
        bootstrap.Modal.getOrCreateInstance(document.getElementById('modalPreNomina')).show();
    }

    function getPreNominaPdfTable(tipo, rows) {
        const header = (text, alignment = 'left') => ({ text, style: 'tableHeader', alignment });
        const amountCell = (amount, bold = false) => ({
            text: `RD$${formatMoney(amount)}`,
            alignment: 'right',
            bold,
        });

        if (tipo === 'administrativos') {
            return {
                widths: ['18%', '12%', '23%', '16%', '16%', '15%'],
                body: [
                    [header('Departamento'), header('Id empleado'), header('Nombre'), header('Cédula'), header('Empresa'), header('Monto', 'right')],
                    ...rows.map((row) => [
                        row.grupo || '',
                        row.empleadoid || 'Sin ID',
                        row.nombre || '',
                        row.cedula || '',
                        row.empresa || '',
                        amountCell(row.monto),
                    ]),
                ],
            };
        }

        if (tipo === 'coordinadores') {
            return {
                widths: ['13%', '25%', '16%', '18%', '13%', '15%'],
                body: [
                    [header('Id empleado'), header('Nombre'), header('Cédula'), header('Empresa'), header('Agencias', 'center'), header('Monto', 'right')],
                    ...rows.map((row) => [
                        row.empleadoid || 'Sin ID',
                        row.nombre || '',
                        row.cedula || '',
                        row.empresa || '',
                        { text: String(row.agencias), alignment: 'center' },
                        amountCell(row.monto),
                    ]),
                ],
            };
        }

        if (tipo === 'agentes_con_id') {
            return {
                widths: ['11%', '15%', '20%', '14%', '25%', '15%'],
                body: [
                    [header('Id empleado'), header('Cédula'), header('Nombre'), header('Empresa'), header('Coordinador'), header('Incentivo', 'center')],
                    ...rows.map((row) => [
                        row.empleadoid || '',
                        row.cedula || '',
                        row.nombre || '',
                        row.empresa || '',
                        row.coordinador || 'Sin coordinador asignado',
                        { text: 'Califica', alignment: 'center', bold: true },
                    ]),
                ],
            };
        }

        return {
            widths: ['15%', '24%', '15%', '12%', '19%', '15%'],
            body: [
                [header('Cédula'), header('Nombre'), header('Empresa'), header('Terminal'), header('Agencia'), header('Incentivo', 'center')],
                ...rows.map((row) => [
                    row.cedula || '',
                    row.nombre || '',
                    row.empresa || '',
                    row.terminal || '',
                    row.agencia || '',
                    { text: 'Califica', alignment: 'center', bold: true },
                ]),
            ],
        };
    }

    function generarPdfPreNomina(tipo, empresa = '') {
        if (typeof pdfMake === 'undefined') {
            Swal.fire({ title: 'Error', text: 'No se encontró la librería para generar PDF.', icon: 'error' });
            return;
        }

        const configuration = getPreNominaConfigurations()
            .find((item) => item.tipo === tipo && item.empresa === empresa);
        const rows = getPreNominaRows(tipo, empresa);

        if (!configuration || !rows.length) {
            Swal.fire({
                title: 'Sin datos',
                text: 'No hay registros con monto mayor a cero para este PDF.',
                icon: 'warning',
            });
            return;
        }

        const table = getPreNominaPdfTable(tipo, rows);
        const total = rows.reduce((sum, row) => sum + toIntegerAmount(row.monto), 0);
        const fechaInicio = document.getElementById('ni_fecha_ini')?.value || '';
        const fechaFin = document.getElementById('ni_fecha_fin')?.value || '';
        const sistema = document.getElementById('ni_sistema')?.value || 'Todos';
        const totalColumns = table.widths.length;
        if (['agentes_con_id', 'sin_id'].includes(tipo)) {
            table.body.push([
                {
                    text: `TOTAL REGISTROS: ${rows.length.toLocaleString('en-US')}`,
                    colSpan: totalColumns,
                    alignment: 'right',
                    bold: true,
                    fillColor: '#e2e8f0',
                },
                ...Array.from({ length: totalColumns - 1 }, () => ({})),
            ]);
        } else {
            table.body.push([
                { text: 'TOTAL', colSpan: totalColumns - 1, alignment: 'right', bold: true, fillColor: '#e2e8f0' },
                ...Array.from({ length: totalColumns - 2 }, () => ({})),
                { text: `RD$${formatMoney(total)}`, alignment: 'right', bold: true, fillColor: '#e2e8f0' },
            ]);
        }

        const docDefinition = {
            pageSize: 'LETTER',
            pageOrientation: 'landscape',
            pageMargins: [28, 32, 28, 32],
            content: [
                { text: 'PRE NÓMINA DE INCENTIVOS', style: 'title' },
                { text: configuration.titulo, style: 'subtitle' },
                {
                    columns: [
                        { text: `Período: ${formatDateDisplay(fechaInicio)} al ${formatDateDisplay(fechaFin)}` },
                        { text: `Sistema: ${sistema}`, alignment: 'center' },
                        { text: `Registros: ${rows.length.toLocaleString('en-US')}`, alignment: 'right' },
                    ],
                    margin: [0, 6, 0, 12],
                    fontSize: 9,
                    color: '#475569',
                },
                {
                    table: {
                        headerRows: 1,
                        widths: table.widths,
                        body: table.body,
                    },
                    layout: {
                        fillColor: (rowIndex) => rowIndex > 0 && rowIndex % 2 === 0 ? '#f8fafc' : null,
                        hLineColor: () => '#cbd5e1',
                        vLineColor: () => '#cbd5e1',
                    },
                    fontSize: 8,
                },
            ],
            footer: (currentPage, pageCount) => ({
                text: `Página ${currentPage} de ${pageCount}`,
                alignment: 'center',
                margin: [0, 10, 0, 0],
                fontSize: 8,
                color: '#64748b',
            }),
            styles: {
                title: { fontSize: 17, bold: true, color: '#0f172a', alignment: 'center' },
                subtitle: { fontSize: 12, bold: true, color: '#334155', alignment: 'center', margin: [0, 3, 0, 3] },
                tableHeader: { bold: true, color: '#ffffff', fillColor: '#1e3a8a', fontSize: 8 },
            },
            defaultStyle: { fontSize: 9 },
        };

        pdfMake.createPdf(docDefinition).download(
            `pre_nomina_${configuration.filename}_${fechaFin || 'incentivos'}.pdf`
        );
    }

    function getPreNominaExcelData(tipo, rows) {
        const total = rows.reduce((sum, row) => sum + toIntegerAmount(row.monto), 0);

        if (tipo === 'administrativos') {
            return {
                headers: ['Departamento', 'Id empleado', 'Nombre', 'Cédula', 'Empresa', 'Monto'],
                rows: [
                    ...rows.map((row) => [
                        row.grupo || '',
                        row.empleadoid || 'Sin ID',
                        row.nombre || '',
                        row.cedula || '',
                        row.empresa || '',
                        String(toIntegerAmount(row.monto)),
                    ]),
                    ['', '', '', '', 'TOTAL', String(total)],
                ],
            };
        }

        if (tipo === 'coordinadores') {
            return {
                headers: ['Id empleado', 'Nombre', 'Cédula', 'Empresa', 'Agencias', 'Monto'],
                rows: [
                    ...rows.map((row) => [
                        row.empleadoid || 'Sin ID',
                        row.nombre || '',
                        row.cedula || '',
                        row.empresa || '',
                        String(row.agencias),
                        String(toIntegerAmount(row.monto)),
                    ]),
                    ['', '', '', '', 'TOTAL', String(total)],
                ],
            };
        }

        if (tipo === 'agentes_con_id') {
            return {
                headers: ['Id empleado', 'Cédula', 'Nombre', 'Empresa', 'Coordinador', 'Incentivo'],
                rows: [
                    ...rows.map((row) => [
                        row.empleadoid || '',
                        row.cedula || '',
                        row.nombre || '',
                        row.empresa || '',
                        row.coordinador || 'Sin coordinador asignado',
                        'Califica',
                    ]),
                    ['', '', '', '', 'TOTAL REGISTROS', String(rows.length)],
                ],
            };
        }

        return {
            headers: ['Cédula', 'Nombre', 'Empresa', 'Terminal', 'Agencia', 'Incentivo'],
            rows: [
                ...rows.map((row) => [
                    row.cedula || '',
                    row.nombre || '',
                    row.empresa || '',
                    row.terminal || '',
                    row.agencia || '',
                    'Califica',
                ]),
                ['', '', '', '', 'TOTAL REGISTROS', String(rows.length)],
            ],
        };
    }

    function generarExcelPreNomina(tipo, empresa = '') {
        const configuration = getPreNominaConfigurations()
            .find((item) => item.tipo === tipo && item.empresa === empresa);
        const rows = getPreNominaRows(tipo, empresa);

        if (!configuration || !rows.length) {
            Swal.fire({
                title: 'Sin datos',
                text: 'No hay registros disponibles para este Excel.',
                icon: 'warning',
            });
            return;
        }

        const excelData = getPreNominaExcelData(tipo, rows);
        const fechaFin = document.getElementById('ni_fecha_fin')?.value || 'incentivos';
        const filename = `pre_nomina_${configuration.filename}_${fechaFin}`;

        if (typeof JSZip === 'undefined') {
            downloadBlob(`${filename}.xls`, buildExcelHtmlBlob(excelData.headers, excelData.rows));
            return;
        }

        buildXlsxWorkbookBlob([{
            name: configuration.titulo,
            headers: excelData.headers,
            rows: excelData.rows,
        }])
            .then((blob) => downloadBlob(`${filename}.xlsx`, blob))
            .catch((error) => {
                Swal.fire({
                    title: 'Error',
                    text: error?.message || 'No se pudo generar el archivo Excel.',
                    icon: 'error',
                });
            });
    }

    function downloadBlob(filename, blob) {
        const url = URL.createObjectURL(blob);
        const link = document.createElement('a');
        link.href = url;
        link.download = filename;
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
        URL.revokeObjectURL(url);
    }

    function generarTxtPagoIncentivo(options = {}) {
        const data = getPagoIncentivoExportData(options);
        if (!data) {
            return;
        }

        generarTxtPagoDesdeData(data, 'pago_incentivo');
    }

    function generarTxtPagoDesdeData(data, filenamePrefix) {
        const lines = [
            data.headers.join(','),
            ...data.rows.map((row) => row.join(',')),
        ];
        const blob = new Blob(['\ufeff' + lines.join('\r\n')], { type: 'text/plain;charset=utf-8;' });
        downloadBlob(`${filenamePrefix}_${data.fechaFin}${data.suffix}.txt`, blob);
    }

    function generarTxtPagoAdministrativo() {
        const data = getAdministrativosPagoExportData();
        if (!data) {
            return;
        }

        generarTxtPagoDesdeData(data, 'pago_incentivo');
    }

    function generarTxtPagoCoordinadores() {
        const data = getCoordinadoresPagoExportData();
        if (!data) {
            return;
        }

        generarTxtPagoDesdeData(data, 'pago_incentivo');
    }

    function generarTxtPagoAdmiCoor() {
        const data = getAdmiCoorPagoExportData();
        if (!data) {
            return;
        }

        generarTxtPagoDesdeData(data, 'pago_incentivo');
    }

    function escapeXml(value) {
        return String(value ?? '')
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&apos;');
    }

    function excelColumnName(index) {
        let name = '';
        let number = index + 1;
        while (number > 0) {
            const remainder = (number - 1) % 26;
            name = String.fromCharCode(65 + remainder) + name;
            number = Math.floor((number - 1) / 26);
        }
        return name;
    }

    function normalizeWorksheetName(name, index, usedNames) {
        const fallback = `Hoja ${index + 1}`;
        const cleanName = String(name || fallback)
            .replace(/[\[\]\*\/\\\?:]/g, ' ')
            .replace(/\s+/g, ' ')
            .trim() || fallback;
        let baseName = cleanName.slice(0, 31);
        let finalName = baseName;
        let count = 2;

        while (usedNames.has(finalName.toLowerCase())) {
            const suffix = ` ${count}`;
            finalName = `${baseName.slice(0, 31 - suffix.length)}${suffix}`;
            count += 1;
        }

        usedNames.add(finalName.toLowerCase());
        return finalName;
    }

    function buildWorksheetXml(headers, rows) {
        const allRows = [headers, ...rows];
        const sheetRows = allRows.map((row, rowIndex) => {
            const rowNumber = rowIndex + 1;
            const cells = row.map((value, columnIndex) => {
                const cellRef = `${excelColumnName(columnIndex)}${rowNumber}`;
                return `<c r="${cellRef}" t="inlineStr"><is><t>${escapeXml(value)}</t></is></c>`;
            }).join('');

            return `<row r="${rowNumber}">${cells}</row>`;
        }).join('');

        return `<worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main"><sheetData>${sheetRows}</sheetData></worksheet>`;
    }

    function buildXlsxWorkbookBlob(sheets) {
        const zip = new JSZip();
        const usedNames = new Set();
        const workbookSheets = (Array.isArray(sheets) ? sheets : [])
            .filter((sheet) => Array.isArray(sheet?.headers) && Array.isArray(sheet?.rows))
            .map((sheet, index) => ({
                ...sheet,
                name: normalizeWorksheetName(sheet.name, index, usedNames),
            }));

        const worksheetOverrides = workbookSheets.map((sheet, index) => (
            `<Override PartName="/xl/worksheets/sheet${index + 1}.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/>`
        )).join('');
        const workbookSheetNodes = workbookSheets.map((sheet, index) => (
            `<sheet name="${escapeXml(sheet.name)}" sheetId="${index + 1}" r:id="rId${index + 1}"/>`
        )).join('');
        const workbookRelationships = workbookSheets.map((sheet, index) => (
            `<Relationship Id="rId${index + 1}" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet${index + 1}.xml"/>`
        )).join('');

        zip.file('[Content_Types].xml', `${XML_DECL}
<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types"><Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/><Default Extension="xml" ContentType="application/xml"/><Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/>${worksheetOverrides}</Types>`);
        zip.folder('_rels').file('.rels', `${XML_DECL}
<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships"><Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/></Relationships>`);
        zip.folder('xl').file('workbook.xml', `${XML_DECL}
<workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships"><sheets>${workbookSheetNodes}</sheets></workbook>`);
        zip.folder('xl').folder('_rels').file('workbook.xml.rels', `${XML_DECL}
<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">${workbookRelationships}</Relationships>`);
        workbookSheets.forEach((sheet, index) => {
            zip.folder('xl').folder('worksheets').file(`sheet${index + 1}.xml`, `${XML_DECL}
${buildWorksheetXml(sheet.headers, sheet.rows)}`);
        });

        return zip.generateAsync({ type: 'blob', mimeType: 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' });
    }

    function buildXlsxBlob(headers, rows) {
        return buildXlsxWorkbookBlob([{ name: 'Pago incentivo', headers, rows }]);
    }

    function buildExcelHtmlBlob(headers, rows) {
        const tableRows = [headers, ...rows].map((row) => (
            `<tr>${row.map((value) => `<td>${escapeHtml(value)}</td>`).join('')}</tr>`
        )).join('');
        const html = `
            <html>
                <head>
                    <meta charset="UTF-8">
                <\/head>
                <body>
                    <table>${tableRows}</table>
                <\/body>
            <\/html>
        `;

        return new Blob(['\ufeff' + html], { type: 'application/vnd.ms-excel;charset=utf-8;' });
    }

    function buildExcelHtmlWorkbookBlob(sheets) {
        const tableBlocks = sheets.map((sheet) => {
            const tableRows = [sheet.headers, ...sheet.rows].map((row) => (
                `<tr>${row.map((value) => `<td>${escapeHtml(value)}</td>`).join('')}</tr>`
            )).join('');

            return `<h3>${escapeHtml(sheet.name)}</h3><table>${tableRows}</table><br>`;
        }).join('');
        const html = `
            <html>
                <head>
                    <meta charset="UTF-8">
                <\/head>
                <body>
                    ${tableBlocks}
                <\/body>
            <\/html>
        `;

        return new Blob(['\ufeff' + html], { type: 'application/vnd.ms-excel;charset=utf-8;' });
    }

    function generarXlsxPagoIncentivo(options = {}) {
        const data = getPagoIncentivoExportData(options);
        if (!data) {
            return;
        }

        generarXlsxPagoDesdeData(data, 'pago_incentivo');
    }

    function generarXlsxPagoDesdeData(data, filenamePrefix) {
        if (typeof JSZip === 'undefined') {
            const blob = buildExcelHtmlBlob(data.headers, data.rows);
            downloadBlob(`${filenamePrefix}_${data.fechaFin}${data.suffix}.xls`, blob);
            return;
        }

        buildXlsxBlob(data.headers, data.rows).then((blob) => {
            downloadBlob(`${filenamePrefix}_${data.fechaFin}${data.suffix}.xlsx`, blob);
        });
    }

    function generarXlsxPagoAdmiCoor(options = {}) {
        const data = getAdmiCoorPagoExportWorkbookData(options);
        if (!data) {
            return;
        }

        if (typeof JSZip === 'undefined') {
            const blob = buildExcelHtmlWorkbookBlob(data.sheets);
            downloadBlob(`pago_incentivo_${data.fechaFin}${data.suffix}.xls`, blob);
            return;
        }

        buildXlsxWorkbookBlob(data.sheets).then((blob) => {
            downloadBlob(`pago_incentivo_${data.fechaFin}${data.suffix}.xlsx`, blob);
        });
    }

    function seleccionarDescargaExcelPago() {
        if (!cachedRows.length) {
            Swal.fire({ title: 'Informacion', text: 'Primero debes generar el reporte.', icon: 'warning' });
            return;
        }

        const summary = getEmpleadoIdPagoSummary();
        const administrativosPagoRows = getAdministrativeDisplayRows()
            .map((row) => toIntegerAmount(getAdministrativeDisplayAmount(row)))
            .filter((amount) => amount > 0);
        const coordinadoresPagoRows = getCoordinatorDisplayRows()
            .map((row) => toIntegerAmount(getCoordinatorDisplayAmount(row)))
            .filter((amount) => amount > 0);
        const administrativosTotal = administrativosPagoRows.reduce((sum, amount) => sum + amount, 0);
        const coordinadoresTotal = coordinadoresPagoRows.reduce((sum, amount) => sum + amount, 0);

        if (summary.totalMonto <= 0 && administrativosTotal <= 0 && coordinadoresTotal <= 0) {
            Swal.fire({ title: 'Sin datos', text: 'No hay importes mayores a cero para generar archivos de pago.', icon: 'warning' });
            return;
        }

        Swal.fire({
            title: 'Generar archivo de pago',
            html: `
                <div class="text-start small">
                    <div><strong>Con IdEmpleado:</strong> ${summary.usuariosConId.toLocaleString('en-US')} usuarios | ${formatMoney(summary.montoConId)}</div>
                    <div><strong>Sin IdEmpleado:</strong> ${summary.usuariosSinId.toLocaleString('en-US')} usuarios | ${formatMoney(summary.montoSinId)}</div>
                    <div><strong>Administrativo:</strong> ${administrativosPagoRows.length.toLocaleString('en-US')} registros | ${formatMoney(administrativosTotal)}</div>
                    <div><strong>Coordinadores:</strong> ${coordinadoresPagoRows.length.toLocaleString('en-US')} registros | ${formatMoney(coordinadoresTotal)}</div>
                    <div style="display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:10px;margin-top:16px;">
                        <div>
                            <button type="button" class="btn btn-dark btn-lg fw-bold w-100 py-3" id="btnPagoExcelCompleto">Excel pago todos</button>
                        </div>
                        <div>
                            <button type="button" class="btn btn-outline-dark btn-lg fw-bold w-100 py-3" id="btnPagoExcelSinId">Excel excluir sin ID</button>
                        </div>
                        <div style="grid-column:1 / -1;">
                            <button type="button" class="btn btn-primary btn-lg fw-bold w-100 py-3" id="btnPagoExcelAdmiCoor">admi-coor</button>
                        </div>
                        <div style="grid-column:1 / -1;">
                            <button type="button" class="btn btn-info btn-lg fw-bold w-100 py-3" id="btnPagoExcelAdmiCoorPorEmpresa">admi-coor por empresa (2 hojas)</button>
                        </div>
                    </div>
                </div>
            `,
            icon: summary.usuariosSinId > 0 ? 'warning' : 'info',
            showCancelButton: true,
            cancelButtonText: 'Cancelar',
            showConfirmButton: false,
            didOpen: () => {
                document.getElementById('btnPagoExcelCompleto')?.addEventListener('click', () => {
                    Swal.close();
                    generarXlsxPagoIncentivo({ excludeSinEmpleadoId: false });
                });
                document.getElementById('btnPagoExcelSinId')?.addEventListener('click', () => {
                    Swal.close();
                    generarXlsxPagoIncentivo({ excludeSinEmpleadoId: true });
                });
                document.getElementById('btnPagoExcelAdmiCoor')?.addEventListener('click', () => {
                    Swal.close();
                    generarXlsxPagoAdmiCoor();
                });
                document.getElementById('btnPagoExcelAdmiCoorPorEmpresa')?.addEventListener('click', () => {
                    Swal.close();
                    generarXlsxPagoAdmiCoor({ combineByCompany: true });
                });
            },
        });
    }

    function exportAdministrativosExcel() {
        const rows = getAdministrativeDisplayRows().map((row) => [
            row.grupo,
            row.nombre,
            row.empleadoid || '',
            row.empresa,
            formatAdministrativeConfigValue(row),
            formatMoney(getAdministrativeDisplayAmount(row)),
        ]);

        exportRowsToExcelCsv(
            'incentivos_administrativos_validacion.csv',
            ['Grupo', 'Nombre', 'IdEmpleado', 'Empresa', '% Total / Monto fijo', 'Monto'],
            rows
        );
    }

    function getValidacionGerencialRows() {
        const departmentOrder = {
            '1. Gtes. Y Encarg.': 1,
            '2. Monitoreo': 2,
            '4. Operadores': 3,
            '5. Servs. Tecnicos': 4,
            '6. Seguridad': 5,
        };

        return [
            ...administrativeRows.map((row) => ({
                ...row,
                grupo: normalizeAdministrativeGroup(row.grupo),
                empresa: normalizeAdministrativeEmpresaLabel(row.empresa),
                __tipo: 'admin',
            })),
            ...operatorRows.map((row) => ({
                ...row,
                grupo: normalizeAdministrativeGroup(row.grupo),
                empresa: normalizeAdministrativeEmpresaLabel(row.empresa),
                __tipo: 'operador',
            })),
        ]
            .filter((row) => getAdministrativeCategoryKeyByGroup(row.grupo) !== null)
            .sort((a, b) => {
                const groupCompare = (departmentOrder[a.grupo] || 99) - (departmentOrder[b.grupo] || 99);
                if (groupCompare !== 0) return groupCompare;

                const empresaCompare = String(a.empresa || '').localeCompare(String(b.empresa || ''));
                if (empresaCompare !== 0) return empresaCompare;

                return String(a.nombre || '').localeCompare(String(b.nombre || ''));
            });
    }

    function formatValidacionGerencialConfigValue(row) {
        return isFixedAdministrativeGroup(row?.grupo)
            ? `RD$ ${formatMoney(row?.pct)}`
            : `${formatAdministrativePct(row?.pct)}%`;
    }

    function generarPdfValidacionGerencial() {
        if (!cachedRows.length) {
            Swal.fire({ title: 'Informacion', text: 'Primero debes generar el reporte.', icon: 'warning' });
            return;
        }

        if (typeof pdfMake === 'undefined') {
            Swal.fire({ title: 'Error', text: 'No se encontro la libreria para generar PDF.', icon: 'error' });
            return;
        }

        const rows = getValidacionGerencialRows();
        if (!rows.length) {
            Swal.fire({ title: 'Sin datos', text: 'No hay categorias administrativas para validar.', icon: 'warning' });
            return;
        }

        Swal.fire({
            title: 'Generando datos',
            text: 'Preparando validacion gerencial por departamento...',
            icon: 'info',
            allowOutsideClick: false,
            showConfirmButton: false,
            didOpen: () => Swal.showLoading()
        });

        setTimeout(() => {
            try {
                const fechaIni = document.getElementById('ni_fecha_ini')?.value || '';
                const fechaFin = document.getElementById('ni_fecha_fin')?.value || '';
                const sistema = document.getElementById('ni_sistema')?.value || '';
                const tipoPago = document.getElementById('ni_tipo_pago')?.selectedOptions?.[0]?.textContent || '';
                const total = rows.reduce((sum, row) => sum + getAdministrativeAmountByRow(row), 0);
                const totalRowFill = '#cfe2ff';
                const totalRowText = '#0f172a';

                const tableBody = [
                    [
                        { text: 'Departamento', style: 'tableHeader' },
                        { text: 'Empresa', style: 'tableHeader' },
                        { text: 'Nombre', style: 'tableHeader' },
                        { text: '% / Monto fijo', style: 'tableHeader', alignment: 'right' },
                        { text: 'Monto a pagar', style: 'tableHeader', alignment: 'right' },
                    ],
                    ...rows.map((row) => [
                        row.grupo || '',
                        row.empresa || '',
                        row.nombre || '',
                        { text: formatValidacionGerencialConfigValue(row), alignment: 'right' },
                        { text: formatMoney(getAdministrativeAmountByRow(row)), alignment: 'right' },
                    ]),
                    [
                        { text: 'Total', colSpan: 4, alignment: 'right', bold: true, fillColor: totalRowFill, color: totalRowText },
                        { text: '', fillColor: totalRowFill },
                        { text: '', fillColor: totalRowFill },
                        { text: '', fillColor: totalRowFill },
                        { text: formatMoney(total), alignment: 'right', bold: true, fillColor: totalRowFill, color: totalRowText },
                    ],
                ];

                const docDefinition = {
                    pageSize: 'LETTER',
                    pageOrientation: 'landscape',
                    pageMargins: [28, 34, 28, 40],
                    footer: function (currentPage, pageCount) {
                        return {
                            text: `Pagina ${currentPage} de ${pageCount}`,
                            alignment: 'right',
                            margin: [0, 0, 28, 0],
                            fontSize: 8,
                            color: '#6c757d',
                        };
                    },
                    content: [
                        { text: 'Validacion Gerencial', style: 'title' },
                        { text: 'Categorias administrativas sin agentes de venta', style: 'subtitle' },
                        {
                            columns: [
                                { text: `Periodo: ${formatDateDisplay(fechaIni)} al ${formatDateDisplay(fechaFin)}` },
                                { text: `Sistema: ${sistema || 'Todos'}` },
                                { text: `Tipo de pago: ${tipoPago}` },
                            ],
                            margin: [0, 8, 0, 8],
                            fontSize: 9,
                        },
                        {
                            columns: [
                                { text: `Total administrativo: ${formatMoney(total)}`, bold: true },
                                { text: `Registros: ${rows.length}`, alignment: 'center' },
                                { text: `Generado: ${new Date().toLocaleString('es-DO')}`, alignment: 'right' },
                            ],
                            margin: [0, 0, 0, 12],
                            fontSize: 9,
                        },
                        {
                            table: {
                                headerRows: 1,
                                dontBreakRows: true,
                                keepWithHeaderRows: 1,
                                widths: ['18%', '13%', '*', '14%', '14%'],
                                body: tableBody,
                            },
                            layout: {
                                fillColor: function (rowIndex) {
                                    if (rowIndex === 0) return '#eef2f7';
                                    if (rowIndex === tableBody.length - 1) return totalRowFill;
                                    return rowIndex % 2 === 0 ? '#fbfcfd' : null;
                                },
                                hLineColor: function () { return '#d9dee3'; },
                                vLineColor: function () { return '#d9dee3'; },
                            },
                        },
                    ],
                    styles: {
                        title: { fontSize: 16, bold: true },
                        subtitle: { fontSize: 10, color: '#495057' },
                        tableHeader: { bold: true, fontSize: 9, color: '#212529' },
                    },
                    defaultStyle: {
                        fontSize: 8,
                    },
                };

                const pdf = pdfMake.createPdf(docDefinition);
                Swal.close();
                pdf.download(`validacion_gerencial_${fechaFin || 'incentivos'}.pdf`);
            } catch (error) {
                console.error('Error generando validacion gerencial:', error);
                Swal.fire({
                    title: 'Error',
                    text: error?.message || String(error) || 'No se pudo generar la validacion gerencial.',
                    icon: 'error'
                });
            }
        }, 150);
    }

    function generarPdfDetalleCalendario() {
        if (!cachedRows.length) {
            Swal.fire({ title: 'Informacion', text: 'Primero debes generar el reporte.', icon: 'warning' });
            return;
        }

        if (typeof pdfMake === 'undefined') {
            Swal.fire({ title: 'Error', text: 'No se encontro la libreria para generar PDF.', icon: 'error' });
            return;
        }

        const detail = cachedMeta?.detalle_calendario_tipos_pago || {};
        const paymentTypes = ['tramos_60', 'tramos_70', 'tramos_80'];
        const fechaIni = document.getElementById('ni_fecha_ini')?.value || '';
        const fechaFin = document.getElementById('ni_fecha_fin')?.value || '';
        const sistema = document.getElementById('ni_sistema')?.value || 'Todos';

        const content = [
            { text: 'Detalle del Calendario de Pagos', style: 'title' },
            { text: 'Agencias calculadas por tipo de pago y rango efectivo', style: 'subtitle' },
            {
                columns: [
                    { text: `Periodo: ${formatDateDisplay(fechaIni)} al ${formatDateDisplay(fechaFin)}` },
                    { text: `Sistema: ${sistema}`, alignment: 'center' },
                    { text: `Generado: ${new Date().toLocaleString('es-DO')}`, alignment: 'right' },
                ],
                margin: [0, 10, 0, 12],
                fontSize: 9,
            },
        ];

        paymentTypes.forEach((paymentType) => {
            const typeDetail = detail?.[paymentType] || {};
            const ranges = Array.isArray(typeDetail?.rangos) ? typeDetail.rangos : [];
            const totalAgencies = Number(typeDetail?.agencias || 0);
            const label = paymentType.replace('tramos_', '');
            const tableBody = [
                [
                    { text: 'Rango de aplicacion', style: 'tableHeader' },
                    { text: 'Agencias', style: 'tableHeader', alignment: 'right' },
                ],
                ...(ranges.length ? ranges.map((range) => [
                    range.desde === range.hasta
                        ? formatDateDisplay(range.desde)
                        : `${formatDateDisplay(range.desde)} al ${formatDateDisplay(range.hasta)}`,
                    { text: Number(range.agencias || 0).toLocaleString('en-US'), alignment: 'right' },
                ]) : [[
                    { text: 'Sin agencias calculadas en este tipo', color: '#6c757d' },
                    { text: '0', alignment: 'right', color: '#6c757d' },
                ]]),
            ];

            content.push(
                {
                    text: `Pago ${label}: ${totalAgencies.toLocaleString('en-US')} agencias calculadas`,
                    style: 'sectionTitle',
                    margin: [0, 8, 0, 5],
                },
                {
                    table: {
                        headerRows: 1,
                        widths: ['*', '25%'],
                        body: tableBody,
                    },
                    layout: {
                        fillColor: function (rowIndex) {
                            if (rowIndex === 0) return '#eef2f7';
                            return rowIndex % 2 === 0 ? '#fbfcfd' : null;
                        },
                        hLineColor: function () { return '#d9dee3'; },
                        vLineColor: function () { return '#d9dee3'; },
                    },
                    margin: [0, 0, 0, 8],
                }
            );
        });

        content.push({
            text: 'Los totales representan terminales unicas con ventas calculadas. Una terminal puede aparecer en mas de un tipo cuando su configuracion cambio durante el periodo.',
            fontSize: 8,
            color: '#495057',
            margin: [0, 10, 0, 0],
        });

        const docDefinition = {
            pageSize: 'LETTER',
            pageMargins: [36, 36, 36, 42],
            footer: function (currentPage, pageCount) {
                return {
                    text: `Pagina ${currentPage} de ${pageCount}`,
                    alignment: 'right',
                    margin: [0, 0, 36, 0],
                    fontSize: 8,
                    color: '#6c757d',
                };
            },
            content,
            styles: {
                title: { fontSize: 16, bold: true, color: '#212529' },
                subtitle: { fontSize: 10, color: '#495057' },
                sectionTitle: { fontSize: 11, bold: true, color: '#1f2937' },
                tableHeader: { bold: true, fontSize: 9, color: '#212529' },
            },
            defaultStyle: { fontSize: 9 },
        };

        pdfMake.createPdf(docDefinition).download(`detalle_calendario_incentivos_administrativos_${fechaFin || 'incentivos'}.pdf`);
    }

    function seleccionarPdfInformeGerencialProceso() {
        if (!cachedRows.length) {
            Swal.fire({ title: 'Informacion', text: 'Primero debes generar el reporte.', icon: 'warning' });
            return;
        }

        Swal.fire({
            title: 'Informe Gerencial PDF',
            html: `
                <div class="text-start small">
                    <div class="mb-3 text-muted">Selecciona el alcance que quieres generar.</div>
                    <div style="display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:10px;">
                        <button type="button" class="btn btn-dark btn-lg fw-bold py-3" id="btnInformeCompleto">Completo</button>
                        <button type="button" class="btn btn-primary btn-lg fw-bold py-3" id="btnInformeJoselito">Joselito</button>
                        <button type="button" class="btn btn-info btn-lg fw-bold py-3" id="btnInformeNegosur">Negosur</button>
                    </div>
                </div>
            `,
            icon: 'info',
            showCancelButton: true,
            cancelButtonText: 'Cancelar',
            showConfirmButton: false,
            didOpen: () => {
                document.getElementById('btnInformeCompleto')?.addEventListener('click', () => {
                    Swal.close();
                    generarPdfInformeGerencialProceso({ empresaKey: 'todos' });
                });
                document.getElementById('btnInformeJoselito')?.addEventListener('click', () => {
                    Swal.close();
                    generarPdfInformeGerencialProceso({ empresaKey: 'joselito' });
                });
                document.getElementById('btnInformeNegosur')?.addEventListener('click', () => {
                    Swal.close();
                    generarPdfInformeGerencialProceso({ empresaKey: 'negosur' });
                });
            },
        });
    }

    function generarPdfInformeGerencialProceso(options = {}) {
        if (!cachedRows.length) {
            Swal.fire({ title: 'Informacion', text: 'Primero debes generar el reporte.', icon: 'warning' });
            return;
        }

        if (typeof pdfMake === 'undefined') {
            Swal.fire({ title: 'Error', text: 'No se encontro la libreria para generar PDF.', icon: 'error' });
            return;
        }

        Swal.fire({
            title: 'Generando informe',
            text: 'Preparando informe gerencial del proceso...',
            icon: 'info',
            allowOutsideClick: false,
            showConfirmButton: false,
            didOpen: () => Swal.showLoading()
        });

        setTimeout(() => {
            try {
                const fechaIni = document.getElementById('ni_fecha_ini')?.value || '';
                const fechaFin = document.getElementById('ni_fecha_fin')?.value || '';
                const sistema = document.getElementById('ni_sistema')?.value || 'Todos';
                const tipoPago = document.getElementById('ni_tipo_pago')?.selectedOptions?.[0]?.textContent || '';
                const modoCalculo = cachedMeta?.modo_calculo_label || document.getElementById('ni_modo_calculo')?.selectedOptions?.[0]?.textContent || '';
                const informeEmpresaKey = options?.empresaKey || 'todos';
                const informeEmpresaLabel = getInformeEmpresaLabel(informeEmpresaKey);
                const filterInformeEmpresa = (row) => informeEmpresaKey === 'todos'
                    || normalizeInformeEmpresaKey(row?.empresa) === informeEmpresaKey;
                const grossRowsAll = getBaseFilteredRows({ includeEmpresaFilter: false });
                const processedRowsAll = Array.isArray(currentFilteredRows) ? currentFilteredRows : [];
                const grossRows = grossRowsAll.filter(filterInformeEmpresa);
                const processedRows = processedRowsAll.filter(filterInformeEmpresa);
                const totalIncentivoBrutoGlobal = grossRowsAll.reduce((sum, row) => sum + toIntegerAmount(row?.nuevo_incentivo), 0);
                const informeShare = informeEmpresaKey === 'todos'
                    ? 1
                    : (totalIncentivoBrutoGlobal > 0
                        ? grossRows.reduce((sum, row) => sum + toIntegerAmount(row?.nuevo_incentivo), 0) / totalIncentivoBrutoGlobal
                        : 0);

                if (!grossRows.length) {
                    Swal.close();
                    Swal.fire({ title: 'Sin datos', text: `No hay registros para ${informeEmpresaLabel}.`, icon: 'warning' });
                    return;
                }

                const totalVendidoBruto = grossRows.reduce((sum, row) => sum + toIntegerAmount(row?.ventas_mes_actual), 0);
                const totalIncentivoBruto = grossRows.reduce((sum, row) => sum + toIntegerAmount(row?.nuevo_incentivo), 0);
                const totalIncentivoProcesado = processedRows.reduce((sum, row) => sum + toIntegerAmount(row?.nuevo_incentivo), 0);
                const empleadoIdSummary = getEmpleadoIdPagoSummary(processedRows);
                const totalDiezPorciento = toIntegerAmount(currentDistributionBase * informeShare);
                const montoAdministrativo = toIntegerAmount(currentAdministrativePoolBase * informeShare);
                const montoCoordinador = toIntegerAmount(currentCoordinatorBase * informeShare);
                const scaleExcluded = (value) => toIntegerAmount(toIntegerAmount(value) * informeShare);
                const totalDeduccionesDetectadas = toIntegerAmount(
                    scaleExcluded(currentExcludedApplication.faltantesDisponible)
                    + scaleExcluded(currentExcludedApplication.desvinculadosDisponible)
                    + scaleExcluded(currentExcludedApplication.coordinadoresDisponible)
                );
                const totalRebajadoInforme = scaleExcluded(currentExcludedApplication.totalRebajado);
                const totalFinalPagar = toIntegerAmount(totalIncentivoBruto + totalDiezPorciento - totalRebajadoInforme);
                const resumenCumplimiento = getUsuariosCumplimientoSummary(grossRows);
                const terminalesExcluidas = getExcludedTerminalesArray();
                const usuariosPorActualizar = getUsuariosPorActualizarRows(processedRows).length;
                const agenciasSinEmpresa = getAgenciasSinEmpresaRows(processedRows).length;
                const administrativosTotal = toIntegerAmount(montoAdministrativo);
                const operadoresSeguridadTotal = toIntegerAmount(currentFixedBagTopUp * informeShare);
                const montoRetencionCoordinadores = scaleExcluded(currentExcludedApplication.coordinadoresDisponible);
                const coordinadoresTotal = Math.max(toIntegerAmount(montoCoordinador - montoRetencionCoordinadores), 0);
                const distribucionTotal = toIntegerAmount(administrativosTotal + coordinadoresTotal + operadoresSeguridadTotal);
                const agenciasPorTipoPago = ['tramos_60', 'tramos_70', 'tramos_80'].map((paymentType) => {
                    const distribution = cachedMeta?.distribucion_tipos_pago?.[paymentType] || {};
                    const agenciesByCompany = distribution?.agencias_por_empresa || {};
                    const agencies = informeEmpresaKey === 'todos'
                        ? Number(distribution?.agencias || 0)
                        : Object.entries(agenciesByCompany).reduce((total, [company, count]) => (
                            normalizeInformeEmpresaKey(company) === informeEmpresaKey
                                ? total + Number(count || 0)
                                : total
                        ), 0);

                    return {
                        label: paymentType.replace('tramos_', ''),
                        agencies,
                    };
                });

                const moneyCell = (value, bold = false) => ({
                    text: formatMoney(value),
                    alignment: 'right',
                    bold,
                });
                const numberCell = (value, bold = false) => ({
                    text: Number(value || 0).toLocaleString('en-US'),
                    alignment: 'right',
                    bold,
                });
                const totalRowFill = '#cfe2ff';
                const totalRowText = '#0f172a';
                const warningRowFill = '#fff3cd';
                const warningRowText = '#7a4d00';
                const paymentRowFill = '#d1e7dd';
                const paymentRowText = '#0f5132';
                const totalRow = (row) => row.map((cell, index) => {
                    if (cell && typeof cell === 'object' && !Array.isArray(cell)) {
                        return {
                            ...cell,
                            bold: true,
                            fillColor: totalRowFill,
                            color: totalRowText,
                        };
                    }

                    return {
                        text: String(cell ?? ''),
                        alignment: index === 0 ? 'left' : 'right',
                        bold: true,
                        fillColor: totalRowFill,
                        color: totalRowText,
                    };
                });
                const warningRow = (row) => row.map((cell, index) => {
                    if (cell && typeof cell === 'object' && !Array.isArray(cell)) {
                        return {
                            ...cell,
                            bold: true,
                            fillColor: warningRowFill,
                            color: warningRowText,
                        };
                    }

                    return {
                        text: String(cell ?? ''),
                        alignment: index === 0 ? 'left' : 'right',
                        bold: true,
                        fillColor: warningRowFill,
                        color: warningRowText,
                    };
                });
                const paymentRow = (row) => row.map((cell, index) => {
                    if (cell && typeof cell === 'object' && !Array.isArray(cell)) {
                        return {
                            ...cell,
                            bold: true,
                            fillColor: paymentRowFill,
                            color: paymentRowText,
                        };
                    }

                    return {
                        text: String(cell ?? ''),
                        alignment: index === 0 ? 'left' : 'right',
                        bold: true,
                        fillColor: paymentRowFill,
                        color: paymentRowText,
                    };
                });
                const sectionTitle = (text) => ({
                    text,
                    style: 'sectionTitle',
                    margin: [0, 12, 0, 6],
                });
                const tableLayout = {
                    fillColor: function (rowIndex) {
                        if (rowIndex === 0) return '#eef2f7';
                        return rowIndex % 2 === 0 ? '#fbfcfd' : null;
                    },
                    hLineColor: function () { return '#d9dee3'; },
                    vLineColor: function () { return '#d9dee3'; },
                };

                const resumenEjecutivo = [
                    [{ text: 'Concepto', style: 'tableHeader' }, { text: 'Monto', style: 'tableHeader', alignment: 'right' }],
                    totalRow(['Total vendido bruto', moneyCell(totalVendidoBruto)]),
                    totalRow(['Total incentivo bruto generado', moneyCell(totalIncentivoBruto)]),
                    [`Porcentaje administrativo/coordinador (${formatPercentDisplay(adminPctBruto)}%)`, moneyCell(totalDiezPorciento)],
                    totalRow(['Total final a pagar', moneyCell(totalFinalPagar, true)]),
                ];

                const totalPagarDetallado = [
                    [{ text: 'Destino', style: 'tableHeader' }, { text: 'Monto a pagar', style: 'tableHeader', alignment: 'right' }],
                    paymentRow(['Monto a pagar por agente de ventas', moneyCell(totalIncentivoProcesado)]),
                    paymentRow(['Monto a pagar por administrativo', moneyCell(administrativosTotal + operadoresSeguridadTotal)]),
                    paymentRow(['Monto a pagar por coordinadores', moneyCell(coordinadoresTotal)]),
                ];

                const deducciones = [
                    [{ text: 'Concepto', style: 'tableHeader' }, { text: 'Monto', style: 'tableHeader', alignment: 'right' }],
                    ['Monto generado por faltantes', moneyCell(scaleExcluded(currentExcludedApplication.faltantesDisponible))],
                    ['Monto generado por desvinculados', moneyCell(scaleExcluded(currentExcludedApplication.desvinculadosDisponible))],
                    ['Monto generado por coordinadores excluidos', moneyCell(scaleExcluded(currentExcludedApplication.coordinadoresDisponible))],
                    totalRow(['Total deducciones detectadas', moneyCell(totalDeduccionesDetectadas, true)]),
                    ['Aplicado a bolsa fija por faltantes', moneyCell(scaleExcluded(currentExcludedApplication.aplicadoFaltantes))],
                    ['Aplicado a bolsa fija por desvinculados', moneyCell(scaleExcluded(currentExcludedApplication.aplicadoDesvinculados))],
                    ['Monto en retención aplicado por coordinadores', moneyCell(scaleExcluded(currentExcludedApplication.aplicadoCoordinadores))],
                    totalRow(['Reasignado a operadores/seguridad', moneyCell(operadoresSeguridadTotal, true)]),
                    totalRow(['Rebaja neta por exclusiones', moneyCell(totalRebajadoInforme, true)]),
                ];

                const distribucion = [
                    [{ text: 'Destino', style: 'tableHeader' }, { text: 'Monto base', style: 'tableHeader', alignment: 'right' }, { text: 'Monto distribuido en plantilla', style: 'tableHeader', alignment: 'right' }],
                    ['Administrativo', moneyCell(montoAdministrativo), moneyCell(administrativosTotal)],
                    ['Coordinadores', moneyCell(montoCoordinador), moneyCell(coordinadoresTotal)],
                    ['Monto en retención coordinadores', moneyCell(montoRetencionCoordinadores), moneyCell(0)],
                    ['Operadores/Seguridad reasignado', moneyCell(operadoresSeguridadTotal), moneyCell(operadoresSeguridadTotal)],
                    totalRow(['Total distribuido', { text: '', alignment: 'right' }, moneyCell(distribucionTotal)]),
                ];

                const controles = [
                    [{ text: 'Control', style: 'tableHeader' }, { text: 'Cantidad / Monto', style: 'tableHeader', alignment: 'right' }],
                    ['Usuarios que cumplieron', numberCell(resumenCumplimiento.cumplen)],
                    ['Usuarios que no cumplieron', numberCell(resumenCumplimiento.noCumplen)],
                    ['Usuarios procesados despues de exclusiones', numberCell(processedRows.length)],
                    ['Incentivo de usuarios procesados', moneyCell(totalIncentivoProcesado)],
                    ['Usuarios con IdEmpleado', numberCell(empleadoIdSummary.usuariosConId)],
                    ['Monto usuarios con IdEmpleado', moneyCell(empleadoIdSummary.montoConId)],
                    ['Usuarios sin IdEmpleado', numberCell(empleadoIdSummary.usuariosSinId)],
                    warningRow(['Monto usuarios sin IdEmpleado', moneyCell(empleadoIdSummary.montoSinId)]),
                    ['Cedulas excluidas por faltantes', numberCell(excludedFaltantesCedulas.size)],
                    ['Cedulas excluidas por desvinculados', numberCell(excludedDesvinculadosCedulas.size)],
                    ['Ids excluidos por desvinculados', numberCell(excludedDesvinculadosEmpleadoIds.size)],
                    ['Coordinadores con monto en retención', numberCell(excludedCoordinatorIds.size)],
                    ['Terminales excluidas del calculo', numberCell(terminalesExcluidas.length)],
                    ['Usuarios pendientes por actualizar', numberCell(usuariosPorActualizar)],
                    ['Agencias sin empresa', numberCell(agenciasSinEmpresa)],
                ];

                const empresaRowsMap = new Map();
                grossRows.forEach((row) => {
                    const empresa = normalizeEmpresaLabel(row?.empresa);
                    const key = normalizeEmpresaValue(empresa);
                    const current = empresaRowsMap.get(key) || {
                        empresa,
                        total_vendido: 0,
                        incentivo_base: 0,
                        usuariosSet: new Set(),
                    };

                    current.total_vendido += toIntegerAmount(row?.ventas_mes_actual);
                    current.incentivo_base += toIntegerAmount(row?.nuevo_incentivo);

                    const cedula = normalizeCedulaValue(row?.cedula) || String(row?.cedula ?? '').trim();
                    if (cedula) {
                        current.usuariosSet.add(cedula);
                    }

                    empresaRowsMap.set(key, current);
                });

                const ajusteFinalPorEmpresa = toIntegerAmount(totalDiezPorciento - currentExcludedApplication.totalRebajado);
                const empresasResumenFinal = Array.from(empresaRowsMap.values())
                    .sort((a, b) => String(a.empresa || '').localeCompare(String(b.empresa || ''), 'es', { sensitivity: 'base' }));

                let ajusteAsignado = 0;
                let totalFinalEmpresaAsignado = 0;
                const ultimoIndiceConIncentivo = empresasResumenFinal
                    .map((row, idx) => ({ row, idx }))
                    .filter(item => toIntegerAmount(item.row.incentivo_base) > 0)
                    .map(item => item.idx)
                    .pop();

                empresasResumenFinal.forEach((row, idx) => {
                    const base = toIntegerAmount(row.incentivo_base);
                    let ajuste = 0;

                    if (totalIncentivoBruto > 0 && base > 0) {
                        ajuste = idx === ultimoIndiceConIncentivo
                            ? toIntegerAmount(ajusteFinalPorEmpresa - ajusteAsignado)
                            : toIntegerAmount(ajusteFinalPorEmpresa * (base / totalIncentivoBruto));
                    }

                    ajusteAsignado += ajuste;
                    row.ajuste = ajuste;
                    row.total_final = toIntegerAmount(base + ajuste);
                    totalFinalEmpresaAsignado += row.total_final;
                });

                if (empresasResumenFinal.length && totalFinalEmpresaAsignado !== totalFinalPagar) {
                    const targetIndex = ultimoIndiceConIncentivo ?? empresasResumenFinal.length - 1;
                    empresasResumenFinal[targetIndex].total_final = toIntegerAmount(
                        empresasResumenFinal[targetIndex].total_final + (totalFinalPagar - totalFinalEmpresaAsignado)
                    );
                    empresasResumenFinal[targetIndex].ajuste = toIntegerAmount(
                        empresasResumenFinal[targetIndex].total_final - empresasResumenFinal[targetIndex].incentivo_base
                    );
                }

                const empresasTable = [
                    [
                        { text: 'Empresa', style: 'tableHeader' },
                        { text: 'Ventas', style: 'tableHeader', alignment: 'right' },
                        { text: 'Incentivo base', style: 'tableHeader', alignment: 'right' },
                        { text: 'Ajuste admin/rebajas', style: 'tableHeader', alignment: 'right' },
                        { text: 'Total final', style: 'tableHeader', alignment: 'right' },
                        { text: 'Usuarios', style: 'tableHeader', alignment: 'right' },
                    ],
                    ...(empresasResumenFinal.length
                        ? empresasResumenFinal.map(row => [
                            row.empresa,
                            moneyCell(row.total_vendido),
                            moneyCell(row.incentivo_base),
                            moneyCell(row.ajuste),
                            moneyCell(row.total_final),
                            numberCell(row.usuariosSet.size),
                        ])
                        : [['Sin desglose', moneyCell(0), moneyCell(0), moneyCell(0), moneyCell(0), numberCell(0)]]),
                    totalRow([
                        'Total',
                        moneyCell(empresasResumenFinal.reduce((sum, row) => sum + toIntegerAmount(row.total_vendido), 0)),
                        moneyCell(totalIncentivoBruto),
                        moneyCell(ajusteFinalPorEmpresa),
                        moneyCell(totalFinalPagar),
                        numberCell(empresasResumenFinal.reduce((sum, row) => sum + row.usuariosSet.size, 0)),
                    ]),
                ];

                const terminalesExcluidasText = terminalesExcluidas.length
                    ? terminalesExcluidas.join(', ')
                    : 'No hay terminales excluidas.';
                const paymentTypeAgencyBlock = {
                    table: {
                        widths: ['*', '*', '*'],
                        body: [
                            agenciasPorTipoPago.map(item => ({
                                text: `Pago ${item.label}`,
                                alignment: 'center',
                                bold: true,
                                fillColor: '#e8f0fe',
                                color: '#1e3a5f',
                            })),
                            agenciasPorTipoPago.map(item => ({
                                text: `${item.agencies.toLocaleString('en-US')} agencias`,
                                alignment: 'center',
                                bold: true,
                                fontSize: 11,
                            })),
                        ],
                    },
                    layout: {
                        hLineColor: function () { return '#b8c7dc'; },
                        vLineColor: function () { return '#b8c7dc'; },
                    },
                    margin: [0, 0, 0, 6],
                };

                const docDefinition = {
                    pageSize: 'LETTER',
                    pageMargins: [32, 34, 32, 42],
                    footer: function (currentPage, pageCount) {
                        return {
                            text: `Pagina ${currentPage} de ${pageCount}`,
                            alignment: 'right',
                            margin: [0, 0, 32, 0],
                            fontSize: 8,
                            color: '#6c757d',
                        };
                    },
                    content: [
                        { text: `Informe Gerencial de Incentivos Administrativos - ${informeEmpresaLabel}`, style: 'title' },
                        { text: 'Resumen de cierre del proceso de incentivo', style: 'subtitle' },
                        {
                            columns: [
                                { text: `Periodo: ${formatDateDisplay(fechaIni)} al ${formatDateDisplay(fechaFin)}` },
                                { text: `Sistema: ${sistema}` },
                                { text: `Tipo de pago: ${tipoPago}` },
                            ],
                            margin: [0, 8, 0, 4],
                            fontSize: 9,
                        },
                        {
                            columns: [
                                { text: `Modo: ${modoCalculo}` },
                                { text: `Alcance: ${informeEmpresaLabel}` },
                                { text: `Generado: ${new Date().toLocaleString('es-DO')}`, alignment: 'right' },
                            ],
                            margin: [0, 0, 0, 8],
                            fontSize: 9,
                        },
                        sectionTitle('Agencias calculadas por tipo de pago'),
                        paymentTypeAgencyBlock,
                        sectionTitle('Resumen ejecutivo'),
                        { table: { widths: ['*', '28%'], body: resumenEjecutivo }, layout: tableLayout },
                        sectionTitle('Total a pagar detallado'),
                        { table: { widths: ['*', '28%'], body: totalPagarDetallado }, layout: tableLayout },
                        sectionTitle('Deducciones y reasignaciones'),
                        { table: { widths: ['*', '28%'], body: deducciones }, layout: tableLayout },
                        sectionTitle('Distribucion administrativa'),
                        { table: { widths: ['*', '25%', '28%'], body: distribucion }, layout: tableLayout },
                        sectionTitle('Control del proceso'),
                        { table: { widths: ['*', '28%'], body: controles }, layout: tableLayout },
                        sectionTitle('Resumen por empresa'),
                        { table: { widths: ['*', '17%', '17%', '17%', '17%', '12%'], body: empresasTable }, layout: tableLayout },
                        sectionTitle('Notas gerenciales'),
                        {
                            ul: [
                                'Las terminales excluidas no fueron consideradas en el calculo de ventas ni incentivo.',
                                'Los faltantes, desvinculados y coordinadores con monto en retención se usan primero para cubrir la bolsa fija; el excedente reduce el total final.',
                                'Los coordinadores no excluidos conservan el monto calculado originalmente.',
                                'El informe refleja la configuracion activa al momento de generacion.',
                                `Terminales excluidas: ${terminalesExcluidasText}`,
                            ],
                            fontSize: 8,
                            color: '#495057',
                        },
                    ],
                    styles: {
                        title: { fontSize: 16, bold: true, color: '#212529' },
                        subtitle: { fontSize: 10, color: '#495057' },
                        sectionTitle: { fontSize: 11, bold: true, color: '#1f2937' },
                        tableHeader: { bold: true, fontSize: 8.5, color: '#212529' },
                    },
                    defaultStyle: {
                        fontSize: 8,
                    },
                };

                const pdf = pdfMake.createPdf(docDefinition);
                Swal.close();
                const suffix = informeEmpresaKey === 'todos' ? 'completo' : informeEmpresaKey;
                pdf.download(`informe_gerencial_incentivos_administrativos_${suffix}_${fechaFin || 'proceso'}.pdf`);
            } catch (error) {
                console.error('Error generando informe gerencial:', error);
                Swal.fire({
                    title: 'Error',
                    text: error?.message || String(error) || 'No se pudo generar el informe gerencial.',
                    icon: 'error'
                });
            }
        }, 150);
    }

    function exportCoordinadoresExcel() {
        const rows = getCoordinatorDisplayRows().map((row) => [
            row.nombre,
            row.empleadoid || '',
            toNumber(row.agencias),
            toNumber(row.agencias_validas),
            formatMoney(row.monto_usuarios),
            formatCoordinatorPct(row),
            formatMoney(getCoordinatorAmount(row)),
            isCoordinatorExcluded(row) ? 'SI' : 'NO',
            formatMoney(getCoordinatorAppliedAmount(row)),
            formatMoney(getCoordinatorDisplayAmount(row)),
        ]);

        exportRowsToExcelCsv(
            'coordinadores_incentivos_administrativos_validacion.csv',
            ['Nombre', 'IdEmpleado', 'Agencias', 'Validas', 'Monto', '% Total', 'Monto Calculado', 'En retención', 'Monto en Retención', 'Monto a Pagar'],
            rows
        );
    }

    function getCoordinatorCompanyLabel(row) {
        const companyId = String(row?.companyid ?? '').trim();
        const companyValue = companyId || String(row?.empresa ?? '').trim();
        const normalizedCompany = companyValue.toLowerCase();

        if (companyValue === '168' || normalizedCompany.includes('joselito') || normalizedCompany.includes('consorcio')) {
            return 'Grupo Joselito';
        }
        if (companyValue === '169' || normalizedCompany.includes('negosur')) {
            return 'Negosur';
        }

        return 'Actualizar empresa';
    }

    function generarPdfInformeCoordinadores() {
        if (!cachedRows.length) {
            Swal.fire({ title: 'Informacion', text: 'Primero debes generar el reporte.', icon: 'warning' });
            return;
        }

        if (typeof pdfMake === 'undefined') {
            Swal.fire({ title: 'Error', text: 'No se encontro la libreria para generar PDF.', icon: 'error' });
            return;
        }

        const rows = getCoordinatorDisplayRows();
        if (!rows.length) {
            Swal.fire({ title: 'Sin datos', text: 'No hay coordinadores para generar el informe.', icon: 'warning' });
            return;
        }

        const fechaIni = document.getElementById('ni_fecha_ini')?.value || '';
        const fechaFin = document.getElementById('ni_fecha_fin')?.value || '';
        const sistema = document.getElementById('ni_sistema')?.value || 'Todos';
        const totalAgencies = rows.reduce((sum, row) => sum + toNumber(row.agencias), 0);
        const totalValidAgencies = rows.reduce((sum, row) => sum + toNumber(row.agencias_validas), 0);
        const totalUserAmount = rows.reduce((sum, row) => sum + toIntegerAmount(row.monto_usuarios), 0);
        const totalCalculated = rows.reduce((sum, row) => sum + getCoordinatorAmount(row), 0);
        const totalRetention = rows.reduce((sum, row) => sum + getCoordinatorAppliedAmount(row), 0);
        const totalPayable = rows.reduce((sum, row) => sum + getCoordinatorDisplayAmount(row), 0);
        const totalPercentage = totalUserAmount > 0 ? 100 : 0;
        const moneyCell = (value, bold = false) => ({
            text: formatMoney(value),
            alignment: 'right',
            bold,
        });
        const numberCell = (value, bold = false) => ({
            text: Number(value || 0).toLocaleString('en-US'),
            alignment: 'right',
            bold,
        });
        const summaryBody = [
            [
                { text: 'Coordinador', style: 'tableHeader' },
                { text: 'ID', style: 'tableHeader' },
                { text: 'Empresa', style: 'tableHeader' },
                { text: 'Agencias', style: 'tableHeader', alignment: 'right' },
                { text: 'Validas', style: 'tableHeader', alignment: 'right' },
                { text: 'Monto usuarios', style: 'tableHeader', alignment: 'right' },
                { text: '%', style: 'tableHeader', alignment: 'right' },
                { text: 'Retencion', style: 'tableHeader', alignment: 'right' },
                { text: 'Calculado', style: 'tableHeader', alignment: 'right' },
                { text: 'A pagar', style: 'tableHeader', alignment: 'right' },
            ],
            ...rows.map((row) => [
                row.nombre || '',
                row.empleadoid || '-',
                getCoordinatorCompanyLabel(row),
                numberCell(row.agencias),
                numberCell(row.agencias_validas),
                moneyCell(row.monto_usuarios),
                { text: `${formatCoordinatorPct(row)}%`, alignment: 'right' },
                moneyCell(getCoordinatorAppliedAmount(row)),
                moneyCell(getCoordinatorAmount(row)),
                moneyCell(getCoordinatorDisplayAmount(row), true),
            ]),
            [
                { text: 'Total', colSpan: 2, bold: true, fillColor: '#d1e7dd' },
                { text: '', fillColor: '#d1e7dd' },
                { text: '', fillColor: '#d1e7dd' },
                { ...numberCell(totalAgencies, true), fillColor: '#d1e7dd' },
                { ...numberCell(totalValidAgencies, true), fillColor: '#d1e7dd' },
                { ...moneyCell(totalUserAmount, true), fillColor: '#d1e7dd' },
                { text: `${totalPercentage.toFixed(2)}%`, alignment: 'right', bold: true, fillColor: '#d1e7dd' },
                { ...moneyCell(totalRetention, true), fillColor: '#d1e7dd' },
                { ...moneyCell(totalCalculated, true), fillColor: '#d1e7dd' },
                { ...moneyCell(totalPayable, true), fillColor: '#d1e7dd' },
            ],
        ];
        const content = [
            { text: 'Informe de Validacion de Coordinadores', style: 'title' },
            { text: 'Revision previa al proceso de pago', style: 'subtitle' },
            {
                columns: [
                    { text: `Periodo: ${formatDateDisplay(fechaIni)} al ${formatDateDisplay(fechaFin)}` },
                    { text: `Sistema: ${sistema}`, alignment: 'center' },
                    { text: `Generado: ${new Date().toLocaleString('es-DO')}`, alignment: 'right' },
                ],
                margin: [0, 8, 0, 8],
                fontSize: 8,
            },
            {
                columns: [
                    { text: `Base coordinadores: ${formatMoney(currentCoordinatorBase)}`, bold: true },
                    { text: `Retencion: ${formatMoney(totalRetention)}`, alignment: 'center', bold: true },
                    { text: `Total a pagar: ${formatMoney(totalPayable)}`, alignment: 'right', bold: true },
                ],
                margin: [0, 0, 0, 10],
                fontSize: 9,
            },
            {
                table: {
                    headerRows: 1,
                    dontBreakRows: true,
                    widths: ['*', '7%', '10%', '7%', '7%', '11%', '6%', '10%', '10%', '10%'],
                    body: summaryBody,
                },
                layout: {
                    fillColor: function (rowIndex) {
                        if (rowIndex === 0) return '#eef2f7';
                        return rowIndex % 2 === 0 ? '#fbfcfd' : null;
                    },
                    hLineColor: function () { return '#d9dee3'; },
                    vLineColor: function () { return '#d9dee3'; },
                },
            },
        ];

        const docDefinition = {
            pageSize: 'LETTER',
            pageOrientation: 'landscape',
            pageMargins: [24, 30, 24, 38],
            footer: function (currentPage, pageCount) {
                return {
                    text: `Pagina ${currentPage} de ${pageCount}`,
                    alignment: 'right',
                    margin: [0, 0, 24, 0],
                    fontSize: 8,
                    color: '#6c757d',
                };
            },
            content,
            styles: {
                title: { fontSize: 16, bold: true, color: '#212529' },
                subtitle: { fontSize: 10, color: '#495057' },
                tableHeader: { bold: true, fontSize: 7.5, color: '#212529' },
            },
            defaultStyle: { fontSize: 7.5 },
        };

        pdfMake.createPdf(docDefinition).download(`validacion_coordinadores_incentivos_administrativos_${fechaFin || 'incentivos'}.pdf`);
    }

    function normalizeAdministrativeGroup(value) {
        const group = String(value ?? '').trim();
        if (group.includes('Servs. Tecnicos') || group.includes('Servs Tecnicos')) {
            return '5. Servs. Tecnicos';
        }
        if (group.toLowerCase() === 'seguridad' || group.toLowerCase() === '6 seguridad') {
            return '6. Seguridad';
        }

        return group;
    }

    function isFixedAdministrativeGroup(value) {
        const group = normalizeAdministrativeGroup(value);
        return group === '4. Operadores' || group === '5. Servs. Tecnicos' || group === '6. Seguridad';
    }

    function updateAdministrativeFilterButtons() {
        const active = administrativeGroupFilter;
        const buttonMap = [
            { id: 'btnAdminFiltroTodos', value: 'todos' },
            { id: 'btnAdminFiltroG1', value: '1. Gtes. Y Encarg.' },
            { id: 'btnAdminFiltroG2', value: '2. Monitoreo' },
            { id: 'btnAdminFiltroG45', value: '4_5' },
        ];

        buttonMap.forEach((item) => {
            const button = document.getElementById(item.id);
            if (!button) return;

            const isActive = active === item.value;
            button.classList.toggle('btn-primary', isActive);
            button.classList.toggle('btn-outline-primary', !isActive);
        });
    }

    function renderPuestoPctInputs() {
        const g1Input = document.getElementById('puesto_pct_g1');
        const g2Input = document.getElementById('puesto_pct_g2');
        const g45Input = document.getElementById('puesto_pct_g45');

        if (g1Input) g1Input.value = puestoPctConfig.g1;
        if (g2Input) g2Input.value = puestoPctConfig.g2;
        if (g45Input) g45Input.value = puestoPctConfig.g45;
    }

    function readPuestoPctInputs() {
        puestoPctConfig.g1 = Math.max(0, parseFloat(document.getElementById('puesto_pct_g1')?.value || 0) || 0);
        puestoPctConfig.g2 = Math.max(0, parseFloat(document.getElementById('puesto_pct_g2')?.value || 0) || 0);
        puestoPctConfig.g45 = Math.max(0, parseFloat(document.getElementById('puesto_pct_g45')?.value || 0) || 0);
    }

    function getPuestoPctByCategoryKey(categoryKey) {
        if (categoryKey === 'g1') return toNumber(puestoPctConfig.g1);
        if (categoryKey === 'g2') return toNumber(puestoPctConfig.g2);
        if (categoryKey === 'g45') return toNumber(puestoPctConfig.g45);
        return 0;
    }

    function getAdministrativePoolForEmpresa(empresaValue) {
        const key = normalizeAdministrativeEmpresaKey(empresaValue);
        return toNumber(currentAdministrativePoolByEmpresa[key] || 0);
    }

    function getPuestoCategoryBudget(categoryKey, empresaValue = null) {
        const base = empresaValue === null
            ? toIntegerAmount(currentAdministrativePoolBase)
            : toIntegerAmount(getAdministrativePoolForEmpresa(empresaValue));
        const topUp = categoryKey === 'g45' && empresaValue === null
            ? toIntegerAmount(currentFixedBagTopUp)
            : 0;

        return toIntegerAmount(base * (getPuestoPctByCategoryKey(categoryKey) / 100)) + topUp;
    }

    function getAdministrativeCategoryKeyByGroup(groupValue) {
        const group = normalizeAdministrativeGroup(groupValue);
        if (group === '1. Gtes. Y Encarg.') return 'g1';
        if (group === '2. Monitoreo') return 'g2';
        if (group === '4. Operadores' || group === '5. Servs. Tecnicos' || group === '6. Seguridad') return 'g45';
        return null;
    }

    function getAdministrativeCategoryPctTotal(categoryKey, empresaValue = null) {
        const empresaKey = empresaValue === null ? null : normalizeAdministrativeEmpresaKey(empresaValue);
        const allRows = [
            ...administrativeRows.map((row) => ({
                ...row,
                grupo: normalizeAdministrativeGroup(row.grupo),
                __empresaKey: normalizeAdministrativeEmpresaKey(row.empresa),
            })),
            ...operatorRows.map((row) => ({
                ...row,
                grupo: normalizeAdministrativeGroup(row.grupo),
                __empresaKey: normalizeAdministrativeEmpresaKey(row.empresa),
            })),
        ];

        return allRows
            .filter((row) => getAdministrativeCategoryKeyByGroup(row.grupo) === categoryKey)
            .filter((row) => empresaKey === null || row.__empresaKey === empresaKey)
            .reduce((sum, row) => sum + (isFixedAdministrativeGroup(row.grupo) ? toIntegerAmount(row.pct) : toNumber(row.pct)), 0);
    }

    function getFixedAdministrativeAmountTotal(empresaValue = null) {
        const empresaKey = empresaValue === null ? null : normalizeAdministrativeEmpresaKey(empresaValue);

        return operatorRows
            .map((row) => ({
                ...row,
                grupo: normalizeAdministrativeGroup(row.grupo),
                __empresaKey: normalizeAdministrativeEmpresaKey(row.empresa),
            }))
            .filter((row) => isFixedAdministrativeGroup(row.grupo))
            .filter((row) => empresaKey === null || row.__empresaKey === empresaKey)
            .reduce((sum, row) => sum + toIntegerAmount(row.pct), 0);
    }

    function getFixedAdministrativeBalance(empresaValue = null) {
        const budget = getPuestoCategoryBudget('g45', empresaValue);
        const fixedTotal = getFixedAdministrativeAmountTotal(empresaValue);

        return {
            budget,
            fixedTotal,
            remaining: Math.max(budget - fixedTotal, 0),
            missing: Math.max(fixedTotal - budget, 0),
        };
    }

    function getAdministrativeAmountByRow(row) {
        const categoryKey = getAdministrativeCategoryKeyByGroup(row?.grupo);
        if (!categoryKey) {
            return 0;
        }

        if (isFixedAdministrativeGroup(row?.grupo)) {
            return toIntegerAmount(row?.pct);
        }

        const empresa = normalizeAdministrativeEmpresaLabel(row?.empresa);
        const categoryPctTotal = getAdministrativeCategoryPctTotal(categoryKey, empresa);
        if (categoryPctTotal <= 0) {
            return 0;
        }

        const categoryBudget = getPuestoCategoryBudget(categoryKey, empresa);
        return toIntegerAmount(categoryBudget * (toNumber(row.pct) / categoryPctTotal));
    }

    function getAdministrativeAmount(row) {
        return getAdministrativeAmountByRow(row);
    }

    function getAdministrativeDisplayRows() {
        const adminItems = administrativeRows.map((row, idx) => ({
            ...row,
            grupo: normalizeAdministrativeGroup(row.grupo),
            empresa: normalizeAdministrativeEmpresaLabel(row.empresa),
            __empresaKey: normalizeAdministrativeEmpresaKey(row.empresa),
            __tipo: 'admin',
            __idx: idx,
        }));

        const operatorItems = operatorRows.map((row, idx) => ({
            ...row,
            grupo: normalizeAdministrativeGroup(row.grupo),
            empresa: normalizeAdministrativeEmpresaLabel(row.empresa),
            __empresaKey: normalizeAdministrativeEmpresaKey(row.empresa),
            __tipo: 'operador',
            __idx: idx,
        }));

        const allRows = [...adminItems, ...operatorItems];
        const empresaFilterKey = getAdministrativeEmpresaFilterKey();
        let filteredRows = allRows;

        if (administrativeGroupFilter === '4_5') {
            filteredRows = filteredRows.filter((row) => getAdministrativeCategoryKeyByGroup(row.grupo) === 'g45');
        } else if (administrativeGroupFilter !== 'todos') {
            filteredRows = filteredRows.filter((row) => row.grupo === administrativeGroupFilter);
        }

        if (empresaFilterKey !== 'todos') {
            filteredRows = filteredRows.filter((row) => row.__empresaKey === empresaFilterKey);
        }

        return filteredRows.filter((row) => !isAdministrativeDesvinculadoExcluded(row));
    }

    function getAdministrativeDisplayAmount(row) {
        if (row.__tipo === 'operador') {
            return getAdministrativeAmountByRow(operatorRows[row.__idx] || row);
        }

        return getAdministrativeAmountByRow(administrativeRows[row.__idx] || row);
    }

    function getCoordinatorPctTotal() {
        return getCoordinatorMontoUsuariosTotal();
    }

    function getCoordinatorAmount(row) {
        const pctTotal = getCoordinatorPctTotal();
        if (pctTotal <= 0) {
            return 0;
        }

        return toIntegerAmount(currentCoordinatorBase * (toIntegerAmount(row.monto_usuarios) / pctTotal));
    }

    function isCoordinatorExcluded(row) {
        const key = getCoordinatorIdKey(row);
        return key !== '' && excludedCoordinatorIds.has(key);
    }

    function getCoordinatorDisplayAmount(row) {
        return isCoordinatorExcluded(row) ? 0 : getCoordinatorAmount(row);
    }

    function formatCoordinatorPct(row) {
        const pctTotal = getCoordinatorPctTotal();
        if (pctTotal <= 0) {
            return '0.00';
        }

        return ((toNumber(row.monto_usuarios) / pctTotal) * 100).toFixed(2);
    }

    function getCoordinatorMontoUsuariosTotal() {
        return coordinatorRows.reduce((sum, row) => sum + toIntegerAmount(row.monto_usuarios), 0);
    }

    function getCoordinatorDetailUsers(row) {
        if (!row || row.id === null || row.id === undefined) {
            return [];
        }

        return coordinatorUserDetailsByCoordinator[String(row.id)] || [];
    }

    function formatAdministrativePct(value) {
        return (toNumber(value) * 100).toFixed(2);
    }

    function formatAdministrativeConfigValue(row) {
        return isFixedAdministrativeGroup(row?.grupo)
            ? toNumber(row?.pct).toFixed(2)
            : formatAdministrativePct(row?.pct);
    }

    function readAdministrativeConfigInputValue(row, field, inputValue) {
        if (field !== 'pct') {
            return inputValue;
        }

        const value = Math.max(0, parseFloat(inputValue || 0) || 0);
        return isFixedAdministrativeGroup(row?.grupo) ? value : value / 100;
    }

    function getAdministrativeFilteredBudget() {
        if (administrativeGroupFilter === '1. Gtes. Y Encarg.') return getPuestoCategoryBudget('g1');
        if (administrativeGroupFilter === '2. Monitoreo') return getPuestoCategoryBudget('g2');
        if (administrativeGroupFilter === '4_5') return getPuestoCategoryBudget('g45');

        return getPuestoCategoryBudget('g1') + getPuestoCategoryBudget('g2') + getPuestoCategoryBudget('g45');
    }

    function recalculateAdministrativeOperatorBases() {
        currentAdministrativeBase = getPuestoCategoryBudget('g1') + getPuestoCategoryBudget('g2');
        currentOperatorBase = getPuestoCategoryBudget('g45');
    }

    function getOperatorAmount(row) {
        return getAdministrativeAmountByRow(row);
    }

    function updateAdministrativeSummary() {
        const visibleRows = getAdministrativeDisplayRows();
        const totalDistribuido = visibleRows.reduce((sum, row) => sum + getAdministrativeDisplayAmount(row), 0);
        const montoG1 = visibleRows
            .filter(row => getAdministrativeCategoryKeyByGroup(row.grupo) === 'g1')
            .reduce((sum, row) => sum + getAdministrativeDisplayAmount(row), 0);
        const montoG2 = visibleRows
            .filter(row => getAdministrativeCategoryKeyByGroup(row.grupo) === 'g2')
            .reduce((sum, row) => sum + getAdministrativeDisplayAmount(row), 0);
        const montoG45 = visibleRows
            .filter(row => getAdministrativeCategoryKeyByGroup(row.grupo) === 'g45')
            .reduce((sum, row) => sum + getAdministrativeDisplayAmount(row), 0);
        const montoFiltro = totalDistribuido;

        document.getElementById('admin_base_total').textContent = formatMoney(toIntegerAmount(currentAdministrativePoolBase) + toIntegerAmount(currentFixedBagTopUp));
        document.getElementById('admin_distribuido_total').textContent = formatMoney(montoFiltro);
        const tablaTotal = document.getElementById('admin_tabla_total');
        if (tablaTotal) {
            tablaTotal.textContent = formatMoney(totalDistribuido);
        }
        const catG1 = document.getElementById('admin_cat_g1');
        if (catG1) {
            catG1.textContent = `${toNumber(puestoPctConfig.g1).toFixed(2)}% | ${formatMoney(montoG1)}`;
        }
        const catG2 = document.getElementById('admin_cat_g2');
        if (catG2) {
            catG2.textContent = `${toNumber(puestoPctConfig.g2).toFixed(2)}% | ${formatMoney(montoG2)}`;
        }
        const catG45 = document.getElementById('admin_cat_g45');
        if (catG45) {
            const balanceG45 = getFixedAdministrativeBalance();
            const balanceText = balanceG45.missing > 0
                ? ` | Faltan ${formatMoney(balanceG45.missing)}`
                : ` | Restante ${formatMoney(balanceG45.remaining)}`;
            catG45.textContent = `${toNumber(puestoPctConfig.g45).toFixed(2)}% | ${formatMoney(montoG45)}${balanceText}`;
            catG45.classList.toggle('text-danger', balanceG45.missing > 0);
        }
        const totalCol = document.getElementById('admin_col_total');
        if (totalCol) {
            totalCol.textContent = formatMoney(totalDistribuido);
        }
    }

    function updateCoordinatorSummary() {
        const totalDistribuido = getCoordinatorDisplayRows().reduce((sum, row) => sum + getCoordinatorDisplayAmount(row), 0);
        const totalBolsa = getCoordinatorExcludedTotal();

        document.getElementById('coord_base_total').textContent = formatMoney(currentCoordinatorBase);
        document.getElementById('coord_distribuido_total').textContent = `${formatMoney(totalDistribuido)} | Retención: ${formatMoney(totalBolsa)}`;
    }

    function updateAdministrativeAmounts() {
        document.querySelectorAll('.admin-display-monto').forEach((cell) => {
            const idx = parseInt(cell.dataset.idx, 10);
            const tipo = cell.dataset.tipo;
            if (Number.isNaN(idx)) {
                return;
            }

            const amount = tipo === 'operador'
                ? getOperatorAmount(operatorRows[idx] || {})
                : getAdministrativeAmount(administrativeRows[idx] || {});

            cell.textContent = formatMoney(amount);
        });
        updateAdministrativeSummary();
    }

    function updateCoordinatorAmounts() {
        coordinatorRows.forEach((row, idx) => {
            const cell = document.querySelector(`.coord-monto[data-idx="${idx}"]`);
            if (cell) {
                cell.textContent = formatMoney(getCoordinatorDisplayAmount(row));
            }

            const pctInput = document.querySelector(`.coord-pct-input[data-idx="${idx}"]`);
            if (pctInput) {
                pctInput.value = formatCoordinatorPct(row);
            }
        });
        updateCoordinatorSummary();
    }

    function updateOperatorAmounts() {
        updateAdministrativeAmounts();
    }

    function updateAdministrativeAndOperatorAmounts() {
        recalculateAdministrativeOperatorBases();
        updateAdministrativeAmounts();
    }

    function getGrupoNuevoAdministrativo() {
        if (administrativeGroupFilter === '1. Gtes. Y Encarg.') return '1. Gtes. Y Encarg.';
        if (administrativeGroupFilter === '2. Monitoreo') return '2. Monitoreo';
        if (administrativeGroupFilter === '4_5') return '4. Operadores';

        return '4. Operadores';
    }

    function getEmpresaNuevaAdministrativa() {
        const selected = document.getElementById('ni_filtro_empresa')?.value || 'todos';
        if (selected === 'todos') {
            return '';
        }

        return normalizeAdministrativeEmpresaLabel(selected);
    }

    function buildAdminSelectOptions(options, selectedValue) {
        const selected = String(selectedValue || '').trim();
        const values = [...options];
        if (selected && !values.includes(selected)) {
            values.unshift(selected);
        }

        return values
            .map(value => `<option value="${escapeHtml(value)}" ${value === selected ? 'selected' : ''}>${escapeHtml(value)}</option>`)
            .join('');
    }

    function rebalanceAdministrativeRows() {
        const split = splitAdministrativeRowsByGroup([...administrativeRows, ...operatorRows]);
        administrativeRows = split.administrativos;
        operatorRows = split.operadores;
    }

    function scrollAdministrativeTableToBottom() {
        const tableWrapper = document.querySelector('#tbodyAdministrativos')?.closest('.table-responsive');
        if (tableWrapper) {
            tableWrapper.scrollTop = tableWrapper.scrollHeight;
        }
    }

    function agregarFilaAdministrativa() {
        const grupo = getGrupoNuevoAdministrativo();
        const row = {
            id: null,
            grupo,
            nombre: '',
            empresa: getEmpresaNuevaAdministrativa(),
            pct: 0,
        };

        if (isFixedAdministrativeGroup(grupo)) {
            operatorRows.push(row);
        } else {
            administrativeRows.push(row);
        }

        renderAdministrativeCategoryTable();
        updateAdministrativeAndOperatorAmounts();
        scrollAdministrativeTableToBottom();
    }

    function eliminarFilaAdministrativa(tipo, idx) {
        const index = parseInt(idx, 10);
        if (Number.isNaN(index)) {
            return;
        }

        if (tipo === 'operador') {
            operatorRows.splice(index, 1);
        } else {
            administrativeRows.splice(index, 1);
        }

        renderAdministrativeCategoryTable();
        updateAdministrativeAndOperatorAmounts();
    }

    function getAdministrativeRowsForSave() {
        return [
            ...administrativeRows,
            ...operatorRows,
        ].map((row) => ({
            id: row?.id || null,
            grupo: normalizeAdministrativeGroup(row?.grupo),
            nombre: String(row?.nombre || '').trim(),
            empresa: normalizeAdministrativeEmpresaLabel(row?.empresa),
            pct_total: isFixedAdministrativeGroup(row?.grupo)
                ? toNumber(row?.pct)
                : toNumber(row?.pct) * 100,
        }));
    }

    function validateAdministrativeRowsForSave(rows) {
        if (!rows.length) {
            return 'Debe existir al menos una fila administrativa.';
        }

        const invalidIndex = rows.findIndex(row => !row.grupo || !row.nombre || !row.empresa);
        if (invalidIndex >= 0) {
            return `Completa grupo, nombre y empresa en la fila ${invalidIndex + 1} antes de guardar.`;
        }

        const keys = new Set();
        for (const row of rows) {
            if (!isFixedAdministrativeGroup(row.grupo) && toNumber(row.pct_total) > 100) {
                return 'El % Total no puede ser mayor que 100 para gerentes, encargados o monitoreo.';
            }

            const key = `${row.grupo}|${row.nombre}|${row.empresa}`.toLowerCase();
            if (keys.has(key)) {
                return 'Hay filas duplicadas por grupo, nombre y empresa. Ajustalas antes de guardar.';
            }
            keys.add(key);
        }

        return '';
    }

    function refreshAdministrativeRowsFromServer(rows) {
        administrativeRowsConfig = Array.isArray(rows) ? rows : [];
        const split = splitAdministrativeRowsByGroup(normalizeAdministrativeRowsFromPayload(rows));
        administrativeRows = split.administrativos;
        operatorRows = split.operadores;
        renderAdministrativeCategoryTable();
        updateAdministrativeAndOperatorAmounts();
    }

    function guardarAdministrativosPlantilla() {
        const rows = getAdministrativeRowsForSave();
        const error = validateAdministrativeRowsForSave(rows);
        if (error) {
            Swal.fire({ title: 'Validacion', text: error, icon: 'warning' });
            return;
        }

        Swal.fire({
            title: 'Guardar cambios',
            text: 'Esto actualizara permanentemente la tabla de administrativos del incentivo.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Guardar',
            cancelButtonText: 'Cancelar',
        }).then((result) => {
            if (!result.isConfirmed) {
                return;
            }

            Swal.fire({
                title: 'Guardando plantilla...',
                icon: 'info',
                allowOutsideClick: false,
                showConfirmButton: false,
                didOpen: () => Swal.showLoading(),
            });

            fetch('/incentivos/calculo-de-incentivos/administrativos/sincronizar', {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                },
                body: JSON.stringify({ rows }),
            })
                .then(response => parseResponseAsJson(response, 'Error guardando plantilla administrativa'))
                .then(resp => {
                    refreshAdministrativeRowsFromServer(resp.data || []);
                    Swal.fire({
                        title: 'Guardado',
                        text: resp.message || 'Plantilla administrativa guardada correctamente.',
                        icon: 'success',
                    });
                })
                .catch(error => {
                    Swal.fire({ title: 'Error', text: error?.message || String(error), icon: 'warning' });
                });
        });
    }

    function renderAdministrativeCategoryTable() {
        const tbody = document.getElementById('tbodyAdministrativos');
        tbody.innerHTML = '';
        updateAdministrativeFilterButtons();

        const rows = getAdministrativeDisplayRows();

        if (!rows.length) {
            tbody.innerHTML = '<tr><td colspan="6" class="text-center text-muted">No hay datos para este filtro.</td></tr>';
            updateAdministrativeSummary();
            return;
        }

        rows.forEach((row) => {
            const isFixedGroup = isFixedAdministrativeGroup(row.grupo);
            const inputClass = row.__tipo === 'operador' ? 'op-input op-pct-input' : 'admin-input admin-pct-input';
            const rowClass = row.__tipo === 'operador' ? 'op-input' : 'admin-input';
            const amount = getAdministrativeDisplayAmount(row);
            const suffix = isFixedGroup ? 'RD$' : '%';
            const max = isFixedGroup ? '9999999' : '100';

            const tr = document.createElement('tr');
            tr.innerHTML = `
                <td>
                    <select class="form-select form-select-sm ${rowClass} admin-grupo-select" data-field="grupo" data-idx="${row.__idx}">
                        ${buildAdminSelectOptions(ADMIN_GROUP_OPTIONS, row.grupo)}
                    </select>
                </td>
                <td><input type="text" class="form-control form-control-sm ${rowClass}" data-field="nombre" data-idx="${row.__idx}" value="${escapeHtml(row.nombre)}"></td>
                <td>
                    <select class="form-select form-select-sm ${rowClass}" data-field="empresa" data-idx="${row.__idx}">
                        ${buildAdminSelectOptions(ADMIN_EMPRESA_OPTIONS, row.empresa)}
                    </select>
                </td>
                <td>
                    <div class="input-group input-group-sm">
                        <input type="number" class="form-control ${inputClass}" data-field="pct" data-idx="${row.__idx}" min="0" max="${max}" step="0.01" value="${formatAdministrativeConfigValue(row)}">
                        <span class="input-group-text">${suffix}</span>
                    </div>
                </td>
                <td class="text-end fw-semibold admin-display-monto" data-tipo="${row.__tipo}" data-idx="${row.__idx}">${formatMoney(amount)}</td>
                <td class="text-center">
                    <button type="button" class="btn btn-sm btn-soft-danger btn-delete-admin-row" data-tipo="${row.__tipo}" data-idx="${row.__idx}">Eliminar</button>
                </td>
            `;
            tbody.appendChild(tr);
        });

        updateAdministrativeSummary();
    }

    function renderCoordinatorTable() {
        const tbody = document.getElementById('tbodyCoordinadores');
        tbody.innerHTML = '';

        const rows = getCoordinatorDisplayRows();

        if (!rows.length) {
            tbody.innerHTML = '<tr><td colspan="8" class="text-center text-muted">No hay coordinadores registrados.</td></tr>';
            updateCoordinatorSummary();
            return;
        }

        rows.forEach((row) => {
            const idx = row.__idx;
            const detailUsers = getCoordinatorDetailUsers(row);
            const hasDetail = detailUsers.length > 0;
            const excluded = isCoordinatorExcluded(row);
            const rowAmount = getCoordinatorDisplayAmount(row);
            const appliedAmount = getCoordinatorAppliedAmount(row);
            const tr = document.createElement('tr');
            tr.classList.toggle('table-warning', excluded);
            tr.innerHTML = `
                <td><input type="text" class="form-control form-control-sm coord-input" data-field="nombre" data-idx="${idx}" value="${escapeHtml(row.nombre)}"></td>
                <td class="text-center fw-semibold">${toNumber(row.agencias)}</td>
                <td class="text-center fw-semibold text-success">${toNumber(row.agencias_validas)}</td>
                <td class="text-center">
                    <input class="form-check-input coord-bolsa-check" type="checkbox" data-idx="${idx}" ${excluded ? 'checked' : ''}>
                </td>
                <td class="text-end fw-semibold">${formatMoney(row.monto_usuarios)}</td>
                <td class="text-center">
                    <button type="button" class="btn btn-sm btn-outline-primary btn-ver-detalle-coord" data-idx="${idx}" ${hasDetail ? '' : 'disabled'}>Ver</button>
                </td>
                <td>
                    <div class="input-group input-group-sm">
                        <input type="number" class="form-control coord-pct-input" data-idx="${idx}" min="0" step="0.01" value="${formatCoordinatorPct(row)}" readonly>
                        <span class="input-group-text">%</span>
                    </div>
                </td>
                <td class="text-end fw-semibold coord-monto" data-idx="${idx}">
                    ${formatMoney(rowAmount)}
                    ${excluded ? `<br><small class="text-warning">Retención: ${formatMoney(appliedAmount)}</small>` : ''}
                </td>
            `;
            tbody.appendChild(tr);
        });

        updateCoordinatorSummary();
    }

    function renderCoordinatorDetailTable(row) {
        const tbody = document.getElementById('tbodyCoordinadorDetalle');
        const title = document.getElementById('coordinatorDetailTitle');
        const excelButton = document.getElementById('btnExportCoordinadorDetalleExcel');
        const pdfButton = document.getElementById('btnExportCoordinadorDetallePdf');
        const users = getCoordinatorDetailUsers(row);
        const coordinatorName = row?.nombre || 'Coordinador';

        currentCoordinatorDetailRow = row || null;
        title.textContent = `Detalle de Usuarios - ${coordinatorName}`;
        tbody.innerHTML = '';
        excelButton.disabled = users.length === 0;
        pdfButton.disabled = users.length === 0;

        if (!users.length) {
            tbody.innerHTML = '<tr><td colspan="3" class="text-center text-muted">No hay usuarios para este coordinador.</td></tr>';
            return;
        }

        users.forEach((user) => {
            const tr = document.createElement('tr');
            tr.innerHTML = `
                <td class="fw-semibold">${escapeHtml(user.cedula)}</td>
                <td>${escapeHtml(user.usuario)}</td>
                <td class="text-end fw-semibold">${formatMoney(user.incentivo)}</td>
            `;
            tbody.appendChild(tr);
        });
    }

    function getCoordinatorDetailExportData() {
        const row = currentCoordinatorDetailRow;
        const users = getCoordinatorDetailUsers(row);

        if (!row || !users.length) {
            Swal.fire({
                title: 'Sin datos',
                text: 'No hay usuarios disponibles para exportar en este coordinador.',
                icon: 'warning',
            });
            return null;
        }

        return {
            row,
            users,
            coordinatorName: String(row.nombre || 'Coordinador').trim(),
            total: users.reduce((sum, user) => sum + toIntegerAmount(user?.incentivo), 0),
        };
    }

    function coordinatorDetailFilename(value) {
        return String(value || 'coordinador')
            .normalize('NFD')
            .replace(/[\u0300-\u036f]/g, '')
            .replace(/[^a-z0-9]+/gi, '_')
            .replace(/^_+|_+$/g, '')
            .toLowerCase() || 'coordinador';
    }

    function exportCoordinatorDetailExcel() {
        const data = getCoordinatorDetailExportData();
        if (!data) {
            return;
        }

        const headers = ['Cédula', 'Usuario', 'Incentivo'];
        const rows = [
            ...data.users.map((user) => [
                String(user?.cedula ?? ''),
                String(user?.usuario ?? ''),
                String(toIntegerAmount(user?.incentivo)),
            ]),
            ['', 'TOTAL', String(data.total)],
        ];
        const filename = `detalle_usuarios_${coordinatorDetailFilename(data.coordinatorName)}`;

        if (typeof JSZip === 'undefined') {
            downloadBlob(`${filename}.xls`, buildExcelHtmlBlob(headers, rows));
            return;
        }

        buildXlsxBlob(headers, rows).then((blob) => downloadBlob(`${filename}.xlsx`, blob));
    }

    function exportCoordinatorDetailPdf() {
        const data = getCoordinatorDetailExportData();
        if (!data) {
            return;
        }

        if (typeof pdfMake === 'undefined') {
            Swal.fire({ title: 'Error', text: 'No se encontró la librería para generar PDF.', icon: 'error' });
            return;
        }

        const fechaInicio = document.getElementById('ni_fecha_ini')?.value || '';
        const fechaFin = document.getElementById('ni_fecha_fin')?.value || '';
        const sistema = document.getElementById('ni_sistema')?.value || 'Todos';
        const tableBody = [
            [
                { text: 'Cédula', style: 'tableHeader' },
                { text: 'Usuario', style: 'tableHeader' },
                { text: 'Incentivo', style: 'tableHeader', alignment: 'right' },
            ],
            ...data.users.map((user) => [
                String(user?.cedula ?? ''),
                String(user?.usuario ?? ''),
                { text: `RD$${formatMoney(user?.incentivo)}`, alignment: 'right' },
            ]),
            [
                { text: 'TOTAL', colSpan: 2, alignment: 'right', bold: true, fillColor: '#e2e8f0' },
                {},
                { text: `RD$${formatMoney(data.total)}`, alignment: 'right', bold: true, fillColor: '#e2e8f0' },
            ],
        ];
        const docDefinition = {
            pageSize: 'LETTER',
            pageMargins: [36, 36, 36, 36],
            content: [
                { text: 'DETALLE DE USUARIOS POR COORDINADOR', style: 'title' },
                { text: data.coordinatorName, style: 'subtitle' },
                {
                    columns: [
                        { text: `Período: ${formatDateDisplay(fechaInicio)} al ${formatDateDisplay(fechaFin)}` },
                        { text: `Sistema: ${sistema}`, alignment: 'center' },
                        { text: `Usuarios: ${data.users.length.toLocaleString('en-US')}`, alignment: 'right' },
                    ],
                    margin: [0, 8, 0, 14],
                    fontSize: 9,
                    color: '#475569',
                },
                {
                    table: {
                        headerRows: 1,
                        widths: ['25%', '50%', '25%'],
                        body: tableBody,
                    },
                    layout: {
                        fillColor: (rowIndex) => rowIndex > 0 && rowIndex % 2 === 0 ? '#f8fafc' : null,
                        hLineColor: () => '#cbd5e1',
                        vLineColor: () => '#cbd5e1',
                    },
                    fontSize: 9,
                },
            ],
            footer: (currentPage, pageCount) => ({
                text: `Página ${currentPage} de ${pageCount}`,
                alignment: 'center',
                margin: [0, 10, 0, 0],
                fontSize: 8,
                color: '#64748b',
            }),
            styles: {
                title: { fontSize: 16, bold: true, alignment: 'center', color: '#0f172a' },
                subtitle: { fontSize: 12, bold: true, alignment: 'center', color: '#334155', margin: [0, 4, 0, 4] },
                tableHeader: { bold: true, color: '#ffffff', fillColor: '#1e3a8a' },
            },
            defaultStyle: { fontSize: 9 },
        };
        const filename = `detalle_usuarios_${coordinatorDetailFilename(data.coordinatorName)}_${fechaFin || 'incentivos'}.pdf`;

        pdfMake.createPdf(docDefinition).download(filename);
    }

    function aplicarCoordinadoresBolsa() {
        if (!coordinatorRows.length) {
            Swal.fire({ title: 'Sin coordinadores', text: 'No hay coordinadores registrados para aplicar.', icon: 'info' });
            return;
        }

        if (!cachedRows.length) {
            Swal.fire({ title: 'Informacion', text: 'Primero debes generar el reporte para calcular el monto de coordinadores.', icon: 'warning' });
            return;
        }

        const selectedIndexes = [...document.querySelectorAll('.coord-bolsa-check:checked')]
            .map(input => parseInt(input.dataset.idx, 10))
            .filter(idx => !Number.isNaN(idx) && coordinatorRows[idx]);
        const nextExcludedIds = new Set();
        const nextAmounts = {};

        selectedIndexes.forEach((idx) => {
            const row = coordinatorRows[idx];
            const key = getCoordinatorIdKey(row);
            if (!key) {
                return;
            }

            nextExcludedIds.add(key);
            nextAmounts[key] = toIntegerAmount(getCoordinatorAmount(row));
        });

        Swal.fire({
            title: 'Aplicando coordinadores...',
            text: 'Estamos marcando el monto en retención de los coordinadores seleccionados y recalculando el reporte.',
            icon: 'info',
            allowOutsideClick: false,
            showConfirmButton: false,
            didOpen: () => Swal.showLoading()
        });

        setTimeout(() => {
            excludedCoordinatorIds = nextExcludedIds;
            appliedCoordinatorAmounts = nextAmounts;

            applyLocalFilters(false);

            Swal.fire({
                title: 'Coordinadores aplicados',
                text: `${excludedCoordinatorIds.size} coordinadores con monto en retención por ${formatMoney(getCoordinatorExcludedTotal())}. Los demas coordinadores conservan su monto calculado.`,
                icon: 'success',
            });
        }, 120);
    }

    function updateCoordinatorValidAgencies(meta) {
        const validAgenciesByCoordinator = meta?.coordinador_agencias_validas || {};
        const userAmountsByCoordinator = meta?.coordinador_monto_usuarios || {};
        coordinatorUserDetailsByCoordinator = meta?.coordinador_detalle_usuarios || {};

        coordinatorRows = coordinatorRows.map((row) => ({
            ...row,
            agencias_validas: toNumber(validAgenciesByCoordinator[String(row.id)] || 0),
            monto_usuarios: toNumber(userAmountsByCoordinator[String(row.id)] || 0),
        }));
    }

    function renderRangesTable() {
        const tbody = document.getElementById('tbodyTramosPago');
        tbody.innerHTML = '';

        const tipoPago = document.getElementById('ni_tipo_pago').value;
        const tipoPagoLabel = document.getElementById('ni_tipo_pago').selectedOptions[0].textContent;
        document.getElementById('modalConfigPctTitle').textContent = `Configurar ${tipoPagoLabel}`;

        payoutRangesByType[tipoPago].forEach((row, idx) => {
            const hastaValue = row.hasta === null || row.hasta === undefined ? '' : row.hasta;
            const label = row.tipo === 'porcentaje' ? '% de ventas' : 'Pago fijo';
            const tr = document.createElement('tr');
            tr.innerHTML = `
                <td><input type="number" class="form-control tramo-desde" data-idx="${idx}" min="0" step="1" value="${row.desde}"></td>
                <td><input type="number" class="form-control tramo-hasta" data-idx="${idx}" min="0" step="1" value="${hastaValue}" placeholder="Sin tope"></td>
                <td>
                    <div class="input-group">
                        <input type="number" class="form-control tramo-pago" data-idx="${idx}" min="0" step="0.01" value="${row.pago}">
                        <span class="input-group-text">${label}</span>
                    </div>
                    <input type="hidden" class="tramo-tipo" data-idx="${idx}" value="${row.tipo}">
                </td>
            `;
            tbody.appendChild(tr);
        });
    }

    function readRangesFromTable() {
        const desdeInputs = document.querySelectorAll('.tramo-desde');
        const hastaInputs = document.querySelectorAll('.tramo-hasta');
        const pagoInputs = document.querySelectorAll('.tramo-pago');

        const ranges = [];
        for (let i = 0; i < desdeInputs.length; i++) {
            const desde = parseFloat(desdeInputs[i].value || 0);
            const hasta = hastaInputs[i].value === '' ? null : parseFloat(hastaInputs[i].value || 0);
            const pago = parseFloat(pagoInputs[i].value || 0);
            const tipo = document.querySelector(`.tramo-tipo[data-idx="${i}"]`)?.value || 'fijo';

            if (desde < 0 || (hasta !== null && hasta < 0) || pago < 0) {
                throw new Error(`Hay valores negativos en la fila ${i + 1}.`);
            }
            if (hasta !== null && desde > hasta) {
                throw new Error(`El valor Desde no puede ser mayor que Hasta en la fila ${i + 1}.`);
            }

            ranges.push({ desde, hasta, pago, tipo });
        }

        return ranges.sort((a, b) => a.desde - b.desde);
    }

    function updateCardsFromData(data) {
        const grossRows = getBaseFilteredRows();
        const cumplimientoSummary = getUsuariosCumplimientoSummary(data);
        const totalCumplen = cumplimientoSummary.cumplen;
        const totalNoCumplen = cumplimientoSummary.noCumplen;
        const totalPorActualizar = getUsuariosPorActualizarRows(data).length;
        const totalAgenciasSinEmpresa = getAgenciasSinEmpresaRows(data).length;
        const totalVendidoBruto = grossRows.reduce((sum, item) => sum + toIntegerAmount(item.ventas_mes_actual), 0);
        const totalIncentivoBruto = grossRows.reduce((sum, item) => sum + toIntegerAmount(item.nuevo_incentivo), 0);
        const empleadoIdSummary = getEmpleadoIdPagoSummary(data);
        const distribution = calculateAdministrativeDistribution(totalIncentivoBruto);
        const adminValor = distribution.totalAdministrativoCoordinador;
        const adminDistribucion = distribution.administrativo;
        const coordinadorDistribucion = distribution.coordinador;
        const fixedBagMissing = getFixedBagMissingBeforeTopUp(adminDistribucion);
        currentExcludedApplication = calculateExcludedApplication(fixedBagMissing);
        currentFixedBagTopUp = currentExcludedApplication.totalAplicado;
        const totalConAdmin = toIntegerAmount(totalIncentivoBruto + adminValor - currentExcludedApplication.totalRebajado);
        currentAdministrativePoolByEmpresa = buildAdministrativePoolByEmpresa(grossRows);
        currentDistributionBase = adminValor;
        currentAdministrativePoolBase = adminDistribucion;
        currentCoordinatorBase = coordinadorDistribucion;

        document.getElementById('ni_count_cumplen').textContent = totalCumplen;
        document.getElementById('ni_count_no_cumplen').textContent = totalNoCumplen;
        document.getElementById('ni_count_por_actualizar').textContent = totalPorActualizar;
        document.getElementById('ni_count_agencias_sin_empresa').textContent = totalAgenciasSinEmpresa;
        document.getElementById('ni_total_vendido').textContent = formatMoney(totalVendidoBruto);
        document.getElementById('ni_total_incentivo').textContent = formatMoney(totalIncentivoBruto);
        document.getElementById('ni_admin_resumen').textContent =
            '';
        document.getElementById('ni_admin_resumen').innerHTML =
            `<div>Porcentaje (${formatPercentDisplay(adminPctBruto)}%): ${formatMoney(adminValor)}</div>
            <div>Administrativo: ${formatMoney(adminDistribucion)}</div>
            <div>Coordinador: ${formatMoney(coordinadorDistribucion)}</div>
            <div>Reasignado a Operadores/Seguridad: ${formatMoney(currentFixedBagTopUp)}</div>
            <div>Deducciones detectadas: ${formatMoney(currentExcludedApplication.faltantesDisponible + currentExcludedApplication.desvinculadosDisponible + currentExcludedApplication.coordinadoresDisponible)}</div>
            <div>Coordinadores con monto en retención: ${formatMoney(currentExcludedApplication.coordinadoresDisponible)}</div>
            <div>Usuarios con IdEmpleado: ${empleadoIdSummary.usuariosConId.toLocaleString('en-US')} | ${formatMoney(empleadoIdSummary.montoConId)}</div>
            <div>Usuarios sin IdEmpleado: ${empleadoIdSummary.usuariosSinId.toLocaleString('en-US')} | ${formatMoney(empleadoIdSummary.montoSinId)}</div>
            <div>Rebaja neta por exclusiones: ${formatMoney(currentExcludedApplication.totalRebajado)}</div>`;
        document.getElementById('ni_total_con_admin').textContent =
            `Total a Pagar Final: ${formatMoney(totalConAdmin)}`;
        updateAdministrativeAndOperatorAmounts();
        updateCoordinatorAmounts();
        updatePuestoPctSummaryCard();
    }

    function renderPaymentTypeBreakdown(item) {
        const details = Array.isArray(item?.tipos_pago_detalle) ? item.tipos_pago_detalle : [];
        if (!details.length) {
            return '<span class="badge bg-light text-muted">General</span>';
        }

        return details.map((detail) => {
            const value = String(detail?.tipo_pago || '').replace('tramos_', '');
            const className = value === '60'
                ? 'bg-success-subtle text-success'
                : (value === '70' ? 'bg-info-subtle text-info' : 'bg-warning-subtle text-warning');
            const title = `Ventas del tipo: ${formatMoney(detail?.ventas)} | Base total de la escala: ${formatMoney(detail?.ventas_base_escala)} | Incentivo proporcional: ${formatMoney(detail?.incentivo)} | Dias: ${toNumber(detail?.dias)}`;

            return `<span class="badge ${className} me-1" title="${escapeHtml(title)}">${escapeHtml(value)}</span>`;
        }).join('');
    }

    function renderTableFromData(data) {
        if ($.fn.DataTable.isDataTable('#tableNuevoIncentivo')) {
            $('#tableNuevoIncentivo').DataTable().destroy();
        }

        const tableBody = document.querySelector('#tableNuevoIncentivo tbody');
        tableBody.innerHTML = '';

        data.forEach(item => {
            const meta = evaluateMetaMinima(item);
            const cumpleBadge = meta.cumplio
                ? '<span class="badge bg-success">CUMPLIO</span>'
                : `<span class="badge bg-danger">NO CUMPLE | Faltan ${formatMoney(meta.faltante)} (${meta.faltantePct.toFixed(2)}%)</span>`;

            const row = document.createElement('tr');
            row.innerHTML = `
                <td>${item.cedula}</td>
                <td>${escapeHtml(item.empleadoid || '')}</td>
                <td class="cell-nombre">${renderNombreEmpleado(item.nombre)}</td>
                <td>${escapeHtml(normalizeEmpresaLabel(item.empresa))}</td>
                <td>${renderPaymentTypeBreakdown(item)}</td>
                <td class="text-end">${formatMoney(item.ventas_ultimo_mes)}</td>
                <td class="text-end">${formatMoney(item.ventas_mes_actual)}</td>
                <td class="text-center">${item.dias_ventas_mes_actual ?? 0}</td>
                <td class="text-center">${renderHorasTotal(item.horas_total)}</td>
                <td>${cumpleBadge}</td>
                <td class="text-end">${formatMoney(item.nuevo_incentivo)}</td>
            `;
            tableBody.appendChild(row);
        });

        $('#tableNuevoIncentivo').DataTable({
            responsive: true,
            dom: 'Bfrtip',
            buttons: ['copy', 'csv', 'excel', 'pdf', 'print'],
            order: [[5, 'desc']],
            autoWidth: true,
            columnDefs: [
                { targets: 0, width: '7rem' },
                { targets: 1, width: '7rem' },
                { targets: 2, width: '13rem' },
                { targets: 3, width: '8rem' },
                { targets: 4, width: '7rem' },
                { targets: [5, 6, 10], width: '8.4rem', className: 'text-end' },
                { targets: 7, width: '4.4rem', className: 'text-center' },
                { targets: 8, width: '5.2rem', className: 'text-center' },
                { targets: 9, width: '9rem' },
            ],
            pageLength: 10000,
            scrollY: '500px',
            scrollX: true,
            scrollCollapse: true,
            language: {
                lengthMenu: 'Mostrar _MENU_ registros por pagina',
                info: 'Mostrando _START_ a _END_ de _TOTAL_ registros',
                infoEmpty: 'No hay registros disponibles',
                infoFiltered: '(filtrado de _MAX_ registros totales)',
                search: 'Buscar:',
                paginate: {
                    first: 'Primero',
                    last: 'Ultimo',
                    next: 'Siguiente',
                    previous: 'Anterior'
                }
            }
        });
    }

    function renderFaltantesIncentivoTable(rows) {
        const data = Array.isArray(rows) ? rows : [];

        if (tableFaltantesIncentivo) {
            tableFaltantesIncentivo.destroy();
            $('#tableFaltantesIncentivo tbody').empty();
        }

        tableFaltantesIncentivo = $('#tableFaltantesIncentivo').DataTable({
            data: data,
            columns: [
                { data: 'cedula' },
                {
                    data: 'nombre',
                    render: function(data, type) {
                        return type === 'display' ? renderNombreEmpleado(data) : data;
                    }
                },
                {
                    data: 'cantidad_faltantes',
                    className: 'text-center',
                    render: function(data, type) {
                        const value = Number(data || 0);
                        return type === 'display' ? value.toLocaleString('en-US') : value;
                    }
                },
                {
                    data: 'monto',
                    className: 'text-end',
                    render: function(data, type) {
                        const value = Number(data || 0);
                        return type === 'display' ? formatMoney(value) : value;
                    }
                }
            ],
            autoWidth: false,
            responsive: true,
            pageLength: 10,
            lengthMenu: [10, 25, 50],
            order: [[2, 'desc']],
            language: {
                lengthMenu: 'Mostrar _MENU_ registros por pagina',
                info: 'Mostrando _START_ a _END_ de _TOTAL_ registros',
                infoEmpty: 'No hay registros disponibles',
                infoFiltered: '(filtrado de _MAX_ registros totales)',
                search: 'Buscar:',
                zeroRecords: 'No hay faltantes para las cedulas consultadas',
                paginate: {
                    first: 'Primero',
                    last: 'Ultimo',
                    next: 'Siguiente',
                    previous: 'Anterior'
                }
            }
        });
    }

    function renderDesvinculadosIncentivoTable(rows) {
        const data = Array.isArray(rows) ? rows : [];

        if (tableDesvinculadosIncentivo) {
            tableDesvinculadosIncentivo.destroy();
            $('#tableDesvinculadosIncentivo tbody').empty();
        }

        tableDesvinculadosIncentivo = $('#tableDesvinculadosIncentivo').DataTable({
            data: data,
            columns: [
                { data: 'cedula' },
                {
                    data: 'empleadoid',
                    render: function(data, type) {
                        const value = String(data || '').trim();
                        return type === 'display' ? escapeHtml(value || '-') : value;
                    }
                },
                {
                    data: 'nombre',
                    render: function(data, type) {
                        return type === 'display' ? renderNombreEmpleado(data) : data;
                    }
                },
                {
                    data: 'estatus',
                    render: function(data, type) {
                        const value = String(data || '').trim();
                        return type === 'display' ? escapeHtml(value || 'Desvinculado') : value;
                    }
                },
                {
                    data: 'fecha_salida',
                    render: function(data, type) {
                        const value = String(data || '').trim();
                        return type === 'display' ? escapeHtml(value || '-') : value;
                    }
                }
            ],
            autoWidth: false,
            responsive: true,
            pageLength: 10,
            lengthMenu: [10, 25, 50],
            order: [[2, 'asc']],
            language: {
                lengthMenu: 'Mostrar _MENU_ registros por pagina',
                info: 'Mostrando _START_ a _END_ de _TOTAL_ registros',
                infoEmpty: 'No hay registros disponibles',
                infoFiltered: '(filtrado de _MAX_ registros totales)',
                search: 'Buscar:',
                zeroRecords: 'No hay usuarios desvinculados para las cedulas consultadas',
                paginate: {
                    first: 'Primero',
                    last: 'Ultimo',
                    next: 'Siguiente',
                    previous: 'Anterior'
                }
            }
        });
    }

    function renderUsuariosActualizarTable(rows) {
        const data = Array.isArray(rows) ? rows : [];

        if (tableUsuariosActualizar) {
            tableUsuariosActualizar.destroy();
            $('#tableUsuariosActualizar tbody').empty();
        }

        tableUsuariosActualizar = $('#tableUsuariosActualizar').DataTable({
            data: data,
            columns: [
                { data: 'cedula' },
                {
                    data: 'nombre',
                    render: function(data, type) {
                        return type === 'display' ? renderNombreEmpleado(data) : data;
                    }
                },
                {
                    data: 'empresa',
                    render: function(data, type) {
                        return type === 'display' ? escapeHtml(normalizeEmpresaLabel(data)) : data;
                    }
                },
                {
                    data: null,
                    render: function(data, type, row) {
                        return type === 'display' ? renderAgenciaFormato(row) : `${row?.ultima_agencia_nombre || ''} ${row?.ultima_terminal || ''}`;
                    }
                },
                {
                    data: 'ultimo_dia_venta',
                    render: function(data, type) {
                        return type === 'display' ? formatDateDisplay(data) : data;
                    }
                }
            ],
            autoWidth: false,
            responsive: true,
            pageLength: 10,
            lengthMenu: [10, 25, 50],
            order: [[3, 'desc']],
            language: {
                lengthMenu: 'Mostrar _MENU_ registros por pagina',
                info: 'Mostrando _START_ a _END_ de _TOTAL_ registros',
                infoEmpty: 'No hay registros disponibles',
                infoFiltered: '(filtrado de _MAX_ registros totales)',
                search: 'Buscar:',
                zeroRecords: 'No hay cedulas pendientes por actualizar',
                paginate: {
                    first: 'Primero',
                    last: 'Ultimo',
                    next: 'Siguiente',
                    previous: 'Anterior'
                }
            }
        });
    }

    function abrirUsuariosActualizarModal() {
        const pendientes = getUsuariosPorActualizarRows();

        if (!pendientes.length) {
            Swal.fire({
                title: 'Sin pendientes',
                text: 'No hay cedulas por actualizar en el maestro de empleados.',
                icon: 'info'
            });
            return;
        }

        renderUsuariosActualizarTable(pendientes);
        const modal = new bootstrap.Modal(document.getElementById('modalUsuariosActualizar'));
        modal.show();
    }

    function getAgenciasSinEmpresaRows(sourceRows = currentFilteredRows) {
        const rows = Array.isArray(sourceRows) ? sourceRows : [];
        const grouped = new Map();

        rows
            .filter(row => esAgenciaPorAsignarEmpresa(row))
            .forEach((row) => {
                const terminal = String(row?.ultima_terminal || 'SIN TERMINAL').trim() || 'SIN TERMINAL';
                const current = grouped.get(terminal) || {
                    terminal,
                    nombre_agencia: String(row?.ultima_agencia_nombre || 'SIN AGENCIA').trim() || 'SIN AGENCIA',
                    usuariosSet: new Set(),
                    ventas: 0,
                    ultimo_dia_venta: '',
                };
                const cedula = String(row?.cedula ?? '').replace(/\D+/g, '');
                if (cedula) {
                    current.usuariosSet.add(cedula);
                }
                current.ventas += toIntegerAmount(row?.ventas_mes_actual);
                const fecha = String(row?.ultimo_dia_venta || '').trim();
                if (fecha && (!current.ultimo_dia_venta || fecha > current.ultimo_dia_venta)) {
                    current.ultimo_dia_venta = fecha;
                    current.nombre_agencia = String(row?.ultima_agencia_nombre || current.nombre_agencia).trim() || current.nombre_agencia;
                }

                grouped.set(terminal, current);
            });

        return Array.from(grouped.values())
            .map(row => ({
                terminal: row.terminal,
                nombre_agencia: row.nombre_agencia,
                usuarios: row.usuariosSet.size,
                ventas: row.ventas,
                ultimo_dia_venta: row.ultimo_dia_venta,
            }))
            .sort((a, b) => toIntegerAmount(b.ventas) - toIntegerAmount(a.ventas));
    }

    function renderAgenciasSinEmpresaTable(rows) {
        const data = Array.isArray(rows) ? rows : [];

        if (tableAgenciasSinEmpresa) {
            tableAgenciasSinEmpresa.destroy();
            $('#tableAgenciasSinEmpresa tbody').empty();
        }

        tableAgenciasSinEmpresa = $('#tableAgenciasSinEmpresa').DataTable({
            data: data,
            columns: [
                { data: 'terminal' },
                { data: 'nombre_agencia' },
                {
                    data: 'usuarios',
                    className: 'text-center',
                    render: function(data, type) {
                        const value = Number(data || 0);
                        return type === 'display' ? value.toLocaleString('en-US') : value;
                    }
                },
                {
                    data: 'ventas',
                    className: 'text-end',
                    render: function(data, type) {
                        return type === 'display' ? formatMoney(data) : toIntegerAmount(data);
                    }
                },
                {
                    data: 'ultimo_dia_venta',
                    render: function(data, type) {
                        return type === 'display' ? formatDateDisplay(data) : data;
                    }
                }
            ],
            autoWidth: false,
            responsive: true,
            pageLength: 10,
            lengthMenu: [10, 25, 50],
            order: [[3, 'desc']],
            language: {
                lengthMenu: 'Mostrar _MENU_ registros por pagina',
                info: 'Mostrando _START_ a _END_ de _TOTAL_ registros',
                infoEmpty: 'No hay registros disponibles',
                infoFiltered: '(filtrado de _MAX_ registros totales)',
                search: 'Buscar:',
                zeroRecords: 'No hay agencias pendientes de empresa',
                paginate: {
                    first: 'Primero',
                    last: 'Ultimo',
                    next: 'Siguiente',
                    previous: 'Anterior'
                }
            }
        });
    }

    function abrirAgenciasSinEmpresaModal() {
        const pendientes = getAgenciasSinEmpresaRows();

        if (!pendientes.length) {
            Swal.fire({
                title: 'Sin pendientes',
                text: 'No hay agencias sin empresa en el reporte actual.',
                icon: 'info'
            });
            return;
        }

        renderAgenciasSinEmpresaTable(pendientes);
        const modal = new bootstrap.Modal(document.getElementById('modalAgenciasSinEmpresa'));
        modal.show();
    }

    function exportUsuariosActualizarExcel() {
        const pendientes = getUsuariosPorActualizarRows();

        exportRowsToCsv(
            pendientes,
            ['Cedula', 'Nombre', 'Empresa', 'Nombre agencia', 'Terminal', 'Ultimo dia con venta'],
            row => [
                row.cedula || '',
                row.nombre || 'Actualizar en maestro de empleados',
                normalizeEmpresaLabel(row.empresa),
                row.ultima_agencia_nombre || 'SIN AGENCIA',
                row.ultima_terminal || '',
                formatDateDisplay(row.ultimo_dia_venta),
            ],
            'usuarios_por_actualizar.csv'
        );
    }

    function exportAgenciasSinEmpresaExcel() {
        const pendientes = getAgenciasSinEmpresaRows();

        exportRowsToCsv(
            pendientes,
            ['Terminal', 'Nombre agencia', 'Usuarios', 'Ventas', 'Ultimo dia con venta'],
            row => [
                row.terminal || '',
                row.nombre_agencia || 'SIN AGENCIA',
                row.usuarios || 0,
                formatMoney(row.ventas),
                formatDateDisplay(row.ultimo_dia_venta),
            ],
            'agencias_por_asignar_empresa.csv'
        );
    }

    function reconocerTerminalesExcluidas() {
        const fileInput = document.getElementById('terminales_excluir_file');
        const manualInput = document.getElementById('terminales_excluir_manual');
        const hasFile = fileInput?.files && fileInput.files.length > 0;
        const manualText = String(manualInput?.value || '').trim();

        if (!hasFile && !manualText) {
            Swal.fire({ title: 'Datos requeridos', text: 'Selecciona un archivo o escribe al menos una terminal.', icon: 'warning' });
            return;
        }

        const formData = new FormData();
        if (hasFile) {
            formData.append('file', fileInput.files[0]);
        }
        formData.append('terminales_manual', manualText);
        formData.append('_token', csrfToken());

        Swal.fire({
            title: 'Reconociendo terminales',
            text: 'Analizando terminales para excluir...',
            allowOutsideClick: false,
            showConfirmButton: false,
            didOpen: () => Swal.showLoading()
        });

        fetch('/incentivos/calculo-de-incentivos/terminales-excluidas/reconocer', {
            method: 'POST',
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': csrfToken(),
            },
            credentials: 'same-origin',
            body: formData,
        })
            .then(response => parseResponseAsJson(response, 'Error reconociendo terminales excluidas'))
            .then(resp => {
                Swal.close();
                renderTerminalesExcluidasResultado(resp);
            })
            .catch(error => {
                recognizedTerminalesExcluidas = [];
                document.getElementById('btnAplicarTerminalesExcluidas').disabled = true;
                document.getElementById('resultadoTerminalesExcluidas').style.display = 'none';
                Swal.fire({ title: 'Error', text: error?.message || String(error), icon: 'warning' });
            });
    }

    async function aplicarTerminalesExcluidas() {
        const seleccionadas = Array.from(document.querySelectorAll('.terminal-excluida-check:checked'))
            .map(input => normalizeTerminalValue(input.value))
            .filter(Boolean);

        const recalcular = cachedRows.length
            && document.getElementById('ni_fecha_ini').value
            && document.getElementById('ni_fecha_fin').value;

        try {
            Swal.fire({
                title: 'Guardando exclusiones',
                text: 'Actualizando terminales_excluidas_incentivo...',
                allowOutsideClick: false,
                showConfirmButton: false,
                didOpen: () => Swal.showLoading()
            });

            const resp = await guardarTerminalesExcluidasIncentivo([...new Set(seleccionadas)]);
            excludedTerminales = new Set(Array.isArray(resp?.terminales) ? resp.terminales : seleccionadas);
            recognizedTerminalesExcluidas = [];
            persistExcludedTerminales();
            bootstrap.Modal.getInstance(document.getElementById('modalExcluirTerminales'))?.hide();

            await Swal.fire({
                title: 'Terminales excluidas',
                text: `${getExcludedTerminalesArray().length} terminales quedaron fijas para excluir del calculo.`,
                icon: 'success'
            });

            if (recalcular) {
                listNuevoIncentivo(false);
            }
        } catch (error) {
            Swal.fire({ title: 'Error', text: error?.message || String(error), icon: 'warning' });
        }
    }

    async function limpiarTerminalesExcluidas() {
        const recalcular = cachedRows.length
            && document.getElementById('ni_fecha_ini').value
            && document.getElementById('ni_fecha_fin').value;

        try {
            Swal.fire({
                title: 'Limpiando exclusiones',
                text: 'Actualizando terminales_excluidas_incentivo...',
                allowOutsideClick: false,
                showConfirmButton: false,
                didOpen: () => Swal.showLoading()
            });

            const resp = await guardarTerminalesExcluidasIncentivo([]);
            excludedTerminales = new Set(Array.isArray(resp?.terminales) ? resp.terminales : []);
            recognizedTerminalesExcluidas = [];
            persistExcludedTerminales();
            document.getElementById('resultadoTerminalesExcluidas').style.display = 'none';
            document.getElementById('btnAplicarTerminalesExcluidas').disabled = true;

            await Swal.fire({ title: 'Exclusiones limpiadas', text: 'No hay terminales excluidas para el proximo calculo.', icon: 'success' });

            if (recalcular) {
                listNuevoIncentivo(false);
            }
        } catch (error) {
            Swal.fire({ title: 'Error', text: error?.message || String(error), icon: 'warning' });
        }
    }

    function getBaseFilteredRows(options = {}) {
        const includeEmpresaFilter = options.includeEmpresaFilter !== false;
        const filtroCumplimiento = document.getElementById('ni_filtro_cumplimiento').value;
        const filtroEmpresa = document.getElementById('ni_filtro_empresa').value;

        let filtered = cachedRows.filter(item => toIntegerAmount(item?.ventas_mes_actual) > 0);
        if (filtroCumplimiento === 'cumplidos') {
            filtered = filtered.filter(item => evaluateMetaMinima(item).cumplio);
        } else if (filtroCumplimiento === 'no_cumplidos') {
            filtered = filtered.filter(item => !evaluateMetaMinima(item).cumplio);
        }

        if (includeEmpresaFilter && filtroEmpresa !== 'todos') {
            filtered = filtered.filter(item => normalizeEmpresaValue(item?.empresa) === filtroEmpresa);
        }

        return filtered;
    }

    function getCedulasReporteActual() {
        const sourceRows = currentFilteredRows;

        return [...new Set(sourceRows
            .filter(row => toIntegerAmount(row?.ventas_mes_actual) > 0)
            .map(row => getCedulaKey(row?.cedula))
            .filter(Boolean)
        )];
    }

    function getEmpleadoIdsReporteActual() {
        const sourceRows = currentFilteredRows;

        return [...new Set(sourceRows
            .filter(row => toIntegerAmount(row?.ventas_mes_actual) > 0)
            .map(row => getEmpleadoIdKey(row?.empleadoid))
            .filter(Boolean)
        )];
    }

    function getCedulasSinEmpleadoIdReporteActual() {
        const sourceRows = currentFilteredRows;

        return [...new Set(sourceRows
            .filter(row => toIntegerAmount(row?.ventas_mes_actual) > 0)
            .filter(row => !getEmpleadoIdKey(row?.empleadoid))
            .map(row => getCedulaKey(row?.cedula))
            .filter(Boolean)
        )];
    }

    function calcularMontoIncentivosPorCedulas(cedulas) {
        const cedulasSet = new Set((Array.isArray(cedulas) ? cedulas : [...cedulas])
            .map(getCedulaKey)
            .filter(Boolean));

        return currentFilteredRows
            .filter(row => cedulasSet.has(getCedulaKey(row?.cedula)))
            .reduce((sum, row) => sum + toIntegerAmount(row?.nuevo_incentivo), 0);
    }

    function calcularMontoIncentivosPorDesvinculados(cedulas, empleadoIds) {
        const cedulasSet = new Set((Array.isArray(cedulas) ? cedulas : [...cedulas])
            .map(getCedulaKey)
            .filter(Boolean));
        const empleadoIdsSet = new Set((Array.isArray(empleadoIds) ? empleadoIds : [...empleadoIds])
            .map(getEmpleadoIdKey)
            .filter(Boolean));

        return sumIncentivesByDesvinculados(currentFilteredRows, cedulasSet, empleadoIdsSet);
    }

    function aplicarFaltantesIncentivo() {
        if (!faltantesPeriodoConsultados) {
            Swal.fire({ title: 'Consulta pendiente', text: 'Primero debes consultar los faltantes del reporte.', icon: 'warning' });
            return;
        }

        if (!lastFaltantesCedulas.size) {
            faltantesPeriodoAplicados = true;
            updatePeriodoValidationState();
            bootstrap.Modal.getInstance(document.getElementById('modalFaltantesIncentivo'))?.hide();
            Swal.fire({ title: 'Faltantes validados', text: 'La consulta no encontró cédulas con faltantes.', icon: 'success' });
            return;
        }

        Swal.fire({
            title: 'Aplicando faltantes...',
            text: 'Estamos ocultando las cedulas con faltantes y recalculando el reporte.',
            icon: 'info',
            allowOutsideClick: false,
            showConfirmButton: false,
            didOpen: () => Swal.showLoading()
        });

        setTimeout(() => {
            excludedFaltantesCedulas = new Set([
                ...excludedFaltantesCedulas,
                ...[...lastFaltantesCedulas].map(getCedulaKey).filter(Boolean),
            ]);
            faltantesPeriodoAplicados = true;
            updatePeriodoValidationState();
            applyLocalFilters(false);
            bootstrap.Modal.getInstance(document.getElementById('modalFaltantesIncentivo'))?.hide();

            Swal.fire({
                title: 'Faltantes aplicados',
                text: `${lastFaltantesCedulas.size} cedulas fueron ocultadas. Aplicado a bolsa fija: ${formatMoney(currentExcludedApplication.aplicadoFaltantes)}. Rebaja neta: ${formatMoney(currentExcludedApplication.rebajadoFaltantes)}.`,
                icon: 'success'
            });
        }, 120);
    }

    function consultarFaltantesIncentivo() {
        if (!cachedRows.length) {
            Swal.fire({ title: 'Informacion', text: 'Primero debes generar el reporte.', icon: 'warning' });
            return;
        }

        const fechaIni = document.getElementById('ni_fecha_ini').value;
        const fechaFin = document.getElementById('ni_fecha_fin').value;
        const cedulas = getCedulasReporteActual();
        faltantesPeriodoConsultados = false;
        faltantesPeriodoAplicados = false;
        updatePeriodoValidationState();

        if (!fechaIni || !fechaFin) {
            Swal.fire({ title: 'Informacion', text: 'Debe seleccionar fecha inicio y fecha fin.', icon: 'warning' });
            return;
        }

        if (!cedulas.length) {
            Swal.fire({ title: 'Sin cedulas', text: 'No hay cedulas disponibles en el reporte actual.', icon: 'warning' });
            return;
        }

        Swal.fire({
            title: 'Consultando faltantes...',
            icon: 'info',
            allowOutsideClick: false,
            showConfirmButton: false,
            didOpen: () => Swal.showLoading()
        });

        fetch('/incentivos/calculo-de-incentivos/faltantes', {
            method: 'POST',
            headers: {
                'Accept': 'application/json',
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
            },
            body: JSON.stringify({
                cedulas: cedulas,
                fecha_ini: fechaIni,
                fecha_fin: fechaFin,
            }),
        })
            .then(response => parseResponseAsJson(response, 'Error consultando faltantes del incentivo'))
            .then(resp => {
                Swal.close();
                const faltantesRows = Array.isArray(resp.data) ? resp.data : [];
                lastFaltantesCedulas = new Set(faltantesRows
                    .map(row => getCedulaKey(row?.cedula))
                    .filter(Boolean));
                faltantesPeriodoConsultados = true;
                updatePeriodoValidationState();
                const montoIncentivosConFaltantes = calcularMontoIncentivosPorCedulas([...lastFaltantesCedulas]);

                document.getElementById('faltantesIncentivoTotalMonto').textContent = formatMoney(resp.total_monto || 0);
                document.getElementById('faltantesIncentivoTotalCantidad').textContent = Number(resp.total_faltantes || 0).toLocaleString('en-US');
                document.getElementById('faltantesIncentivoTotalCedulas').textContent = cedulas.length.toLocaleString('en-US');
                document.getElementById('faltantesIncentivoMontoIncentivos').textContent = formatMoney(montoIncentivosConFaltantes);
                document.getElementById('faltantesIncentivoRango').textContent = `Rango consultado: ${fechaIni} al ${fechaFin}`;
                renderFaltantesIncentivoTable(faltantesRows);

                const modal = new bootstrap.Modal(document.getElementById('modalFaltantesIncentivo'));
                modal.show();
            })
            .catch(error => {
                Swal.fire({ title: 'Error', text: error?.message || String(error), icon: 'warning' });
            });
    }

    function aplicarDesvinculadosIncentivo() {
        if (!desvinculadosPeriodoConsultados) {
            Swal.fire({ title: 'Consulta pendiente', text: 'Primero debes consultar los usuarios desvinculados.', icon: 'warning' });
            return;
        }

        if (!lastDesvinculadosCedulas.size && !lastDesvinculadosEmpleadoIds.size) {
            desvinculadosPeriodoAplicados = true;
            updatePeriodoValidationState();
            bootstrap.Modal.getInstance(document.getElementById('modalDesvinculadosIncentivo'))?.hide();
            Swal.fire({ title: 'Desvinculados validados', text: 'La consulta no encontró usuarios desvinculados.', icon: 'success' });
            return;
        }

        Swal.fire({
            title: 'Aplicando usuarios desvinculados...',
            text: 'Estamos ocultando los usuarios desvinculados y recalculando el reporte.',
            icon: 'info',
            allowOutsideClick: false,
            showConfirmButton: false,
            didOpen: () => Swal.showLoading()
        });

        setTimeout(() => {
            excludedDesvinculadosCedulas = new Set([
                ...excludedDesvinculadosCedulas,
                ...[...lastDesvinculadosCedulas].map(getCedulaKey).filter(Boolean),
            ]);
            excludedDesvinculadosEmpleadoIds = new Set([
                ...excludedDesvinculadosEmpleadoIds,
                ...[...lastDesvinculadosEmpleadoIds].map(getEmpleadoIdKey).filter(Boolean),
            ]);
            desvinculadosPeriodoAplicados = true;
            updatePeriodoValidationState();
            applyLocalFilters(false);
            bootstrap.Modal.getInstance(document.getElementById('modalDesvinculadosIncentivo'))?.hide();

            Swal.fire({
                title: 'Desvinculados aplicados',
                text: `${lastDesvinculadosCedulas.size} cedulas / ${lastDesvinculadosEmpleadoIds.size} ids fueron ocultados. Aplicado a bolsa fija: ${formatMoney(currentExcludedApplication.aplicadoDesvinculados)}. Rebaja neta: ${formatMoney(currentExcludedApplication.rebajadoDesvinculados)}.`,
                icon: 'success'
            });
        }, 120);
    }

    function consultarDesvinculadosIncentivo() {
        if (!cachedRows.length) {
            Swal.fire({ title: 'Informacion', text: 'Primero debes generar el reporte.', icon: 'warning' });
            return;
        }

        const cedulas = getCedulasReporteActual();
        desvinculadosPeriodoConsultados = false;
        desvinculadosPeriodoAplicados = false;
        updatePeriodoValidationState();

        if (!cedulas.length) {
            Swal.fire({ title: 'Sin cedulas', text: 'No hay cedulas disponibles en el reporte actual.', icon: 'warning' });
            return;
        }

        Swal.fire({
            title: 'Consultando usuarios desvinculados...',
            icon: 'info',
            allowOutsideClick: false,
            showConfirmButton: false,
            didOpen: () => Swal.showLoading()
        });

        fetch('/incentivos/calculo-de-incentivos/desvinculados', {
            method: 'POST',
            headers: {
                'Accept': 'application/json',
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
            },
            body: JSON.stringify({
                cedulas: cedulas,
            }),
        })
            .then(response => parseResponseAsJson(response, 'Error consultando usuarios desvinculados del incentivo'))
            .then(resp => {
                Swal.close();
                const rows = Array.isArray(resp.data) ? resp.data : [];
                lastDesvinculadosCedulas = new Set(rows
                    .map(row => getCedulaKey(row?.cedula))
                    .filter(Boolean));
                lastDesvinculadosEmpleadoIds = new Set();
                desvinculadosPeriodoConsultados = true;
                updatePeriodoValidationState();
                const montoIncentivosConDesvinculados = calcularMontoIncentivosPorDesvinculados(lastDesvinculadosCedulas, lastDesvinculadosEmpleadoIds);

                document.getElementById('desvinculadosIncentivoTotalUsuarios').textContent = Number(resp.total_desvinculados || 0).toLocaleString('en-US');
                document.getElementById('desvinculadosIncentivoTotalDesactivados').textContent = Number(resp.total_desactivados || 0).toLocaleString('en-US');
                document.getElementById('desvinculadosIncentivoTotalFechaSalida').textContent = Number(resp.total_con_fecha_salida || 0).toLocaleString('en-US');
                document.getElementById('desvinculadosIncentivoMontoIncentivos').textContent = formatMoney(montoIncentivosConDesvinculados);
                document.getElementById('desvinculadosIncentivoRango').textContent = `Cedulas consultadas en maestra: ${cedulas.length.toLocaleString('en-US')}`;
                renderDesvinculadosIncentivoTable(rows);

                const modal = new bootstrap.Modal(document.getElementById('modalDesvinculadosIncentivo'));
                modal.show();
            })
            .catch(error => {
                Swal.fire({ title: 'Error', text: error?.message || String(error), icon: 'warning' });
            });
    }

    function getAdministrativeDesvinculadosSourceRows() {
        return [
            ...administrativeRows.map((row) => ({ ...row, __tipo: 'admin' })),
            ...operatorRows.map((row) => ({ ...row, __tipo: 'operador' })),
        ].filter((row) => !isAdministrativeDesvinculadoExcluded(row));
    }

    function getModuleDesvinculadosAffectedRows(sourceRows, desvinculadosRows) {
        const empleadoIds = new Set((Array.isArray(desvinculadosRows) ? desvinculadosRows : [])
            .map((row) => getEmpleadoIdKey(row?.empleadoid))
            .filter(Boolean));
        const cedulas = new Set((Array.isArray(desvinculadosRows) ? desvinculadosRows : [])
            .map((row) => getCedulaKey(row?.cedula))
            .filter(Boolean));

        return (Array.isArray(sourceRows) ? sourceRows : []).filter((row) => {
            const empleadoId = getEmpleadoIdKey(row?.empleadoid);
            if (empleadoId) {
                return empleadoIds.has(empleadoId);
            }

            const cedula = getCedulaKey(row?.cedula);
            return cedula ? cedulas.has(cedula) : false;
        });
    }

    function applyModuleDesvinculadosRows(tipo, affectedRows) {
        const isAdmin = tipo === 'administrativos';
        const cedulasSet = isAdmin ? excludedAdministrativeDesvinculadosCedulas : excludedCoordinatorDesvinculadosCedulas;
        const empleadoIdsSet = isAdmin ? excludedAdministrativeDesvinculadosEmpleadoIds : excludedCoordinatorDesvinculadosEmpleadoIds;

        affectedRows.forEach((row) => {
            const empleadoId = getEmpleadoIdKey(row?.empleadoid);
            if (empleadoId) {
                empleadoIdsSet.add(empleadoId);
                return;
            }

            const cedula = getCedulaKey(row?.cedula);
            if (cedula) {
                cedulasSet.add(cedula);
            }
        });

        if (isAdmin) {
            renderAdministrativeCategoryTable();
            updateAdministrativeAndOperatorAmounts();
        } else {
            renderCoordinatorTable();
            updateCoordinatorAmounts();
        }
    }

    function consultarDesvinculadosModuloPago(tipo) {
        const isAdmin = tipo === 'administrativos';
        const label = isAdmin ? 'administrativos' : 'coordinadores';
        const sourceRows = isAdmin ? getAdministrativeDesvinculadosSourceRows() : getCoordinatorDisplayRows();
        const cedulas = [...new Set(sourceRows.map((row) => getCedulaKey(row?.cedula)).filter(Boolean))];
        const empleadoids = [...new Set(sourceRows.map((row) => getEmpleadoIdKey(row?.empleadoid)).filter(Boolean))];
        const cedulasConsulta = isAdmin ? cedulas : [];

        if (!isAdmin && !empleadoids.length) {
            Swal.fire({
                title: 'Sin IdEmpleado',
                text: 'No hay IdEmpleado en coordinadores para validar desvinculados sin mezclar agentes de venta.',
                icon: 'info',
            });
            return;
        }

        if (!cedulasConsulta.length && !empleadoids.length) {
            Swal.fire({
                title: 'Sin datos',
                text: `No hay cedulas ni IdEmpleado en ${label} para consultar desvinculados.`,
                icon: 'info',
            });
            return;
        }

        Swal.fire({
            title: `Consultando desvinculados de ${label}...`,
            text: 'Estamos validando la maestra de empleados.',
            icon: 'info',
            allowOutsideClick: false,
            showConfirmButton: false,
            didOpen: () => Swal.showLoading()
        });

        fetch('/incentivos/calculo-de-incentivos/desvinculados', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrfToken(),
            },
            body: JSON.stringify({ cedulas: cedulasConsulta, empleadoids }),
        })
            .then(response => parseResponseAsJson(response, `Error consultando desvinculados de ${label}`))
            .then(resp => {
                const desvinculadosRows = Array.isArray(resp.data) ? resp.data : [];
                const affectedRows = getModuleDesvinculadosAffectedRows(sourceRows, desvinculadosRows);
                const monto = affectedRows.reduce((sum, row) => (
                    sum + (isAdmin
                        ? toIntegerAmount(getAdministrativeAmountByRow(row))
                        : toIntegerAmount(getCoordinatorDisplayAmount(row)))
                ), 0);

                if (!affectedRows.length) {
                    Swal.fire({
                        title: 'Sin desvinculados',
                        text: `No se encontraron ${label} desactivados o con fecha de salida en la maestra.`,
                        icon: 'info',
                    });
                    return;
                }

                Swal.fire({
                    title: `Aplicar desvinculados de ${label}`,
                    html: `
                        <div class="text-start">
                            <div><strong>Registros a limpiar:</strong> ${affectedRows.length.toLocaleString('en-US')}</div>
                            <div><strong>Monto afectado:</strong> ${formatMoney(monto)}</div>
                            <div><strong>Desactivados:</strong> ${Number(resp.total_desactivados || 0).toLocaleString('en-US')}</div>
                            <div><strong>Con fecha de salida:</strong> ${Number(resp.total_con_fecha_salida || 0).toLocaleString('en-US')}</div>
                        </div>
                    `,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Aplicar',
                    cancelButtonText: 'Cancelar',
                }).then((result) => {
                    if (!result.isConfirmed) {
                        return;
                    }

                    applyModuleDesvinculadosRows(tipo, affectedRows);
                    Swal.fire({
                        title: 'Desvinculados aplicados',
                        text: `${affectedRows.length} registros fueron limpiados de ${label}.`,
                        icon: 'success',
                    });
                });
            })
            .catch(error => {
                Swal.fire({ title: 'Error', text: error?.message || String(error), icon: 'warning' });
            });
    }

    function applyLocalFilters(showFilterAlert = false) {
        if (!cachedRows.length) {
            Swal.fire({ title: 'Informacion', text: 'Primero debes generar el reporte.', icon: 'warning' });
            return;
        }

        const sistema = document.getElementById('ni_sistema').value;
        const tipoPago = document.getElementById('ni_tipo_pago').value;
        const modoCalculo = document.getElementById('ni_modo_calculo').value;
        const alcanceProductos = document.getElementById('ni_alcance_productos').value;

        if (cachedSistema !== sistema || cachedTipoPago !== tipoPago || cachedModoCalculo !== modoCalculo || cachedAlcanceProductos !== alcanceProductos) {
            Swal.fire({ title: 'Informacion', text: 'Cambiaste el sistema, tipo de pago, modo de calculo o alcance. Presiona \"Generar Reporte\" para recargar datos.', icon: 'info' });
            return;
        }

        let filtered = getBaseFilteredRows();
        filtered = filtered.filter(item => !isExcludedFromMainIncentiveTable(item));

        currentFilteredRows = filtered;
        renderTableFromData(filtered);
        updateCardsFromData(filtered);
        const modalAdministrativos = document.getElementById('modalAdministrativos');
        if (modalAdministrativos && modalAdministrativos.classList.contains('show')) {
            renderAdministrativeCategoryTable();
        }
        const modalCoordinadores = document.getElementById('modalCoordinadores');
        if (modalCoordinadores && modalCoordinadores.classList.contains('show')) {
            renderCoordinatorTable();
        }

        document.getElementById('ni_rango_evaluado').textContent =
            `Mes evaluado: ${cachedMeta.eval_ini || ''} al ${cachedMeta.eval_fin || ''} | Modo: ${cachedMeta.modo_calculo_label || 'General consolidado'} | Alcance: ${cachedMeta.alcance_productos_label || 'Reporte completo'}${renderResumenEmpresas(cachedMeta)}`;

        if (showFilterAlert) {
            Swal.fire({
                toast: true,
                position: 'top-end',
                icon: 'success',
                title: 'Filtros aplicados en memoria',
                showConfirmButton: false,
                timer: 1800,
                timerProgressBar: true
            });
        }
    }

    document.addEventListener('DOMContentLoaded', () => {
        const fechaHoy = new Date();
        const yyyy = fechaHoy.getFullYear();
        const mm = String(fechaHoy.getMonth() + 1).padStart(2, '0');
        const dd = String(fechaHoy.getDate()).padStart(2, '0');

        document.getElementById('ni_fecha_fin').value = `${yyyy}-${mm}-${dd}`;
        document.getElementById('ni_fecha_ini').value = `${yyyy}-${mm}-01`;
        updatePuestoPctSummaryCard();
        populateEmpresaFilterOptions([]);
        updateExcludedTerminalesCount();

        document.querySelector('#btnConfigPct').addEventListener('click', function() {
            renderRangesTable();
            const modal = new bootstrap.Modal(document.getElementById('modalConfigPct'));
            modal.show();
        });
        document.querySelector('#btnConfigPuestoPct').addEventListener('click', function() {
            renderPuestoPctInputs();
            const modal = new bootstrap.Modal(document.getElementById('modalConfigPuestoPct'));
            modal.show();
        });
        document.querySelector('#btnConfigAdminPct').addEventListener('click', function() {
            if (!CAN_CONFIG_ADMIN_PCT) {
                Swal.fire({
                    title: 'Acceso restringido',
                    text: 'Solo superadmin puede modificar este porcentaje.',
                    icon: 'warning'
                });
                return;
            }

            document.getElementById('admin_pct_bruto').value = adminPctBruto;
            const modal = new bootstrap.Modal(document.getElementById('modalConfigAdminPct'));
            modal.show();
        });

        document.querySelector('#btnConfigAdministrativos').addEventListener('click', function() {
            renderAdministrativeCategoryTable();
            const modal = new bootstrap.Modal(document.getElementById('modalAdministrativos'));
            modal.show();
        });

        document.querySelector('#btnAdminFiltroTodos').addEventListener('click', function() {
            administrativeGroupFilter = 'todos';
            renderAdministrativeCategoryTable();
        });

        document.querySelector('#btnAdminFiltroG1').addEventListener('click', function() {
            administrativeGroupFilter = '1. Gtes. Y Encarg.';
            renderAdministrativeCategoryTable();
        });

        document.querySelector('#btnAdminFiltroG2').addEventListener('click', function() {
            administrativeGroupFilter = '2. Monitoreo';
            renderAdministrativeCategoryTable();
        });

        document.querySelector('#btnAdminFiltroG45').addEventListener('click', function() {
            administrativeGroupFilter = '4_5';
            renderAdministrativeCategoryTable();
        });

        document.querySelector('#btnConfigCoordinadores').addEventListener('click', function() {
            renderCoordinatorTable();
            const modal = new bootstrap.Modal(document.getElementById('modalCoordinadores'));
            modal.show();
        });

        document.querySelector('#btnConfigHorasTotal').addEventListener('click', function() {
            document.getElementById('horas_total_minimo').value = horasTotalMinimo;
            const modal = new bootstrap.Modal(document.getElementById('modalConfigHorasTotal'));
            modal.show();
        });

        document.querySelector('#btnExcluirTerminales').addEventListener('click', function() {
            const aplicarBtn = document.getElementById('btnAplicarTerminalesExcluidas');
            document.getElementById('terminales_excluir_file').value = '';
            document.getElementById('terminales_excluir_manual').value = '';
            recognizedTerminalesExcluidas = [];
            aplicarBtn.disabled = true;
            renderTerminalesExcluidasActuales();

            const modal = new bootstrap.Modal(document.getElementById('modalExcluirTerminales'));
            modal.show();
        });

        document.querySelector('#btnReconocerTerminalesExcluidas').addEventListener('click', function() {
            reconocerTerminalesExcluidas();
        });

        document.querySelector('#btnAplicarTerminalesExcluidas').addEventListener('click', function() {
            aplicarTerminalesExcluidas();
        });

        document.querySelector('#btnLimpiarTerminalesExcluidas').addEventListener('click', function() {
            limpiarTerminalesExcluidas();
        });

        document.querySelector('#terminales_excluir_file').addEventListener('change', function() {
            recognizedTerminalesExcluidas = [];
            document.getElementById('btnAplicarTerminalesExcluidas').disabled = true;
            document.getElementById('resultadoTerminalesExcluidas').style.display = 'none';
        });

        document.querySelector('#btnConsultarFaltantes').addEventListener('click', function() {
            consultarFaltantesIncentivo();
        });

        document.querySelector('#btnConsultarDesvinculados').addEventListener('click', function() {
            consultarDesvinculadosIncentivo();
        });

        document.querySelector('#btnAplicarFaltantesIncentivo').addEventListener('click', function() {
            aplicarFaltantesIncentivo();
        });

        document.querySelector('#btnAplicarDesvinculadosIncentivo').addEventListener('click', function() {
            aplicarDesvinculadosIncentivo();
        });

        document.querySelector('#ni_count_por_actualizar').addEventListener('click', function() {
            abrirUsuariosActualizarModal();
        });

        document.querySelector('#ni_count_agencias_sin_empresa').addEventListener('click', function() {
            abrirAgenciasSinEmpresaModal();
        });

        document.getElementById('modalFaltantesIncentivo').addEventListener('shown.bs.modal', function() {
            if (tableFaltantesIncentivo) {
                tableFaltantesIncentivo.columns.adjust().responsive.recalc();
            }
        });

        document.getElementById('modalDesvinculadosIncentivo').addEventListener('shown.bs.modal', function() {
            if (tableDesvinculadosIncentivo) {
                tableDesvinculadosIncentivo.columns.adjust().responsive.recalc();
            }
        });

        document.getElementById('modalUsuariosActualizar').addEventListener('shown.bs.modal', function() {
            if (tableUsuariosActualizar) {
                tableUsuariosActualizar.columns.adjust().responsive.recalc();
            }
        });

        document.getElementById('modalAgenciasSinEmpresa').addEventListener('shown.bs.modal', function() {
            if (tableAgenciasSinEmpresa) {
                tableAgenciasSinEmpresa.columns.adjust().responsive.recalc();
            }
        });

        document.querySelector('#btnExportUsuariosActualizarExcel').addEventListener('click', function() {
            exportUsuariosActualizarExcel();
        });

        document.querySelector('#btnExportAgenciasSinEmpresaExcel').addEventListener('click', function() {
            exportAgenciasSinEmpresaExcel();
        });

        document.querySelector('#tbodyCoordinadores').addEventListener('click', function(event) {
            const button = event.target.closest('.btn-ver-detalle-coord');
            if (!button) {
                return;
            }

            const idx = parseInt(button.dataset.idx, 10);
            const row = coordinatorRows[idx];
            if (!row) {
                return;
            }

            renderCoordinatorDetailTable(row);
            bootstrap.Modal.getInstance(document.getElementById('modalCoordinadores'))?.hide();
            const detailModal = new bootstrap.Modal(document.getElementById('modalCoordinadorDetalle'));
            detailModal.show();
        });

        document.querySelector('#btnBackToCoordinadores').addEventListener('click', function() {
            bootstrap.Modal.getInstance(document.getElementById('modalCoordinadorDetalle'))?.hide();
            const modal = new bootstrap.Modal(document.getElementById('modalCoordinadores'));
            modal.show();
        });

        document.querySelector('#btnExportCoordinadorDetalleExcel').addEventListener('click', function() {
            exportCoordinatorDetailExcel();
        });

        document.querySelector('#btnExportCoordinadorDetallePdf').addEventListener('click', function() {
            exportCoordinatorDetailPdf();
        });

        document.querySelector('#tbodyAdministrativos').addEventListener('input', function(event) {
            const input = event.target;
            if (input.tagName === 'SELECT') {
                return;
            }

            const idx = parseInt(input.dataset.idx, 10);
            const field = input.dataset.field;
            if (!field || Number.isNaN(idx)) return;

            if (input.classList.contains('admin-input')) {
                if (!administrativeRows[idx]) return;
                administrativeRows[idx][field] = readAdministrativeConfigInputValue(administrativeRows[idx], field, input.value);
                updateAdministrativeAndOperatorAmounts();
                return;
            }

            if (input.classList.contains('op-input')) {
                if (!operatorRows[idx]) return;
                operatorRows[idx][field] = readAdministrativeConfigInputValue(operatorRows[idx], field, input.value);
                updateAdministrativeAndOperatorAmounts();
            }
        });

        document.querySelector('#tbodyAdministrativos').addEventListener('change', function(event) {
            const input = event.target;
            const idx = parseInt(input.dataset.idx, 10);
            const field = input.dataset.field;

            if (field && !Number.isNaN(idx) && (input.classList.contains('admin-input') || input.classList.contains('op-input'))) {
                const rows = input.classList.contains('op-input') ? operatorRows : administrativeRows;
                if (!rows[idx]) return;

                const wasFixedGroup = isFixedAdministrativeGroup(rows[idx].grupo);
                rows[idx][field] = readAdministrativeConfigInputValue(rows[idx], field, input.value);

                if (field === 'grupo') {
                    const isNowFixedGroup = isFixedAdministrativeGroup(rows[idx].grupo);
                    if (wasFixedGroup !== isNowFixedGroup) {
                        rows[idx].pct = 0;
                    }

                    rebalanceAdministrativeRows();
                    renderAdministrativeCategoryTable();
                }

                updateAdministrativeAndOperatorAmounts();
            }

            if (input.classList.contains('admin-pct-input') || input.classList.contains('op-pct-input')) {
                const rows = input.classList.contains('op-input') ? operatorRows : administrativeRows;
                input.value = rows[idx] ? formatAdministrativeConfigValue(rows[idx]) : (parseFloat(input.value || 0) || 0).toFixed(2);
            }
        });

        document.querySelector('#tbodyAdministrativos').addEventListener('click', function(event) {
            const button = event.target.closest('.btn-delete-admin-row');
            if (!button) {
                return;
            }

            eliminarFilaAdministrativa(button.dataset.tipo, button.dataset.idx);
        });

        document.querySelector('#btnAgregarAdministrativoFila').addEventListener('click', function() {
            agregarFilaAdministrativa();
        });

        document.querySelector('#btnGuardarAdministrativosPlantilla').addEventListener('click', function() {
            guardarAdministrativosPlantilla();
        });

        document.querySelector('#btnRestaurarAdministrativos').addEventListener('click', function() {
            administrativeRows = getDefaultAdministrativeRows();
            operatorRows = getDefaultOperatorRows();
            excludedAdministrativeDesvinculadosCedulas = new Set();
            excludedAdministrativeDesvinculadosEmpleadoIds = new Set();
            administrativeGroupFilter = 'todos';
            renderAdministrativeCategoryTable();
            updateAdministrativeAndOperatorAmounts();
        });

        document.querySelector('#tbodyCoordinadores').addEventListener('input', function(event) {
            const input = event.target;
            if (!input.classList.contains('coord-input')) {
                return;
            }

            const idx = parseInt(input.dataset.idx, 10);
            const field = input.dataset.field;
            if (!coordinatorRows[idx] || !field) {
                return;
            }

            coordinatorRows[idx][field] = field === 'pct'
                ? (Math.max(0, parseFloat(input.value || 0) || 0) / 100)
                : input.value;

            updateCoordinatorAmounts();
        });

        document.querySelector('#tbodyCoordinadores').addEventListener('change', function(event) {
            const input = event.target;
            if (input.classList.contains('coord-pct-input')) {
                input.value = (parseFloat(input.value || 0) || 0).toFixed(2);
            }
        });

        document.querySelector('#btnRestaurarCoordinadores').addEventListener('click', function() {
            coordinatorRows = getDefaultCoordinatorRows();
            coordinatorUserDetailsByCoordinator = {};
            excludedCoordinatorIds = new Set();
            appliedCoordinatorAmounts = {};
            excludedCoordinatorDesvinculadosCedulas = new Set();
            excludedCoordinatorDesvinculadosEmpleadoIds = new Set();

            if (cachedRows.length) {
                applyLocalFilters(false);
            } else {
                renderCoordinatorTable();
            }
        });

        document.querySelector('#btnAplicarCoordinadoresBolsa').addEventListener('click', function() {
            aplicarCoordinadoresBolsa();
        });

        document.querySelector('#btnDesvinculadosAdministrativos').addEventListener('click', function() {
            consultarDesvinculadosModuloPago('administrativos');
        });

        document.querySelector('#btnDesvinculadosCoordinadores').addEventListener('click', function() {
            consultarDesvinculadosModuloPago('coordinadores');
        });

        document.querySelector('#btnExportAdministrativosExcel').addEventListener('click', function() {
            exportAdministrativosExcel();
        });

        document.querySelector('#btnValidacionGerencial').addEventListener('click', function() {
            generarPdfValidacionGerencial();
        });

        document.querySelector('#btnInformeGerencialProceso').addEventListener('click', function() {
            seleccionarPdfInformeGerencialProceso();
        });

        document.querySelector('#btnDetalleCalendarioPdf').addEventListener('click', function() {
            generarPdfDetalleCalendario();
        });

        document.querySelector('#btnExportCoordinadoresExcel').addEventListener('click', function() {
            exportCoordinadoresExcel();
        });

        document.querySelector('#btnInformeCoordinadoresPdf').addEventListener('click', function() {
            generarPdfInformeCoordinadores();
        });

        document.querySelector('#btnRestaurarTramos').addEventListener('click', function() {
            const tipoPago = document.getElementById('ni_tipo_pago').value;
            payoutRangesByType[tipoPago] = getDefaultRanges()[tipoPago];
            renderRangesTable();
        });

        document.querySelector('#btnGuardarPct').addEventListener('click', function() {
            try {
                const tipoPago = document.getElementById('ni_tipo_pago').value;
                payoutRangesByType[tipoPago] = readRangesFromTable();
            } catch (e) {
                Swal.fire({ title: 'Validacion', text: e.message, icon: 'warning' });
                return;
            }

            bootstrap.Modal.getInstance(document.getElementById('modalConfigPct'))?.hide();
            Swal.fire({ title: 'Configuracion guardada', text: 'Los tramos se aplicaran al generar el reporte.', icon: 'success' });
        });

        document.querySelector('#btnGuardarAdminPct').addEventListener('click', function() {
            if (!CAN_CONFIG_ADMIN_PCT) {
                Swal.fire({
                    title: 'Acceso restringido',
                    text: 'Solo superadmin puede guardar este porcentaje.',
                    icon: 'warning'
                });
                return;
            }

            adminPctBruto = parseFloat(document.getElementById('admin_pct_bruto').value || 0);
            bootstrap.Modal.getInstance(document.getElementById('modalConfigAdminPct'))?.hide();

            if (
                cachedRows.length
                && cachedSistema === document.getElementById('ni_sistema').value
                && cachedTipoPago === document.getElementById('ni_tipo_pago').value
                && cachedModoCalculo === document.getElementById('ni_modo_calculo').value
                && cachedAlcanceProductos === document.getElementById('ni_alcance_productos').value
            ) {
                applyLocalFilters(false);
            } else {
                currentDistributionBase = 0;
                currentAdministrativePoolBase = 0;
                currentAdministrativePoolByEmpresa = {};
                currentAdministrativeBase = 0;
                currentOperatorBase = 0;
                currentCoordinatorBase = 0;
                currentFixedBagTopUp = 0;
                currentExcludedApplication = getEmptyExcludedApplication();
                document.getElementById('ni_admin_resumen').innerHTML =
                    `<div>Porcentaje (${formatPercentDisplay(adminPctBruto)}%): 0</div>
                    <div>Administrativo: 0</div>
                    <div>Coordinador: 0</div>`;
                document.getElementById('ni_total_con_admin').textContent = 'Total a Pagar Final: 0';
                updateAdministrativeAndOperatorAmounts();
                updateCoordinatorAmounts();
                updatePuestoPctSummaryCard();
            }

            Swal.fire({
                title: 'Configuracion guardada',
                text: 'El % administrativo se aplica sobre el Total Incentivo a Pagar.',
                icon: 'success'
            });
        });

        document.querySelector('#btnGuardarPuestoPct').addEventListener('click', function() {
            readPuestoPctInputs();
            updatePuestoPctSummaryCard();
            updateAdministrativeAndOperatorAmounts();
            bootstrap.Modal.getInstance(document.getElementById('modalConfigPuestoPct'))?.hide();

            Swal.fire({
                title: 'Configuracion guardada',
                text: '% por categoria guardado correctamente.',
                icon: 'success'
            });
        });

        document.querySelector('#btnGuardarHorasTotal').addEventListener('click', function() {
            const nuevoMinimo = toNumber(document.getElementById('horas_total_minimo').value);
            if (nuevoMinimo < 0) {
                Swal.fire({ title: 'Validacion', text: 'El minimo de horas no puede ser negativo.', icon: 'warning' });
                return;
            }

            horasTotalMinimo = nuevoMinimo;
            localStorage.setItem('incentivos_administrativos_horas_total_minimo', String(horasTotalMinimo));
            bootstrap.Modal.getInstance(document.getElementById('modalConfigHorasTotal'))?.hide();

            if (currentFilteredRows.length) {
                renderTableFromData(currentFilteredRows);
            }

            Swal.fire({
                title: 'Configuracion guardada',
                text: `Minimo de horas configurado en ${formatHours(horasTotalMinimo)}.`,
                icon: 'success'
            });
        });
    });

    function listNuevoIncentivo(showFilterAlert = false) {
        const sistema = document.getElementById('ni_sistema').value;
        const fechaIni = document.getElementById('ni_fecha_ini').value;
        const fechaFin = document.getElementById('ni_fecha_fin').value;
        const minDias = document.getElementById('ni_min_dias').value;
        const tipoPago = document.getElementById('ni_tipo_pago').value;
        const modoCalculo = document.getElementById('ni_modo_calculo').value;
        const alcanceProductos = document.getElementById('ni_alcance_productos').value;

        if (!fechaIni || !fechaFin) {
            Swal.fire({ title: 'Informacion', text: 'Debe seleccionar fecha inicio y fecha fin.', icon: 'warning' });
            return;
        }

        Swal.fire({
            title: 'Procesando Informacion ...',
            icon: 'info',
            allowOutsideClick: false,
            showConfirmButton: false,
            timerProgressBar: true,
            didOpen: () => Swal.showLoading()
        });

        $('#tableNuevoIncentivo tbody').empty();
        currentFilteredRows = [];
        lastFaltantesCedulas = new Set();
        excludedFaltantesCedulas = new Set();
        lastDesvinculadosCedulas = new Set();
        lastDesvinculadosEmpleadoIds = new Set();
        excludedDesvinculadosCedulas = new Set();
        excludedDesvinculadosEmpleadoIds = new Set();
        excludedAdministrativeDesvinculadosCedulas = new Set();
        excludedAdministrativeDesvinculadosEmpleadoIds = new Set();
        excludedCoordinatorDesvinculadosCedulas = new Set();
        excludedCoordinatorDesvinculadosEmpleadoIds = new Set();
        excludedCoordinatorIds = new Set();
        appliedCoordinatorAmounts = {};
        currentFixedBagTopUp = 0;
        currentExcludedApplication = getEmptyExcludedApplication();
        resetPeriodoValidationState();

        const params = new URLSearchParams({
            sistema: sistema,
            filtro_cumplimiento: 'todos',
            fecha_ini: fechaIni,
            fecha_fin: fechaFin,
            min_dias_venta: minDias,
            tipo_pago: tipoPago,
            modo_calculo: modoCalculo,
            alcance_productos: alcanceProductos,
            rangos_pago: JSON.stringify(payoutRangesByType[tipoPago]),
            rangos_pago_por_tipo: JSON.stringify(payoutRangesByType),
            terminales_excluidas: JSON.stringify(getExcludedTerminalesArray()),
        });
        const reportSignature = getReportConfigurationSignature();

        fetch('/incentivos/calculo-de-incentivos/reporte?' + params.toString(), {
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
            },
        })
            .then(response => parseResponseAsJson(response, 'Error consultando incentivos administrativos'))
            .then(resp => {
                if ('message' in resp) {
                    Swal.fire({ title: 'Informacion', text: resp.message, icon: 'warning' });
                    return;
                }

                const data = normalizeIntegerReportRows(resp.data || []);
                cachedRows = data;
                cachedMeta = resp.meta || {};
                updateCoordinatorValidAgencies(cachedMeta);
                cachedSistema = sistema;
                cachedTipoPago = tipoPago;
                cachedModoCalculo = modoCalculo;
                cachedAlcanceProductos = alcanceProductos;
                cachedFechaInicio = fechaIni;
                cachedFechaFin = fechaFin;
                cachedReportSignature = reportSignature;
                populateEmpresaFilterOptions(cachedRows);
                updatePeriodoValidationState();

                Swal.close();
                applyLocalFilters(showFilterAlert);
            })
            .catch(error => {
                Swal.fire({ title: 'Error', text: error?.message || String(error), icon: 'warning' });
            });
    }

    function filtrarCumplimientoTabla() {
        applyLocalFilters(true);
    }

    document.querySelector('#btnGenerarNuevoIncentivo').addEventListener('click', function() {
        bootstrap.Modal.getOrCreateInstance(document.getElementById('modalAlcanceReporte')).show();
    });

    document.querySelector('#modalAlcanceReporte').addEventListener('click', function(event) {
        const button = event.target.closest('.js-generar-reporte-alcance');
        if (!button) {
            return;
        }

        document.getElementById('ni_alcance_productos').value = button.dataset.alcance;
        updatePeriodoValidationState();
        bootstrap.Modal.getInstance(document.getElementById('modalAlcanceReporte'))?.hide();
        listNuevoIncentivo();
    });

    document.querySelector('#btnGuardarPeriodoIncentivo').addEventListener('click', function() {
        guardarPeriodoIncentivo();
    });

    document.querySelector('#btnGenerarExcelPago').addEventListener('click', function() {
        seleccionarDescargaExcelPago();
    });

    document.querySelector('#btnPreNomina').addEventListener('click', function() {
        abrirModalPreNomina();
    });

    document.querySelector('#modalPreNomina').addEventListener('click', function(event) {
        const button = event.target.closest('button[data-tipo]');
        if (!button) {
            return;
        }

        if (button.dataset.formato === 'excel') {
            generarExcelPreNomina(button.dataset.tipo, button.dataset.empresa || '');
            return;
        }

        generarPdfPreNomina(button.dataset.tipo, button.dataset.empresa || '');
    });

    document.querySelector('#btnFiltrarCumplimiento').addEventListener('click', function() {
        filtrarCumplimientoTabla();
    });

    document.querySelector('#btnCalendarioTiposPago').addEventListener('click', function() {
        const modal = new bootstrap.Modal(document.getElementById('modalCalendarioTiposPago'));
        modal.show();
        loadCalendarPaymentGrid();
    });

    document.querySelector('#btnCargarCalendarioPago').addEventListener('click', function() {
        loadCalendarPaymentGrid();
    });

    document.querySelector('#calendarioBuscarTerminal').addEventListener('keydown', function(event) {
        if (event.key === 'Enter') {
            event.preventDefault();
            loadCalendarPaymentGrid();
        }
    });

    document.querySelector('#btnAplicarTipoMasivo').addEventListener('click', function() {
        applyBulkCalendarPaymentType();
    });

    document.querySelector('#btnReconocerTerminalesCalendario').addEventListener('click', function() {
        recognizeCalendarTerminals();
    });

    document.querySelector('#btnLimpiarTerminalesCalendario').addEventListener('click', function() {
        clearRecognizedCalendarTerminalScenario();
    });

    document.querySelector('#btnAplicarTerminalesReconocidas').addEventListener('click', function() {
        applyRecognizedCalendarTerminals();
    });

    document.querySelector('#calendarioAlcanceTerminales').addEventListener('change', function() {
        updateCalendarBulkDateControls();
    });

    document.querySelector('#calendarioSeleccionarTerminalesReconocidas').addEventListener('change', function() {
        document.querySelectorAll('.calendario-terminal-reconocida').forEach((checkbox) => {
            checkbox.checked = this.checked;
        });
    });

    document.querySelector('#btnGuardarCalendarioPago').addEventListener('click', function() {
        saveCalendarPaymentChanges();
    });

    document.querySelector('#calendarioPaginaAnterior').addEventListener('click', function() {
        const page = Number(calendarPaymentPagination.pagina_actual || 1);
        if (page > 1) {
            loadCalendarPaymentGrid(page - 1, true, false);
        }
    });

    document.querySelector('#calendarioPaginaSiguiente').addEventListener('click', function() {
        const page = Number(calendarPaymentPagination.pagina_actual || 1);
        const lastPage = Number(calendarPaymentPagination.ultima_pagina || 1);
        if (page < lastPage) {
            loadCalendarPaymentGrid(page + 1, true, false);
        }
    });

    document.querySelector('#calendarioPorPagina').addEventListener('change', function() {
        loadCalendarPaymentGrid(1, true, false);
    });

</script>
@endsection
