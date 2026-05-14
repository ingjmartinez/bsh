@extends('app')

@section('content')
    <div class="main-content">
        <div class="page-content">
            <div class="container-fluid">
                <!-- start page title -->
                <div class="row">
                    <div class="col-12">
                        <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                            <h4 class="mb-sm-0">Incentivos</h4>

                            <div class="page-title-right">
                                <ol class="breadcrumb m-0">
                                    <li class="breadcrumb-item"><a href="{{ route('inicio.index') }}">Inicio</a></li>
                                    <li class="breadcrumb-item"><a href="{{ route('incentivos.index') }}">Incentivos</a></li>
                                    <li class="breadcrumb-item active">Gestion Incentivos</li>
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
                                <h5 class="card-title mb-0">Incentivos</h5>

                                <div class="d-flex gap-3 align-items-center justify-content-between">
                                    <div><label class="mb-0" for="year">Año</label></div>
                                    <div>
                                        <select id="year" class="form-select">
                                            <option value="2026">2026</option>
                                            <option value="2025">2025</option>
                                            <option value="2024">2024</option>
                                            <option value="2023">2023</option>
                                            <option value="2022">2022</option>
                                            <option value="2021">2021</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="d-flex gap-3 align-items-center justify-content-between">
                                    <button type="button" class="btn btn-primary" id="btnExcluir">
                                        Excluir Productos
                                    </button>

                                    <div><label class="mb-0" for="mes">Mes</label></div>
                                    <div>
                                        <select id="mes" class="form-select">
                                            <option value="1">Enero</option>
                                            <option value="2">Febrero</option>
                                            <option value="3">Marzo</option>
                                            <option value="4">Abril</option>
                                            <option value="5">Mayo</option>
                                            <option value="6">Junio</option>
                                            <option value="7">Julio</option>
                                            <option value="8">Agosto</option>
                                            <option value="9">Septiembre</option>
                                            <option value="10">Octubre</option>
                                            <option value="11">Noviembre</option>
                                            <option value="12">Diciembre</option>
                                        </select>
                                    </div>

                                    <button type="button" class="btn btn-primary" id="btnGenerar">
                                        Genarar Incentivos
                                    </button>

                                    <button type="button" class="btn btn-success" id="btnGuardar">
                                        Guardar Incentivos
                                    </button>
                                </div>
                            </div>
                            <div class="card-body">
                                <table id="tableItems"
                                    class="table table-bordered dt-responsive nowrap table-striped align-middle"
                                    style="width:100%">
                                    <thead>
                                        <tr>
                                            <th>Agencia</th>
                                            <th>Tipo Producto</th>
                                            <th>Sistema</th>
                                            <th>Total T.</th>
                                            <th>Promedio M.</th>
                                            <th>Venta Base</th>
                                            <th>Total Mes</th>
                                            <th>Nivel</th>
                                            <th>Venta Incremental</th>
                                            <th>Meta Incremental</th>
                                            {{-- <th>Meta Plan</th> --}}
                                        </tr>
                                    </thead>
                                    <tbody></tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-12">
                        <div class="card">
                            <div class="card-header d-flex align-items-center justify-content-between">
                                <h5 class="card-title mb-0">Agencia Plan</h5>
                                <div class="d-flex gap-3 align-items-center justify-content-between">
                                    <div><label class="mb-0" for="mes_plan">Mes</label></div>
                                    <div>
                                        <select id="mes_plan" class="form-select">
                                            <option value="1">Enero</option>
                                            <option value="2">Febrero</option>
                                            <option value="3">Marzo</option>
                                            <option value="4">Abril</option>
                                            <option value="5">Mayo</option>
                                            <option value="6">Junio</option>
                                            <option value="7">Julio</option>
                                            <option value="8">Agosto</option>
                                            <option value="9">Septiembre</option>
                                            <option value="10">Octubre</option>
                                            <option value="11">Noviembre</option>
                                            <option value="12">Diciembre</option>
                                        </select>
                                    </div>

                                    <button type="button" class="btn btn-primary" id="btnGenerarDataPlan">
                                        Genarar Data
                                    </button>

                                    <button type="button" class="btn btn-success" id="btnGuardarPlanAgencia">
                                        Guardar Plan Agencia
                                    </button>
                                </div>
                            </div>
                            <div class="card-body">
                                <table id="tableAgenciaPlan"
                                    class="table table-bordered dt-responsive nowrap table-striped align-middle"
                                    style="width:100%">
                                    <thead>
                                        <tr>
                                            <th>Agencia</th>
                                            <th>Tipo Prodto</th>
                                            <th>Sistema</th>
                                            <th>V. Mes</th>
                                            <th>Meta Incrmntal</th>
                                            <th>Excedente</th>
                                            <th>% Agte</th>
                                            <th>% Cndor</th>
                                            <th>% Admin</th>
                                            <th>M. Agte</th>
                                            <th>M. Cndor</th>
                                            <th>M. Admin</th>
                                            <th>Total</th>
                                        </tr>
                                    </thead>
                                    <tbody></tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-12">
                        <div class="card">
                            <div class="card-header d-flex align-items-center justify-content-between">
                                <h5 class="card-title mb-0">Efectividad Por Usuario</h5>
                                <div class="d-flex gap-3 align-items-center justify-content-between">
                                    <div><label class="mb-0" for="mes_efectividad">Mes</label></div>
                                    <div>
                                        <select id="mes_efectividad" class="form-select">
                                            <option value="1">Enero</option>
                                            <option value="2">Febrero</option>
                                            <option value="3">Marzo</option>
                                            <option value="4">Abril</option>
                                            <option value="5">Mayo</option>
                                            <option value="6">Junio</option>
                                            <option value="7">Julio</option>
                                            <option value="8">Agosto</option>
                                            <option value="9">Septiembre</option>
                                            <option value="10">Octubre</option>
                                            <option value="11">Noviembre</option>
                                            <option value="12">Diciembre</option>
                                        </select>
                                    </div>

                                    <button type="button" class="btn btn-primary" id="btnGenerarDataEfectividad">
                                        Genarar Data
                                    </button>

                                    <button type="button" class="btn btn-success" id="btnGuardarEfectividad">
                                        Guardar Efectividad
                                    </button>
                                </div>
                            </div>
                            <div class="card-body">
                                <table id="tableEfectividad"
                                    class="table table-bordered dt-responsive nowrap table-striped align-middle"
                                    style="width:100%">
                                    <thead>
                                        <tr>
                                            <th>Agencia</th>
                                            <th>Sistema</th>
                                            <th>Tipo Producto</th>
                                            <th>Ventas Mes</th>
                                            <th>Cedula Bet</th>
                                            <th>Monto Cedula</th>
                                            <th>Porc Cedula</th>
                                            <th>Cedula Net</th>
                                            <th>Monto Cedula</th>
                                            <th>Porc Cedula</th>
                                        </tr>
                                    </thead>
                                    <tbody></tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-12">
                        <div class="card">
                            <div class="card-header d-flex align-items-center justify-content-between">
                                <h5 class="card-title mb-0">Pago Agentes de Venta</h5>
                                <div class="d-flex gap-3 align-items-center justify-content-between">
                                    <div><label class="mb-0" for="sistema">Sistema</label></div>
                                    <div>
                                        <select id="sistema" class="form-select">
                                            <option value="Lotobet">Lotobet</option>
                                            <option value="Lotonet">Lotonet</option>
                                        </select>
                                    </div>

                                    <div><label class="mb-0" for="mes_incentivo">Mes</label></div>
                                    <div>
                                        <select id="mes_incentivo" class="form-select">
                                            <option value="1">Enero</option>
                                            <option value="2">Febrero</option>
                                            <option value="3">Marzo</option>
                                            <option value="4">Abril</option>
                                            <option value="5">Mayo</option>
                                            <option value="6">Junio</option>
                                            <option value="7">Julio</option>
                                            <option value="8">Agosto</option>
                                            <option value="9">Septiembre</option>
                                            <option value="10">Octubre</option>
                                            <option value="11">Noviembre</option>
                                            <option value="12">Diciembre</option>
                                        </select>
                                    </div>

                                    <button type="button" class="btn btn-primary" id="btnGenerarDataPagoAgente">
                                        Genarar Data
                                    </button>

                                    <button type="button" class="btn btn-success" id="btnGuardarPagoAgente">
                                        Guardar Pago Incentivos
                                    </button>
                                </div>
                            </div>
                            <div class="card-body">
                                <table id="tablePagoAgente"
                                    class="table table-bordered dt-responsive nowrap table-striped align-middle"
                                    style="width:100%">
                                    <thead>
                                        <tr>
                                            <th>Agencia</th>
                                            <th>Tipo Producto</th>
                                            <th>Cedula</th>
                                            <th>% Cedula</th>
                                            <th>Total Agente</th>
                                            <th>Monto Incentivo</th>
                                        </tr>
                                    </thead>
                                    <tbody></tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-12">
                        <div class="card">
                            <div class="card-header d-flex align-items-center justify-content-between">
                                <h5 class="card-title mb-0">Pago Coordinadores</h5>
                                <div class="d-flex gap-3 align-items-center justify-content-between">
                                    {{-- <div><label class="mb-0" for="sistema">Sistema</label></div>
                                    <div>
                                        <select id="sistema" class="form-select">
                                            <option value="Lotobet">Lotobet</option>
                                            <option value="Lotonet">Lotonet</option>
                                        </select>
                                    </div> --}}

                                    <div><label class="mb-0" for="mes_coordinador">Mes</label></div>
                                    <div>
                                        <select id="mes_coordinador" class="form-select">
                                            <option value="1">Enero</option>
                                            <option value="2">Febrero</option>
                                            <option value="3">Marzo</option>
                                            <option value="4">Abril</option>
                                            <option value="5">Mayo</option>
                                            <option value="6">Junio</option>
                                            <option value="7">Julio</option>
                                            <option value="8">Agosto</option>
                                            <option value="9">Septiembre</option>
                                            <option value="10">Octubre</option>
                                            <option value="11">Noviembre</option>
                                            <option value="12">Diciembre</option>
                                        </select>
                                    </div>

                                    <button type="button" class="btn btn-primary" id="btnGenerarDataPagoCoordinador">
                                        Genarar Data
                                    </button>

                                    <button type="button" class="btn btn-success" id="btnGuardarPagoCoordinador">
                                        Guardar Pago Incentivos
                                    </button>
                                </div>
                            </div>
                            <div class="card-body">
                                <table id="tablePagoCoordinador"
                                    class="table table-bordered dt-responsive nowrap table-striped align-middle"
                                    style="width:100%">
                                    <thead>
                                        <tr>
                                            <th>Compania</th>
                                            <th>Cedula</th>
                                            <th>Nombres</th>
                                            <th>Apellidos</th>
                                            <th>Total</th>
                                            <th>Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody></tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-12">
                        <div class="card">
                            <div class="card-header d-flex align-items-center justify-content-between">
                                <h5 class="card-title mb-0">Pago Administradores</h5>
                                <div class="d-flex gap-3 align-items-center justify-content-between">
                                    {{-- <div><label class="mb-0" for="sistema_admin">Sistema</label></div>
                                    <div>
                                        <select id="sistema_admin" class="form-select">
                                            <option value="Lotobet">Lotobet</option>
                                            <option value="Lotonet">Lotonet</option>
                                        </select>
                                    </div> --}}

                                    <div><label class="mb-0" for="mes_admin">Mes</label></div>
                                    <div>
                                        <select id="mes_admin" class="form-select">
                                            <option value="1">Enero</option>
                                            <option value="2">Febrero</option>
                                            <option value="3">Marzo</option>
                                            <option value="4">Abril</option>
                                            <option value="5">Mayo</option>
                                            <option value="6">Junio</option>
                                            <option value="7">Julio</option>
                                            <option value="8">Agosto</option>
                                            <option value="9">Septiembre</option>
                                            <option value="10">Octubre</option>
                                            <option value="11">Noviembre</option>
                                            <option value="12">Diciembre</option>
                                        </select>
                                    </div>

                                    <button type="button" class="btn btn-primary" id="btnGenerarDataPagoAdmin">
                                        Genarar Data
                                    </button>

                                    <button type="button" class="btn btn-success" id="btnGuardarPagoAdmin">
                                        Guardar Pago Incentivos
                                    </button>
                                </div>
                            </div>
                            <div class="card-body">
                                <table id="tablePagoAdmin"
                                    class="table table-bordered dt-responsive nowrap table-striped align-middle"
                                    style="width:100%">
                                    <thead>
                                        <tr>
                                            <th>Empresa</th>
                                            <th>Emp ID</th>
                                            <th>Cedula</th>
                                            <th>Nombres</th>
                                            <th>Apellidos</th>
                                            <th>Porcentaje</th>
                                            <th>Tradicional</th>
                                            <th>No Tradicional</th>
                                            <th>Recargas</th>
                                            <th>Paquetico</th>
                                            <th>Total</th>
                                            <th>Acciones</th>
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

    <div id="myModal" class="modal fade" tabindex="-1" aria-labelledby="myModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl" style="max-width: 80%;">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="myModalLabel">Detalle de Pago Coordinador</h5>
                    <button type="button" id="btnClose" class="btn-close" data-bs-dismiss="modal"
                        aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="card-body">
                        <table id="tablePagoCoordinadorDetalle"
                            class="table table-bordered dt-responsive nowrap table-striped align-middle"
                            style="width:100%">
                            <thead>
                                <tr>
                                    <th>Agencia</th>
                                    <th>Tipo Prodto</th>
                                    <th>Sistema</th>
                                    <th>V. Mes</th>
                                    <th>V. Base</th>
                                    <th>Excdnte</th>
                                    <th>% Coordinador</th>
                                    <th>M. Coordinador</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                </div>
            </div><!-- /.modal-content -->
        </div><!-- /.modal-dialog -->
    </div><!-- /.modal -->

    <div id="myModalAdmin" class="modal fade" tabindex="-1" aria-labelledby="myModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl" style="max-width: 80%;">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="myModalLabel">Detalle de Pago Administrativo</h5>
                    <button type="button" id="btnClose" class="btn-close" data-bs-dismiss="modal"
                        aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="card-body">
                        <table id="tablePagoAdminDetalle"
                            class="table table-bordered dt-responsive nowrap table-striped align-middle"
                            style="width:100%">
                            <thead>
                                <tr>
                                    <th>Tipo Producto</th>
                                    <th>Total Tipo Producto</th>
                                    <th>Porcentaje Administrativo</th>
                                    <th>Total</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                </div>
            </div><!-- /.modal-content -->
        </div><!-- /.modal-dialog -->
    </div><!-- /.modal -->

    <div id="myModalExcluir" class="modal fade" tabindex="-1" aria-labelledby="myModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="myModalLabel">Excluir Productos</h5>
                    <button type="button" id="btnCloseExcluidos" class="btn-close"
                        data-bs-dismiss="modal"aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="card-body">
                        <table id="tableProductosExcluir"
                            class="table table-bordered dt-responsive nowrap table-striped align-middle"
                            style="width:100%">
                            <thead>
                                <tr>
                                    <th>
                                        {{-- Seleccionar Todos --}}
                                        <input type="checkbox" id="chkSeleccionarTodos">
                                    </th>
                                    <th>Producto ID</th>
                                    <th>Nombre Producto</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($productosExcluidos as $producto)
                                    <tr>
                                        <td>
                                            <input type="checkbox" class="chkProductoExcluir"
                                                data-producto-id="{{ $producto->producto_id }}">
                                        </td>
                                        <td>{{ $producto->producto_id }}</td>
                                        <td>{{ $producto->descripcion }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-primary" id="btnGenerarExcluir">
                            Genarar Incentivos
                        </button>
                    </div>
                </div>
            </div><!-- /.modal-content -->
        </div><!-- /.modal-dialog -->
    </div><!-- /.modal -->
@endsection

@section('script')
    <script>
        let datosToSave = [];
        document.querySelector("#btnExcluir").addEventListener("click", function() {
            var myModalExcluir = new bootstrap.Modal(document.getElementById('myModalExcluir'), {
                keyboard: false
            });
            myModalExcluir.show();
        });

        document.querySelector("#btnGuardar").addEventListener("click", function() {
            Swal.fire({
                title: "Guardndo Información ...",
                icon: 'info',
                allowOutsideClick: false,
                showConfirmButton: false,
                timerProgressBar: true,
                didOpen: () => Swal.showLoading()
            });
            let mes = document.getElementById('mes').value;
            let year = document.getElementById('year').value;
            fetch("/incentivos/save", {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/json",
                        "X-CSRF-TOKEN": "{{ csrf_token() }}"
                    },
                    body: JSON.stringify({
                        datos: datosToSave,
                        mes,
                        year
                    })
                })
                .then(response => response.json())
                .then(data => {
                    Swal.fire({
                        title: "Listo",
                        text: data.message,
                        icon: "success"
                    });
                })
                .catch(error => {
                    Swal.fire({
                        title: "Error",
                        text: error,
                        icon: "warning"
                    });
                });
        });

        document.querySelector("#btnGuardarPlanAgencia").addEventListener("click", function() {
            Swal.fire({
                title: "Guardndo Información ...",
                icon: 'info',
                allowOutsideClick: false,
                showConfirmButton: false,
                timerProgressBar: true,
                didOpen: () => Swal.showLoading()
            });
            let mes = document.getElementById('mes_plan').value;
            let year = document.getElementById('year').value;
            fetch("/incentivos/save/plan-agencia", {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/json",
                        "X-CSRF-TOKEN": "{{ csrf_token() }}"
                    },
                    body: JSON.stringify({
                        datos: datosToSave,
                        mes,
                        year
                    })
                })
                .then(response => response.json())
                .then(data => {
                    Swal.fire({
                        title: "Listo",
                        text: data.message,
                        icon: "success"
                    });
                })
                .catch(error => {
                    Swal.fire({
                        title: "Error",
                        text: error,
                        icon: "warning"
                    });
                });
        });

        document.querySelector("#btnGuardarEfectividad").addEventListener("click", function() {
            Swal.fire({
                title: "Guardndo Información ...",
                icon: 'info',
                allowOutsideClick: false,
                showConfirmButton: false,
                timerProgressBar: true,
                didOpen: () => Swal.showLoading()
            });
            let mes = document.getElementById('mes_efectividad').value;
            let year = document.getElementById('year').value;
            fetch("/incentivos/save/efectividad", {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/json",
                        "X-CSRF-TOKEN": "{{ csrf_token() }}"
                    },
                    body: JSON.stringify({
                        datos: datosToSave,
                        mes,
                        year
                    })
                })
                .then(response => response.json())
                .then(data => {
                    Swal.fire({
                        title: "Listo",
                        text: data.message,
                        icon: "success"
                    });
                })
                .catch(error => {
                    Swal.fire({
                        title: "Error",
                        text: error,
                        icon: "warning"
                    });
                });
        });

        document.querySelector("#btnGuardarPagoAgente").addEventListener("click", function() {
            Swal.fire({
                title: "Guardndo Información ...",
                icon: 'info',
                allowOutsideClick: false,
                showConfirmButton: false,
                timerProgressBar: true,
                didOpen: () => Swal.showLoading()
            });
            let sistema = document.getElementById('sistema').value;
            let mes = document.getElementById('mes_incentivo').value;
            let year = document.getElementById('year').value;
            fetch("/incentivos/save/pago-incentivos-agente", {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/json",
                        "X-CSRF-TOKEN": "{{ csrf_token() }}"
                    },
                    body: JSON.stringify({
                        datos: datosToSave,
                        mes,
                        sistema,
                        year
                    })
                })
                .then(response => response.json())
                .then(data => {
                    Swal.fire({
                        title: "Listo",
                        text: data.message,
                        icon: "success"
                    });
                })
                .catch(error => {
                    Swal.fire({
                        title: "Error",
                        text: error,
                        icon: "warning"
                    });
                });
        });

        document.querySelector("#btnGuardarPagoCoordinador").addEventListener("click", function() {
            Swal.fire({
                title: "Guardndo Información ...",
                icon: 'info',
                allowOutsideClick: false,
                showConfirmButton: false,
                timerProgressBar: true,
                didOpen: () => Swal.showLoading()
            });
            let sistema = document.getElementById('sistema').value;
            let mes = document.getElementById('mes_incentivo').value;
            let year = document.getElementById('year').value;
            fetch("/incentivos/save/pago-incentivos-coordinador", {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/json",
                        "X-CSRF-TOKEN": "{{ csrf_token() }}"
                    },
                    body: JSON.stringify({
                        datos: datosToSave,
                        mes,
                        sistema,
                        year
                    })
                })
                .then(response => response.json())
                .then(data => {
                    Swal.fire({
                        title: "Listo",
                        text: data.message,
                        icon: "success"
                    });
                })
                .catch(error => {
                    Swal.fire({
                        title: "Error",
                        text: error,
                        icon: "warning"
                    });
                });
        });

        document.querySelector("#btnGuardarPagoAdmin").addEventListener("click", function() {
            Swal.fire({
                title: "Guardndo Información ...",
                icon: 'info',
                allowOutsideClick: false,
                showConfirmButton: false,
                timerProgressBar: true,
                didOpen: () => Swal.showLoading()
            });
            let sistema = document.getElementById('sistema').value;
            let mes = document.getElementById('mes_admin').value;
            let year = document.getElementById('year').value;
            fetch("/incentivos/save/pago-incentivos-admin", {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/json",
                        "X-CSRF-TOKEN": "{{ csrf_token() }}"
                    },
                    body: JSON.stringify({
                        datos: datosToSave,
                        mes,
                        sistema,
                        year
                    })
                })
                .then(response => response.json())
                .then(data => {
                    Swal.fire({
                        title: "Listo",
                        text: data.message,
                        icon: "success"
                    });
                })
                .catch(error => {
                    Swal.fire({
                        title: "Error",
                        text: error,
                        icon: "warning"
                    });
                });
        });

        document.getElementById('btnGenerar').addEventListener('click', list);
        document.getElementById('btnGenerarExcluir').addEventListener('click', list);

        function list() {
            $('#tableItems').DataTable().destroy();
            datosToSave = [];
            let year = document.getElementById('year').value;
            let mes = document.getElementById('mes').value;
            if (mes === '') {
                Swal.fire({
                    title: "Información",
                    text: 'Seleccione un mes para generar los incentivos',
                    icon: "warning"
                });
                return;
            }
            Swal.fire({
                title: "Procesando Información ...",
                icon: 'info',
                allowOutsideClick: false,
                showConfirmButton: false,
                timerProgressBar: true,
                didOpen: () => Swal.showLoading()
            });
            // Obtenr productos excluidos
            let checks = document.querySelectorAll('.chkProductoExcluir');
            let productosExcluidos = [];
            checks.forEach(function(check) {
                if (check.checked) {
                    productosExcluidos.push(check.getAttribute('data-producto-id'));
                }
            });
            let excluidos = productosExcluidos.join(',');
            fetch("/incentivos/list?mes=" + mes + "&excluidos=" + excluidos + '&year=' + year)
                .then(response => response.json())
                .then(data => {
                    const tableBody = document.querySelector('#tableItems tbody');
                    tableBody.innerHTML = ''; // Limpiar filas existentes

                    data.forEach(item => {
                        const row = document.createElement('tr');
                        row.innerHTML = `
                            <td>${item.agencia_id}</td>
                            <td>${item.tipo_producto}</td>
                            <td>${item.sistema}</td>
                            <td>${item.total_trimestre}</td>
                            <td>${item.promedio_mensual}</td>
                            <td>${item.venta_base}</td>
                            <td>${item.total_mes}</td>
                            <td>${item.nivel}</td>
                            <td>${item.cumplimiento}</td>
                            <td>${item.meta_incremental}</td>
                        `;
                        tableBody.appendChild(row);
                        datosToSave.push(item);
                    });

                    $('#tableItems').DataTable({
                        responsive: true,
                        scrollX: true,
                        columnDefs: [
                            { targets: [1, 2], visible: $(window).width() > 768 }
                        ],
                        dom: 'Bfrtip',
                        buttons: [
                            'copy', 'csv', 'excel', 'pdf', 'print'
                        ],
                        order: [
                            [0, 'asc'],
                            [1, 'asc']
                        ]
                    });
                    Swal.close();
                    btnCloseExcluidos.click();
                })
                .catch(error => {
                    Swal.fire({
                        title: "Error",
                        text: error,
                        icon: "warning"
                    });
                });
        }

        function listAgenciaPlan() {
            datosToSave = [];
            let mes = document.getElementById('mes_plan').value;
            let year = document.getElementById('year').value;
            if (mes === '') {
                Swal.fire({
                    title: "Información",
                    text: 'Seleccione un mes para generar la información',
                    icon: "warning"
                });
                return;
            }
            Swal.fire({
                title: "Procesando Información ...",
                icon: 'info',
                allowOutsideClick: false,
                showConfirmButton: false,
                timerProgressBar: true,
                didOpen: () => Swal.showLoading()
            });

            $('#tableAgenciaPlan').DataTable().destroy();
            $('#tableAgenciaPlan tbody').empty();
            fetch("/incentivos/list/plan-agencia?mes=" + mes + '&year=' + year)
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

                    const tableBody = document.querySelector('#tableAgenciaPlan tbody');
                    tableBody.innerHTML = ''; // Limpiar filas existentes

                    data.forEach(item => {
                        const row = document.createElement('tr');
                        row.innerHTML = `
                            <td>${item.agencia_id}</td>
                            <td>${item.tipo_producto}</td>
                            <td>${item.sistema}</td>
                            <td>${item.venta_mes}</td>
                            <td>${item.venta_base}</td>
                            <td>${item.excedente}</td>
                            <td>${item.porcentaje_agente}</td>
                            <td>${item.porcentaje_coordinador}</td>
                            <td>${item.porcentaje_administrativo}</td>
                            <td>${item.monto_agente}</td>
                            <td>${item.monto_coordinador}</td>
                            <td>${item.monto_administrativo}</td>
                            <td>${item.total_distribucion}</td>
                        `;
                        tableBody.appendChild(row);
                        datosToSave.push(item);
                    });

                    $('#tableAgenciaPlan').DataTable({
                        responsive: true,
                        scrollX: true,
                        columnDefs: [
                            { targets: [1, 2], visible: $(window).width() > 768 }
                        ],
                        dom: 'Bfrtip',
                        buttons: [
                            'copy', 'csv', 'excel', 'pdf', 'print'
                        ],
                        order: [
                            [0, 'asc'],
                            [1, 'asc']
                        ]
                    });
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

        // Cargar la lista al cargar la página
        document.querySelector("#btnGenerarDataPlan").addEventListener('click', function() {
            listAgenciaPlan();
        });

        function listEfectividad() {
            datosToSave = [];
            let mes = document.getElementById('mes_efectividad').value;
            if (mes === '') {
                Swal.fire({
                    title: "Información",
                    text: 'Seleccione un mes para generar la información',
                    icon: "warning"
                });
                return;
            }
            let checks = document.querySelectorAll('.chkProductoExcluir');
            let productosExcluidos = [];
            checks.forEach(function(check) {
                if (check.checked) {
                    productosExcluidos.push(check.getAttribute('data-producto-id'));
                }
            });
            let excluidos = productosExcluidos.join(',');
            let year = document.getElementById('year').value;
            Swal.fire({
                title: "Procesando Información ...",
                icon: 'info',
                allowOutsideClick: false,
                showConfirmButton: false,
                timerProgressBar: true,
                didOpen: () => Swal.showLoading()
            });

            $('#tableEfectividad').DataTable().destroy();
            $('#tableEfectividad tbody').empty();
            fetch("/incentivos/list/efectividad-usuario?mes=" + mes + "&excluidos=" + excluidos + '&year=' + year)
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

                    const tableBody = document.querySelector('#tableEfectividad tbody');
                    tableBody.innerHTML = ''; // Limpiar filas existentes

                    data.forEach(item => {
                        const row = document.createElement('tr');
                        row.innerHTML = `
                            <td>${item.agencia_id}</td>
                            <td>${item.sistema}</td>
                            <td>${item.tipo_producto}</td>
                            <td>${item.venta_mes}</td>
                            <td>${item.cedula_bet}</td>
                            <td>${item.monto_bet_cedula}</td>
                            <td>${item.porc_bet}</td>
                            <td>${item.cedula_net}</td>
                            <td>${item.monto_net_cedula}</td>
                            <td>${item.porc_net}</td>
                        `;
                        tableBody.appendChild(row);
                        datosToSave.push(item);
                    });

                    $('#tableEfectividad').DataTable({
                        responsive: true,
                        scrollX: true,
                        columnDefs: [
                            { targets: [1, 2], visible: $(window).width() > 768 }
                        ],
                        dom: 'Bfrtip',
                        buttons: [
                            'copy', 'csv', 'excel', 'pdf', 'print'
                        ],
                        order: [
                            [0, 'asc'],
                            [2, 'asc']
                        ]
                    });
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

        document.querySelector("#btnGenerarDataEfectividad").addEventListener('click', function() {
            listEfectividad();
        });

        function listPagoAgente() {
            datosToSave = [];
            let sistema = document.getElementById('sistema').value;
            let mes = document.getElementById('mes_incentivo').value;
            let year = document.getElementById('year').value;
            if (mes === '') {
                Swal.fire({
                    title: "Información",
                    text: 'Seleccione un mes para generar la información',
                    icon: "warning"
                });
                return;
            }
            Swal.fire({
                title: "Procesando Información ...",
                icon: 'info',
                allowOutsideClick: false,
                showConfirmButton: false,
                timerProgressBar: true,
                didOpen: () => Swal.showLoading()
            });

            $('#tablePagoAgente').DataTable().destroy();
            $('#tablePagoAgente tbody').empty();
            fetch("/incentivos/list/pago-incentivos-agente?mes=" + mes + '&sistema=' + sistema + '&year=' + year)
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

                    const tableBody = document.querySelector('#tablePagoAgente tbody');
                    tableBody.innerHTML = ''; // Limpiar filas existentes

                    data.forEach(item => {
                        const row = document.createElement('tr');
                        row.innerHTML = `
                            <td>${item.agencia_id}</td>
                            <td>${item.tipo_producto}</td>
                            <td>${item.cedula}</td>
                            <td>${item.porcentaje_cedula}</td>
                            <td>${item.monto_agente}</td>
                            <td>${item.monto_incentivo}</td>
                        `;
                        tableBody.appendChild(row);
                        datosToSave.push(item);
                    });

                    $('#tablePagoAgente').DataTable({
                        responsive: true,
                        scrollX: true,
                        columnDefs: [
                            { targets: [1, 2], visible: $(window).width() > 768 }
                        ],
                        dom: 'Bfrtip',
                        buttons: [
                            'copy', 'csv', 'excel', 'pdf', 'print'
                        ],
                        order: [
                            [0, 'asc'],
                            [2, 'asc']
                        ]
                    });
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

        document.querySelector("#btnGenerarDataPagoAgente").addEventListener('click', function() {
            listPagoAgente();
        });

        function listPagoCoordinador() {
            datosToSave = [];
            let sistema = document.getElementById('sistema').value;
            let mes = document.getElementById('mes_coordinador').value;
            let year = document.getElementById('year').value;
            if (mes === '') {
                Swal.fire({
                    title: "Información",
                    text: 'Seleccione un mes para generar la información',
                    icon: "warning"
                });
                return;
            }
            Swal.fire({
                title: "Procesando Información ...",
                icon: 'info',
                allowOutsideClick: false,
                showConfirmButton: false,
                timerProgressBar: true,
                didOpen: () => Swal.showLoading()
            });

            $('#tablePagoCoordinador').DataTable().destroy();
            $('#tablePagoCoordinador tbody').empty();
            fetch("/incentivos/list/pago-incentivos-coordinador?mes=" + mes + '&sistema=' + sistema + '&year=' + year)
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

                    const tableBody = document.querySelector('#tablePagoCoordinador tbody');
                    tableBody.innerHTML = ''; // Limpiar filas existentes

                    data.forEach(item => {
                        const row = document.createElement('tr');
                        row.innerHTML = `
                            <td>${item.company}</td>
                            <td>${item.cedula}</td>
                            <td>${item.nombres}</td>
                            <td>${item.apellidos}</td>
                            <td>${item.total_empleado}</td>
                            <td>
                                <button onclick='listPagoCoordinadorDetalle("${item.cedula}")' class='btn btn-success'>Ver Detalle</button>
                            </td>
                        `;
                        tableBody.appendChild(row);
                        datosToSave.push(item);
                    });

                    $('#tablePagoCoordinador').DataTable({
                        responsive: true,
                        scrollX: true,
                        columnDefs: [
                            { targets: [2, 3], visible: $(window).width() > 768 }
                        ],
                        dom: 'Bfrtip',
                        buttons: [
                            'copy', 'csv', 'excel', 'pdf', 'print'
                        ],
                        order: [
                            [1, 'asc']
                        ]
                    });
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

        document.querySelector("#btnGenerarDataPagoCoordinador").addEventListener('click', function() {
            listPagoCoordinador();
        });

        function listPagoCoordinadorDetalle(cedula, tipo_producto = '') {
            var modal = new bootstrap.Modal(document.getElementById('myModal'));
            modal.show();

            datosToSave = [];
            Swal.fire({
                title: "Procesando Información ...",
                icon: 'info',
                allowOutsideClick: false,
                showConfirmButton: false,
                timerProgressBar: true,
                didOpen: () => Swal.showLoading()
            });
            $('#tablePagoCoordinadorDetalle').DataTable().destroy();
            $('#tablePagoCoordinadorDetalle tbody').empty();
            let year = document.getElementById('year').value;
            let sistema = document.getElementById('sistema').value;
            let mes = document.getElementById('mes_coordinador').value;
            fetch("/incentivos/list/pago-incentivos-coordinador-detalle?cedula=" + cedula +
                    '&tipo_producto=' + tipo_producto +
                    '&sistema=' + sistema +
                    '&mes=' + mes +
                    '&year=' + year)
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

                    const tableBody = document.querySelector('#tablePagoCoordinadorDetalle tbody');
                    tableBody.innerHTML = ''; // Limpiar filas existentes

                    data.forEach(item => {
                        const row = document.createElement('tr');
                        row.innerHTML = `
                            <td>${item.agencia_id}</td>
                            <td>${item.tipo_producto}</td>
                            <td>${item.sistema}</td>
                            <td>${item.venta_mes}</td>
                            <td>${item.venta_base}</td>
                            <td>${item.excedente}</td>
                            <td>${item.porcentaje_coordinador}</td>
                            <td>${item.monto_coordinador}</td>
                        `;
                        tableBody.appendChild(row);
                        datosToSave.push(item);
                    });

                    $('#tablePagoCoordinadorDetalle').DataTable({
                        responsive: true,
                        scrollX: true,
                        columnDefs: [
                            { targets: [1, 2], visible: $(window).width() > 768 }
                        ],
                        dom: 'Bfrtip',
                        buttons: [
                            'copy', 'csv', 'excel', 'pdf', 'print'
                        ]
                    });
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

        function listPagoAdmin() {
            datosToSave = [];
            let sistema = document.getElementById('sistema').value;
            let mes = document.getElementById('mes_admin').value;
            let year = document.getElementById('year').value;
            if (mes === '') {
                Swal.fire({
                    title: "Información",
                    text: 'Seleccione un mes para generar la información',
                    icon: "warning"
                });
                return;
            }
            Swal.fire({
                title: "Procesando Información ...",
                icon: 'info',
                allowOutsideClick: false,
                showConfirmButton: false,
                timerProgressBar: true,
                didOpen: () => Swal.showLoading()
            });

            $('#tablePagoAdmin').DataTable().destroy();
            $('#tablePagoAdmin tbody').empty();
            fetch("/incentivos/list/pago-incentivos-admin?mes=" + mes + '&sistema=' + sistema + '&year=' + year)
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

                    const tableBody = document.querySelector('#tablePagoAdmin tbody');
                    tableBody.innerHTML = ''; // Limpiar filas existentes

                    data.forEach(item => {
                        const row = document.createElement('tr');
                        row.innerHTML = `
                            <td>${item.empresa}</td>
                            <td>${item.empleadoid}</td>
                            <td>${item.cedula}</td>
                            <td>${item.nombres}</td>
                            <td>${item.apellidos}</td>
                            <td>${item.porcentaje}</td>
                            <td>${item.Tradicional}</td>
                            <td>${item.No_Tradicional}</td>
                            <td>${item.Recargas}</td>
                            <td>${item.Paquetico}</td>
                            <td>${item.Total_a_cobrar}</td>
                            <td>
                                <button onclick="listPagoAdminDetalle('${item.cedula}', ${item.companyid})" class='btn btn-success'>Ver</button>
                            </td>
                        `;
                        tableBody.appendChild(row);
                        datosToSave.push(item);
                    });

                    $('#tablePagoAdmin').DataTable({
                        responsive: true,
                        scrollX: true,
                        columnDefs: [
                            { targets: [2, 3], visible: $(window).width() > 768 }
                        ],
                        dom: 'Bfrtip',
                        buttons: [
                            'copy', 'csv', 'excel', 'pdf', 'print'
                        ],
                        order: [
                            [1, 'asc']
                        ]
                    });
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

        document.querySelector("#btnGenerarDataPagoAdmin").addEventListener('click', function() {
            listPagoAdmin();
        });

        function listPagoAdminDetalle(cedula, companyid) {
            var modal = new bootstrap.Modal(document.getElementById('myModalAdmin'));
            modal.show();
            let mes = document.getElementById('mes_admin').value;

            let year = document.getElementById('year').value;

            Swal.fire({
                title: "Procesando Información ...",
                icon: 'info',
                allowOutsideClick: false,
                showConfirmButton: false,
                timerProgressBar: true,
                didOpen: () => Swal.showLoading()
            });
            $('#tablePagoAdminDetalle').DataTable().destroy();
            $('#tablePagoAdminDetalle tbody').empty();
            fetch("/incentivos/list/pago-incentivos-admin-detalle?cedula=" + cedula + '&mes=' + mes + '&companyid=' +
                    companyid + '&year=' + year)
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

                    const tableBody = document.querySelector('#tablePagoAdminDetalle tbody');
                    tableBody.innerHTML = ''; // Limpiar filas existentes

                    data.forEach(item => {
                        const row = document.createElement('tr');
                        row.innerHTML = `
                            <td>${item.tipo_producto}</td>
                            <td>${item.total_tipo_producto}</td>
                            <td>${item.porcentaje}</td>
                            <td>${item.total_a_pagar}</td>
                        `;
                        tableBody.appendChild(row);
                        datosToSave.push(item);
                    });

                    $('#tablePagoAdminDetalle').DataTable({
                        responsive: true,
                        scrollX: true,
                        columnDefs: [
                            { targets: [1, 2], visible: $(window).width() > 768 }
                        ],
                        dom: 'Bfrtip',
                        buttons: [
                            'copy', 'csv', 'excel', 'pdf', 'print'
                        ],
                        order: [
                            [1, 'asc']
                        ]
                    });
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
