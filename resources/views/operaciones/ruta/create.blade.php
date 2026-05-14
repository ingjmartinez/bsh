@extends('app')

@section('content')
    <div class="main-content">
        <div class="page-content">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-12">
                        <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                            <h4 class="mb-sm-0">Nueva Ruta</h4>
                            <div class="page-title-right">
                                <ol class="breadcrumb m-0">
                                    <li class="breadcrumb-item"><a href="{{ route('operaciones.index') }}">Operaciones</a></li>
                                    <li class="breadcrumb-item"><a href="{{ route('ruta.index') }}">Ruta</a></li>
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
                                <form action="{{ route('ruta.store') }}" method="POST">
                                    @csrf

                                    <div class="row g-3">
                                        <div class="col-md-6">
                                            <label class="form-label">Nombre de Ruta <span class="text-danger">*</span></label>
                                            <input type="text" name="nombre_ruta" class="form-control @error('nombre_ruta') is-invalid @enderror" value="{{ old('nombre_ruta') }}" oninput="this.value = this.value.toUpperCase()" required>
                                            @error('nombre_ruta')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                        </div>

                                        <div class="col-md-6">
                                            <label class="form-label">Empresa <span class="text-danger">*</span></label>
                                            <select name="empresa" class="form-select @error('empresa') is-invalid @enderror" required>
                                                <option value="">Seleccione una empresa</option>
                                                @foreach (($empresas ?? []) as $empresa)
                                                    <option value="{{ $empresa }}" @selected(old('empresa') === $empresa)>{{ $empresa }}</option>
                                                @endforeach
                                            </select>
                                            @error('empresa')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                        </div>

                                        <div class="col-md-6">
                                            <label class="form-label">Operador asignado <span class="text-danger">*</span></label>
                                            <select name="operador_ruta_id" class="form-select @error('operador_ruta_id') is-invalid @enderror" required>
                                                <option value="">Seleccione un operador</option>
                                                @foreach (($operadores ?? []) as $operador)
                                                    <option value="{{ $operador->id }}" @selected((string) old('operador_ruta_id') === (string) $operador->id)>
                                                        {{ trim(($operador->nombre ?? '') . ' ' . ($operador->apellido ?? '')) }}
                                                    </option>
                                                @endforeach
                                            </select>
                                            @error('operador_ruta_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                        </div>
                                    </div>

                                    <div class="d-flex justify-content-end gap-2 mt-4">
                                        <a href="{{ route('ruta.index') }}" class="btn btn-secondary">Cancelar</a>
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
