@extends('app')

@section('content')
<div class="main-content"><div class="page-content"><div class="container-fluid">
    <div class="page-title-box d-sm-flex align-items-center justify-content-between">
        <div><h4 class="mb-1">Supervisión de rutas</h4><p class="text-muted mb-0">Agencias visitadas y pendientes por responsable.</p></div>
        <a href="{{ route('servicios-generales.mi-ruta.index') }}" class="btn btn-primary"><i class="ri-route-line me-1"></i>Ir a Mi ruta</a>
    </div>

    @if($setupPending)
        <div class="alert alert-warning">La estructura de rutas todavía no está instalada.</div>
    @else
        @php
            $datosModal = [];
            foreach ($resumenUsuarios as $persona) {
                $filas = $persona->detalle->map(function ($detalle) {
                    $agencia = $detalle->agencia;
                    $visita = $detalle->visita;
                    return [
                        'id' => $agencia->id,
                        'terminal' => $agencia->terminal ?: $agencia->agencia,
                        'nombre' => $agencia->nombre_agencia ?? '',
                        'visitada' => (bool) $detalle->visitada,
                        'resultado' => !$detalle->visitada ? 'Por visitar' : ($visita?->conforme ? 'Todo en orden' : 'Con novedad'),
                        'hora' => $visita?->check_out_at?->format('h:i A') ?: '-',
                    ];
                })->values();
                $datosModal[$persona->id] = [
                    'nombre' => $persona->nombre,
                    'todas' => $filas,
                    'visitadas' => $filas->where('visitada', true)->values(),
                    'pendientes' => $filas->where('visitada', false)->values(),
                ];
            }
        @endphp

        <div class="card mb-3">
            <div class="card-body d-flex flex-wrap align-items-end gap-3">
                <form method="GET" class="d-flex align-items-end gap-2">
                    <div><label class="form-label">Fecha de la ruta</label><input type="date" name="fecha" class="form-control" value="{{ $fecha }}"></div>
                    <button class="btn btn-primary">Consultar</button>
                </form>
                <div class="ms-md-auto d-flex flex-wrap gap-2">
                    <span class="badge bg-primary-subtle text-primary fs-6 px-3 py-2">Agencias: {{ $statsRuta['agencias'] }}</span>
                    <span class="badge bg-success-subtle text-success fs-6 px-3 py-2">Visitadas: {{ $statsRuta['visitadas'] }}</span>
                    <span class="badge bg-warning-subtle text-warning fs-6 px-3 py-2">Por visitar: {{ $statsRuta['pendientes'] }}</span>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-1">Rendimiento por responsable</h5>
                <p class="text-muted mb-0 small">Selecciona cualquier cantidad para consultar los IDs de terminales.</p>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table id="tablaSupervisionRutas" class="table table-bordered table-hover align-middle w-100">
                        <thead class="table-light">
                            <tr>
                                <th>Nombre</th>
                                <th>Cargo</th>
                                <th class="text-center">Agencias asignadas</th>
                                <th class="text-center">Visitadas</th>
                                <th class="text-center">Por visitar</th>
                                <th>Avance</th>
                            </tr>
                        </thead>
                        <tbody>
                        @foreach($resumenUsuarios as $persona)
                            @php $porcentaje = $persona->total ? round(($persona->visitadas / $persona->total) * 100) : 0; @endphp
                            <tr>
                                <td><strong>{{ $persona->nombre }}</strong></td>
                                <td>{{ $persona->cargo ?: 'Responsable de ruta' }}</td>
                                <td class="text-center" data-order="{{ $persona->total }}">
                                    <button type="button" class="btn btn-sm btn-outline-primary btn-ver-terminales" data-persona="{{ $persona->id }}" data-tipo="todas">
                                        <i class="ri-building-line me-1"></i>{{ $persona->total }}
                                    </button>
                                </td>
                                <td class="text-center" data-order="{{ $persona->visitadas }}">
                                    <button type="button" class="btn btn-sm btn-outline-success btn-ver-terminales" data-persona="{{ $persona->id }}" data-tipo="visitadas">
                                        <i class="ri-checkbox-circle-line me-1"></i>{{ $persona->visitadas }}
                                    </button>
                                </td>
                                <td class="text-center" data-order="{{ $persona->pendientes }}">
                                    <button type="button" class="btn btn-sm btn-outline-warning btn-ver-terminales" data-persona="{{ $persona->id }}" data-tipo="pendientes">
                                        <i class="ri-time-line me-1"></i>{{ $persona->pendientes }}
                                    </button>
                                </td>
                                <td data-order="{{ $porcentaje }}">
                                    <div class="d-flex align-items-center gap-2"><div class="progress flex-grow-1" style="height:8px;min-width:90px"><div class="progress-bar bg-{{ $porcentaje===100?'success':'primary' }}" style="width:{{ $porcentaje }}%"></div></div><span class="small fw-semibold">{{ $porcentaje }}%</span></div>
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @endif
</div></div></div>

@unless($setupPending)
<div class="modal fade" id="terminalesRutaModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable"><div class="modal-content">
        <div class="modal-header"><div><h5 class="modal-title" id="terminalesRutaTitulo">Detalle de agencias</h5><small class="text-muted" id="terminalesRutaSubtitulo"></small></div><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
        <div class="modal-body p-0">
            <div class="table-responsive"><table class="table table-hover align-middle mb-0">
                <thead class="table-light sticky-top"><tr><th class="ps-3">ID</th><th>ID de terminal</th><th>Agencia</th><th>Resultado</th><th class="pe-3">Hora</th></tr></thead>
                <tbody id="terminalesRutaBody"></tbody>
            </table></div>
        </div>
        <div class="modal-footer"><span class="me-auto text-muted" id="terminalesRutaTotal"></span><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button></div>
    </div></div>
</div>
@endunless
@endsection

@section('script')
@unless($setupPending)
<script>
document.addEventListener('DOMContentLoaded', function () {
    const datosRutas = @json($datosModal);
    const modal = new bootstrap.Modal(document.getElementById('terminalesRutaModal'));
    const body = document.getElementById('terminalesRutaBody');
    const titulo = document.getElementById('terminalesRutaTitulo');
    const subtitulo = document.getElementById('terminalesRutaSubtitulo');
    const total = document.getElementById('terminalesRutaTotal');
    const etiquetas = {todas: 'Agencias asignadas', visitadas: 'Agencias visitadas', pendientes: 'Agencias por visitar'};
    const escapar = valor => String(valor ?? '').replace(/[&<>'"]/g, caracter => ({'&':'&amp;','<':'&lt;','>':'&gt;',"'":'&#039;','"':'&quot;'}[caracter]));

    document.querySelectorAll('.btn-ver-terminales').forEach(button => button.addEventListener('click', function () {
        const persona = datosRutas[this.dataset.persona];
        const tipo = this.dataset.tipo;
        const filas = persona?.[tipo] || [];
        titulo.textContent = etiquetas[tipo] || 'Detalle de agencias';
        subtitulo.textContent = persona?.nombre || '';
        total.textContent = `${filas.length} agencia(s)`;
        body.innerHTML = filas.length ? filas.map(fila => {
            const badge = !fila.visitada ? 'warning' : (fila.resultado === 'Todo en orden' ? 'success' : 'danger');
            return `<tr><td class="ps-3">${escapar(fila.id)}</td><td><strong>${escapar(fila.terminal)}</strong></td><td>${escapar(fila.nombre || '-')}</td><td><span class="badge bg-${badge}-subtle text-${badge}">${escapar(fila.resultado)}</span></td><td class="pe-3">${escapar(fila.hora)}</td></tr>`;
        }).join('') : '<tr><td colspan="5" class="text-center text-muted py-4">No hay agencias en esta categoría.</td></tr>';
        modal.show();
    }));

    if (window.jQuery && $.fn.DataTable) {
        $('#tablaSupervisionRutas').DataTable({
            pageLength: 15,
            order: [[4, 'desc']],
            responsive: true,
            language: {
                decimal: '', emptyTable: 'No hay responsables con agencias asignadas', info: 'Mostrando _START_ a _END_ de _TOTAL_ responsables',
                infoEmpty: 'Mostrando 0 responsables', infoFiltered: '(filtrado de _MAX_)', lengthMenu: 'Mostrar _MENU_',
                loadingRecords: 'Cargando...', processing: 'Procesando...', search: 'Buscar:', zeroRecords: 'No se encontraron resultados',
                paginate: {first: 'Primero', last: 'Último', next: 'Siguiente', previous: 'Anterior'}
            },
            columnDefs: [{targets: [2,3,4], className: 'text-center'}]
        });
    }
});
</script>
@endunless
@endsection
