@extends('app')

@section('content')
    <style>
        #modalCrearTareaInc,
        #modalConfigEstadosInc {
            z-index: 1065;
        }

        .swal2-container {
            z-index: 2000;
        }
    </style>

    <div class="main-content">
        <div class="page-content">
            <div class="container-fluid">

                <div class="row">
                    <div class="col-12">
                        <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                            <h4 class="mb-sm-0">Incumplimiento de Horario por Agencia</h4>

                            <div class="page-title-right">
                                <ol class="breadcrumb m-0">
                                    <li class="breadcrumb-item"><a href="{{ route('inicio.index') }}">Inicio</a></li>
                                    <li class="breadcrumb-item"><a href="{{ route('mantenimiento.index') }}">Mantenimientos</a></li>
                                    <li class="breadcrumb-item"><a href="{{ route('agencias.index') }}">Agencias</a></li>
                                    <li class="breadcrumb-item active">Incumplimientos</li>
                                </ol>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-lg-12">
                        <div class="card">
                            <div class="card-header">
                                <div class="row g-2 align-items-end">
                                    <div class="col-md-3">
                                        <label for="fecha" class="form-label mb-1">Fecha</label>
                                        <input type="date" id="fecha" class="form-control" value="{{ now()->toDateString() }}">
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-check form-switch mt-4">
                                            <input class="form-check-input" type="checkbox" id="soloIncumplidas" checked>
                                            <label class="form-check-label" for="soloIncumplidas">Solo incumplidas</label>
                                        </div>
                                    </div>
                                    <div class="col-md-6 text-md-end">
                                        <button type="button" class="btn btn-info" id="btnConfigEstados">
                                            <i class="ri-settings-3-line me-1"></i> Configurar estados
                                        </button>
                                        <button type="button" class="btn btn-primary" id="btnConsultar">
                                            <i class="ri-search-line me-1"></i> Consultar
                                        </button>
                                        <a href="{{ route('agencias.index') }}" class="btn btn-light">
                                            <i class="ri-arrow-left-line me-1"></i> Volver
                                        </a>
                                    </div>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="alert alert-info mb-3" id="resumenBox">
                                    Selecciona una fecha para consultar.
                                </div>

                                <div class="table-responsive">
                                    <table id="tableIncumplimientos" class="table table-bordered table-striped align-middle" style="width:100%; font-size:0.85rem;">
                                        <thead class="table-light">
                                            <tr>
                                                <th>Agencia</th>
                                                <th>Entrada AM</th>
                                                <th>Salida AM</th>
                                                <th>Entrada PM</th>
                                                <th>Salida PM</th>
                                                <th>Entrada  AM Real</th>
                                                <th>Salida AM Real</th>
                                                <th>Entrada PM Real</th>
                                                <th>Salida PM Real</th>
                                                <th>Min. Tarde</th>
                                                <th>Min. Salida Antes</th>
                                                <th>Fuente</th>
                                                <th>Estado</th>
                                                <th>Observaciones</th>
                                                <th>Acciones</th>
                                            </tr>
                                        </thead>
                                        <tbody></tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="modal fade" id="modalCrearTareaInc" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog modal-lg">
                        <div class="modal-content">
                            <div class="modal-header bg-primary text-white">
                                <h5 class="modal-title"><i class="ri-task-line me-2"></i>Crear tarea desde incumplimiento</h5>
                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body">
                                <form id="formTareaIncumplimiento">
                                    <input type="hidden" id="inc-row-data">
                                    <div class="row g-3">
                                        <div class="col-12">
                                            <label class="form-label">Título <span class="text-danger">*</span></label>
                                            <input type="text" class="form-control" id="inc-tarea-titulo" required>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">Departamento <span class="text-danger">*</span></label>
                                            <select class="form-select" id="inc-tarea-departamento" required>
                                                <option value="">Cargando departamentos...</option>
                                            </select>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">Prioridad <span class="text-danger">*</span></label>
                                            <select class="form-select" id="inc-tarea-prioridad">
                                                <option value="baja">🟢 Baja</option>
                                                <option value="media" selected>🔵 Media</option>
                                                <option value="alta">🟡 Alta</option>
                                                <option value="critica">🔴 Crítica</option>
                                            </select>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">Asignar a</label>
                                            <select class="form-select" id="inc-tarea-asignado">
                                                <option value="">Cargando usuarios...</option>
                                            </select>
                                        </div>
                                        <div class="col-12">
                                            <label class="form-label">Descripción</label>
                                            <textarea class="form-control" id="inc-tarea-descripcion" rows="3"></textarea>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">Fecha inicio <span class="text-danger">*</span></label>
                                            <input type="date" class="form-control" id="inc-tarea-inicio" required>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">Fecha fin <span class="text-danger">*</span></label>
                                            <input type="date" class="form-control" id="inc-tarea-fin" required>
                                        </div>
                                    </div>
                                </form>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancelar</button>
                                <button type="button" class="btn btn-primary" id="btnGuardarTareaInc">
                                    <i class="ri-save-line me-1"></i> Guardar tarea
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="modal fade" id="modalConfigEstadosInc" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog modal-lg modal-dialog-scrollable">
                        <div class="modal-content">
                            <div class="modal-header bg-info text-white">
                                <h5 class="modal-title"><i class="ri-settings-3-line me-2"></i>Configurar estados por minutos</h5>
                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body">
                                <p class="text-muted mb-3">Define etiquetas y rangos de minutos (se toma el mayor entre Min. tarde y Min. salida antes).</p>

                                <div class="table-responsive">
                                    <table class="table table-bordered align-middle mb-0" style="font-size: 0.9rem;">
                                        <thead class="table-light">
                                            <tr>
                                                <th style="width: 38%;">Etiqueta</th>
                                                <th style="width: 20%;">Desde (min)</th>
                                                <th style="width: 20%;">Hasta (min)</th>
                                                <th style="width: 22%;">Color</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <td><input type="text" class="form-control" id="cfg-label-0"></td>
                                                <td><input type="number" min="0" class="form-control" id="cfg-min-0"></td>
                                                <td><input type="number" min="0" class="form-control" id="cfg-max-0"></td>
                                                <td>
                                                    <select class="form-select" id="cfg-color-0">
                                                        <option value="success">Verde</option>
                                                        <option value="primary">Azul</option>
                                                        <option value="warning">Amarillo</option>
                                                        <option value="danger">Rojo</option>
                                                        <option value="secondary">Gris</option>
                                                        <option value="dark">Negro</option>
                                                    </select>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td><input type="text" class="form-control" id="cfg-label-1"></td>
                                                <td><input type="number" min="0" class="form-control" id="cfg-min-1"></td>
                                                <td><input type="number" min="0" class="form-control" id="cfg-max-1"></td>
                                                <td>
                                                    <select class="form-select" id="cfg-color-1">
                                                        <option value="success">Verde</option>
                                                        <option value="primary">Azul</option>
                                                        <option value="warning">Amarillo</option>
                                                        <option value="danger">Rojo</option>
                                                        <option value="secondary">Gris</option>
                                                        <option value="dark">Negro</option>
                                                    </select>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td><input type="text" class="form-control" id="cfg-label-2"></td>
                                                <td><input type="number" min="0" class="form-control" id="cfg-min-2"></td>
                                                <td><input type="number" min="0" class="form-control" id="cfg-max-2"></td>
                                                <td>
                                                    <select class="form-select" id="cfg-color-2">
                                                        <option value="success">Verde</option>
                                                        <option value="primary">Azul</option>
                                                        <option value="warning">Amarillo</option>
                                                        <option value="danger">Rojo</option>
                                                        <option value="secondary">Gris</option>
                                                        <option value="dark">Negro</option>
                                                    </select>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td><input type="text" class="form-control" id="cfg-label-3"></td>
                                                <td><input type="number" min="0" class="form-control" id="cfg-min-3"></td>
                                                <td><input type="number" min="0" class="form-control" id="cfg-max-3" placeholder="Vacío = sin límite"></td>
                                                <td>
                                                    <select class="form-select" id="cfg-color-3">
                                                        <option value="success">Verde</option>
                                                        <option value="primary">Azul</option>
                                                        <option value="warning">Amarillo</option>
                                                        <option value="danger">Rojo</option>
                                                        <option value="secondary">Gris</option>
                                                        <option value="dark">Negro</option>
                                                    </select>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-light" id="btnRestablecerConfigEstados">Restablecer</button>
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                                <button type="button" class="btn btn-info" id="btnGuardarConfigEstados">
                                    <i class="ri-save-line me-1"></i> Guardar configuración
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>

        <footer class="footer">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-sm-6">
                        <script>document.write(new Date().getFullYear())</script> © CRM.
                    </div>
                </div>
            </div>
        </footer>
    </div>
@endsection

@section('script')
<script>
    let table;
    let ultimaRespuesta = [];
    const CSRF = '{{ csrf_token() }}';
    const URL_TAREAS = '{{ url('/tareas') }}';
    const URL_DEPTOS = '{{ url('/tareas/departamentos') }}';
    const URL_USUARIOS_TAREAS = '{{ url('/tareas/usuarios') }}';
    const CFG_STORAGE_KEY = 'incumplimientos_config_estados_v1';

    const CFG_DEFAULT = {
        niveles: [
            { key: 'cumple', label: 'Cumplen dentro de 5 minutos', min: 0, max: 5, color: 'success' },
            { key: 'aviso', label: 'Aviso dentro de 6-8 minutos', min: 6, max: 8, color: 'primary' },
            { key: 'incumple', label: 'Incumple dentro de 9-15 minutos', min: 9, max: 15, color: 'warning' },
            { key: 'requiere_visita', label: 'Requiere visita dentro de 16 minutos y más', min: 16, max: null, color: 'danger' },
        ]
    };

    let configEstados = cargarConfigEstados();

    function clonarConfig(base) {
        return JSON.parse(JSON.stringify(base));
    }

    function cargarConfigEstados() {
        try {
            const raw = localStorage.getItem(CFG_STORAGE_KEY);
            if (!raw) return clonarConfig(CFG_DEFAULT);
            const parsed = JSON.parse(raw);
            if (!parsed || !Array.isArray(parsed.niveles) || parsed.niveles.length !== 4) {
                return clonarConfig(CFG_DEFAULT);
            }
            return parsed;
        } catch (e) {
            return clonarConfig(CFG_DEFAULT);
        }
    }

    function guardarConfigEstadosEnStorage() {
        localStorage.setItem(CFG_STORAGE_KEY, JSON.stringify(configEstados));
    }

    function poblarModalConfigEstados() {
        configEstados.niveles.forEach((nivel, i) => {
            $(`#cfg-label-${i}`).val(nivel.label || '');
            $(`#cfg-min-${i}`).val((nivel.min ?? 0));
            $(`#cfg-max-${i}`).val(nivel.max ?? '');
            $(`#cfg-color-${i}`).val(nivel.color || 'secondary');
        });
    }

    function leerConfigEstadosDesdeModal() {
        const niveles = [0, 1, 2, 3].map((i) => {
            const min = parseInt($(`#cfg-min-${i}`).val(), 10);
            const maxRaw = $(`#cfg-max-${i}`).val();
            const max = maxRaw === '' ? null : parseInt(maxRaw, 10);
            return {
                key: (configEstados.niveles[i]?.key || `nivel_${i}`),
                label: ($(`#cfg-label-${i}`).val() || '').trim(),
                min: Number.isNaN(min) ? 0 : min,
                max: Number.isNaN(max) ? null : max,
                color: $(`#cfg-color-${i}`).val() || 'secondary',
            };
        });

        return { niveles };
    }

    function validarConfigEstados(cfg) {
        if (!cfg || !Array.isArray(cfg.niveles) || cfg.niveles.length !== 4) {
            return 'La configuración debe tener 4 niveles.';
        }

        for (let i = 0; i < cfg.niveles.length; i++) {
            const n = cfg.niveles[i];

            if (!n.label) {
                return `La etiqueta del nivel ${i + 1} es obligatoria.`;
            }

            if (n.min == null || Number.isNaN(n.min) || n.min < 0) {
                return `El "Desde" del nivel ${i + 1} debe ser un número mayor o igual a 0.`;
            }

            if (n.max != null && (Number.isNaN(n.max) || n.max < 0)) {
                return `El "Hasta" del nivel ${i + 1} debe ser un número mayor o igual a 0 o vacío.`;
            }

            if (n.max != null && n.max < n.min) {
                return `En el nivel ${i + 1}, "Hasta" no puede ser menor que "Desde".`;
            }
        }

        return null;
    }

    function obtenerMinutosImpacto(row) {
        const tarde = Math.round(parseFloat(row?.minutos_tarde || 0));
        const salidaAntes = Math.round(parseFloat(row?.minutos_salida_antes || 0));
        return Math.max(tarde, salidaAntes);
    }

    function evaluarEstadoPorConfig(minutos) {
        const niveles = [...(configEstados.niveles || [])].sort((a, b) => (a.min ?? 0) - (b.min ?? 0));

        for (const nivel of niveles) {
            const min = Number(nivel.min ?? 0);
            const max = nivel.max == null ? null : Number(nivel.max);

            if (minutos >= min && (max == null || minutos <= max)) {
                return nivel;
            }
        }

        return niveles[niveles.length - 1] || { key: 'sin_estado', label: 'Sin estado', color: 'secondary' };
    }

    function aplicarClasificacionConfig(filas) {
        return (filas || []).map((row) => {
            const minutosImpacto = obtenerMinutosImpacto(row);
            const estadoCfg = evaluarEstadoPorConfig(minutosImpacto);

            return {
                ...row,
                estado: estadoCfg.label,
                estado_key: estadoCfg.key,
                estado_color: estadoCfg.color || 'secondary',
                minutos_impacto: minutosImpacto,
                incumplida: estadoCfg.key !== 'cumple',
            };
        });
    }

    function renderizarTablaConConfig(resp) {
        const filasClasificadas = aplicarClasificacionConfig(resp.data || []);
        const soloIncumplidas = $('#soloIncumplidas').is(':checked');
        const data = soloIncumplidas
            ? filasClasificadas.filter((r) => r.estado_key !== 'cumple')
            : filasClasificadas;

        if (table) {
            table.clear().rows.add(data).draw();
        }

        const total = filasClasificadas.length;
        const aviso = filasClasificadas.filter((r) => r.estado_key === 'aviso').length;
        const incumple = filasClasificadas.filter((r) => r.estado_key === 'incumple').length;
        const visita = filasClasificadas.filter((r) => r.estado_key === 'requiere_visita').length;

        $('#resumenBox').html(
            `<strong>Fecha:</strong> ${resp.fecha} | <strong>Total:</strong> ${total} | <strong>Aviso:</strong> ${aviso} | <strong>Incumple:</strong> ${incumple} | <strong>Requiere visita:</strong> ${visita}`
        );
    }

    function cargarDepartamentosTareas() {
        $.getJSON(URL_DEPTOS, function(data) {
            let options = '<option value="">Seleccionar...</option>';
            (data || []).forEach(function(d) {
                options += `<option value="${d.id}">${d.nombre}</option>`;
            });
            $('#inc-tarea-departamento').html(options);
        }).fail(function() {
            $('#inc-tarea-departamento').html('<option value="">No se pudieron cargar</option>');
        });
    }

    function cargarUsuariosTareas() {
        $.getJSON(URL_USUARIOS_TAREAS, function(data) {
            let options = '<option value="">Sin asignar</option>';
            (data || []).forEach(function(u) {
                options += `<option value="${u.id}">${u.name}</option>`;
            });
            $('#inc-tarea-asignado').html(options);
        }).fail(function() {
            $('#inc-tarea-asignado').html('<option value="">No se pudieron cargar</option>');
        });
    }

    function limpiarBackdropsBootstrap() {
        if (document.querySelector('.modal.show')) {
            return;
        }

        document.querySelectorAll('.modal-backdrop').forEach(function (backdrop) {
            backdrop.remove();
        });
        document.body.classList.remove('modal-open');
        document.body.style.removeProperty('overflow');
        document.body.style.removeProperty('padding-right');
    }

    function prepararModalBootstrap(modalId) {
        const modalEl = document.getElementById(modalId);
        if (!modalEl) {
            return null;
        }

        if (modalEl.parentElement !== document.body) {
            document.body.appendChild(modalEl);
        }

        if (modalEl.dataset.backdropCleanup !== '1') {
            modalEl.addEventListener('hidden.bs.modal', limpiarBackdropsBootstrap);
            modalEl.dataset.backdropCleanup = '1';
        }

        return modalEl;
    }

    function abrirModalBootstrap(modalId) {
        const modalEl = prepararModalBootstrap(modalId);
        if (!modalEl) {
            return;
        }

        if (window.bootstrap?.Modal) {
            bootstrap.Modal.getOrCreateInstance(modalEl).show();
            return;
        }

        $(`#${modalId}`).modal('show');
    }

    function cerrarModalBootstrap(modalId) {
        const modalEl = document.getElementById(modalId);
        if (!modalEl) {
            return;
        }

        if (window.bootstrap?.Modal) {
            bootstrap.Modal.getOrCreateInstance(modalEl).hide();
        } else {
            $(`#${modalId}`).modal('hide');
        }

        setTimeout(limpiarBackdropsBootstrap, 250);
    }

    function abrirModalCrearTareaDesdeFila(row) {
        const fecha = $('#fecha').val() || '{{ now()->toDateString() }}';
        const tituloDefault = `Seguimiento incumplimiento - Agencia ${row.agencia || '-'} (${row.nombre_agencia || '-'})`;
        const descripcionDefault = `Se crea tarea de seguimiento por incumplimiento de horario.\n\n` +
            `Fecha: ${fecha}\n` +
            `Terminal: ${row.terminal || '-'}\n` +
            `Agencia: ${row.agencia || '-'}\n` +
            `Nombre: ${row.nombre_agencia || '-'}\n` +
            `Estado: ${row.estado || '-'}\n` +
            `Observaciones: ${row.observaciones || '-'}\n` +
            `Min. tarde: ${row.minutos_tarde || 0}\n` +
            `Min. salida antes: ${row.minutos_salida_antes || 0}`;

        $('#inc-row-data').val(JSON.stringify(row));
        $('#inc-tarea-titulo').val(tituloDefault);
        $('#inc-tarea-prioridad').val((row.estado === 'INCUMPLE') ? 'alta' : 'media');
        $('#inc-tarea-descripcion').val(descripcionDefault);
        $('#inc-tarea-inicio').val(fecha);
        $('#inc-tarea-fin').val(fecha);
        $('#inc-tarea-departamento').val('');
        $('#inc-tarea-asignado').val('');

        abrirModalBootstrap('modalCrearTareaInc');
    }

    function guardarTareaDesdeIncumplimiento() {
        const payload = {
            titulo: $('#inc-tarea-titulo').val(),
            descripcion: $('#inc-tarea-descripcion').val(),
            departamento_id: $('#inc-tarea-departamento').val(),
            prioridad: $('#inc-tarea-prioridad').val(),
            fecha_inicio: $('#inc-tarea-inicio').val(),
            fecha_fin: $('#inc-tarea-fin').val(),
            asignado_id: $('#inc-tarea-asignado').val() || null,
        };

        if (!payload.titulo || !payload.departamento_id || !payload.fecha_inicio || !payload.fecha_fin) {
            Swal.fire('Campos requeridos', 'Completa título, departamento y fechas para crear la tarea.', 'warning');
            return;
        }

        Swal.fire({
            title: 'Guardando tarea...',
            text: 'Por favor espera.',
            allowOutsideClick: false,
            didOpen: () => Swal.showLoading()
        });

        $.ajax({
            url: URL_TAREAS,
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': CSRF },
            contentType: 'application/json',
            data: JSON.stringify(payload),
            success: function(resp) {
                cerrarModalBootstrap('modalCrearTareaInc');
                Swal.fire('Tarea creada', resp.message || 'La tarea fue creada correctamente.', 'success');
            },
            error: function(xhr) {
                const errors = xhr?.responseJSON?.errors;
                if (errors) {
                    const msg = Object.values(errors).flat().join('<br>');
                    Swal.fire({ icon: 'error', title: 'Error de validación', html: msg });
                    return;
                }
                const errorMsg = xhr?.responseJSON?.message || 'No se pudo crear la tarea.';
                Swal.fire('Error', errorMsg, 'error');
            }
        });
    }

    async function solicitarCorreoYEnviar(row) {
        const result = await Swal.fire({
            title: 'Enviar mini reporte',
            text: 'Indica el correo destino para esta fila.',
            input: 'email',
            inputLabel: 'Correo destino',
            inputPlaceholder: 'ejemplo@correo.com',
            showCancelButton: true,
            confirmButtonText: 'Enviar correo',
            cancelButtonText: 'Cancelar',
            confirmButtonColor: '#405189',
            inputValidator: (value) => {
                if (!value) {
                    return 'Debes indicar un correo destino';
                }
            }
        });

        if (!result.isConfirmed || !result.value) {
            return;
        }

        Swal.fire({
            title: 'Enviando...',
            text: 'Por favor espera un momento.',
            allowOutsideClick: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });

        $.ajax({
            url: '{{ route('agencias.incumplimientos.send-mail') }}',
            method: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                email: result.value,
                fecha: $('#fecha').val(),
                registro: row
            },
            success: function(resp) {
                Swal.fire('Correo enviado', resp.message || 'Mini reporte enviado correctamente.', 'success');
            },
            error: function(xhr) {
                const errorMsg = xhr?.responseJSON?.message || 'No se pudo enviar el correo.';
                Swal.fire('Error', errorMsg, 'error');
            }
        });
    }

    function cargarData() {
        const fecha = $('#fecha').val();

        $.ajax({
            url: '{{ route('agencias.incumplimientos.list') }}',
            method: 'GET',
            data: {
                fecha: fecha,
                solo_incumplidas: 0
            },
            success: function(resp) {
                ultimaRespuesta = resp || { data: [] };
                renderizarTablaConConfig(ultimaRespuesta);
            },
            error: function() {
                Swal.fire('Error', 'No se pudo cargar la información.', 'error');
            }
        });
    }

    $(document).ready(function() {
        ['modalCrearTareaInc', 'modalConfigEstadosInc'].forEach(function (modalId) {
            prepararModalBootstrap(modalId);
        });

        table = $('#tableIncumplimientos').DataTable({
            data: [],
            responsive: true,
            scrollX: true,
            columns: [
                { data: 'nombre_agencia', render: function(data, type, row) {
                    return (data || row.agencia || '-') + `<div class="text-muted fs-11">Cod: ${row.agencia || '-'}</div>`;
                }},
                { data: 'entrada_am_programada', defaultContent: '-', className: 'text-center', render: function(data) {
                    return data || '-';
                }},
                { data: 'salida_am_programada', defaultContent: '-', className: 'text-center', render: function(data) {
                    return data || '-';
                }},
                { data: 'entrada_pm_programada', defaultContent: '-', className: 'text-center', render: function(data) {
                    return data || '-';
                }},
                { data: 'salida_pm_programada', defaultContent: '-', className: 'text-center', render: function(data) {
                    return data || '-';
                }},
                { data: 'entrada_real', className: 'text-center' },
                { data: 'salida_am_real', className: 'text-center' },
                { data: 'entrada_pm_real', className: 'text-center' },
                { data: 'salida_real', className: 'text-center' },
                { data: 'minutos_tarde', className: 'text-center', render: function(data) {
                    const minutos = Math.round(parseFloat(data || 0));
                    return minutos > 0 ? `<span class="badge bg-danger">${minutos}</span>` : '<span class="badge bg-success">0</span>';
                }},
                { data: 'minutos_salida_antes', className: 'text-center', render: function(data) {
                    const minutos = Math.round(parseFloat(data || 0));
                    return minutos > 0 ? `<span class="badge bg-warning text-dark">${minutos}</span>` : '<span class="badge bg-success">0</span>';
                }},
                { data: 'fuente', className: 'text-center' },
                { data: 'estado', className: 'text-center', render: function(data, type, row) {
                    const badgeColor = row?.estado_color || 'secondary';
                    return `<span class="badge bg-${badgeColor}">${data || 'Sin estado'}</span>`;
                }},
                { data: 'observaciones' },
                { data: null, orderable: false, searchable: false, className: 'text-center', render: function(data, type, row) {
                    const rowData = encodeURIComponent(JSON.stringify(row));
                    return `<div class="d-flex gap-1 justify-content-center">
                                <button type="button" class="btn btn-sm btn-soft-primary btnEnviarCorreo" data-row="${rowData}" title="Enviar mini reporte por correo">
                                    <i class="ri-mail-send-line me-1"></i> Enviar
                                </button>
                                <button type="button" class="btn btn-sm btn-soft-info btnCrearTarea" data-row="${rowData}" title="Crear tarea de seguimiento">
                                    <i class="ri-task-line me-1"></i> Tarea
                                </button>
                            </div>`;
                }},
            ],
            language: {
                url: '//cdn.datatables.net/plug-ins/1.11.5/i18n/es-ES.json'
            },
            dom: '<"row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6"f>>rtip'
        });

        $('#tableIncumplimientos').on('click', '.btnEnviarCorreo', function() {
            const encoded = $(this).attr('data-row');

            if (!encoded) {
                Swal.fire('Error', 'No se encontró información para enviar.', 'error');
                return;
            }

            try {
                const row = JSON.parse(decodeURIComponent(encoded));
                solicitarCorreoYEnviar(row);
            } catch (e) {
                Swal.fire('Error', 'No se pudo procesar la fila seleccionada.', 'error');
            }
        });

        $('#tableIncumplimientos').on('click', '.btnCrearTarea', function() {
            const encoded = $(this).attr('data-row');

            if (!encoded) {
                Swal.fire('Error', 'No se encontró información para crear la tarea.', 'error');
                return;
            }

            try {
                const row = JSON.parse(decodeURIComponent(encoded));
                abrirModalCrearTareaDesdeFila(row);
            } catch (e) {
                Swal.fire('Error', 'No se pudo procesar la fila seleccionada.', 'error');
            }
        });

        $('#btnGuardarTareaInc').on('click', guardarTareaDesdeIncumplimiento);

        $('#btnConfigEstados').on('click', function() {
            poblarModalConfigEstados();
            abrirModalBootstrap('modalConfigEstadosInc');
        });

        $('#btnGuardarConfigEstados').on('click', function() {
            const cfg = leerConfigEstadosDesdeModal();
            const error = validarConfigEstados(cfg);

            if (error) {
                Swal.fire('Configuración inválida', error, 'warning');
                return;
            }

            configEstados = cfg;
            guardarConfigEstadosEnStorage();
            cerrarModalBootstrap('modalConfigEstadosInc');

            if (ultimaRespuesta && Array.isArray(ultimaRespuesta.data)) {
                renderizarTablaConConfig(ultimaRespuesta);
            }

            Swal.fire('Configuración guardada', 'Los estados fueron actualizados correctamente.', 'success');
        });

        $('#btnRestablecerConfigEstados').on('click', function() {
            configEstados = clonarConfig(CFG_DEFAULT);
            poblarModalConfigEstados();
        });

        cargarDepartamentosTareas();
        cargarUsuariosTareas();

        $('#btnConsultar').on('click', cargarData);
        $('#soloIncumplidas').on('change', function() {
            if (ultimaRespuesta && Array.isArray(ultimaRespuesta.data)) {
                renderizarTablaConConfig(ultimaRespuesta);
            }
        });

        cargarData();
    });
</script>
@endsection
