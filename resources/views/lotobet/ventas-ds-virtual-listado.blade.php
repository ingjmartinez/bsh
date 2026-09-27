@extends('app')

@section('content')
    <div class="main-content">
        <div class="page-content">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-12">
                        <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                            <h4 class="mb-sm-0">Ventas DS Virtual</h4>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="card-title mb-0">Configurar Token</h5>
                            </div>
                            <div class="card-body">
                                <div class="row g-2 mb-3 align-items-end acciones-ds-virtual">
                                    <div class="col-12 col-lg-auto d-grid d-md-flex gap-2">
                                        <button id="btnGenerarTokenDsVirtual" class="btn btn-primary">Generar Token</button>
                                        <button id="btnGenerarData" class="btn btn-primary">Generar Data</button>
                                    </div>
                                    <div class="col-12 col-md-4 col-lg-2">
                                        <label for="inputFecha" class="form-label mb-1">Fecha</label>
                                        <input type="date" id="inputFecha" class="form-control">
                                    </div>
                                    <div class="col-12 col-lg-auto d-grid d-md-flex gap-2">
                                        <button id="btnGuardarData" class="btn btn-primary">Guardar Data</button>
                                        <button id="btnEliminarData" class="btn btn-danger">Eliminar Data</button>
                                    </div>
                                    <div class="col-12 col-md-8 col-lg d-grid d-lg-flex justify-content-lg-end">
                                        <button id="btnGenerarDataFecha" class="btn btn-primary" data-bs-toggle="modal"
                                            data-bs-target="#modalRangoDsVirtual">Generar Data Por Fecha</button>
                                    </div>
                                </div>

                                <div class="table-responsive">
                                    <table id="tableVentasDsVirtual"
                                        class="table table-bordered nowrap table-striped align-middle"
                                        style="width:100%">
                                        <thead>
                                            <tr>
                                                <th>Consorcio</th>
                                                <th>Fecha</th>
                                                <th>Agencia</th>
                                                <th>Ventas</th>
                                                <th>Premios pagados</th>
                                                <th>Proveedor ID</th>
                                                <th>Premios</th>
                                                <th>Proveedor</th>
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

    <div id="modalRangoDsVirtual" class="modal fade" tabindex="-1" aria-labelledby="modalRangoDsVirtualLabel"
        aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalRangoDsVirtualLabel">Procesar datos por rango de fechas</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="fechaInicioDsVirtual" class="form-label">Fecha inicio</label>
                        <input type="date" class="form-control" id="fechaInicioDsVirtual">
                    </div>
                    <div>
                        <label for="fechaFinDsVirtual" class="form-label">Fecha fin</label>
                        <input type="date" class="form-control" id="fechaFinDsVirtual">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cerrar</button>
                    <button type="button" class="btn btn-primary" id="btnGuardarDataFecha">Registrar Data</button>
                    <button type="button" class="btn btn-danger" id="btnEliminarDataFecha">Eliminar Data</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('script')
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const dataUrl = @json(route('ventas-ds-virtual.data'));
            const tokenUrl = @json(url('/generar-token'));
            const syncUrl = @json(route('ventas-ds-virtual.sync'));
            const deleteUrl = @json(route('ventas-ds-virtual.destroy'));
            const csrfToken = @json(csrf_token());

            const escapeHtml = value => String(value ?? '')
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#039;');

            const formatMoney = value => Number(value || 0).toLocaleString('en-US', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2,
            });

            async function loadTransactions(date) {
                const url = new URL(dataUrl, window.location.origin);
                url.searchParams.set('fecha', date);
                const response = await fetch(url, {headers: {'Accept': 'application/json'}});
                const data = await response.json();

                if (!response.ok) {
                    throw new Error(data.message || 'No se pudo consultar Ventas DS Virtual.');
                }

                if ($.fn.DataTable.isDataTable('#tableVentasDsVirtual')) {
                    $('#tableVentasDsVirtual').DataTable().clear().destroy();
                }

                const tableBody = document.querySelector('#tableVentasDsVirtual tbody');
                tableBody.innerHTML = '';

                (data.ventas || []).forEach(item => {
                    const row = document.createElement('tr');
                    row.innerHTML = `
                        <td>${escapeHtml(item.consorcio_id)}</td>
                        <td>${escapeHtml(item.fecha)}</td>
                        <td>${escapeHtml(item.agencia_id)}</td>
                        <td class="text-end">${formatMoney(item.ventas)}</td>
                        <td class="text-end">${formatMoney(item.premios_pagados)}</td>
                        <td>${escapeHtml(item.proveedor_id)}</td>
                        <td class="text-end">${formatMoney(item.premios)}</td>
                        <td>${escapeHtml(item.proveedor_nombre)}</td>
                    `;
                    tableBody.appendChild(row);
                });

                $('#tableVentasDsVirtual').DataTable({
                    destroy: true,
                    responsive: true,
                    scrollX: true,
                    pageLength: 10,
                    dom: 'Bfrtip',
                    buttons: ['copy', 'csv', 'excel', 'pdf', 'print'],
                });

                return Number(data.total || 0);
            }

            function requireDate() {
                const date = document.getElementById('inputFecha').value;

                if (!date) {
                    throw new Error('Por favor, selecciona una fecha.');
                }

                return date;
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
                const data = await response.json();

                if (!response.ok) {
                    throw new Error(data.message || `Error HTTP ${response.status}`);
                }

                return data;
            }

            async function syncDate(date) {
                return jsonRequest(syncUrl, {
                    method: 'POST',
                    headers: {'Content-Type': 'application/json'},
                    body: JSON.stringify({fecha: date}),
                });
            }

            async function deleteDate(date) {
                return jsonRequest(deleteUrl, {
                    method: 'DELETE',
                    headers: {'Content-Type': 'application/json'},
                    body: JSON.stringify({fecha: date}),
                });
            }

            function rangeDates() {
                const start = document.getElementById('fechaInicioDsVirtual').value;
                const end = document.getElementById('fechaFinDsVirtual').value;

                if (!start || !end) {
                    throw new Error('Selecciona la fecha de inicio y la fecha final.');
                }

                const current = new Date(`${start}T00:00:00`);
                const last = new Date(`${end}T00:00:00`);

                if (current > last) {
                    throw new Error('La fecha de inicio debe ser anterior a la fecha final.');
                }

                const dates = [];
                while (current <= last) {
                    dates.push(current.toISOString().slice(0, 10));
                    current.setDate(current.getDate() + 1);
                }

                return dates;
            }

            document.getElementById('btnGenerarTokenDsVirtual').addEventListener('click', async event => {
                const button = event.currentTarget;
                button.disabled = true;

                try {
                    Swal.fire({
                        title: 'Generando token...',
                        allowOutsideClick: false,
                        didOpen: () => Swal.showLoading(),
                    });

                    const response = await fetch(tokenUrl, {
                        headers: {
                            'Accept': 'application/json',
                        },
                    });
                    const data = await response.json();

                    if (!response.ok) {
                        throw new Error(data.message || 'No se pudo generar el token DS Virtual.');
                    }

                    Swal.fire('Token generado', data.success || 'Token generado correctamente.', 'success');
                } catch (error) {
                    Swal.fire('Error', error.message, 'error');
                } finally {
                    button.disabled = false;
                }
            });

            document.getElementById('btnGenerarData').addEventListener('click', async () => {
                try {
                    const date = requireDate();
                    Swal.fire({title: 'Consultando...', allowOutsideClick: false, didOpen: () => Swal.showLoading()});
                    const total = await loadTransactions(date);
                    Swal.fire('Listo', `Datos obtenidos: ${total.toLocaleString('es-DO')} registros.`, 'success');
                } catch (error) {
                    Swal.fire('Error', error.message, 'error');
                }
            });

            document.getElementById('btnGuardarData').addEventListener('click', async () => {
                try {
                    const date = requireDate();
                    Swal.fire({title: 'Guardando información...', allowOutsideClick: false, didOpen: () => Swal.showLoading()});
                    const data = await syncDate(date);
                    await loadTransactions(date);
                    Swal.fire('Listo', data.message || 'Datos guardados correctamente.', 'success');
                } catch (error) {
                    Swal.fire('Error', error.message, 'error');
                }
            });

            document.getElementById('btnEliminarData').addEventListener('click', async () => {
                try {
                    const date = requireDate();
                    const confirmation = await Swal.fire({
                        title: 'Confirmar eliminación',
                        text: `¿Eliminar los datos correspondientes a ${date}?`,
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonText: 'Sí, eliminar',
                        cancelButtonText: 'Cancelar',
                    });

                    if (!confirmation.isConfirmed) {
                        return;
                    }

                    const data = await deleteDate(date);
                    await loadTransactions(date);
                    Swal.fire('Listo', data.message || 'Datos eliminados correctamente.', 'success');
                } catch (error) {
                    Swal.fire('Error', error.message, 'error');
                }
            });

            document.getElementById('btnGuardarDataFecha').addEventListener('click', async event => {
                const button = event.currentTarget;

                try {
                    const dates = rangeDates();
                    button.disabled = true;
                    Swal.fire({title: 'Guardando información...', html: `0 / ${dates.length}`, allowOutsideClick: false, didOpen: () => Swal.showLoading()});

                    for (let index = 0; index < dates.length; index++) {
                        Swal.update({html: `Procesando ${dates[index]} (${index + 1} / ${dates.length})`});
                        await syncDate(dates[index]);
                    }

                    bootstrap.Modal.getOrCreateInstance(document.getElementById('modalRangoDsVirtual')).hide();
                    Swal.fire('Listo', `${dates.length} fecha(s) procesada(s) correctamente.`, 'success');
                } catch (error) {
                    Swal.fire('Error', error.message, 'error');
                } finally {
                    button.disabled = false;
                }
            });

            document.getElementById('btnEliminarDataFecha').addEventListener('click', async event => {
                const button = event.currentTarget;

                try {
                    const dates = rangeDates();
                    const confirmation = await Swal.fire({
                        title: 'Confirmar eliminación',
                        text: `¿Eliminar los datos de ${dates.length} fecha(s)?`,
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonText: 'Sí, eliminar',
                        cancelButtonText: 'Cancelar',
                    });

                    if (!confirmation.isConfirmed) {
                        return;
                    }

                    button.disabled = true;
                    Swal.fire({title: 'Eliminando información...', html: `0 / ${dates.length}`, allowOutsideClick: false, didOpen: () => Swal.showLoading()});

                    for (let index = 0; index < dates.length; index++) {
                        Swal.update({html: `Eliminando ${dates[index]} (${index + 1} / ${dates.length})`});
                        await deleteDate(dates[index]);
                    }

                    bootstrap.Modal.getOrCreateInstance(document.getElementById('modalRangoDsVirtual')).hide();
                    Swal.fire('Listo', `${dates.length} fecha(s) eliminada(s) correctamente.`, 'success');
                } catch (error) {
                    Swal.fire('Error', error.message, 'error');
                } finally {
                    button.disabled = false;
                }
            });
        });
    </script>
@endsection
