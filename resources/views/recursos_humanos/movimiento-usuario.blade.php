@extends('app')

@section('content')
    <div class="main-content">
        <div class="page-content">
            <div class="container-fluid">
                <div class="row"><div class="col-12"><div class="page-title-box d-sm-flex align-items-center justify-content-between">
                    <h4 class="mb-sm-0">Movimiento de usuario</h4>
                    <a href="{{ route('recursos-humanos.index') }}" class="btn btn-light"><i class="ri-arrow-left-line me-1"></i> Recursos Humanos</a>
                </div></div></div>

                <div class="card">
                    <div class="card-header"><h5 class="card-title mb-0">Consultar empleado</h5></div>
                    <div class="card-body">
                        <div class="row g-3 align-items-end">
                            <div class="col-md-5 col-lg-4">
                                <label for="cedulaEmpleado" class="form-label">Cédula</label>
                                <input type="text" id="cedulaEmpleado" class="form-control" maxlength="30" placeholder="Digite la cédula del empleado">
                            </div>
                            <div class="col-md-auto"><button type="button" id="btnBuscarEmpleado" class="btn btn-primary"><i class="ri-search-line me-1"></i> Buscar empleado</button></div>
                        </div>
                        <div id="datosEmpleado" class="alert alert-info border-0 mt-3 mb-0 d-none">
                            <div class="row g-2"><div class="col-md-5"><strong>Empleado:</strong> <span id="nombreEmpleado"></span></div><div class="col-md-3"><strong>Cédula:</strong> <span id="cedulaEncontrada"></span></div><div class="col-md-2"><strong>Centro de costo:</strong> <span id="centroCostoEmpleado"></span></div><div class="col-md-2"><strong>Estatus:</strong> <span id="estatusEmpleado"></span></div></div>
                        </div>
                    </div>
                </div>

                <form id="formMovimiento" class="d-none">
                    <input type="hidden" id="empleadoId">
                    <div class="row g-3">
                        <div class="col-lg-6"><div class="card h-100 border-start border-3 border-warning">
                            <div class="card-header"><h5 class="card-title mb-0"><i class="ri-map-pin-line me-1"></i> ¿Dónde está actualmente?</h5></div>
                            <div class="card-body"><div class="row g-3">
                                <div class="col-md-6"><label for="terminalOrigen" class="form-label">Código de terminal</label><input type="text" id="terminalOrigen" class="form-control" maxlength="50"></div>
                                <div class="col-md-6"><label for="centroCostoOrigen" class="form-label">Código de centro de costo</label><input type="text" id="centroCostoOrigen" class="form-control" maxlength="50"></div>
                                <div class="col-12"><label for="grupoOrigen" class="form-label">Nombre del grupo</label><input type="text" id="grupoOrigen" class="form-control" maxlength="100"></div>
                            </div><small class="text-muted d-block mt-3">Complete por lo menos uno de los tres campos.</small></div>
                        </div></div>
                        <div class="col-lg-6"><div class="card h-100 border-start border-3 border-success">
                            <div class="card-header"><h5 class="card-title mb-0"><i class="ri-map-pin-add-line me-1"></i> ¿Hacia dónde se moverá?</h5></div>
                            <div class="card-body"><div class="row g-3">
                                <div class="col-md-6"><label for="terminalDestino" class="form-label">Código de terminal</label><input type="text" id="terminalDestino" class="form-control" maxlength="50"></div>
                                <div class="col-md-6"><label for="centroCostoDestino" class="form-label">Código de centro de costo</label><input type="text" id="centroCostoDestino" class="form-control" maxlength="50"></div>
                                <div class="col-12"><label for="grupoDestino" class="form-label">Nombre del grupo</label><input type="text" id="grupoDestino" class="form-control" maxlength="100"></div>
                            </div><small class="text-muted d-block mt-3">Complete por lo menos uno de los tres campos.</small></div>
                        </div></div>
                    </div>
                    <div class="card mt-3"><div class="card-body">
                        <label for="observacionMovimiento" class="form-label">Observación</label><textarea id="observacionMovimiento" class="form-control" rows="3" maxlength="1000"></textarea>
                        <div class="d-flex justify-content-end gap-2 mt-3"><button type="button" id="btnLimpiarMovimiento" class="btn btn-light">Limpiar</button><button type="submit" class="btn btn-primary"><i class="ri-save-line me-1"></i> Registrar movimiento</button></div>
                    </div></div>
                </form>

                <div class="card mt-3"><div class="card-header"><h5 class="card-title mb-0">Historial de movimientos</h5></div><div class="card-body"><div class="table-responsive">
                    <table id="tablaMovimientos" class="table table-bordered table-striped align-middle nowrap" style="width:100%"><thead><tr><th>Fecha</th><th>Cédula</th><th>Empleado</th><th>Terminal origen</th><th>Centro costo origen</th><th>Grupo origen</th><th>Terminal destino</th><th>Centro costo destino</th><th>Grupo destino</th><th>Observación</th><th>Registrado por</th></tr></thead><tbody></tbody></table>
                </div></div></div>
            </div>
        </div>
    </div>
@endsection

@section('script')
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const urls = {employee: @json(route('recursos-humanos.movimiento-usuario.employee')), store: @json(route('recursos-humanos.movimiento-usuario.store')), list: @json(route('recursos-humanos.movimiento-usuario.list'))};
            const csrfToken = @json(csrf_token());
            let table;
            const escapeHtml = value => String(value ?? '').replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;').replace(/'/g, '&#039;');
            const formatDateTime = value => {
                if (!value) return '';
                const date = new Date(value);
                if (Number.isNaN(date.getTime())) return value;

                return date.toLocaleString('es-DO', {
                    day: '2-digit',
                    month: '2-digit',
                    year: 'numeric',
                    hour: '2-digit',
                    minute: '2-digit',
                    hour12: true,
                });
            };

            async function request(url, options = {}) {
                const response = await fetch(url, {...options, headers: {'Accept': 'application/json', 'X-CSRF-TOKEN': csrfToken, ...(options.headers || {})}});
                const data = await response.json();
                if (!response.ok) throw new Error(data.message || Object.values(data.errors || {}).flat()[0] || 'No se pudo completar la solicitud.');
                return data;
            }

            async function loadHistory() {
                const data = await request(urls.list);
                if (table) table.destroy();
                document.querySelector('#tablaMovimientos tbody').innerHTML = data.movimientos.map(item => `<tr><td data-order="${escapeHtml(item.created_at)}">${escapeHtml(formatDateTime(item.created_at))}</td><td>${escapeHtml(item.cedula)}</td><td>${escapeHtml(item.nombre_empleado)}</td><td>${escapeHtml(item.terminal_origen)}</td><td>${escapeHtml(item.centro_costo_origen)}</td><td>${escapeHtml(item.grupo_origen)}</td><td>${escapeHtml(item.terminal_destino)}</td><td>${escapeHtml(item.centro_costo_destino)}</td><td>${escapeHtml(item.grupo_destino)}</td><td>${escapeHtml(item.observacion)}</td><td>${escapeHtml(item.usuario_registro)}${item.usuario_email ? `<br><small class="text-muted">${escapeHtml(item.usuario_email)}</small>` : ''}</td></tr>`).join('');
                table = $('#tablaMovimientos').DataTable({responsive: true, scrollX: true, pageLength: 25, order: [[0, 'desc']], dom: 'Bfrtip', buttons: ['copy', 'csv', 'excel', 'pdf', 'print']});
            }

            function clearMovement() {
                ['terminalOrigen', 'centroCostoOrigen', 'grupoOrigen', 'terminalDestino', 'centroCostoDestino', 'grupoDestino', 'observacionMovimiento'].forEach(id => document.getElementById(id).value = '');
            }

            function clearEmployeeQuery() {
                clearMovement();
                document.getElementById('cedulaEmpleado').value = '';
                document.getElementById('empleadoId').value = '';
                document.getElementById('nombreEmpleado').textContent = '';
                document.getElementById('cedulaEncontrada').textContent = '';
                document.getElementById('centroCostoEmpleado').textContent = '';
                document.getElementById('estatusEmpleado').textContent = '';
                document.getElementById('datosEmpleado').classList.add('d-none');
                document.getElementById('formMovimiento').classList.add('d-none');
            }

            document.getElementById('btnBuscarEmpleado').addEventListener('click', async () => {
                const identity = document.getElementById('cedulaEmpleado').value.trim();
                if (!identity) return Swal.fire('Cédula requerida', 'Digite la cédula del empleado.', 'warning');
                try {
                    const url = new URL(urls.employee, window.location.origin); url.searchParams.set('cedula', identity);
                    const data = await request(url); const employee = data.empleado;
                    document.getElementById('empleadoId').value = employee.id;
                    document.getElementById('nombreEmpleado').textContent = employee.nombre;
                    document.getElementById('cedulaEncontrada').textContent = employee.cedula;
                    document.getElementById('centroCostoEmpleado').textContent = employee.centro_costo || 'Sin asignar';
                    document.getElementById('estatusEmpleado').textContent = employee.estatus;
                    document.getElementById('datosEmpleado').classList.remove('d-none');
                    document.getElementById('formMovimiento').classList.remove('d-none'); clearMovement();
                } catch (error) { document.getElementById('datosEmpleado').classList.add('d-none'); document.getElementById('formMovimiento').classList.add('d-none'); Swal.fire('Empleado no encontrado', error.message, 'error'); }
            });

            document.getElementById('btnLimpiarMovimiento').addEventListener('click', clearMovement);
            document.getElementById('formMovimiento').addEventListener('submit', async event => {
                event.preventDefault();
                const payload = {empleado_id: document.getElementById('empleadoId').value, terminal_origen: document.getElementById('terminalOrigen').value, centro_costo_origen: document.getElementById('centroCostoOrigen').value, grupo_origen: document.getElementById('grupoOrigen').value, terminal_destino: document.getElementById('terminalDestino').value, centro_costo_destino: document.getElementById('centroCostoDestino').value, grupo_destino: document.getElementById('grupoDestino').value, observacion: document.getElementById('observacionMovimiento').value};
                try {
                    const data = await request(urls.store, {method: 'POST', headers: {'Content-Type': 'application/json'}, body: JSON.stringify(payload)});
                    clearEmployeeQuery(); await loadHistory(); Swal.fire('Listo', data.message, 'success');
                } catch (error) { Swal.fire('Error', error.message, 'error'); }
            });

            loadHistory().catch(error => Swal.fire('Error', error.message, 'error'));
        });
    </script>
@endsection
