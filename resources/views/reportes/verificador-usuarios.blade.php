@extends('app')

@section('content')
    <div class="main-content">

        <div class="page-content">
            <div class="container-fluid">

                <!-- start page title -->
                <div class="row">
                    <div class="col-12">
                        <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                            <h4 class="mb-sm-0">Verificador de Usuarios</h4>

                            <div class="page-title-right">
                                <ol class="breadcrumb m-0">
                                    <li class="breadcrumb-item"><a href="{{ route('inicio.index') }}">Inicio</a></li>
                                    <li class="breadcrumb-item"><a href="{{ route('reportes.index') }}">Reportes</a></li>
                                    <li class="breadcrumb-item active">Verificador de Usuarios</li>
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
                                <h5 class="card-title mb-0">Asistencias y Faltantes por Usuario</h5>

                                <div class="d-flex gap-3 align-items-center justify-content-between flex-wrap">
                                    <div>
                                        <label class="mb-0" for="fecha_inicio">Desde</label>
                                        <input type="date" class="form-control" id="fecha_inicio">
                                    </div>

                                    <div>
                                        <label class="mb-0" for="fecha_fin">Hasta</label>
                                        <input type="date" class="form-control" id="fecha_fin">
                                    </div>

                                    <div>
                                        <label class="mb-0" for="sistema">Sistema</label>
                                        <select class="form-control" id="sistema">
                                            <option value="todos">Todos</option>
                                            <option value="lotobet">Lotobet</option>
                                            <option value="lotonet">Lotonet</option>
                                        </select>
                                    </div>

                                    <button id="btnFiltrar" class="btn btn-primary">
                                        Filtrar
                                    </button>

                                    <button id="btnExportarExcel" class="btn btn-success">
                                        Exportar Excel
                                    </button>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive" style="width:100%; height:525px; max-height:525px; overflow-y:scroll;">
                                    <table id="tableVerificador"
                                        class="table table-bordered dt-responsive nowrap table-striped align-middle"
                                        style="width:100%; font-size: 12px;">
                                        <thead>
                                            <tr>
                                                <th>Empleado ID</th>
                                                <th>Nombres</th>
                                                <th>Apellidos</th>
                                                <th>CÃ©dula</th>
                                                <th class="text-center">Horas NET</th>
                                                <th class="text-center">Horas BET</th>
                                                <th class="text-center">Horas Total</th>
                                                <th class="text-center">Faltantes NET</th>
                                                <th class="text-center">Faltantes BET</th>
                                                <th class="text-center">Faltantes Total</th>
                                                <th class="text-end">Monto Falt. NET</th>
                                                <th class="text-end">Monto Falt. BET</th>
                                                <th class="text-end">Monto Falt. Total</th>
                                                <th>Comentario</th>
                                            </tr>
                                        </thead>
                                        <tbody></tbody>
                                    </table>
                                </div>
                                <div class="d-flex flex-sm-fill d-sm-flex align-items-sm-center justify-content-sm-between mt-3">
                                    <div>
                                        <p class="small text-muted">
                                            Total de registros:
                                            <span id="totalRegistros" class="fw-semibold">0</span>
                                        </p>
                                    </div>
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
                        </script> Â© ERP.
                    </div>
                </div>
            </div>
        </footer>
    </div>

    <script>
        const urlBase = '/reportes-verificador-usuarios';

        // Cargar datos iniciales
        document.addEventListener('DOMContentLoaded', function() {
            // Establecer fechas por defecto (mes actual)
            const hoy = new Date();
            const primerDia = new Date(hoy.getFullYear(), hoy.getMonth(), 1);
            const ultimoDia = new Date(hoy.getFullYear(), hoy.getMonth() + 1, 0);

            document.getElementById('fecha_inicio').value = primerDia.toISOString().split('T')[0];
            document.getElementById('fecha_fin').value = ultimoDia.toISOString().split('T')[0];

            cargarDatos();
        });

        function cargarDatos() {
            const fechaInicio = document.getElementById('fecha_inicio').value;
            const fechaFin = document.getElementById('fecha_fin').value;
            const sistema = document.getElementById('sistema').value;

            if (!fechaInicio || !fechaFin) {
                alert('Por favor seleccione las fechas');
                return;
            }

            const params = new URLSearchParams({
                fecha_inicio: fechaInicio,
                fecha_fin: fechaFin,
                sistema: sistema
            });

            // Mostrar loading
            const tbody = document.querySelector('#tableVerificador tbody');
            tbody.innerHTML = '<tr><td colspan="14" class="text-center">Cargando...</td></tr>';

            fetch(`${urlBase}/list?${params}`)
                .then(response => response.json())
                .then(data => {
                    mostrarDatos(data);
                })
                .catch(error => {
                    console.error('Error:', error);
                    tbody.innerHTML = '<tr><td colspan="14" class="text-center text-danger">Error al cargar los datos</td></tr>';
                });
        }

        function mostrarDatos(data) {
            const tbody = document.querySelector('#tableVerificador tbody');
            tbody.innerHTML = '';

            if (!data || data.length === 0) {
                tbody.innerHTML = '<tr><td colspan="14" class="text-center">No se encontraron registros</td></tr>';
                document.getElementById('totalRegistros').textContent = '0';
                return;
            }

            data.forEach(registro => {
                const row = document.createElement('tr');
                
                // Resaltar filas con comentario (sin empleado)
                if (registro.comentario && registro.comentario !== '') {
                    row.classList.add('table-warning');
                }

                row.innerHTML = `
                    <td class="text-center">${registro.empleadoid || '-'}</td>
                    <td>${registro.nombres || '-'}</td>
                    <td>${registro.apellidos || '-'}</td>
                    <td>${registro.cedula || '-'}</td>
                    <td class="text-center">${parseFloat(registro.horas_net).toFixed(2)}</td>
                    <td class="text-center">${parseFloat(registro.horas_bet).toFixed(2)}</td>
                    <td class="text-center"><strong>${parseFloat(registro.horas_total).toFixed(2)}</strong></td>
                    <td class="text-center">${registro.cant_faltantes_net}</td>
                    <td class="text-center">${registro.cant_faltantes_bet}</td>
                    <td class="text-center"><strong>${registro.cant_faltantes_total}</strong></td>
                    <td class="text-end">$${parseFloat(registro.monto_faltantes_net).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2})}</td>
                    <td class="text-end">$${parseFloat(registro.monto_faltantes_bet).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2})}</td>
                    <td class="text-end"><strong>$${parseFloat(registro.monto_faltantes_total).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2})}</strong></td>
                    <td>${registro.comentario ? '<span class="badge bg-warning">' + registro.comentario + '</span>' : ''}</td>
                `;
                tbody.appendChild(row);
            });

            // Actualizar contador
            document.getElementById('totalRegistros').textContent = data.length;
        }

        document.getElementById('btnFiltrar').addEventListener('click', function() {
            cargarDatos();
        });

        document.getElementById('btnExportarExcel').addEventListener('click', function() {
            const fechaInicio = document.getElementById('fecha_inicio').value;
            const fechaFin = document.getElementById('fecha_fin').value;
            const sistema = document.getElementById('sistema').value;

            if (!fechaInicio || !fechaFin) {
                alert('Por favor seleccione las fechas');
                return;
            }

            const params = new URLSearchParams({
                fecha_inicio: fechaInicio,
                fecha_fin: fechaFin,
                sistema: sistema
            });

            window.location.href = `${urlBase}/excel?${params}`;
        });
    </script>
@endsection

