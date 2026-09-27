@extends('app')

@section('content')
    <div class="main-content">
        <div class="page-content">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-12">
                        <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                            <h4 class="mb-sm-0">Novedades de Horario</h4>
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
                                            <option value="lotobet">Lotobet Real</option>
                                            <option value="lotedom">Lotedom</option>
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
                                        <label for="detalle_filtro" class="form-label">Detalle</label>
                                        <select id="detalle_filtro" class="form-control">
                                            <option value="todos">Todos</option>
                                            <option value="cumple">Cumple</option>
                                            <option value="tiene_falta">Tiene falta</option>
                                        </select>
                                    </div>
                                    <div class="col-md-3">
                                        <button type="button" class="btn btn-info w-100" id="btnConfigurarHorario">
                                            <i class="ri-settings-3-line"></i> Configurar horario
                                        </button>
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
                            <div class="card-header d-flex align-items-center justify-content-between">
                                <h5 class="card-title mb-0">Listado de Novedades</h5>
                                <div class="d-flex gap-2">
                                    <button type="button" class="btn btn-outline-success btn-sm" id="btnExportarPagoExcel">Novedad de Pago</button>
                                    <button type="button" class="btn btn-success btn-sm" id="btnExportarExcel">Exportar Excel</button>
                                </div>
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
                                                <th>Cédula</th>
                                                <th>Fecha</th>
                                                <th>Primer Login</th>
                                                <th>Último Login</th>
                                                <th class="text-end">Horas Acumuladas</th>
                                                <th>Detalle</th>
                                                <th>Acción</th>
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

    <div class="modal fade" id="detalleFaltantesHorarioModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header"><h5 class="modal-title">Novedades de Horario</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                <div class="modal-body">
                    <div class="row g-3 mb-3">
                        <div class="col-md-6"><span class="text-muted">Nombre</span><div id="detalleHorarioNombre" class="fw-semibold"></div></div>
                        <div class="col-md-3"><span class="text-muted">Cédula</span><div id="detalleHorarioCedula" class="fw-semibold"></div></div>
                        <div class="col-md-3"><span class="text-muted">Terminal</span><div id="detalleHorarioAgencia" class="fw-semibold"></div></div>
                        <div class="col-md-3"><span class="text-muted">Horas faltantes</span><div id="detalleHorarioTotalFaltantes" class="fw-semibold"></div></div>
                        <div class="col-md-3"><span class="text-muted">Monto total</span><div id="detalleHorarioMontoTotal" class="fw-semibold"></div></div>
                    </div>
                    <div class="table-responsive"><table class="table table-sm table-bordered"><thead><tr><th>Fecha</th><th>Horas trabajadas</th><th>Horas faltantes</th><th>Monto</th></tr></thead><tbody id="detalleHorarioFechasFaltantes"></tbody></table></div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('script')
    <script>
        let tableNovedadesHorario;
        let horasRequeridasReporte = localStorage.getItem('novedades_horas_requeridas') || '8';
        let valorHoraReporte = localStorage.getItem('novedades_valor_hora') || '150';
        const exportUrl = @json(route('recursos-humanos.novedades-horario.export'));
        const exportPagoUrl = @json(route('recursos-humanos.novedades-horario.export-pago'));
        const detalleUrl = @json(route('recursos-humanos.novedades-horario.detalle'));

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

        function parametrosReporte(extra = {}) {
            return new URLSearchParams({
                sistema: document.getElementById('sistema').value,
                fecha_inicio: document.getElementById('fecha_inicio').value,
                fecha_fin: document.getElementById('fecha_fin').value,
                horas_requeridas: horasRequeridasReporte,
                valor_hora: valorHoraReporte,
                detalle: document.getElementById('detalle_filtro').value,
                ...extra
            });
        }

        function renderizarDetalle(row) {
            const faltantes = Math.max(Number(horasRequeridasReporte) - Number(row.horas_acumuladas || 0), 0);
            if (faltantes <= 0) return '<span class="badge bg-success">Cumple</span>';
            return `<span class="badge bg-danger">Tiene falta</span><div class="small">${formatearNumero(faltantes)} h · $${formatearNumero(faltantes * Number(valorHoraReporte))}</div>`;
        }

        async function configurarHorario() {
            const result = await Swal.fire({
                title: 'Configurar horario',
                width: 768,
                padding: '2rem 2.4rem 2.5rem',
                html: `
                    <div class="text-start">
                        <label for="horasConfig" class="form-label fs-5 fw-medium">Horas requeridas</label>
                        <input
                            id="horasConfig"
                            type="number"
                            min="1"
                            step="1"
                            class="form-control form-control-lg mb-4"
                            value="${horasRequeridasReporte}"
                            placeholder="Ej: 8">

                        <label for="valorConfig" class="form-label fs-5 fw-medium">Valor de una hora</label>
                        <input
                            id="valorConfig"
                            type="number"
                            min="0.01"
                            step="0.01"
                            class="form-control form-control-lg"
                            value="${valorHoraReporte}"
                            placeholder="Ej: 150">

                        <p class="text-muted fs-6 mt-2 mb-0">
                            Ejemplo: 8 representa 8 horas. El valor de una hora se usará para calcular el monto de la falta.
                        </p>
                    </div>
                `,
                showCancelButton: true,
                confirmButtonText: 'Guardar',
                cancelButtonText: 'Cancelar',
                preConfirm: () => {
                    const horas = Number(document.getElementById('horasConfig').value);
                    const valor = Number(document.getElementById('valorConfig').value);

                    if (!Number.isInteger(horas) || horas < 1) {
                        Swal.showValidationMessage('Las horas requeridas deben ser un número entero mayor que cero.');
                        return false;
                    }

                    if (!Number.isFinite(valor) || valor <= 0) {
                        Swal.showValidationMessage('El valor de una hora debe ser mayor que cero.');
                        return false;
                    }

                    return { horas, valor };
                }
            });

            if (!result.isConfirmed) return;
            horasRequeridasReporte = String(result.value.horas);
            valorHoraReporte = String(result.value.valor);
            localStorage.setItem('novedades_horas_requeridas', horasRequeridasReporte);
            localStorage.setItem('novedades_valor_hora', valorHoraReporte);
            tableNovedadesHorario?.ajax.reload();
        }

        async function verDetalle(row) {
            const response = await fetch(`${detalleUrl}?${parametrosReporte({ cedula: row.cedula, terminal: row.terminal })}`);
            const data = await response.json();
            if (!response.ok) return Swal.fire('Error', data.message || 'No se pudo consultar el detalle.', 'error');
            document.getElementById('detalleHorarioNombre').textContent = data.nombre;
            document.getElementById('detalleHorarioCedula').textContent = data.cedula;
            document.getElementById('detalleHorarioAgencia').textContent = data.terminal;
            document.getElementById('detalleHorarioTotalFaltantes').textContent = `${formatearNumero(data.total_faltantes)} horas`;
            document.getElementById('detalleHorarioMontoTotal').textContent = `$${formatearNumero(data.monto_total)}`;
            document.getElementById('detalleHorarioFechasFaltantes').innerHTML = data.detalle.map(item => `<tr><td>${item.fecha}</td><td>${formatearNumero(item.horas_acumuladas)}</td><td>${formatearNumero(item.horas_faltantes)}</td><td>$${formatearNumero(item.monto_dia)}</td></tr>`).join('') || '<tr><td colspan="4" class="text-center">Sin faltas</td></tr>';
            bootstrap.Modal.getOrCreateInstance(document.getElementById('detalleFaltantesHorarioModal')).show();
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

            Swal.fire({
                title: 'Consultando novedades',
                text: 'Espere mientras se procesa la información.',
                allowOutsideClick: false,
                allowEscapeKey: false,
                showConfirmButton: false,
                didOpen: () => Swal.showLoading()
            });

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
                        fecha_fin: fechaFin,
                        horas_requeridas: horasRequeridasReporte,
                        detalle: document.getElementById('detalle_filtro').value
                    },
                    dataSrc: function (json) {
                        Swal.close();
                        actualizarResumen(json.resumen || {});
                        return json.data || [];
                    },
                    error: function (xhr) {
                        Swal.close();
                        actualizarResumen({});
                        const mensaje = xhr.responseJSON?.message || 'No se pudieron consultar las novedades de horario.';
                        setTimeout(() => Swal.fire('Error', mensaje, 'error'), 150);
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
                    },
                    { data: null, orderable: false, render: function (data, type, row) { return type === 'display' ? renderizarDetalle(row) : ''; } },
                    { data: null, orderable: false, searchable: false, render: function () { return '<button type="button" class="btn btn-sm btn-info btn-ver-detalle">Ver</button>'; } }
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

        $.fn.dataTable.ext.errMode = 'none';
        document.getElementById('btnBuscar').addEventListener('click', cargarNovedadesHorario);
        document.getElementById('btnConfigurarHorario').addEventListener('click', configurarHorario);
        document.getElementById('btnExportarExcel').addEventListener('click', () => window.location.href = `${exportUrl}?${parametrosReporte()}`);
        document.getElementById('btnExportarPagoExcel').addEventListener('click', () => window.location.href = `${exportPagoUrl}?${parametrosReporte({ detalle: 'tiene_falta' })}`);
        $('#tableNovedadesHorario').on('click', '.btn-ver-detalle', function () { verDetalle(tableNovedadesHorario.row($(this).closest('tr')).data()); });
    </script>
@endsection

