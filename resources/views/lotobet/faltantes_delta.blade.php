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
                            <h4 class="mb-sm-0">Faltantes Delta</h4>
                            <div class="page-title-right">
                                <ol class="breadcrumb m-0">
                                    <li class="breadcrumb-item"><a href="javascript: void(0);">Tables</a></li>
                                    <li class="breadcrumb-item active">Datatables</li>
                                </ol>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-lg-12">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="card-title mb-0">Configurar Token</h5>
                            </div>
                            <div class="card-body">
                                <div class="row g-2 mb-3 acciones-delta align-items-end">
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

                                <table id="tableFaltantes"
                                    class="table table-bordered dt-responsive nowrap table-striped align-middle"
                                    style="width:100%">
                                    <thead>
                                        <tr>
                                            <th>Transaccion</th>
                                            <th>Tipo</th>
                                            <th>Concepto</th>
                                            <th>Estatus</th>
                                            <th>Fecha Transaccion</th>
                                            <th>Fecha Inclusion</th>
                                            <th>Usuario</th>
                                            <th>Cuenta</th>
                                            <th>Debito</th>
                                            <th>Credito</th>
                                            <th>Numero</th>
                                            <th>Nombre Cuenta</th>
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
            </div>
        </div>
    </div>
@endsection

@section('script')
    <script>
    (() => {
        const endpoints = {
            get: '/get-faltantes-delta',
            save: '/save-faltantes-delta',
            delete: '/delete-faltantes-delta',
            token: '/login-flash',
        };

        const escapeHtml = (value) => String(value ?? '')
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');

        const showLoading = (title, html = null) => {
            Swal.fire({
                title,
                html,
                icon: 'info',
                allowOutsideClick: false,
                showConfirmButton: false,
                timerProgressBar: true,
                didOpen: () => Swal.showLoading()
            });
        };

        const selectedDates = () => {
            const fechaInicio = document.getElementById('fechaInicio').value;
            const fechaFin = document.getElementById('fechaFin').value;

            if (!fechaInicio || !fechaFin) {
                throw new Error('Por favor, selecciona ambas fechas');
            }

            const startDate = new Date(fechaInicio);
            const endDate = new Date(fechaFin);

            if (startDate > endDate) {
                throw new Error('La fecha de inicio debe ser anterior a la fecha de fin');
            }

            const dates = [];
            const currentDate = new Date(startDate);

            while (currentDate <= endDate) {
                dates.push(currentDate.toISOString().split('T')[0]);
                currentDate.setDate(currentDate.getDate() + 1);
            }

            return dates;
        };

        document.getElementById('btnGenerarToken').addEventListener('click', () => {
            showLoading('Generando token ...');

            fetch(endpoints.token)
                .then(response => response.json())
                .then(data => {
                    if (data.message) {
                        Swal.fire({ title: 'Error', text: data.message, icon: 'error' });
                        return;
                    }

                    Swal.fire({ title: 'Listo', text: data.success, icon: 'success' });
                })
                .catch(error => {
                    console.error('Error fetching data:', error);
                    Swal.fire({ title: 'Error', text: 'No se pudo generar el token', icon: 'error' });
                });
        });

        document.getElementById('btnGenerarData').addEventListener('click', () => {
            const fecha = document.getElementById('inputFecha').value;
            if (!fecha) {
                Swal.fire({ title: 'Error', text: 'Por favor, selecciona una fecha', icon: 'error' });
                return;
            }

            showLoading('Cargando ...');
            if ($.fn.DataTable.isDataTable('#tableFaltantes')) {
                $('#tableFaltantes').DataTable().destroy();
            }

            const tableBody = document.querySelector('#tableFaltantes tbody');
            tableBody.innerHTML = '';

            fetch(`${endpoints.get}?fecha=${fecha}`)
                .then(response => response.json())
                .then(data => {
                    if (data.code != 0) {
                        Swal.fire({
                            title: 'Error',
                            text: data.message || data.error || 'No se pudo obtener la data',
                            icon: 'error'
                        });
                        return;
                    }

                    data.faltantes.forEach(item => {
                        const row = document.createElement('tr');
                        row.innerHTML = `
                            <td>${escapeHtml(item.IdTrans)}</td>
                            <td>${escapeHtml(item.IdTipoTrans)}</td>
                            <td>${escapeHtml(item.Concepto)}</td>
                            <td>${escapeHtml(item.Estatus)}</td>
                            <td>${escapeHtml(item.FecTransaccion)}</td>
                            <td>${escapeHtml(item.FecInclusion)}</td>
                            <td>${escapeHtml(item.UsrInclusion)}</td>
                            <td>${escapeHtml(item.IdCuenta)}</td>
                            <td>${escapeHtml(item.Debito)}</td>
                            <td>${escapeHtml(item.Credito)}</td>
                            <td>${escapeHtml(item.Numero)}</td>
                            <td>${escapeHtml(item.NombreCuenta)}</td>
                        `;
                        tableBody.appendChild(row);
                    });

                    $('#tableFaltantes').DataTable({
                        destroy: true,
                        responsive: true,
                        scrollX: true,
                        dom: 'Bfrtip',
                        buttons: ['copy', 'csv', 'excel', 'pdf', 'print']
                    });

                    Swal.fire({ title: 'Listo', text: 'Datos obtenidos correctamente', icon: 'success' });
                })
                .catch(error => {
                    console.error('Error fetching data:', error);
                    Swal.fire({ title: 'Error', text: 'No se pudo obtener la data', icon: 'error' });
                });
        });

        document.getElementById('btnGuardarData').addEventListener('click', () => {
            const fecha = document.getElementById('inputFecha').value;
            if (!fecha) {
                Swal.fire({ title: 'Error', text: 'Por favor, selecciona una fecha', icon: 'error' });
                return;
            }

            showLoading('Guardando informacion ...');
            fetch(`${endpoints.save}?fecha=${fecha}`)
                .then(response => response.json())
                .then(data => Swal.fire({ title: data.code ? 'Error' : 'Listo', text: data.message, icon: data.code ? 'error' : 'success' }))
                .catch(error => {
                    console.error('Error fetching data:', error);
                    Swal.fire({ title: 'Error', text: 'No se pudo guardar la data', icon: 'error' });
                });
        });

        document.getElementById('btnEliminarData').addEventListener('click', () => {
            const fecha = document.getElementById('inputFecha').value;
            if (!fecha) {
                Swal.fire({ title: 'Error', text: 'Por favor, selecciona una fecha', icon: 'error' });
                return;
            }

            showLoading('Eliminando informacion ...');
            fetch(`${endpoints.delete}?fecha=${fecha}`)
                .then(response => response.json())
                .then(data => Swal.fire({ title: 'Listo', text: data.message, icon: 'success' }))
                .catch(error => {
                    console.error('Error fetching data:', error);
                    Swal.fire({ title: 'Error', text: 'No se pudo eliminar la data', icon: 'error' });
                });
        });

        document.getElementById('btnGuardarDataFecha').addEventListener('click', async () => {
            let dates;
            try {
                dates = selectedDates();
            } catch (error) {
                Swal.fire({ title: 'Error', text: error.message, icon: 'error' });
                return;
            }

            const button = document.getElementById('btnGuardarDataFecha');
            const responses = [];
            button.disabled = true;

            try {
                showLoading('Guardando informacion ...', `0 / ${dates.length}`);

                for (let i = 0; i < dates.length; i++) {
                    const date = dates[i];
                    Swal.update({ html: `Procesando ${date} (${i + 1} / ${dates.length})` });

                    const response = await fetch(`${endpoints.save}?fecha=${date}`);
                    const data = await response.json();
                    if (!response.ok || (data.code !== undefined && data.code !== 0)) {
                        throw new Error(data.message || `Error guardando fecha ${date}`);
                    }

                    responses.push(data.total ? `Fecha: ${date} Total: ${data.total}` : data.message);
                }

                document.getElementById('btnClose').click();
                Swal.fire({ title: 'Listo', html: responses.join('<br>'), icon: 'success' });
            } catch (error) {
                Swal.fire({ title: 'Error', text: error.message || 'Ocurrio un error al procesar las fechas', icon: 'error' });
            } finally {
                button.disabled = false;
            }
        });

        document.getElementById('btnEliminarDataFecha').addEventListener('click', async () => {
            let dates;
            try {
                dates = selectedDates();
            } catch (error) {
                Swal.fire({ title: 'Error', text: error.message, icon: 'error' });
                return;
            }

            const confirmed = await Swal.fire({
                title: 'Confirmar eliminacion',
                html: `Eliminar data desde <strong>${dates[0]}</strong> hasta <strong>${dates[dates.length - 1]}</strong>?`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Si, eliminar',
                cancelButtonText: 'Cancelar'
            });

            if (!confirmed.isConfirmed) {
                return;
            }

            const button = document.getElementById('btnEliminarDataFecha');
            const responses = [];
            button.disabled = true;

            try {
                showLoading('Eliminando informacion ...', `0 / ${dates.length}`);

                for (let i = 0; i < dates.length; i++) {
                    const date = dates[i];
                    Swal.update({ html: `Eliminando ${date} (${i + 1} / ${dates.length})` });

                    const response = await fetch(`${endpoints.delete}?fecha=${date}`);
                    const data = await response.json();
                    if (!response.ok || (data.code !== undefined && data.code !== 0)) {
                        throw new Error(data.message || `Error eliminando fecha ${date}`);
                    }

                    responses.push(data.message);
                }

                document.getElementById('btnClose').click();
                Swal.fire({ title: 'Listo', html: responses.join('<br>'), icon: 'success' });
            } catch (error) {
                Swal.fire({ title: 'Error', text: error.message || 'Ocurrio un error al procesar las fechas', icon: 'error' });
            } finally {
                button.disabled = false;
            }
        });
    })();
    </script>
@endsection
