@extends('app')

@section('content')
    <div class="main-content">
        <div class="page-content">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-12">
                        <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                            <h4 class="mb-sm-0">Reporte KPI Ventas V</h4>
                            <div class="page-title-right">
                                <ol class="breadcrumb m-0">
                                    <li class="breadcrumb-item"><a href="{{ route('inicio.index') }}">Inicio</a></li>
                                    <li class="breadcrumb-item"><a href="{{ route('comercial.index') }}">Comercial</a></li>
                                    <li class="breadcrumb-item active">kpi-ventas-v</li>
                                </ol>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-body">
                                <form method="GET" action="{{ route('comercial.kpi-ventas-v') }}" class="row g-3 align-items-end" id="form-filtro-kpi-ventas-v">
                                    <div class="col-md-3">
                                        <label class="form-label">Fecha</label>
                                        <input type="date" name="fecha" class="form-control" value="{{ $fecha ?? now()->format('Y-m-d') }}" required>
                                    </div>
                                    <div class="col-md-9">
                                        <button type="submit" class="btn btn-primary me-2" id="btn-filtrar-kpi-ventas-v">
                                            <i class="ri-filter-3-line me-1"></i>Filtrar
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>

                    <div class="col-12">
                        <div class="alert alert-info py-2 mb-3" role="alert">
                            <strong>Fecha aplicada:</strong>
                            {{ $fecha ?? now()->format('Y-m-d') }}
                        </div>
                    </div>

                    <div class="col-xl-4 col-md-4">
                        <div class="card card-animate">
                            <div class="card-body">
                                <div class="d-flex align-items-center">
                                    <div class="flex-grow-1">
                                        <p class="text-uppercase fw-medium text-muted mb-0">Tradicional</p>
                                    </div>
                                </div>
                                <div class="d-flex align-items-end justify-content-between mt-4">
                                    <div>
                                        <h4 class="fs-22 fw-semibold mb-0">RD$ {{ number_format($kpis['tradicional'] ?? 0, 2) }}</h4>
                                        <small class="text-muted d-block">Agencias con venta: {{ number_format((int) ($indicadoresActual['tradicional']['agencias'] ?? 0), 0) }}</small>
                                        <small class="text-muted d-block">Promedio por agencia: RD$ {{ number_format((float) ($indicadoresActual['tradicional']['promedio'] ?? 0), 2) }}</small>
                                    </div>
                                    <div class="avatar-sm flex-shrink-0">
                                        <span class="avatar-title bg-primary rounded fs-3">
                                            <i class="ri-line-chart-line text-white"></i>
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-xl-4 col-md-4">
                        <div class="card card-animate">
                            <div class="card-body">
                                <div class="d-flex align-items-center">
                                    <div class="flex-grow-1">
                                        <p class="text-uppercase fw-medium text-muted mb-0">No Tradicional</p>
                                    </div>
                                </div>
                                <div class="d-flex align-items-end justify-content-between mt-4">
                                    <div>
                                        <h4 class="fs-22 fw-semibold mb-0">RD$ {{ number_format($kpis['no_tradicional'] ?? 0, 2) }}</h4>
                                        <small class="text-muted d-block">Agencias con venta: {{ number_format((int) ($indicadoresActual['no_tradicional']['agencias'] ?? 0), 0) }}</small>
                                        <small class="text-muted d-block">Promedio por agencia: RD$ {{ number_format((float) ($indicadoresActual['no_tradicional']['promedio'] ?? 0), 2) }}</small>
                                    </div>
                                    <div class="avatar-sm flex-shrink-0">
                                        <span class="avatar-title bg-info rounded fs-3">
                                            <i class="ri-bar-chart-grouped-line text-white"></i>
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-xl-4 col-md-4">
                        <div class="card card-animate">
                            <div class="card-body">
                                <div class="d-flex align-items-center">
                                    <div class="flex-grow-1">
                                        <p class="text-uppercase fw-medium text-muted mb-0">Recargas</p>
                                    </div>
                                </div>
                                <div class="d-flex align-items-end justify-content-between mt-4">
                                    <div>
                                        <h4 class="fs-22 fw-semibold mb-0">RD$ {{ number_format($kpis['recargas'] ?? 0, 2) }}</h4>
                                        <small class="text-muted d-block">Agencias con venta: {{ number_format((int) ($indicadoresActual['recargas']['agencias'] ?? 0), 0) }}</small>
                                        <small class="text-muted d-block">Promedio por agencia: RD$ {{ number_format((float) ($indicadoresActual['recargas']['promedio'] ?? 0), 2) }}</small>
                                    </div>
                                    <div class="avatar-sm flex-shrink-0">
                                        <span class="avatar-title bg-success rounded fs-3">
                                            <i class="ri-exchange-dollar-line text-white"></i>
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-12 mt-1">
                        <div class="alert alert-light py-2 mb-3" role="alert">
                            <strong>Ventas semana anterior (mismo día):</strong>
                            {{ $fechaSemanaAnterior ?? '-' }}
                        </div>
                    </div>

                    <div class="col-xl-4 col-md-4">
                        <div class="card card-animate">
                            <div class="card-body">
                                <div class="d-flex align-items-center">
                                    <div class="flex-grow-1">
                                        <p class="text-uppercase fw-medium text-muted mb-0">Tradicional - Semana Anterior</p>
                                    </div>
                                </div>
                                <div class="d-flex align-items-end justify-content-between mt-4">
                                    <div>
                                        <h4 class="fs-22 fw-semibold mb-0">RD$ {{ number_format($ventasSemanaAnterior['tradicional'] ?? 0, 2) }}</h4>
                                        <small class="text-muted d-block">Agencias con venta: {{ number_format((int) ($indicadoresSemanaAnterior['tradicional']['agencias'] ?? 0), 0) }}</small>
                                        <small class="text-muted d-block">Promedio por agencia: RD$ {{ number_format((float) ($indicadoresSemanaAnterior['tradicional']['promedio'] ?? 0), 2) }}</small>
                                    </div>
                                    <div class="avatar-sm flex-shrink-0">
                                        <span class="avatar-title bg-primary rounded fs-3">
                                            <i class="ri-line-chart-line text-white"></i>
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-xl-4 col-md-4">
                        <div class="card card-animate">
                            <div class="card-body">
                                <div class="d-flex align-items-center">
                                    <div class="flex-grow-1">
                                        <p class="text-uppercase fw-medium text-muted mb-0">No Tradicional - Semana Anterior</p>
                                    </div>
                                </div>
                                <div class="d-flex align-items-end justify-content-between mt-4">
                                    <div>
                                        <h4 class="fs-22 fw-semibold mb-0">RD$ {{ number_format($ventasSemanaAnterior['no_tradicional'] ?? 0, 2) }}</h4>
                                        <small class="text-muted d-block">Agencias con venta: {{ number_format((int) ($indicadoresSemanaAnterior['no_tradicional']['agencias'] ?? 0), 0) }}</small>
                                        <small class="text-muted d-block">Promedio por agencia: RD$ {{ number_format((float) ($indicadoresSemanaAnterior['no_tradicional']['promedio'] ?? 0), 2) }}</small>
                                    </div>
                                    <div class="avatar-sm flex-shrink-0">
                                        <span class="avatar-title bg-info rounded fs-3">
                                            <i class="ri-bar-chart-grouped-line text-white"></i>
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-xl-4 col-md-4">
                        <div class="card card-animate">
                            <div class="card-body">
                                <div class="d-flex align-items-center">
                                    <div class="flex-grow-1">
                                        <p class="text-uppercase fw-medium text-muted mb-0">Recargas - Semana Anterior</p>
                                    </div>
                                </div>
                                <div class="d-flex align-items-end justify-content-between mt-4">
                                    <div>
                                        <h4 class="fs-22 fw-semibold mb-0">RD$ {{ number_format($ventasSemanaAnterior['recargas'] ?? 0, 2) }}</h4>
                                        <small class="text-muted d-block">Agencias con venta: {{ number_format((int) ($indicadoresSemanaAnterior['recargas']['agencias'] ?? 0), 0) }}</small>
                                        <small class="text-muted d-block">Promedio por agencia: RD$ {{ number_format((float) ($indicadoresSemanaAnterior['recargas']['promedio'] ?? 0), 2) }}</small>
                                    </div>
                                    <div class="avatar-sm flex-shrink-0">
                                        <span class="avatar-title bg-success rounded fs-3">
                                            <i class="ri-exchange-dollar-line text-white"></i>
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-12 mt-1">
                        <div class="alert alert-light py-2 mb-3" role="alert">
                            <strong>Ventas mes anterior (mismo día):</strong>
                            {{ $fechaMesAnterior ?? '-' }}
                        </div>
                    </div>

                    <div class="col-xl-4 col-md-4">
                        <div class="card card-animate">
                            <div class="card-body">
                                <div class="d-flex align-items-center">
                                    <div class="flex-grow-1">
                                        <p class="text-uppercase fw-medium text-muted mb-0">Tradicional - Mes Anterior</p>
                                    </div>
                                </div>
                                <div class="d-flex align-items-end justify-content-between mt-4">
                                    <div>
                                        <h4 class="fs-22 fw-semibold mb-0">RD$ {{ number_format($ventasMesAnterior['tradicional'] ?? 0, 2) }}</h4>
                                        <small class="text-muted d-block">Agencias con venta: {{ number_format((int) ($indicadoresMesAnterior['tradicional']['agencias'] ?? 0), 0) }}</small>
                                        <small class="text-muted d-block">Promedio por agencia: RD$ {{ number_format((float) ($indicadoresMesAnterior['tradicional']['promedio'] ?? 0), 2) }}</small>
                                    </div>
                                    <div class="avatar-sm flex-shrink-0">
                                        <span class="avatar-title bg-primary rounded fs-3">
                                            <i class="ri-line-chart-line text-white"></i>
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-xl-4 col-md-4">
                        <div class="card card-animate">
                            <div class="card-body">
                                <div class="d-flex align-items-center">
                                    <div class="flex-grow-1">
                                        <p class="text-uppercase fw-medium text-muted mb-0">No Tradicional - Mes Anterior</p>
                                    </div>
                                </div>
                                <div class="d-flex align-items-end justify-content-between mt-4">
                                    <div>
                                        <h4 class="fs-22 fw-semibold mb-0">RD$ {{ number_format($ventasMesAnterior['no_tradicional'] ?? 0, 2) }}</h4>
                                        <small class="text-muted d-block">Agencias con venta: {{ number_format((int) ($indicadoresMesAnterior['no_tradicional']['agencias'] ?? 0), 0) }}</small>
                                        <small class="text-muted d-block">Promedio por agencia: RD$ {{ number_format((float) ($indicadoresMesAnterior['no_tradicional']['promedio'] ?? 0), 2) }}</small>
                                    </div>
                                    <div class="avatar-sm flex-shrink-0">
                                        <span class="avatar-title bg-info rounded fs-3">
                                            <i class="ri-bar-chart-grouped-line text-white"></i>
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-xl-4 col-md-4">
                        <div class="card card-animate">
                            <div class="card-body">
                                <div class="d-flex align-items-center">
                                    <div class="flex-grow-1">
                                        <p class="text-uppercase fw-medium text-muted mb-0">Recargas - Mes Anterior</p>
                                    </div>
                                </div>
                                <div class="d-flex align-items-end justify-content-between mt-4">
                                    <div>
                                        <h4 class="fs-22 fw-semibold mb-0">RD$ {{ number_format($ventasMesAnterior['recargas'] ?? 0, 2) }}</h4>
                                        <small class="text-muted d-block">Agencias con venta: {{ number_format((int) ($indicadoresMesAnterior['recargas']['agencias'] ?? 0), 0) }}</small>
                                        <small class="text-muted d-block">Promedio por agencia: RD$ {{ number_format((float) ($indicadoresMesAnterior['recargas']['promedio'] ?? 0), 2) }}</small>
                                    </div>
                                    <div class="avatar-sm flex-shrink-0">
                                        <span class="avatar-title bg-success rounded fs-3">
                                            <i class="ri-exchange-dollar-line text-white"></i>
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-12 mt-1">
                        <div class="alert alert-light py-2 mb-3" role="alert">
                            <strong>Ventas año anterior (mismo día):</strong>
                            {{ $fechaAnioAnterior ?? '-' }}
                        </div>
                    </div>

                    <div class="col-xl-4 col-md-6">
                        <div class="card card-animate">
                            <div class="card-body">
                                <div class="d-flex align-items-center">
                                    <div class="flex-grow-1">
                                        <p class="text-uppercase fw-medium text-muted mb-0">Tradicional - Año Anterior</p>
                                    </div>
                                </div>
                                <div class="d-flex align-items-end justify-content-between mt-4">
                                    <div>
                                        <h4 class="fs-22 fw-semibold mb-0">RD$ {{ number_format($ventasAnioAnterior['tradicional'] ?? 0, 2) }}</h4>
                                        <small class="text-muted d-block">Agencias con venta: {{ number_format((int) ($indicadoresAnioAnterior['tradicional']['agencias'] ?? 0), 0) }}</small>
                                        <small class="text-muted d-block">Promedio por agencia: RD$ {{ number_format((float) ($indicadoresAnioAnterior['tradicional']['promedio'] ?? 0), 2) }}</small>
                                    </div>
                                    <div class="avatar-sm flex-shrink-0">
                                        <span class="avatar-title bg-primary rounded fs-3">
                                            <i class="ri-line-chart-line text-white"></i>
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-xl-4 col-md-6">
                        <div class="card card-animate">
                            <div class="card-body">
                                <div class="d-flex align-items-center">
                                    <div class="flex-grow-1">
                                        <p class="text-uppercase fw-medium text-muted mb-0">No Tradicional - Año Anterior</p>
                                    </div>
                                </div>
                                <div class="d-flex align-items-end justify-content-between mt-4">
                                    <div>
                                        <h4 class="fs-22 fw-semibold mb-0">RD$ {{ number_format($ventasAnioAnterior['no_tradicional'] ?? 0, 2) }}</h4>
                                        <small class="text-muted d-block">Agencias con venta: {{ number_format((int) ($indicadoresAnioAnterior['no_tradicional']['agencias'] ?? 0), 0) }}</small>
                                        <small class="text-muted d-block">Promedio por agencia: RD$ {{ number_format((float) ($indicadoresAnioAnterior['no_tradicional']['promedio'] ?? 0), 2) }}</small>
                                    </div>
                                    <div class="avatar-sm flex-shrink-0">
                                        <span class="avatar-title bg-info rounded fs-3">
                                            <i class="ri-bar-chart-grouped-line text-white"></i>
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-xl-4 col-md-6">
                        <div class="card card-animate">
                            <div class="card-body">
                                <div class="d-flex align-items-center">
                                    <div class="flex-grow-1">
                                        <p class="text-uppercase fw-medium text-muted mb-0">Recargas - Año Anterior</p>
                                    </div>
                                </div>
                                <div class="d-flex align-items-end justify-content-between mt-4">
                                    <div>
                                        <h4 class="fs-22 fw-semibold mb-0">RD$ {{ number_format($ventasAnioAnterior['recargas'] ?? 0, 2) }}</h4>
                                        <small class="text-muted d-block">Agencias con venta: {{ number_format((int) ($indicadoresAnioAnterior['recargas']['agencias'] ?? 0), 0) }}</small>
                                        <small class="text-muted d-block">Promedio por agencia: RD$ {{ number_format((float) ($indicadoresAnioAnterior['recargas']['promedio'] ?? 0), 2) }}</small>
                                    </div>
                                    <div class="avatar-sm flex-shrink-0">
                                        <span class="avatar-title bg-success rounded fs-3">
                                            <i class="ri-exchange-dollar-line text-white"></i>
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-12 mt-2">
                        <div class="card">
                            <div class="card-header d-flex align-items-center justify-content-between">
                                <h5 class="card-title mb-0">Tabla de Validación (vt_usuarios_bet)</h5>
                                <span class="badge bg-light text-dark">Filtrado por día</span>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-bordered table-striped align-middle mb-0" id="table-validacion-kpi-v">
                                        <thead>
                                            <tr>
                                                <th>Fecha</th>
                                                <th>Agencias con venta</th>
                                                <th>Promedio de venta</th>
                                                <th>Tradicional</th>
                                                <th>No Tradicional</th>
                                                <th>Recargas</th>
                                                <th>Total General</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @php
                                                $filasComparativas = collect($comparativasTabla ?? []);
                                                $filaReferencia = $filasComparativas->first() ?? [];
                                                $promedioRefGeneral = (float) ($filaReferencia['promedio_venta_general'] ?? 0);
                                                $promedioRefTrad = (float) ($filaReferencia['promedio_tradicional'] ?? 0);
                                                $promedioRefNoTrad = (float) ($filaReferencia['promedio_no_tradicional'] ?? 0);
                                                $promedioRefRecargas = (float) ($filaReferencia['promedio_recargas'] ?? 0);

                                                $calcVariacion = function (float $actual, float $comparado) {
                                                    if ($comparado <= 0) {
                                                        return null;
                                                    }
                                                    return (($actual - $comparado) / $comparado) * 100;
                                                };

                                                $sumAgenciasConVenta = 0;
                                                $sumPromedioVenta = 0;
                                                $sumTradicional = 0;
                                                $sumNoTradicional = 0;
                                                $sumRecargas = 0;
                                                $sumGeneral = 0;
                                            @endphp
                                            @forelse(($comparativasTabla ?? []) as $fila)
                                                @php
                                                    $isReferencia = $loop->first;

                                                    $sumAgenciasConVenta += (int) ($fila['total_agencias_con_venta'] ?? 0);
                                                    $sumPromedioVenta += (float) ($fila['promedio_venta_general'] ?? 0);
                                                    $sumTradicional += (float) ($fila['total_tradicional'] ?? 0);
                                                    $sumNoTradicional += (float) ($fila['total_no_tradicional'] ?? 0);
                                                    $sumRecargas += (float) ($fila['total_recargas'] ?? 0);
                                                    $sumGeneral += (float) ($fila['total_general'] ?? 0);

                                                    $promedioFilaGeneral = (float) ($fila['promedio_venta_general'] ?? 0);
                                                    $promedioFilaTrad = (float) ($fila['promedio_tradicional'] ?? 0);
                                                    $promedioFilaNoTrad = (float) ($fila['promedio_no_tradicional'] ?? 0);
                                                    $promedioFilaRecargas = (float) ($fila['promedio_recargas'] ?? 0);

                                                    $varGeneral = $isReferencia ? null : $calcVariacion($promedioRefGeneral, $promedioFilaGeneral);
                                                    $varTrad = $isReferencia ? null : $calcVariacion($promedioRefTrad, $promedioFilaTrad);
                                                    $varNoTrad = $isReferencia ? null : $calcVariacion($promedioRefNoTrad, $promedioFilaNoTrad);
                                                    $varRecargas = $isReferencia ? null : $calcVariacion($promedioRefRecargas, $promedioFilaRecargas);
                                                @endphp
                                                <tr>
                                                    <td>
                                                        <div class="fw-semibold">{{ $fila['titulo'] ?? '' }}</div>
                                                        <div class="text-muted">{{ $fila['fecha'] ?? '' }}</div>
                                                    </td>
                                                    <td>{{ number_format((int) ($fila['total_agencias_con_venta'] ?? 0), 0) }}</td>
                                                    <td>
                                                        <div>RD$ {{ number_format($promedioFilaGeneral, 2) }}</div>
                                                        @if(!$isReferencia && $varGeneral !== null)
                                                            @if($varGeneral > 0)
                                                                <div class="text-success fs-11">Excedente {{ number_format($varGeneral, 2) }}%</div>
                                                            @elseif($varGeneral < 0)
                                                                <div class="text-danger fs-11">Por debajo {{ number_format(abs($varGeneral), 2) }}%</div>
                                                            @else
                                                                <div class="text-muted fs-11">Sin variación</div>
                                                            @endif
                                                        @endif
                                                    </td>
                                                    <td>
                                                        <div>RD$ {{ number_format((float) ($fila['total_tradicional'] ?? 0), 2) }}</div>
                                                        <div class="text-muted fs-11">Agencias: {{ number_format((int) ($fila['agencias_tradicional'] ?? 0), 0) }}</div>
                                                        <div class="text-muted fs-11">Promedio: RD$ {{ number_format($promedioFilaTrad, 2) }}</div>
                                                        @if(!$isReferencia && $varTrad !== null)
                                                            @if($varTrad > 0)
                                                                <div class="text-success fs-11">Excedente {{ number_format($varTrad, 2) }}%</div>
                                                            @elseif($varTrad < 0)
                                                                <div class="text-danger fs-11">Por debajo {{ number_format(abs($varTrad), 2) }}%</div>
                                                            @else
                                                                <div class="text-muted fs-11">Sin variación</div>
                                                            @endif
                                                        @endif
                                                    </td>
                                                    <td>
                                                        <div>RD$ {{ number_format((float) ($fila['total_no_tradicional'] ?? 0), 2) }}</div>
                                                        <div class="text-muted fs-11">Agencias: {{ number_format((int) ($fila['agencias_no_tradicional'] ?? 0), 0) }}</div>
                                                        <div class="text-muted fs-11">Promedio: RD$ {{ number_format($promedioFilaNoTrad, 2) }}</div>
                                                        @if(!$isReferencia && $varNoTrad !== null)
                                                            @if($varNoTrad > 0)
                                                                <div class="text-success fs-11">Excedente {{ number_format($varNoTrad, 2) }}%</div>
                                                            @elseif($varNoTrad < 0)
                                                                <div class="text-danger fs-11">Por debajo {{ number_format(abs($varNoTrad), 2) }}%</div>
                                                            @else
                                                                <div class="text-muted fs-11">Sin variación</div>
                                                            @endif
                                                        @endif
                                                    </td>
                                                    <td>
                                                        <div>RD$ {{ number_format((float) ($fila['total_recargas'] ?? 0), 2) }}</div>
                                                        <div class="text-muted fs-11">Agencias: {{ number_format((int) ($fila['agencias_recargas'] ?? 0), 0) }}</div>
                                                        <div class="text-muted fs-11">Promedio: RD$ {{ number_format($promedioFilaRecargas, 2) }}</div>
                                                        @if(!$isReferencia && $varRecargas !== null)
                                                            @if($varRecargas > 0)
                                                                <div class="text-success fs-11">Excedente {{ number_format($varRecargas, 2) }}%</div>
                                                            @elseif($varRecargas < 0)
                                                                <div class="text-danger fs-11">Por debajo {{ number_format(abs($varRecargas), 2) }}%</div>
                                                            @else
                                                                <div class="text-muted fs-11">Sin variación</div>
                                                            @endif
                                                        @endif
                                                    </td>
                                                    <td>RD$ {{ number_format((float) ($fila['total_general'] ?? 0), 2) }}</td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="7" class="text-center text-muted">No hay datos para mostrar en las comparativas.</td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                        @if(!empty($comparativasTabla ?? []))
                                            <tfoot>
                                                <tr class="fw-semibold">
                                                    <td>Total</td>
                                                    <td>{{ number_format($sumAgenciasConVenta, 0) }}</td>
                                                    <td>RD$ {{ number_format($sumPromedioVenta, 2) }}</td>
                                                    <td>RD$ {{ number_format($sumTradicional, 2) }}</td>
                                                    <td>RD$ {{ number_format($sumNoTradicional, 2) }}</td>
                                                    <td>RD$ {{ number_format($sumRecargas, 2) }}</td>
                                                    <td>RD$ {{ number_format($sumGeneral, 2) }}</td>
                                                </tr>
                                            </tfoot>
                                        @endif
                                    </table>
                                </div>
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
        const formFiltro = document.getElementById('form-filtro-kpi-ventas-v');
        const btnFiltrar = document.getElementById('btn-filtrar-kpi-ventas-v');

        if (!formFiltro || !btnFiltrar) return;

        formFiltro.addEventListener('submit', function () {
            btnFiltrar.disabled = true;
            Swal.fire({
                title: 'Cargando...',
                text: 'Procesando filtro, por favor espera.',
                allowOutsideClick: false,
                allowEscapeKey: false,
                didOpen: () => Swal.showLoading(),
            });
        });
    });
</script>
@endsection
