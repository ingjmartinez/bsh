@extends('app')

@section('content')
    <div class="main-content">
        <div class="page-content">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-12">
                        <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                            <h4 class="mb-sm-0">Reporte Nuevo Incentivo</h4>
                            <div class="page-title-right">
                                <ol class="breadcrumb m-0">
                                    <li class="breadcrumb-item"><a href="{{ route('inicio.index') }}">Inicio</a></li>
                                    <li class="breadcrumb-item"><a href="{{ route('incentivos.index') }}">Incentivos</a></li>
                                    <li class="breadcrumb-item active">Reporte Nuevo Incentivo</li>
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
                            <div class="card-body">
                                <p class="text-uppercase fw-medium text-muted mb-1">Total Incentivo a Pagar</p>
                                <h4 class="mb-0" id="ni_total_incentivo">0.00</h4>
                                <div class="d-block mt-1 fw-semibold fs-5 text-primary" id="ni_admin_resumen">Administrativo (0%): 0.00</div>
                                <div class="mt-2 fw-bold fs-4 text-success" id="ni_total_con_admin">Total a Pagar Final: 0.00</div>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-12">
                        <div class="card">
                            <div class="card-header d-flex align-items-center justify-content-between">
                                <div>
                                    <h5 class="card-title mb-0">Cálculo por sistema y rango</h5>
                                    <small class="text-muted">Evalúa el mes anterior completo según la fecha fin seleccionada.</small>
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
                                    <button type="button" class="btn btn-info" id="btnFiltrarCumplimiento">
                                        Filtrar
                                    </button>
                                    <div>
                                        <label class="mb-0" for="ni_fecha_ini">Fecha inicio</label>
                                        <input type="date" id="ni_fecha_ini" class="form-control">
                                    </div>
                                    <div>
                                        <label class="mb-0" for="ni_fecha_fin">Fecha fin</label>
                                        <input type="date" id="ni_fecha_fin" class="form-control">
                                    </div>
                                    <div>
                                        <label class="mb-0" for="ni_minimo_agencia">Mínimo agencia</label>
                                        <input type="number" id="ni_minimo_agencia" class="form-control" value="80000" min="0" step="0.01">
                                    </div>
                                    <div>
                                        <label class="mb-0" for="ni_min_dias">Mín. días venta</label>
                                        <input type="number" id="ni_min_dias" class="form-control" value="10" min="1" step="1">
                                    </div>
                                    <button type="button" class="btn btn-soft-secondary" id="btnConfigPct">
                                        Configurar %
                                    </button>
                                    <button type="button" class="btn btn-soft-secondary" id="btnConfigAdminPct">
                                        % Administrativo
                                    </button>
                                    <button type="button" class="btn btn-primary" id="btnGenerarNuevoIncentivo">
                                        Generar Reporte
                                    </button>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="mb-2 text-muted" id="ni_rango_evaluado"></div>
                                <table id="tableNuevoIncentivo"
                                    class="table table-bordered dt-responsive nowrap table-striped align-middle"
                                    style="width:100%">
                                    <thead>
                                        <tr>
                                            <th>Cédula</th>
                                            <th>Ventas Último Mes</th>
                                            <th>Ventas Mes Actual</th>
                                            <th>Días Ventas Mes Actual</th>
                                            <th>Mínimo Agencia</th>
                                            <th>Cumple Mínimo</th>
                                            <th>% Comisión</th>
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
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Configurar % Comisión</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-6">
                            <label class="form-label">&lt; 100,000</label>
                            <input type="number" id="pct_1" class="form-control" value="1" min="0" step="0.01">
                        </div>
                        <div class="col-6">
                            <label class="form-label">100,000 - 149,999</label>
                            <input type="number" id="pct_2" class="form-control" value="2" min="0" step="0.01">
                        </div>
                        <div class="col-6">
                            <label class="form-label">150,000 - 199,999</label>
                            <input type="number" id="pct_3" class="form-control" value="3" min="0" step="0.01">
                        </div>
                        <div class="col-6">
                            <label class="form-label">&gt;= 200,000</label>
                            <input type="number" id="pct_4" class="form-control" value="4" min="0" step="0.01">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
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
                    <h5 class="modal-title">Configurar % Administrativo</h5>
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
@endsection

@section('script')
    <script>
        let pctConfig = {
            pct_1: 1,
            pct_2: 2,
            pct_3: 3,
            pct_4: 4,
        };
        let cachedRows = [];
        let cachedMeta = {};
        let cachedSistema = null;
        let adminPctBruto = 0;

        function toNumber(value) {
            if (value === null || value === undefined) return 0;
            return parseFloat(String(value).replace(/,/g, '')) || 0;
        }

        function formatPercentDisplay(value) {
            const number = parseFloat(value);
            if (Number.isNaN(number)) return '0';
            return Number.isInteger(number) ? String(number) : String(number);
        }

        function updateCardsFromData(data) {
            const totalCumplen = data.filter(item => item.cumple_minimo === 'SI').length;
            const totalNoCumplen = data.filter(item => item.cumple_minimo !== 'SI').length;
            const totalVendido = data.reduce((sum, item) => sum + toNumber(item.ventas_mes_actual), 0);
            const totalIncentivo = data.reduce((sum, item) => sum + toNumber(item.nuevo_incentivo), 0);
            const adminValor = totalIncentivo * (adminPctBruto / 100);
            const totalConAdmin = totalIncentivo + adminValor;

            document.getElementById('ni_count_cumplen').textContent = totalCumplen;
            document.getElementById('ni_count_no_cumplen').textContent = totalNoCumplen;
            document.getElementById('ni_total_vendido').textContent = totalVendido.toLocaleString('en-US', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            });
            document.getElementById('ni_total_incentivo').textContent = totalIncentivo.toLocaleString('en-US', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            });
            document.getElementById('ni_admin_resumen').textContent =
                `Administrativo (${formatPercentDisplay(adminPctBruto)}%): ${adminValor.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`;
            document.getElementById('ni_total_con_admin').textContent =
                `Total a Pagar Final: ${totalConAdmin.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`;
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
                    <td>${item.ventas_ultimo_mes}</td>
                    <td>${item.ventas_mes_actual || '0.00'}</td>
                    <td>${item.dias_ventas_mes_actual ?? 0}</td>
                    <td>${item.minimo_agencia}</td>
                    <td>${cumpleBadge}</td>
                    <td>${item.pct_comision}</td>
                    <td>${item.nuevo_incentivo}</td>
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
                Swal.fire({
                    title: 'Información',
                    text: 'Primero debes generar el reporte.',
                    icon: 'warning'
                });
                return;
            }

            const sistema = document.getElementById('ni_sistema').value;
            const filtroCumplimiento = document.getElementById('ni_filtro_cumplimiento').value;

            // El filtro en memoria aplica al mismo sistema con el que se cargaron los datos.
            if (cachedSistema !== sistema) {
                Swal.fire({
                    title: 'Información',
                    text: 'Cambiaste el sistema. Presiona "Generar Reporte" para recargar datos de ese sistema.',
                    icon: 'info'
                });
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
                document.getElementById('pct_1').value = pctConfig.pct_1;
                document.getElementById('pct_2').value = pctConfig.pct_2;
                document.getElementById('pct_3').value = pctConfig.pct_3;
                document.getElementById('pct_4').value = pctConfig.pct_4;
                const modal = new bootstrap.Modal(document.getElementById('modalConfigPct'));
                modal.show();
            });

            document.querySelector('#btnConfigAdminPct').addEventListener('click', function() {
                document.getElementById('admin_pct_bruto').value = adminPctBruto;
                const modal = new bootstrap.Modal(document.getElementById('modalConfigAdminPct'));
                modal.show();
            });

            document.querySelector('#btnGuardarPct').addEventListener('click', function() {
                pctConfig.pct_1 = parseFloat(document.getElementById('pct_1').value || 0);
                pctConfig.pct_2 = parseFloat(document.getElementById('pct_2').value || 0);
                pctConfig.pct_3 = parseFloat(document.getElementById('pct_3').value || 0);
                pctConfig.pct_4 = parseFloat(document.getElementById('pct_4').value || 0);

                bootstrap.Modal.getInstance(document.getElementById('modalConfigPct'))?.hide();
                Swal.fire({
                    title: 'Configuración guardada',
                    text: 'Los porcentajes se aplicarán al generar el reporte.',
                    icon: 'success'
                });
            });

            document.querySelector('#btnGuardarAdminPct').addEventListener('click', function() {
                adminPctBruto = parseFloat(document.getElementById('admin_pct_bruto').value || 0);
                bootstrap.Modal.getInstance(document.getElementById('modalConfigAdminPct'))?.hide();

                if (cachedRows.length && cachedSistema === document.getElementById('ni_sistema').value) {
                    applyLocalFilters(false);
                } else {
                    document.getElementById('ni_admin_resumen').textContent =
                        `Administrativo (${formatPercentDisplay(adminPctBruto)}%): 0.00`;
                    document.getElementById('ni_total_con_admin').textContent = 'Total a Pagar Final: 0.00';
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
            const minimoAgencia = document.getElementById('ni_minimo_agencia').value;
            const minDias = document.getElementById('ni_min_dias').value;

            if (!fechaIni || !fechaFin) {
                Swal.fire({
                    title: 'Información',
                    text: 'Debe seleccionar fecha inicio y fecha fin.',
                    icon: 'warning'
                });
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
                minimo_agencia: minimoAgencia,
                min_dias_venta: minDias,
                pct_1: pctConfig.pct_1,
                pct_2: pctConfig.pct_2,
                pct_3: pctConfig.pct_3,
                pct_4: pctConfig.pct_4,
            });

            fetch('/incentivos/reporte-nuevo-incentivo?' + params.toString())
                .then(response => response.json())
                .then(resp => {
                    if ('message' in resp) {
                        Swal.fire({
                            title: 'Información',
                            text: resp.message,
                            icon: 'warning'
                        });
                        return;
                    }

                    const data = resp.data || [];
                    cachedRows = data;
                    cachedMeta = resp.meta || {};
                    cachedSistema = sistema;

                    Swal.close();

                    applyLocalFilters(showFilterAlert);
                })
                .catch(error => {
                    Swal.fire({
                        title: 'Error',
                        text: error,
                        icon: 'warning'
                    });
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

