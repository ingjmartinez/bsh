@extends('app')

@section('content')
    <div class="main-content">
        <div class="page-content">
            <div class="container-fluid">

                <!-- start page title -->
                <div class="row">
                    <div class="col-12">
                        <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                            <h4 class="mb-sm-0">Mantenimiento de Usuarios</h4>

                            <div class="page-title-right">
                                <ol class="breadcrumb m-0">
                                    <li class="breadcrumb-item"><a href="{{ route('inicio.index') }}">Inicio</a></li>
                                    <li class="breadcrumb-item"><a href="{{ route('mantenimiento.index') }}">Mantenimientos</a></li>
                                    <li class="breadcrumb-item active">Usuarios</li>
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
                                <div class="row align-items-center">
                                    <div class="col-12 col-md-6 mb-3 mb-md-0">
                                        <h5 class="card-title mb-0">Lista de Usuarios</h5>
                                    </div>
                                    <div class="col-12 col-md-6">
                                        <div class="d-flex flex-column flex-sm-row gap-2 justify-content-md-end">
                                            @can('usuarios.create')
                                                <a href="{{ route('usuarios.create') }}" class="btn btn-primary">
                                                    <i class="ri-add-line align-bottom me-1"></i> Nuevo Usuario
                                                </a>
                                            @endcan
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table id="tableUsuarios" class="table table-bordered table-striped align-middle" style="width:100%; font-size: 0.875rem;">
                                        <thead class="table-light">
                                            <tr>
                                                <th class="text-center" style="min-width: 50px;">ID</th>
                                                <th style="min-width: 150px;">Nombre</th>
                                                <th style="min-width: 200px;">Correo Electr&oacute;nico</th>
                                                <th style="min-width: 200px;">Roles</th>
                                                <th style="min-width: 150px;">Fecha de Registro</th>
                                                <th class="text-center" style="min-width: 100px;">Acciones</th>
                                            </tr>
                                        </thead>
                                        <tbody></tbody>
                                    </table>
                                </div>
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
                        </script> &copy; ERP.
                    </div>
                </div>
            </div>
        </footer>
    </div>

    <!-- Modal para eliminar -->
    <div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="deleteModalLabel">Confirmar eliminaci&oacute;n</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    &iquest;Est&aacute; seguro que desea eliminar este usuario? Esta acci&oacute;n no se puede deshacer.
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <form id="deleteForm" method="POST" style="display:inline;">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger">Eliminar</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('script')
<script>
    $(document).ready(function() {
        var currentUserId = {{ auth()->id() }};
        var canEdit = @json(auth()->user()->can('usuarios.edit'));
        var canDelete = @json(auth()->user()->can('usuarios.delete'));

        var table = $('#tableUsuarios').DataTable({
            processing: true,
            serverSide: true,
            ajax: '{{ route('usuarios.list') }}',
            responsive: true,
            scrollX: true,
            columnDefs: [
                { targets: [4], visible: $(window).width() > 768 }
            ],
            columns: [
                { data: 'id', name: 'id', className: 'text-center' },
                { data: 'name', name: 'name' },
                { data: 'email', name: 'email' },
                { data: 'roles', name: 'roles', orderable: false, searchable: false },
                { data: 'created_at', name: 'created_at' },
                {
                    data: null,
                    orderable: false,
                    searchable: false,
                    className: 'text-center',
                    render: function(data, type, row) {
                        var buttons = `
                            <div class="d-flex gap-1 justify-content-center flex-nowrap">`;

                        if (canEdit) {
                            buttons += `
                                <a href="/usuarios/${row.id}/edit" class="btn btn-sm btn-success" title="Editar">
                                    <i class="ri-pencil-line"></i>
                                </a>`;
                        }

                        // No mostrar boton eliminar para el usuario actual
                        if (canDelete && row.id !== currentUserId) {
                            buttons += `
                                <button class="btn btn-sm btn-danger btn-delete" data-id="${row.id}" title="Eliminar">
                                    <i class="ri-delete-bin-line"></i>
                                </button>`;
                        }

                        buttons += `</div>`;
                        return buttons;
                    }
                }
            ],
            language: {
                url: '//cdn.datatables.net/plug-ins/1.11.5/i18n/es-ES.json'
            },
            dom: '<"row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6"f>>rtip'
        });

        // Manejar eliminacion
        $('#tableUsuarios').on('click', '.btn-delete', function() {
            var id = $(this).data('id');
            var form = $('#deleteForm');
            form.attr('action', '/usuarios/' + id);
            $('#deleteModal').modal('show');
        });

        // Mostrar mensaje de exito si existe
        @if(session('success'))
            Swal.fire({
                icon: 'success',
                title: '\u00A1\u00C9xito!',
                text: '{{ session('success') }}',
                timer: 3000,
                showConfirmButton: false
            });
        @endif

        // Mostrar mensaje de error si existe
        @if(session('error'))
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: '{{ session('error') }}'
            });
        @endif

        // Mostrar errores de validacion
        @if($errors->any())
            Swal.fire({
                icon: 'error',
                title: 'Error de validaci\u00F3n',
                html: `{!! implode('<br>', $errors->all()) !!}`
            });
        @endif
    });
</script>
@endsection

