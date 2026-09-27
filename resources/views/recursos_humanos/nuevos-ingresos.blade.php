@extends('app')

@section('content')
    <div class="main-content">
        <div class="page-content">
            <div class="container-fluid">
                <div class="row"><div class="col-12"><div class="page-title-box d-sm-flex align-items-center justify-content-between">
                    <h4 class="mb-sm-0">Nuevos ingresos</h4>
                    <a href="{{ route('recursos-humanos.index') }}" class="btn btn-light"><i class="ri-arrow-left-line me-1"></i> Recursos Humanos</a>
                </div></div></div>

                <div class="row g-3 mb-3">
                    <div class="col-sm-6 col-xl-3"><div class="card card-animate h-100"><div class="card-body d-flex align-items-center justify-content-between">
                        <div><p class="text-uppercase fw-medium text-muted mb-2">Nuevos ingresos</p><h3 id="totalNuevosIngresos" class="mb-0">0</h3></div>
                        <div class="avatar-sm"><span class="avatar-title bg-success-subtle text-success rounded-circle fs-4"><i class="ri-user-add-line"></i></span></div>
                    </div></div></div>
                    <div class="col-sm-6 col-xl-3"><div class="card h-100"><div class="card-body">
                        <p class="text-uppercase fw-medium text-muted mb-2">Criterio</p><h5 class="mb-0">Menos de <span id="criterioDiasHabiles">20</span> días hábiles</h5><small class="text-muted">Lunes a viernes</small>
                    </div></div></div>
                </div>

                <div class="card">
                    <div class="card-header d-flex align-items-center justify-content-between gap-2">
                        <div><h5 class="card-title mb-1">Empleados de nuevo ingreso</h5><p class="text-muted mb-0">Solo empleados activos con menos de 20 días hábiles.</p></div>
                        <div class="d-flex gap-2">
                            <button type="button" id="btnConfigurarNuevosIngresos" class="btn btn-light" data-bs-toggle="modal" data-bs-target="#modalConfiguracionNuevosIngresos"><i class="ri-settings-3-line me-1"></i> Configuración</button>
                            <button type="button" id="btnActualizarNuevosIngresos" class="btn btn-primary"><i class="ri-refresh-line me-1"></i> Actualizar</button>
                        </div>
                    </div>
                    <div class="card-body"><div class="table-responsive">
                        <table id="tablaNuevosIngresos" class="table table-bordered table-striped align-middle nowrap" style="width:100%">
                            <thead><tr><th>Cédula</th><th>Nombre completo</th><th>Fecha de ingreso</th><th>Días hábiles</th></tr></thead><tbody></tbody>
                        </table>
                    </div></div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modalConfiguracionNuevosIngresos" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog"><div class="modal-content">
            <div class="modal-header"><h5 class="modal-title">Configurar nuevos ingresos</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body">
                <label for="limiteDiasHabiles" class="form-label">Límite de días hábiles</label>
                <input type="number" id="limiteDiasHabiles" class="form-control" min="1" max="20" step="1" value="20">
                <div class="form-text">Puede seleccionar entre 1 y 20 días. Se mostrarán empleados con menos días hábiles que el valor indicado.</div>
            </div>
            <div class="modal-footer"><button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancelar</button><button type="button" id="btnGuardarConfiguracionNuevosIngresos" class="btn btn-primary">Guardar configuración</button></div>
        </div></div>
    </div>
@endsection

@section('script')
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const urls = {
                data: @json(route('recursos-humanos.nuevos-ingresos.data')),
                configuration: @json(route('recursos-humanos.nuevos-ingresos.configuration')),
                updateConfiguration: @json(route('recursos-humanos.nuevos-ingresos.configuration.update')),
            };
            const csrfToken = @json(csrf_token());
            let table;
            const escapeHtml = value => String(value ?? '').replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;').replace(/'/g, '&#039;');
            const formatDate = value => value ? new Date(`${value}T00:00:00`).toLocaleDateString('es-DO', {day: '2-digit', month: '2-digit', year: 'numeric'}) : '';

            async function loadNewEmployees(showLoading = true) {
                if (showLoading) Swal.fire({title: 'Consultando nuevos ingresos...', allowOutsideClick: false, didOpen: () => Swal.showLoading()});
                try {
                    const response = await fetch(urls.data, {headers: {'Accept': 'application/json'}});
                    const data = await response.json();
                    if (!response.ok) throw new Error(data.message || 'No se pudo consultar el reporte.');

                    document.getElementById('totalNuevosIngresos').textContent = Number(data.total).toLocaleString('es-DO');
                    document.getElementById('criterioDiasHabiles').textContent = data.limite_dias_habiles;
                    document.getElementById('limiteDiasHabiles').value = data.limite_dias_habiles;
                    if (table) table.destroy();
                    document.querySelector('#tablaNuevosIngresos tbody').innerHTML = data.empleados.map(employee => `<tr><td>${escapeHtml(employee.cedula)}</td><td>${escapeHtml(employee.nombre)}</td><td data-order="${escapeHtml(employee.fecha_ingreso)}">${escapeHtml(formatDate(employee.fecha_ingreso))}</td><td class="text-center"><span class="badge bg-success-subtle text-success fs-6">${employee.dias_habiles}</span></td></tr>`).join('');
                    table = $('#tablaNuevosIngresos').DataTable({responsive: true, scrollX: true, pageLength: 25, order: [[3, 'asc']], dom: 'Bfrtip', buttons: ['copy', 'csv', 'excel', 'pdf', 'print']});
                    if (showLoading) Swal.close();
                } catch (error) { Swal.fire('Error', error.message, 'error'); }
            }

            document.getElementById('btnActualizarNuevosIngresos').addEventListener('click', () => loadNewEmployees());
            document.getElementById('btnGuardarConfiguracionNuevosIngresos').addEventListener('click', async () => {
                try {
                    const days = Number(document.getElementById('limiteDiasHabiles').value);
                    if (!Number.isInteger(days) || days < 1 || days > 20) {
                        Swal.fire('Valor no válido', 'Seleccione un número entero entre 1 y 20 días hábiles.', 'warning');
                        return;
                    }
                    const response = await fetch(urls.updateConfiguration, {method: 'PUT', headers: {'Accept': 'application/json', 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken}, body: JSON.stringify({dias_habiles: days})});
                    const data = await response.json();
                    if (!response.ok) throw new Error(data.message || Object.values(data.errors || {}).flat()[0] || 'No se pudo guardar la configuración.');
                    bootstrap.Modal.getOrCreateInstance(document.getElementById('modalConfiguracionNuevosIngresos')).hide();
                    await loadNewEmployees(false);
                    Swal.fire('Listo', data.message, 'success');
                } catch (error) { Swal.fire('Error', error.message, 'error'); }
            });
            loadNewEmployees(false);
        });
    </script>
@endsection
