@extends('app')

@section('content')
    <div class="main-content">
        <div class="page-content">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-12">
                        <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                            <h4 class="mb-sm-0">Recursos Humanos</h4>
                            <div class="page-title-right">
                                <ol class="breadcrumb m-0">
                                    <li class="breadcrumb-item"><a href="{{ route('inicio.index') }}">Inicio</a></li>
                                    <li class="breadcrumb-item"><a href="{{ route('recursos-humanos.index') }}">Recursos Humanos</a></li>
                                    <li class="breadcrumb-item active">Dashboard</li>
                                </ol>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row g-3 mb-3">
                    <div class="col-12">
                        <div class="card overflow-hidden border-0 shadow-sm" style="background: linear-gradient(135deg, #0f766e 0%, #1d4ed8 55%, #312e81 100%);">
                            <div class="card-body p-4 p-lg-5 text-white position-relative">
                                <div class="row align-items-center g-4">
                                    <div class="col-lg-7">
                                        <span class="badge rounded-pill bg-white bg-opacity-10 text-white mb-3">Mini Dashboard RRHH</span>
                                        <h2 class="fw-semibold text-white mb-2">Vista de Empleados, estatus y masa salariales</h2>
                                        <p class="mb-0 text-white text-opacity-75">
                                            Filtra por empresa y analiza usuarios activos, inactivos y salario mensual de usuarios activos agrupado por ciudad.
                                        </p>
                                    </div>
                                    <div class="col-lg-5">
                                        <div class="row g-3">
                                            <div class="col-sm-6">
                                                <label class="form-label text-white text-opacity-75">Empresa</label>
                                                <select id="empresa" class="form-select border-0 shadow-sm">
                                                    <option value="">Todas</option>
                                                    <option value="126">126 = Business Support Hub</option>
                                                    <option value="100">100 = Consorcio SH-QPL</option>
                                                </select>
                                            </div>
                                            <div class="col-sm-6 d-flex align-items-end">
                                                <div class="d-grid gap-2 w-100">
                                                    <button type="button" class="btn btn-light text-primary fw-semibold" id="btnRefrescarDashboard">
                                                        Actualizar dashboard
                                                    </button>
                                                    <button type="button" class="btn btn-outline-light fw-semibold" id="btnSincronizar">
                                                        Sincronizar empleados
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row g-3 mb-3">
                    <div class="col-md-6 col-xl-3">
                        <div class="card border-0 shadow-sm h-100">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-start mb-3">
                                    <div>
                                        <div class="text-muted text-uppercase small fw-semibold">Empleados totales</div>
                                        <div class="display-6 fw-semibold mb-0" id="kpi-total-empleados">0</div>
                                    </div>
                                    <div class="avatar-sm">
                                        <span class="avatar-title rounded-circle bg-primary-subtle text-primary fs-4">
                                            <i class="ri-team-line"></i>
                                        </span>
                                    </div>
                                </div>
                                <div class="text-muted small">Conteo total según empresa filtrada.</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 col-xl-3">
                        <div class="card border-0 shadow-sm h-100">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-start mb-3">
                                    <div>
                                        <div class="text-muted text-uppercase small fw-semibold">Activos</div>
                                        <div class="display-6 fw-semibold text-success mb-0" id="kpi-activos">0</div>
                                    </div>
                                    <div class="avatar-sm">
                                        <span class="avatar-title rounded-circle bg-success-subtle text-success fs-4">
                                            <i class="ri-user-follow-line"></i>
                                        </span>
                                    </div>
                                </div>
                                <div class="text-muted small">Fecha de salida vacía = empleado activo.</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 col-xl-3">
                        <div class="card border-0 shadow-sm h-100">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-start mb-3">
                                    <div>
                                        <div class="text-muted text-uppercase small fw-semibold">Inactivos</div>
                                        <div class="display-6 fw-semibold text-danger mb-0" id="kpi-inactivos">0</div>
                                    </div>
                                    <div class="avatar-sm">
                                        <span class="avatar-title rounded-circle bg-danger-subtle text-danger fs-4">
                                            <i class="ri-user-unfollow-line"></i>
                                        </span>
                                    </div>
                                </div>
                                <div class="text-muted small">Fecha de salida con dato = empleado inactivo.</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 col-xl-3">
                        <div class="card border-0 shadow-sm h-100">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-start mb-3">
                                    <div>
                                        <div class="text-muted text-uppercase small fw-semibold">Salario mensual</div>
                                        <div class="fs-2 fw-semibold text-info mb-0" id="kpi-salario-total">0.00</div>
                                    </div>
                                    <div class="avatar-sm">
                                        <span class="avatar-title rounded-circle bg-info-subtle text-info fs-4">
                                            <i class="ri-money-dollar-circle-line"></i>
                                        </span>
                                    </div>
                                </div>
                                <div class="text-muted small">Masa salarial mensual solo de usuarios activos.</div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row g-3 mb-3">
                    <div class="col-xl-4">
                        <div class="card border-0 shadow-sm h-100">
                            <div class="card-header bg-transparent border-0">
                                <h5 class="card-title mb-0">Estado de empleados</h5>
                            </div>
                            <div class="card-body">
                                <div id="chartEstadoEmpleados" style="min-height: 320px;"></div>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-8">
                        <div class="card border-0 shadow-sm h-100">
                            <div class="card-header bg-transparent border-0">
                                <h5 class="card-title mb-0">Salario mensual por ciudad de usuarios activos</h5>
                            </div>
                            <div class="card-body">
                                <div id="chartSalarioCiudad" style="min-height: 320px;"></div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row g-3 mb-3">
                    <div class="col-xl-6">
                        <div class="card border-0 shadow-sm h-100">
                            <div class="card-header bg-transparent border-0">
                                <h5 class="card-title mb-0">Cantidad de empleados por ciudad</h5>
                            </div>
                            <div class="card-body">
                                <div id="chartEmpleadosCiudad" style="min-height: 320px;"></div>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-6">
                        <div class="card border-0 shadow-sm h-100">
                            <div class="card-header bg-transparent border-0">
                                <h5 class="card-title mb-0">Participacion salarial por empresa</h5>
                            </div>
                            <div class="card-body">
                                <div id="chartSalarioEmpresa" style="min-height: 320px;"></div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row g-3">
                    <div class="col-xl-5">
                        <div class="card border-0 shadow-sm h-100">
                            <div class="card-header bg-transparent border-0">
                                <h5 class="card-title mb-0">Top ciudades</h5>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-borderless align-middle mb-0">
                                        <thead class="table-light">
                                            <tr>
                                                <th>Ciudad</th>
                                                <th class="text-center">Empleados</th>
                                                <th class="text-center">Activos</th>
                                                <th class="text-end">Salario</th>
                                            </tr>
                                        </thead>
                                        <tbody id="tbodyResumenCiudad"></tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-xl-7">
                        <div class="card border-0 shadow-sm">
                            <div class="card-header d-flex align-items-center justify-content-between">
                                <h5 class="card-title mb-0">Empleados</h5>
                                <span class="badge bg-primary-subtle text-primary" id="badgeEmpresaActual">Todas las empresas</span>
                            </div>
                            <div class="card-body">
                                <table id="tableEmpleados" class="table table-bordered dt-responsive nowrap table-striped align-middle" style="width:100%">
                                    <thead>
                                        <tr>
                                            <th>Empresa</th>
                                            <th>Id Empleado</th>
                                            <th>Nombres</th>
                                            <th>Apellidos</th>
                                            <th>Cedula</th>
                                            <th>Ciudad</th>
                                            <th>Salario Mensual</th>
                                            <th>Fecha Ingreso</th>
                                            <th>Fecha Salida</th>
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

        <footer class="footer">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-sm-6">
                        <script>
                            document.write(new Date().getFullYear())
                        </script> © Velzon.
                    </div>
                    <div class="col-sm-6">
                        <div class="text-sm-end d-none d-sm-block">
                            Design & Develop by Themesbrand
                        </div>
                    </div>
                </div>
            </div>
        </footer>
    </div>
@endsection

@section('script')
    <script src="{{ asset('libs/apexcharts/apexcharts.min.js') }}"></script>
    <script>
        let chartEstado = null;
        let chartSalarioCiudad = null;
        let chartEmpleadosCiudad = null;
        let chartSalarioEmpresa = null;

        function formatoMonto(valor) {
            return Number(valor || 0).toLocaleString('en-US', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2,
            });
        }

        function empresaTexto(valor) {
            if (String(valor) === '126') return 'Business Support Hub';
            if (String(valor) === '100') return 'Consorcio SH-QPL';
            return 'Todas las empresas';
        }

        function obtenerEmpresaActual() {
            return document.getElementById('empresa').value || '';
        }

        function destruirChart(instancia) {
            if (instancia && typeof instancia.destroy === 'function') {
                instancia.destroy();
            }
        }

        async function parsearRespuestaJson(response, contextoError) {
            const contentType = (response.headers.get('content-type') || '').toLowerCase();
            const cuerpo = await response.text();
            let payload = null;

            if (cuerpo) {
                try {
                    payload = JSON.parse(cuerpo);
                } catch (_errorParse) {
                    payload = null;
                }
            }

            if (!response.ok) {
                const mensajeServidor = payload?.message || payload?.error || '';
                const mensajeNoJson = !contentType.includes('application/json')
                    ? 'El servidor devolvio HTML/no JSON. Revisa storage/logs/laravel.log.'
                    : '';
                const detalle = [mensajeServidor, mensajeNoJson].filter(Boolean).join(' | ');
                throw new Error(contextoError + ' (HTTP ' + response.status + ')' + (detalle ? ': ' + detalle : ''));
            }

            if (!payload) {
                throw new Error(contextoError + ': Respuesta no valida en formato JSON.');
            }

            return payload;
        }

        function renderCharts(payload) {
            const estado = payload?.charts?.estado || { labels: [], series: [] };
            const salarioCiudad = payload?.charts?.salario_ciudad || { labels: [], series: [] };
            const empleadosCiudad = payload?.charts?.empleados_ciudad || { labels: [], series: [] };
            const salarioEmpresa = payload?.charts?.salario_empresa || { labels: [], series: [] };

            destruirChart(chartEstado);
            destruirChart(chartSalarioCiudad);
            destruirChart(chartEmpleadosCiudad);
            destruirChart(chartSalarioEmpresa);

            chartEstado = new ApexCharts(document.querySelector('#chartEstadoEmpleados'), {
                chart: { type: 'donut', height: 320, toolbar: { show: false } },
                series: estado.series || [],
                labels: estado.labels || [],
                colors: ['#22c55e', '#ef4444'],
                legend: { position: 'bottom' },
                dataLabels: { enabled: true },
                plotOptions: { pie: { donut: { size: '68%' } } }
            });

            chartSalarioCiudad = new ApexCharts(document.querySelector('#chartSalarioCiudad'), {
                chart: { type: 'bar', height: 320, toolbar: { show: false } },
                series: [{ name: 'Salario mensual de usuarios activos', data: salarioCiudad.series || [] }],
                xaxis: { categories: salarioCiudad.labels || [] },
                colors: ['#3b82f6'],
                plotOptions: { bar: { borderRadius: 6, horizontal: false, columnWidth: '45%' } },
                dataLabels: { enabled: false },
                yaxis: {
                    labels: {
                        formatter: function (value) {
                            return formatoMonto(value);
                        }
                    }
                },
                tooltip: {
                    y: {
                        formatter: function (value) {
                            return '$' + formatoMonto(value);
                        }
                    }
                }
            });

            chartEmpleadosCiudad = new ApexCharts(document.querySelector('#chartEmpleadosCiudad'), {
                chart: { type: 'bar', height: 320, toolbar: { show: false } },
                series: [{ name: 'Empleados', data: empleadosCiudad.series || [] }],
                xaxis: { categories: empleadosCiudad.labels || [] },
                colors: ['#14b8a6'],
                plotOptions: { bar: { borderRadius: 6, horizontal: true, barHeight: '55%' } },
                dataLabels: { enabled: false }
            });

            const salarioEmpresaSeries = salarioEmpresa.series || [];
            const salarioEmpresaTotal = salarioEmpresaSeries.reduce(function (acc, item) {
                return acc + Number(item || 0);
            }, 0);
            const salarioEmpresaPorcentaje = salarioEmpresaSeries.map(function (value) {
                if (salarioEmpresaTotal <= 0) {
                    return 0;
                }

                return Number(((Number(value || 0) / salarioEmpresaTotal) * 100).toFixed(2));
            });

            chartSalarioEmpresa = new ApexCharts(document.querySelector('#chartSalarioEmpresa'), {
                chart: { type: 'donut', height: 320, toolbar: { show: false } },
                series: salarioEmpresaPorcentaje,
                labels: salarioEmpresa.labels || [],
                colors: ['#6366f1', '#f59e0b'],
                legend: {
                    show: true,
                    position: 'bottom',
                    formatter: function (seriesName, opts) {
                        const porcentaje = salarioEmpresaPorcentaje[opts.seriesIndex] || 0;
                        const monto = salarioEmpresaSeries[opts.seriesIndex] || 0;
                        return seriesName + ' - ' + porcentaje.toFixed(2) + '% ($' + formatoMonto(monto) + ')';
                    }
                },
                dataLabels: {
                    enabled: true,
                    formatter: function (value) {
                        return value.toFixed(1) + '%';
                    }
                },
                tooltip: {
                    y: {
                        formatter: function (_value, opts) {
                            const indice = opts.seriesIndex;
                            const porcentaje = salarioEmpresaPorcentaje[indice] || 0;
                            const monto = salarioEmpresaSeries[indice] || 0;
                            return porcentaje.toFixed(2) + '% | $' + formatoMonto(monto);
                        }
                    }
                },
                plotOptions: {
                    pie: {
                        donut: {
                            size: '70%',
                            labels: {
                                show: true,
                                name: {
                                    show: true,
                                    fontSize: '16px'
                                },
                                value: {
                                    show: true,
                                    fontSize: '18px',
                                    formatter: function (value) {
                                        return Number(value || 0).toFixed(2) + '%';
                                    }
                                },
                                total: {
                                    show: true,
                                    label: 'Total',
                                    formatter: function () {
                                        return '$' + formatoMonto(salarioEmpresaTotal);
                                    }
                                }
                            }
                        }
                    }
                }
            });

            chartEstado.render();
            chartSalarioCiudad.render();
            chartEmpleadosCiudad.render();
            chartSalarioEmpresa.render();
        }

        function renderResumen(payload) {
            const resumen = payload?.resumen || {};
            document.getElementById('kpi-total-empleados').textContent = Number(resumen.total_empleados || 0).toLocaleString('en-US');
            document.getElementById('kpi-activos').textContent = Number(resumen.activos || 0).toLocaleString('en-US');
            document.getElementById('kpi-inactivos').textContent = Number(resumen.inactivos || 0).toLocaleString('en-US');
            document.getElementById('kpi-salario-total').textContent = '$' + formatoMonto(resumen.salario_mensual_activos || 0);
        }

        function renderTablaCiudades(payload) {
            const tbody = document.getElementById('tbodyResumenCiudad');
            const filas = Array.isArray(payload?.detalle_ciudad) ? payload.detalle_ciudad : [];

            tbody.innerHTML = '';
            filas.forEach(function (fila) {
                const tr = document.createElement('tr');
                tr.innerHTML = `
                    <td><div class="fw-semibold">${fila.ciudad || 'Sin ciudad'}</div></td>
                    <td class="text-center">${Number(fila.empleados || 0).toLocaleString('en-US')}</td>
                    <td class="text-center">${Number(fila.activos || 0).toLocaleString('en-US')}</td>
                    <td class="text-end fw-semibold">$${formatoMonto(fila.salario || 0)}</td>
                `;
                tbody.appendChild(tr);
            });
        }

        function actualizarBadgeEmpresa() {
            document.getElementById('badgeEmpresaActual').textContent = empresaTexto(obtenerEmpresaActual());
        }

        function cargarDashboard() {
            const empresa = obtenerEmpresaActual();
            actualizarBadgeEmpresa();

            return fetch('/empleados/dashboard?empresa=' + encodeURIComponent(empresa), {
                headers: {
                    'Accept': 'application/json',
                },
            })
                .then(response => parsearRespuestaJson(response, 'Error al cargar dashboard de empleados'))
                .then(payload => {
                    renderResumen(payload);
                    renderCharts(payload);
                    renderTablaCiudades(payload);
                })
                .catch(error => {
                    console.error('Error dashboard empleados:', error);
                    Swal.fire('Error', 'No se pudo cargar el dashboard de Recursos Humanos.', 'error');
                });
        }

        function list() {
            const empresa = obtenerEmpresaActual();

            return fetch("/empleados/list?empresa=" + encodeURIComponent(empresa), {
                headers: {
                    'Accept': 'application/json',
                },
            })
                .then(response => parsearRespuestaJson(response, 'Error al cargar listado de empleados'))
                .then(data => {
                    const tableBody = document.querySelector('#tableEmpleados tbody');
                    tableBody.innerHTML = '';

                    data.forEach(item => {
                        const activo = !item.fechasalida;
                        const row = document.createElement('tr');
                        row.innerHTML = `
                            <td>${item.company}</td>
                            <td>${item.empleadoid}</td>
                            <td>${item.nombres}</td>
                            <td>${item.apellidos}</td>
                            <td>${item.cedula ?? ''}</td>
                            <td>${item.ciudad ?? ''}</td>
                            <td class="text-end">$${formatoMonto(item.salariomensual || 0)}</td>
                            <td>${item.fechaingreso ?? ''}</td>
                            <td>${activo ? '<span class="badge bg-success-subtle text-success">Activo</span>' : '<span class="badge bg-danger-subtle text-danger">' + (item.fechasalida ?? '') + '</span>'}</td>
                        `;
                        tableBody.appendChild(row);
                    });

                    if ($.fn.DataTable.isDataTable('#tableEmpleados')) {
                        $('#tableEmpleados').DataTable().destroy();
                    }

                    $('#tableEmpleados').DataTable({
                        responsive: true,
                        scrollX: true,
                        pageLength: 10,
                        dom: 'Bfrtip',
                        buttons: ['copy', 'csv', 'excel', 'pdf', 'print']
                    });
                })
                .catch(error => {
                    console.error('Error fetching empleados:', error);
                    Swal.fire('Error', 'No se pudieron cargar los empleados.', 'error');
                });
        }

        document.querySelector("#btnSincronizar").addEventListener("click", function () {
            const empresa = document.getElementById('empresa').value;

            if (!empresa) {
                Swal.fire({
                    title: 'Empresa requerida',
                    text: 'Debe seleccionar una empresa antes de sincronizar empleados.',
                    icon: 'warning',
                    confirmButtonText: 'OK'
                });
                return;
            }

            Swal.fire({
                title: "Sincronizando: 0% ...",
                icon: 'info',
                allowOutsideClick: false,
                showConfirmButton: false,
                timerProgressBar: true,
                didOpen: () => Swal.showLoading()
            });

            let textSwal = document.querySelector('#swal2-title');
            let elapsed = 0;
            const duration = 600;
            const interval = setInterval(() => {
                elapsed += 1;
                let percent = Math.min(Math.round((elapsed / duration) * 90), 90);
                textSwal.innerHTML = "Sincronizando: " + percent + "%";
            }, 1000);

            fetch('/empleados/sincronizar?empresa=' + empresa, {
                headers: {
                    'Accept': 'application/json',
                },
            })
                .then(response => parsearRespuestaJson(response, 'Error durante la sincronizacion de empleados'))
                .then((payload) => {
                    textSwal.innerHTML = "Sincronizando: 100%";
                    clearInterval(interval);
                    Swal.fire({
                        title: "Listo",
                        text: "Sincronizacion completada con exito para " + (payload.empresa_nombre || empresaTexto(empresa)),
                        icon: "success"
                    });
                    cargarDashboard();
                    list();
                })
                .catch(error => {
                    textSwal.innerHTML = "Sincronizando: 100%";
                    clearInterval(interval);
                    Swal.fire({
                        title: "Error",
                        text: error?.message || 'No fue posible sincronizar empleados.',
                        icon: "warning"
                    });
                });
        });

        document.getElementById('empresa').addEventListener('change', function () {
            cargarDashboard().finally(function () {
                setTimeout(list, 0);
            });
        });

        document.getElementById('btnRefrescarDashboard').addEventListener('click', function () {
            cargarDashboard().finally(function () {
                setTimeout(list, 0);
            });
        });

        document.addEventListener('DOMContentLoaded', function () {
            cargarDashboard().finally(function () {
                setTimeout(list, 0);
            });
        });
    </script>
@endsection

