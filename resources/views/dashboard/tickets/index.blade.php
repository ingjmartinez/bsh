@extends('app')

@section('content')
    @php
        $estadoLabels = [
            'pendiente' => 'Pendiente',
            'en_proceso' => 'En Proceso',
            'pagado' => 'Pagado',
            'token_enviado' => 'Token enviado',
            'ticket_pagado' => 'Ticket pagado Por otra Terminal',
            'nulo' => 'Nulo',
            'averia_cerrada' => 'Averia Cerrada',
        ];

        $estadoBadges = [
            'pendiente' => 'warning',
            'en_proceso' => 'info',
            'pagado' => 'success',
            'token_enviado' => 'primary',
            'ticket_pagado' => 'success',
            'nulo' => 'danger',
            'averia_cerrada' => 'success',
        ];

        $estadoTotal = max(1, array_sum($dashboard['estados'] ?? []));
    @endphp

    <div class="main-content">
        <div class="page-content">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-12">
                        <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                            <h4 class="mb-sm-0">Dashboard de Tickets</h4>
                            <div class="page-title-right">
                                <ol class="breadcrumb m-0">
                                    <li class="breadcrumb-item"><a href="{{ route('dashboard.index') }}">Dashboard</a></li>
                                    <li class="breadcrumb-item active">Tickets</li>
                                </ol>
                            </div>
                        </div>
                    </div>
                </div>

                @if ($setupPending)
                    <div class="alert alert-warning">
                        La tabla del modulo aun no existe. Ejecuta las migraciones para empezar a registrar tickets.
                    </div>
                @endif

                <div class="card">
                    <div class="card-header">
                        <form method="GET" action="{{ route('dashboard.tickets') }}" class="row g-3 align-items-end">
                            <div class="col-md-3">
                                <label class="form-label" for="estado">Estado</label>
                                <select class="form-select" id="estado" name="estado">
                                    <option value="">Todos</option>
                                    @foreach ($estadoLabels as $value => $label)
                                        <option value="{{ $value }}" @selected(($filtros['estado'] ?? '') === $value)>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label" for="desde">Desde</label>
                                <input type="date" class="form-control" id="desde" name="desde" value="{{ $filtros['desde'] ?? '' }}">
                            </div>
                            <div class="col-md-2">
                                <label class="form-label" for="hasta">Hasta</label>
                                <input type="date" class="form-control" id="hasta" name="hasta" value="{{ $filtros['hasta'] ?? '' }}">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label" for="buscar">Buscar</label>
                                <input type="text" class="form-control" id="buscar" name="buscar" value="{{ $filtros['buscar'] ?? '' }}" placeholder="Terminal, telefono o mensaje">
                            </div>
                            <div class="col-md-1">
                                <button class="btn btn-primary w-100" type="submit" title="Filtrar">
                                    <i class="ri-search-line"></i>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <div class="row">
                    <div class="col-xl-3 col-md-6">
                        <div class="card card-animate">
                            <div class="card-body">
                                <p class="text-uppercase fw-medium text-muted mb-1">Total tickets</p>
                                <h4 class="fs-22 fw-semibold mb-0">{{ number_format($stats['total']) }}</h4>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-3 col-md-6">
                        <div class="card card-animate">
                            <div class="card-body">
                                <p class="text-uppercase fw-medium text-muted mb-1">Pendientes</p>
                                <h4 class="fs-22 fw-semibold mb-0 text-warning">{{ number_format($stats['pendientes']) }}</h4>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-3 col-md-6">
                        <div class="card card-animate">
                            <div class="card-body">
                                <p class="text-uppercase fw-medium text-muted mb-1">Pagados</p>
                                <h4 class="fs-22 fw-semibold mb-0 text-success">{{ number_format($stats['pagados']) }}</h4>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-3 col-md-6">
                        <div class="card card-animate">
                            <div class="card-body">
                                <p class="text-uppercase fw-medium text-muted mb-1">Nulos</p>
                                <h4 class="fs-22 fw-semibold mb-0 text-danger">{{ number_format($stats['nulos']) }}</h4>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-xl-8">
                        <div class="row">
                            @foreach ($dashboard['categorias'] as $categoria)
                                <div class="col-xl-4 col-md-6">
                                    <div class="card h-100">
                                        <div class="card-body">
                                            <div class="d-flex align-items-center justify-content-between mb-3">
                                                <div>
                                                    <p class="text-muted text-uppercase fw-medium mb-1">{{ $categoria['label'] }}</p>
                                                    <h4 class="mb-0">{{ number_format($categoria['total']) }}</h4>
                                                </div>
                                                <span class="avatar-title bg-{{ $categoria['color'] }}-subtle text-{{ $categoria['color'] }} rounded-2 fs-2">
                                                    <i class="{{ $categoria['icon'] }}"></i>
                                                </span>
                                            </div>
                                            <div class="d-flex justify-content-between small mb-2">
                                                <span>Cierre</span>
                                                <strong>{{ $categoria['porcentaje_cierre'] }}%</strong>
                                            </div>
                                            <div class="progress progress-sm">
                                                <div class="progress-bar bg-{{ $categoria['color'] }}" role="progressbar" style="width: {{ $categoria['porcentaje_cierre'] }}%"></div>
                                            </div>
                                            <div class="table-responsive mt-3">
                                                <table class="table table-sm align-middle mb-0">
                                                    <tbody>
                                                        <tr>
                                                            <td class="text-muted">Pendientes</td>
                                                            <td class="text-end fw-semibold text-warning">{{ number_format($categoria['pendientes']) }}</td>
                                                        </tr>
                                                        <tr>
                                                            <td class="text-muted">En proceso</td>
                                                            <td class="text-end fw-semibold text-info">{{ number_format($categoria['en_proceso']) }}</td>
                                                        </tr>
                                                        <tr>
                                                            <td class="text-muted">Cerrados</td>
                                                            <td class="text-end fw-semibold text-success">{{ number_format($categoria['cerrados']) }}</td>
                                                        </tr>
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <div class="row mt-4">
                            @foreach ($dashboard['categorias'] as $categoria)
                                <div class="col-xl-4">
                                    <div class="card h-100">
                                        <div class="card-header">
                                            <h5 class="card-title mb-0">{{ $categoria['label'] }} recientes</h5>
                                        </div>
                                        <div class="card-body">
                                            <div class="table-responsive">
                                                <table class="table table-sm align-middle mb-0">
                                                    <thead class="table-light">
                                                        <tr>
                                                            <th>Terminal</th>
                                                            <th>Estado</th>
                                                            <th>Entrada</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        @forelse ($categoria['ultimos'] as $ticket)
                                                            <tr>
                                                                <td class="fw-semibold">{{ $ticket->ticket_numero }}</td>
                                                                <td>
                                                                    <span class="badge bg-{{ $ticket->estado_badge }}">{{ $ticket->estado_label }}</span>
                                                                </td>
                                                                <td class="text-muted">{{ optional($ticket->created_at)->format('d/m h:i A') }}</td>
                                                            </tr>
                                                        @empty
                                                            <tr>
                                                                <td colspan="3" class="text-center text-muted py-3">Sin datos</td>
                                                            </tr>
                                                        @endforelse
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <div class="col-xl-4">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="card-title mb-0">Distribucion por estado</h5>
                            </div>
                            <div class="card-body">
                                @foreach ($estadoLabels as $estado => $label)
                                    @php
                                        $count = (int) (($dashboard['estados'][$estado] ?? 0));
                                        $percent = round(($count / $estadoTotal) * 100);
                                        $badge = $estadoBadges[$estado] ?? 'secondary';
                                    @endphp
                                    <div class="mb-3">
                                        <div class="d-flex justify-content-between mb-1">
                                            <span class="text-muted">{{ $label }}</span>
                                            <strong>{{ number_format($count) }}</strong>
                                        </div>
                                        <div class="progress progress-sm">
                                            <div class="progress-bar bg-{{ $badge }}" role="progressbar" style="width: {{ $percent }}%"></div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        <div class="card">
                            <div class="card-header">
                                <h5 class="card-title mb-0">Pendientes mas antiguos</h5>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-sm align-middle mb-0">
                                        <thead class="table-light">
                                            <tr>
                                                <th>Terminal</th>
                                                <th>Categoria</th>
                                                <th>Entrada</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse ($dashboard['pendientesAntiguos'] as $ticket)
                                                <tr>
                                                    <td class="fw-semibold">{{ $ticket->ticket_numero }}</td>
                                                    <td>{{ $ticket->categoria_label }}</td>
                                                    <td class="text-muted">{{ optional($ticket->created_at)->format('d/m h:i A') }}</td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="3" class="text-center text-muted py-3">Sin pendientes</td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <div class="card">
                            <div class="card-header">
                                <h5 class="card-title mb-0">Gestores destacados</h5>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-sm align-middle mb-0">
                                        <tbody>
                                            @forelse ($dashboard['topGestores'] as $gestor)
                                                <tr>
                                                    <td>{{ $gestor->procesadoPor->name ?? 'Sin usuario' }}</td>
                                                    <td class="text-end fw-semibold">{{ number_format($gestor->total) }}</td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td class="text-center text-muted py-3">Sin gestiones registradas</td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row mt-4">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="card-title mb-0">Cierres recientes</h5>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-sm table-hover align-middle mb-0">
                                        <thead class="table-light">
                                            <tr>
                                                <th>Solicitud</th>
                                                <th>Categoria</th>
                                                <th>Terminal</th>
                                                <th>Estado</th>
                                                <th>Gestionado por</th>
                                                <th>Gestion</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse ($dashboard['recientesCerrados'] as $ticket)
                                                <tr>
                                                    <td class="fw-semibold">{{ $ticket->codigo }}</td>
                                                    <td>{{ $ticket->categoria_label }}</td>
                                                    <td>{{ $ticket->ticket_numero }}</td>
                                                    <td><span class="badge bg-{{ $ticket->estado_badge }}">{{ $ticket->estado_label }}</span></td>
                                                    <td>{{ $ticket->procesadoPor->name ?? 'Sin usuario' }}</td>
                                                    <td class="text-muted">{{ optional($ticket->procesado_at ?? $ticket->updated_at)->format('d/m/Y h:i A') }}</td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="6" class="text-center text-muted py-4">Sin cierres recientes</td>
                                                </tr>
                                            @endforelse
                                        </tbody>
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
