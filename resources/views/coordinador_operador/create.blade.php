@extends('app')

@section('content')
    <div class="main-content">
        <div class="page-content">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-12">
                        <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                            <h4 class="mb-sm-0">Nuevo Coordinador</h4>
                            <div class="page-title-right">
                                <ol class="breadcrumb m-0">
                                    <li class="breadcrumb-item"><a href="{{ route('inicio.index') }}">Inicio</a></li>
                                    <li class="breadcrumb-item"><a href="{{ route('mantenimiento.index') }}">Mantenimientos</a></li>
                                    <li class="breadcrumb-item"><a href="{{ route('coordinador-operador.index') }}">Coordinador</a></li>
                                    <li class="breadcrumb-item active">Crear</li>
                                </ol>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-lg-8 mx-auto">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="card-title mb-0">Datos del registro</h5>
                            </div>
                            <div class="card-body">
                                <form action="{{ route('coordinador-operador.store') }}" method="POST">
                                    @csrf

                                    <div class="row g-3">
                                        <div class="col-md-6">
                                            <label class="form-label">Nombre completo <span class="text-danger">*</span></label>
                                            <input type="text" name="nombre" class="form-control @error('nombre') is-invalid @enderror" value="{{ old('nombre') }}" required>
                                            @error('nombre')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                        </div>

                                        <div class="col-md-6">
                                            <label class="form-label">Apellido</label>
                                            <input type="text" name="apellido" class="form-control @error('apellido') is-invalid @enderror" value="{{ old('apellido') }}">
                                            @error('apellido')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                        </div>

                                        <div class="col-md-6">
                                            <label class="form-label">Correo <span class="text-danger">*</span></label>
                                            <input type="email" name="correo" class="form-control @error('correo') is-invalid @enderror" value="{{ old('correo') }}" required>
                                            @error('correo')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                        </div>

                                        <div class="col-md-6">
                                            <label class="form-label">Cargo <span class="text-danger">*</span></label>
                                            <select name="cargo" class="form-select @error('cargo') is-invalid @enderror" required>
                                                <option value="">Seleccione un cargo</option>
                                                @foreach($cargosDisponibles as $cargo)
                                                    <option value="{{ $cargo }}" @selected(old('cargo') === $cargo)>{{ $cargo }}</option>
                                                @endforeach
                                            </select>
                                            @error('cargo')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                        </div>

                                        <div class="col-md-6">
                                            <label class="form-label">Cédula <span class="text-danger">*</span></label>
                                            <input type="text" name="cedula" class="form-control @error('cedula') is-invalid @enderror" value="{{ old('cedula') }}" inputmode="numeric" pattern="[0-9]{11}" maxlength="11" oninput="this.value=this.value.replace(/\D/g,'').slice(0, 11); this.setCustomValidity('')" oninvalid="if(this.validity.valueMissing){this.setCustomValidity('Campo de 11 Digitos obligatorios')}else if(this.value.length < 11){this.setCustomValidity('Faltan digitos: la cedula debe tener 11')}else if(this.value.length > 11){this.setCustomValidity('Tiene digitos de mas: la cedula debe tener 11')}else{this.setCustomValidity('Campo de 11 Digitos obligatorios')}" required>
                                            <div class="form-text">Campo de 11 Digitos obligatorios</div>
                                            @error('cedula')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                        </div>

                                        <div class="col-md-6">
                                            <label class="form-label">Teléfono <span class="text-danger">*</span></label>
                                            <input type="text" name="telefono" class="form-control @error('telefono') is-invalid @enderror" value="{{ old('telefono') }}" inputmode="numeric" pattern="[0-9]{11}" maxlength="11" oninput="this.value=this.value.replace(/\D/g,'').slice(0, 11); this.setCustomValidity('')" oninvalid="if(this.validity.valueMissing){this.setCustomValidity('Campo de 11 Digitos obligatorios')}else if(this.value.length < 11){this.setCustomValidity('Faltan digitos: el telefono debe tener 11')}else if(this.value.length > 11){this.setCustomValidity('Tiene digitos de mas: el telefono debe tener 11')}else{this.setCustomValidity('Campo de 11 Digitos obligatorios')}" required>
                                            <div class="form-text">Campo de 11 Digitos obligatorios</div>
                                            @error('telefono')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                        </div>

                                        <input type="hidden" name="puesto" value="coordinador">
                                        <div class="col-12">
                                            <label class="form-label">Usuario que utilizará el panel móvil</label>
                                            <select name="user_id" class="form-select @error('user_id') is-invalid @enderror">
                                                <option value="">Sin vincular todavía</option>
                                                @foreach($usuariosDisponibles as $usuario)<option value="{{ $usuario->id }}" @selected(old('user_id')==$usuario->id)>{{ $usuario->name }} — {{ $usuario->email }}</option>@endforeach
                                            </select>
                                            <div class="form-text">Este usuario verá únicamente su ruta diaria.</div>
                                            @error('user_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                        </div>
                                    </div>

                                    <div class="d-flex justify-content-end gap-2 mt-4">
                                        <a href="{{ route('coordinador-operador.index') }}" class="btn btn-secondary">Cancelar</a>
                                        <button type="submit" class="btn btn-primary">Guardar</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('script')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const cedulaInput = document.querySelector('input[name="cedula"]');
        const cedulaLabel = cedulaInput?.closest('.col-md-6')?.querySelector('.form-label');
        const cedulaHelp = cedulaInput?.closest('.col-md-6')?.querySelector('.form-text');

        if (cedulaInput) {
            cedulaInput.removeAttribute('required');
            cedulaInput.setAttribute('aria-describedby', 'cedulaHelp');
            cedulaInput.oninvalid = function () {
                if (!this.value) {
                    this.setCustomValidity('');
                } else if (this.value.length < 11) {
                    this.setCustomValidity('Faltan digitos: la cedula debe tener 11');
                } else if (this.value.length > 11) {
                    this.setCustomValidity('Tiene digitos de mas: la cedula debe tener 11');
                } else {
                    this.setCustomValidity('La cedula debe tener 11 digitos');
                }
            };
        }

        if (cedulaLabel) {
            cedulaLabel.textContent = 'Cedula';
        }

        if (cedulaHelp) {
            cedulaHelp.id = 'cedulaHelp';
            cedulaHelp.textContent = 'Opcional. Si la registra, debe tener 11 digitos.';
        }
    });
</script>
@endsection

