@extends('app')

@section('content')
    <div class="main-content">
        <div class="page-content">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-12">
                        <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                            <h4 class="mb-sm-0">Reporte Nuevo Incentivo V3</h4>
                            <div class="page-title-right">
                                <ol class="breadcrumb m-0">
                                    <li class="breadcrumb-item"><a href="{{ route('inicio.index') }}">Inicio</a></li>
                                    <li class="breadcrumb-item"><a href="{{ route('incentivos.index') }}">Incentivos</a></li>
                                    <li class="breadcrumb-item active">Reporte Nuevo Incentivo V3</li>
                                </ol>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-lg-4 col-md-6">
                        <div class="card card-animate">
                            <div class="card-body">
                                <p class="text-uppercase fw-medium text-muted mb-1">Total Vendido</p>
                                <h4 class="mb-0" id="ni_total_vendido">0.00</h4>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-4 col-md-6">
                        <div class="card card-animate">
                            <div class="card-body d-flex justify-content-between align-items-center">
                                <div>
                                    <p class="text-uppercase fw-medium text-muted mb-1">Usuarios que Cumplieron</p>
                                    <h4 class="mb-0 text-success" id="ni_count_cumplen">0</h4>
                                </div>
                                <div>
                                    <p class="text-uppercase fw-medium text-muted mb-1">Usuarios que No Cumplieron</p>
                                    <h4 class="mb-0 text-danger" id="ni_count_no_cumplen">0</h4>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-4 col-md-6">
                        <div class="card card-animate">
                            <div class="card-body text-start" style="min-height: 178px;">
                                <p class="text-uppercase fw-medium text-muted mb-1">Total Incentivo a Pagar</p>
                                <h4 class="mb-0" id="ni_total_incentivo">0.00</h4>
                                <div class="d-block mt-1 fw-semibold fs-5 text-primary text-start" id="ni_admin_resumen">
                                    <div>Porcentaje (0%): 0.00</div>
                                    <div>Administrativo: 0.00</div>
                                    <div>Coordinador: 0.00</div>
                                </div>
                                <div class="mt-2 fw-bold fs-4 text-success text-start" id="ni_total_con_admin">Total a Pagar Final: 0.00</div>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-12">
                        <div class="card">
                            <div class="card-header d-flex align-items-center justify-content-between">
                                <div>
                                    <h5 class="card-title mb-0">Calculo por sistema y tipo de pago (V3)</h5>
                                    <small class="text-muted">Configura tramos de venta mensual por pago a 60, 70 u 80.</small>
                                </div>
                                <div class="d-flex gap-3 align-items-end flex-wrap">
                                    <div>
                                        <label class="mb-0" for="ni_sistema">Sistema</label>
                                        <select id="ni_sistema" class="form-select">
                                            <option value="Todos">Todos</option>
                                            <option value="Lotobet">Lotobet Real</option>
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
                                        <label class="mb-0" for="ni_min_dias">Mín. días venta</label>
                                        <input type="number" id="ni_min_dias" class="form-control" value="10" min="1" step="1">
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
                                    <button type="button" class="btn btn-soft-secondary" id="btnConfigAdminPct">Porcentaje</button>
                                    <button type="button" class="btn btn-soft-secondary" id="btnConfigAdministrativos">Administrativo</button>
                                    <button type="button" class="btn btn-soft-secondary" id="btnConfigOperadores">Operadores</button>
                                    <button type="button" class="btn btn-soft-secondary" id="btnConfigCoordinadores">Coordinador</button>
                                    <button type="button" class="btn btn-primary" id="btnGenerarNuevoIncentivo">Generar Reporte</button>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="mb-2 text-muted" id="ni_rango_evaluado"></div>
                                <table id="tableNuevoIncentivo" class="table table-bordered dt-responsive nowrap table-striped align-middle" style="width:100%">
                                    <thead>
                                        <tr>
                                            <th>Cédula</th>
                                            <th>Ventas Último Mes</th>
                                            <th>Ventas Mes Actual</th>
                                            <th>Días Ventas Mes Actual</th>
                                            <th>Cumple Regla</th>
                                            <th>Pago Escala</th>
                                            <th>Nuevo Incentivo</th>
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
                    <input type="number" id="admin_pct_bruto" class="form-control" value="0" min="0" step="0.01">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-primary" id="btnGuardarAdminPct">Guardar</button>
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
                        <div class="col-md-6">
                            <div class="border rounded p-2">
                                <small class="text-muted d-block">Bolsa administrativa total</small>
                                <strong id="admin_base_total">0.00</strong>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="border rounded p-2">
                                <small class="text-muted d-block">Monto para Administrativo</small>
                                <strong id="admin_distribuido_total">0.00</strong>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label mb-1" for="adminGrupoCalculo">Calcular administrativo por</label>
                            <select id="adminGrupoCalculo" class="form-select">
                                <option value="todos">Todos los grupos</option>
                                <option value="1. Gtes. Y Encarg.">1. Gtes. Y Encarg.</option>
                                <option value="2. Monitoreo">2. Monitoreo</option>
                                <option value="5. Servs. Tecnicos">5. Servs. Tecnicos</option>
                            </select>
                        </div>
                    </div>
                    <div class="table-responsive" style="max-height: 520px;">
                        <table class="table table-bordered table-sm align-middle mb-0">
                            <thead class="table-light sticky-top">
                                <tr>
                                    <th style="min-width: 180px;">Grupo</th>
                                    <th style="min-width: 260px;">Nombre</th>
                                    <th style="min-width: 140px;">Empresa</th>
                                    <th style="min-width: 120px;">% Total</th>
                                    <th style="min-width: 160px;">Monto Administrativo</th>
                                </tr>
                            </thead>
                            <tbody id="tbodyAdministrativos"></tbody>
                            <tfoot class="table-light">
                                <tr>
                                    <th colspan="4" class="text-end">Total</th>
                                    <th class="text-end" id="admin_col_total">0.00</th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-success" id="btnExportAdministrativosExcel">Excel</button>
                    <button type="button" class="btn btn-soft-secondary" id="btnRestaurarAdministrativos">Restaurar plantilla</button>
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cerrar</button>
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
                                <strong id="coord_base_total">0.00</strong>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="border rounded p-2">
                                <small class="text-muted d-block">Total distribuido</small>
                                <strong id="coord_distribuido_total">0.00</strong>
                            </div>
                        </div>
                    </div>
                    <div class="table-responsive" style="max-height: 520px;">
                        <table class="table table-bordered table-sm align-middle mb-0">
                            <thead class="table-light sticky-top">
                                <tr>
                                    <th style="min-width: 280px;">Nombre</th>
                                    <th style="min-width: 100px;">Agencias</th>
                                    <th style="min-width: 100px;">Válidas</th>
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
                    <button type="button" class="btn btn-success" id="btnExportCoordinadoresExcel">Excel</button>
                    <button type="button" class="btn btn-soft-secondary" id="btnRestaurarCoordinadores">Restaurar plantilla</button>
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cerrar</button>
                </div>
            </div>
        </div>
    </div>

    <div id="modalOperadores" class="modal fade" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Desglose Operadores</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <div class="border rounded p-2">
                                <small class="text-muted d-block">Bolsa administrativa total</small>
                                <strong id="op_base_total">0.00</strong>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="border rounded p-2">
                                <small class="text-muted d-block">Monto para Operadores</small>
                                <strong id="op_distribuido_total">0.00</strong>
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
                                    <th style="min-width: 120px;">% Total</th>
                                    <th style="min-width: 160px;">Monto Operador</th>
                                </tr>
                            </thead>
                            <tbody id="tbodyOperadores"></tbody>
                            <tfoot class="table-light">
                                <tr>
                                    <th colspan="4" class="text-end">Total</th>
                                    <th class="text-end" id="op_col_total">0.00</th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-success" id="btnExportOperadoresExcel">Excel</button>
                    <button type="button" class="btn btn-soft-secondary" id="btnRestaurarOperadores">Restaurar plantilla</button>
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
                    <button type="button" class="btn btn-soft-secondary" id="btnBackToCoordinadores">Atras</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('script')
<script>
    function buildRanges(percent, pagos) {
        return [
            { desde: 100001, hasta: 150000, pago: pagos[0], tipo: 'fijo' },
            { desde: 150001, hasta: 250000, pago: pagos[1], tipo: 'fijo' },
            { desde: 250001, hasta: 400000, pago: pagos[2], tipo: 'fijo' },
            { desde: 400001, hasta: 550000, pago: pagos[3], tipo: 'fijo' },
            { desde: 550001, hasta: 700000, pago: pagos[4], tipo: 'fijo' },
            { desde: 700001, hasta: 850000, pago: pagos[5], tipo: 'fijo' },
            { desde: 850001, hasta: 1000000, pago: pagos[6], tipo: 'fijo' },
            { desde: 1000001, hasta: 5000000, pago: percent, tipo: 'porcentaje' },
            { desde: 5000001, hasta: null, pago: percent, tipo: 'porcentaje' },
        ];
    }

    function getDefaultRanges() {
        return {
            tramos_60: buildRanges(1, [500, 1000, 2000, 4000, 6000, 8000, 9000]),
            tramos_70: buildRanges(0.75, [375, 750, 1500, 3000, 4500, 6000, 6750]),
            tramos_80: buildRanges(0.5, [250, 500, 1000, 2000, 3000, 4000, 4500]),
        };
    }

    function getDefaultAdministrativeRows() {
        return [
            ["1. Gtes. Y Encarg.", "Aramis Morel Arroyo", "BSH Support", 0.0849724584486235],
            ["1. Gtes. Y Encarg.", "Yulaine Echevarria", "BSH Support", 0.05],
            ["1. Gtes. Y Encarg.", "Johanset Batista", "BSH Support", 0.0445551328578509],
            ["1. Gtes. Y Encarg.", "Ciprian Rafael Beard Almonte", "BSH Support", 0.043],
            ["1. Gtes. Y Encarg.", "Renzo Figueroa", "BSH Support", 0.04],
            ["2. Monitoreo", "Geldhis Paola Acosta Carrion", "BSH Support", 0.016],
            ["2. Monitoreo", "Joselania Olivo García", "BSH Support", 0.016],
            ["2. Monitoreo", "Kengripher Junior De Oleo Belen", "BSH Support", 0.016],
            ["2. Monitoreo", "María Liriano", "BSH Support", 0.016],
            ["2. Monitoreo", "Stefanny Onasi Webster", "BSH Support", 0.016],
            ["2. Monitoreo", "Reidy Reynoso Melendez", "BSH Support", 0.016],
            ["4. Operadores", "LUIS JAVIER MARTINEZ", "BSH Support", 0.0173540487874658],
            ["4. Operadores", "Richard Guzman Herrera", "BSH Support", 0.0173540487874658],
            ["4. Operadores", "Armandalis Baez Cuevas", "BSH Support", 0.0173540487874658],
            ["4. Operadores", "Erick Payano", "BSH Support", 0.0173540487874658],
            ["4. Operadores", "Pedro Minier", "BSH Support", 0.0173540487874658],
            ["4. Operadores", "Valentin Mieses", "BSH Support", 0.0173540487874658],
            ["4. Operadores", "Feliz Alexander Mateo", "BSH Support", 0],
            ["4. Operadores", "Jonathan Peralta", "BSH Support", 0.0173540487874658],
            ["4. Operadores", "Gabriel Martinez De Jesus (2 meses)", "BSH Support", 0.0173540487874658],
            ["4. Operadores", "Juan Luis Montaño", "BSH Support", 0.0173540487874658],
            ["4. Operadores", "Wilmer Jose Corporán\nColon", "BSH Support", 0.0173540487874658],
            ["4. Operadores", "Santo Tibrey Rosario (1 mes)", "BSH Support", 0.0173540487874658],
            ["4. Operadores", "Hector Manuel Medina", "BSH Support", 0.0173540487874658],
            ["4. Operadores", "Jose Luis Rodriguez", "BSH Support", 0.0173540487874658],
            ["4. Operadores", "Juan Geronimo Garcia", "BSH Support", 0.0173540487874658],
            ["4. Operadores", "Alexander Cepeda", "BSH Support", 0.0173540487874658],
            ["4. Operadores", "Juan Francisco Mejía", "BSH Support", 0.0194685692013614],
            ["4. Operadores", "Victor Manuel de la Cruz", "BSH Support", 0.0194685692013614],
            ["4. Operadores", "Sterling Miguel Bello Bello", "BSH Support", 0.0194685692013614],
            ["4. Operadores", "Starlin Arredondo Martinez", "BSH Support", 0.0194685692013614],
            ["4. Operadores", "Santico Julian Willian Fenelon", "BSH Support", 0.0194685692013614],
            ["4. Operadores", "Wilkin Cabral", "BSH Support", 0.0194685692013614],
            ["4. Operadores", "Rosmery Celeste Nú ñ ez naranjo", "BSH Support", 0.0194685692013614],
            ["4. Operadores", "Osvaldo Antonio Moreno Perez", "BSH Support", 0.0194685692013614],
            ["5. Servs. Tecnicos", "Bryan Jose Rodriguez de Jesus", "BSH Support", 0.0150275415513765],
            ["5. Servs. Tecnicos", "Juan Francisco De La Cruz Aracena", "BSH Support", 0.0150275415513765],
            ["5. Servs. Tecnicos", "Brian Oriel Robles Tolentino", "BSH Support", 0.0150275415513765],
            ["5. Servs. Tecnicos", "Daniel Matos Gomez", "BSH Support", 0.0150275415513765],
            ["1. Servs. Tecnicos", "Harlin Cerda Cerda", "BSH Support", 0.0300550831027529],
            ["5. Servs. Tecnicos", "Dymitri Joseph", "BSH Support", 0.0150275415513765],
            ["5. Servs. Tecnicos", "Isidro Valdez Pascual", "BSH Support", 0.0150275415513765],
            ["5. Servs. Tecnicos", "Rudys Bernardo Cabrera", "BSH Support", 0.0150275415513765],
            ["5. Servs. Tecnicos", "Socrates Osiris Morban Piña", "BSH Support", 0.0150275415513765],
            ["5. Servs. Tecnicos", "Jose Antonio De Peña De La Cruz", "BSH Support", 0.0150275415513765],
            ["5. Servs. Tecnicos", "Juan Perez Desena", "BSH Support", 0.0150275415513765],
            ["5. Servs. Tecnicos", "Jose Antonio Polanco Vasquez", "BSH Support", 0.0150275415513765],
            ["5. Servs. Tecnicos", "Josué Johan Martinez Mancebo", "BSH Support", 0.0150275415513765],
            ["5. Servs. Tecnicos", "Pedro Hector Santana Mateo", "BSH Support", 0.0150275415513765],
            ["1. Gtes. Y Encarg.", "Erick junior Velasquez gomez", "Consorcio SH", 0.124049587695015],
            ["1. Gtes. Y Encarg.", "Yukaine Echavarria", "Consorcio SH", 0.035],
            ["1. Gtes. Y Encarg.", "Aramis \nMorel Arroyo", "Consorcio SH", 0.045],
            ["1. Gtes. Y Encarg.", "Johanset Batista", "Consorcio SH", 0.03],
            ["2. Monitoreo", "Altagracia karina Vallejo Bueno", "Consorcio SH", 0.04],
            ["2. Monitoreo", "Leyshi Y. Beras", "Consorcio SH", 0.03],
            ["2. Monitoreo", "Geldis Acosto", "Consorcio SH", 0.01],
            ["2. Monitoreo", "Joselanea Reynoso", "Consorcio SH", 0.01],
            ["2. Monitoreo", "Junior de Oleo", "Consorcio SH", 0.01],
            ["2. Monitoreo", "María Liriano", "Consorcio SH", 0.01],
            ["2. Monitoreo", "Reidy Reynoso", "Consorcio SH", 0.01],
            ["2. Monitoreo", "yolaidi Arias", "Consorcio SH", 0.03],
            ["4. Operadores", "Hector De Regla Mariñez Diaz", "Consorcio SH", 0.03],
            ["4. Operadores", "Sherlyn Lexander Perdomo valdez", "Consorcio SH", 0.03],
            ["4. Operadores", "Sayi Virginia Marmolejos", "Consorcio SH", 0.00893353511893479],
            ["4. Operadores", "Leydi Guerrero", "Consorcio SH", 0.0183070385116862],
            ["4. Operadores", "Alba Yris Tapia Morillo", "Consorcio SH", 0.0183070385116862],
            ["4. Operadores", "Yenifer Estefani Baez Cordero", "Consorcio SH", 0.0183070385116862],
            ["4. Operadores", "Yudekis Tejeda Tejeda", "Consorcio SH", 0.0183070385116862],
            ["4. Operadores", "Hector Guerrero Tejeda", "Consorcio SH", 0.0183070385116862],
            ["4. Operadores", "Sandra Diomare Arias Garcia", "Consorcio SH", 0.0183070385116862],
            ["4. Operadores", "Yaniris Reynoso", "Consorcio SH", 0.0183070385116862],
            ["4. Operadores", "jerry alejandro anziani arias", "Consorcio SH", 0.0183070385116862],
            ["4. Operadores", "Victor Baez", "Consorcio SH", 0.0183070385116862],
            ["4. Operadores", "Carla yasmel Barias baez", "Consorcio SH", 0.0183070385116862],
            ["4. Operadores", "Estafani Espinosa", "Consorcio SH", 0.0183070385116862],
            ["4. Operadores", "FElix Rosario", "Consorcio SH", 0.0140923312152275],
            ["4. Operadores", "Rosabel Arias de Lara", "Consorcio SH", 0.0140923312152275],
            ["4. Operadores", "Alex Yadir Figuereo Abreu", "Consorcio SH", 0.0140923312152275],
            ["4. Operadores", "Rafaela Brito", "Consorcio SH", 0.0140923312152275],
            ["4. Operadores", "Samir Ferrera", "Consorcio SH", 0.0127621930270497],
            ["4. Operadores", "Martin alejandro fernandez bello", "Consorcio SH", 0.00719861317352419],
            ["4. Operadores", "Victor julio Galvan montero", "Consorcio SH", 0.00719861317352419],
            ["4. Operadores", "Raulin Matos", "Consorcio SH", 0.00780783001427268],
            ["4. Operadores", "Nestor Antonio Terrero", "Consorcio SH", 0.00780783001427268],
            ["4. Operadores", "Leivin Pina Feliz", "Consorcio SH", 0.00780783001427268],
            ["4. Operadores", "Jeison Manuel Feliz Feliz", "Consorcio SH", 0.00780783001427268],
            ["4. Operadores", "José vega", "Consorcio SH", 0.00780783001427268],
            ["4. Operadores", "Georges gabriel reyes cuevas", "Consorcio SH", 0.00780783001427268],
            ["4. Operadores", "yeandrys dileissy Ortiz perez", "Consorcio SH", 0.00780783001427268],
            ["4. Operadores", "Silvia patricia batista diaz", "Consorcio SH", 0.00780783001427268],
            ["4. Operadores", "Fermin brito De leon", "Consorcio SH", 0.00808769747562137],
            ["4. Operadores", "Manuel Emilio Pérez", "Consorcio SH", 0.00808769747562137],
            ["4. Operadores", "Manuel Emilio Rivas", "Consorcio SH", 0.00808769747562137],
            ["4. Operadores", "Marco daniel Méndez gonzalez", "Consorcio SH", 0.00808769747562137],
            ["4. Operadores", "waner omar diaz sena", "Consorcio SH", 0.00808769747562137],
            ["4. Operadores", "Zenaldo gregorio volquez perez", "Consorcio SH", 0.006],
            ["4. Operadores", "Wilton feliz Alcantara pienda", "Consorcio SH", 0.0127621930270497],
            ["4. Operadores", "Deibi Garcia Made", "Consorcio SH", 0.0127621930270497],
            ["4. Operadores", "Pedro Suero", "Consorcio SH", 0.0045266991406722],
            ["4. Operadores", "Martires Suero Flores", "Consorcio SH", 0.0045266991406722],
            ["4. Operadores", "Melvin Moreno", "Consorcio SH", 0.0045266991406722],
            ["4. Operadores", "Pedro Suero Rosario", "Consorcio SH", 0.004],
            ["4. Operadores", "Martires Suero Flores", "Consorcio SH", 0.004],
            ["4. Operadores", "Melvin Francisco Moreno Suero", "Consorcio SH", 0.004],
            ["5. Servs. Tecnicos", "Julio Inocencio Dominguez Perez", "Consorcio SH", 0.0173444076495108],
            ["5. Servs. Tecnicos", "Raulin Guerrero", "Consorcio SH", 0.02809917539003],
            ["5. Servs. Tecnicos", "Comas Abreu Marcos", "Consorcio SH", 0.00867220382475538],
            ["5. Servs. Tecnicos", "Wendi Reyes", "Consorcio SH", 0.0094685238738982],
            ["5. Servs. Tecnicos", "Henry Matos", "Consorcio SH", 0.0094685238738982],
            ["5. Servs. Tecnicos", "Ignacio Nivar Victoriano", "Consorcio SH", 0.006],
            ["5. Servs. Tecnicos", "LUIS ENRIQUE MERAN MORA", "Consorcio SH", 0.0234159794916916],
        ].map(function (row) {
            return {
                grupo: row[0],
                nombre: row[1],
                empresa: row[2],
                pct: row[3],
            };
        });
    }

    function getDefaultCoordinatorRows() {
        return @json($coordinadores ?? []);
    }

    function getDefaultOperatorRows() {
        return [
    {
        "grupo": "4. Operadores",
        "nombre": "LUIS JAVIER MARTINEZ",
        "empresa": "BSH Support",
        "pct": 0.017354048787466
    },
    {
        "grupo": "4. Operadores",
        "nombre": "Richard Guzman Herrera",
        "empresa": "BSH Support",
        "pct": 0.017354048787466
    },
    {
        "grupo": "4. Operadores",
        "nombre": "Armandalis Baez Cuevas",
        "empresa": "BSH Support",
        "pct": 0.017354048787466
    },
    {
        "grupo": "4. Operadores",
        "nombre": "Erick Payano",
        "empresa": "BSH Support",
        "pct": 0.017354048787466
    },
    {
        "grupo": "4. Operadores",
        "nombre": "Pedro Minier",
        "empresa": "BSH Support",
        "pct": 0.017354048787466
    },
    {
        "grupo": "4. Operadores",
        "nombre": "Valentin Mieses",
        "empresa": "BSH Support",
        "pct": 0.017354048787466
    },
    {
        "grupo": "4. Operadores",
        "nombre": "Jonathan Peralta",
        "empresa": "BSH Support",
        "pct": 0.017354048787466
    },
    {
        "grupo": "4. Operadores",
        "nombre": "Gabriel Martinez De Jesus (2 meses)",
        "empresa": "BSH Support",
        "pct": 0.017354048787466
    },
    {
        "grupo": "4. Operadores",
        "nombre": "Juan Luis Montaño",
        "empresa": "BSH Support",
        "pct": 0.017354048787466
    },
    {
        "grupo": "4. Operadores",
        "nombre": "Wilmer Jose Corporán Colon",
        "empresa": "BSH Support",
        "pct": 0.017354048787466
    },
    {
        "grupo": "4. Operadores",
        "nombre": "Santo Tibrey Rosario (1 mes)",
        "empresa": "BSH Support",
        "pct": 0.017354048787466
    },
    {
        "grupo": "4. Operadores",
        "nombre": "Hector Manuel Medina",
        "empresa": "BSH Support",
        "pct": 0.017354048787466
    },
    {
        "grupo": "4. Operadores",
        "nombre": "Jose Luis Rodriguez",
        "empresa": "BSH Support",
        "pct": 0.017354048787466
    },
    {
        "grupo": "4. Operadores",
        "nombre": "Juan Geronimo Garcia",
        "empresa": "BSH Support",
        "pct": 0.017354048787466
    },
    {
        "grupo": "4. Operadores",
        "nombre": "Alexander Cepeda",
        "empresa": "BSH Support",
        "pct": 0.017354048787466
    },
    {
        "grupo": "4. Operadores",
        "nombre": "Juan Francisco Mejía",
        "empresa": "BSH Support",
        "pct": 0.019468569201361
    },
    {
        "grupo": "4. Operadores",
        "nombre": "Victor Manuel de la Cruz",
        "empresa": "BSH Support",
        "pct": 0.019468569201361
    },
    {
        "grupo": "4. Operadores",
        "nombre": "Sterling Miguel Bello Bello",
        "empresa": "BSH Support",
        "pct": 0.019468569201361
    },
    {
        "grupo": "4. Operadores",
        "nombre": "Starlin Arredondo Martinez",
        "empresa": "BSH Support",
        "pct": 0.019468569201361
    },
    {
        "grupo": "4. Operadores",
        "nombre": "Santico Julian Willian Fenelon",
        "empresa": "BSH Support",
        "pct": 0.019468569201361
    },
    {
        "grupo": "4. Operadores",
        "nombre": "Wilkin Cabral",
        "empresa": "BSH Support",
        "pct": 0.019468569201361
    },
    {
        "grupo": "4. Operadores",
        "nombre": "Rosmery Celeste Nú ñ ez naranjo",
        "empresa": "BSH Support",
        "pct": 0.019468569201361
    },
    {
        "grupo": "4. Operadores",
        "nombre": "Osvaldo Antonio Moreno Perez",
        "empresa": "BSH Support",
        "pct": 0.019468569201361
    },
    {
        "grupo": "4. Operadores",
        "nombre": "Hector De Regla",
        "empresa": "Consorcio SH",
        "pct": 0.03
    },
    {
        "grupo": "4. Operadores",
        "nombre": "Sherlyn Lexander",
        "empresa": "Consorcio SH",
        "pct": 0.03
    },
    {
        "grupo": "4. Operadores",
        "nombre": "Sayi Virginia",
        "empresa": "Consorcio SH",
        "pct": 0.0089335351189348
    },
    {
        "grupo": "4. Operadores",
        "nombre": "Leydi",
        "empresa": "Consorcio SH",
        "pct": 0.018307038511686
    },
    {
        "grupo": "4. Operadores",
        "nombre": "Alba Yris",
        "empresa": "Consorcio SH",
        "pct": 0.018307038511686
    },
    {
        "grupo": "4. Operadores",
        "nombre": "Yenifer Estefani",
        "empresa": "Consorcio SH",
        "pct": 0.018307038511686
    },
    {
        "grupo": "4. Operadores",
        "nombre": "Yudekis Tejeda",
        "empresa": "Consorcio SH",
        "pct": 0.018307038511686
    },
    {
        "grupo": "4. Operadores",
        "nombre": "Hector",
        "empresa": "Consorcio SH",
        "pct": 0.018307038511686
    },
    {
        "grupo": "4. Operadores",
        "nombre": "Sandra Diomare",
        "empresa": "Consorcio SH",
        "pct": 0.018307038511686
    },
    {
        "grupo": "4. Operadores",
        "nombre": "Yaniris",
        "empresa": "Consorcio SH",
        "pct": 0.018307038511686
    },
    {
        "grupo": "4. Operadores",
        "nombre": "jerry alejandro",
        "empresa": "Consorcio SH",
        "pct": 0.018307038511686
    },
    {
        "grupo": "4. Operadores",
        "nombre": "Victor",
        "empresa": "Consorcio SH",
        "pct": 0.018307038511686
    },
    {
        "grupo": "4. Operadores",
        "nombre": "Carla yasmel",
        "empresa": "Consorcio SH",
        "pct": 0.018307038511686
    },
    {
        "grupo": "4. Operadores",
        "nombre": "Estafani",
        "empresa": "Consorcio SH",
        "pct": 0.018307038511686
    },
    {
        "grupo": "4. Operadores",
        "nombre": "FElix",
        "empresa": "Consorcio SH",
        "pct": 0.014092331215227
    },
    {
        "grupo": "4. Operadores",
        "nombre": "Rosabel",
        "empresa": "Consorcio SH",
        "pct": 0.014092331215227
    },
    {
        "grupo": "4. Operadores",
        "nombre": "Alex Yadir",
        "empresa": "Consorcio SH",
        "pct": 0.014092331215227
    },
    {
        "grupo": "4. Operadores",
        "nombre": "Rafaela",
        "empresa": "Consorcio SH",
        "pct": 0.014092331215227
    },
    {
        "grupo": "4. Operadores",
        "nombre": "Samir",
        "empresa": "Consorcio SH",
        "pct": 0.01276219302705
    },
    {
        "grupo": "4. Operadores",
        "nombre": "Martin alejandro",
        "empresa": "Consorcio SH",
        "pct": 0.0071986131735242
    },
    {
        "grupo": "4. Operadores",
        "nombre": "Victor julio",
        "empresa": "Consorcio SH",
        "pct": 0.0071986131735242
    },
    {
        "grupo": "4. Operadores",
        "nombre": "Raulin",
        "empresa": "Consorcio SH",
        "pct": 0.0078078300142727
    },
    {
        "grupo": "4. Operadores",
        "nombre": "Nestor Antonio",
        "empresa": "Consorcio SH",
        "pct": 0.0078078300142727
    },
    {
        "grupo": "4. Operadores",
        "nombre": "Leivin",
        "empresa": "Consorcio SH",
        "pct": 0.0078078300142727
    },
    {
        "grupo": "4. Operadores",
        "nombre": "Jeison Manuel",
        "empresa": "Consorcio SH",
        "pct": 0.0078078300142727
    },
    {
        "grupo": "4. Operadores",
        "nombre": "José",
        "empresa": "Consorcio SH",
        "pct": 0.0078078300142727
    },
    {
        "grupo": "4. Operadores",
        "nombre": "Georges gabriel",
        "empresa": "Consorcio SH",
        "pct": 0.0078078300142727
    },
    {
        "grupo": "4. Operadores",
        "nombre": "yeandrys dileissy",
        "empresa": "Consorcio SH",
        "pct": 0.0078078300142727
    },
    {
        "grupo": "4. Operadores",
        "nombre": "Silvia patricia",
        "empresa": "Consorcio SH",
        "pct": 0.0078078300142727
    },
    {
        "grupo": "4. Operadores",
        "nombre": "Fermin brito",
        "empresa": "Consorcio SH",
        "pct": 0.0080876974756214
    },
    {
        "grupo": "4. Operadores",
        "nombre": "Manuel Emilio",
        "empresa": "Consorcio SH",
        "pct": 0.0080876974756214
    },
    {
        "grupo": "4. Operadores",
        "nombre": "Manuel Emilio ",
        "empresa": "Consorcio SH",
        "pct": 0.0080876974756214
    },
    {
        "grupo": "4. Operadores",
        "nombre": "Marco daniel",
        "empresa": "Consorcio SH",
        "pct": 0.0080876974756214
    },
    {
        "grupo": "4. Operadores",
        "nombre": "waner omar",
        "empresa": "Consorcio SH",
        "pct": 0.0080876974756214
    },
    {
        "grupo": "4. Operadores",
        "nombre": "Zenaldo gregorio",
        "empresa": "Consorcio SH",
        "pct": 0.006
    },
    {
        "grupo": "4. Operadores",
        "nombre": "Wilton feliz",
        "empresa": "Consorcio SH",
        "pct": 0.01276219302705
    },
    {
        "grupo": "4. Operadores",
        "nombre": "Deibi Garcia",
        "empresa": "Consorcio SH",
        "pct": 0.01276219302705
    },
    {
        "grupo": "4. Operadores",
        "nombre": "Pedro",
        "empresa": "Consorcio SH",
        "pct": 0.0045266991406722
    },
    {
        "grupo": "4. Operadores",
        "nombre": "Martires",
        "empresa": "Consorcio SH",
        "pct": 0.0045266991406722
    },
    {
        "grupo": "4. Operadores",
        "nombre": "Melvin",
        "empresa": "Consorcio SH",
        "pct": 0.0045266991406722
    },
    {
        "grupo": "4. Operadores",
        "nombre": "Pedro",
        "empresa": "Consorcio SH",
        "pct": 0.004
    },
    {
        "grupo": "4. Operadores",
        "nombre": "Martires",
        "empresa": "Consorcio SH",
        "pct": 0.004
    },
    {
        "grupo": "4. Operadores",
        "nombre": "Melvin Francisco",
        "empresa": "Consorcio SH",
        "pct": 0.004
    }
];
    }

    let payoutRangesByType = getDefaultRanges();
    let administrativeRows = getDefaultAdministrativeRows().filter((row) => normalizeAdministrativeGroup(row.grupo) !== '4. Operadores');
    let operatorRows = getDefaultOperatorRows();
    let coordinatorRows = getDefaultCoordinatorRows();
    let cachedRows = [];
    let cachedMeta = {};
    let cachedSistema = null;
    let cachedTipoPago = null;
    let adminPctBruto = 0;
    let administrativeGroupFilter = 'todos';
    let currentDistributionBase = 0;
    let currentAdministrativePoolBase = 0;
    let currentAdministrativeBase = 0;
    let currentOperatorBase = 0;
    let currentCoordinatorBase = 0;
    let coordinatorUserDetailsByCoordinator = {};

    function toNumber(value) {
        if (value === null || value === undefined) return 0;
        return parseFloat(String(value).replace(/,/g, '')) || 0;
    }

    function formatPercentDisplay(value) {
        const number = parseFloat(value);
        if (Number.isNaN(number)) return '0';
        return Number.isInteger(number) ? String(number) : String(number);
    }

    function formatMoney(value) {
        return toNumber(value).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }

    function escapeHtml(value) {
        return String(value ?? '')
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    function toCsvValue(value) {
        const text = String(value ?? '');
        if (/[",\n]/.test(text)) {
            return `"${text.replace(/"/g, '""')}"`;
        }
        return text;
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

    function exportAdministrativosExcel() {
        const rows = getActiveAdministrativeRows().map((row) => [
            row.grupo,
            row.nombre,
            row.empresa,
            formatAdministrativePct(row.pct),
            formatMoney(getAdministrativeAmount(row)),
        ]);

        exportRowsToExcelCsv(
            'administrativos_validacion.csv',
            ['Grupo', 'Nombre', 'Empresa', '% Total', 'Monto Administrativo'],
            rows
        );
    }

    function exportOperadoresExcel() {
        const rows = operatorRows.map((row) => [
            row.grupo,
            row.nombre,
            row.empresa,
            formatAdministrativePct(row.pct),
            formatMoney(getOperatorAmount(row)),
        ]);

        exportRowsToExcelCsv(
            'operadores_validacion.csv',
            ['Grupo', 'Nombre', 'Empresa', '% Total', 'Monto Operador'],
            rows
        );
    }

    function exportCoordinadoresExcel() {
        const rows = coordinatorRows.map((row) => [
            row.nombre,
            toNumber(row.agencias),
            toNumber(row.agencias_validas),
            formatMoney(row.monto_usuarios),
            formatCoordinatorPct(row),
            formatMoney(getCoordinatorAmount(row)),
        ]);

        exportRowsToExcelCsv(
            'coordinadores_validacion.csv',
            ['Nombre', 'Agencias', 'Validas', 'Monto', '% Total', 'Monto Coordinador'],
            rows
        );
    }

    function normalizeAdministrativeGroup(value) {
        const group = String(value ?? '').trim();
        if (group.includes('Servs. Tecnicos')) {
            return '5. Servs. Tecnicos';
        }

        return group;
    }

    function getActiveAdministrativeRows() {
        if (administrativeGroupFilter === 'todos') {
            return administrativeRows;
        }

        return administrativeRows.filter((row) => normalizeAdministrativeGroup(row.grupo) === administrativeGroupFilter);
    }

    function getAdministrativePctTotal() {
        return getActiveAdministrativeRows().reduce((sum, row) => sum + toNumber(row.pct), 0);
    }

    function getAdministrativeAmount(row) {
        if (administrativeGroupFilter !== 'todos' && normalizeAdministrativeGroup(row.grupo) !== administrativeGroupFilter) {
            return 0;
        }

        const pctTotal = getAdministrativePctTotal();
        if (pctTotal <= 0) {
            return 0;
        }

        return currentAdministrativeBase * (toNumber(row.pct) / pctTotal);
    }

    function getCoordinatorPctTotal() {
        return getCoordinatorMontoUsuariosTotal();
    }

    function getCoordinatorAmount(row) {
        const pctTotal = getCoordinatorPctTotal();
        if (pctTotal <= 0) {
            return 0;
        }

        return currentCoordinatorBase * (toNumber(row.monto_usuarios) / pctTotal);
    }

    function formatCoordinatorPct(row) {
        const pctTotal = getCoordinatorPctTotal();
        if (pctTotal <= 0) {
            return '0.00';
        }

        return ((toNumber(row.monto_usuarios) / pctTotal) * 100).toFixed(2);
    }

    function getCoordinatorMontoUsuariosTotal() {
        return coordinatorRows.reduce((sum, row) => sum + toNumber(row.monto_usuarios), 0);
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

    function getAdministrativeRawPctTotal() {
        return administrativeRows.reduce((sum, row) => sum + toNumber(row.pct), 0);
    }

    function getOperatorPctTotal() {
        return operatorRows.reduce((sum, row) => sum + toNumber(row.pct), 0);
    }

    function recalculateAdministrativeOperatorBases() {
        const administrativeRawPctTotal = getAdministrativeRawPctTotal();
        const operatorPctTotal = getOperatorPctTotal();
        const combinedPctTotal = administrativeRawPctTotal + operatorPctTotal;

        if (combinedPctTotal <= 0 || currentAdministrativePoolBase <= 0) {
            currentAdministrativeBase = 0;
            currentOperatorBase = 0;
            return;
        }

        currentAdministrativeBase = currentAdministrativePoolBase * (administrativeRawPctTotal / combinedPctTotal);
        currentOperatorBase = currentAdministrativePoolBase * (operatorPctTotal / combinedPctTotal);
    }

    function getOperatorAmount(row) {
        const pctTotal = getOperatorPctTotal();
        if (pctTotal <= 0) {
            return 0;
        }

        return currentOperatorBase * (toNumber(row.pct) / pctTotal);
    }

    function updateAdministrativeSummary() {
        const totalDistribuido = administrativeRows.reduce((sum, row) => sum + getAdministrativeAmount(row), 0);

        document.getElementById('admin_base_total').textContent = formatMoney(currentAdministrativePoolBase);
        document.getElementById('admin_distribuido_total').textContent = formatMoney(currentAdministrativeBase);
        const totalCol = document.getElementById('admin_col_total');
        if (totalCol) {
            totalCol.textContent = formatMoney(totalDistribuido);
        }
    }

    function updateCoordinatorSummary() {
        const totalDistribuido = coordinatorRows.reduce((sum, row) => sum + getCoordinatorAmount(row), 0);

        document.getElementById('coord_base_total').textContent = formatMoney(currentCoordinatorBase);
        document.getElementById('coord_distribuido_total').textContent = formatMoney(totalDistribuido);
    }

    function updateOperatorSummary() {
        const totalDistribuido = operatorRows.reduce((sum, row) => sum + getOperatorAmount(row), 0);

        document.getElementById('op_base_total').textContent = formatMoney(currentAdministrativePoolBase);
        document.getElementById('op_distribuido_total').textContent = formatMoney(currentOperatorBase);
        const totalCol = document.getElementById('op_col_total');
        if (totalCol) {
            totalCol.textContent = formatMoney(totalDistribuido);
        }
    }

    function updateAdministrativeAmounts() {
        administrativeRows.forEach((row, idx) => {
            const cell = document.querySelector(`.admin-monto[data-idx="${idx}"]`);
            if (cell) {
                cell.textContent = formatMoney(getAdministrativeAmount(row));
            }
        });
        updateAdministrativeSummary();
    }

    function updateCoordinatorAmounts() {
        coordinatorRows.forEach((row, idx) => {
            const cell = document.querySelector(`.coord-monto[data-idx="${idx}"]`);
            if (cell) {
                cell.textContent = formatMoney(getCoordinatorAmount(row));
            }

            const pctInput = document.querySelector(`.coord-pct-input[data-idx="${idx}"]`);
            if (pctInput) {
                pctInput.value = formatCoordinatorPct(row);
            }
        });
        updateCoordinatorSummary();
    }

    function updateOperatorAmounts() {
        operatorRows.forEach((row, idx) => {
            const cell = document.querySelector(`.op-monto[data-idx="${idx}"]`);
            if (cell) {
                cell.textContent = formatMoney(getOperatorAmount(row));
            }
        });
        updateOperatorSummary();
    }

    function updateAdministrativeAndOperatorAmounts() {
        recalculateAdministrativeOperatorBases();
        updateAdministrativeAmounts();
        updateOperatorAmounts();
    }

    function renderAdministrativeTable() {
        const tbody = document.getElementById('tbodyAdministrativos');
        tbody.innerHTML = '';

        administrativeRows.forEach((row, idx) => {
            row.grupo = normalizeAdministrativeGroup(row.grupo);

            const tr = document.createElement('tr');
            tr.innerHTML = `
                <td><input type="text" class="form-control form-control-sm admin-input" data-field="grupo" data-idx="${idx}" value="${escapeHtml(row.grupo)}"></td>
                <td><input type="text" class="form-control form-control-sm admin-input" data-field="nombre" data-idx="${idx}" value="${escapeHtml(row.nombre)}"></td>
                <td><input type="text" class="form-control form-control-sm admin-input" data-field="empresa" data-idx="${idx}" value="${escapeHtml(row.empresa)}"></td>
                <td>
                    <div class="input-group input-group-sm">
                        <input type="number" class="form-control admin-input admin-pct-input" data-field="pct" data-idx="${idx}" min="0" step="0.01" value="${formatAdministrativePct(row.pct)}">
                        <span class="input-group-text">%</span>
                    </div>
                </td>
                <td class="text-end fw-semibold admin-monto" data-idx="${idx}">${formatMoney(getAdministrativeAmount(row))}</td>
            `;
            tbody.appendChild(tr);
        });

        updateAdministrativeSummary();
    }

    function renderOperatorTable() {
        const tbody = document.getElementById('tbodyOperadores');
        tbody.innerHTML = '';

        operatorRows.forEach((row, idx) => {
            const tr = document.createElement('tr');
            tr.innerHTML = `
                <td><input type="text" class="form-control form-control-sm op-input" data-field="grupo" data-idx="${idx}" value="${escapeHtml(row.grupo)}"></td>
                <td><input type="text" class="form-control form-control-sm op-input" data-field="nombre" data-idx="${idx}" value="${escapeHtml(row.nombre)}"></td>
                <td><input type="text" class="form-control form-control-sm op-input" data-field="empresa" data-idx="${idx}" value="${escapeHtml(row.empresa)}"></td>
                <td>
                    <div class="input-group input-group-sm">
                        <input type="number" class="form-control op-input op-pct-input" data-field="pct" data-idx="${idx}" min="0" step="0.01" value="${formatAdministrativePct(row.pct)}">
                        <span class="input-group-text">%</span>
                    </div>
                </td>
                <td class="text-end fw-semibold op-monto" data-idx="${idx}">${formatMoney(getOperatorAmount(row))}</td>
            `;
            tbody.appendChild(tr);
        });

        updateOperatorSummary();
    }

    function renderCoordinatorTable() {
        const tbody = document.getElementById('tbodyCoordinadores');
        tbody.innerHTML = '';

        if (!coordinatorRows.length) {
            tbody.innerHTML = '<tr><td colspan="7" class="text-center text-muted">No hay coordinadores registrados.</td></tr>';
            updateCoordinatorSummary();
            return;
        }

        coordinatorRows.forEach((row, idx) => {
            const detailUsers = getCoordinatorDetailUsers(row);
            const hasDetail = detailUsers.length > 0;
            const tr = document.createElement('tr');
            tr.innerHTML = `
                <td><input type="text" class="form-control form-control-sm coord-input" data-field="nombre" data-idx="${idx}" value="${escapeHtml(row.nombre)}"></td>
                <td class="text-center fw-semibold">${toNumber(row.agencias)}</td>
                <td class="text-center fw-semibold text-success">${toNumber(row.agencias_validas)}</td>
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
                <td class="text-end fw-semibold coord-monto" data-idx="${idx}">${formatMoney(getCoordinatorAmount(row))}</td>
            `;
            tbody.appendChild(tr);
        });

        updateCoordinatorSummary();
    }

    function renderCoordinatorDetailTable(row) {
        const tbody = document.getElementById('tbodyCoordinadorDetalle');
        const title = document.getElementById('coordinatorDetailTitle');
        const users = getCoordinatorDetailUsers(row);
        const coordinatorName = row?.nombre || 'Coordinador';

        title.textContent = `Detalle de Usuarios - ${coordinatorName}`;
        tbody.innerHTML = '';

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
        const totalCumplen = data.filter(item => item.cumple_minimo === 'SI').length;
        const totalNoCumplen = data.filter(item => item.cumple_minimo !== 'SI').length;
        const totalVendido = data.reduce((sum, item) => sum + toNumber(item.ventas_mes_actual), 0);
        const totalIncentivo = data.reduce((sum, item) => sum + toNumber(item.nuevo_incentivo), 0);
        const adminValor = totalIncentivo * (adminPctBruto / 100);
        const adminDistribucion = adminValor * 0.45;
        const coordinadorDistribucion = adminValor * 0.55;
        const totalConAdmin = totalIncentivo + adminValor;
        currentDistributionBase = adminValor;
        currentAdministrativePoolBase = adminDistribucion;
        currentCoordinatorBase = coordinadorDistribucion;

        document.getElementById('ni_count_cumplen').textContent = totalCumplen;
        document.getElementById('ni_count_no_cumplen').textContent = totalNoCumplen;
        document.getElementById('ni_total_vendido').textContent = totalVendido.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        document.getElementById('ni_total_incentivo').textContent = totalIncentivo.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        document.getElementById('ni_admin_resumen').textContent =
            '';
        document.getElementById('ni_admin_resumen').innerHTML =
            `<div>Porcentaje (${formatPercentDisplay(adminPctBruto)}%): ${formatMoney(adminValor)}</div>
            <div>Administrativo: ${formatMoney(adminDistribucion)}</div>
            <div>Coordinador: ${formatMoney(coordinadorDistribucion)}</div>`;
        document.getElementById('ni_total_con_admin').textContent =
            `Total a Pagar Final: ${totalConAdmin.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`;
        updateAdministrativeAndOperatorAmounts();
        updateCoordinatorAmounts();
    }

    function renderTableFromData(data) {
        if ($.fn.DataTable.isDataTable('#tableNuevoIncentivo')) {
            $('#tableNuevoIncentivo').DataTable().destroy();
        }

        const tableBody = document.querySelector('#tableNuevoIncentivo tbody');
        tableBody.innerHTML = '';

        data.forEach(item => {
            const cumpleBadge = item.cumple_minimo === 'SI'
                ? '<span class="badge bg-success">CUMPLIÓ</span>'
                : '<span class="badge bg-danger">NO CUMPLE</span>';

            const row = document.createElement('tr');
            row.innerHTML = `
                <td>${item.cedula}</td>
                <td>${formatMoney(item.ventas_ultimo_mes)}</td>
                <td>${formatMoney(item.ventas_mes_actual)}</td>
                <td>${item.dias_ventas_mes_actual ?? 0}</td>
                <td>${cumpleBadge}</td>
                <td>${formatMoney(item.pago_escala)}</td>
                <td>${formatMoney(item.nuevo_incentivo)}</td>
            `;
            tableBody.appendChild(row);
        });

        $('#tableNuevoIncentivo').DataTable({
            responsive: true,
            dom: 'Bfrtip',
            buttons: ['copy', 'csv', 'excel', 'pdf', 'print'],
            order: [[1, 'desc']],
            pageLength: 10000,
            scrollY: '500px',
            scrollCollapse: true,
            language: {
                lengthMenu: 'Mostrar _MENU_ registros por página',
                info: 'Mostrando _START_ a _END_ de _TOTAL_ registros',
                infoEmpty: 'No hay registros disponibles',
                infoFiltered: '(filtrado de _MAX_ registros totales)',
                search: 'Buscar:',
                paginate: {
                    first: 'Primero',
                    last: 'Último',
                    next: 'Siguiente',
                    previous: 'Anterior'
                }
            }
        });
    }

    function applyLocalFilters(showFilterAlert = false) {
        if (!cachedRows.length) {
            Swal.fire({ title: 'Información', text: 'Primero debes generar el reporte.', icon: 'warning' });
            return;
        }

        const sistema = document.getElementById('ni_sistema').value;
        const filtroCumplimiento = document.getElementById('ni_filtro_cumplimiento').value;
        const tipoPago = document.getElementById('ni_tipo_pago').value;

        if (cachedSistema !== sistema || cachedTipoPago !== tipoPago) {
            Swal.fire({ title: 'Informacion', text: 'Cambiaste el sistema o tipo de pago. Presiona \"Generar Reporte\" para recargar datos.', icon: 'info' });
            return;
        }

        let filtered = [...cachedRows];
        if (filtroCumplimiento === 'cumplidos') {
            filtered = filtered.filter(item => item.cumple_minimo === 'SI');
        } else if (filtroCumplimiento === 'no_cumplidos') {
            filtered = filtered.filter(item => item.cumple_minimo !== 'SI');
        }

        renderTableFromData(filtered);
        updateCardsFromData(filtered);

        document.getElementById('ni_rango_evaluado').textContent =
            `Mes evaluado: ${cachedMeta.eval_ini || ''} al ${cachedMeta.eval_fin || ''}`;

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

        document.querySelector('#btnConfigPct').addEventListener('click', function() {
            renderRangesTable();
            const modal = new bootstrap.Modal(document.getElementById('modalConfigPct'));
            modal.show();
        });
        document.querySelector('#btnConfigAdminPct').addEventListener('click', function() {
            document.getElementById('admin_pct_bruto').value = adminPctBruto;
            const modal = new bootstrap.Modal(document.getElementById('modalConfigAdminPct'));
            modal.show();
        });

        document.querySelector('#btnConfigAdministrativos').addEventListener('click', function() {
            document.getElementById('adminGrupoCalculo').value = administrativeGroupFilter;
            renderAdministrativeTable();
            const modal = new bootstrap.Modal(document.getElementById('modalAdministrativos'));
            modal.show();
        });

        document.querySelector('#btnConfigOperadores').addEventListener('click', function() {
            renderOperatorTable();
            const modal = new bootstrap.Modal(document.getElementById('modalOperadores'));
            modal.show();
        });

        document.querySelector('#adminGrupoCalculo').addEventListener('change', function() {
            administrativeGroupFilter = this.value;
            updateAdministrativeAmounts();
        });

        document.querySelector('#btnConfigCoordinadores').addEventListener('click', function() {
            renderCoordinatorTable();
            const modal = new bootstrap.Modal(document.getElementById('modalCoordinadores'));
            modal.show();
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

        document.querySelector('#tbodyAdministrativos').addEventListener('input', function(event) {
            const input = event.target;
            if (!input.classList.contains('admin-input')) {
                return;
            }

            const idx = parseInt(input.dataset.idx, 10);
            const field = input.dataset.field;
            if (!administrativeRows[idx] || !field) {
                return;
            }

            administrativeRows[idx][field] = field === 'pct'
                ? (Math.max(0, parseFloat(input.value || 0) || 0) / 100)
                : input.value;

            updateAdministrativeAndOperatorAmounts();
        });

        document.querySelector('#tbodyAdministrativos').addEventListener('change', function(event) {
            const input = event.target;
            if (input.classList.contains('admin-pct-input')) {
                input.value = (parseFloat(input.value || 0) || 0).toFixed(2);
            }
        });

        document.querySelector('#btnRestaurarAdministrativos').addEventListener('click', function() {
            administrativeRows = getDefaultAdministrativeRows().filter((row) => normalizeAdministrativeGroup(row.grupo) !== '4. Operadores');
            administrativeGroupFilter = 'todos';
            document.getElementById('adminGrupoCalculo').value = administrativeGroupFilter;
            renderAdministrativeTable();
            updateAdministrativeAndOperatorAmounts();
        });

        document.querySelector('#tbodyOperadores').addEventListener('input', function(event) {
            const input = event.target;
            if (!input.classList.contains('op-input')) {
                return;
            }

            const idx = parseInt(input.dataset.idx, 10);
            const field = input.dataset.field;
            if (!operatorRows[idx] || !field) {
                return;
            }

            operatorRows[idx][field] = field === 'pct'
                ? (Math.max(0, parseFloat(input.value || 0) || 0) / 100)
                : input.value;

            updateAdministrativeAndOperatorAmounts();
        });

        document.querySelector('#tbodyOperadores').addEventListener('change', function(event) {
            const input = event.target;
            if (input.classList.contains('op-pct-input')) {
                input.value = (parseFloat(input.value || 0) || 0).toFixed(2);
            }
        });

        document.querySelector('#btnRestaurarOperadores').addEventListener('click', function() {
            operatorRows = getDefaultOperatorRows();
            renderOperatorTable();
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
            renderCoordinatorTable();
        });

        document.querySelector('#btnExportAdministrativosExcel').addEventListener('click', function() {
            exportAdministrativosExcel();
        });

        document.querySelector('#btnExportOperadoresExcel').addEventListener('click', function() {
            exportOperadoresExcel();
        });

        document.querySelector('#btnExportCoordinadoresExcel').addEventListener('click', function() {
            exportCoordinadoresExcel();
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
                Swal.fire({ title: 'Validación', text: e.message, icon: 'warning' });
                return;
            }

            bootstrap.Modal.getInstance(document.getElementById('modalConfigPct'))?.hide();
            Swal.fire({ title: 'Configuración guardada', text: 'Los tramos se aplicarán al generar el reporte.', icon: 'success' });
        });

        document.querySelector('#btnGuardarAdminPct').addEventListener('click', function() {
            adminPctBruto = parseFloat(document.getElementById('admin_pct_bruto').value || 0);
            bootstrap.Modal.getInstance(document.getElementById('modalConfigAdminPct'))?.hide();

            if (cachedRows.length && cachedSistema === document.getElementById('ni_sistema').value && cachedTipoPago === document.getElementById('ni_tipo_pago').value) {
                applyLocalFilters(false);
            } else {
                currentDistributionBase = 0;
                currentAdministrativePoolBase = 0;
                currentAdministrativeBase = 0;
                currentOperatorBase = 0;
                currentCoordinatorBase = 0;
                document.getElementById('ni_admin_resumen').innerHTML =
                    `<div>Porcentaje (${formatPercentDisplay(adminPctBruto)}%): 0.00</div>
                    <div>Administrativo: 0.00</div>
                    <div>Coordinador: 0.00</div>`;
                document.getElementById('ni_total_con_admin').textContent = 'Total a Pagar Final: 0.00';
                updateAdministrativeAndOperatorAmounts();
                updateCoordinatorAmounts();
            }

            Swal.fire({
                title: 'Configuración guardada',
                text: 'El % administrativo se aplica sobre el Total Incentivo a Pagar.',
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

        if (!fechaIni || !fechaFin) {
            Swal.fire({ title: 'Información', text: 'Debe seleccionar fecha inicio y fecha fin.', icon: 'warning' });
            return;
        }

        Swal.fire({
            title: 'Procesando Información ...',
            icon: 'info',
            allowOutsideClick: false,
            showConfirmButton: false,
            timerProgressBar: true,
            didOpen: () => Swal.showLoading()
        });

        $('#tableNuevoIncentivo tbody').empty();

        const params = new URLSearchParams({
            sistema: sistema,
            filtro_cumplimiento: 'todos',
            fecha_ini: fechaIni,
            fecha_fin: fechaFin,
            min_dias_venta: minDias,
            tipo_pago: tipoPago,
            rangos_pago: JSON.stringify(payoutRangesByType[tipoPago]),
        });

        fetch('/incentivos/reporte-nuevo-incentivo-v3?' + params.toString())
            .then(response => response.json())
            .then(resp => {
                if ('message' in resp) {
                    Swal.fire({ title: 'Información', text: resp.message, icon: 'warning' });
                    return;
                }

                const data = resp.data || [];
                cachedRows = data;
                cachedMeta = resp.meta || {};
                updateCoordinatorValidAgencies(cachedMeta);
                cachedSistema = sistema;
                cachedTipoPago = tipoPago;

                Swal.close();
                applyLocalFilters(showFilterAlert);
            })
            .catch(error => {
                Swal.fire({ title: 'Error', text: error, icon: 'warning' });
            });
    }

    function filtrarCumplimientoTabla() {
        applyLocalFilters(true);
    }

    document.querySelector('#btnGenerarNuevoIncentivo').addEventListener('click', function() {
        listNuevoIncentivo();
    });

    document.querySelector('#btnFiltrarCumplimiento').addEventListener('click', function() {
        filtrarCumplimientoTabla();
    });
</script>
@endsection





