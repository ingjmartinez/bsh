@extends('app')

@section('content')
    <div class="main-content">
        <div class="page-content">
            <div class="container-fluid">
                <style>
                    .acciones-lotobet .btn {
                        width: auto;
                    }

                    @media (max-width: 767.98px) {
                        .acciones-lotobet .btn {
                            width: 100%;
                        }

                        .acciones-lotobet .btn {
                            min-height: 44px;
                        }
                    }
                </style>

                <!-- start page title -->
                <div class="row">
                    <div class="col-12">
                        <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                            <h4 class="mb-sm-0">Ventas por Producto Lotobet</h4>

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
                                <div class="row g-2 mb-3 acciones-lotobet align-items-end">
                                    <div class="col-12 col-lg-4 d-grid d-md-flex gap-2">
                                        <button id="btnGenerarToken" class="btn btn-primary">Generar Token</button>
                                        <button id="btnGenerarData" class="btn btn-primary">Generar Data</button>
                                    </div>

                                    <div class="col-12 col-md-4 col-lg-2">
                                        <label for="inputFecha" class="form-label mb-1">Fecha</label>
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

                                <table id="tableVentas"
                                    class="table table-bordered dt-responsive nowrap table-striped align-middle"
                                    style="width:100%">
                                    <thead>
                                        <tr>
                                            <th>Consorcio</th>
                                            <th>Agencia</th>
                                            <th>Producto</th>
                                            <th>Descripcion</th>
                                            <th>Monto</th>
                                            <th>Fecha</th>
                                            <th>Comisión</th>
                                            <th>Comisión Supervisor</th>
                                            <th>N° Sorteo</th>
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
        async function parseJsonResponse(response, contexto) {
            const body = await response.text();
            let data = null;

            if (body) {
                try {
                    data = JSON.parse(body);
                } catch (_error) {
                    data = null;
                }
            }

            if (!response.ok) {
                const rawMessage = data?.message || data?.error || body || `HTTP ${response.status}`;
                const message = String(rawMessage).replace(/<[^>]*>/g, ' ').replace(/\s+/g, ' ').trim().slice(0, 500);
                throw new Error(`${contexto} (${response.status}): ${message}`);
            }

            if (!data) {
                throw new Error(`${contexto}: respuesta invalida del servidor.`);
            }

            return data;
        }

        const btnGenerarToken = document.getElementById('btnGenerarToken');
        btnGenerarToken.addEventListener('click', async () => {
            try {
                const response = await fetch("/generar-token", {
                    headers: { 'Accept': 'application/json' }
                });
                const data = await parseJsonResponse(response, 'Error generando token');

                Swal.fire({
                    title: "Listo",
                    text: data.success,
                    icon: "success"
                });
            } catch (error) {
                Swal.fire({
                    title: "Error",
                    text: error.message || "No se pudo generar el token.",
                    icon: "error"
                });
            }
        });

        const btnGenerarData = document.getElementById('btnGenerarData');
        btnGenerarData.addEventListener('click', async () => {
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

            $('#tableVentas').DataTable().destroy();
            const tableBody = document.querySelector('#tableVentas tbody');
            tableBody.innerHTML = '';

            try {
                const response = await fetch(`/ventas-producto-lotobet?fecha=${encodeURIComponent(fecha)}`, {
                    headers: { 'Accept': 'application/json' }
                });
                const data = await parseJsonResponse(response, 'Error consultando ventas por producto');

                if (data.code != 0) {
                    throw new Error(data.message || 'La API devolvio un error.');
                }

                Swal.fire({
                    title: "Listo",
                    text: "Datos obtenidos correctamente",
                    icon: "success"
                });

                tableBody.innerHTML = '';
                data.ventas.forEach(item => {
                    const row = document.createElement('tr');
                    row.innerHTML = `
                        <td>${item.consorcio_id}</td>
                        <td>${item.agencia_id}</td>
                        <td>${item.producto_id}</td>
                        <td>${item.descripcion}</td>
                        <td>${item.monto}</td>
                        <td>${fecha}</td>
                        <td>${item.comision}</td>
                        <td>${item.comision_supervisor}</td>
                        <td>${item.numero_sorteo}</td>
                    `;
                    tableBody.appendChild(row);
                });

                $('#tableVentas').DataTable({
                    destroy: true,
                    responsive: true,
                    dom: 'Bfrtip',
                    buttons: [
                        'copy', 'csv', 'excel', 'pdf', 'print'
                    ]
                });
            } catch (error) {
                Swal.fire({
                    title: "Error",
                    text: error.message || "No se pudo consultar la data.",
                    icon: "error"
                });
            }
        });

        const btnGuardarData = document.getElementById('btnGuardarData');
        btnGuardarData.addEventListener('click', async () => {
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

            try {
                const response = await fetch(`/save-ventas-producto-lotobet?fecha=${encodeURIComponent(fecha)}`, {
                    headers: { 'Accept': 'application/json' }
                });
                const data = await parseJsonResponse(response, 'Error guardando ventas por producto');

                Swal.fire({
                    title: "Listo",
                    text: data.message,
                    icon: "success"
                });
            } catch (error) {
                Swal.fire({
                    title: "Error",
                    text: error.message || "No se pudo guardar la data.",
                    icon: "error"
                });
            }
        });

        const btnEliminarData = document.getElementById('btnEliminarData');
        btnEliminarData.addEventListener('click', async () => {
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

            try {
                const response = await fetch(`/delete-ventas-producto-lotobet?fecha=${encodeURIComponent(fecha)}`, {
                    headers: { 'Accept': 'application/json' }
                });
                const data = await parseJsonResponse(response, 'Error eliminando ventas por producto');

                Swal.fire({
                    title: "Listo",
                    text: data.message,
                    icon: "success"
                });
            } catch (error) {
                Swal.fire({
                    title: "Error",
                    text: error.message || "No se pudo eliminar la data.",
                    icon: "error"
                });
            }
        });

        const btnGuardarDataFecha = document.getElementById('btnGuardarDataFecha');
        btnGuardarDataFecha.addEventListener('click', async () => {
            // Quiero consultar un api y hacer una peticion por cada fecha entre el rango seleccionado
            const fechaInicio = document.getElementById('fechaInicio').value;
            const fechaFin = document.getElementById('fechaFin').value;
            if (!fechaInicio || !fechaFin) {
                Swal.fire({
                    title: "Error",
                    text: "Por favor, selecciona ambas fechas",
                    icon: "error"
                });
                return;
            }

            // recorrer la fecha entre el rango
            const startDate = new Date(fechaInicio);
            const endDate = new Date(fechaFin);

            if (startDate > endDate) {
                Swal.fire({
                    title: "Error",
                    text: "La fecha de inicio debe ser anterior a la fecha de fin",
                    icon: "error"
                });
                return;
            }

            let responses = [];
            let currentDate = new Date(startDate);
            const dates = [];

            while (currentDate <= endDate) {
                dates.push(currentDate.toISOString().split('T')[0]); // Formato YYYY-MM-DD
                currentDate.setDate(currentDate.getDate() + 1); // Incrementar un día
            }

            // Ejecutar las peticiones una a una (secuencialmente)
            btnGuardarDataFecha.disabled = true;
            try {
                Swal.fire({
                    title: "Guardando información ...",
                    html: `0 / ${dates.length}`,
                    allowOutsideClick: false,
                    showConfirmButton: false,
                    didOpen: () => Swal.showLoading()
                });

                for (let i = 0; i < dates.length; i++) {
                    const date = dates[i];
                    // Actualizar progreso en el modal
                    Swal.update({
                        html: `Procesando ${date} (${i + 1} / ${dates.length})`
                    });

                    const response = await fetch(`/save-ventas-producto-lotobet?fecha=${encodeURIComponent(date)}`, {
                        headers: { 'Accept': 'application/json' }
                    });
                    const data = await parseJsonResponse(response, `Error guardando fecha ${date}`);

                    // Si tu API devuelve un código de error, puedes manejarlo aquí
                    if (data && data.code !== undefined && data.code !== 0) {
                        throw new Error(data.message || `Error guardando fecha ${date}`);
                    }

                    if (!data.total) {
                        responses.push(data.message);
                    } else {
                        responses.push('Fecha: ' + date + ' Total: ' + data.total);
                    }
                    // Opcional: puedes hacer una pequeña pausa si tu API lo requiere
                    // await new Promise(r => setTimeout(r, 200));
                }

                // Cerrar modal del range y notificar éxito
                document.getElementById('btnClose').click();
                Swal.fire({
                    title: "Listo",
                    html: responses.join('<br>'),
                    icon: "success"
                });
            } catch (error) {
                Swal.fire({
                    title: "Error",
                    text: error.message || "Ocurrió un error al procesar las fechas",
                    icon: "error"
                });
            } finally {
                btnGuardarDataFecha.disabled = false;
            }
        });

        const btnEliminarDataFecha = document.getElementById('btnEliminarDataFecha');
        btnEliminarDataFecha.addEventListener('click', async () => {
            const fechaInicio = document.getElementById('fechaInicio').value;
            const fechaFin = document.getElementById('fechaFin').value;
            if (!fechaInicio || !fechaFin) {
                Swal.fire({
                    title: "Error",
                    text: "Por favor, selecciona ambas fechas",
                    icon: "error"
                });
                return;
            }

            const startDate = new Date(fechaInicio);
            const endDate = new Date(fechaFin);

            if (startDate > endDate) {
                Swal.fire({
                    title: "Error",
                    text: "La fecha de inicio debe ser anterior a la fecha de fin",
                    icon: "error"
                });
                return;
            }

            const confirmed = await Swal.fire({
                title: 'Confirmar eliminación',
                html: `¿Eliminar data desde <strong>${fechaInicio}</strong> hasta <strong>${fechaFin}</strong>?`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Sí, eliminar',
                cancelButtonText: 'Cancelar'
            });
            if (!confirmed.isConfirmed) return;

            let responses = [];
            let currentDate = new Date(startDate);
            const dates = [];

            while (currentDate <= endDate) {
                dates.push(currentDate.toISOString().split('T')[0]);
                currentDate.setDate(currentDate.getDate() + 1);
            }

            btnEliminarDataFecha.disabled = true;
            try {
                Swal.fire({
                    title: "Eliminando información ...",
                    html: `0 / ${dates.length}`,
                    allowOutsideClick: false,
                    showConfirmButton: false,
                    didOpen: () => Swal.showLoading()
                });

                for (let i = 0; i < dates.length; i++) {
                    const date = dates[i];
                    Swal.update({
                        html: `Eliminando ${date} (${i + 1} / ${dates.length})`
                    });

                    const response = await fetch(`/delete-ventas-producto-lotobet?fecha=${encodeURIComponent(date)}`, {
                        headers: { 'Accept': 'application/json' }
                    });
                    const data = await parseJsonResponse(response, `Error eliminando fecha ${date}`);

                    if (data && data.code !== undefined && data.code !== 0) {
                        throw new Error(data.message || `Error eliminando fecha ${date}`);
                    }

                    if (!data.total) {
                        responses.push(data.message);
                    } else {
                        responses.push('Fecha: ' + date + ' Total: ' + data.total);
                    }
                }

                document.getElementById('btnClose').click();
                Swal.fire({
                    title: "Listo",
                    html: responses.join('<br>'),
                    icon: "success"
                });
            } catch (error) {
                Swal.fire({
                    title: "Error",
                    text: error.message || "Ocurrió un error al procesar las fechas",
                    icon: "error"
                });
            } finally {
                btnEliminarDataFecha.disabled = false;
            }
        });
    </script>
@endsection

