@extends('app')

@php
    $procesosPorDepto = [
        'gerencia' => [
            'icono' => 'ri-briefcase-4-line',
            'color' => 'primary',
            'descripcion' => 'Supervision estrategica, toma de decisiones y seguimiento de indicadores clave del negocio.',
            'procesos' => [
                ['nombre' => 'Planificacion Estrategica', 'icono' => 'ri-bar-chart-grouped-line', 'desc' => 'Definicion de objetivos, metas y estrategias del periodo.'],
                ['nombre' => 'Reuniones de Seguimiento', 'icono' => 'ri-team-line', 'desc' => 'Coordinacion semanal con jefes de departamento.'],
                ['nombre' => 'Aprobacion de Presupuestos', 'icono' => 'ri-money-dollar-circle-line', 'desc' => 'Revision y aprobacion de gastos e inversiones.'],
                ['nombre' => 'Indicadores de Gestion (KPIs)', 'icono' => 'ri-dashboard-3-line', 'desc' => 'Monitoreo de indicadores clave de rendimiento.'],
                ['nombre' => 'Auditoria Interna', 'icono' => 'ri-file-search-line', 'desc' => 'Verificacion de cumplimiento de procesos y controles.'],
                ['nombre' => 'Reportes Ejecutivos', 'icono' => 'ri-file-chart-line', 'desc' => 'Generacion de informes para la alta direccion.'],
            ],
        ],
        'contabilidad' => [
            'icono' => 'ri-calculator-line',
            'color' => 'success',
            'descripcion' => 'Gestion financiera, registros contables, facturacion y control de pagos.',
            'procesos' => [
                ['nombre' => 'Cuentas por Cobrar', 'icono' => 'ri-hand-coin-line', 'desc' => 'Seguimiento de facturas pendientes de cobro.'],
                ['nombre' => 'Cuentas por Pagar', 'icono' => 'ri-wallet-3-line', 'desc' => 'Control de obligaciones y pagos a proveedores.'],
                ['nombre' => 'Conciliacion Bancaria', 'icono' => 'ri-bank-line', 'desc' => 'Verificacion de movimientos bancarios vs registros.'],
                ['nombre' => 'Facturacion', 'icono' => 'ri-bill-line', 'desc' => 'Emision y control de comprobantes fiscales.'],
                ['nombre' => 'Cierre Mensual', 'icono' => 'ri-calendar-check-line', 'desc' => 'Proceso de cierre contable de cada periodo.'],
                ['nombre' => 'Reportes Financieros', 'icono' => 'ri-line-chart-line', 'desc' => 'Estados financieros y reportes de gestion.'],
            ],
        ],
        'recursos-humanos' => [
            'icono' => 'ri-group-line',
            'color' => 'warning',
            'descripcion' => 'Administracion del talento humano, nomina, evaluaciones y bienestar del personal.',
            'procesos' => [
                ['nombre' => 'Reclutamiento y Seleccion', 'icono' => 'ri-user-search-line', 'desc' => 'Busqueda, entrevistas y contratacion de personal.'],
                ['nombre' => 'Nomina', 'icono' => 'ri-money-dollar-box-line', 'desc' => 'Calculo y pago de salarios y beneficios.'],
                ['nombre' => 'Evaluacion de Desempeno', 'icono' => 'ri-star-line', 'desc' => 'Medicion del rendimiento de los colaboradores.'],
                ['nombre' => 'Capacitacion', 'icono' => 'ri-graduation-cap-line', 'desc' => 'Planes de formacion y desarrollo profesional.'],
                ['nombre' => 'Control de Asistencia', 'icono' => 'ri-time-line', 'desc' => 'Registro de entradas, salidas y permisos.'],
                ['nombre' => 'Clima Organizacional', 'icono' => 'ri-emotion-happy-line', 'desc' => 'Encuestas y acciones de bienestar laboral.'],
            ],
        ],
        'operaciones' => [
            'icono' => 'ri-settings-3-line',
            'color' => 'info',
            'descripcion' => 'Gestion operativa de agencias, logistica y control de calidad del servicio.',
            'procesos' => [
                ['nombre' => 'Gestion de Agencias', 'icono' => 'ri-building-2-line', 'desc' => 'Supervision y control de operaciones en agencias.'],
                ['nombre' => 'Control de Inventario', 'icono' => 'ri-archive-line', 'desc' => 'Seguimiento de equipos, materiales y suministros.'],
                ['nombre' => 'Logistica', 'icono' => 'ri-truck-line', 'desc' => 'Coordinacion de entregas y traslados.'],
                ['nombre' => 'Control de Calidad', 'icono' => 'ri-shield-check-line', 'desc' => 'Verificacion de estandares en la operacion.'],
                ['nombre' => 'Incidencias Operativas', 'icono' => 'ri-alarm-warning-line', 'desc' => 'Registro y resolucion de problemas operativos.'],
                ['nombre' => 'Reportes Operativos', 'icono' => 'ri-file-list-3-line', 'desc' => 'Informes diarios y semanales de operacion.'],
            ],
        ],
        'comercial' => [
            'icono' => 'ri-shopping-bag-line',
            'color' => 'danger',
            'descripcion' => 'Estrategias de ventas, atencion al cliente, promociones y desarrollo de mercado.',
            'procesos' => [
                ['nombre' => 'Gestion de Ventas', 'icono' => 'ri-shopping-cart-2-line', 'desc' => 'Seguimiento de metas y rendimiento comercial.'],
                ['nombre' => 'Atencion al Cliente', 'icono' => 'ri-customer-service-2-line', 'desc' => 'Soporte y resolucion de consultas de clientes.'],
                ['nombre' => 'Promociones y Campanas', 'icono' => 'ri-megaphone-line', 'desc' => 'Planificacion y ejecucion de ofertas comerciales.'],
                ['nombre' => 'Analisis de Mercado', 'icono' => 'ri-pie-chart-line', 'desc' => 'Estudio de tendencias y competencia.'],
                ['nombre' => 'Cartera de Clientes', 'icono' => 'ri-contacts-book-line', 'desc' => 'Gestion y fidelizacion de la base de clientes.'],
                ['nombre' => 'Reportes Comerciales', 'icono' => 'ri-funds-line', 'desc' => 'Indicadores de ventas y rendimiento comercial.'],
            ],
        ],
        'mantenimiento' => [
            'icono' => 'ri-tools-line',
            'color' => 'secondary',
            'descripcion' => 'Mantenimiento preventivo y correctivo de equipos, instalaciones y sistemas.',
            'procesos' => [
                ['nombre' => 'Mantenimiento Preventivo', 'icono' => 'ri-calendar-todo-line', 'desc' => 'Programacion de mantenimientos periodicos.'],
                ['nombre' => 'Mantenimiento Correctivo', 'icono' => 'ri-hammer-line', 'desc' => 'Atencion y reparacion de fallas reportadas.'],
                ['nombre' => 'Orden de Trabajo', 'icono' => 'ri-task-line', 'desc' => 'Creacion y seguimiento de ordenes de trabajo.'],
                ['nombre' => 'Inventario de Repuestos', 'icono' => 'ri-archive-drawer-line', 'desc' => 'Control de piezas y materiales disponibles.'],
                ['nombre' => 'Historial de Equipos', 'icono' => 'ri-history-line', 'desc' => 'Registro de intervenciones por equipo.'],
                ['nombre' => 'Reportes de Mantenimiento', 'icono' => 'ri-file-text-line', 'desc' => 'Informes de estado y costos de mantenimiento.'],
            ],
        ],
        'tecnologia' => [
            'icono' => 'ri-computer-line',
            'color' => 'dark',
            'descripcion' => 'Soporte tecnico, infraestructura, desarrollo de sistemas y seguridad informatica.',
            'procesos' => [
                ['nombre' => 'Soporte Tecnico', 'icono' => 'ri-customer-service-line', 'desc' => 'Atencion de tickets y soporte a usuarios.'],
                ['nombre' => 'Infraestructura y Redes', 'icono' => 'ri-router-line', 'desc' => 'Administracion de servidores, redes y conectividad.'],
                ['nombre' => 'Desarrollo de Sistemas', 'icono' => 'ri-code-s-slash-line', 'desc' => 'Creacion y mejora de aplicaciones internas.'],
                ['nombre' => 'Seguridad Informatica', 'icono' => 'ri-shield-keyhole-line', 'desc' => 'Proteccion de datos y prevencion de amenazas.'],
                ['nombre' => 'Respaldos y Recuperacion', 'icono' => 'ri-database-2-line', 'desc' => 'Copias de seguridad y planes de contingencia.'],
                ['nombre' => 'Gestion de Licencias', 'icono' => 'ri-key-2-line', 'desc' => 'Control de software y licenciamiento.'],
            ],
        ],
    ];

    $depto = $procesosPorDepto[$departamentoSlug] ?? $procesosPorDepto['gerencia'];
    $procesosBaseEditados = $procesosBaseEditados ?? collect();
    $procesosCustom = $procesosCustom ?? collect();
@endphp

@section('content')
    <div class="main-content">
        <div class="page-content">
            <div class="container-fluid">

                {{-- Titulo y Breadcrumb --}}
                <div class="row">
                    <div class="col-12">
                        <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                            <h4 class="mb-sm-0">
                                <i class="{{ $depto['icono'] }} me-1"></i> Procesos - {{ $departamentoNombre }}
                            </h4>
                            <div class="page-title-right">
                                <ol class="breadcrumb m-0">
                                    <li class="breadcrumb-item"><a href="{{ route('inicio.index') }}">Inicio</a></li>
                                    <li class="breadcrumb-item"><a href="{{ route('procesos.index') }}">Procesos</a></li>
                                    <li class="breadcrumb-item active">{{ $departamentoNombre }}</li>
                                </ol>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Navegacion de Departamentos --}}
                <div class="row mb-3">
                    <div class="col-12">
                        <div class="d-flex flex-wrap gap-2">
                            @foreach($departamentos as $slug => $nombre)
                                <a href="{{ route('procesos.departamento', ['departamento' => $slug]) }}"
                                   class="btn {{ $departamentoSlug === $slug ? 'btn-'.$depto['color'] : 'btn-soft-secondary' }} btn-sm">
                                    <i class="{{ $procesosPorDepto[$slug]['icono'] ?? 'ri-folder-line' }} me-1"></i>{{ $nombre }}
                                </a>
                            @endforeach
                        </div>
                    </div>
                </div>

                {{-- Descripcion del Departamento --}}
                <div class="row mb-3">
                    <div class="col-12">
                        <div class="alert alert-{{ $depto['color'] }} alert-borderless shadow-sm mb-0" role="alert">
                            <i class="{{ $depto['icono'] }} me-1 fs-16 align-middle"></i>
                            <strong>{{ $departamentoNombre }}:</strong> {{ $depto['descripcion'] }}
                        </div>
                    </div>
                </div>

                {{-- Tarjetas de Procesos (Predeterminados) --}}
                <div class="row g-3">
                    @foreach($depto['procesos'] as $index => $proceso)
                        @php
                            $procesoBase = $proceso['nombre'];
                            $procesoEditado = $procesosBaseEditados->get($procesoBase);
                            $procesoNombre = $procesoEditado->nombre ?? $proceso['nombre'];
                            $procesoIcono = $procesoEditado->icono ?? $proceso['icono'];
                            $procesoDescripcion = $procesoEditado->descripcion ?? $proceso['desc'];
                            $protocoloProceso = $procesoEditado->protocolo ?? '';
                        @endphp
                        <div class="col-xl-4 col-md-6">
                            <div class="card card-height-100 border-0 shadow-sm overflow-hidden">
                                <div class="card-body d-flex align-items-start gap-3">
                                    <div class="flex-shrink-0">
                                        <div class="avatar-sm">
                                            <div class="avatar-title bg-{{ $depto['color'] }}-subtle text-{{ $depto['color'] }} rounded-circle fs-20">
                                                <i class="{{ $procesoIcono }}"></i>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="flex-grow-1">
                                        <h6 class="card-title mb-1">{{ $procesoNombre }}</h6>
                                        <p class="text-muted mb-2 fs-13">{{ $procesoDescripcion }}</p>
                                        @if(!empty($protocoloProceso))
                                            <span class="badge bg-success-subtle text-success fs-11">
                                                <i class="ri-check-line me-1 align-middle"></i>Protocolo definido
                                            </span>
                                        @else
                                            <span class="badge bg-{{ $depto['color'] }}-subtle text-{{ $depto['color'] }} fs-11">
                                                <i class="ri-circle-fill fs-7 me-1 align-middle"></i>Sin protocolo
                                            </span>
                                        @endif
                                    </div>
                                </div>
                                <div class="card-footer bg-transparent border-top py-2">
                                    <div class="d-flex align-items-center justify-content-between gap-2 flex-wrap">
                                        <span class="text-muted fs-12"><i class="ri-time-line me-1"></i>Predeterminado</span>
                                        <div class="d-flex align-items-center gap-2">
                                            <button class="btn btn-sm btn-soft-info btn-editar-proceso-base"
                                                    data-proceso-base="{{ $procesoBase }}"
                                                    data-nombre="{{ $procesoNombre }}"
                                                    data-icono="{{ $procesoIcono }}"
                                                    data-descripcion="{{ $procesoDescripcion }}"
                                                    data-protocolo="{{ $protocoloProceso }}"
                                                    title="Editar tarjeta">
                                                <i class="ri-pencil-line me-1"></i>Editar
                                            </button>
                                            <button class="btn btn-sm btn-soft-{{ $depto['color'] }} btn-protocolo"
                                                    data-nombre="{{ $procesoNombre }}"
                                                    data-proceso-base="{{ $procesoBase }}"
                                                    data-protocolo="{{ $protocoloProceso }}"
                                                    data-tipo="predeterminado">
                                                <i class="ri-edit-line me-1"></i>Protocolo
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach

                    {{-- Tarjetas de Procesos Personalizados (BD) --}}
                    @foreach($procesosCustom as $custom)
                        <div class="col-xl-4 col-md-6" id="card-custom-{{ $custom->id }}">
                            <div class="card card-height-100 border-0 shadow-sm overflow-hidden border-start border-3 border-{{ $depto['color'] }}">
                                <div class="card-body d-flex align-items-start gap-3">
                                    <div class="flex-shrink-0">
                                        <div class="avatar-sm">
                                            <div class="avatar-title bg-{{ $depto['color'] }}-subtle text-{{ $depto['color'] }} rounded-circle fs-20">
                                                <i class="{{ $custom->icono }}"></i>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="flex-grow-1">
                                        <h6 class="card-title mb-1">
                                            {{ $custom->nombre }}
                                            <span class="badge bg-{{ $depto['color'] }}-subtle text-{{ $depto['color'] }} fs-10 ms-1">Personalizado</span>
                                        </h6>
                                        <p class="text-muted mb-2 fs-13">{{ $custom->descripcion }}</p>
                                        @if(!empty($custom->protocolo))
                                            <span class="badge bg-success-subtle text-success fs-11">
                                                <i class="ri-check-line me-1 align-middle"></i>Protocolo definido
                                            </span>
                                        @else
                                            <span class="badge bg-warning-subtle text-warning fs-11">
                                                <i class="ri-circle-fill fs-7 me-1 align-middle"></i>Sin protocolo
                                            </span>
                                        @endif
                                    </div>
                                </div>
                                <div class="card-footer bg-transparent border-top py-2">
                                    <div class="d-flex align-items-center justify-content-between">
                                        <div class="d-flex align-items-center gap-2">
                                            <button class="btn btn-sm btn-soft-info btn-editar-proceso"
                                                    data-id="{{ $custom->id }}"
                                                    data-nombre="{{ $custom->nombre }}"
                                                    data-icono="{{ $custom->icono }}"
                                                    data-descripcion="{{ $custom->descripcion ?? '' }}"
                                                    data-protocolo="{{ $custom->protocolo ?? '' }}"
                                                    title="Editar tarjeta">
                                                <i class="ri-pencil-line me-1"></i>Editar
                                            </button>
                                            <button class="btn btn-sm btn-soft-danger btn-eliminar-proceso"
                                                    data-id="{{ $custom->id }}"
                                                    data-nombre="{{ $custom->nombre }}">
                                                <i class="ri-delete-bin-line"></i>
                                            </button>
                                        </div>
                                        <button class="btn btn-sm btn-soft-{{ $depto['color'] }} btn-protocolo"
                                                data-nombre="{{ $custom->nombre }}"
                                                data-protocolo="{{ $custom->protocolo ?? '' }}"
                                                data-tipo="personalizado"
                                                data-id="{{ $custom->id }}"
                                                title="Editar protocolo">
                                            <i class="ri-flow-chart me-1"></i>Protocolo
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach

                    {{-- Tarjeta para Agregar Nuevo Proceso --}}
                    <div class="col-xl-4 col-md-6">
                        <div class="card card-height-100 border-2 border-dashed border-{{ $depto['color'] }} bg-{{ $depto['color'] }}-subtle bg-opacity-10 shadow-none overflow-hidden"
                             role="button" id="btnAbrirNuevoProceso" style="min-height: 180px;">
                            <div class="card-body d-flex flex-column align-items-center justify-content-center text-center">
                                <div class="avatar-md mb-2">
                                    <div class="avatar-title bg-{{ $depto['color'] }}-subtle text-{{ $depto['color'] }} rounded-circle fs-24">
                                        <i class="ri-add-line"></i>
                                    </div>
                                </div>
                                <h6 class="text-{{ $depto['color'] }} mb-1">Nuevo Proceso</h6>
                                <p class="text-muted fs-13 mb-0">Agregar tarjeta personalizada</p>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>

    {{-- Modal: Editar Protocolo / Flujo de Trabajo --}}
    <div class="modal fade" id="modalProtocolo" tabindex="-1" aria-labelledby="modalProtocoloLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-{{ $depto['color'] }}-subtle">
                    <h5 class="modal-title" id="modalProtocoloLabel">
                        <i class="ri-flow-chart me-1"></i> Protocolo / Flujo de Trabajo
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Proceso:</label>
                        <span id="protocoloNombreProceso" class="badge bg-{{ $depto['color'] }} fs-13"></span>
                    </div>
                    <div class="mb-3">
                        <label for="protocoloTexto" class="form-label fw-semibold">
                            <i class="ri-file-list-3-line me-1"></i>Protocolo / Flujo de Trabajo
                        </label>
                        <textarea class="form-control" id="protocoloTexto" rows="12"
                                  placeholder="Describe aqui el protocolo o flujo de trabajo paso a paso...&#10;&#10;Ejemplo:&#10;1. Recibir solicitud del area&#10;2. Verificar documentacion&#10;3. Aprobar o rechazar&#10;4. Notificar al solicitante"></textarea>
                        <div class="form-text">Puedes escribir los pasos numerados, instrucciones o descripcion del flujo de trabajo.</div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light btn-sm" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-{{ $depto['color'] }} btn-sm" id="btnGuardarProtocolo">
                        <i class="ri-save-line me-1"></i>Guardar Protocolo
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- Modal: Crear Nuevo Proceso --}}
    <div class="modal fade" id="modalNuevoProceso" tabindex="-1" aria-labelledby="modalNuevoProcesoLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-{{ $depto['color'] }}-subtle">
                    <h5 class="modal-title" id="modalNuevoProcesoLabel">
                        <i class="ri-add-circle-line me-1"></i> Nuevo Proceso Personalizado
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-8">
                            <label for="nuevoNombre" class="form-label fw-semibold">Nombre del Proceso <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="nuevoNombre" maxlength="150"
                                   placeholder="Ej: Control de Documentos">
                        </div>
                        <div class="col-md-4">
                            <label for="nuevoIcono" class="form-label fw-semibold">Icono</label>
                            <select class="form-select" id="nuevoIcono">
                                <option value="ri-file-list-3-line">Documento</option>
                                <option value="ri-bar-chart-grouped-line">Grafico</option>
                                <option value="ri-team-line">Equipo</option>
                                <option value="ri-money-dollar-circle-line">Finanzas</option>
                                <option value="ri-shield-check-line">Verificacion</option>
                                <option value="ri-settings-3-line">Configuracion</option>
                                <option value="ri-calendar-check-line">Calendario</option>
                                <option value="ri-task-line">Tarea</option>
                                <option value="ri-truck-line">Logistica</option>
                                <option value="ri-customer-service-line">Soporte</option>
                                <option value="ri-code-s-slash-line">Desarrollo</option>
                                <option value="ri-database-2-line">Base de Datos</option>
                                <option value="ri-tools-line">Herramientas</option>
                                <option value="ri-alarm-warning-line">Alerta</option>
                                <option value="ri-folder-open-line">Carpeta</option>
                            </select>
                        </div>
                        <div class="col-12">
                            <label for="nuevaDescripcion" class="form-label fw-semibold">Descripcion</label>
                            <input type="text" class="form-control" id="nuevaDescripcion" maxlength="500"
                                   placeholder="Breve descripcion del proceso">
                        </div>
                        <div class="col-12">
                            <label for="nuevoProtocolo" class="form-label fw-semibold">
                                <i class="ri-flow-chart me-1"></i>Protocolo / Flujo de Trabajo
                            </label>
                            <textarea class="form-control" id="nuevoProtocolo" rows="8"
                                      placeholder="Describe aqui el protocolo o flujo de trabajo paso a paso (opcional)..."></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light btn-sm" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-{{ $depto['color'] }} btn-sm" id="btnCrearProceso">
                        <i class="ri-add-line me-1"></i>Crear Proceso
                    </button>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('script')
<script>
(function () {
    var departamento = '{{ $departamentoSlug }}';
    var modalProtocolo = new bootstrap.Modal(document.getElementById('modalProtocolo'));
    var modalNuevo = new bootstrap.Modal(document.getElementById('modalNuevoProceso'));
    var protocoloActual = { nombre: '', tipo: '', id: null, procesoBase: null };
    var editandoProcesoId = null;
    var editandoProcesoModo = null;
    var editandoProcesoBase = null;

    function resetModalProceso() {
        editandoProcesoId = null;
        editandoProcesoModo = null;
        editandoProcesoBase = null;
        document.getElementById('nuevoNombre').value = '';
        document.getElementById('nuevaDescripcion').value = '';
        document.getElementById('nuevoProtocolo').value = '';
        document.getElementById('nuevoIcono').selectedIndex = 0;
    }

    function setEstadoBotonProceso() {
        var btn = document.getElementById('btnCrearProceso');
        if (editandoProcesoModo === 'predeterminado' || editandoProcesoModo === 'personalizado') {
            btn.innerHTML = '<i class="ri-save-line me-1"></i>Guardar Cambios';
            return;
        }

        btn.innerHTML = '<i class="ri-add-line me-1"></i>Crear Proceso';
    }

    // --- Abrir modal de protocolo ---
    document.querySelectorAll('.btn-protocolo').forEach(function (btn) {
        btn.addEventListener('click', function () {
            protocoloActual.nombre = this.dataset.nombre;
            protocoloActual.tipo = this.dataset.tipo;
            protocoloActual.id = this.dataset.id || null;
            protocoloActual.procesoBase = this.dataset.procesoBase || null;
            document.getElementById('protocoloNombreProceso').textContent = protocoloActual.nombre;
            document.getElementById('protocoloTexto').value = this.dataset.protocolo || '';
            modalProtocolo.show();
        });
    });

    // --- Guardar protocolo ---
    document.getElementById('btnGuardarProtocolo').addEventListener('click', function () {
        var btn = this;
        var textoProtocolo = document.getElementById('protocoloTexto').value.trim();
        btn.disabled = true;
        btn.innerHTML = '<i class="ri-loader-4-line me-1 spin"></i>Guardando...';

        fetch('{{ route("procesos.guardarProtocolo") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': APP_CSRF,
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                departamento: departamento,
                nombre: protocoloActual.nombre,
                proceso_base: protocoloActual.procesoBase,
                protocolo: textoProtocolo
            })
        })
        .then(function (r) { return r.json(); })
        .then(function (data) {
            btn.disabled = false;
            btn.innerHTML = '<i class="ri-save-line me-1"></i>Guardar Protocolo';
            if (data.ok) {
                modalProtocolo.hide();
                Swal.fire({
                    icon: 'success',
                    title: 'Protocolo guardado',
                    text: 'El protocolo de "' + protocoloActual.nombre + '" se guardo correctamente.',
                    confirmButtonText: 'OK',
                    customClass: { confirmButton: 'btn btn-primary btn-sm' },
                    buttonsStyling: false
                }).then(function () {
                    location.reload();
                });
            }
        })
        .catch(function () {
            btn.disabled = false;
            btn.innerHTML = '<i class="ri-save-line me-1"></i>Guardar Protocolo';
            Swal.fire({ icon: 'error', title: 'Error', text: 'No se pudo guardar el protocolo.', customClass: { confirmButton: 'btn btn-danger btn-sm' }, buttonsStyling: false });
        });
    });

    // --- Abrir modal nuevo proceso ---
    document.getElementById('btnAbrirNuevoProceso').addEventListener('click', function () {
        resetModalProceso();
        document.getElementById('modalNuevoProcesoLabel').innerHTML = '<i class="ri-add-circle-line me-1"></i> Nuevo Proceso Personalizado';
        setEstadoBotonProceso();
        modalNuevo.show();
    });

    // --- Abrir modal para editar proceso predeterminado ---
    document.querySelectorAll('.btn-editar-proceso-base').forEach(function (btn) {
        btn.addEventListener('click', function () {
            resetModalProceso();
            editandoProcesoModo = 'predeterminado';
            editandoProcesoBase = this.dataset.procesoBase || '';
            document.getElementById('modalNuevoProcesoLabel').innerHTML = '<i class="ri-pencil-line me-1"></i> Editar Tarjeta';
            setEstadoBotonProceso();
            document.getElementById('nuevoNombre').value = this.dataset.nombre || '';
            document.getElementById('nuevaDescripcion').value = this.dataset.descripcion || '';
            document.getElementById('nuevoProtocolo').value = this.dataset.protocolo || '';
            document.getElementById('nuevoIcono').value = this.dataset.icono || 'ri-file-list-3-line';
            modalNuevo.show();
        });
    });

    // --- Abrir modal para editar proceso ---
    document.querySelectorAll('.btn-editar-proceso').forEach(function (btn) {
        btn.addEventListener('click', function () {
            resetModalProceso();
            editandoProcesoModo = 'personalizado';
            editandoProcesoId = this.dataset.id;
            document.getElementById('modalNuevoProcesoLabel').innerHTML = '<i class="ri-pencil-line me-1"></i> Editar Proceso Personalizado';
            setEstadoBotonProceso();
            document.getElementById('nuevoNombre').value = this.dataset.nombre || '';
            document.getElementById('nuevaDescripcion').value = this.dataset.descripcion || '';
            document.getElementById('nuevoProtocolo').value = this.dataset.protocolo || '';
            document.getElementById('nuevoIcono').value = this.dataset.icono || 'ri-file-list-3-line';
            modalNuevo.show();
        });
    });

    // --- Crear proceso ---
    document.getElementById('btnCrearProceso').addEventListener('click', function () {
        var nombre = document.getElementById('nuevoNombre').value.trim();
        if (!nombre) {
            Swal.fire({ icon: 'warning', title: 'Campo requerido', text: 'El nombre del proceso es obligatorio.', customClass: { confirmButton: 'btn btn-warning btn-sm' }, buttonsStyling: false });
            return;
        }
        var btn = this;
        btn.disabled = true;
        btn.innerHTML = '<i class="ri-loader-4-line me-1 spin"></i>' + (editandoProcesoModo ? 'Guardando...' : 'Creando...');

        var url = '{{ route("procesos.crearProceso") }}';
        var method = 'POST';

        if (editandoProcesoModo === 'predeterminado') {
            url = '{{ route("procesos.actualizarProcesoBase") }}';
        } else if (editandoProcesoModo === 'personalizado' && editandoProcesoId) {
            url = '/procesos/' + editandoProcesoId;
            method = 'PUT';
        }

        fetch(url, {
            method: method,
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': APP_CSRF,
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                departamento: departamento,
                proceso_base: editandoProcesoBase,
                nombre: nombre,
                descripcion: document.getElementById('nuevaDescripcion').value.trim(),
                icono: document.getElementById('nuevoIcono').value,
                protocolo: document.getElementById('nuevoProtocolo').value.trim() || null
            })
        })
        .then(function (r) { return r.json(); })
        .then(function (data) {
            btn.disabled = false;
            setEstadoBotonProceso();
            if (data.ok) {
                modalNuevo.hide();
                var esEdicion = editandoProcesoModo === 'predeterminado' || editandoProcesoModo === 'personalizado';
                Swal.fire({
                    icon: 'success',
                    title: esEdicion ? 'Tarjeta actualizada' : 'Proceso creado',
                    text: esEdicion ? '"' + nombre + '" se actualizo correctamente.' : '"' + nombre + '" se agrego correctamente.',
                    confirmButtonText: 'OK',
                    customClass: { confirmButton: 'btn btn-primary btn-sm' },
                    buttonsStyling: false
                }).then(function () {
                    resetModalProceso();
                    location.reload();
                });
            }
        })
        .catch(function () {
            btn.disabled = false;
            setEstadoBotonProceso();
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: editandoProcesoModo ? 'No se pudo actualizar la tarjeta.' : 'No se pudo crear el proceso.',
                customClass: { confirmButton: 'btn btn-danger btn-sm' },
                buttonsStyling: false
            });
        });
    });

    // --- Eliminar proceso personalizado ---
    document.querySelectorAll('.btn-eliminar-proceso').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var id = this.dataset.id;
            var nombre = this.dataset.nombre;
            Swal.fire({
                icon: 'warning',
                title: 'Eliminar proceso',
                html: 'Se eliminara <strong>' + nombre + '</strong> y su protocolo. Esta accion no se puede deshacer.',
                showCancelButton: true,
                confirmButtonText: 'Si, eliminar',
                cancelButtonText: 'Cancelar',
                customClass: { confirmButton: 'btn btn-danger btn-sm me-2', cancelButton: 'btn btn-light btn-sm' },
                buttonsStyling: false
            }).then(function (result) {
                if (result.isConfirmed) {
                    fetch('/procesos/' + id, {
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': APP_CSRF,
                            'Accept': 'application/json'
                        }
                    })
                    .then(function (r) { return r.json(); })
                    .then(function (data) {
                        if (data.ok) {
                            var card = document.getElementById('card-custom-' + id);
                            if (card) card.remove();
                            Swal.fire({ icon: 'success', title: 'Eliminado', text: '"' + nombre + '" fue eliminado.', timer: 1500, showConfirmButton: false });
                        }
                    })
                    .catch(function () {
                        Swal.fire({ icon: 'error', title: 'Error', text: 'No se pudo eliminar el proceso.', customClass: { confirmButton: 'btn btn-danger btn-sm' }, buttonsStyling: false });
                    });
                }
            });
        });
    });
})();
</script>
<style>
    @keyframes spin { from { transform: rotate(0deg); } to { transform: rotate(360deg); } }
    .spin { display: inline-block; animation: spin 1s linear infinite; }
</style>
@endsection

