@extends('app')

@section('content')
    <div class="main-content">
        <div class="page-content">
            <div class="container-fluid">
                <!-- start page title -->
                <div class="row">
                    <div class="col-12">
                        <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                            <h4 class="mb-sm-0">Incentivos Reporte de Pagos</h4>

                            <div class="page-title-right">
                                <ol class="breadcrumb m-0">
                                    <li class="breadcrumb-item"><a href="{{ route('inicio.index') }}">Inicio</a></li>
                                    <li class="breadcrumb-item"><a href="{{ route('incentivos.index') }}">Incentivos</a></li>
                                    <li class="breadcrumb-item active">Reporte de Pagos</li>
                                </ol>
                            </div>

                        </div>
                    </div>
                </div>
                <!-- end page title -->

                <div class="row">
                    <div class="col-lg-12">
                        <div class="card">
                            <div class="card-header d-flex align-items-center justify-content-between">
                                <div>
                                    <h3 class="mb-0">Total a pagar: <span id="totalPago"></span></h1>
                                </div>
                                <div class="d-flex gap-3 align-items-center justify-content-between">
                                    <div class="d-flex flex-column gap-2">
                                        <div class="d-flex gap-3 align-items-center justify-content-between">
                                            <div>
                                                <div><label class="mb-0" for="tipo">Tipo</label></div>
                                                <select id="tipo" class="form-select">
                                                    <option value="">Todos</option>
                                                    <option value="1">Agente de Venta</option>
                                                    <option value="2">Coordinador</option>
                                                    <option value="3">Administrativo</option>
                                                    <option value="4">Operador</option>
                                                </select>
                                            </div>
                                            <div>
                                                <div><label class="mb-0" for="empresa">Empresa</label></div>
                                                <select id="empresa" class="form-select">
                                                    <option value="">Todas</option>
                                                    <option value="168">BSH Support</option>
                                                    <option value="169">Negosur</option>
                                                </select>
                                            </div>
                                            <div>
                                                <div><label class="mb-0" for="year">AÃ±o</label></div>
                                                <select id="year" class="form-select">
                                                    <option value="2026">2026</option>
                                                    <option value="2025">2025</option>
                                                    <option value="2024">2024</option>
                                                    <option value="2023">2023</option>
                                                    <option value="2022">2022</option>
                                                    <option value="2021">2021</option>
                                                </select>
                                            </div>
                                            <div>
                                                <div><label class="mb-0" for="mes">Mes</label></div>
                                                <select id="mes" class="form-select">
                                                    <option value="">Seleccione</option>
                                                    <option value="1">Enero</option>
                                                    <option value="2">Febrero</option>
                                                    <option value="3">Marzo</option>
                                                    <option value="4">Abril</option>
                                                    <option value="5">Mayo</option>
                                                    <option value="6">Junio</option>
                                                    <option value="7">Julio</option>
                                                    <option value="8">Agosto</option>
                                                    <option value="9">Septiembre</option>
                                                    <option value="10">Octubre</option>
                                                    <option value="11">Noviembre</option>
                                                    <option value="12">Diciembre</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="d-flex gap-3 align-items-center justify-content-between">
                                            <div>
                                                <div><label class="mb-0" for="califican">Califican</label></div>
                                                <select id="califican" class="form-select">
                                                    <option value="1">Todos</option>
                                                    <option value="2">Califican</option>
                                                    <option value="3">No Califican</option>
                                                </select>
                                            </div>
                                            <div>
                                                <div><label class="mb-0" for="horas">Horas</label></div>
                                                <select id="horas" class="form-select">
                                                    <option value="1">Todos</option>
                                                    <option value="2">> 150</option>
                                                </select>
                                            </div>
                                            <div>
                                                <div><label class="mb-0" for="pago">Pago</label></div>
                                                <select id="pago" class="form-select">
                                                    <option value="1">Todos</option>
                                                    <option value="2">< $200.00</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                    <button type="button" class="btn btn-primary" id="btnGenerar">
                                        Genarar Data
                                    </button>
                                </div>
                            </div>
                            <div class="card-body">
                                <table id="table"
                                    class="table table-bordered dt-responsive nowrap table-striped align-middle"
                                    style="width:100%">
                                    <thead>
                                        <tr>
                                            <th>Empresa</th>
                                            <th>Tipo Empleado</th>
                                            <th>ID Empleado</th>
                                            <th>Nombres y Apellidos</th>
                                            <th>Cedula</th>
                                            <th>Cuenta</th>
                                            <th>Monto a pagar</th>
                                        </tr>
                                    </thead>
                                    <tbody></tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-12">
                        <div class="card">
                            <div class="card-header d-flex align-items-center justify-content-between">
                                <div>
                                    <h5 class="card-title mb-0">Reporte Nuevo Incentivo</h5>
                                    <small class="text-muted">EvalÃºa el Ãºltimo mes dentro del rango seleccionado.</small>
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
                                        <label class="mb-0" for="ni_fecha_ini">Fecha inicio</label>
                                        <input type="date" id="ni_fecha_ini" class="form-control">
                                    </div>
                                    <div>
                                        <label class="mb-0" for="ni_fecha_fin">Fecha fin</label>
                                        <input type="date" id="ni_fecha_fin" class="form-control">
                                    </div>
                                    <div>
                                        <label class="mb-0" for="ni_minimo_agencia">MÃ­nimo agencia</label>
                                        <input type="number" id="ni_minimo_agencia" class="form-control" value="80000" min="0" step="0.01">
                                    </div>
                                    <div>
                                        <label class="mb-0" for="ni_min_dias">MÃ­n. dÃ­as venta</label>
                                        <input type="number" id="ni_min_dias" class="form-control" value="10" min="1" step="1">
                                    </div>
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
                                            <th>CÃ©dula</th>
                                            <th>Ventas Ãšltimo Mes</th>
                                            <th>DÃ­as Ventas Ãšltimo Mes</th>
                                            <th>MÃ­nimo Agencia</th>
                                            <th>Cumple MÃ­nimo</th>
                                            <th>% ComisiÃ³n</th>
                                            <th>Nuevo Incentivo</th>
                                        </tr>
                                    </thead>
                                    <tbody></tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div><!--end row-->
            </div>
            <!-- container-fluid -->
        </div>
    </div>
    <!-- end main content-->
@endsection

@section('script')
    <script>
        let datosToSave = [];

        document.addEventListener('DOMContentLoaded', () => {
            const fechaHoy = new Date();
            const yyyy = fechaHoy.getFullYear();
            const mm = String(fechaHoy.getMonth() + 1).padStart(2, '0');
            const dd = String(fechaHoy.getDate()).padStart(2, '0');

            document.getElementById('ni_fecha_fin').value = `${yyyy}-${mm}-${dd}`;
            document.getElementById('ni_fecha_ini').value = `${yyyy}-${mm}-01`;
        });

        function list() {
            let totalPago = 0;
            datosToSave = [];
            let year = document.getElementById('year').value;
            let mes = document.getElementById('mes').value;
            let empresa = document.getElementById('empresa').value;
            let tipo = document.getElementById('tipo').value;
            let califican = document.getElementById('califican').value;
            let horas = document.getElementById('horas').value;
            let pago = document.getElementById('pago').value;

            Swal.fire({
                title: "Procesando InformaciÃ³n ...",
                icon: 'info',
                allowOutsideClick: false,
                showConfirmButton: false,
                timerProgressBar: true,
                didOpen: () => Swal.showLoading()
            });

            $('#table').DataTable().destroy();
            $('#table tbody').empty();
            fetch("/incentivos/reporte-pago-incentivos?mes=" + mes + "&empresa=" + empresa + "&tipo=" + tipo + "&year=" +
                    year + "&califican=" + califican + "&horas=" + horas + "&pago=" + pago)
                .then(response => response.json())
                .then(data => {
                    if ('message' in data) {
                        Swal.fire({
                            title: "InformaciÃ³n",
                            text: data.message,
                            icon: "warning"
                        });
                        return;
                    }

                    const tableBody = document.querySelector('#table tbody');
                    tableBody.innerHTML = ''; // Limpiar filas existentes

                    data.forEach(item => {
                        const row = document.createElement('tr');
                        row.innerHTML = `
                            <td>${item.company}</td>
                            <td>${item.tipo}</td>
                            <td>${item.empleado_id}</td>
                            <td>${item.nombres}</td>
                            <td>${item.cedula}</td>
                            <td>${item.cuenta}</td>
                            <td>${item.monto}</td>
                        `;
                        tableBody.appendChild(row);
                    });

                    // Calcular el total
                    let total = data.reduce((sum, item) => {
                        return sum + parseFloat(item.monto.replace(/,/g, ''));
                    }, 0);
                    document.getElementById('totalPago').innerText = new Intl.NumberFormat('en-US', {
                        style: 'currency',
                        currency: 'USD'
                    }).format(total);

                    var table = $('#table').DataTable({
                        responsive: true,
                        dom: 'Bfrtip',
                        buttons: [
                            'copy', 'csv', 'excel', 'pdf', 'print'
                        ],
                        order: [
                            [0, 'asc'],
                            [2, 'asc']
                        ],
                        pageLength: 10000,
                        scrollY: '500px',
                        scrollCollapse: true,
                        language: {
                            lengthMenu: "Mostrar _MENU_ registros por pÃ¡gina",
                            info: "Mostrando _START_ a _END_ de _TOTAL_ registros",
                            infoEmpty: "No hay registros disponibles",
                            infoFiltered: "(filtrado de _MAX_ registros totales)",
                            search: "Buscar:",
                            paginate: {
                                first: "Primero",
                                last: "Ãšltimo",
                                next: "Siguiente",
                                previous: "Anterior"
                            }
                        }
                    });
                    Swal.close();
                })
                .catch(error => {
                    Swal.fire({
                        title: "Error",
                        text: error,
                        icon: "warning"
                    });
                });
        }

        function listNuevoIncentivo() {
            const sistema = document.getElementById('ni_sistema').value;
            const fechaIni = document.getElementById('ni_fecha_ini').value;
            const fechaFin = document.getElementById('ni_fecha_fin').value;
            const minimoAgencia = document.getElementById('ni_minimo_agencia').value;
            const minDias = document.getElementById('ni_min_dias').value;

            if (!fechaIni || !fechaFin) {
                Swal.fire({
                    title: 'InformaciÃ³n',
                    text: 'Debe seleccionar fecha inicio y fecha fin.',
                    icon: 'warning'
                });
                return;
            }

            Swal.fire({
                title: 'Procesando InformaciÃ³n ...',
                icon: 'info',
                allowOutsideClick: false,
                showConfirmButton: false,
                timerProgressBar: true,
                didOpen: () => Swal.showLoading()
            });

            if ($.fn.DataTable.isDataTable('#tableNuevoIncentivo')) {
                $('#tableNuevoIncentivo').DataTable().destroy();
            }
            $('#tableNuevoIncentivo tbody').empty();

            const params = new URLSearchParams({
                sistema: sistema,
                fecha_ini: fechaIni,
                fecha_fin: fechaFin,
                minimo_agencia: minimoAgencia,
                min_dias_venta: minDias,
            });

            fetch('/incentivos/reporte-nuevo-incentivo?' + params.toString())
                .then(response => response.json())
                .then(resp => {
                    if ('message' in resp) {
                        Swal.fire({
                            title: 'InformaciÃ³n',
                            text: resp.message,
                            icon: 'warning'
                        });
                        return;
                    }

                    const data = resp.data || [];
                    const tableBody = document.querySelector('#tableNuevoIncentivo tbody');
                    tableBody.innerHTML = '';

                    data.forEach(item => {
                        const row = document.createElement('tr');
                        row.innerHTML = `
                            <td>${item.cedula}</td>
                            <td>${item.ventas_ultimo_mes}</td>
                            <td>${item.dias_ventas_ultimo_mes}</td>
                            <td>${item.minimo_agencia}</td>
                            <td>${item.cumple_minimo}</td>
                            <td>${item.pct_comision}</td>
                            <td>${item.nuevo_incentivo}</td>
                        `;
                        tableBody.appendChild(row);
                    });

                    const meta = resp.meta || {};
                    document.getElementById('ni_rango_evaluado').textContent =
                        `Mes evaluado: ${meta.eval_ini || ''} al ${meta.eval_fin || ''}`;

                    $('#tableNuevoIncentivo').DataTable({
                        responsive: true,
                        dom: 'Bfrtip',
                        buttons: [
                            'copy', 'csv', 'excel', 'pdf', 'print'
                        ],
                        order: [[1, 'desc']],
                        pageLength: 10000,
                        scrollY: '500px',
                        scrollCollapse: true,
                        language: {
                            lengthMenu: 'Mostrar _MENU_ registros por pÃ¡gina',
                            info: 'Mostrando _START_ a _END_ de _TOTAL_ registros',
                            infoEmpty: 'No hay registros disponibles',
                            infoFiltered: '(filtrado de _MAX_ registros totales)',
                            search: 'Buscar:',
                            paginate: {
                                first: 'Primero',
                                last: 'Ãšltimo',
                                next: 'Siguiente',
                                previous: 'Anterior'
                            }
                        }
                    });

                    Swal.close();
                })
                .catch(error => {
                    Swal.fire({
                        title: 'Error',
                        text: error,
                        icon: 'warning'
                    });
                });
        }

        document.querySelector("#btnGenerar").addEventListener('click', function() {
            list();
        });

        document.querySelector('#btnGenerarNuevoIncentivo').addEventListener('click', function() {
            listNuevoIncentivo();
        });
    </script>
@endsection

