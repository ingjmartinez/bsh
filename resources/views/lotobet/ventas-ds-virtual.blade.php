@extends('app')

@section('content')
    <div class="main-content">
        <div class="page-content">
            <div class="container-fluid">
                <style>
                    .acciones-ds-virtual .btn {
                        width: auto;
                    }

                    @media (max-width: 767.98px) {
                        .acciones-ds-virtual .btn {
                            width: 100%;
                            min-height: 44px;
                        }
                    }
                </style>

                <div class="row">
                    <div class="col-12">
                        <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                            <h4 class="mb-sm-0">Procesar Ventas DS Virtual</h4>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-lg-12">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="card-title mb-0">Ejecutar todas las tareas por fecha</h5>
                            </div>
                            <div class="card-body">
                                <div class="row g-2 mb-3 acciones-ds-virtual align-items-end">
                                    <div class="col-12 col-lg-2 d-grid">
                                        <button id="btnGenerarToken" class="btn btn-secondary">Generar Token</button>
                                    </div>

                                    <div class="col-12 col-md-4 col-lg-2">
                                        <label for="inputFecha" class="form-label mb-1">Fecha</label>
                                        <input type="date" id="inputFecha" class="form-control">
                                    </div>

                                    <div class="col-12 col-lg-4 d-grid d-md-flex gap-2">
                                        <button id="btnProcesarUno" class="btn btn-primary">Procesar Fecha</button>
                                        <button id="btnEliminarFecha" class="btn btn-danger">Eliminar Fecha</button>
                                    </div>

                                    <div class="col-12 col-md-8 col-lg-4 d-grid d-md-flex gap-2">
                                        <button id="btnRango" class="btn btn-warning" data-bs-toggle="modal"
                                            data-bs-target="#modalRango">Procesar por Rango</button>
                                        <button id="btnRangoEliminar" class="btn btn-danger" data-bs-toggle="modal"
                                            data-bs-target="#modalRango">Eliminar por Rango</button>
                                        <button id="btnConfigAuto" class="btn btn-info" data-bs-toggle="modal"
                                            data-bs-target="#modalConfigAuto">Configurar</button>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-12 col-lg-5 mb-3">
                                        <div class="card">
                                            <div class="card-body">
                                                <h6>Estados por módulo</h6>
                                                <div style="max-height:360px; overflow:auto;">
                                                    <table class="table table-sm table-bordered" id="statusTable">
                                                        <thead>
                                                            <tr>
                                                                <th style="width:1%">#</th>
                                                                <th>Módulo</th>
                                                                <th style="width:1%">Estado</th>
                                                                <th>Detalle</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody></tbody>
                                                    </table>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-12 col-lg-7">
                                        <div class="card">
                                            <div class="card-body">
                                                <h6>Registro de ejecución</h6>
                                                <div id="logContainer"
                                                    style="max-height:360px; overflow:auto; background:#f8f9fa; padding:10px; border-radius:4px;">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div id="modalRango" class="modal fade" tabindex="-1" aria-labelledby="modalRangoLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalRangoLabel">Procesar / Eliminar Rango de Fechas</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="fechaInicio" class="form-label">Fecha Inicio</label>
                        <input type="date" class="form-control" id="fechaInicio">
                    </div>
                    <div class="mb-3">
                        <label for="fechaFin" class="form-label">Fecha Fin</label>
                        <input type="date" class="form-control" id="fechaFin">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cerrar</button>
                    <button type="button" id="btnProcesarRango" class="btn btn-primary">Procesar Rango</button>
                    <button type="button" id="btnEliminarRango" class="btn btn-danger">Eliminar Rango</button>
                </div>
            </div>
        </div>
    </div>

    <div id="modalConfigAuto" class="modal fade" tabindex="-1" aria-labelledby="modalConfigAutoLabel"
        aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalConfigAutoLabel">Configurar auto proceso DS Virtual</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body">
                    <div class="form-check form-switch mb-3">
                        <input class="form-check-input" type="checkbox" id="cfgEnabledDsVirtual">
                        <label class="form-check-label" for="cfgEnabledDsVirtual">Habilitar ejecución automática</label>
                    </div>
                    <div class="mb-3">
                        <label for="cfgHoraDsVirtual" class="form-label">Hora</label>
                        <input type="time" class="form-control" id="cfgHoraDsVirtual">
                    </div>
                    <div class="mb-0">
                        <label for="cfgCorreoDsVirtual" class="form-label">Correo destino</label>
                        <input type="email" class="form-control" id="cfgCorreoDsVirtual"
                            placeholder="correo@dominio.com">
                    </div>
                    <div class="mt-3">
                        <label for="cfgMaxSecondsDsVirtual" class="form-label">Tiempo máximo</label>
                        <input type="number" class="form-control" id="cfgMaxSecondsDsVirtual" min="60" max="7200"
                            step="60">
                        <small class="text-muted">En segundos. Usa 1800 para permitir hasta 30 minutos.</small>
                    </div>
                    <div class="mt-3">
                        <label for="cfgDiaDsVirtual" class="form-label">Fecha a procesar</label>
                        <select class="form-select" id="cfgDiaDsVirtual">
                            <option value="0">Mismo día</option>
                            <option value="-1">Día de ayer</option>
                        </select>
                    </div>
                    <div class="mt-3">
                        <label for="cfgFechaEspecificaDsVirtual" class="form-label">Fecha específica (opcional)</label>
                        <input type="date" class="form-control" id="cfgFechaEspecificaDsVirtual">
                        <small class="text-muted">Si defines una fecha específica, tendrá prioridad.</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cerrar</button>
                    <button type="button" id="btnGuardarConfigDsVirtual" class="btn btn-primary">Guardar</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('script')
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const moduleDefinition = {
                name: 'Ventas DS Virtual',
                syncUrl: @json(route('ventas-ds-virtual.sync')),
                deleteUrl: @json(route('ventas-ds-virtual.destroy')),
            };
            const tokenUrl = @json(url('/generar-token'));
            const autoConfigUrl = @json(url('/auto-proceso/ds_virtual/config'));
            const csrfToken = @json(csrf_token());
            const statusTableBody = document.querySelector('#statusTable tbody');
            const logContainer = document.getElementById('logContainer');

            function setStatus(status, detail = '-') {
                const badgeClasses = {
                    Pendiente: 'bg-secondary',
                    Ejecutando: 'bg-info',
                    OK: 'bg-success',
                    Error: 'bg-danger',
                };
                const statusCell = statusTableBody.querySelector('.status-cell');
                const detailCell = statusTableBody.querySelector('.detail-cell');
                statusCell.replaceChildren();
                const badge = document.createElement('span');
                badge.className = `badge ${badgeClasses[status] || 'bg-secondary'}`;
                badge.textContent = status;
                statusCell.appendChild(badge);
                detailCell.textContent = detail;
            }

            function initializeStatus() {
                const row = document.createElement('tr');
                const numberCell = document.createElement('td');
                const nameCell = document.createElement('td');
                const statusCell = document.createElement('td');
                const detailCell = document.createElement('td');
                numberCell.textContent = '1';
                nameCell.textContent = moduleDefinition.name;
                statusCell.className = 'status-cell';
                detailCell.className = 'detail-cell';
                row.append(numberCell, nameCell, statusCell, detailCell);
                statusTableBody.replaceChildren(row);
                setStatus('Pendiente');
            }

            function addLog(message, level = 'info') {
                const row = document.createElement('div');
                row.style.padding = '6px 4px';
                row.style.borderBottom = '1px solid #e9ecef';
                row.style.color = level === 'error' ? '#c92a2a' : '#212529';
                row.textContent = `[${new Date().toLocaleString()}] ${message}`;
                logContainer.prepend(row);
            }

            async function jsonRequest(url, options = {}) {
                const response = await fetch(url, {
                    ...options,
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        ...(options.headers || {}),
                    },
                });
                const raw = await response.text();
                let data = {};

                try {
                    data = raw ? JSON.parse(raw) : {};
                } catch (_error) {
                    data = {};
                }

                if (!response.ok) {
                    const validationMessage = Object.values(data.errors || {}).flat()[0];
                    throw new Error(validationMessage || data.message || raw.slice(0, 300) || `HTTP ${response.status}`);
                }

                return data;
            }

            function requireDate(inputId) {
                const date = document.getElementById(inputId).value;
                if (!date) {
                    throw new Error('Selecciona una fecha.');
                }
                return date;
            }

            function rangeDates() {
                const start = requireDate('fechaInicio');
                const end = requireDate('fechaFin');
                const current = new Date(`${start}T00:00:00`);
                const last = new Date(`${end}T00:00:00`);

                if (current > last) {
                    throw new Error('La fecha de inicio debe ser menor o igual que la fecha final.');
                }

                const dates = [];
                while (current <= last) {
                    dates.push([
                        current.getFullYear(),
                        String(current.getMonth() + 1).padStart(2, '0'),
                        String(current.getDate()).padStart(2, '0'),
                    ].join('-'));
                    current.setDate(current.getDate() + 1);
                }

                return dates;
            }

            async function processDate(date) {
                addLog(`Iniciando procesamiento para ${date}`);
                setStatus('Ejecutando', 'Sincronizando...');

                try {
                    const data = await jsonRequest(moduleDefinition.syncUrl, {
                        method: 'POST',
                        headers: {'Content-Type': 'application/json'},
                        body: JSON.stringify({fecha: date}),
                    });
                    const detail = `${data.message} Recibidos: ${data.received}; almacenados: ${data.stored}.`;
                    setStatus('OK', detail);
                    addLog(`OK ${moduleDefinition.name}: ${detail}`);
                    return data;
                } catch (error) {
                    setStatus('Error', error.message);
                    addLog(`ERROR ${moduleDefinition.name}: ${error.message}`, 'error');
                    throw error;
                }
            }

            async function deleteDate(date) {
                addLog(`Iniciando eliminación para ${date}`);
                setStatus('Ejecutando', 'Eliminando...');

                try {
                    const data = await jsonRequest(moduleDefinition.deleteUrl, {
                        method: 'DELETE',
                        headers: {'Content-Type': 'application/json'},
                        body: JSON.stringify({fecha: date}),
                    });
                    const detail = `${data.message} Eliminados: ${data.deleted}.`;
                    setStatus('OK', detail);
                    addLog(`OK ${moduleDefinition.name}: ${detail}`);
                    return data;
                } catch (error) {
                    setStatus('Error', error.message);
                    addLog(`ERROR ${moduleDefinition.name}: ${error.message}`, 'error');
                    throw error;
                }
            }

            async function runRange(dates, operation) {
                for (let index = 0; index < dates.length; index++) {
                    Swal.update({html: `${operation === processDate ? 'Procesando' : 'Eliminando'} ${dates[index]} (${index + 1} / ${dates.length})`});
                    await operation(dates[index]);
                }
            }

            async function loadAutoConfig() {
                const config = await jsonRequest(autoConfigUrl);
                document.getElementById('cfgEnabledDsVirtual').checked = Boolean(config.enabled);
                document.getElementById('cfgHoraDsVirtual').value = config.hora ? String(config.hora).slice(0, 5) : '';
                document.getElementById('cfgCorreoDsVirtual').value = config.correo || '';
                document.getElementById('cfgMaxSecondsDsVirtual').value = config.max_seconds || 1800;
                document.getElementById('cfgDiaDsVirtual').value = String(config.process_day_offset ?? 0);
                document.getElementById('cfgFechaEspecificaDsVirtual').value = config.process_date ? String(config.process_date).slice(0, 10) : '';
            }

            async function saveAutoConfig() {
                const data = await jsonRequest(autoConfigUrl, {
                    method: 'POST',
                    headers: {'Content-Type': 'application/json'},
                    body: JSON.stringify({
                        enabled: document.getElementById('cfgEnabledDsVirtual').checked,
                        hora: document.getElementById('cfgHoraDsVirtual').value || null,
                        correo: document.getElementById('cfgCorreoDsVirtual').value || null,
                        max_seconds: Number(document.getElementById('cfgMaxSecondsDsVirtual').value || 1800),
                        process_day_offset: Number(document.getElementById('cfgDiaDsVirtual').value || 0),
                        process_date: document.getElementById('cfgFechaEspecificaDsVirtual').value || null,
                    }),
                });
                bootstrap.Modal.getOrCreateInstance(document.getElementById('modalConfigAuto')).hide();
                addLog(data.message);
                Swal.fire('Listo', data.message, 'success');
            }

            initializeStatus();

            document.getElementById('btnGenerarToken').addEventListener('click', async () => {
                try {
                    const data = await jsonRequest(tokenUrl);
                    addLog(data.success || 'Token generado correctamente.');
                    Swal.fire('Listo', data.success || 'Token generado correctamente.', 'success');
                } catch (error) {
                    addLog(`Error generando token: ${error.message}`, 'error');
                    Swal.fire('Error', error.message, 'error');
                }
            });

            document.getElementById('btnProcesarUno').addEventListener('click', async () => {
                try {
                    const date = requireDate('inputFecha');
                    Swal.fire({title: 'Procesando...', allowOutsideClick: false, didOpen: () => Swal.showLoading()});
                    await processDate(date);
                    Swal.fire('Listo', 'Proceso finalizado. Revisa el registro de ejecución.', 'success');
                } catch (error) {
                    Swal.fire('Error', error.message, 'error');
                }
            });

            document.getElementById('btnEliminarFecha').addEventListener('click', async () => {
                try {
                    const date = requireDate('inputFecha');
                    const confirmation = await Swal.fire({
                        title: 'Confirmar',
                        text: `¿Eliminar toda la data para ${date}?`,
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonText: 'Sí, eliminar',
                        cancelButtonText: 'Cancelar',
                    });
                    if (!confirmation.isConfirmed) {
                        return;
                    }
                    Swal.fire({title: 'Eliminando...', allowOutsideClick: false, didOpen: () => Swal.showLoading()});
                    await deleteDate(date);
                    Swal.fire('Listo', 'Eliminación finalizada. Revisa el registro de ejecución.', 'success');
                } catch (error) {
                    Swal.fire('Error', error.message, 'error');
                }
            });

            document.getElementById('btnProcesarRango').addEventListener('click', async event => {
                const button = event.currentTarget;
                try {
                    const dates = rangeDates();
                    button.disabled = true;
                    Swal.fire({title: 'Procesando rango...', html: `0 / ${dates.length}`, allowOutsideClick: false, didOpen: () => Swal.showLoading()});
                    await runRange(dates, processDate);
                    bootstrap.Modal.getOrCreateInstance(document.getElementById('modalRango')).hide();
                    Swal.fire('Listo', 'Rango procesado. Revisa el registro de ejecución.', 'success');
                } catch (error) {
                    Swal.fire('Error', error.message, 'error');
                } finally {
                    button.disabled = false;
                }
            });

            document.getElementById('btnEliminarRango').addEventListener('click', async event => {
                const button = event.currentTarget;
                try {
                    const dates = rangeDates();
                    const confirmation = await Swal.fire({
                        title: 'Confirmar eliminación por rango',
                        text: `Se eliminarán ${dates.length} fechas. Esta acción no se puede deshacer.`,
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonText: 'Sí, eliminar',
                        cancelButtonText: 'Cancelar',
                    });
                    if (!confirmation.isConfirmed) {
                        return;
                    }
                    button.disabled = true;
                    Swal.fire({title: 'Eliminando rango...', html: `0 / ${dates.length}`, allowOutsideClick: false, didOpen: () => Swal.showLoading()});
                    await runRange(dates, deleteDate);
                    bootstrap.Modal.getOrCreateInstance(document.getElementById('modalRango')).hide();
                    Swal.fire('Listo', 'Rango eliminado. Revisa el registro de ejecución.', 'success');
                } catch (error) {
                    Swal.fire('Error', error.message, 'error');
                } finally {
                    button.disabled = false;
                }
            });

            document.getElementById('btnConfigAuto').addEventListener('click', async () => {
                try {
                    await loadAutoConfig();
                } catch (error) {
                    addLog(`No se pudo cargar la configuración automática: ${error.message}`, 'error');
                    Swal.fire('Error', error.message, 'error');
                }
            });

            document.getElementById('btnGuardarConfigDsVirtual').addEventListener('click', async () => {
                try {
                    await saveAutoConfig();
                } catch (error) {
                    Swal.fire('Error', error.message, 'error');
                }
            });
        });
    </script>
@endsection
