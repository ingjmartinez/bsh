@extends('app')

@section('content')
    @php
        $estadoOpcionesFiltro = [
            'pendiente' => 'Pendiente',
            'pagado' => 'Pagado',
            'token_enviado' => 'Token enviado',
            'token_no_funciono' => 'Token No Funciono',
            'ticket_pagado' => 'Ticket pagado Por otra Terminal',
            'nulo' => 'Nulo',
            'rechazado' => 'Rechazado',
            'en_proceso' => 'En Proceso',
            'averia_cerrada' => 'Averia Cerrada',
        ];
        $estadosSeleccionadosFiltro = collect((array) ($filtros['estado'] ?? []))
            ->filter()
            ->values()
            ->all();
    @endphp

    <style>
        [data-layout-mode="dark"] .tickets-page .tickets-table th,
        [data-layout-mode="dark"] .tickets-page .tickets-table td,
        [data-layout-mode="dark"] .tickets-page .ticket-dark-text,
        [data-layout-mode="dark"] .tickets-page .tickets-table .text-muted {
            color: var(--vz-body-color) !important;
        }

        [data-layout-mode="dark"] .tickets-page .tickets-table .badge.bg-light {
            background-color: rgba(255, 255, 255, 0.14) !important;
            color: var(--vz-body-color) !important;
        }
    </style>

    <div class="main-content tickets-page">
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
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header">
                                <div class="d-flex flex-column gap-3">
                                    <div class="d-flex flex-wrap align-items-center justify-content-between gap-2">
                                        <div class="d-flex flex-wrap gap-3">
                                            <div>
                                                <span class="text-muted me-2">Pagar ticket</span>
                                                <strong>{{ number_format($stats['pagar']) }}</strong>
                                            </div>
                                            <div>
                                                <span class="text-muted me-2">Anular ticket</span>
                                                <strong>{{ number_format($stats['anular']) }}</strong>
                                            </div>
                                            <div>
                                                <span class="text-muted me-2">Reportar averia</span>
                                                <strong>{{ number_format($stats['averia'] ?? 0) }}</strong>
                                            </div>
                                            <div>
                                                <span class="text-muted me-2">Token No Funciono</span>
                                                <strong class="ticket-dark-text">{{ number_format($stats['token_no_funciono'] ?? 0) }}</strong>
                                            </div>
                                        </div>
                                        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#ticketManualModal">
                                            <i class="ri-add-line me-1"></i>Registro manual
                                        </button>
                                    </div>

                                    <form method="GET" action="{{ route('tickets.index') }}" class="row g-3 align-items-end">
                                    <div class="col-lg-3 col-md-6">
                                        <label class="form-label" for="categoria">Categoria</label>
                                        <select class="form-select" id="categoria" name="categoria">
                                            <option value="">Todas</option>
                                            <option value="pagar_ticket" @selected(($filtros['categoria'] ?? '') === 'pagar_ticket')>Pagar ticket</option>
                                            <option value="anular_ticket" @selected(($filtros['categoria'] ?? '') === 'anular_ticket')>Anular ticket</option>
                                            <option value="reportar_averia" @selected(($filtros['categoria'] ?? '') === 'reportar_averia')>Reportar averia</option>
                                        </select>
                                    </div>
                                    <div class="col-lg-2 col-md-6">
                                        <label class="form-label" for="ticketEstadoDropdown">Estado</label>
                                        <div class="dropdown w-100" id="ticketEstadoFilter" data-selected-label="estado seleccionado" data-selected-label-plural="estados seleccionados">
                                            <button
                                                class="btn btn-outline-secondary dropdown-toggle w-100 d-flex align-items-center justify-content-between"
                                                type="button"
                                                id="ticketEstadoDropdown"
                                                data-bs-toggle="dropdown"
                                                data-bs-auto-close="outside"
                                                aria-expanded="false">
                                                <span class="ticket-estado-filter-text">Todos</span>
                                            </button>
                                            <div class="dropdown-menu w-100 p-2" aria-labelledby="ticketEstadoDropdown">
                                                @foreach ($estadoOpcionesFiltro as $value => $label)
                                                    @php
                                                        $isSelected = in_array($value, $estadosSeleccionadosFiltro, true);
                                                    @endphp
                                                    <button
                                                        type="button"
                                                        class="dropdown-item d-flex align-items-center gap-2 ticket-estado-option {{ $isSelected ? 'active' : '' }}"
                                                        data-value="{{ $value }}"
                                                        aria-pressed="{{ $isSelected ? 'true' : 'false' }}">
                                                        <i class="ri-check-line {{ $isSelected ? '' : 'invisible' }}"></i>
                                                        <span>{{ $label }}</span>
                                                    </button>
                                                @endforeach
                                            </div>
                                            <div class="ticket-estado-hidden-inputs">
                                                @foreach ($estadosSeleccionadosFiltro as $estadoSeleccionado)
                                                    <input type="hidden" name="estado[]" value="{{ $estadoSeleccionado }}">
                                                @endforeach
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-lg-2 col-md-6">
                                        <label class="form-label" for="desde">Desde</label>
                                        <input type="date" class="form-control" id="desde" name="desde" value="{{ $filtros['desde'] ?? '' }}">
                                    </div>
                                    <div class="col-lg-2 col-md-6">
                                        <label class="form-label" for="hasta">Hasta</label>
                                        <input type="date" class="form-control" id="hasta" name="hasta" value="{{ $filtros['hasta'] ?? '' }}">
                                    </div>
                                    <div class="col-lg-3 col-md-12">
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
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-hover align-middle tickets-table">
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
                                                <th>Accion</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse ($solicitudes as $solicitud)
                                                @php
                                                    $tomadoPorMi = (int) ($solicitud->tomado_por_id ?? 0) === (int) auth()->id();
                                                    $tomadoPorOtro = !empty($solicitud->tomado_por_id) && !$tomadoPorMi;
                                                    $gestionCerrada = in_array($solicitud->estado, ['pagado', 'nulo', 'averia_cerrada', 'rechazado'], true);
                                                @endphp
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
                                                        @if ($tomadoPorOtro)
                                                            <span class="text-muted">
                                                                En gestion por {{ $solicitud->tomadoPor?->name ?? 'otro usuario' }}
                                                            </span>
                                                        @elseif ($solicitud->estado === 'ticket_pagado')
                                                            <form method="POST" action="{{ route('tickets.estado', $solicitud) }}">
                                                                @csrf
                                                                @method('PUT')
                                                                <input type="hidden" name="estado" value="pagado">
                                                                <input type="hidden" name="notas" value="{{ $solicitud->notas }}">
                                                                <button class="btn btn-sm btn-success" type="submit">
                                                                    <i class="ri-check-line me-1"></i>Finalizar
                                                                </button>
                                                            </form>
                                                        @elseif (!in_array($solicitud->estado, ['pagado', 'nulo', 'averia_cerrada', 'rechazado'], true))
                                                            @php
                                                                $estadosGestion = match ($solicitud->categoria) {
                                                                    'anular_ticket' => ['pendiente' => 'Pendiente', 'nulo' => 'Nulo', 'rechazado' => 'RECHAZAR'],
                                                                    'reportar_averia' => ['pendiente' => 'Pendiente', 'en_proceso' => 'En Proceso', 'averia_cerrada' => 'Averia Cerrada'],
                                                                    default => $solicitud->estado === 'token_enviado'
                                                                        ? ['pendiente' => 'Pendiente', 'pagado' => 'Pagado', 'token_enviado' => 'Token enviado', 'ticket_pagado' => 'Ticket pagado Por otra Terminal', 'rechazado' => 'RECHAZAR']
                                                                        : ($solicitud->estado === 'token_no_funciono'
                                                                            ? ['pendiente' => 'Pendiente', 'pagado' => 'Pagado', 'token_no_funciono' => 'Token No Funciono', 'ticket_pagado' => 'Ticket pagado Por otra Terminal', 'rechazado' => 'RECHAZAR']
                                                                            : ['pendiente' => 'Pendiente', 'pagado' => 'Pagado', 'ticket_pagado' => 'Ticket pagado Por otra Terminal', 'rechazado' => 'RECHAZAR']),
                                                                };
                                                                $motivosRechazo = $rechazoMotivos[$solicitud->categoria] ?? [];
                                                            @endphp
                                                            <form method="POST" action="{{ route('tickets.estado', $solicitud) }}" class="d-flex flex-wrap gap-2 ticket-estado-form" data-current-estado="{{ $solicitud->estado }}">
                                                                @csrf
                                                                @method('PUT')
                                                                <select class="form-select form-select-sm ticket-estado-select" name="estado" style="max-width: 210px;">
                                                                    @foreach ($estadosGestion as $estadoValue => $estadoLabel)
                                                                        <option value="{{ $estadoValue }}" @selected($solicitud->estado === $estadoValue)>{{ $estadoLabel }}</option>
                                                                    @endforeach
                                                                </select>
                                                                @if (!empty($motivosRechazo))
                                                                    <select class="form-select form-select-sm ticket-rechazo-select d-none" name="rechazo_motivo" style="min-width: 320px;">
                                                                        <option value="">Motivo del rechazo</option>
                                                                        @foreach ($motivosRechazo as $motivo)
                                                                            <option value="{{ $motivo }}">{{ $motivo }}</option>
                                                                        @endforeach
                                                                    </select>
                                                                @endif
                                                                <input type="hidden" name="notas" value="">
                                                                <button class="btn btn-sm btn-success" type="submit">
                                                                    <i class="ri-save-3-line"></i>
                                                                </button>
                                                            </form>
                                                        @else
                                                            <span class="text-muted">Gestion cerrada</span>
                                                        @endif
                                                        @if ($solicitud->procesadoPor)
                                                            <small class="text-muted d-block mt-1">
                                                                Por {{ $solicitud->procesadoPor->name }} - {{ optional($solicitud->procesado_at)->format('d/m/Y h:i A') }}
                                                            </small>
                                                        @endif
                                                    </td>
                                                    <td style="min-width: 150px;">
                                                        @if ($gestionCerrada)
                                                            <span class="badge bg-light text-muted">Finalizado</span>
                                                        @elseif ($tomadoPorMi)
                                                            <div class="d-flex flex-column gap-1">
                                                                <span class="badge bg-success-subtle text-success">
                                                                    <i class="ri-user-follow-line me-1"></i>Tomado por ti
                                                                </span>
                                                                <form method="POST" action="{{ route('tickets.liberar', $solicitud) }}">
                                                                    @csrf
                                                                    @method('DELETE')
                                                                    <button class="btn btn-sm btn-outline-secondary w-100" type="submit">
                                                                        Liberar
                                                                    </button>
                                                                </form>
                                                            </div>
                                                        @elseif ($tomadoPorOtro)
                                                            <span class="badge bg-warning-subtle text-warning">
                                                                <i class="ri-lock-line me-1"></i>{{ $solicitud->tomadoPor?->name ?? 'Tomado' }}
                                                            </span>
                                                        @else
                                                            <form method="POST" action="{{ route('tickets.tomar', $solicitud) }}">
                                                                @csrf
                                                                <button class="btn btn-sm btn-outline-primary w-100" type="submit">
                                                                    <i class="ri-user-add-line me-1"></i>Tomar
                                                                </button>
                                                            </form>
                                                        @endif
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="9" class="text-center text-muted py-4">
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
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="ticketManualModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <form method="POST" action="{{ route('tickets.store') }}">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title">Registro manual</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body vstack gap-3">
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
                        <div>
                            <label class="form-label" for="manual_attachment_url">Imagen del comprobante</label>
                            <input type="url" class="form-control" id="manual_attachment_url" name="attachment_url" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary">
                            <i class="ri-add-line me-1"></i>Registrar
                        </button>
                    </div>
                </form>
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

    <div class="modal fade" id="ticketTerminalPagoModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <form id="ticketTerminalPagoForm">
                    <div class="modal-header">
                        <h5 class="modal-title" id="ticketTerminalPagoModalTitle">Ticket pagado Por otra Terminal</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <label class="form-label" for="terminal_pago_numero" id="terminalPagoLabel">Terminal que pago</label>
                        <input
                            type="text"
                            class="form-control"
                            id="terminal_pago_numero"
                            inputmode="numeric"
                            autocomplete="off"
                            placeholder="Ej: 0705888"
                            required>
                        <div class="invalid-feedback" id="terminalPagoFeedback">Indica el numero de terminal que pago.</div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-success">
                            <i class="ri-save-3-line me-1"></i>Guardar
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@section('script')
    <script>
        (function () {
            const filter = document.getElementById('ticketEstadoFilter');

            if (!filter) {
                return;
            }

            const text = filter.querySelector('.ticket-estado-filter-text');
            const hiddenInputs = filter.querySelector('.ticket-estado-hidden-inputs');
            const options = Array.from(filter.querySelectorAll('.ticket-estado-option'));
            const labels = new Map(options.map((option) => [option.dataset.value, option.textContent.trim()]));

            function selectedValues() {
                return Array.from(hiddenInputs.querySelectorAll('input[name="estado[]"]'))
                    .map((input) => input.value)
                    .filter(Boolean);
            }

            function setSelectedValues(values) {
                hiddenInputs.innerHTML = '';

                values.forEach(function (value) {
                    const input = document.createElement('input');
                    input.type = 'hidden';
                    input.name = 'estado[]';
                    input.value = value;
                    hiddenInputs.appendChild(input);
                });
            }

            function refreshUi() {
                const selected = selectedValues();

                options.forEach(function (option) {
                    const isSelected = selected.includes(option.dataset.value);
                    option.classList.toggle('active', isSelected);
                    option.setAttribute('aria-pressed', isSelected ? 'true' : 'false');
                    option.querySelector('i')?.classList.toggle('invisible', !isSelected);
                });

                if (!text) {
                    return;
                }

                if (selected.length === 0) {
                    text.textContent = 'Todos';
                } else if (selected.length === 1) {
                    text.textContent = labels.get(selected[0]) || '1 estado seleccionado';
                } else {
                    text.textContent = `${selected.length} estados seleccionados`;
                }
            }

            options.forEach(function (option) {
                option.addEventListener('click', function () {
                    const value = option.dataset.value;
                    const selected = selectedValues();
                    const nextSelected = selected.includes(value)
                        ? selected.filter((item) => item !== value)
                        : selected.concat(value);

                    setSelectedValues(nextSelected);
                    refreshUi();
                });
            });

            refreshUi();
        })();

        (function () {
            const modalEl = document.getElementById('ticketTerminalPagoModal');
            const modalForm = document.getElementById('ticketTerminalPagoForm');
            const terminalInput = document.getElementById('terminal_pago_numero');
            const modalTitle = document.getElementById('ticketTerminalPagoModalTitle');
            const terminalLabel = document.getElementById('terminalPagoLabel');
            const terminalFeedback = document.getElementById('terminalPagoFeedback');
            let pendingForm = null;
            let pendingValuePrefix = 'Terminal que pago ';

            if (!modalEl || !modalForm || !terminalInput || !modalTitle || !terminalLabel || !terminalFeedback || !window.bootstrap) {
                return;
            }

            const modal = new bootstrap.Modal(modalEl);

            function modalContextFor(form) {
                const estado = form.querySelector('[name="estado"]')?.value || '';
                const options = Array.from(form.querySelectorAll('[name="estado"] option'));
                const hasTicketPagado = options.some((option) => option.value === 'ticket_pagado');
                const currentEstado = form.dataset.currentEstado || '';

                if (estado === 'pagado' && hasTicketPagado && currentEstado !== 'token_enviado') {
                    return {
                        label: 'Numeracion del token',
                        title: 'Enviar token',
                        feedback: 'Indica la numeracion del token.',
                        placeholder: 'Ej: 123456',
                        valuePrefix: '',
                    };
                }

                if (estado === 'ticket_pagado') {
                    return {
                        label: 'Terminal que pago',
                        title: 'Ticket pagado Por otra Terminal',
                        feedback: 'Indica el numero de terminal que pago.',
                        placeholder: 'Ej: 0705888',
                        valuePrefix: 'Terminal que pago ',
                    };
                }

                if (estado === 'nulo' && !hasTicketPagado) {
                    return {
                        label: 'Terminal anulado',
                        title: 'Anular ticket',
                        feedback: 'Indica el codigo de terminal anulado.',
                        placeholder: 'Ej: 0705888',
                        valuePrefix: 'Terminal anulado ',
                    };
                }

                return null;
            }

            function toggleRejectReason(form) {
                const estado = form.querySelector('[name="estado"]')?.value || '';
                const rechazoSelect = form.querySelector('[name="rechazo_motivo"]');

                if (!rechazoSelect) {
                    return;
                }

                const isRejected = estado === 'rechazado';
                rechazoSelect.classList.toggle('d-none', !isRejected);
                rechazoSelect.required = isRejected;

                if (!isRejected) {
                    rechazoSelect.value = '';
                    rechazoSelect.classList.remove('is-invalid');
                }
            }

            document.querySelectorAll('.ticket-estado-form').forEach(function (form) {
                const estadoSelect = form.querySelector('[name="estado"]');
                if (estadoSelect) {
                    toggleRejectReason(form);
                    estadoSelect.addEventListener('change', function () {
                        toggleRejectReason(form);
                    });
                }
                form.querySelector('[name="rechazo_motivo"]')?.addEventListener('change', function (event) {
                    event.target.classList.remove('is-invalid');
                });

                form.addEventListener('submit', function (event) {
                    const estado = form.querySelector('[name="estado"]')?.value || '';
                    const rechazoSelect = form.querySelector('[name="rechazo_motivo"]');

                    if (estado === 'rechazado') {
                        if (!rechazoSelect || rechazoSelect.value.trim() === '') {
                            event.preventDefault();
                            rechazoSelect?.classList.add('is-invalid');
                            rechazoSelect?.focus();
                            return;
                        }

                        form.querySelector('[name="notas"]').value = rechazoSelect.value;
                    }

                    const context = modalContextFor(form);

                    if (!context || form.dataset.confirmedTerminalPago === '1') {
                        return;
                    }

                    event.preventDefault();
                    pendingForm = form;
                    pendingValuePrefix = context.valuePrefix;
                    modalTitle.textContent = context.title;
                    terminalLabel.textContent = context.label;
                    terminalFeedback.textContent = context.feedback;
                    terminalInput.placeholder = context.placeholder;
                    terminalInput.value = '';
                    terminalInput.classList.remove('is-invalid');
                    modal.show();
                });
            });

            modalEl.addEventListener('shown.bs.modal', function () {
                terminalInput.focus();
            });

            modalEl.addEventListener('hidden.bs.modal', function () {
                pendingForm = null;
                terminalInput.value = '';
                terminalInput.classList.remove('is-invalid');
            });

            modalForm.addEventListener('submit', function (event) {
                event.preventDefault();

                const terminal = terminalInput.value.trim();
                if (terminal === '') {
                    terminalInput.classList.add('is-invalid');
                    terminalInput.focus();
                    return;
                }

                if (!pendingForm) {
                    modal.hide();
                    return;
                }

                pendingForm.querySelector('[name="notas"]').value = `${pendingValuePrefix}${terminal}`;
                pendingForm.dataset.confirmedTerminalPago = '1';
                modal.hide();
                pendingForm.requestSubmit();
            });
        })();

        (function () {
            const modal = document.getElementById('ticketImageModal');
            const image = document.getElementById('ticketImagePreview');
            const link = document.getElementById('ticketImageLink');
            const title = document.getElementById('ticketImageModalTitle');
            const storageKey = 'tickets.image_modal.restore';

            if (!modal || !image || !link || !title) {
                return;
            }

            function setImageModal(url, codigo) {
                image.src = url;
                link.href = url;
                title.textContent = `Imagen de ${codigo || 'Ticket'}`;
            }

            modal.addEventListener('show.bs.modal', function (event) {
                const trigger = event.relatedTarget;

                if (!trigger) {
                    return;
                }

                const url = trigger?.getAttribute('data-attachment-url') || '';
                const codigo = trigger?.getAttribute('data-ticket-codigo') || 'Ticket';

                setImageModal(url, codigo);
            });

            modal.addEventListener('hidden.bs.modal', function () {
                image.src = '';
                link.href = '#';
                title.textContent = 'Imagen de ticket';
                window.sessionStorage.removeItem(storageKey);
            });

            try {
                const rawRestore = window.sessionStorage.getItem(storageKey);
                const restore = rawRestore ? JSON.parse(rawRestore) : null;

                if (restore?.url && (!restore.expiresAt || Date.now() < restore.expiresAt) && window.bootstrap) {
                    window.sessionStorage.removeItem(storageKey);
                    setImageModal(restore.url, restore.codigo || 'Ticket');
                    bootstrap.Modal.getOrCreateInstance(modal).show();
                }
            } catch (error) {
                window.sessionStorage.removeItem(storageKey);
            }
        })();

        (function () {
            const activityUrl = @json($ticketActivityUrl ?? null);
            let currentSignature = @json($ticketFeedSignature ?? null);
            const imageModal = document.getElementById('ticketImageModal');
            const imagePreview = document.getElementById('ticketImagePreview');
            const imageTitle = document.getElementById('ticketImageModalTitle');
            const imageRestoreStorageKey = 'tickets.image_modal.restore';

            if (!activityUrl || !currentSignature) {
                return;
            }

            let pollTimer = null;

            function rememberOpenImageModal() {
                if (!imageModal?.classList.contains('show') || !imagePreview?.src) {
                    return;
                }

                window.sessionStorage.setItem(imageRestoreStorageKey, JSON.stringify({
                    url: imagePreview.src,
                    codigo: (imageTitle?.textContent || 'Imagen de ticket').replace(/^Imagen de\s+/i, ''),
                    expiresAt: Date.now() + 5 * 60 * 1000,
                }));
            }

            async function checkTicketActivity() {
                if (document.hidden) {
                    return;
                }

                try {
                    const response = await fetch(activityUrl, {
                        method: 'GET',
                        headers: {
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest',
                        },
                        cache: 'no-store',
                    });

                    if (!response.ok) {
                        return;
                    }

                    const data = await response.json();
                    const signature = data?.signature || null;

                    if (!signature) {
                        return;
                    }

                    if (signature !== currentSignature) {
                        currentSignature = signature;

                        if (imageModal?.classList.contains('show')) {
                            rememberOpenImageModal();
                        }

                        window.location.reload();
                    }
                } catch (error) {
                    // Polling silencioso para no interrumpir al usuario.
                }
            }

            pollTimer = window.setInterval(checkTicketActivity, 5000);

            window.addEventListener('beforeunload', function () {
                if (pollTimer) {
                    window.clearInterval(pollTimer);
                }
            });
        })();
    </script>
@endsection
