@extends('app')

@section('content')
    <div class="main-content">
        <div class="page-content">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-12">
                        <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                            <h4 class="mb-sm-0">Reporte Nuevo Incentivo - Parte 2</h4>
                            <div class="page-title-right">
                                <ol class="breadcrumb m-0">
                                    <li class="breadcrumb-item"><a href="{{ route('inicio.index') }}">Inicio</a></li>
                                    <li class="breadcrumb-item"><a href="{{ route('incentivos.index') }}">Incentivos</a></li>
                                    <li class="breadcrumb-item active">Reporte Nuevo Incentivo V2</li>
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
                                    <h5 class="card-title mb-0">Cálculo por sistema y rango (V2)</h5>
                                    <small class="text-muted">Configura tramos de venta mensual y pago fijo por agente.</small>
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
                                    <div class="form-check mb-2">
                                        <input class="form-check-input" type="checkbox" id="checkTramo2">
                                        <label class="form-check-label" for="checkTramo2">Usar Tramo 2</label>
                                    </div>
                                    <button type="button" class="btn btn-soft-secondary" id="btnConfigPct">Configurar Tramos</button>
                                    <button type="button" class="btn btn-soft-secondary" id="btnConfigPct2">Configurar Tramo2</button>
                                    <button type="button" class="btn btn-soft-secondary" id="btnConfigAdminPct">% Administrativo</button>
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
                    <h5 class="modal-title">Configurar Tramos de Pago</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-info py-2 mb-3">Define los rangos de venta mensual y el pago fijo para el agente.</div>
                    <div class="table-responsive">
                        <table class="table table-bordered table-sm align-middle mb-0">
                            <thead>
                                <tr>
                                    <th>Ventas Mensual Desde</th>
                                    <th>Hasta</th>
                                    <th>Pago a la Agente de Venta</th>
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

    <div id="modalConfigPct2" class="modal fade" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Configurar Tramo2</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-info py-2 mb-3">Copia de tramos base. Desde 1,000,001 se aplica porcentaje (default 1%).</div>
                    <div class="table-responsive">
                        <table class="table table-bordered table-sm align-middle mb-0">
                            <thead>
                                <tr>
                                    <th>Ventas Mensual Desde</th>
                                    <th>Hasta</th>
                                    <th>Valor (fijo o % desde 1,000,001)</th>
                                </tr>
                            </thead>
                            <tbody id="tbodyTramosPago2"></tbody>
                        </table>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-soft-secondary" id="btnRestaurarTramos2">Restaurar por defecto</button>
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-primary" id="btnGuardarPct2">Guardar</button>
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
    function getDefaultRanges() {
        return [
            { desde: 100001, hasta: 250000, pago: 1000 },
            { desde: 250001, hasta: 400000, pago: 2000 },
            { desde: 400001, hasta: 550000, pago: 4000 },
            { desde: 550001, hasta: 700000, pago: 6000 },
            { desde: 700001, hasta: 850000, pago: 8000 },
            { desde: 850001, hasta: 1000000, pago: 10000 },
            { desde: 1000001, hasta: 1150000, pago: 12000 },
            { desde: 1150001, hasta: 1300000, pago: 14000 },
            { desde: 1300001, hasta: 1450000, pago: 16000 },
            { desde: 1450001, hasta: 1600000, pago: 18000 },
            { desde: 1600001, hasta: 1750000, pago: 20000 },
            { desde: 1750001, hasta: 1900000, pago: 22000 },
            { desde: 1900001, hasta: 2050000, pago: 24000 },
            { desde: 2050001, hasta: 2200000, pago: 26000 },
            { desde: 2200001, hasta: 2350000, pago: 28000 },
            { desde: 2350001, hasta: 2500000, pago: 30000 },
            { desde: 2500001, hasta: 2650000, pago: 32000 },
            { desde: 2650001, hasta: 2800000, pago: 34000 },
            { desde: 2800001, hasta: 2950000, pago: 36000 },
            { desde: 2950001, hasta: 3100000, pago: 38000 },
            { desde: 3100001, hasta: 3250000, pago: 40000 },
            { desde: 3250001, hasta: 3400000, pago: 42000 },
            { desde: 3400001, hasta: 3550000, pago: 44000 },
            { desde: 3550001, hasta: 3700000, pago: 46000 },
            { desde: 3700001, hasta: 3850000, pago: 48000 },
            { desde: 3850001, hasta: 5000000, pago: 50000 },
        ];
    }

    let payoutRanges = getDefaultRanges();
    let payoutRanges2 = getDefaultRanges().map(function (row) {
        if (row.desde >= 1000001) {
            return { ...row, pago: 1 };
        }
        return { ...row };
    });
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

    function renderRangesTable() {
        const tbody = document.getElementById('tbodyTramosPago');
        tbody.innerHTML = '';

        payoutRanges.forEach((row, idx) => {
            const tr = document.createElement('tr');
            tr.innerHTML = `
                <td><input type="number" class="form-control tramo-desde" data-idx="${idx}" min="0" step="1" value="${row.desde}"></td>
                <td><input type="number" class="form-control tramo-hasta" data-idx="${idx}" min="0" step="1" value="${row.hasta}"></td>
                <td><input type="number" class="form-control tramo-pago" data-idx="${idx}" min="0" step="0.01" value="${row.pago}"></td>
            `;
            tbody.appendChild(tr);
        });
    }

    function renderRangesTable2() {
        const tbody = document.getElementById('tbodyTramosPago2');
        tbody.innerHTML = '';

        payoutRanges2.forEach((row, idx) => {
            const tr = document.createElement('tr');
            tr.innerHTML = `
                <td><input type="number" class="form-control tramo2-desde" data-idx="${idx}" min="0" step="1" value="${row.desde}"></td>
                <td><input type="number" class="form-control tramo2-hasta" data-idx="${idx}" min="0" step="1" value="${row.hasta}"></td>
                <td><input type="number" class="form-control tramo2-pago" data-idx="${idx}" min="0" step="0.01" value="${row.pago}"></td>
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
            const hasta = parseFloat(hastaInputs[i].value || 0);
            const pago = parseFloat(pagoInputs[i].value || 0);

            if (desde < 0 || hasta < 0 || pago < 0) {
                throw new Error(`Hay valores negativos en la fila ${i + 1}.`);
            }
            if (desde > hasta) {
                throw new Error(`El valor Desde no puede ser mayor que Hasta en la fila ${i + 1}.`);
            }

            ranges.push({ desde, hasta, pago });
        }

        return ranges.sort((a, b) => a.desde - b.desde);
    }

    function readRangesFromTable2() {
        const desdeInputs = document.querySelectorAll('.tramo2-desde');
        const hastaInputs = document.querySelectorAll('.tramo2-hasta');
        const pagoInputs = document.querySelectorAll('.tramo2-pago');

        const ranges = [];
        for (let i = 0; i < desdeInputs.length; i++) {
            const desde = parseFloat(desdeInputs[i].value || 0);
            const hasta = parseFloat(hastaInputs[i].value || 0);
            const pago = parseFloat(pagoInputs[i].value || 0);

            if (desde < 0 || hasta < 0 || pago < 0) {
                throw new Error(`Hay valores negativos en la fila ${i + 1}.`);
            }
            if (desde > hasta) {
                throw new Error(`El valor Desde no puede ser mayor que Hasta en la fila ${i + 1}.`);
            }

            ranges.push({ desde, hasta, pago });
        }

        return ranges.sort((a, b) => a.desde - b.desde);
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
        document.getElementById('ni_total_vendido').textContent = totalVendido.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        document.getElementById('ni_total_incentivo').textContent = totalIncentivo.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
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
                <td>${cumpleBadge}</td>
                <td>${item.pago_escala ?? '0.00'}</td>
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
            Swal.fire({ title: 'Información', text: 'Primero debes generar el reporte.', icon: 'warning' });
            return;
        }

        const sistema = document.getElementById('ni_sistema').value;
        const filtroCumplimiento = document.getElementById('ni_filtro_cumplimiento').value;

        if (cachedSistema !== sistema) {
            Swal.fire({ title: 'Información', text: 'Cambiaste el sistema. Presiona "Generar Reporte" para recargar datos de ese sistema.', icon: 'info' });
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

        document.querySelector('#btnConfigPct2').addEventListener('click', function() {
            renderRangesTable2();
            const modal = new bootstrap.Modal(document.getElementById('modalConfigPct2'));
            modal.show();
        });

        document.querySelector('#btnConfigAdminPct').addEventListener('click', function() {
            document.getElementById('admin_pct_bruto').value = adminPctBruto;
            const modal = new bootstrap.Modal(document.getElementById('modalConfigAdminPct'));
            modal.show();
        });

        document.querySelector('#btnRestaurarTramos').addEventListener('click', function() {
            payoutRanges = getDefaultRanges();
            renderRangesTable();
        });

        document.querySelector('#btnRestaurarTramos2').addEventListener('click', function() {
            payoutRanges2 = getDefaultRanges().map(function (row) {
                if (row.desde >= 1000001) {
                    return { ...row, pago: 1 };
                }
                return { ...row };
            });
            renderRangesTable2();
        });

        document.querySelector('#btnGuardarPct').addEventListener('click', function() {
            try {
                payoutRanges = readRangesFromTable();
            } catch (e) {
                Swal.fire({ title: 'Validación', text: e.message, icon: 'warning' });
                return;
            }

            bootstrap.Modal.getInstance(document.getElementById('modalConfigPct'))?.hide();
            Swal.fire({ title: 'Configuración guardada', text: 'Los tramos se aplicarán al generar el reporte.', icon: 'success' });
        });

        document.querySelector('#btnGuardarPct2').addEventListener('click', function() {
            try {
                payoutRanges2 = readRangesFromTable2();
            } catch (e) {
                Swal.fire({ title: 'Validación', text: e.message, icon: 'warning' });
                return;
            }

            bootstrap.Modal.getInstance(document.getElementById('modalConfigPct2'))?.hide();
            Swal.fire({ title: 'Configuración guardada', text: 'Los Tramo2 se aplicarán al generar el reporte.', icon: 'success' });
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
        const minDias = document.getElementById('ni_min_dias').value;
        const usarTramo2 = document.getElementById('checkTramo2').checked;

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
            tramo_activo: usarTramo2 ? 'tramo2' : 'tramo1',
            rangos_pago: JSON.stringify(usarTramo2 ? payoutRanges2 : payoutRanges),
        });

        fetch('/incentivos/reporte-nuevo-incentivo-v2?' + params.toString())
            .then(response => response.json())
            .then(resp => {
                if ('message' in resp) {
                    Swal.fire({ title: 'Información', text: resp.message, icon: 'warning' });
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
