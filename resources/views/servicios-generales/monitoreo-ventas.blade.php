@extends('app')

@section('content')
<div class="main-content"><div class="page-content"><div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div><h4 class="mb-1">Monitoreo de ventas CMS</h4><p class="text-muted mb-0">Alertas diarias de Lotobet Real por agencia. Los cierres son manuales.</p></div>
        <a href="{{ route('servicios-generales.index') }}" class="btn btn-outline-secondary btn-sm">Servicios Generales</a>
    </div>

    @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
    @if($errors->any())<div class="alert alert-danger"><ul class="mb-0">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif

    <div class="row g-3 mb-3">
        @foreach($umbrales as $categoria => $umbral)
            <div class="col-md-4"><div class="card mb-0"><div class="card-body"><div class="text-muted">Umbral diario {{ str_replace('_', ' ', $categoria) }}</div><div class="fs-4 fw-semibold">RD$ {{ number_format($umbral, 0) }}</div></div></div></div>
        @endforeach
    </div>

    @if($puedeAdministrar)
        <div class="card mb-3"><div class="card-body">
            <h5 class="card-title">Asignar asesor CMS</h5>
            <form method="post" action="{{ route('servicios-generales.monitoreo-ventas.asignar') }}" class="row g-2 align-items-end">
                @csrf
                <div class="col-md-3"><label class="form-label" for="central">Central</label><select id="central" name="central" class="form-select" required><option value="">Seleccione</option>@foreach($centrales as $central)<option value="{{ $central }}" @selected(old('central') === $central)>{{ $central }}</option>@endforeach</select></div>
                <div class="col-md-3"><label class="form-label" for="grupo">Grupo</label><select id="grupo" name="grupo" class="form-select" required><option value="">Seleccione</option>@foreach($grupos as $grupo)<option value="{{ $grupo }}" @selected(old('grupo') === $grupo)>{{ $grupo }}</option>@endforeach</select></div>
                <div class="col-md-4"><label class="form-label" for="asesor_id">Asesor</label><select id="asesor_id" name="asesor_id" class="form-select" required><option value="">Seleccione</option>@foreach($asesores as $asesor)<option value="{{ $asesor->id }}" @selected(old('asesor_id') == $asesor->id)>{{ $asesor->name }}</option>@endforeach</select></div>
                <div class="col-md-2"><button class="btn btn-primary w-100">Guardar</button></div>
            </form>
            @if($asignaciones->isNotEmpty())<div class="small text-muted mt-3">Asignaciones: @foreach($asignaciones as $asignacion)<span class="badge bg-light text-dark me-1">{{ $asignacion->central }} / {{ $asignacion->grupo }} → {{ $asignacion->asesor?->name }}</span>@endforeach</div>@endif
        </div></div>
    @endif

    @if($puedeAdministrar)
        <div class="card mb-3"><div class="card-body"><h5 class="card-title">Rutas críticas · {{ $fechaRutas ? \Carbon\Carbon::parse($fechaRutas)->format('d/m/Y') : 'sin fecha' }}</h5>
            <p class="text-muted mb-2">Dos o más agencias distintas con alerta en la misma ruta y fecha.</p>
            @forelse($rutasCriticas as $ruta)<span class="badge bg-danger me-2 mb-1">{{ $ruta->ruta }}: {{ $ruta->agencias_alerta }} agencias</span>@empty<span class="text-muted">No hay rutas críticas para esta fecha.</span>@endforelse
        </div></div>
    @endif

    <div class="card"><div class="card-body">
        <div class="d-flex justify-content-between align-items-center mb-3"><h5 class="card-title mb-0">Casos ({{ $casos->total() }})</h5>
            <form method="get" class="d-flex gap-2"><input type="date" name="fecha" value="{{ request('fecha') }}" class="form-control" aria-label="Fecha"><select name="estado" class="form-select" aria-label="Estado"><option value="">Todos</option><option value="pendiente" @selected(request('estado') === 'pendiente')>Pendientes</option><option value="en_seguimiento" @selected(request('estado') === 'en_seguimiento')>En seguimiento</option><option value="cerrado" @selected(request('estado') === 'cerrado')>Cerrados</option></select><button class="btn btn-outline-primary">Filtrar</button></form>
        </div>
        <div class="table-responsive"><table class="table table-hover align-middle"><thead><tr><th>Fecha / agencia</th><th>Central / grupo / ruta</th><th>Tradicional</th><th>No tradicional</th><th>Recargas</th><th>Asesor / estado</th><th>Gestión</th></tr></thead><tbody>
        @forelse($casos as $caso)
            <tr>
                <td>{{ $caso->fecha->format('d/m/Y') }}<br><strong>{{ $caso->agencia?->terminal }}</strong> {{ $caso->agencia?->nombre_agencia }}<br><small class="text-muted">Alertas históricas: {{ $reincidencias[$caso->agencia_id] ?? 1 }}</small></td>
                <td>{{ $caso->central }}<br>{{ $caso->grupo }} / {{ $caso->ruta }}</td>
                @foreach($umbrales as $categoria => $umbral)<td class="{{ $caso->{$categoria} < $umbral ? 'text-danger fw-semibold' : 'text-success' }}">RD$ {{ number_format($caso->{$categoria}, 2) }}</td>@endforeach
                <td>{{ $caso->asesor?->name ?? 'Sin asignar' }}<br><span class="badge {{ $caso->estado === 'cerrado' ? 'bg-success' : 'bg-warning text-dark' }}">{{ str_replace('_', ' ', $caso->estado) }}</span></td>
                <td><button class="btn btn-sm btn-outline-primary" type="button" data-bs-toggle="collapse" data-bs-target="#caso-{{ $caso->id }}" aria-expanded="false">Ver / gestionar</button></td>
            </tr>
            <tr class="collapse" id="caso-{{ $caso->id }}"><td colspan="7">
                @if($caso->estado !== 'cerrado' && (int) $caso->asesor_id === (int) auth()->id())
                    <form method="post" action="{{ route('servicios-generales.monitoreo-ventas.seguimiento', $caso) }}" class="row g-2 mb-3">@csrf
                        <div class="col-md-4"><label class="form-label">Causa</label><textarea name="causa" class="form-control" rows="2" required>{{ $caso->causa }}</textarea></div>
                        <div class="col-md-4"><label class="form-label">Gestión</label><textarea name="gestion" class="form-control" rows="2" required>{{ $caso->gestion }}</textarea></div>
                        <div class="col-md-4"><label class="form-label">Resultado</label><textarea name="resultado" class="form-control" rows="2" required>{{ $caso->resultado }}</textarea></div>
                        <div class="col-12"><button class="btn btn-outline-primary btn-sm">Guardar seguimiento</button> @if((int) $caso->asesor_id === (int) auth()->id())<button name="cerrar" value="1" class="btn btn-success btn-sm">Guardar y cerrar caso</button>@endif</div>
                    </form>
                @else
                    <p class="mb-2"><strong>Causa:</strong> {{ $caso->causa ?? 'Sin registrar' }}<br><strong>Gestión:</strong> {{ $caso->gestion ?? 'Sin registrar' }}<br><strong>Resultado:</strong> {{ $caso->resultado ?? 'Sin registrar' }}</p>
                @endif
                <strong>Historial</strong><ul class="mb-0">@foreach($caso->seguimientos as $seguimiento)<li>{{ $seguimiento->created_at->format('d/m/Y H:i') }} · {{ $seguimiento->usuario?->name ?? 'Sistema' }} · {{ $seguimiento->accion }}: {{ $seguimiento->detalle }}</li>@endforeach</ul>
            </td></tr>
        @empty<tr><td colspan="7" class="text-center text-muted py-4">No hay casos para estos filtros.</td></tr>@endforelse
        </tbody></table></div>{{ $casos->links() }}
    </div></div>
</div></div></div>
@endsection
