@extends('app')

@section('content')
    <div class="main-content">
        <div class="page-content">
            <div class="container-fluid">

                <!-- start page title -->
                <div class="row">
                    <div class="col-12">
                        <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                            <h4 class="mb-sm-0">Editar Usuario</h4>

                            <div class="page-title-right">
                                <ol class="breadcrumb m-0">
                                    <li class="breadcrumb-item"><a href="{{ route('inicio.index') }}">Inicio</a></li>
                                    <li class="breadcrumb-item"><a href="{{ route('mantenimiento.index') }}">Mantenimientos</a></li>
                                    <li class="breadcrumb-item"><a href="{{ route('usuarios.index') }}">Usuarios</a></li>
                                    <li class="breadcrumb-item active">Editar</li>
                                </ol>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- end page title -->

                <div class="row">
                    <div class="col-lg-8 mx-auto">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="card-title mb-0">Datos del Usuario</h5>
                            </div>
                            <div class="card-body">
                                <form action="{{ route('usuarios.update', $usuario->id) }}" method="POST">
                                    @csrf
                                    @method('PUT')

                                    <div class="row">
                                        <div class="col-12 col-md-6 mb-3">
                                            <label for="name" class="form-label">Nombre <span class="text-danger">*</span></label>
                                            <div class="input-group">
                                                <span class="input-group-text"><i class="ri-user-line"></i></span>
                                                <input type="text" class="form-control @error('name') is-invalid @enderror"
                                                       id="name" name="name" value="{{ old('name', $usuario->name) }}"
                                                       placeholder="Nombre completo" required>
                                            </div>
                                            @error('name')
                                                <div class="text-danger small mt-1">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <div class="col-12 col-md-6 mb-3">
                                            <label for="email" class="form-label">Correo ElectrÃ³nico <span class="text-danger">*</span></label>
                                            <div class="input-group">
                                                <span class="input-group-text"><i class="ri-mail-line"></i></span>
                                                <input type="email" class="form-control @error('email') is-invalid @enderror"
                                                       id="email" name="email" value="{{ old('email', $usuario->email) }}"
                                                       placeholder="correo@ejemplo.com" required>
                                            </div>
                                            @error('email')
                                                <div class="text-danger small mt-1">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <div class="col-12 mb-3">
                                            <div class="alert alert-info py-2 mb-0">
                                                <i class="ri-information-line me-1"></i>
                                                Deja los campos de contraseÃ±a vacÃ­os si no deseas cambiarla.
                                            </div>
                                        </div>

                                        <div class="col-12 col-md-6 mb-3">
                                            <label for="password" class="form-label">Nueva ContraseÃ±a</label>
                                            <div class="input-group">
                                                <span class="input-group-text"><i class="ri-lock-2-line"></i></span>
                                                <input type="password" class="form-control @error('password') is-invalid @enderror"
                                                       id="password" name="password"
                                                       placeholder="MÃ­nimo 8 caracteres">
                                                <button class="btn btn-light" type="button" onclick="togglePassword('password', 'passIcon1')">
                                                    <i class="ri-eye-off-line" id="passIcon1"></i>
                                                </button>
                                            </div>
                                            @error('password')
                                                <div class="text-danger small mt-1">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <div class="col-12 col-md-6 mb-3">
                                            <label for="password_confirmation" class="form-label">Confirmar ContraseÃ±a</label>
                                            <div class="input-group">
                                                <span class="input-group-text"><i class="ri-lock-check-line"></i></span>
                                                <input type="password" class="form-control"
                                                       id="password_confirmation" name="password_confirmation"
                                                       placeholder="Repite la contraseÃ±a">
                                                <button class="btn btn-light" type="button" onclick="togglePassword('password_confirmation', 'passIcon2')">
                                                    <i class="ri-eye-off-line" id="passIcon2"></i>
                                                </button>
                                            </div>
                                        </div>

                                        <div class="col-12 mb-3">
                                            <label for="roles" class="form-label">Roles</label>
                                            <select id="roles" name="roles[]" class="form-select" multiple>
                                                @foreach($roles as $role)
                                                    <option value="{{ $role->name }}" @selected(in_array($role->name, old('roles', $userRoles ?? [])))>
                                                        {{ $role->name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                            <div class="form-text">Puedes seleccionar uno o varios roles.</div>
                                        </div>
                                    </div>

                                    <div class="d-flex flex-column flex-sm-row justify-content-end gap-2 mt-3">
                                        <a href="{{ route('usuarios.index') }}" class="btn btn-secondary">
                                            <i class="ri-close-line align-bottom me-1"></i> Cancelar
                                        </a>
                                        <button type="submit" class="btn btn-primary">
                                            <i class="ri-save-line align-bottom me-1"></i> Actualizar
                                        </button>
                                    </div>
                                </form>
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
                        </script> Â© ERP.
                    </div>
                </div>
            </div>
        </footer>
    </div>
@endsection

@section('script')
<script>
    function togglePassword(inputId, iconId) {
        const input = document.getElementById(inputId);
        const icon = document.getElementById(iconId);
        if (input.type === 'password') {
            input.type = 'text';
            icon.classList.remove('ri-eye-off-line');
            icon.classList.add('ri-eye-line');
        } else {
            input.type = 'password';
            icon.classList.remove('ri-eye-line');
            icon.classList.add('ri-eye-off-line');
        }
    }
</script>
@endsection

