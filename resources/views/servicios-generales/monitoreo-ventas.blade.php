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
        <div class="col-md-4"><div class="card mb-0"><div class="card-body"><div class="text-muted">Asesores agregados</div><div class="fs-4 fw-semibold">{{ $asesoresAsignados }}</div></div></div></div>
        <div class="col-md-4"><div class="card mb-0"><div class="card-body"><div class="text-muted">Nivel de cumplimiento · {{ $fechaComparativa ? \Carbon\Carbon::parse($fechaComparativa)->format('d/m/Y') : 'sin fecha' }}</div><div class="fs-4 fw-semibold">{{ $nivelCumplimiento === null ? 'Sin datos' : number_format($nivelCumplimiento, 1).'%' }}</div></div></div></div>
        @if($puedeAdministrar)<div class="col-md-4"><div class="card mb-0"><div class="card-body d-flex align-items-center"><button type="button" class="btn btn-outline-primary w-100" data-bs-toggle="modal" data-bs-target="#configurarUmbrales">Configurar umbrales por asesor y producto</button></div></div></div>@endif
    </div>

    @if($puedeAdministrar)
        <div class="modal fade" id="configurarUmbrales" tabindex="-1" aria-labelledby="configurarUmbralesTitulo" aria-hidden="true"><div class="modal-dialog"><div class="modal-content">
            <div class="modal-header"><h5 class="modal-title" id="configurarUmbralesTitulo">Configurar umbrales diarios</h5><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button></div>
            <form method="post" action="{{ route('servicios-generales.monitoreo-ventas.umbrales') }}">@csrf<div class="modal-body">
                <label class="form-label" for="umbral_asesor_id">Asesor</label><select id="umbral_asesor_id" name="asesor_id" class="form-select mb-3" required><option value="">Seleccione</option>@foreach($asesores as $asesor)<option value="{{ $asesor->id }}" @selected(old('asesor_id') == $asesor->id)>{{ $asesor->name }}</option>@endforeach</select>
                @foreach($umbrales as $categoria => $umbral)<label class="form-label" for="umbral_{{ $categoria }}">{{ ucfirst(str_replace('_', ' ', $categoria)) }} · RD$</label><input id="umbral_{{ $categoria }}" name="umbrales[{{ $categoria }}]" type="number" step="0.01" min="0" value="{{ old('umbrales.'.$categoria, $umbral) }}" class="form-control mb-3" required>@endforeach
            </div><div class="modal-footer"><button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button><button class="btn btn-primary">Guardar parámetros</button></div></form>
        </div></div></div>
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const selector = document.getElementById('umbral_asesor_id');
                const configuraciones = @json($configuraciones->map(fn ($filas) => $filas->pluck('monto', 'categoria')));
                const valoresBase = @json($umbrales);
                selector.addEventListener('change', function () {
                    Object.entries(valoresBase).forEach(function ([categoria, valor]) {
                        document.getElementById('umbral_' + categoria).value = configuraciones[selector.value]?.[categoria] ?? valor;
                    });
                });
            });
        </script>
    @endif

    @if($puedeAdministrar)
        <div class="card mb-3"><div class="card-body">
            <div class="d-flex justify-content-between align-items-center mb-3"><div><h5 class="card-title mb-1">Comparativa de terminales por producto</h5><small class="text-muted">Ventas del {{ $fechaComparativa ? \Carbon\Carbon::parse($fechaComparativa)->format('d/m/Y') : 'día seleccionado' }} · cada terminal se compara con la meta de su asesor.</small></div><form method="get" class="d-flex gap-2"><input type="date" name="fecha" value="{{ request('fecha', $fechaComparativa) }}" class="form-control" aria-label="Fecha de comparativa"><button class="btn btn-outline-primary">Consultar</button></form></div>
            <div class="table-responsive"><table class="table table-hover align-middle"><thead><tr><th>Asesor</th><th>Producto</th><th>Meta diaria</th><th>Terminales asignadas</th><th>Cumplieron</th><th>No cumplieron</th><th>Terminales pendientes</th></tr></thead><tbody>
                @forelse($comparativaTerminales as $fila)<tr><td>{{ $fila['asesor'] }}</td><td>{{ ucfirst(str_replace('_', ' ', $fila['categoria'])) }}</td><td>RD$ {{ number_format($fila['meta'], 2) }}</td><td>{{ $fila['total'] }}</td><td class="text-success fw-semibold">{{ $fila['cumplen']->count() }}</td><td class="text-danger fw-semibold">{{ $fila['pendientes']->count() }}</td><td>@forelse($fila['pendientes'] as $terminal)<span class="badge bg-danger me-1 mb-1">{{ $terminal['terminal'] }} · RD$ {{ number_format($terminal['monto'], 2) }}</span>@empty<span class="text-muted">Ninguna</span>@endforelse</td></tr>
                @empty<tr><td colspan="7" class="text-center text-muted py-3">No hay asesores con terminales asignadas.</td></tr>@endforelse
            </tbody></table></div>
        </div></div>

        <div class="card mb-3"><div class="card-body">
            <h5 class="card-title">Umbrales configurados ({{ $configuraciones->count() }})</h5>
            <div class="table-responsive"><table class="table table-hover align-middle"><thead><tr><th>Asesor</th><th>Tradicional</th><th>No tradicional</th><th>Recargas</th><th>Asignación</th></tr></thead><tbody>
                @forelse($configuraciones as $asesorId => $metas)
                    <tr><td>{{ $metas->first()->asesor?->name ?? 'Asesor no disponible' }}</td>
                        @foreach($umbrales as $categoria => $umbral)<td>RD$ {{ number_format((float) ($metas->firstWhere('categoria', $categoria)?->monto ?? $umbral), 2) }}</td>@endforeach
                        <td>@forelse($asignaciones->where('asesor_id', $asesorId) as $asignacion)<span class="badge bg-light text-dark me-1">{{ $asignacion->central }} / {{ $asignacion->grupo }}</span>@empty<span class="text-muted">Sin central y grupo asignados</span>@endforelse</td>
                    </tr>
                @empty<tr><td colspan="5" class="text-center text-muted py-3">Todavía no hay umbrales configurados.</td></tr>@endforelse
            </tbody></table></div>
        </div></div>
    @endif

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
                @foreach($umbrales as $categoria => $umbral)@php($meta = (float) ($configuraciones->get($caso->asesor_id, collect())->firstWhere('categoria', $categoria)?->monto ?? $umbral))<td class="{{ $caso->{$categoria} < $meta ? 'text-danger fw-semibold' : 'text-success' }}">RD$ {{ number_format($caso->{$categoria}, 2) }}<br><small class="text-muted">Meta: RD$ {{ number_format($meta, 2) }}</small></td>@endforeach
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
