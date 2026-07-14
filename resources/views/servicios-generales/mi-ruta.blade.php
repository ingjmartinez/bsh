@extends('app')

@section('content')
<div class="main-content"><div class="page-content"><div class="container-fluid" style="max-width:760px">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div><h4 class="mb-1">Mi ruta de hoy</h4><div class="text-muted">{{ $persona ? trim($persona->nombre.' '.$persona->apellido) : 'Sin perfil vinculado' }} · {{ \Carbon\Carbon::parse($fecha)->format('d/m/Y') }}</div></div>
        <a href="{{ route('servicios-generales.ruta-inspeccion.index') }}" class="btn btn-outline-secondary btn-sm">Supervisión</a>
    </div>
    @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
    @if(session('error'))<div class="alert alert-danger">{{ session('error') }}</div>@endif
    @if($errors->any())<div class="alert alert-danger"><ul class="mb-0">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif

    @if(!$persona)
        <div class="card"><div class="card-body text-center py-5"><i class="ri-user-unfollow-line fs-1 text-warning"></i><h5 class="mt-3">Tu usuario no está vinculado a un responsable</h5><p class="text-muted mb-0">Vincula la cuenta desde Coordinador/Operador.</p></div></div>
    @else
        @php $visitadas=$visitas->filter(fn($v)=>$v->check_out_at)->count(); $pendientes=$visitas->count()-$visitadas; @endphp
        <div class="card mb-3"><div class="card-body">
            <div class="d-flex justify-content-between mb-2"><strong>Avance de la ruta</strong><strong>{{ $visitadas }}/{{ $visitas->count() }}</strong></div>
            <div class="progress" style="height:12px"><div class="progress-bar bg-success" style="width:{{ $visitas->count()?round(($visitadas/$visitas->count())*100):0 }}%"></div></div>
            <div class="d-flex justify-content-between mt-2 small"><span class="text-success">{{ $visitadas }} visitada(s)</span><span class="text-warning">{{ $pendientes }} pendiente(s)</span></div>
        </div></div>

        @forelse($visitas as $visita)
            @php $visitada=(bool)$visita->check_out_at; @endphp
            <div class="card border-start border-4 {{ $visitada?($visita->conforme?'border-success':'border-danger'):'border-primary' }}">
                <div class="card-body">
                    <div class="d-flex justify-content-between gap-2 mb-3">
                        <div><h5 class="mb-1">{{ $visita->agencia->terminal ?? 'Agencia' }}</h5><div class="text-muted">{{ $visita->agencia->nombre_agencia ?? $visita->agencia->agencia ?? '' }}</div></div>
                        @if(!$visitada)<span class="badge bg-warning-subtle text-warning align-self-start">Pendiente</span>
                        @elseif($visita->conforme)<span class="badge bg-success align-self-start">Todo en orden</span>
                        @else<span class="badge bg-danger align-self-start">Con novedad</span>@endif
                    </div>

                    @if(!$visita->check_in_at)
                        <form action="{{ route('servicios-generales.mi-ruta.iniciar',$visita) }}" method="POST" class="form-iniciar">@csrf
                            <input type="hidden" name="latitud"><input type="hidden" name="longitud">
                            <button class="btn btn-primary btn-lg w-100"><i class="ri-map-pin-user-line me-1"></i>Visitar esta agencia</button>
                        </form>
                    @elseif(!$visitada)
                        <div class="alert alert-info py-2"><i class="ri-map-pin-line me-1"></i>Visita iniciada a las {{ $visita->check_in_at->format('h:i A') }}. Verifica la agencia.</div>
                        <form action="{{ route('servicios-generales.mi-ruta.resultado',$visita) }}" method="POST" class="mb-2">@csrf
                            <input type="hidden" name="resultado" value="orden">
                            <button class="btn btn-success btn-lg w-100"><i class="ri-checkbox-circle-line me-1"></i>Todo está en orden</button>
                        </form>
                        <button class="btn btn-outline-danger w-100" data-bs-toggle="collapse" data-bs-target="#novedad{{ $visita->id }}"><i class="ri-error-warning-line me-1"></i>Hay una novedad</button>
                        <div class="collapse mt-3" id="novedad{{ $visita->id }}">
                            <form action="{{ route('servicios-generales.mi-ruta.resultado',$visita) }}" method="POST" enctype="multipart/form-data" class="border rounded p-3 bg-light">@csrf
                                <input type="hidden" name="resultado" value="novedad">
                                <div class="mb-3"><label class="form-label">Describe la novedad</label><textarea class="form-control" name="observacion" rows="3" required></textarea></div>
                                <div class="mb-3"><label class="form-label">Fotografía</label><input type="file" class="form-control" name="evidencia" accept="image/*" capture="environment" required></div>
                                <button class="btn btn-danger w-100">Registrar novedad y continuar</button>
                            </form>
                        </div>
                    @else
                        <div class="d-flex justify-content-between small text-muted"><span><i class="ri-check-line me-1"></i>Visitada</span><span>{{ $visita->check_out_at->format('h:i A') }}</span></div>
                        @if(!$visita->conforme && data_get($visita->metadata,'observacion_visita'))<div class="alert alert-danger mt-3 mb-0"><strong>Novedad:</strong> {{ data_get($visita->metadata,'observacion_visita') }}</div>@endif
                    @endif
                </div>
            </div>
        @empty
            <div class="card"><div class="card-body text-center py-5"><i class="ri-calendar-check-line fs-1 text-muted"></i><h5 class="mt-3">No tienes agencias para visitar hoy</h5><p class="text-muted mb-0">La ruta se genera automáticamente a las 5:00 AM.</p></div></div>
        @endforelse
    @endif
</div></div></div>
@endsection

@section('script')
<script>
document.querySelectorAll('.form-iniciar').forEach(form=>form.addEventListener('submit',function(e){
    if(!navigator.geolocation||this.dataset.geoReady==='1') return;
    e.preventDefault(); const f=this;
    navigator.geolocation.getCurrentPosition(p=>{f.querySelector('[name=latitud]').value=p.coords.latitude;f.querySelector('[name=longitud]').value=p.coords.longitude;f.dataset.geoReady='1';f.submit();},()=>{f.dataset.geoReady='1';f.submit();},{enableHighAccuracy:true,timeout:6000});
}));
</script>
@endsection
