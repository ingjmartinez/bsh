@extends('app')

@section('content')
    <!-- ============================================================== -->
    <!-- Start right Content here -->
    <!-- ============================================================== -->
    <div class="main-content">

        <div class="page-content">
            <div class="container-fluid">

                <!-- start page title -->
                <div class="row">
                    <div class="col-12">
                        <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                            <h4 class="mb-sm-0">Business Support Hub</h4>
                        </div>
                    </div>
                </div>
                <!-- end page title -->

                <div class="row">
                    <div class="col-lg-12">
                        <div class="card">
                            <div class="card-header">
                                <div class="d-flex justify-content-between align-items-center">
                                    <h5 class="card-title mb-0">Cuentas</h5>
                                    <div>
                                        <button type="button" class="btn btn-info me-2" id="btnSincronizarCuentas">
                                            Sincronizar
                                        </button>
                                        <button type="button" class="btn btn-success" id="btnNuevaCuenta">
                                            Nueva cuenta
                                        </button>
                                    </div>
                                </div>
                            </div>
                            <div class="card-body">
                                <table id="tableCuentas"
                                    class="table table-bordered dt-responsive nowrap table-striped align-middle"
                                    style="width:100%">
                                    <thead>
                                        <tr>
                                            <th>Cuenta</th>
                                            <th>DescripciÃ³n</th>
                                            <th>Cuenta Control</th>
                                            <th>Tipo</th>
                                            <th>Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody></tbody>
                                </table>
                            </div>
                        </div>
                    </div><!--end col-->

                    <div class="col-lg-12">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="card-title mb-0">Detalle de cuenta</h5>
                            </div>
                            <div class="card-body">
                                <table id="tableDetalleCuenta"
                                    class="table table-bordered dt-responsive nowrap table-striped align-middle"
                                    style="width:100%">
                                    <thead>
                                        <tr>
                                            <th>Nro. Asiento</th>
                                            <th>Fecha</th>
                                            <th>Referencia</th>
                                            <th>Referencia No.</th>
                                            <th>DÃ©bito</th>
                                            <th>CrÃ©dito</th>
                                            <th>DescripciÃ³n</th>
                                            <th>Grupo</th>
                                            <th>Subgrupo</th>
                                            <th>DivisiÃ³n</th>
                                            <th>Centro de Costo</th>
                                            <th>Conciliado</th>
                                            <th>MÃ³dulo</th>
                                            <th>Fecha Grabado</th>
                                            <th>Fecha Modificado</th>
                                            <th>Creado Por</th>
                                            <th>Modificado Por</th>
                                            <th>Referencia DescripciÃ³n</th>
                                            <th>Sociedad</th>
                                        </tr>
                                    </thead>
                                    <tbody></tbody>
                                </table>
                            </div>
                        </div>
                    </div><!--end col-->
                </div><!--end row-->
            </div>
            <!-- container-fluid -->
        </div>
        <!-- End Page-content -->

        <footer class="footer">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-sm-6">
                        <script>
                            document.write(new Date().getFullYear())
                        </script> Â© Velzon.
                    </div>
                    <div class="col-sm-6">
                        <div class="text-sm-end d-none d-sm-block">
                            Design & Develop by Themesbrand
                        </div>
                    </div>
                </div>
            </div>
        </footer>
    </div>
    <!-- end main content-->


    <div id="myModal" class="modal fade" tabindex="-1" aria-labelledby="myModalLabel" aria-hidden="true"
        style="display: none;">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="myModalLabel">Modal Heading</h5>
                    <button type="button" id="btnClose" class="btn-close" data-bs-dismiss="modal"
                        aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="cuenta" class="form-label">Cuenta</label>
                        <input type="text" class="form-control" id="cuenta" placeholder="Cuenta" readonly>
                    </div>
                    <div class="mb-3">
                        <label for="fechaInicio" class="form-label">Fecha inicio</label>
                        <input type="date" class="form-control" id="fechaInicio" value="<?php echo date('Y-m-d'); ?>">
                    </div>
                    <div class="mb-3">
                        <label for="fechaFin" class="form-label">Fecha fin</label>
                        <input type="date" class="form-control" id="fechaFin" value="<?php echo date('Y-m-d'); ?>">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-primary" id="btnEntradas">Consultar Entradas</button>
                </div>

            </div><!-- /.modal-content -->
        </div><!-- /.modal-dialog -->
    </div><!-- /.modal -->

    <div id="cuentaModal" class="modal fade" tabindex="-1" aria-labelledby="cuentaModalLabel" aria-hidden="true"
        style="display: none;">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="cuentaModalLabel">Nueva cuenta</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="cuentaId">
                    <div class="mb-3">
                        <label for="formCuenta" class="form-label">Cuenta</label>
                        <input type="text" class="form-control" id="formCuenta" placeholder="Cuenta">
                    </div>
                    <div class="mb-3">
                        <label for="formDescripcion" class="form-label">DescripciÃ³n</label>
                        <input type="text" class="form-control" id="formDescripcion" placeholder="DescripciÃ³n">
                    </div>
                    <div class="mb-3">
                        <label for="formCtaControl" class="form-label">Cuenta Control</label>
                        <input type="text" class="form-control" id="formCtaControl" placeholder="Cuenta Control">
                    </div>
                    <div class="mb-3">
                        <label for="formTipo" class="form-label">Tipo</label>
                        <input type="text" class="form-control" id="formTipo" placeholder="Tipo">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cerrar</button>
                    <button type="button" class="btn btn-primary" id="btnGuardarCuenta">Guardar</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('script')
    <script>
        const csrfToken = '{{ csrf_token() }}';
        const cuentaModalElement = document.getElementById('cuentaModal');
        const cuentaModal = new bootstrap.Modal(cuentaModalElement);

        document.getElementById('btnNuevaCuenta').addEventListener('click', abrirModalNuevaCuenta);
        document.getElementById('btnGuardarCuenta').addEventListener('click', guardarCuenta);
        document.getElementById('btnSincronizarCuentas').addEventListener('click', sincronizarCuentas);

        cargarCuentas();

        function cargarCuentas() {
            fetch('/api-cuentas')
                .then(response => response.json())
                .then(data => {
                    const tableBody = document.querySelector('#tableCuentas tbody');
                    tableBody.innerHTML = '';

                    data.forEach(item => {
                        const row = document.createElement('tr');
                        row.innerHTML = `
                            <td>${item.CUENTA ?? ''}</td>
                            <td>${item.DESCRIPCION ?? ''}</td>
                            <td>${item.CTACONTROL ?? ''}</td>
                            <td>${item.TIPO ?? ''}</td>
                            <td>
                                <button class="btn btn-primary btn-sm me-1" onclick="verEntradasCuenta('${item.CUENTA}')">Ver Entradas</button>
                                <button class="btn btn-warning btn-sm me-1" onclick="editarCuenta(${item.id}, '${escapeJs(item.CUENTA)}', '${escapeJs(item.DESCRIPCION)}', '${escapeJs(item.CTACONTROL)}', '${escapeJs(item.TIPO)}')">Editar</button>
                                <button class="btn btn-danger btn-sm" onclick="eliminarCuenta(${item.id})">Eliminar</button>
                            </td>
                        `;
                        tableBody.appendChild(row);
                    });

                    if ($.fn.DataTable.isDataTable('#tableCuentas')) {
                        $('#tableCuentas').DataTable().destroy();
                    }

                    $('#tableCuentas').DataTable({
                        responsive: true,
                        scrollX: true,
                        columnDefs: [
                            { targets: [2, 3], visible: $(window).width() > 768 }
                        ],
                        dom: 'Bfrtip',
                        buttons: ['copy', 'csv', 'excel', 'pdf', 'print']
                    });
                })
                .catch(error => {
                    console.error('Error fetching data:', error);
                    alert('No se pudieron cargar las cuentas.');
                });
        }

        function abrirModalNuevaCuenta() {
            document.getElementById('cuentaModalLabel').innerText = 'Nueva cuenta';
            document.getElementById('cuentaId').value = '';
            document.getElementById('formCuenta').value = '';
            document.getElementById('formDescripcion').value = '';
            document.getElementById('formCtaControl').value = '';
            document.getElementById('formTipo').value = '';
            cuentaModal.show();
        }

        function editarCuenta(id, cuenta, descripcion, ctacontrol, tipo) {
            document.getElementById('cuentaModalLabel').innerText = 'Editar cuenta';
            document.getElementById('cuentaId').value = id;
            document.getElementById('formCuenta').value = cuenta || '';
            document.getElementById('formDescripcion').value = descripcion || '';
            document.getElementById('formCtaControl').value = ctacontrol || '';
            document.getElementById('formTipo').value = tipo || '';
            cuentaModal.show();
        }

        function guardarCuenta() {
            const id = document.getElementById('cuentaId').value;
            const payload = {
                cuenta: document.getElementById('formCuenta').value.trim(),
                descripcion: document.getElementById('formDescripcion').value.trim(),
                ctacontrol: document.getElementById('formCtaControl').value.trim(),
                tipo: document.getElementById('formTipo').value.trim(),
            };

            if (!payload.cuenta || !payload.descripcion) {
                alert('Cuenta y descripciÃ³n son obligatorias.');
                return;
            }

            const url = id ? `/api-cuentas/${id}` : '/api-cuentas';
            const method = id ? 'PUT' : 'POST';

            fetch(url, {
                    method,
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify(payload),
                })
                .then(async response => {
                    const data = await response.json().catch(() => ({}));
                    if (!response.ok) {
                        const errorText = data.message || 'No se pudo guardar la cuenta.';
                        throw new Error(errorText);
                    }
                    return data;
                })
                .then(() => {
                    cuentaModal.hide();
                    cargarCuentas();
                })
                .catch(error => {
                    alert(error.message || 'Error guardando la cuenta.');
                });
        }

        function sincronizarCuentas() {
            if (!confirm('Â¿Deseas sincronizar las cuentas desde el API externo?')) {
                return;
            }

            const boton = document.getElementById('btnSincronizarCuentas');
            const textoOriginal = boton.innerText;
            boton.disabled = true;
            boton.innerText = 'Sincronizando...';

            fetch('/api-cuentas/sync', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json',
                    },
                })
                .then(async response => {
                    const data = await response.json().catch(() => ({}));

                    if (!response.ok) {
                        throw new Error(data.message || 'No se pudo sincronizar.');
                    }

                    alert(
                        `SincronizaciÃ³n completada.\nRecibidas: ${data.total_recibidas ?? 0}\nCreadas: ${data.creadas ?? 0}\nActualizadas: ${data.actualizadas ?? 0}\nOmitidas: ${data.omitidas ?? 0}`
                    );

                    cargarCuentas();
                })
                .catch(error => {
                    alert(error.message || 'Error sincronizando cuentas.');
                })
                .finally(() => {
                    boton.disabled = false;
                    boton.innerText = textoOriginal;
                });
        }

        function eliminarCuenta(id) {
            if (!confirm('Â¿Seguro que desea eliminar esta cuenta?')) {
                return;
            }

            fetch(`/api-cuentas/${id}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json',
                    },
                })
                .then(async response => {
                    const data = await response.json().catch(() => ({}));
                    if (!response.ok) {
                        const errorText = data.message || 'No se pudo eliminar la cuenta.';
                        throw new Error(errorText);
                    }
                    return data;
                })
                .then(() => {
                    cargarCuentas();
                })
                .catch(error => {
                    alert(error.message || 'Error eliminando la cuenta.');
                });
        }

        function escapeJs(value) {
            return String(value ?? '')
                .replace(/\\/g, '\\\\')
                .replace(/'/g, "\\'")
                .replace(/\"/g, '&quot;');
        }

        function verEntradasCuenta(cuenta) {
            setCuenta(cuenta);
            const modal = new bootstrap.Modal(document.getElementById('myModal'));
            modal.show();
        }

        function setCuenta(cuenta) {
            document.getElementById('cuenta').value = cuenta;
        }

        document.getElementById('btnEntradas').addEventListener('click', verDetalle);

        function verDetalle() {
            let cuenta = document.getElementById('cuenta').value;
            let fechaInicio = document.getElementById('fechaInicio').value;
            let fechaFin = document.getElementById('fechaFin').value;

            if (!cuenta) {
                alert("Por favor, seleccione una cuenta.");
                return;
            }

            if (!fechaInicio || !fechaFin) {
                alert("Por favor, seleccione el rango de fechas.");
                return;
            }

            if (fechaInicio > fechaFin) {
                alert("La fecha inicio no puede ser mayor que la fecha fin.");
                return;
            }

            fetch("/api-entradas?fecha_inicio=" + encodeURIComponent(fechaInicio) + "&fecha_fin=" + encodeURIComponent(fechaFin) + "&cuenta=" + encodeURIComponent(cuenta))
                .then(async response => {
                    let data;

                    // Intentamos detectar si la respuesta es JSON o texto
                    const contentType = response.headers.get("content-type");

                    if (contentType && contentType.includes("application/json")) {
                        data = await response.json();
                    } else {
                        const text = await response.text();
                        // Intentamos parsear por si el servidor no envÃ­a el header correcto
                        try {
                            data = JSON.parse(text);
                        } catch {
                            // No es JSON, es texto plano
                            alert(text || "No hay nada encontrado");
                            return; // detenemos aquÃ­
                        }
                    }

                    // Si llega aquÃ­, es un JSON vÃ¡lido
                    if (!data.result || !data.result.Det) {
                        alert("No hay datos encontrados en la respuesta");
                        return;
                    }

                    const tableBody = document.querySelector('#tableDetalleCuenta tbody');
                    tableBody.innerHTML = '';

                    data.result.Det.forEach(item => {
                        const row = document.createElement('tr');
                        row.innerHTML = `
                            <td>${item.NoAsiento}</td>
                            <td>${item.Fecha}</td>
                            <td>${item.Ref}</td>
                            <td>${item.NoRef}</td>
                            <td>${item.Debito}</td>
                            <td>${item.Credito}</td>
                            <td>${item.Descripcion}</td>
                            <td>${item.Grupo}</td>
                            <td>${item.SubGrupo}</td>
                            <td>${item.Division}</td>
                            <td>${item.CentroCosto}</td>
                            <td>${item.Conciliado}</td>
                            <td>${item.Modulo}</td>
                            <td>${item.FechaGrabado}</td>
                            <td>${item.CreadoPor}</td>
                            <td>${item.FechaModificado}</td>
                            <td>${item.ModificadoPor}</td>
                            <td>${item.RefDesc}</td>
                            <td>${item.Sociedad}</td>
                        `;
                        tableBody.appendChild(row);
                    });

                    document.getElementById('btnClose').click();

                    $('#tableDetalleCuenta').DataTable({
                        destroy: true,
                        responsive: true,
                        scrollX: true,
                        columnDefs: [
                            { targets: [3, 4, 5, 6, 7, 8, 9, 10, 11], visible: $(window).width() > 992 }
                        ],
                        dom: 'Bfrtip',
                        buttons: ['copy', 'csv', 'excel', 'pdf', 'print']
                    });
                })
                .catch(error => {
                    alert("Error en la peticiÃ³n: " + error);
                });

        }
    </script>
@endsection

