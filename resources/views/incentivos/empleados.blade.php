@extends('app')

@section('content')
    <div class="main-content">
        <div class="page-content">
            <div class="container-fluid">
                <!-- start page title -->
                <div class="row">
                    <div class="col-12">
                        <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                            <h4 class="mb-sm-0">Control de Incentivos de Empleados</h4>

                            <div class="page-title-right">
                                <ol class="breadcrumb m-0">
                                    <li class="breadcrumb-item"><a href="{{ route('inicio.index') }}">Inicio</a></li>
                                    <li class="breadcrumb-item"><a href="{{ route('incentivos.index') }}">Incentivos</a></li>
                                    <li class="breadcrumb-item active">Incentivos Empleados</li>
                                </ol>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- end page title -->

                <div class="row">
                    <div class="col-lg-12">
                        <div class="card">
                            <div class="card-header d-flex align-items-center justify-content-between">
                                <h5 class="card-title mb-0">Control de Incentivos de Empleados</h5>
                            </div>
                            <div class="card-body">
                                <table id="tableEmpleados"
                                    class="table table-bordered dt-responsive nowrap table-striped align-middle"
                                    style="width:100%">
                                    <thead>
                                        <tr>
                                            <th>Empresa</th>
                                            <th>Id Empleado</th>
                                            <th>Nombres</th>
                                            <th>Apellidos</th>
                                            <th>Cedula</th>
                                            <th>Departamento</th>
                                            <th>Aplica Incentivo</th>
                                            <th>Porcentaje</th>
                                            <th></th>
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
    <!-- end main content-->

    <div id="myModal" class="modal fade" tabindex="-1" aria-labelledby="myModalLabel" aria-hidden="true"
        style="display: none;">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="myModalLabel">Formulario</h5>
                    <button type="button" id="btnClose" class="btn-close" data-bs-dismiss="modal"
                        aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="codigo" readonly>
                    <div class="mb-3">
                        <label for="tipo" class="form-label">Tipo de Empleado</label>
                        <select id="tipo" class="form-select">
                            <option value="">Seleccione</option>
                            <option value="1">Agente de Venta</option>
                            <option value="2">Coordinador</option>
                            <option value="3">Administrativo</option>
                            <option value="4">Operador</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="aplica" class="form-label">Aplica Incentivo</label>
                        <select id="aplica" class="form-select">
                            <option value="SI">SI</option>
                            <option value="NO">NO</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="porcentaje" class="form-label">Porcentaje</label>
                        <input type="text" class="form-control" id="porcentaje">
                    </div>
                    <div class="listAgencias d-none">
                        <label for="agencias" class="form-label">Agencias</label>
                        <label for="agencias" class="form-label">Total Agencias: <span id="totalAgencias"></span></label>
                        <textarea id="agencias" class="form-control" rows="5"></textarea>
                        <div id="agenciasNoValidas"></div>
                    </div>
                    <div class="listAgencias d-none">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cerrar</button>
                    <button type="button" class="btn btn-primary" id="btnGuardar">Guardar Cambios</button>
                </div>
            </div><!-- /.modal-content -->
        </div><!-- /.modal-dialog -->
    </div><!-- /.modal -->

    <input type="hidden" id="agenciasJson" value="{{ $agencias }}">
@endsection

@section('script')
    <script>
        let AGENCIAS = JSON.parse(agenciasJson.value).map(String);

        document.querySelector('#tipo').addEventListener('change', function() {
            let tipo = this.value;
            let listAgencias = document.querySelector('.listAgencias');
            if (tipo == 2 || tipo == 4) {
                listAgencias.classList.remove('d-none');
            } else {
                listAgencias.classList.add('d-none');
            }
        });

        function validarAgencias(textarea) {
            let agenciasInput = textarea.value.replace(/[^0-9\n]/g, '').split('\n').map(l => l.trim()).filter(l => l !== '');
            let agenciasNoValidas = agenciasInput.filter(ag => !AGENCIAS.includes(ag));
            let validAgencias = [...new Set(agenciasInput.filter(ag => AGENCIAS.includes(ag)))];

            let agenciasNoValidasDiv = document.querySelector('#agenciasNoValidas');
            if (agenciasNoValidas.length > 0) {
                agenciasNoValidasDiv.innerHTML =
                    `<p class="text-danger">Agencias no válidas: ${agenciasNoValidas.join(', ')}</p>`;
            } else {
                agenciasNoValidasDiv.innerHTML = '';
            }

            document.querySelector('#totalAgencias').innerText = validAgencias.length;
            return validAgencias;
        }

        // Mientras escribe: solo mostrar feedback, NO reasignar el valor
        document.querySelector('#agencias').addEventListener('input', function() {
            validarAgencias(this);
        });

        // Al perder el foco: limpiar y dejar solo agencias válidas
        document.querySelector('#agencias').addEventListener('blur', function() {
            let validAgencias = validarAgencias(this);
            this.value = validAgencias.join('\n');
        });

        document.querySelector('#agencias').addEventListener('paste', function(e) {
            e.preventDefault();
            let pasteData = (e.clipboardData || window.clipboardData).getData('text');
            // Filtrar solo numeros y saltos de linea
            pasteData = pasteData.replace(/[^0-9\n]/g, '');

            // Preparar listas
            let existingAgencias = this.value.split('\n').map(line => line.trim()).filter(l => l !== '');
            let newAgencias = pasteData.split('\n').map(line => line.trim()).filter(l => l !== '');
            // Solo nuevas que no estén ya en existing
            let uniqueAgencias = newAgencias.filter(ag => ag !== '' && !existingAgencias.includes(ag));

            // Separar válidas e inválidas
            let agenciasNoValidas = uniqueAgencias.filter(ag => !AGENCIAS.includes(ag));
            let validUniqueAgencias = uniqueAgencias.filter(ag => AGENCIAS.includes(ag));

            // Mostrar agencias no validas
            let agenciasNoValidasDiv = document.querySelector('#agenciasNoValidas');
            if (agenciasNoValidas.length > 0) {
                agenciasNoValidasDiv.innerHTML =
                    `<p class="text-danger">Agencias no válidas: ${agenciasNoValidas.join(', ')}</p>`;
            } else {
                agenciasNoValidasDiv.innerHTML = '';
            }

            // Combinar existing + validUniqueAgencias y eliminar duplicados
            let combined = existingAgencias.concat(validUniqueAgencias);
            combined = combined.filter(l => l !== '');
            combined = [...new Set(combined)];

            // Actualizar textarea y total
            this.value = combined.join('\n');
            document.querySelector('#totalAgencias').innerText = combined.length;
        });

        var modal = new bootstrap.Modal(document.getElementById('myModal'));

        document.querySelector("#btnGuardar").addEventListener("click", function() {
            Swal.fire({
                title: "Guardndo Información ...",
                icon: 'info',
                allowOutsideClick: false,
                showConfirmButton: false,
                timerProgressBar: true,
                didOpen: () => Swal.showLoading()
            });
            let id = document.getElementById('codigo').value;
            let aplica = document.getElementById('aplica').value;
            let porcentaje = document.getElementById('porcentaje').value;
            let tipo = document.getElementById('tipo').value;
            let agencias = document.getElementById('agencias').value.split('\n')
                .map(l => l.trim()).filter(l => l !== '').join(',');

            if (tipo == 2 || tipo == 4) {
                if (agencias.length == 0) {
                    Swal.fire({
                        title: "Error",
                        text: "Debe ingresar al menos una agencia válida para el tipo de empleado seleccionado.",
                        icon: "warning"
                    });
                    return;
                }
            }
            fetch("/incentivos/empleados/update", {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/json",
                        "X-CSRF-TOKEN": "{{ csrf_token() }}"
                    },
                    body: JSON.stringify({
                        id,
                        aplica,
                        porcentaje,
                        tipo,
                        agencias
                    })
                })
                .then(response => response.json())
                .then(data => {
                    modal.hide();
                    Swal.fire({
                        title: "Listo",
                        text: data.message,
                        icon: "success"
                    });
                    setTimeout(() => {
                        list();
                    }, 1000);
                })
                .catch(error => {
                    Swal.fire({
                        title: "Error",
                        text: error,
                        icon: "warning"
                    });
                });
        });

        function list() {
            Swal.fire({
                title: "Procesando Información ...",
                icon: 'info',
                allowOutsideClick: false,
                showConfirmButton: false,
                timerProgressBar: true,
                didOpen: () => Swal.showLoading()
            });
            $('#tableEmpleados').DataTable().destroy();
            $('#tableEmpleados tbody').empty();
            fetch("/incentivos/empleados/list")
                .then(response => response.json())
                .then(data => {
                    const tableBody = document.querySelector('#tableEmpleados tbody');
                    tableBody.innerHTML = ''; // Limpiar filas existentes

                    data.forEach(item => {
                        const row = document.createElement('tr');
                        row.innerHTML = `
                            <td>${item.company}</td>
                            <td>${item.empleadoid}</td>
                            <td>${item.nombres}</td>
                            <td>${item.apellidos}</td>
                            <td>${item.cedula}</td>
                            <td>${item.depto}</td>
                            <td>${item.aplica_incentivo}</td>
                            <td>${item.porcentaje_incentivo}</td>
                            <td>
                                <button onclick="detalle('${item.id}')"" class='btn btn-primary'>Ver</button>
                            </td>
                        `;
                        tableBody.appendChild(row);
                    });

                    $('#tableEmpleados').DataTable({
                        responsive: true,
                        scrollX: true,
                        columnDefs: [
                            { targets: [3, 4, 5], visible: $(window).width() > 768 }
                        ],
                        dom: 'Bfrtip',
                        buttons: [
                            'copy', 'csv', 'excel', 'pdf', 'print'
                        ]
                    });

                    Swal.close();
                })
                .catch(error => console.error('Error fetching data:', error));
        }

        // Cargar la lista al cargar la página
        document.addEventListener('DOMContentLoaded', function() {
            list();
        });

        function detalle(codigo) {
            modal.show();

            Swal.fire({
                title: "Procesando Información ...",
                icon: 'info',
                allowOutsideClick: false,
                showConfirmButton: false,
                timerProgressBar: true,
                didOpen: () => Swal.showLoading()
            });

            fetch("/empleados/show/" + codigo)
                .then(response => response.json())
                .then(data => {
                    if ('message' in data) {
                        Swal.fire({
                            title: "Información",
                            text: data.message,
                            icon: "warning"
                        });
                        return;
                    }
                    document.querySelector('#codigo').value = data.id;
                    document.querySelector('#aplica').value = data.aplica_incentivo;
                    document.querySelector('#porcentaje').value = data.porcentaje_incentivo;
                    document.querySelector('#tipo').value = data.tipo_empleado_incentivo;                    
                    document.querySelector('#tipo').dispatchEvent(new Event('change'));
                    document.querySelector('#agencias').value = data.agencias ? data.agencias.split(',').join('\n') : '';
                    let agenciasTextarea = document.querySelector('#agencias');
                    let validAgencias = validarAgencias(agenciasTextarea);
                    agenciasTextarea.value = validAgencias.join('\n');
                    Swal.close();
                })
                .catch(error => {
                    Swal.fire({
                        title: "Error",
                        text: error,
                        icon: "warning"
                    });
                });
        }
    </script>
@endsection

