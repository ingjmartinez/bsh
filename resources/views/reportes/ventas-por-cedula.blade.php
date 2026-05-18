@extends('app')

@section('content')
    <div class="main-content">
        <div class="page-content">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-12">
                        <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                            <h4 class="mb-sm-0">Ventas por CÃ©dula</h4>
                            <div class="page-title-right">
                                <ol class="breadcrumb m-0">
                                    <li class="breadcrumb-item"><a href="{{ route('inicio.index') }}">Inicio</a></li>
                                    <li class="breadcrumb-item"><a href="{{ route('reportes.index') }}">Reportes</a></li>
                                    <li class="breadcrumb-item active">Ventas por CÃ©dula</li>
                                </ol>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-lg-12">
                        <div class="card">
                            <div class="card-header d-flex align-items-center justify-content-between">
                                <h5 class="card-title mb-0">Filtros</h5>
                            </div>
                            <div class="card-body">
                                <div class="row g-3 align-items-end">
                                    <div class="col-md-3">
                                        <label for="cedula" class="form-label">CÃ©dula</label>
                                        <input type="text" id="cedula" class="form-control" placeholder="Ej: 40240461497">
                                    </div>
                                    <div class="col-md-2">
                                        <label for="sistema" class="form-label">Sistema</label>
                                        <select id="sistema" class="form-control">
                                            <option value="todos">Todos</option>
                                            <option value="lotonet">Lotonet</option>
                                            <option value="lotobet">Lotobet</option>
                                        </select>
                                    </div>
                                    <div class="col-md-2">
                                        <label for="fecha_inicio" class="form-label">Fecha Inicio</label>
                                        <input type="date" id="fecha_inicio" class="form-control" value="{{ date('Y-m-01') }}">
                                    </div>
                                    <div class="col-md-2">
                                        <label for="fecha_fin" class="form-label">Fecha Fin</label>
                                        <input type="date" id="fecha_fin" class="form-control" value="{{ date('Y-m-d') }}">
                                    </div>
                                    <div class="col-md-2">
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
                    <div class="col-lg-12">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="card-title mb-0">Resultados</h5>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table id="tableVentasCedula" class="table table-bordered table-striped align-middle" style="width: 100%;">
                                        <thead>
                                            <tr>
                                                <th>IdentificaciÃ³n</th>
                                                <th>DÃ­a</th>
                                                <th>Agencia</th>
                                                <th class="text-end">Total DÃ­a Agencia</th>
                                            </tr>
                                        </thead>
                                        <tbody></tbody>
                                    </table>
                                </div>
                                <div class="mt-2">
                                    <small class="text-muted">Total de registros: <span id="totalRegistros" class="fw-semibold">0</span></small>
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
        let table;

        function cargarDatos() {
            const cedula = document.getElementById('cedula').value.trim();
            const sistema = document.getElementById('sistema').value;
            const fechaInicio = document.getElementById('fecha_inicio').value;
            const fechaFin = document.getElementById('fecha_fin').value;

            if (!cedula) {
                Swal.fire({
                    title: 'Error',
                    text: 'Ingrese una cÃ©dula para consultar',
                    icon: 'error'
                });
                return;
            }

            if (!fechaInicio || !fechaFin) {
                Swal.fire({
                    title: 'Error',
                    text: 'Seleccione la fecha de inicio y fin',
                    icon: 'error'
                });
                return;
            }

            if (fechaInicio > fechaFin) {
                Swal.fire({
                    title: 'Error',
                    text: 'La fecha de inicio no puede ser mayor que la fecha fin',
                    icon: 'error'
                });
                return;
            }

            Swal.fire({
                title: 'Consultando... ',
                allowOutsideClick: false,
                didOpen: () => Swal.showLoading()
            });

            if (table) {
                table.destroy();
            }

            table = $('#tableVentasCedula').DataTable({
                ajax: {
                    url: '/reportes-ventas-por-cedula/list',
                    type: 'GET',
                    data: {
                        cedula: cedula,
                        sistema: sistema,
                        fecha_inicio: fechaInicio,
                        fecha_fin: fechaFin
                    },
                    dataSrc: '',
                    complete: function() {
                        Swal.close();
                    }
                },
                columns: [
                    { data: 'Identificacion' },
                    { data: 'Dia' },
                    { data: 'Agencia' },
                    {
                        data: 'Total_Dia_Agencia',
                        className: 'text-end',
                        render: function(data) {
                            return parseFloat(data || 0).toLocaleString('en-US', {
                                minimumFractionDigits: 2,
                                maximumFractionDigits: 2
                            });
                        }
                    }
                ],
                autoWidth: false,
                dom: 'Bfrtip',
                buttons: ['copy', 'csv', 'excel', 'pdf', 'print'],
                language: {
                    url: '//cdn.datatables.net/plug-ins/1.13.7/i18n/es-ES.json'
                },
                order: [[1, 'asc'], [3, 'desc']],
                paging: false,
                searching: false,
                info: false,
                drawCallback: function(settings) {
                    const total = settings?.json ? settings.json.length : 0;
                    document.getElementById('totalRegistros').textContent = total;
                }
            });
        }

        document.getElementById('btnBuscar').addEventListener('click', cargarDatos);
        document.getElementById('cedula').addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                cargarDatos();
            }
        });
    </script>
@endsection

