@extends('app')

@section('content')
    <div class="main-content">
        <div class="page-content">
            <div class="container-fluid">
                <style>
                    .acciones-lotonet .btn {
                        width: auto;
                    }

                    @media (max-width: 767.98px) {
                        .acciones-lotonet .btn {
                            width: 100%;
                            min-height: 44px;
                        }
                    }
                </style>

                <!-- start page title -->
                <div class="row">
                    <div class="col-12">
                        <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                            <h4 class="mb-sm-0">Pagos A Otra Empresa - Lotonet</h4>

                            <div class="page-title-right">
                                <ol class="breadcrumb m-0">
                                    <li class="breadcrumb-item"><a href="javascript: void(0);">Tables</a></li>
                                    <li class="breadcrumb-item active">Datatables</li>
                                </ol>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- end page title -->

                <div class="row">
                    <div class="col-lg-12">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="card-title mb-0">Configurar Token</h5>
                            </div>
                            <div class="card-body">
                                <div class="row g-2 mb-3 acciones-lotonet align-items-end">
                                    <div class="col-12 col-lg-4 d-grid d-md-flex gap-2">
                                        <button id="btnGenerarToken" class="btn btn-primary">Generar Token</button>
                                        <button id="btnGenerarData" class="btn btn-primary">Generar Data</button>
                                    </div>

                                    <div class="col-12 col-md-4 col-lg-2">
                                        <input type="date" id="inputFecha" class="form-control">
                                    </div>
                                    <div class="col-12 col-lg-4 d-grid d-md-flex gap-2">
                                        <button id="btnGuardarData" class="btn btn-primary">Guardar Data</button>
                                        <button id="btnEliminarData" class="btn btn-danger">Eliminar Data</button>
                                    </div>
                                    <div class="col-12 col-md-8 col-lg-2 d-grid">
                                        <button id="btnConsultar" type="button" class="btn btn-primary"
                                            data-bs-toggle="modal" data-bs-target="#myModal">Generar Data Por Fecha</button>
                                    </div>
                                </div>

                                <table id="tableRecargas"
                                    class="table table-bordered dt-responsive nowrap table-striped align-middle"
                                    style="width:100%">
                                    <thead>
                                        <tr>
                                            <th>Consorcio</th>
                                            <th>Producto</th>
                                            <th>Agencia</th>
                                            <th>Descripción</th>
                                            <th>Monto</th>
                                            <th>Fecha</th>
                                            <th>Importe</th>
                                            <th>Pagado Consorcio</th>
                                            <th>Plataforma Pago</th>
                                        </tr>
                                    </thead>
                                    <tbody></tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div><!--end row-->
            </div>
            <!-- container-fluid -->
        </div>
    </div>

    <div id="myModal" class="modal fade" tabindex="-1" aria-labelledby="myModalLabel" aria-hidden="true"
        style="display: none;">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="myModalLabel">Guardar Datos Por Rango De Fechas</h5>
                    <button type="button" id="btnClose" class="btn-close" data-bs-dismiss="modal"
                        aria-label="Close"></button>
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
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-primary" id="btnGuardarDataFecha">Registrar Data</button>
                    <button type="button" class="btn btn-danger" id="btnEliminarDataFecha">Eliminar Data</button>
                </div>

            </div><!-- /.modal-content -->
        </div><!-- /.modal-dialog -->
    </div><!-- /.modal -->
@endsection

@section('script')
    <script>
        const btnGenerarToken = document.getElementById('btnGenerarToken');
        btnGenerarToken.addEventListener('click', () => {
            fetch("/iniciar-session")
                .then(response => response.json())
                .then(data => {
                    Swal.fire({
                        title: "Listo",
                        text: data.success,
                        icon: "success"
                    });
                })
                .catch(error => console.error('Error fetching data:', error));
        });

        const btnGenerarData = document.getElementById('btnGenerarData');
        btnGenerarData.addEventListener('click', () => {
            const fecha = document.getElementById('inputFecha').value;
            if (!fecha) {
                Swal.fire({
                    title: "Error",
                    text: "Por favor, selecciona una fecha",
                    icon: "error"
                });
                return;
            }

            Swal.fire({
                title: "Cargando ...",
                icon: 'info',
                allowOutsideClick: false,
                showConfirmButton: false,
                timerProgressBar: true,
                didOpen: () => Swal.showLoading()
            });

            $('#tableRecargas').DataTable().destroy();
            const tableBody = document.querySelector('#tableRecargas tbody');
            tableBody.innerHTML = '';

            fetch(`/get-pagos-aotra-empresa-lotonet?fecha=${fecha}`)
                .then(response => response.json())
                .then(data => {
                    if (data.code != 0) {
                        Swal.fire({
                            title: "Error",
                            text: data.message,
                            icon: "error"
                        });
                    } else {
                        Swal.fire({
                            title: "Listo",
                            text: "Datos obtenidos correctamente",
                            icon: "success"
                        });

                        tableBody.innerHTML = ''; // Clear existing rows

                        data.pagos.forEach(item => {
                            const row = document.createElement('tr');
                            row.innerHTML = `
                                <td>${item.consorcio_id}</td>
                                <td>${item.producto_id}</td>
                                <td>${item.agencia_id}</td>
                                <td>${item.descripcion}</td>
                                <td>${item.monto}</td>
                                <td>${fecha}</td>
                                <td>${item.importe}</td>
                                <td>${item.pagado_consorcio_id}</td>
                                <td>${item.plataforma}</td>
                            `;
                            tableBody.appendChild(row);
                        });

                        $('#tableRecargas').DataTable({
                            destroy: true,
                            responsive: true,
                            dom: 'Bfrtip',
                            buttons: [
                                'copy', 'csv', 'excel', 'pdf', 'print'
                            ]
                        });
                    }
                })
                .catch(error => console.error('Error fetching data:', error));
        });

        const btnGuardarData = document.getElementById('btnGuardarData');
        btnGuardarData.addEventListener('click', () => {
            const fecha = document.getElementById('inputFecha').value;
            if (!fecha) {
                Swal.fire({
                    title: "Error",
                    text: "Por favor, selecciona una fecha",
                    icon: "error"
                });
                return;
            }

            Swal.fire({
                title: "Guardando información ...",
                icon: 'info',
                allowOutsideClick: false,
                showConfirmButton: false,
                timerProgressBar: true,
                didOpen: () => Swal.showLoading()
            });
            fetch(`/save-pagos-aotra-empresa-lotonet?fecha=${fecha}`)
                .then(response => response.json())
                .then(data => {
                    Swal.fire({
                        title: "Listo",
                        text: data.message,
                        icon: "success"
                    });
                })
                .catch(error => console.error('Error fetching data:', error));
        });

        const btnEliminarData = document.getElementById('btnEliminarData');
        btnEliminarData.addEventListener('click', () => {
            const fecha = document.getElementById('inputFecha').value;
            if (!fecha) {
                Swal.fire({
                    title: "Error",
                    text: "Por favor, selecciona una fecha",
                    icon: "error"
                });
                return;
            }

            Swal.fire({
                title: "Guardando información ...",
                icon: 'info',
                allowOutsideClick: false,
                showConfirmButton: false,
                timerProgressBar: true,
                didOpen: () => Swal.showLoading()
            });
            fetch(`/delete-pagos-aotra-empresa-lotonet?fecha=${fecha}`)
                .then(response => response.json())
                .then(data => {
                    Swal.fire({
                        title: "Listo",
                        text: data.message,
                        icon: "success"
                    });
                })
                .catch(error => console.error('Error fetching data:', error));
        });

        const rangeModalElement = document.getElementById('myModal');

        const closeRangeModal = async () => {
            if (!rangeModalElement) return;
            const modalInstance = window.bootstrap?.Modal.getInstance(rangeModalElement);
            if (!modalInstance) return;
            await new Promise((resolve) => {
                let resolved = false;
                const finish = () => {
                    if (resolved) return;
                    resolved = true;
                    rangeModalElement.removeEventListener('hidden.bs.modal', finish);
                    resolve();
                };
                rangeModalElement.addEventListener('hidden.bs.modal', finish, { once: true });
                modalInstance.hide();
                setTimeout(finish, 400);
            });
        };

        const escapeLotonetHtml = (value) => String(value ?? '')
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');

        const getDateRange = (fechaInicio, fechaFin) => {
            const dates = [];
            let currentDate = new Date(`${fechaInicio}T12:00:00`);
            const endDate = new Date(`${fechaFin}T12:00:00`);
            while (currentDate <= endDate) {
                const year = currentDate.getFullYear();
                const month = String(currentDate.getMonth() + 1).padStart(2, '0');
                const day = String(currentDate.getDate()).padStart(2, '0');
                dates.push(`${year}-${month}-${day}`);
                currentDate.setDate(currentDate.getDate() + 1);
            }
            return dates;
        };

        const requestJson = async (url) => {
            const response = await fetch(url);
            const rawText = await response.text();
            let payload = null;
            try {
                payload = rawText ? JSON.parse(rawText) : null;
            } catch (_error) {
                payload = null;
            }
            if (!response.ok) {
                return { ok: false, payload, message: payload?.error || payload?.message || rawText || `Error HTTP ${response.status}` };
            }
            if (payload && payload.code !== undefined && Number(payload.code) !== 0) {
                return { ok: false, payload, message: payload.message || payload.error || 'El proceso devolvio un error.' };
            }
            return { ok: true, payload, message: payload?.message || payload?.success || 'Proceso completado.' };
        };

        const buildResultsHtml = (results) => {
            const rows = results.map((result) => {
                const badgeClass = result.status === 'ok'
                    ? 'success'
                    : (result.status === 'warning' ? 'warning text-dark' : 'danger');
                const total = result.total !== null && result.total !== undefined
                    ? Number(result.total).toLocaleString('es-DO')
                    : '-';
                return `
                    <tr>
                        <td>${escapeLotonetHtml(result.date)}</td>
                        <td><span class="badge bg-${badgeClass}">${escapeLotonetHtml(result.label)}</span></td>
                        <td>${escapeLotonetHtml(result.message)}</td>
                        <td>${escapeLotonetHtml(total)}</td>
                    </tr>
                `;
            }).join('');
            return `
                <div class="table-responsive text-start">
                    <table class="table table-sm align-middle mb-0">
                        <thead>
                            <tr>
                                <th>Fecha</th>
                                <th>Estado</th>
                                <th>Detalle</th>
                                <th>Total</th>
                            </tr>
                        </thead>
                        <tbody>${rows}</tbody>
                    </table>
                </div>
            `;
        };

        const showBatchSummary = async (title, results) => {
            const hasErrors = results.some((result) => result.status === 'error');
            const hasWarnings = results.some((result) => result.status === 'warning');
            const icon = hasErrors ? 'error' : (hasWarnings ? 'warning' : 'success');
            await closeRangeModal();
            return Swal.fire({
                title,
                html: buildResultsHtml(results),
                icon,
                width: 900,
                confirmButtonText: 'Cerrar'
            });
        };

        const btnGuardarDataFecha = document.getElementById('btnGuardarDataFecha');
        btnGuardarDataFecha.addEventListener('click', async () => {
            const fechaInicio = document.getElementById('fechaInicio').value;
            const fechaFin = document.getElementById('fechaFin').value;
            if (!fechaInicio || !fechaFin) {
                Swal.fire({ title: "Error", text: "Por favor, selecciona ambas fechas", icon: "error" });
                return;
            }
            if (new Date(fechaInicio) > new Date(fechaFin)) {
                Swal.fire({ title: "Error", text: "La fecha de inicio debe ser anterior a la fecha de fin", icon: "error" });
                return;
            }

            const responses = [];
            const dates = getDateRange(fechaInicio, fechaFin);

            btnGuardarDataFecha.disabled = true;
            try {
                Swal.fire({
                    title: "Guardando informacion ...",
                    html: `0 / ${dates.length}`,
                    allowOutsideClick: false,
                    showConfirmButton: false,
                    didOpen: () => Swal.showLoading()
                });

                for (let i = 0; i < dates.length; i++) {
                    const date = dates[i];
                    Swal.update({ html: `Procesando ${date} (${i + 1} / ${dates.length})` });

                    const result = await requestJson(`/save-pagos-aotra-empresa-lotonet?fecha=${date}`);
                    const payload = result.payload || {};
                    const total = payload.total ?? null;
                    let status = result.ok ? 'ok' : 'error';
                    let label = result.ok ? 'Guardado' : 'Error';

                    if (result.ok && Number(total || 0) === 0) {
                        status = 'warning';
                        label = 'Sin data';
                    }

                    responses.push({ date, status, label, message: result.message, total });
                }

                await showBatchSummary("Resultado del proceso", responses);
            } catch (error) {
                Swal.fire({ title: "Error", text: error.message || "Ocurrio un error al procesar las fechas", icon: "error" });
            } finally {
                btnGuardarDataFecha.disabled = false;
            }
        });

        const btnEliminarDataFecha = document.getElementById('btnEliminarDataFecha');
        btnEliminarDataFecha.addEventListener('click', async () => {
            const fechaInicio = document.getElementById('fechaInicio').value;
            const fechaFin = document.getElementById('fechaFin').value;
            if (!fechaInicio || !fechaFin) {
                Swal.fire({ title: "Error", text: "Por favor, selecciona ambas fechas", icon: "error" });
                return;
            }
            if (new Date(fechaInicio) > new Date(fechaFin)) {
                Swal.fire({ title: "Error", text: "La fecha de inicio debe ser anterior a la fecha de fin", icon: "error" });
                return;
            }

            const confirmed = await Swal.fire({
                title: 'Confirmar eliminacion',
                html: `¿Eliminar data desde <strong>${fechaInicio}</strong> hasta <strong>${fechaFin}</strong>?`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Si, eliminar',
                cancelButtonText: 'Cancelar'
            });
            if (!confirmed.isConfirmed) return;

            const responses = [];
            const dates = getDateRange(fechaInicio, fechaFin);

            btnEliminarDataFecha.disabled = true;
            try {
                Swal.fire({
                    title: "Eliminando informacion ...",
                    html: `0 / ${dates.length}`,
                    allowOutsideClick: false,
                    showConfirmButton: false,
                    didOpen: () => Swal.showLoading()
                });

                for (let i = 0; i < dates.length; i++) {
                    const date = dates[i];
                    Swal.update({ html: `Eliminando ${date} (${i + 1} / ${dates.length})` });

                    const result = await requestJson(`/delete-pagos-aotra-empresa-lotonet?fecha=${date}`);
                    const payload = result.payload || {};
                    responses.push({
                        date,
                        status: result.ok ? 'ok' : 'error',
                        label: result.ok ? 'Eliminado' : 'Error',
                        message: result.message,
                        total: payload.total ?? null
                    });
                }

                await showBatchSummary("Resultado de la eliminacion", responses);
            } catch (error) {
                Swal.fire({ title: "Error", text: error.message || "Ocurrio un error al procesar las fechas", icon: "error" });
            } finally {
                btnEliminarDataFecha.disabled = false;
            }
        });
    </script>
@endsection

