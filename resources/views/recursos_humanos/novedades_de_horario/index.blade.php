@extends('app')

@section('content')
    <div class="main-content">
        <div class="page-content">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-12">
                        <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                            <h4 class="mb-sm-0">Novedades_de_Horario</h4>
                            <div class="page-title-right">
                                <ol class="breadcrumb m-0">
                                    <li class="breadcrumb-item"><a href="{{ route('inicio.index') }}">Inicio</a></li>
                                    <li class="breadcrumb-item"><a href="{{ route('recursos-humanos.index') }}">Recursos Humanos</a></li>
                                    <li class="breadcrumb-item active">Novedades de Horario</li>
                                </ol>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-lg-12">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="card-title mb-0">Filtros</h5>
                            </div>
                            <div class="card-body">
                                <div class="row g-3 align-items-end">
                                    <div class="col-md-3">
                                        <label for="sistema" class="form-label">Sistema</label>
                                        <select id="sistema" class="form-control">
                                            <option value="todos">Todos</option>
                                            <option value="lotobet">Lotobet</option>
                                            <option value="lotonet">Lotonet</option>
                                        </select>
                                    </div>
                                    <div class="col-md-3">
                                        <label for="fecha_inicio" class="form-label">Fecha Inicio</label>
                                        <input type="date" id="fecha_inicio" class="form-control" value="{{ date('Y-m-01') }}">
                                    </div>
                                    <div class="col-md-3">
                                        <label for="fecha_fin" class="form-label">Fecha Fin</label>
                                        <input type="date" id="fecha_fin" class="form-control" value="{{ date('Y-m-d') }}">
                                    </div>
                                    <div class="col-md-3">
                                        <button type="button" class="btn btn-primary w-100" id="btnBuscar">
                                            <i class="ri-search-line"></i> Consultar
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-xl-3 col-md-6">
                        <div class="card card-animate">
                            <div class="card-body">
                                <p class="text-uppercase fw-medium text-muted mb-1">Total Registros</p>
                                <h4 class="mb-0" id="totalRegistros">0</h4>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-3 col-md-6">
                        <div class="card card-animate">
                            <div class="card-body">
                                <p class="text-uppercase fw-medium text-muted mb-1">Terminales</p>
                                <h4 class="mb-0" id="totalTerminales">0</h4>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-3 col-md-6">
                        <div class="card card-animate">
                            <div class="card-body">
                                <p class="text-uppercase fw-medium text-muted mb-1">Agencias</p>
                                <h4 class="mb-0" id="totalAgencias">0</h4>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-3 col-md-6">
                        <div class="card card-animate">
                            <div class="card-body">
                                <p class="text-uppercase fw-medium text-muted mb-1">Horas Acumuladas</p>
                                <h4 class="mb-0" id="totalHorasAcumuladas">0.00</h4>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-lg-12">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="card-title mb-0">Listado de Novedades</h5>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table id="tableNovedadesHorario" class="table table-bordered table-striped align-middle" style="width: 100%;">
                                        <thead>
                                            <tr>
                                                <th>Terminal</th>
                                                <th>Nombre de Agencia</th>
                                                <th>Ruta</th>
                                                <th>Nombre de Empleado</th>
                                                <th>Cedula</th>
                                                <th>Fecha</th>
                                                <th>Primer Login</th>
                                                <th>Ultimo Login</th>
                                                <th class="text-end">Horas_Acumuladas</th>
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
@endsection

@section('script')
    <script>
        let tableNovedadesHorario;

        function formatearNumero(valor) {
            return Number(valor || 0).toLocaleString('en-US', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            });
        }

        function actualizarResumen(resumen) {
            document.getElementById('totalRegistros').textContent = Number(resumen.total || 0).toLocaleString('en-US');
            document.getElementById('totalTerminales').textContent = Number(resumen.terminales || 0).toLocaleString('en-US');
            document.getElementById('totalAgencias').textContent = Number(resumen.agencias || 0).toLocaleString('en-US');
            document.getElementById('totalHorasAcumuladas').textContent = formatearNumero(resumen.horas_acumuladas);
        }

        function cargarNovedadesHorario() {
            const sistema = document.getElementById('sistema').value;
            const fechaInicio = document.getElementById('fecha_inicio').value;
            const fechaFin = document.getElementById('fecha_fin').value;

            if (!fechaInicio || !fechaFin) {
                Swal.fire('Error', 'Seleccione la fecha de inicio y fin.', 'error');
                return;
            }

            if (fechaInicio && fechaFin && fechaInicio > fechaFin) {
                Swal.fire('Error', 'La fecha de inicio no puede ser mayor que la fecha fin.', 'error');
                return;
            }

            if (tableNovedadesHorario) {
                tableNovedadesHorario.destroy();
            }

            tableNovedadesHorario = $('#tableNovedadesHorario').DataTable({
                processing: true,
                serverSide: true,
                searchDelay: 450,
                ajax: {
                    url: '/recursos-humanos/novedades-horario/list',
                    type: 'GET',
                    data: {
                        sistema: sistema,
                        fecha_inicio: fechaInicio,
                        fecha_fin: fechaFin
                    },
                    dataSrc: function (json) {
                        actualizarResumen(json.resumen || {});
                        return json.data || [];
                    }
                },
                columns: [
                    { data: 'terminal' },
                    { data: 'nombre_agencia' },
                    { data: 'ruta' },
                    { data: 'nombre_empleado' },
                    { data: 'cedula' },
                    { data: 'fecha' },
                    { data: 'primer_login' },
                    { data: 'ultimo_login' },
                    {
                        data: 'horas_acumuladas',
                        className: 'text-end',
                        render: function (data) {
                            return formatearNumero(data);
                        }
                    }
                ],
                autoWidth: false,
                language: {
                    url: '//cdn.datatables.net/plug-ins/1.13.7/i18n/es-ES.json'
                },
                order: [[0, 'desc']],
                pageLength: 25,
                lengthMenu: [25, 50, 100, 200]
            });
        }

        document.getElementById('btnBuscar').addEventListener('click', cargarNovedadesHorario);
    </script>
@endsection
