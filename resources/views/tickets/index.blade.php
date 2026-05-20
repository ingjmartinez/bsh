@extends('app')

@section('content')
    <div class="main-content">
        <div class="page-content">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-12">
                        <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                            <h4 class="mb-sm-0">Tickets WhatsApp</h4>
                            <div class="page-title-right">
                                <ol class="breadcrumb m-0">
                                    <li class="breadcrumb-item"><a href="{{ route('inicio.index') }}">Inicio</a></li>
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

                @if (session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                @if ($errors->any())
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        @foreach ($errors->all() as $error)
                            <div>{{ $error }}</div>
                        @endforeach
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                <div class="row">
                    <div class="col-xl-3 col-md-6">
                        <div class="card card-animate">
                            <div class="card-body">
                                <div class="d-flex align-items-center">
                                    <div class="flex-grow-1">
                                        <p class="text-uppercase fw-medium text-muted mb-0">Total tickets</p>
                                    </div>
                                    <div class="avatar-sm flex-shrink-0">
                                        <span class="avatar-title bg-primary-subtle text-primary rounded-2 fs-2">
                                            <i class="ri-ticket-2-line"></i>
                                        </span>
                                    </div>
                                </div>
                                <div class="mt-3">
                                    <h4 class="fs-22 fw-semibold mb-0">{{ number_format($stats['total']) }}</h4>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-3 col-md-6">
                        <div class="card card-animate">
                            <div class="card-body">
                                <p class="text-uppercase fw-medium text-muted mb-0">Pendientes</p>
                                <h4 class="fs-22 fw-semibold mt-3 mb-0 text-warning">{{ number_format($stats['pendientes']) }}</h4>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-3 col-md-6">
                        <div class="card card-animate">
                            <div class="card-body">
                                <p class="text-uppercase fw-medium text-muted mb-0">Pagados</p>
                                <h4 class="fs-22 fw-semibold mt-3 mb-0 text-success">{{ number_format($stats['pagados']) }}</h4>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-3 col-md-6">
                        <div class="card card-animate">
                            <div class="card-body">
                                <p class="text-uppercase fw-medium text-muted mb-0">Nulos</p>
                                <h4 class="fs-22 fw-semibold mt-3 mb-0 text-danger">{{ number_format($stats['nulos']) }}</h4>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-xl-9">
                        <div class="card">
                            <div class="card-header">
                                <form method="GET" action="{{ route('tickets.index') }}" class="row g-3 align-items-end">
                                    <div class="col-md-3">
                                        <label class="form-label" for="categoria">Categoria</label>
                                        <select class="form-select" id="categoria" name="categoria">
                                            <option value="">Todas</option>
                                            <option value="pagar_ticket" @selected(($filtros['categoria'] ?? '') === 'pagar_ticket')>Pagar ticket</option>
                                            <option value="anular_ticket" @selected(($filtros['categoria'] ?? '') === 'anular_ticket')>Anular ticket</option>
                                        </select>
                                    </div>
                                    <div class="col-md-2">
                                        <label class="form-label" for="estado">Estado</label>
                                        <select class="form-select" id="estado" name="estado">
                                            <option value="">Todos</option>
                                            <option value="pendiente" @selected(($filtros['estado'] ?? '') === 'pendiente')>Pendiente</option>
                                            <option value="pagado" @selected(($filtros['estado'] ?? '') === 'pagado')>Pagado</option>
                                            <option value="nulo" @selected(($filtros['estado'] ?? '') === 'nulo')>Nulo</option>
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
                                    <div class="col-md-3">
                                        <label class="form-label" for="buscar">Buscar</label>
                                        <div class="input-group">
                                            <input type="text" class="form-control" id="buscar" name="buscar" value="{{ $filtros['buscar'] ?? '' }}" placeholder="Terminal o telefono">
                                            <button class="btn btn-primary" type="submit">
                                                <i class="ri-search-line"></i>
                                            </button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-hover align-middle">
                                        <thead class="table-light">
                                            <tr>
                                                <th>Solicitud</th>
                                                <th>Categoria</th>
                                                <th>Codigo terminal</th>
                                                <th>Telefono</th>
                                                <th>Estado</th>
                                                <th>Imagen</th>
                                                <th>Entrada</th>
                                                <th>Gestion</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse ($solicitudes as $solicitud)
                                                <tr>
                                                    <td class="fw-semibold">{{ $solicitud->codigo }}</td>
                                                    <td>{{ $solicitud->categoria_label }}</td>
                                                    <td>{{ $solicitud->ticket_numero }}</td>
                                                    <td>{{ $solicitud->phone }}</td>
                                                    <td>
                                                        <span class="badge bg-{{ $solicitud->estado_badge }}">{{ $solicitud->estado_label }}</span>
                                                    </td>
                                                    <td>
                                                        @if ($solicitud->attachment_url)
                                                            <button
                                                                type="button"
                                                                class="btn btn-sm btn-info"
                                                                data-bs-toggle="modal"
                                                                data-bs-target="#ticketImageModal"
                                                                data-attachment-url="{{ $solicitud->attachment_url }}"
                                                                data-ticket-codigo="{{ $solicitud->codigo }}">
                                                                <i class="ri-image-2-line me-1"></i>Ver
                                                            </button>
                                                        @else
                                                            <span class="text-muted">Sin imagen</span>
                                                        @endif
                                                    </td>
                                                    <td>{{ optional($solicitud->created_at)->format('d/m/Y h:i A') }}</td>
                                                    <td style="min-width: 260px;">
                                                        <form method="POST" action="{{ route('tickets.estado', $solicitud) }}" class="d-flex gap-2">
                                                            @csrf
                                                            @method('PUT')
                                                            <select class="form-select form-select-sm" name="estado">
                                                                <option value="pendiente" @selected($solicitud->estado === 'pendiente')>Pendiente</option>
                                                                <option value="pagado" @selected($solicitud->estado === 'pagado')>Pagado</option>
                                                                <option value="nulo" @selected($solicitud->estado === 'nulo')>Nulo</option>
                                                            </select>
                                                            <button class="btn btn-sm btn-success" type="submit">
                                                                <i class="ri-save-3-line"></i>
                                                            </button>
                                                        </form>
                                                        @if ($solicitud->procesadoPor)
                                                            <small class="text-muted d-block mt-1">
                                                                Por {{ $solicitud->procesadoPor->name }} - {{ optional($solicitud->procesado_at)->format('d/m/Y h:i A') }}
                                                            </small>
                                                        @endif
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="8" class="text-center text-muted py-4">
                                                        No hay solicitudes con los filtros seleccionados.
                                                    </td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>

                                {{ $solicitudes->links() }}
                            </div>
                        </div>
                    </div>

                    <div class="col-xl-3">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="card-title mb-0">Registro manual</h5>
                            </div>
                            <div class="card-body">
                                <form method="POST" action="{{ route('tickets.store') }}" class="vstack gap-3">
                                    @csrf
                                    <div>
                                        <label class="form-label" for="manual_categoria">Categoria</label>
                                        <select class="form-select" id="manual_categoria" name="categoria" required>
                                            <option value="pagar_ticket">Pagar ticket</option>
                                            <option value="anular_ticket">Anular ticket</option>
                                        </select>
                                    </div>
                                    <div>
                                        <label class="form-label" for="manual_ticket">Codigo de terminal</label>
                                        <input type="text" class="form-control" id="manual_ticket" name="ticket_numero" required>
                                    </div>
                                    <div>
                                        <label class="form-label" for="manual_phone">Telefono</label>
                                        <input type="text" class="form-control" id="manual_phone" name="phone">
                                    </div>
                                    <div>
                                        <label class="form-label" for="manual_mensaje">Nota</label>
                                        <textarea class="form-control" id="manual_mensaje" name="mensaje_original" rows="3"></textarea>
                                    </div>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="ri-add-line me-1"></i>Registrar
                                    </button>
                                </form>
                            </div>
                        </div>

                        <div class="card">
                            <div class="card-body">
                                <div class="d-flex justify-content-between">
                                    <span class="text-muted">Pagar ticket</span>
                                    <strong>{{ number_format($stats['pagar']) }}</strong>
                                </div>
                                <div class="d-flex justify-content-between mt-2">
                                    <span class="text-muted">Anular ticket</span>
                                    <strong>{{ number_format($stats['anular']) }}</strong>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="ticketImageModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="ticketImageModalTitle">Imagen de ticket</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body text-center">
                    <a id="ticketImageLink" href="#" target="_blank" rel="noopener noreferrer">
                        <img id="ticketImagePreview" src="" alt="Imagen de ticket" class="img-fluid rounded border">
                    </a>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('script')
    <script>
        (function () {
            const modal = document.getElementById('ticketImageModal');
            const image = document.getElementById('ticketImagePreview');
            const link = document.getElementById('ticketImageLink');
            const title = document.getElementById('ticketImageModalTitle');

            if (!modal || !image || !link || !title) {
                return;
            }

            modal.addEventListener('show.bs.modal', function (event) {
                const trigger = event.relatedTarget;
                const url = trigger?.getAttribute('data-attachment-url') || '';
                const codigo = trigger?.getAttribute('data-ticket-codigo') || 'Ticket';

                image.src = url;
                link.href = url;
                title.textContent = `Imagen de ${codigo}`;
            });

            modal.addEventListener('hidden.bs.modal', function () {
                image.src = '';
                link.href = '#';
                title.textContent = 'Imagen de ticket';
            });
        })();
    </script>
@endsection
