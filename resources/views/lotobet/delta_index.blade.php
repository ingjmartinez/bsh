@extends('app')

@section('content')
    <div class="main-content">
        <div class="page-content">
            <div class="container-fluid">
                <style>
                    .acciones-delta .btn {
                        width: auto;
                    }

                    @media (max-width: 767.98px) {
                        .acciones-delta .btn {
                            width: 100%;
                            min-height: 44px;
                        }
                    }
                </style>

                <div class="row">
                    <div class="col-12">
                        <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                            <h4 class="mb-sm-0">Procesar Todo Delta</h4>
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
                                <div class="row g-2 mb-3 acciones-delta align-items-end">
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
                                        <button id="btnRango" class="btn btn-warning" data-bs-toggle="modal" data-bs-target="#modalRango">Procesar por Rango</button>
                                        <button id="btnRangoEliminar" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#modalRango">Eliminar por Rango</button>
                                        <button id="btnConfigAuto" class="btn btn-info" data-bs-toggle="modal" data-bs-target="#modalConfigAuto">Configurar</button>
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
                                                        <tbody>
                                                            <!-- filas generadas por JS -->
                                                        </tbody>
                                                    </table>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-12 col-lg-7">
                                        <div class="card">
                                            <div class="card-body">
                                                <h6>Registro de ejecución</h6>
                                                <div id="logContainer" style="max-height:360px; overflow:auto; background:#f8f9fa; padding:10px; border-radius:4px;">
                                                    <!-- logs -->
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

    <!-- Modal Rango -->
    <div id="modalRango" class="modal fade" tabindex="-1" aria-labelledby="modalRangoLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalRangoLabel">Procesar / Eliminar Rango de Fechas</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
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

    <div id="modalConfigAuto" class="modal fade" tabindex="-1" aria-labelledby="modalConfigAutoLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalConfigAutoLabel">Configurar auto proceso Delta</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="form-check form-switch mb-3">
                        <input class="form-check-input" type="checkbox" id="cfgEnabledDelta">
                        <label class="form-check-label" for="cfgEnabledDelta">Habilitar ejecucion automatica</label>
                    </div>
                    <div class="mb-3">
                        <label for="cfgHoraDelta" class="form-label">Hora</label>
                        <input type="time" class="form-control" id="cfgHoraDelta">
                    </div>
                    <div class="mb-0">
                        <label for="cfgCorreoDelta" class="form-label">Correo destino</label>
                        <input type="email" class="form-control" id="cfgCorreoDelta" placeholder="correo@dominio.com">
                    </div>
                    <div class="mt-3">
                        <label for="cfgMaxSecondsDelta" class="form-label">Tiempo maximo</label>
                        <input type="number" class="form-control" id="cfgMaxSecondsDelta" min="60" max="7200" step="60">
                        <small class="text-muted">En segundos. Usa 1800 para permitir hasta 30 minutos.</small>
                    </div>
                    <div class="mt-3">
                        <label for="cfgDiaDelta" class="form-label">Fecha a procesar</label>
                        <select class="form-select" id="cfgDiaDelta">
                            <option value="0">Mismo dia</option>
                            <option value="-1">Dia de ayer</option>
                        </select>
                    </div>
                    <div class="mt-3">
                        <label for="cfgFechaEspecificaDelta" class="form-label">Fecha especifica (opcional)</label>
                        <input type="date" class="form-control" id="cfgFechaEspecificaDelta">
                        <small class="text-muted">Si defines una fecha especifica, tiene prioridad sobre "Fecha a procesar".</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cerrar</button>
                    <button type="button" id="btnGuardarConfigDelta" class="btn btn-primary">Guardar</button>
                </div>
            </div>
        </div>
    </div>

@endsection

@section('script')
    <script>
        // Lista de endpoints save en el orden de ejecucion (Delta)
        const modules = [
            { name: 'Ventas Delta', url: '/save-ventas_delta' },
            { name: 'Faltantes Delta', url: '/save-faltantes-delta' }
        ];

        const sistemaAuto = 'delta';
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '{{ csrf_token() }}';

        const logContainer = document.getElementById('logContainer');

        // Render initial status table rows
        const statusTableBody = document.querySelector('#statusTable tbody');
        function initStatusTable() {
            statusTableBody.innerHTML = '';
            modules.forEach((m, idx) => {
                const tr = document.createElement('tr');
                tr.setAttribute('data-module', m.name);
                tr.innerHTML = `
                    <td>${idx + 1}</td>
                    <td>${m.name}</td>
                    <td class="status-cell"><span class="badge bg-secondary">Pending</span></td>
                    <td class="detail-cell">-</td>
                `;
                statusTableBody.appendChild(tr);
            });
        }

        function setStatus(moduleName, status, detail) {
            const row = statusTableBody.querySelector(`tr[data-module="${moduleName}"]`);
            if (!row) return;
            const statusCell = row.querySelector('.status-cell');
            const detailCell = row.querySelector('.detail-cell');
            let badgeClass = 'bg-secondary';
            if (status === 'OK') badgeClass = 'bg-success';
            if (status === 'Error') badgeClass = 'bg-danger';
            if (status === 'Running') badgeClass = 'bg-info';
            statusCell.innerHTML = `<span class="badge ${badgeClass}">${status}</span>`;
            detailCell.textContent = detail || '';
        }

        function addLog(text, level = 'info') {
            const time = new Date().toLocaleString();
            const el = document.createElement('div');
            el.style.padding = '6px 4px';
            el.style.borderBottom = '1px solid #e9ecef';
            el.innerHTML = `<strong>[${time}]</strong> <span style="color:${level === 'error' ? '#c92a2a' : '#212529'}">${text}</span>`;
            logContainer.prepend(el);
        }

        // Initialize status table on load
        initStatusTable();

        async function loadAutoConfig() {
            try {
                const res = await fetch(`/auto-proceso/${sistemaAuto}/config`);
                const cfg = await res.json();
                document.getElementById('cfgEnabledDelta').checked = !!cfg.enabled;
                document.getElementById('cfgHoraDelta').value = cfg.hora ? String(cfg.hora).slice(0, 5) : '';
                document.getElementById('cfgCorreoDelta').value = cfg.correo || '';
                document.getElementById('cfgMaxSecondsDelta').value = cfg.max_seconds || 1800;
                document.getElementById('cfgDiaDelta').value = String(cfg.process_day_offset ?? 0);
                document.getElementById('cfgFechaEspecificaDelta').value = cfg.process_date ? String(cfg.process_date).slice(0, 10) : '';
            } catch (e) {
                addLog('No se pudo cargar la configuracion automatica: ' + e.message, 'error');
            }
        }

        async function saveAutoConfig() {
            const payload = {
                enabled: document.getElementById('cfgEnabledDelta').checked,
                hora: document.getElementById('cfgHoraDelta').value || null,
                correo: document.getElementById('cfgCorreoDelta').value || null,
                max_seconds: Number(document.getElementById('cfgMaxSecondsDelta').value || 1800),
                process_day_offset: Number(document.getElementById('cfgDiaDelta').value || 0),
                process_date: document.getElementById('cfgFechaEspecificaDelta').value || null,
            };

            const res = await fetch(`/auto-proceso/${sistemaAuto}/config`, {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                },
                body: JSON.stringify(payload),
            });

            const raw = await res.text();
            let data = {};
            try {
                data = raw ? JSON.parse(raw) : {};
            } catch (e) {
                data = {};
            }
            if (!res.ok) {
                const serverMsg = data.message || (typeof data.error === 'string' ? data.error : '');
                const fallback = raw ? raw.substring(0, 200) : '';
                throw new Error(serverMsg || `HTTP ${res.status}: ${fallback || 'No se pudo guardar la configuracion'}`);
            }

            Swal.fire({ title: 'Listo', text: 'Configuracion guardada', icon: 'success' });
            const modalElement = document.getElementById('modalConfigAuto');
            const modalInstance = bootstrap.Modal.getInstance(modalElement) || new bootstrap.Modal(modalElement);
            modalInstance.hide();
            addLog('Configuracion automatica guardada');
        }

        document.getElementById('btnConfigAuto').addEventListener('click', loadAutoConfig);
        document.getElementById('btnGuardarConfigDelta').addEventListener('click', async () => {
            try {
                await saveAutoConfig();
            } catch (e) {
                Swal.fire({ title: 'Error', text: e.message || e, icon: 'error' });
            }
        });

        document.getElementById('btnGenerarToken').addEventListener('click', () => {
            fetch('/login-flash')
                .then(r => r.json())
                .then(d => addLog('Generar token Delta: ' + (d.success || JSON.stringify(d))))
                .catch(e => addLog('Error generar token Delta: ' + e.message, 'error'));
        });

        async function processDate(date, options = { stopOnError: false }) {
            addLog(`Iniciando procesamiento para ${date}`);
            const results = [];
            // reset status to pending
            modules.forEach(m => setStatus(m.name, 'Pending', '-'));
            for (let i = 0; i < modules.length; i++) {
                const mod = modules[i];
                addLog(`-> Ejecutando ${mod.name} (${mod.url}) para ${date}`);
                setStatus(mod.name, 'Running', 'Ejecutando...');
                try {
                    const res = await fetch(`${mod.url}?fecha=${date}`);
                    const text = await res.text();
                    let json = null;
                    try { json = JSON.parse(text); } catch(e) { json = null; }
                    if (!res.ok) {
                        addLog(`ERROR ${mod.name}: HTTP ${res.status} - ${text}`, 'error');
                        setStatus(mod.name, 'Error', `HTTP ${res.status}`);
                        results.push({ module: mod.name, ok: false, message: text });
                        if (options.stopOnError) break;
                        continue;
                    }

                    if (json) {
                        const msg = json.message || (json.total ? `Total: ${json.total}` : JSON.stringify(json));
                        addLog(`OK ${mod.name}: ${msg}`);
                        setStatus(mod.name, 'OK', msg);
                        results.push({ module: mod.name, ok: true, data: json });
                    } else {
                        addLog(`OK ${mod.name}: ${text}`);
                        setStatus(mod.name, 'OK', text);
                        results.push({ module: mod.name, ok: true, message: text });
                    }
                } catch (err) {
                    addLog(`EXCEPCIÓN ${mod.name}: ${err.message}`, 'error');
                    setStatus(mod.name, 'Error', err.message);
                    results.push({ module: mod.name, ok: false, message: err.message });
                    if (options.stopOnError) break;
                }
            }
            addLog(`Finalizado procesamiento para ${date}`);

            // resumen
            const okCount = results.filter(r => r.ok).length;
            const errCount = results.filter(r => !r.ok).length;
            addLog(`Resumen: OK=${okCount} Error=${errCount}`);
            Swal.fire({
                title: 'Resumen',
                html: `Fecha: <strong>${date}</strong><br>OK: <strong>${okCount}</strong><br>Errores: <strong>${errCount}</strong>`,
                icon: errCount > 0 ? 'warning' : 'success'
            });

            return results;
        }

        document.getElementById('btnProcesarUno').addEventListener('click', async () => {
            const fecha = document.getElementById('inputFecha').value;
            if (!fecha) {
                Swal.fire({ title: 'Error', text: 'Selecciona una fecha', icon: 'error' });
                return;
            }
            Swal.fire({ title: 'Procesando...', allowOutsideClick: false, didOpen: () => Swal.showLoading() });
            try {
                await processDate(fecha, { stopOnError: false });
                Swal.close();
                Swal.fire({ title: 'Listo', text: 'Proceso finalizado. Revisa el log.', icon: 'success' });
            } catch (e) {
                Swal.fire({ title: 'Error', text: e.message || e, icon: 'error' });
            }
        });

        // Eliminar data por fecha para Delta
        async function deleteDate(date, options = { stopOnError: false }) {
            addLog(`Iniciando eliminación (Delta) para ${date}`);
            const results = [];
            modules.forEach(m => setStatus(m.name, 'Pending', '-'));
            for (let i = 0; i < modules.length; i++) {
                const mod = modules[i];
                const deleteUrl = mod.url.replace('/save-', '/delete-');
                addLog(`-> Eliminando ${mod.name} (${deleteUrl}) para ${date}`);
                setStatus(mod.name, 'Running', 'Eliminando...');
                try {
                    const res = await fetch(`${deleteUrl}?fecha=${date}`);
                    const text = await res.text();
                    let json = null;
                    try { json = JSON.parse(text); } catch(e) { json = null; }
                    if (!res.ok) {
                        addLog(`ERROR ${mod.name}: HTTP ${res.status} - ${text}`, 'error');
                        setStatus(mod.name, 'Error', `HTTP ${res.status}`);
                        results.push({ module: mod.name, ok: false, message: text });
                        if (options.stopOnError) break;
                        continue;
                    }

                    if (json) {
                        const msg = json.message || (json.total ? `Total: ${json.total}` : JSON.stringify(json));
                        addLog(`OK Eliminar ${mod.name}: ${msg}`);
                        setStatus(mod.name, 'OK', msg);
                        results.push({ module: mod.name, ok: true, data: json });
                    } else {
                        addLog(`OK Eliminar ${mod.name}: ${text}`);
                        setStatus(mod.name, 'OK', text);
                        results.push({ module: mod.name, ok: true, message: text });
                    }
                } catch (err) {
                    addLog(`EXCEPCIÓN ${mod.name}: ${err.message}`, 'error');
                    setStatus(mod.name, 'Error', err.message);
                    results.push({ module: mod.name, ok: false, message: err.message });
                    if (options.stopOnError) break;
                }
            }
            addLog(`Finalizado eliminación (Delta) para ${date}`);
            const okCount = results.filter(r => r.ok).length;
            const errCount = results.filter(r => !r.ok).length;
            addLog(`Resumen Eliminación: OK=${okCount} Error=${errCount}`);
            Swal.fire({
                title: 'Resumen Eliminación',
                html: `Fecha: <strong>${date}</strong><br>OK: <strong>${okCount}</strong><br>Errores: <strong>${errCount}</strong>`,
                icon: errCount > 0 ? 'warning' : 'success'
            });
            return results;
        }

        document.getElementById('btnEliminarFecha').addEventListener('click', async () => {
            const fecha = document.getElementById('inputFecha').value;
            if (!fecha) {
                Swal.fire({ title: 'Error', text: 'Selecciona una fecha', icon: 'error' });
                return;
            }
            const confirmed = await Swal.fire({
                title: 'Confirmar',
                text: `¿Eliminar toda la data para ${fecha}? Esta acción no se puede deshacer.`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Sí, eliminar',
                cancelButtonText: 'Cancelar'
            });
            if (!confirmed.isConfirmed) return;

            Swal.fire({ title: 'Eliminando...', allowOutsideClick: false, didOpen: () => Swal.showLoading() });
            try {
                await deleteDate(fecha, { stopOnError: false });
                Swal.close();
                Swal.fire({ title: 'Listo', text: 'Eliminación finalizada. Revisa el log.', icon: 'success' });
            } catch (e) {
                Swal.fire({ title: 'Error', text: e.message || e, icon: 'error' });
            }
        });

        // El botón "Procesar Todo" fue eliminado; usar "Procesar Fecha" que llama a processDate

        document.getElementById('btnProcesarRango').addEventListener('click', async () => {
            const inicio = document.getElementById('fechaInicio').value;
            const fin = document.getElementById('fechaFin').value;
            if (!inicio || !fin) {
                Swal.fire({ title: 'Error', text: 'Selecciona ambas fechas', icon: 'error' });
                return;
            }
            const startDate = new Date(inicio);
            const endDate = new Date(fin);
            if (startDate > endDate) {
                Swal.fire({ title: 'Error', text: 'La fecha inicio debe ser <= fecha fin', icon: 'error' });
                return;
            }

            const dates = [];
            let cur = new Date(startDate);
            while (cur <= endDate) {
                dates.push(cur.toISOString().split('T')[0]);
                cur.setDate(cur.getDate() + 1);
            }

            document.getElementById('btnProcesarRango').disabled = true;
            Swal.fire({ title: 'Procesando rango...', html: `0 / ${dates.length}`, allowOutsideClick: false, didOpen: () => Swal.showLoading() });
            try {
                for (let i = 0; i < dates.length; i++) {
                    const date = dates[i];
                    Swal.update({ html: `Procesando ${date} (${i + 1} / ${dates.length})` });
                    await processDate(date, { stopOnError: false });
                }
                Swal.close();
                addLog('Procesamiento de rango finalizado');
                document.querySelector('#modalRango .btn-close')?.click();
                Swal.fire({ title: 'Listo', text: 'Rango procesado. Revisa el log.', icon: 'success' });
            } catch (e) {
                Swal.fire({ title: 'Error', text: e.message || e, icon: 'error' });
            } finally {
                document.getElementById('btnProcesarRango').disabled = false;
            }
        });

        // Eliminar por rango (Delta): por cada fecha ejecutar deleteDate de forma secuencial
        document.getElementById('btnEliminarRango').addEventListener('click', async () => {
            const inicio = document.getElementById('fechaInicio').value;
            const fin = document.getElementById('fechaFin').value;
            if (!inicio || !fin) {
                Swal.fire({ title: 'Error', text: 'Selecciona ambas fechas', icon: 'error' });
                return;
            }
            const startDate = new Date(inicio);
            const endDate = new Date(fin);
            if (startDate > endDate) {
                Swal.fire({ title: 'Error', text: 'La fecha inicio debe ser <= fecha fin', icon: 'error' });
                return;
            }

            const confirmed = await Swal.fire({
                title: 'Confirmar eliminación por rango',
                html: `¿Eliminar la data desde <strong>${inicio}</strong> hasta <strong>${fin}</strong>? Esta acción no se puede deshacer.`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Sí, eliminar',
                cancelButtonText: 'Cancelar'
            });
            if (!confirmed.isConfirmed) return;

            const dates = [];
            let cur = new Date(startDate);
            while (cur <= endDate) {
                dates.push(cur.toISOString().split('T')[0]);
                cur.setDate(cur.getDate() + 1);
            }

            document.getElementById('btnEliminarRango').disabled = true;
            Swal.fire({ title: 'Eliminando rango...', html: `0 / ${dates.length}`, allowOutsideClick: false, didOpen: () => Swal.showLoading() });
            try {
                for (let i = 0; i < dates.length; i++) {
                    const date = dates[i];
                    Swal.update({ html: `Eliminando ${date} (${i + 1} / ${dates.length})` });
                    await deleteDate(date, { stopOnError: false });
                }
                Swal.close();
                addLog('Eliminación de rango finalizada');
                document.querySelector('#modalRango .btn-close')?.click();
                Swal.fire({ title: 'Listo', text: 'Rango eliminado. Revisa el log.', icon: 'success' });
            } catch (e) {
                Swal.fire({ title: 'Error', text: e.message || e, icon: 'error' });
            } finally {
                document.getElementById('btnEliminarRango').disabled = false;
            }
        });
    </script>
@endsection


