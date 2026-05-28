@extends('app')

@section('content')
    <div class="main-content">
        <div class="page-content">
            <div class="container-fluid">
                <style>
                    .btn-rojo-claro,
                    .btn-rojo-claro:hover,
                    .btn-rojo-claro:focus,
                    .btn-rojo-claro:active {
                        background-color: #fc0137 !important;
                        border-color: #fc0137 !important;
                        color: #fff !important;
                    }
                </style>
                <!-- start page title -->
                <div class="row">
                    <div class="col-12">
                        <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                            <h4 class="mb-sm-0">Cuadre de Ventas</h4>
                            <div class="page-title-right">
                                <ol class="breadcrumb m-0">
                                    <li class="breadcrumb-item"><a href="{{ route('inicio.index') }}">Inicio</a></li>
                                    <li class="breadcrumb-item"><a href="{{ route('reportes.index') }}">Reportes</a></li>
                                    <li class="breadcrumb-item active">Cuadre de Ventas</li>
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
                                <h5 class="card-title mb-0">Filtros</h5>
                            </div>
                            <div class="card-body">
                                <div class="row mb-3">
                                    <div class="col-md-2">
                                        <label for="sistema" class="form-label">Sistema</label>
                                        <select id="sistema" class="form-control">
                                            <option value="Lotobet">Lotobet Real</option>
                                            <option value="Lotonet">Lotonet</option>
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
                                    <div class="col-md-2">
                                        <label class="form-label">&nbsp;</label>
                                        <button type="button" class="btn btn-primary d-block w-100" id="btnBuscar">
                                            <i class="ri-search-line"></i> Buscar
                                        </button>
                                    </div>
                                    <div class="col-md-2">
                                        <label class="form-label">&nbsp;</label>
                                        <button type="button" class="btn btn-success d-block w-100" id="btnDiasFaltantes" disabled>
                                            Días faltantes del rango
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
                                <div style="overflow-x: auto;">
                                    <table id="tableCuadreVentas"
                                        class="table table-bordered table-striped align-middle"
                                        style="width:100%">
                                        <thead>
                                            <tr>
                                                <th>Fecha</th>
                                                <th>Tradicional</th>
                                                <th>No Tradicional</th>
                                                <th>Recarga</th>
                                                <th>Paquetico</th>
                                                <th>Total Día</th>
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
        let table;
        let fechasFaltantesGlobal = [];

        function parseFecha(valor) {
            if (!valor) return null;

            if (/^\d{4}-\d{2}-\d{2}$/.test(valor)) {
                return new Date(valor + 'T00:00:00');
            }

            if (/^\d{2}\/\d{2}\/\d{4}$/.test(valor)) {
                const partes = valor.split('/');
                return new Date(`${partes[2]}-${partes[1]}-${partes[0]}T00:00:00`);
            }

            return null;
        }

        function formatoFecha(isoFecha) {
            const fecha = parseFecha(isoFecha);
            if (!fecha) return isoFecha;
            return fecha.toLocaleDateString('es-DO', {
                day: '2-digit',
                month: '2-digit',
                year: 'numeric'
            });
        }

        function obtenerFechasFaltantes(fechaInicio, fechaFin, filas) {
            const inicio = parseFecha(fechaInicio);
            const fin = parseFecha(fechaFin);

            if (!inicio || !fin || inicio > fin) {
                return [];
            }

            const fechasConVenta = new Set();

            (filas || []).forEach(function(fila) {
                if (!fila || !fila.Fecha || fila.Fecha === 'TOTAL') {
                    return;
                }

                const fechaFila = parseFecha(fila.Fecha);
                if (!fechaFila) {
                    return;
                }

                fechasConVenta.add(fechaFila.toISOString().slice(0, 10));
            });

            const faltantes = [];
            const cursor = new Date(inicio);

            while (cursor <= fin) {
                const iso = cursor.toISOString().slice(0, 10);
                if (!fechasConVenta.has(iso)) {
                    faltantes.push(iso);
                }
                cursor.setDate(cursor.getDate() + 1);
            }

            return faltantes;
        }

        function actualizarBotonDiasFaltantes() {
            const btnDiasFaltantes = document.getElementById('btnDiasFaltantes');
            if (!btnDiasFaltantes) return;

            btnDiasFaltantes.classList.remove('btn-success', 'btn-rojo-claro');

            if (!fechasFaltantesGlobal.length) {
                btnDiasFaltantes.classList.add('btn-success');
                btnDiasFaltantes.textContent = 'No faltan días del rango';
                btnDiasFaltantes.disabled = true;
                return;
            }

            btnDiasFaltantes.classList.add('btn-rojo-claro');
            btnDiasFaltantes.textContent = `Faltan ${fechasFaltantesGlobal.length} días del rango`;
            btnDiasFaltantes.disabled = false;
        }

        function mostrarDiasFaltantes(event) {
            event.preventDefault();

            if (!fechasFaltantesGlobal.length) {
                return;
            }

            const listaFechas = fechasFaltantesGlobal
                .map(function(fecha) {
                    return `<li>${formatoFecha(fecha)}</li>`;
                })
                .join('');

            Swal.fire({
                title: 'Fechas sin ventas en el rango',
                html: `<ul class="text-start mb-0 ps-4">${listaFechas}</ul>`,
                icon: 'info',
                confirmButtonText: 'Cerrar',
                width: 520
            });
        }

        function cargarDatos() {
            const sistema = document.getElementById('sistema').value;
            const fechaInicio = document.getElementById('fecha_inicio').value;
            const fechaFin = document.getElementById('fecha_fin').value;

            if (!fechaInicio || !fechaFin) {
                Swal.fire({
                    title: 'Error',
                    text: 'Seleccione las fechas de inicio y fin',
                    icon: 'error'
                });
                return;
            }

            if (fechaInicio > fechaFin) {
                Swal.fire({
                    title: 'Error',
                    text: 'La fecha de inicio no puede ser mayor a la fecha fin',
                    icon: 'error'
                });
                return;
            }

            Swal.fire({
                title: 'Cargando datos...',
                allowOutsideClick: false,
                didOpen: () => Swal.showLoading()
            });

            if (table) {
                table.clear().destroy();
                $('#tableCuadreVentas tbody').empty();
            }

            fechasFaltantesGlobal = [];
            actualizarBotonDiasFaltantes();

            table = $('#tableCuadreVentas').DataTable({
                ajax: {
                    url: '/reportes-cuadre-ventas/list',
                    type: 'GET',
                    data: {
                        sistema: sistema,
                        fecha_inicio: fechaInicio,
                        fecha_fin: fechaFin
                    },
                    dataSrc: function(json) {
                        fechasFaltantesGlobal = obtenerFechasFaltantes(fechaInicio, fechaFin, json || []);
                        actualizarBotonDiasFaltantes();
                        return json || [];
                    },
                    complete: function(jqXHR, textStatus) {
                        if (textStatus !== 'error') {
                            Swal.close();
                        }
                    },
                    error: function() {
                        fechasFaltantesGlobal = [];
                        actualizarBotonDiasFaltantes();

                        Swal.fire({
                            title: 'Error',
                            text: 'No se pudieron cargar los datos del cuadre de ventas.',
                            icon: 'error'
                        });
                    }
                },
                columns: [
                    { data: 'Fecha' },
                    { data: 'Tradicional', className: 'text-end' },
                    { data: 'No_Tradicional', className: 'text-end' },
                    { data: 'Recarga', className: 'text-end' },
                    { data: 'Paquetico', className: 'text-end' },
                    { data: 'Total_Dia', className: 'text-end' }
                ],
                autoWidth: false,
                responsive: true,
                scrollX: true,
                columnDefs: [
                    { targets: [2, 3, 4], visible: $(window).width() > 768 }
                ],
                dom: 'Bfrtip',
                buttons: [
                    'copy', 'csv', 'excel', 'pdf', 'print'
                ],
                language: {
                    url: '//cdn.datatables.net/plug-ins/1.13.7/i18n/es-ES.json'
                },
                order: [[0, 'asc']],
                paging: false,
                searching: false,
                info: false,
                createdRow: function(row, data, dataIndex) {
                    // Destacar la fila de TOTAL
                    if (data.Fecha === 'TOTAL') {
                        $(row).addClass('table-warning fw-bold');
                    }
                }
            });
        }

        document.getElementById('btnBuscar').addEventListener('click', cargarDatos);
        document.getElementById('btnDiasFaltantes').addEventListener('click', mostrarDiasFaltantes);

        actualizarBotonDiasFaltantes();

        // Cargar datos automáticamente al inicio
        document.addEventListener('DOMContentLoaded', function() {
            // cargarDatos();
        });
    </script>
@endsection

