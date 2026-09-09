@extends('app')

@section('content')
    <div class="main-content">
        <div class="page-content">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-12">
                        <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                            <h4 class="mb-sm-0">Reporte de Bonos</h4>
                            <div class="page-title-right">
                                <ol class="breadcrumb m-0">
                                    <li class="breadcrumb-item"><a href="{{ route('inicio.index') }}">Inicio</a></li>
                                    <li class="breadcrumb-item"><a href="{{ route('incentivos.index') }}">Incentivos</a></li>
                                    <li class="breadcrumb-item active">Reporte de Bonos</li>
                                </ol>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-primary-subtle border-0">
                        <div class="d-flex justify-content-between align-items-center gap-3 flex-wrap">
                            <div>
                                <h5 class="card-title text-primary mb-1">Bono del 0.5% sobre ventas</h5>
                                <small class="text-muted">Formato basado en la hoja “Real” de IncentivosJulio.</small>
                            </div>
                            <span class="badge bg-primary">Reporte independiente</span>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row g-3 align-items-end">
                            <div class="col-md-4 col-xl-2">
                                <label class="form-label" for="reporte_bonos_sistema">Sistema</label>
                                <select id="reporte_bonos_sistema" class="form-select">
                                    <option value="Todos">Todos</option>
                                    <option value="Lotobet">Lotobet</option>
                                    <option value="Lotonet">Lotonet</option>
                                </select>
                            </div>
                            <div class="col-md-4 col-xl-2">
                                <label class="form-label" for="reporte_bonos_fecha_ini">Fecha inicio</label>
                                <input type="date" id="reporte_bonos_fecha_ini" class="form-control">
                            </div>
                            <div class="col-md-4 col-xl-2">
                                <label class="form-label" for="reporte_bonos_fecha_fin">Fecha fin</label>
                                <input type="date" id="reporte_bonos_fecha_fin" class="form-control">
                            </div>
                            <div class="col-md-6 col-xl-3">
                                <button type="button" class="btn btn-primary w-100" id="btnGenerarReporteBonos">
                                    <i class="ri-file-chart-line me-1"></i>Generar Reporte de Bonos
                                </button>
                            </div>
                            <div class="col-md-6 col-xl-3">
                                <div class="alert alert-info py-2 mb-0 small" id="reporteBonosVentaExternaAviso">
                                    Venta externa pendiente de integración; se mostrará en cero hasta recibir el endpoint.
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row g-3 mb-3">
                    <div class="col-md-6 col-xl-3">
                        <div class="card border-0 shadow-sm h-100"><div class="card-body">
                            <div class="text-muted text-uppercase small">No Tradicionales</div>
                            <div class="fs-4 fw-semibold" id="reporteBonosNoTradicionales">RD$ 0.00</div>
                        </div></div>
                    </div>
                    <div class="col-md-6 col-xl-3">
                        <div class="card border-0 shadow-sm h-100"><div class="card-body">
                            <div class="text-muted text-uppercase small">VentaExterna</div>
                            <div class="fs-4 fw-semibold" id="reporteBonosVentaExterna">RD$ 0.00</div>
                        </div></div>
                    </div>
                    <div class="col-md-6 col-xl-3">
                        <div class="card border-0 shadow-sm h-100"><div class="card-body">
                            <div class="text-muted text-uppercase small">Total ventas</div>
                            <div class="fs-4 fw-semibold" id="reporteBonosTotalVentas">RD$ 0.00</div>
                        </div></div>
                    </div>
                    <div class="col-md-6 col-xl-3">
                        <div class="card border-0 shadow-sm h-100 bg-primary-subtle"><div class="card-body">
                            <div class="text-muted text-uppercase small">Total incentivo</div>
                            <div class="fs-4 fw-semibold text-primary" id="reporteBonosTotalIncentivo">RD$ 0.00</div>
                        </div></div>
                    </div>
                </div>

                <div class="card border-0 shadow-sm">
                    <div class="card-header d-flex align-items-center justify-content-between gap-3 flex-wrap">
                        <div>
                            <h5 class="card-title mb-1">Detalle del Reporte de Bonos</h5>
                            <small class="text-muted" id="reporteBonosRango">Selecciona un período para generar el reporte.</small>
                        </div>
                        <span class="badge bg-light text-dark" id="reporteBonosRegistros">0 registros</span>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped align-middle nowrap w-100" id="tablaReporteBonos">
                                <thead><tr>
                                    <th>Cédula</th><th>Nombre</th><th>Empleada</th><th>Centro de Costo</th>
                                    <th>Division</th><th>Grupo</th><th>Ruta</th>
                                    <th class="text-end">No Tradicionales</th><th class="text-end">VentaExterna</th>
                                    <th class="text-end">Total</th><th class="text-end">Incentivo</th><th class="text-end">Faltante</th>
                                </tr></thead>
                                <tbody></tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('script')
    <script>
        const REPORTE_BONOS_DATOS_URL = @json(route('incentivos.reporte-bonos.datos'));
        let tablaReporteBonos = null;

        function reporteBonosNumero(valor) {
            const numero = Number(String(valor ?? 0).replace(/[^0-9.-]+/g, ''));
            return Number.isFinite(numero) ? numero : 0;
        }

        function reporteBonosMonto(valor) {
            return reporteBonosNumero(valor).toLocaleString('en-US', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2,
            });
        }

        function reporteBonosTextoSeguro(valor) {
            const elemento = document.createElement('div');
            elemento.textContent = valor ?? '';
            return elemento.innerHTML;
        }

        function reporteBonosRenderTexto(valor, tipo) {
            return tipo === 'display' ? reporteBonosTextoSeguro(valor) : (valor ?? '');
        }

        function reporteBonosRenderMonto(valor, tipo) {
            return tipo === 'display' ? reporteBonosMonto(valor) : reporteBonosNumero(valor);
        }

        function renderizarReporteBonos(payload) {
            const filas = Array.isArray(payload?.data) ? payload.data : [];
            const meta = payload?.meta || {};

            if (tablaReporteBonos) {
                tablaReporteBonos.destroy();
            }

            tablaReporteBonos = $('#tablaReporteBonos').DataTable({
                data: filas,
                deferRender: true,
                responsive: true,
                scrollX: true,
                pageLength: 25,
                order: [[10, 'desc']],
                dom: 'Bfrtip',
                buttons: ['copy', 'csv', {
                    extend: 'excel',
                    title: 'Reporte de Bonos',
                    filename: `Reporte_de_Bonos_${meta.fecha_inicio || ''}_${meta.fecha_fin || ''}`,
                }, 'print'],
                columns: [
                    { data: 'cedula', defaultContent: '', render: reporteBonosRenderTexto },
                    { data: 'nombre', defaultContent: '', render: reporteBonosRenderTexto },
                    { data: 'empleada', defaultContent: '', render: reporteBonosRenderTexto },
                    { data: 'centro_costo', defaultContent: '', render: reporteBonosRenderTexto },
                    { data: 'division', defaultContent: '', render: reporteBonosRenderTexto },
                    { data: 'grupo', defaultContent: '', render: reporteBonosRenderTexto },
                    { data: 'ruta', defaultContent: '', render: reporteBonosRenderTexto },
                    { data: 'no_tradicional', className: 'text-end', render: reporteBonosRenderMonto },
                    { data: 'venta_externa', className: 'text-end', render: reporteBonosRenderMonto },
                    { data: 'total', className: 'text-end', render: reporteBonosRenderMonto },
                    { data: 'bono', className: 'text-end fw-semibold', render: reporteBonosRenderMonto },
                    { data: 'faltante', className: 'text-end', render: reporteBonosRenderMonto },
                ],
            });

            document.getElementById('reporteBonosRango').textContent = `${meta.fecha_inicio || ''} al ${meta.fecha_fin || ''} · ${meta.sistema || 'Todos'}`;
            document.getElementById('reporteBonosRegistros').textContent = `${Number(meta.total_registros || 0).toLocaleString('en-US')} registros`;
            document.getElementById('reporteBonosNoTradicionales').textContent = `RD$ ${reporteBonosMonto(meta.total_no_tradicional)}`;
            document.getElementById('reporteBonosVentaExterna').textContent = `RD$ ${reporteBonosMonto(meta.total_venta_externa)}`;
            document.getElementById('reporteBonosTotalVentas').textContent = `RD$ ${reporteBonosMonto(meta.total_ventas)}`;
            document.getElementById('reporteBonosTotalIncentivo').textContent = `RD$ ${reporteBonosMonto(meta.total_bono)}`;
            document.getElementById('reporteBonosVentaExternaAviso').classList.toggle('d-none', Boolean(meta.venta_externa_disponible));
        }

        async function generarReporteBonos() {
            const boton = document.getElementById('btnGenerarReporteBonos');
            const fechaInicio = document.getElementById('reporte_bonos_fecha_ini').value;
            const fechaFin = document.getElementById('reporte_bonos_fecha_fin').value;
            const sistema = document.getElementById('reporte_bonos_sistema').value || 'Todos';

            if (!fechaInicio || !fechaFin) {
                Swal.fire('Fechas requeridas', 'Selecciona el período del reporte.', 'warning');
                return;
            }

            boton.disabled = true;
            boton.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Generando...';

            try {
                const params = new URLSearchParams({ fecha_ini: fechaInicio, fecha_fin: fechaFin, sistema });
                const response = await fetch(`${REPORTE_BONOS_DATOS_URL}?${params.toString()}`, { headers: { Accept: 'application/json' } });
                const payload = await response.json();

                if (!response.ok) {
                    throw new Error(payload?.message || 'No fue posible generar el reporte.');
                }

                renderizarReporteBonos(payload);
            } catch (error) {
                Swal.fire('Error', error.message || 'No fue posible generar el reporte de bonos.', 'error');
            } finally {
                boton.disabled = false;
                boton.innerHTML = '<i class="ri-file-chart-line me-1"></i>Generar Reporte de Bonos';
            }
        }

        document.addEventListener('DOMContentLoaded', function () {
            const hoy = new Date();
            const anio = hoy.getFullYear();
            const mes = String(hoy.getMonth() + 1).padStart(2, '0');
            const dia = String(hoy.getDate()).padStart(2, '0');

            document.getElementById('reporte_bonos_fecha_ini').value = `${anio}-${mes}-01`;
            document.getElementById('reporte_bonos_fecha_fin').value = `${anio}-${mes}-${dia}`;
            document.getElementById('btnGenerarReporteBonos').addEventListener('click', generarReporteBonos);
        });
    </script>
@endsection
