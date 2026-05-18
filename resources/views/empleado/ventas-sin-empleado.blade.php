@extends('app')

@section('content')
    <div class="main-content">

        <div class="page-content">
            <div class="container-fluid">

                <!-- start page title -->
                <div class="row">
                    <div class="col-12">
                        <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                            <h4 class="mb-sm-0">Ventas Sin Empleado</h4>

                            <div class="page-title-right">
                                <ol class="breadcrumb m-0">
                                    <li class="breadcrumb-item"><a href="{{ route('inicio.index') }}">Inicio</a></li>
                                    <li class="breadcrumb-item"><a href="{{ route('recursos-humanos.index') }}">Recursos Humanos</a></li>
                                    <li class="breadcrumb-item active">Ventas Sin Empleado</li>
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
                                <h5 class="card-title mb-0">Ventas Sin Empleado</h5>

                                <button type="button" class="btn btn-primary" data-bs-toggle="modal" id="btnNuevo"
                                    data-bs-target="#myModal">
                                    Nuevo Empleado
                                </button>
                            </div>
                            <div class="card-body">
                                <table id="tableEmpleados"
                                    class="table table-bordered dt-responsive nowrap table-striped align-middle"
                                    style="width:100%">
                                    <thead>
                                        <tr>
                                            <th>Consorcio</th>
                                            <th>Agencia</th>
                                            <th>Cedula</th>
                                            <th>Tipo</th>
                                            <th>Producto</th>
                                            <th>Monto</th>
                                            <th>Fecha</th>
                                            <th>Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($ventas as $u)
                                            <tr>
                                                <td>{{ $u->consorcio_id }}</td>
                                                <td>{{ $u->agencia_id }}</td>
                                                <td>{{ $u->cedula }}</td>
                                                <td>{{ $u->tipo }}</td>
                                                <td>{{ $u->producto_id }}</td>
                                                <td>{{ $u->monto }}</td>
                                                <td>{{ $u->fecha }}</td>
                                                <td>{{ $u->origen }}</td>
                                                <td>
                                                    <button class="btn btn-info" data-bs-toggle="modal"
                                                        data-bs-target="#myModal"
                                                        onclick="editar('{{ $u->cedula }}')">Editar</button>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                                {{ $ventas->links() }} <!-- ðŸ”¥ Esto genera los botones de paginaciÃ³n -->
                            </div>
                        </div>
                    </div>
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
                    <h5 class="modal-title" id="myModalLabel">Formulario</h5>
                    <button type="button" id="btnClose" class="btn-close" data-bs-dismiss="modal"
                        aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="codigo" readonly>
                    <div class="mb-3">
                        <label for="id_empleado" class="form-label">Id Empleado</label>
                        <input type="text" class="form-control" id="id_empleado">
                    </div>
                    <div class="mb-3">
                        <label for="nombre" class="form-label">Nombre</label>
                        <input type="text" class="form-control" id="nombre">
                    </div>
                    <div class="mb-3">
                        <label for="cedula" class="form-label">Cedula</label>
                        <input type="text" class="form-control" id="cedula" maxlength="11">
                    </div>
                    <div class="mb-3">
                        <label for="estado" class="form-label">Estado</label>
                        <select id="estado"class="form-select">
                            <option value="1">Activo</option>
                            <option value="0">Inactivo</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-primary" id="btnGuardar">Registrar</button>
                </div>

            </div><!-- /.modal-content -->
        </div><!-- /.modal-dialog -->
    </div><!-- /.modal -->
@endsection

@section('script')
    <script>
        document.querySelector("#btnNuevo").addEventListener("click", function() {
            document.getElementById('codigo').value = '';
            document.getElementById('id_empleado').value = '';
            document.getElementById('nombre').value = '';
            document.getElementById('cedula').value = '';
            document.getElementById('estado').value = '1';
        });

        function list() {
            const tableBody = document.querySelector('#tableEmpleados tbody');
            tableBody.innerHTML = `<td colspan=10>Cargando InformaciÃ³n ...</td>`; // Limpiar filas existentes

            fetch("/ventas-sin-empleado/list")
                .then(response => response.json())
                .then(data => {
                    tableBody.innerHTML = ``;
                    data.forEach(item => {
                        const row = document.createElement('tr');
                        row.innerHTML = `
                            <td>${item.consorcio_id}</td>
                            <td>${item.agencia_id}</td>
                            <td>${item.cedula}</td>
                            <td>${item.tipo}</td>
                            <td>${item.producto_id}</td>
                            <td>${item.monto}</td>
                            <td>${item.fecha}</td>
                            <td>${item.descripcion}</td>
                            <td>
                                <button class="btn btn-info" data-bs-toggle="modal" data-bs-target="#myModal" 
                                    onclick="editar('${item.cedula}')">Editar</button>
                            </td>
                        `;
                        tableBody.appendChild(row);
                    });

                    $('#tableEmpleados').DataTable({
                        responsive: true,
                        dom: 'Bfrtip',
                        buttons: [
                            'copy', 'csv', 'excel', 'pdf', 'print'
                        ]
                    });
                })
                .catch(error => console.error('Error fetching data:', error));
        }

        function editar(cedula) {
            document.getElementById('cedula').value = cedula;
        }

        document.getElementById('btnGuardar').addEventListener('click', function() {
            const codigo = document.getElementById('codigo').value;
            const id_empleado = document.getElementById('id_empleado').value;
            const nombre = document.getElementById('nombre').value;
            const cedula = document.getElementById('cedula').value;
            const estado = document.getElementById('estado').value;

            if (cedula.length < 11 || cedula.length > 11) {
                Swal.fire({
                    title: "Error",
                    text: "La cÃ©dula debe tener 11 dÃ­gitos",
                    icon: "error"
                });
                return;
            }

            if (!/^\d{11}$/.test(cedula)) {
                Swal.fire({
                    title: "Error",
                    text: "La cÃ©dula debe contener solo nÃºmeros",
                    icon: "error"
                });
                return;
            }

            if (id_empleado.trim() === '' || nombre.trim() === '' || cedula.trim() === '') {
                Swal.fire({
                    title: "Error",
                    text: "Por favor, complete todos los campos obligatorios.",
                    icon: "error"
                });
                return;
            }

            const payload = {
                codigo,
                id_empleado,
                nombre,
                cedula,
                estado
            };

            let url = '/empleados/store';
            let method = 'POST';

            Swal.fire({
                title: "Registrando ...",
                icon: 'info',
                allowOutsideClick: false,
                showConfirmButton: false,
                timerProgressBar: true,
                didOpen: () => Swal.showLoading()
            });

            fetch(url, {
                    method,
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify(payload)
                })
                .then(response => response.json())
                .then(data => {
                    Swal.fire({
                        title: "Listo",
                        text: "Registrado con Ã©xito",
                        icon: "success"
                    });
                    // Cerrar el modal
                    document.getElementById('btnClose').click();
                    // Recargar la lista
                    $('#tableEmpleados').DataTable().destroy();
                    list();
                })
                .catch(error => console.error('Error saving data:', error));
        });
    </script>
@endsection

