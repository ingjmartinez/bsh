@extends('app')

@section('content')
    @php
        $oldHorarioAm = old('horario_am');
        $oldHorarioPm = old('horario_pm');

        $amPartes = explode('/', (string) $oldHorarioAm);
        $amInicioTs = isset($amPartes[0]) ? strtotime(trim($amPartes[0])) : false;
        $amFinTs = isset($amPartes[1]) ? strtotime(trim($amPartes[1])) : false;
        $amInicio = $amInicioTs ? date('H:i', $amInicioTs) : '';
        $amFin = $amFinTs ? date('H:i', $amFinTs) : '';

        $pmPartes = explode('/', (string) $oldHorarioPm);
        $pmInicioTs = isset($pmPartes[0]) ? strtotime(trim($pmPartes[0])) : false;
        $pmFinTs = isset($pmPartes[1]) ? strtotime(trim($pmPartes[1])) : false;
        $pmInicio = $pmInicioTs ? date('H:i', $pmInicioTs) : '';
        $pmFin = $pmFinTs ? date('H:i', $pmFinTs) : '';
    @endphp

    <div class="main-content">
        <div class="page-content">
            <div class="container-fluid">

                <!-- start page title -->
                <div class="row">
                    <div class="col-12">
                        <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                            <h4 class="mb-sm-0">Nueva Agencia</h4>

                            <div class="page-title-right">
                                <ol class="breadcrumb m-0">
                                    <li class="breadcrumb-item"><a href="{{ route('inicio.index') }}">Inicio</a></li>
                                    <li class="breadcrumb-item"><a href="{{ route('mantenimiento.index') }}">Mantenimientos</a></li>
                                    <li class="breadcrumb-item"><a href="{{ route('agencias.index') }}">Agencias</a></li>
                                    <li class="breadcrumb-item active">Nueva</li>
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
                                <h5 class="card-title mb-0">Datos de la Agencia</h5>
                            </div>
                            <div class="card-body">
                                <form action="{{ route('agencias.store') }}" method="POST">
                                    @csrf
                                    
                                    <div class="row">
                                        <div class="col-12 col-md-6 mb-3">
                                            <label for="agencia" class="form-label">Agencia <span class="text-danger">*</span></label>
                                            <input type="text" class="form-control @error('agencia') is-invalid @enderror" 
                                                   id="agencia" name="agencia" value="{{ old('agencia') }}" required>
                                            @error('agencia')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <div class="col-12 col-md-6 mb-3">
                                            <label for="nombre_agencia" class="form-label">Nombre Agencia</label>
                                            <input type="text" class="form-control @error('nombre_agencia') is-invalid @enderror" 
                                                   id="nombre_agencia" name="nombre_agencia" value="{{ old('nombre_agencia') }}">
                                            @error('nombre_agencia')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <div class="col-12 col-md-6 mb-3">
                                            <label for="terminal" class="form-label">Terminal</label>
                                            <input type="text" class="form-control @error('terminal') is-invalid @enderror" 
                                                   id="terminal" name="terminal" value="{{ old('terminal') }}">
                                            @error('terminal')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <div class="col-12 col-md-6 mb-3">
                                            <label for="horario_am" class="form-label">Horario AM</label>
                                            <div class="row g-2">
                                                <div class="col-6">
                                                    <input type="time" class="form-control" id="horario_am_inicio" value="{{ $amInicio }}">
                                                    <small class="text-muted">Hora inicio</small>
                                                </div>
                                                <div class="col-6">
                                                    <input type="time" class="form-control" id="horario_am_fin" value="{{ $amFin }}">
                                                    <small class="text-muted">Hora fin</small>
                                                </div>
                                            </div>
                                            <input type="hidden" id="horario_am" name="horario_am" value="{{ old('horario_am') }}">
                                            @error('horario_am')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <div class="col-12 col-md-6 mb-3">
                                            <label for="horario_pm" class="form-label">Horario PM</label>
                                            <div class="row g-2">
                                                <div class="col-6">
                                                    <input type="time" class="form-control" id="horario_pm_inicio" value="{{ $pmInicio }}">
                                                    <small class="text-muted">Hora inicio</small>
                                                </div>
                                                <div class="col-6">
                                                    <input type="time" class="form-control" id="horario_pm_fin" value="{{ $pmFin }}">
                                                    <small class="text-muted">Hora fin</small>
                                                </div>
                                            </div>
                                            <input type="hidden" id="horario_pm" name="horario_pm" value="{{ old('horario_pm') }}">
                                            @error('horario_pm')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <div class="col-12 col-md-6 mb-3">
                                            <label for="sistema" class="form-label">Sistema</label>
                                            <input type="text" class="form-control @error('sistema') is-invalid @enderror" 
                                                   id="sistema" name="sistema" value="{{ old('sistema') }}">
                                            @error('sistema')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <div class="col-12 col-md-6 mb-3">
                                            <label for="empresa" class="form-label">Empresa</label>
                                            <input type="text" class="form-control @error('empresa') is-invalid @enderror"
                                                   id="empresa" name="empresa" maxlength="60" value="{{ old('empresa') }}">
                                            @error('empresa')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <div class="col-12 col-md-6 mb-3">
                                            <label for="ciudad" class="form-label">Ciudad</label>
                                            <input type="text" class="form-control @error('ciudad') is-invalid @enderror" 
                                                   id="ciudad" name="ciudad" value="{{ old('ciudad') }}">
                                            @error('ciudad')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <div class="col-12 col-12 col-md-6 mb-3">
                                            <label for="ruta" class="form-label">Ruta</label>
                                            <input type="text" class="form-control @error('ruta') is-invalid @enderror" 
                                                   id="ruta" name="ruta" value="{{ old('ruta') }}">
                                            @error('ruta')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <div class="col-12 col-md-6 mb-3">
                                            <label for="operador" class="form-label">Operador</label>
                                            <select class="form-select @error('operador') is-invalid @enderror" id="operador" name="operador">
                                                <option value="">Seleccione un operador</option>
                                                @foreach(($operadores ?? []) as $operador)
                                                    <option value="{{ $operador }}" {{ old('operador') === $operador ? 'selected' : '' }}>
                                                        {{ $operador }}
                                                    </option>
                                                @endforeach
                                            </select>
                                            @error('operador')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <div class="col-12 col-md-6 mb-3">
                                            <label for="coordinador" class="form-label">Coordinador</label>
                                            <select class="form-select @error('coordinador') is-invalid @enderror" id="coordinador" name="coordinador">
                                                <option value="">Seleccione un coordinador</option>
                                                @foreach(($coordinadores ?? []) as $coordinador)
                                                    <option value="{{ $coordinador }}" {{ old('coordinador') === $coordinador ? 'selected' : '' }}>
                                                        {{ $coordinador }}
                                                    </option>
                                                @endforeach
                                            </select>
                                            @error('coordinador')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <div class="col-12 col-md-6 mb-3">
                                            <label for="estatus" class="form-label">Estatus <span class="text-danger">*</span></label>
                                            <select class="form-select @error('estatus') is-invalid @enderror" id="estatus" name="estatus" required>
                                                <option value="1" {{ old('estatus', '1') == '1' ? 'selected' : '' }}>Activo</option>
                                                <option value="0" {{ old('estatus') == '0' ? 'selected' : '' }}>Inactivo</option>
                                            </select>
                                            @error('estatus')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <div class="col-12 col-md-6 mb-3">
                                            <label for="aplica_incentivo" class="form-label">Aplica incentivo <span class="text-danger">*</span></label>
                                            <select class="form-select @error('aplica_incentivo') is-invalid @enderror" id="aplica_incentivo" name="aplica_incentivo" required>
                                                <option value="1" {{ old('aplica_incentivo', '1') == '1' ? 'selected' : '' }}>SÃ­</option>
                                                <option value="0" {{ old('aplica_incentivo') == '0' ? 'selected' : '' }}>No</option>
                                            </select>
                                            @error('aplica_incentivo')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>

                                    <div class="d-flex flex-column flex-sm-row justify-content-end gap-2 mt-3">
                                        <a href="{{ route('agencias.index') }}" class="btn btn-secondary">
                                            <i class="ri-close-line align-bottom me-1"></i> Cancelar
                                        </a>
                                        <button type="submit" class="btn btn-primary">
                                            <i class="ri-save-line align-bottom me-1"></i> Guardar
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
    function toAmPm(hhmm) {
        if (!hhmm) return '';
        const [hStr, mStr] = hhmm.split(':');
        let h = parseInt(hStr, 10);
        const suffix = h >= 12 ? 'PM' : 'AM';
        h = h % 12;
        if (h === 0) h = 12;
        return `${h}:${mStr} ${suffix}`;
    }

    function buildRange(startSelector, endSelector) {
        const start = $(startSelector).val();
        const end = $(endSelector).val();
        if (!start && !end) return '';
        if (!start || !end) return '';
        return `${toAmPm(start)} / ${toAmPm(end)}`;
    }

    function syncHorarios() {
        $('#horario_am').val(buildRange('#horario_am_inicio', '#horario_am_fin'));
        $('#horario_pm').val(buildRange('#horario_pm_inicio', '#horario_pm_fin'));
    }

    $(document).ready(function () {
        syncHorarios();
        $('#horario_am_inicio, #horario_am_fin, #horario_pm_inicio, #horario_pm_fin').on('change', syncHorarios);
        $('form').on('submit', syncHorarios);
    });
</script>
@endsection

