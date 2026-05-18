@extends('app')

@section('content')
    <div class="main-content">
        <div class="page-content">
            <div class="container-fluid">

                <!-- start page title -->
                <div class="row">
                    <div class="col-12">
                        <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                            <h4 class="mb-sm-0">Ventas por Usuario Lotobet</h4>

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
                                <div class="row mb-5">
                                    <div class="col-3">
                                        <button id="btnGenerarToken" class="btn btn-primary">Generar Token</button>
                                        <button id="btnGenerarData" class="btn btn-primary">Generar Data</button>
                                    </div>

                                    <div class="col-2">
                                        <input type="date" id="inputFecha" class="form-control">
                                    </div>
                                    <div class="col-3">
                                        <button id="btnGuardarData" class="btn btn-primary">Guardar Data</button>
                                        <button id="btnEliminarData" class="btn btn-danger">Eliminar Data</button>
                                    </div>
                                    <div class="col-4 text-end">
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
                                            <th>Cedula</th>
                                            <th>Descripcion</th>
                                            <th>Tipo</th>
                                            <th>Monto</th>
                                            <th>Fecha</th>
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
        const parseApiResponse = async (response) => {
            const rawText = await response.text();
            let payload = null;

            try {
                payload = rawText ? JSON.parse(rawText) : null;
            } catch (_error) {
                payload = null;
            }

            if (!response.ok) {
                let message = payload?.message || payload?.error || rawText || `Error HTTP ${response.status}`;

                if (response.status === 500) {
                    message = 'El servidor devolvio un error 500 al procesar la solicitud. Intente nuevamente. Si el problema sigue, revise el log del sistema porque puede ser un tema de memoria o proceso interno.';
                }

                return {
                    ok: false,
                    status: response.status,
                    payload,
                    message
                };
            }

            if (payload && payload.code !== undefined && Number(payload.code) !== 0) {
                return {
                    ok: false,
                    status: response.status,
                    payload,
                    message: payload.message || payload.error || 'El proceso devolvio un error.'
                };
            }

            return {
                ok: true,
                status: response.status,
                payload,
                message: payload?.message || payload?.success || 'Proceso completado.'
            };
        };

        const btnGenerarToken = document.getElementById('btnGenerarToken');
        btnGenerarToken.addEventListener('click', async () => {
            fetch("/generar-token")
                .then(parseApiResponse)
                .then(result => {
                    Swal.fire({
                        title: result.ok ? "Listo" : "Error",
                        text: result.message,
                        icon: result.ok ? "success" : "error"
                    });
                })
                .catch(error => {
                    Swal.fire({
                        title: "Error",
                        text: error.message || 'No se pudo completar la solicitud.',
                        icon: "error"
                    });
                });
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

            fetch(`/ventas-usuarios-lotobet?fecha=${fecha}`)
                .then(parseApiResponse)
                .then(result => {
                    if (!result.ok) {
                        Swal.fire({
                            title: "Error",
                            text: result.message,
                            icon: "error"
                        });
                        return;
                    }

                    const data = result.payload || {};

                    Swal.fire({
                        title: "Listo",
                        text: "Datos obtenidos correctamente",
                        icon: "success"
                    });

                    tableBody.innerHTML = '';

                    (data.ventas || []).forEach(item => {
                        const row = document.createElement('tr');
                        row.innerHTML = `
                            <td>${item.consorcio_id}</td>
                            <td>${item.agencia_id}</td>
                            <td>${item.producto_id}</td>
                            <td>${item.cedula}</td>
                            <td>${item.descripcion}</td>
                            <td>${item.tipo}</td>
                            <td>${item.monto}</td>
                            <td>${fecha}</td>
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
                })
                .catch(error => {
                    Swal.fire({
                        title: "Error",
                        text: error.message || 'No se pudo completar la solicitud.',
                        icon: "error"
                    });
                });
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
                title: "Guardando informaciÃ³n ...",
                icon: 'info',
                allowOutsideClick: false,
                showConfirmButton: false,
                timerProgressBar: true,
                didOpen: () => Swal.showLoading()
            });
            fetch(`/save-ventas-usuarios-lotobet?fecha=${fecha}`)
                .then(parseApiResponse)
                .then(result => {
                    Swal.fire({
                        title: result.ok ? "Listo" : "Error",
                        text: result.message,
                        icon: result.ok ? "success" : "error"
                    });
                })
                .catch(error => {
                    Swal.fire({
                        title: "Error",
                        text: error.message || 'No se pudo completar la solicitud.',
                        icon: "error"
                    });
                });
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
                title: "Guardando informaciÃ³n ...",
                icon: 'info',
                allowOutsideClick: false,
                showConfirmButton: false,
                timerProgressBar: true,
                didOpen: () => Swal.showLoading()
            });
            fetch(`/delete-ventas-usuarios-lotobet?fecha=${fecha}`)
                .then(parseApiResponse)
                .then(result => {
                    Swal.fire({
                        title: result.ok ? "Listo" : "Error",
                        text: result.message,
                        icon: result.ok ? "success" : "error"
                    });
                })
                .catch(error => {
                    Swal.fire({
                        title: "Error",
                        text: error.message || 'No se pudo completar la solicitud.',
                        icon: "error"
                    });
                });
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
                currentDate.setDate(currentDate.getDate() + 1); // Incrementar un dÃ­a
            }

            // Ejecutar las peticiones una a una (secuencialmente)
            btnGuardarDataFecha.disabled = true;
            try {
                Swal.fire({
                    title: "Guardando informaciÃ³n ...",
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

                    const result = await fetch(`/save-ventas-usuarios-lotobet?fecha=${date}`).then(parseApiResponse);
                    if (!result.ok) {
                        throw new Error(result.message || `Error procesando fecha ${date}`);
                    }
                    const data = result.payload || null;

                    // Si tu API devuelve un cÃ³digo de error, puedes manejarlo aquÃ­
                    if (data && data.code !== undefined && data.code !== 0) {
                        throw new Error(data.message || `Error guardando fecha ${date}`);
                    }

                    if (!data.total) {
                        responses.push(data.message);
                    } else {
                        responses.push('Fecha: ' + date + ' Total: ' + data.total);
                    }
                    // Opcional: puedes hacer una pequeÃ±a pausa si tu API lo requiere
                    // await new Promise(r => setTimeout(r, 200));
                }

                // Cerrar modal del range y notificar Ã©xito
                document.getElementById('btnClose').click();
                Swal.fire({
                    title: "Listo",
                    html: responses.join('<br>'),
                    icon: "success"
                });
            } catch (error) {
                Swal.fire({
                    title: "Error",
                    text: error.message || "OcurriÃ³ un error al procesar las fechas",
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
                title: 'Confirmar eliminaciÃ³n',
                html: `Â¿Eliminar data desde <strong>${fechaInicio}</strong> hasta <strong>${fechaFin}</strong>?`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'SÃ­, eliminar',
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
                    title: "Eliminando informaciÃ³n ...",
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

                    const result = await fetch(`/delete-ventas-usuarios-lotobet?fecha=${date}`).then(parseApiResponse);
                    if (!result.ok) {
                        throw new Error(result.message || `Error eliminando fecha ${date}`);
                    }
                    const data = result.payload || null;

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
                    text: error.message || "OcurriÃ³ un error al procesar las fechas",
                    icon: "error"
                });
            } finally {
                btnEliminarDataFecha.disabled = false;
            }
        });
    </script>
@endsection

