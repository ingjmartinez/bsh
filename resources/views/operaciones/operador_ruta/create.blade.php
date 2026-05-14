@extends('app')

@section('content')
    <div class="main-content">
        <div class="page-content">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-12">
                        <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                            <h4 class="mb-sm-0">Nuevo Operador_Ruta</h4>
                            <div class="page-title-right">
                                <ol class="breadcrumb m-0">
                                    <li class="breadcrumb-item"><a href="{{ route('operaciones.index') }}">Operaciones</a></li>
                                    <li class="breadcrumb-item"><a href="{{ route('operador-ruta.index') }}">Operador_Ruta</a></li>
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
                                <form action="{{ route('operador-ruta.store') }}" method="POST">
                                    @csrf

                                    <div class="row g-3">
                                        <div class="col-md-6">
                                            <label class="form-label">Nombre <span class="text-danger">*</span></label>
                                            <input type="text" name="nombre" class="form-control @error('nombre') is-invalid @enderror" value="{{ old('nombre') }}" required>
                                            @error('nombre')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                        </div>

                                        <div class="col-md-6">
                                            <label class="form-label">Apellido <span class="text-danger">*</span></label>
                                            <input type="text" name="apellido" class="form-control @error('apellido') is-invalid @enderror" value="{{ old('apellido') }}" required>
                                            @error('apellido')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                        </div>

                                        <div class="col-md-6">
                                            <label class="form-label">Correo <span class="text-danger">*</span></label>
                                            <input type="email" name="correo" class="form-control @error('correo') is-invalid @enderror" value="{{ old('correo') }}" required>
                                            @error('correo')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                        </div>

                                        <div class="col-md-6">
                                            <label class="form-label">Cédula <span class="text-danger">*</span></label>
                                            <input type="text" name="cedula" class="form-control @error('cedula') is-invalid @enderror" value="{{ old('cedula') }}" inputmode="numeric" pattern="[0-9]{11}" oninput="this.value=this.value.replace(/\D/g,''); this.setCustomValidity('')" oninvalid="if(this.validity.valueMissing){this.setCustomValidity('Campo de 11 Digitos obligatorios')}else if(this.value.length < 11){this.setCustomValidity('Faltan digitos: la cedula debe tener 11')}else if(this.value.length > 11){this.setCustomValidity('Tiene digitos de mas: la cedula debe tener 11')}else{this.setCustomValidity('Campo de 11 Digitos obligatorios')}" required>
                                            <div class="form-text">Campo de 11 Digitos obligatorios</div>
                                            @error('cedula')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                        </div>

                                        <div class="col-md-6">
                                            <label class="form-label">Teléfono <span class="text-danger">*</span></label>
                                            <input type="text" name="telefono" class="form-control @error('telefono') is-invalid @enderror" value="{{ old('telefono') }}" inputmode="numeric" pattern="[0-9]{10}" oninput="this.value=this.value.replace(/\D/g,''); this.setCustomValidity('')" oninvalid="if(this.validity.valueMissing){this.setCustomValidity('Campo de 10 Digitos obligatorios')}else if(this.value.length < 10){this.setCustomValidity('Faltan digitos: el telefono debe tener 10')}else if(this.value.length > 10){this.setCustomValidity('Tiene digitos de mas: el telefono debe tener 10')}else{this.setCustomValidity('Campo de 10 Digitos obligatorios')}" required>
                                            <div class="form-text">Campo de 10 Digitos obligatorios</div>
                                            @error('telefono')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                        </div>

                                        <div class="col-md-6">
                                            <label class="form-label">Puesto <span class="text-danger">*</span></label>
                                            <input type="hidden" name="puesto" value="operador">
                                            <input type="text" class="form-control" value="Operador" readonly>
                                            @error('puesto')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                                        </div>
                                    </div>

                                    <div class="d-flex justify-content-end gap-2 mt-4">
                                        <a href="{{ route('operador-ruta.index') }}" class="btn btn-secondary">Cancelar</a>
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
