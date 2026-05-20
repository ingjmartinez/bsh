@extends('app')

@section('content')
    <div class="main-content">
        <div class="page-content">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-12">
                        <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                            <h4 class="mb-sm-0">Catálogo de Juegos</h4>
                            <div class="page-title-right">
                                <ol class="breadcrumb m-0">
                                    <li class="breadcrumb-item"><a href="{{ route('inicio.index') }}">Inicio</a></li>
                                    <li class="breadcrumb-item"><a href="{{ route('mantenimiento.index') }}">Mantenimientos</a></li>
                                    <li class="breadcrumb-item active">Catálogo de Juegos</li>
                                </ol>
                            </div>
                        </div>
                    </div>
                </div>

                @if(session('success'))
                    <div class="alert alert-success py-2">{{ session('success') }}</div>
                @endif

                @if($errors->any())
                    <div class="alert alert-danger py-2">{{ $errors->first() }}</div>
                @endif

                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0">Listado</h5>
                        <div class="d-flex gap-2 align-items-end flex-wrap">
                            <div>
                                <label class="form-label form-label-sm mb-1">Año</label>
                                <input type="number" min="2000" max="2100" id="detectar-anio" class="form-control form-control-sm" value="" placeholder="Año" style="width: 95px;">
                            </div>
                            <div>
                                <label class="form-label form-label-sm mb-1">Mes</label>
                                <select id="detectar-mes" class="form-select form-select-sm" style="width: 130px;">
                                    <option value="">Seleccionar</option>
                                    @for($m = 1; $m <= 12; $m++)
                                        @php
                                            $mesNombre = \Carbon\Carbon::create()->month($m)->locale('es')->translatedFormat('F');
                                        @endphp
                                        <option value="{{ $m }}">
                                            {{ ucfirst($mesNombre) }}
                                        </option>
                                    @endfor
                                </select>
                            </div>
                            <div class="form-check mt-4 pt-1">
                                <input class="form-check-input" type="checkbox" id="detectar-todo">
                                <label class="form-check-label small" for="detectar-todo">
                                    Todo
                                </label>
                            </div>
                            <button class="btn btn-info btn-sm" id="btn-detectar-nuevos">
                                <i class="ri-search-line me-1"></i>Detectar nuevos
                            </button>
                            <button class="btn btn-secondary btn-sm" id="btn-ver-comparativo-sql">
                                <i class="ri-table-line me-1"></i>Consulta SQL (3 columnas)
                            </button>
                            <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#modalCrearJuego">
                                <i class="ri-add-line me-1"></i>Nuevo juego
                            </button>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped align-middle mb-0" id="tabla-catalogo-juegos">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Producto ID</th>
                                        <th>Tipo</th>
                                        <th>Descripción</th>
                                        <th style="width: 140px;">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($juegos as $juego)
                                        <tr>
                                            <td>{{ $juego->id }}</td>
                                            <td>{{ $juego->producto_id }}</td>
                                            <td>{{ $juego->tipo }}</td>
                                            <td>{{ $juego->descripcion }}</td>
                                            <td>
                                                <button
                                                    class="btn btn-warning btn-sm btn-editar-juego"
                                                    data-id="{{ $juego->id }}"
                                                    data-producto-id="{{ $juego->producto_id }}"
                                                    data-tipo="{{ $juego->tipo }}"
                                                    data-descripcion="{{ $juego->descripcion }}"
                                                >
                                                    Editar
                                                </button>
                                                <button
                                                    class="btn btn-danger btn-sm btn-eliminar-juego"
                                                    data-id="{{ $juego->id }}"
                                                    data-producto-id="{{ $juego->producto_id }}"
                                                >
                                                    Eliminar
                                                </button>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="5" class="text-center text-muted">No hay juegos registrados.</td>
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

    <div class="modal fade" id="modalCrearJuego" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST" action="{{ route('mantenimiento.catalogo-juegos.store') }}">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title">Nuevo juego</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Producto ID</label>
                            <input type="text" class="form-control" name="producto_id" maxlength="12" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Tipo</label>
                            <input type="text" class="form-control" name="tipo" maxlength="255" required>
                        </div>
                        <div class="mb-0">
                            <label class="form-label">Descripción</label>
                            <input type="text" class="form-control" name="descripcion" maxlength="255" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary">Guardar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modalEditarJuego" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST" id="form-editar-juego">
                    @csrf
                    @method('PUT')
                    <div class="modal-header">
                        <h5 class="modal-title">Editar juego</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Producto ID</label>
                            <input type="text" class="form-control" name="producto_id" id="edit-producto-id" maxlength="12" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Tipo</label>
                            <input type="text" class="form-control" name="tipo" id="edit-tipo" maxlength="255" required>
                        </div>
                        <div class="mb-0">
                            <label class="form-label">Descripción</label>
                            <input type="text" class="form-control" name="descripcion" id="edit-descripcion" maxlength="255" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary">Actualizar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modalDetectarNuevos" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Productos detectados fuera del catálogo</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-info py-2 mb-3">
                        Esta detección solo informa productos nuevos. La inserción se hará solo con los seleccionados.
                        <div class="mt-1"><strong>Consulta:</strong> <span id="texto-periodo-deteccion">-</span></div>
                        <div class="mt-1 small text-muted"><strong>Debug:</strong> <span id="texto-debug-deteccion">-</span></div>
                    </div>
                    <div id="detectar-loader" class="text-center py-4 d-none">
                        <div class="spinner-border text-primary" role="status"></div>
                        <div class="mt-2 text-muted">Buscando productos nuevos...</div>
                    </div>
                    <div id="detectar-vacio" class="alert alert-success d-none mb-0">
                        No se encontraron productos nuevos.
                    </div>
                    <div id="detectar-contenido" class="d-none">
                        <div class="mb-2 text-end">
                            <button type="button" class="btn btn-outline-secondary btn-sm" id="btn-toggle-no-catalogo">
                                Ver solo "No en catálogo"
                            </button>
                        </div>
                        <div class="form-check mb-2">
                            <input class="form-check-input" type="checkbox" id="check-todos-detectados" checked>
                            <label class="form-check-label" for="check-todos-detectados">
                                Seleccionar todos
                            </label>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped align-middle mb-0">
                                <thead>
                                    <tr>
                                        <th style="width:40px;" class="th-seleccion"></th>
                                        <th id="th-producto-id">Producto ID</th>
                                        <th class="th-completa">Descripción (API)</th>
                                        <th class="th-completa">Tipo</th>
                                        <th class="th-completa">Origen(es)</th>
                                    </tr>
                                </thead>
                                <tbody id="tbody-detectados"></tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cerrar</button>
                    <button type="button" class="btn btn-primary" id="btn-insertar-detectados" disabled>
                        Insertar seleccionados
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modalComparativoSql" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Comparativo SQL</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-info py-2 mb-3">
                        <div><strong>Consulta:</strong> <span id="texto-periodo-comparativo-sql">-</span></div>
                        <div class="small mt-1"><strong>Totales:</strong> BET <span id="total-bet-sql">-</span> | NET <span id="total-net-sql">-</span> | Catálogo <span id="total-catalogo-sql">-</span> | No en catálogo <span id="total-no-catalogo-sql">-</span></div>
                    </div>
                    <div id="comparativo-sql-loader" class="text-center py-4 d-none">
                        <div class="spinner-border text-primary" role="status"></div>
                        <div class="mt-2 text-muted">Ejecutando consulta...</div>
                    </div>
                    <div id="comparativo-sql-vacio" class="alert alert-warning d-none mb-0">
                        No hay datos para mostrar.
                    </div>
                    <div id="comparativo-sql-contenido" class="d-none">
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped align-middle mb-0" id="tabla-comparativo-sql">
                                <thead>
                                    <tr>
                                        <th>no_en_catalogo</th>
                                        <th>descripcion</th>
                                        <th>tipo</th>
                                        <th style="width: 120px;">accion</th>
                                    </tr>
                                </thead>
                                <tbody></tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-success" id="btn-insertar-masivo-comparativo" disabled>
                        Insertar todos
                    </button>
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cerrar</button>
                </div>
            </div>
        </div>
    </div>

    <form method="POST" id="form-eliminar-juego" class="d-none">
        @csrf
        @method('DELETE')
    </form>
@endsection

@section('script')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const modalEditar = new bootstrap.Modal(document.getElementById('modalEditarJuego'));
            const modalDetectar = new bootstrap.Modal(document.getElementById('modalDetectarNuevos'));
            const modalComparativoSql = new bootstrap.Modal(document.getElementById('modalComparativoSql'));
            const formEditar = document.getElementById('form-editar-juego');
            const formEliminar = document.getElementById('form-eliminar-juego');
            const inputEditProductoId = document.getElementById('edit-producto-id');
            const inputEditTipo = document.getElementById('edit-tipo');
            const inputEditDescripcion = document.getElementById('edit-descripcion');
            const btnDetectarNuevos = document.getElementById('btn-detectar-nuevos');
            const btnVerComparativoSql = document.getElementById('btn-ver-comparativo-sql');
            const inputDetectarAnio = document.getElementById('detectar-anio');
            const selectDetectarMes = document.getElementById('detectar-mes');
            const checkDetectarTodo = document.getElementById('detectar-todo');
            const btnInsertarDetectados = document.getElementById('btn-insertar-detectados');
            const checkTodosDetectados = document.getElementById('check-todos-detectados');
            const btnToggleNoCatalogo = document.getElementById('btn-toggle-no-catalogo');
            const tbodyDetectados = document.getElementById('tbody-detectados');
            const detectarLoader = document.getElementById('detectar-loader');
            const detectarVacio = document.getElementById('detectar-vacio');
            const detectarContenido = document.getElementById('detectar-contenido');
            const textoPeriodoDeteccion = document.getElementById('texto-periodo-deteccion');
            const textoDebugDeteccion = document.getElementById('texto-debug-deteccion');
            const thProductoId = document.getElementById('th-producto-id');
            const textoPeriodoComparativoSql = document.getElementById('texto-periodo-comparativo-sql');
            const totalBetSql = document.getElementById('total-bet-sql');
            const totalNetSql = document.getElementById('total-net-sql');
            const totalCatalogoSql = document.getElementById('total-catalogo-sql');
            const totalNoCatalogoSql = document.getElementById('total-no-catalogo-sql');
            const comparativoSqlLoader = document.getElementById('comparativo-sql-loader');
            const comparativoSqlVacio = document.getElementById('comparativo-sql-vacio');
            const comparativoSqlContenido = document.getElementById('comparativo-sql-contenido');
            const btnInsertarMasivoComparativo = document.getElementById('btn-insertar-masivo-comparativo');
            const csrfToken = '{{ csrf_token() }}';
            let tiposCatalogo = [];
            let vistaSoloNoCatalogo = false;
            let dataTableComparativoSql = null;
            let comparativoSqlRows = [];

            function escHtml(str) {
                return String(str)
                    .replace(/&/g, '&amp;').replace(/</g, '&lt;')
                    .replace(/>/g, '&gt;').replace(/"/g, '&quot;')
                    .replace(/'/g, '&#39;');
            }

            function renderBotonInsertarComparativo(row) {
                const productoId = escHtml(row?.no_en_catalogo || '');
                const descripcion = escHtml(row?.descripcion || '');
                const tipo = escHtml(row?.tipo || 'pendiente');

                if (!productoId) {
                    return '';
                }

                return `
                    <button
                        type="button"
                        class="btn btn-success btn-sm btn-insertar-fila-catalogo"
                        data-producto-id="${productoId}"
                        data-descripcion="${descripcion}"
                        data-tipo="${tipo}"
                    >
                        Insertar
                    </button>
                `;
            }

            async function insertarFilaDesdeComparativo(button) {
                const productoId = String(button?.getAttribute('data-producto-id') || '').trim();
                const descripcion = String(button?.getAttribute('data-descripcion') || '').trim();
                const tipo = String(button?.getAttribute('data-tipo') || '').trim() || 'pendiente';

                if (!productoId) {
                    Swal.fire('Error', 'La fila no tiene un producto_id válido.', 'error');
                    return;
                }

                try {
                    button.disabled = true;
                    button.textContent = 'Guardando...';

                    const response = await fetch("{{ route('mantenimiento.catalogo-juegos.insertar-detectados') }}", {
                        method: 'POST',
                        headers: {
                            'Accept': 'application/json',
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': csrfToken,
                            'X-Requested-With': 'XMLHttpRequest',
                        },
                        body: JSON.stringify({
                            productos: [{
                                producto_id: productoId,
                                tipo: tipo,
                                descripcion: descripcion || ('Producto ' + productoId),
                            }],
                        }),
                    });

                    const payload = await response.json();

                    if (!response.ok) {
                        throw new Error(payload?.message || 'No se pudo insertar la fila.');
                    }

                    const fila = button.closest('tr');
                    if (fila) {
                        fila.classList.add('table-success');
                    }

                    button.classList.remove('btn-success');
                    button.classList.add('btn-secondary');
                    button.textContent = 'Insertado';

                    const totalActual = Number(totalNoCatalogoSql?.textContent || 0);
                    if (totalNoCatalogoSql && Number.isFinite(totalActual) && totalActual > 0) {
                        totalNoCatalogoSql.textContent = String(totalActual - 1);
                    }
                } catch (error) {
                    button.disabled = false;
                    button.textContent = 'Insertar';
                    Swal.fire('Error', error.message || 'No se pudo insertar la fila.', 'error');
                }
            }

            async function insertarMasivoDesdeComparativo() {
                const productos = comparativoSqlRows
                    .map(function (row) {
                        const productoId = String(row?.no_en_catalogo || '').trim();
                        if (!productoId) {
                            return null;
                        }

                        const descripcion = String(row?.descripcion || '').trim();
                        const tipo = String(row?.tipo || '').trim() || 'pendiente';

                        return {
                            producto_id: productoId,
                            tipo: tipo,
                            descripcion: descripcion || ('Producto ' + productoId),
                        };
                    })
                    .filter(Boolean);

                if (!productos.length) {
                    Swal.fire('Sin datos', 'No hay filas cargadas para insertar.', 'warning');
                    return;
                }

                const confirmacion = await Swal.fire({
                    title: '¿Insertar todos?',
                    text: `Se intentarán insertar ${productos.length} registros al catálogo.`,
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonText: 'Sí, insertar',
                    cancelButtonText: 'Cancelar',
                });

                if (!confirmacion.isConfirmed) {
                    return;
                }

                try {
                    if (btnInsertarMasivoComparativo) {
                        btnInsertarMasivoComparativo.disabled = true;
                        btnInsertarMasivoComparativo.textContent = 'Insertando...';
                    }

                    const response = await fetch("{{ route('mantenimiento.catalogo-juegos.insertar-detectados') }}", {
                        method: 'POST',
                        headers: {
                            'Accept': 'application/json',
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': csrfToken,
                            'X-Requested-With': 'XMLHttpRequest',
                        },
                        body: JSON.stringify({
                            productos: productos,
                        }),
                    });

                    const payload = await response.json();

                    if (!response.ok) {
                        throw new Error(payload?.message || 'No se pudo ejecutar el insertado masivo.');
                    }

                    Swal.fire(
                        'Proceso completado',
                        `Insertados: ${payload?.insertados || 0} | Omitidos: ${payload?.omitidos || 0}`,
                        'success'
                    ).then(function () {
                        window.location.reload();
                    });
                } catch (error) {
                    if (btnInsertarMasivoComparativo) {
                        btnInsertarMasivoComparativo.disabled = false;
                        btnInsertarMasivoComparativo.textContent = 'Insertar todos';
                    }
                    Swal.fire('Error', error.message || 'No se pudo ejecutar el insertado masivo.', 'error');
                }
            }

            if (checkDetectarTodo) {
                checkDetectarTodo.checked = false;
            }

            function obtenerChecksDetectados() {
                return Array.from(document.querySelectorAll('.check-detectado'));
            }

            function actualizarEstadoInsertar() {
                const seleccionados = obtenerChecksDetectados().filter(function (check) {
                    return check.checked;
                });
                btnInsertarDetectados.disabled = seleccionados.length === 0;
            }

            function limpiarDeteccionVisual() {
                tbodyDetectados.innerHTML = '';
                detectarLoader.classList.add('d-none');
                detectarVacio.classList.add('d-none');
                detectarContenido.classList.add('d-none');
                btnInsertarDetectados.disabled = true;
                checkTodosDetectados.checked = true;
                vistaSoloNoCatalogo = false;
                aplicarVistaNoCatalogo(false);
            }

            function aplicarVistaNoCatalogo(solo) {
                const thCompletas = document.querySelectorAll('.th-completa');
                const tdCompletas = document.querySelectorAll('.td-completa');
                const thSeleccion = document.querySelector('.th-seleccion');
                const tdSeleccion = document.querySelectorAll('.td-seleccion');

                thCompletas.forEach(function (el) {
                    el.classList.toggle('d-none', solo);
                });
                tdCompletas.forEach(function (el) {
                    el.classList.toggle('d-none', solo);
                });

                if (thSeleccion) {
                    thSeleccion.classList.toggle('d-none', solo);
                }
                tdSeleccion.forEach(function (el) {
                    el.classList.toggle('d-none', solo);
                });

                const bloqueSeleccionarTodos = checkTodosDetectados?.closest('.form-check');
                if (bloqueSeleccionarTodos) {
                    bloqueSeleccionarTodos.classList.toggle('d-none', solo);
                }

                if (thProductoId) {
                    thProductoId.textContent = solo ? 'No en catálogo' : 'Producto ID';
                }

                if (btnToggleNoCatalogo) {
                    btnToggleNoCatalogo.textContent = solo
                        ? 'Ver vista completa'
                        : 'Ver solo "No en catálogo"';
                }

                if (btnInsertarDetectados) {
                    if (solo) {
                        btnInsertarDetectados.disabled = true;
                    } else {
                        actualizarEstadoInsertar();
                    }
                }
            }

            async function detectarNuevosProductos() {
                limpiarDeteccionVisual();
                detectarLoader.classList.remove('d-none');
                modalDetectar.show();

                try {
                    const consultarTodo = !!checkDetectarTodo?.checked;
                    const anio = Number(inputDetectarAnio?.value || 0);
                    const mes = Number(selectDetectarMes?.value || 0);

                    if (!consultarTodo) {
                        if (!Number.isInteger(anio) || anio < 2000 || anio > 2100) {
                            throw new Error('El año debe estar entre 2000 y 2100.');
                        }
                        if (!Number.isInteger(mes) || mes < 1 || mes > 12) {
                            throw new Error('El mes seleccionado no es válido.');
                        }
                    }

                    const params = new URLSearchParams();
                    params.set('todo', consultarTodo ? '1' : '0');
                    if (!consultarTodo) {
                        params.set('anio', String(anio));
                        params.set('mes', String(mes));
                    }

                    const response = await fetch("{{ route('mantenimiento.catalogo-juegos.detectar-nuevos') }}" + '?' + params.toString(), {
                        headers: {
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest',
                        }
                    });

                    const payload = await response.json();

                    if (!response.ok) {
                        throw new Error(payload?.message || 'Error en el servidor al detectar productos.');
                    }

                    const data = Array.isArray(payload?.data) ? payload.data : [];
                    tiposCatalogo = Array.isArray(payload?.tipos_catalogo) ? payload.tipos_catalogo : [];
                    if (textoPeriodoDeteccion) {
                        textoPeriodoDeteccion.textContent = String(payload?.periodo_texto || '-');
                    }
                    if (textoDebugDeteccion) {
                        const dbg = payload?.debug || {};
                        textoDebugDeteccion.textContent = `todo=${String(dbg?.todo ?? '-')}, anio=${String(dbg?.anio ?? '-')}, mes=${String(dbg?.mes ?? '-')}, nuevos=${String(dbg?.nuevos_count ?? '-')}, max_net=${String(dbg?.max_fecha_net ?? '-')}, max_bet=${String(dbg?.max_fecha_bet ?? '-')}`;
                    }

                    detectarLoader.classList.add('d-none');

                    if (!data.length) {
                        const sugerenciaTexto = String(payload?.sugerencia_periodo_texto || '').trim();
                        const sugerenciaAnio = Number(payload?.sugerencia_anio || 0);
                        const sugerenciaMes = Number(payload?.sugerencia_mes || 0);
                        const puedeSugerir = !consultarTodo
                            && sugerenciaTexto !== ''
                            && Number.isInteger(sugerenciaAnio)
                            && Number.isInteger(sugerenciaMes)
                            && sugerenciaAnio > 0
                            && sugerenciaMes >= 1
                            && sugerenciaMes <= 12
                            && (sugerenciaAnio !== anio || sugerenciaMes !== mes);

                        if (puedeSugerir) {
                            detectarVacio.innerHTML = `
                                No se encontraron productos nuevos en <strong>${String(payload?.periodo_texto || '-')}</strong>.<br>
                                Último período con data: <strong>${sugerenciaTexto}</strong>.
                                <button type="button" class="btn btn-sm btn-outline-primary ms-2" id="btn-usar-periodo-sugerido">
                                    Usar ${sugerenciaTexto}
                                </button>
                            `;
                        } else {
                            detectarVacio.textContent = 'No se encontraron productos nuevos para la consulta seleccionada.';
                        }
                        detectarVacio.classList.remove('d-none');

                        const btnUsarPeriodoSugerido = document.getElementById('btn-usar-periodo-sugerido');
                        if (btnUsarPeriodoSugerido) {
                            btnUsarPeriodoSugerido.addEventListener('click', function () {
                                if (inputDetectarAnio) inputDetectarAnio.value = String(sugerenciaAnio);
                                if (selectDetectarMes) selectDetectarMes.value = String(sugerenciaMes);
                                detectarNuevosProductos();
                            });
                        }
                        return;
                    }

                    detectarContenido.classList.remove('d-none');

                    data.forEach(function (item) {
                        const tr = document.createElement('tr');
                        const productoId = String(item?.producto_id || '').trim();
                        const descripcion = String(item?.descripcion || '').trim();
                        const origenes = String(item?.origenes_texto || '-');
                        const tipoSugerido = String(item?.tipo_sugerido || 'pendiente').trim();
                        const tiposDetectados = Array.isArray(item?.tipos_detectados) ? item.tipos_detectados : [];
                        const opcionesTipo = Array.from(new Set([
                            tipoSugerido,
                            ...tiposDetectados,
                            ...tiposCatalogo,
                            'pendiente',
                        ].filter(Boolean)));
                        const opcionesTipoHtml = opcionesTipo.map(function (tipo) {
                            const selected = tipo === tipoSugerido ? 'selected' : '';
                            return `<option value="${escHtml(tipo)}" ${selected}>${escHtml(tipo)}</option>`;
                        }).join('');

                        tr.innerHTML = `
                            <td class="td-seleccion">
                                <input type="checkbox" class="form-check-input check-detectado" value="${escHtml(productoId)}" checked>
                            </td>
                            <td>${escHtml(productoId)}</td>
                            <td class="texto-descripcion-detectada td-completa">${escHtml(descripcion) || '-'}</td>
                            <td class="td-completa">
                                <select class="form-select form-select-sm select-tipo-detectado">
                                    ${opcionesTipoHtml}
                                </select>
                            </td>
                            <td class="td-completa">${escHtml(origenes)}</td>
                        `;
                        tbodyDetectados.appendChild(tr);
                    });

                    obtenerChecksDetectados().forEach(function (check) {
                        check.addEventListener('change', actualizarEstadoInsertar);
                    });

                    actualizarEstadoInsertar();
                    aplicarVistaNoCatalogo(vistaSoloNoCatalogo);
                } catch (error) {
                    detectarLoader.classList.add('d-none');
                    Swal.fire('Error', error.message || 'No se pudo ejecutar la detección.', 'error');
                }
            }

            async function cargarComparativoSql() {
                if (!comparativoSqlLoader || !comparativoSqlVacio || !comparativoSqlContenido) {
                    return;
                }

                comparativoSqlLoader.classList.remove('d-none');
                comparativoSqlVacio.classList.add('d-none');
                comparativoSqlContenido.classList.add('d-none');
                modalComparativoSql.show();

                try {
                    const consultarTodo = !!checkDetectarTodo?.checked;
                    const anio = Number(inputDetectarAnio?.value || 0);
                    const mes = Number(selectDetectarMes?.value || 0);

                    if (!consultarTodo) {
                        if (!Number.isInteger(anio) || anio < 2000 || anio > 2100) {
                            throw new Error('Debes indicar un año válido para la consulta SQL.');
                        }
                        if (!Number.isInteger(mes) || mes < 1 || mes > 12) {
                            throw new Error('Debes seleccionar un mes válido para la consulta SQL.');
                        }
                    }

                    const params = new URLSearchParams();
                    params.set('todo', consultarTodo ? '1' : '0');
                    if (!consultarTodo) {
                        params.set('anio', String(anio));
                        params.set('mes', String(mes));
                    }

                    const response = await fetch("{{ route('mantenimiento.catalogo-juegos.comparativo-sql') }}?" + params.toString(), {
                        headers: {
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest',
                        }
                    });

                    const payload = await response.json();
                    if (!response.ok) {
                        throw new Error(payload?.message || 'No se pudo ejecutar el comparativo SQL.');
                    }

                    const rows = Array.isArray(payload?.data) ? payload.data : [];
                    comparativoSqlRows = rows;
                    if (textoPeriodoComparativoSql) {
                        textoPeriodoComparativoSql.textContent = String(payload?.periodo_texto || '-');
                    }
                    if (totalBetSql) totalBetSql.textContent = String(payload?.totales?.bet ?? '-');
                    if (totalNetSql) totalNetSql.textContent = String(payload?.totales?.net ?? '-');
                    if (totalCatalogoSql) totalCatalogoSql.textContent = String(payload?.totales?.catalogo ?? '-');
                    if (totalNoCatalogoSql) totalNoCatalogoSql.textContent = String(payload?.totales?.no_en_catalogo ?? '-');
                    if (btnInsertarMasivoComparativo) {
                        btnInsertarMasivoComparativo.disabled = rows.length === 0;
                        btnInsertarMasivoComparativo.textContent = 'Insertar todos';
                    }

                    comparativoSqlLoader.classList.add('d-none');

                    if (!rows.length) {
                        comparativoSqlVacio.classList.remove('d-none');
                        const tbody = document.querySelector('#tabla-comparativo-sql tbody');
                        if (tbody) {
                            tbody.innerHTML = '';
                        }
                        if (dataTableComparativoSql) {
                            dataTableComparativoSql.clear().draw();
                        }
                        return;
                    }

                    comparativoSqlContenido.classList.remove('d-none');

                    if (window.$ && $.fn.DataTable && $('#tabla-comparativo-sql').length) {
                        if (dataTableComparativoSql) {
                            dataTableComparativoSql.clear();
                            dataTableComparativoSql.rows.add(rows).draw();
                        } else {
                            dataTableComparativoSql = $('#tabla-comparativo-sql').DataTable({
                                responsive: true,
                                pageLength: 10,
                                lengthMenu: [[10, 25, 50, 100], [10, 25, 50, 100]],
                                order: [[0, 'asc']],
                                data: rows,
                                columns: [
                                    {
                                        data: 'no_en_catalogo',
                                        defaultContent: '',
                                        render: function (data) {
                                            const valor = escHtml(data || '');
                                            return valor ? `<span class="badge bg-danger-subtle text-danger">${valor}</span>` : '';
                                        }
                                    },
                                    {
                                        data: 'descripcion',
                                        defaultContent: '',
                                        render: function (data) { return escHtml(data || ''); }
                                    },
                                    {
                                        data: 'tipo',
                                        defaultContent: '',
                                        render: function (data) { return escHtml(data || ''); }
                                    },
                                    {
                                        data: null,
                                        orderable: false,
                                        searchable: false,
                                        render: function (_data, _type, row) {
                                            return renderBotonInsertarComparativo(row);
                                        }
                                    },
                                ],
                                language: {
                                    search: 'Buscar:',
                                    lengthMenu: 'Mostrar _MENU_ registros',
                                    info: 'Mostrando _START_ a _END_ de _TOTAL_ registros',
                                    infoEmpty: 'Mostrando 0 a 0 de 0 registros',
                                    emptyTable: 'No hay datos disponibles',
                                    paginate: {
                                        first: 'Primero',
                                        last: 'Último',
                                        next: 'Siguiente',
                                        previous: 'Anterior'
                                    }
                                }
                            });
                        }
                    } else {
                        const tbody = document.querySelector('#tabla-comparativo-sql tbody');
                        if (tbody) {
                            tbody.innerHTML = rows.map(function (row) {
                                return `<tr>
                                    <td>${row?.no_en_catalogo ? `<span class="badge bg-danger-subtle text-danger">${escHtml(row.no_en_catalogo)}</span>` : ''}</td>
                                    <td>${escHtml(row?.descripcion || '')}</td>
                                    <td>${escHtml(row?.tipo || '')}</td>
                                    <td>${renderBotonInsertarComparativo(row)}</td>
                                </tr>`;
                            }).join('');
                        }
                    }
                } catch (error) {
                    comparativoSqlLoader.classList.add('d-none');
                    Swal.fire('Error', error.message || 'No se pudo ejecutar la consulta SQL.', 'error');
                }
            }

            document.querySelectorAll('.btn-editar-juego').forEach(function (btn) {
                btn.addEventListener('click', function () {
                    const id = this.getAttribute('data-id');
                    formEditar.action = "{{ url('/mantenimiento/catalogo-juegos') }}/" + id;
                    inputEditProductoId.value = this.getAttribute('data-producto-id') || '';
                    inputEditTipo.value = this.getAttribute('data-tipo') || '';
                    inputEditDescripcion.value = this.getAttribute('data-descripcion') || '';
                    modalEditar.show();
                });
            });

            document.querySelectorAll('.btn-eliminar-juego').forEach(function (btn) {
                btn.addEventListener('click', function () {
                    const id = this.getAttribute('data-id');
                    const productoId = this.getAttribute('data-producto-id') || '';

                    Swal.fire({
                        title: '¿Eliminar juego?',
                        text: 'Producto ID: ' + productoId,
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonText: 'Sí, eliminar',
                        cancelButtonText: 'Cancelar',
                    }).then(function (result) {
                        if (!result.isConfirmed) {
                            return;
                        }

                        formEliminar.action = "{{ url('/mantenimiento/catalogo-juegos') }}/" + id;
                        formEliminar.submit();
                    });
                });
            });

            if (btnDetectarNuevos) {
                btnDetectarNuevos.addEventListener('click', detectarNuevosProductos);
            }

            if (btnVerComparativoSql) {
                btnVerComparativoSql.addEventListener('click', cargarComparativoSql);
            }

            if (btnInsertarMasivoComparativo) {
                btnInsertarMasivoComparativo.addEventListener('click', insertarMasivoDesdeComparativo);
            }

            document.addEventListener('click', function (event) {
                const button = event.target.closest('.btn-insertar-fila-catalogo');
                if (!button) {
                    return;
                }

                insertarFilaDesdeComparativo(button);
            });

            if (checkDetectarTodo) {
                checkDetectarTodo.addEventListener('change', function () {
                    const disabled = this.checked;
                    if (inputDetectarAnio) inputDetectarAnio.disabled = disabled;
                    if (selectDetectarMes) selectDetectarMes.disabled = disabled;
                });
            }

            if (checkTodosDetectados) {
                checkTodosDetectados.addEventListener('change', function () {
                    const checked = this.checked;
                    obtenerChecksDetectados().forEach(function (check) {
                        check.checked = checked;
                    });
                    actualizarEstadoInsertar();
                });
            }

            if (btnToggleNoCatalogo) {
                btnToggleNoCatalogo.addEventListener('click', function () {
                    vistaSoloNoCatalogo = !vistaSoloNoCatalogo;
                    aplicarVistaNoCatalogo(vistaSoloNoCatalogo);
                });
            }

            if (btnInsertarDetectados) {
                btnInsertarDetectados.addEventListener('click', async function () {
                    const seleccionados = obtenerChecksDetectados()
                        .filter(function (check) { return check.checked; })
                        .map(function (check) {
                            const fila = check.closest('tr');
                            const selectTipo = fila ? fila.querySelector('.select-tipo-detectado') : null;
                            const tipo = String(selectTipo?.value || '').trim();
                            const descripcionRaw = String(fila?.querySelector('.texto-descripcion-detectada')?.textContent || '').trim();
                            const descripcion = descripcionRaw === '-' ? '' : descripcionRaw;

                            return {
                                producto_id: String(check.value || '').trim(),
                                tipo: tipo || 'pendiente',
                                descripcion: descripcion || ('Producto ' + String(check.value || '').trim()),
                            };
                        });

                    if (!seleccionados.length) {
                        Swal.fire('Sin selección', 'Debes seleccionar al menos un producto para insertar.', 'warning');
                        return;
                    }

                    try {
                        btnInsertarDetectados.disabled = true;
                        btnInsertarDetectados.textContent = 'Insertando...';

                        const response = await fetch("{{ route('mantenimiento.catalogo-juegos.insertar-detectados') }}", {
                            method: 'POST',
                            headers: {
                                'Accept': 'application/json',
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': csrfToken,
                                'X-Requested-With': 'XMLHttpRequest',
                            },
                            body: JSON.stringify({
                                productos: seleccionados,
                            }),
                        });

                        const payload = await response.json();

                        if (!response.ok) {
                            throw new Error(payload?.message || 'No se pudo insertar.');
                        }

                        Swal.fire(
                            'Proceso completado',
                            `Insertados: ${payload.insertados || 0} | Omitidos: ${payload.omitidos || 0}`,
                            'success'
                        ).then(function () {
                            window.location.reload();
                        });
                    } catch (error) {
                        btnInsertarDetectados.textContent = 'Insertar seleccionados';
                        actualizarEstadoInsertar();
                        Swal.fire('Error', error.message || 'No se pudo insertar.', 'error');
                    }
                });
            }

            if (window.$ && $.fn.DataTable && $('#tabla-catalogo-juegos').length) {
                $('#tabla-catalogo-juegos').DataTable({
                    responsive: true,
                    pageLength: 10,
                    lengthMenu: [[10, 25, 50, 100], [10, 25, 50, 100]],
                    order: [[1, 'asc']],
                    language: {
                        search: 'Buscar:',
                        lengthMenu: 'Mostrar _MENU_ registros',
                        info: 'Mostrando _START_ a _END_ de _TOTAL_ registros',
                        infoEmpty: 'Mostrando 0 a 0 de 0 registros',
                        emptyTable: 'No hay datos disponibles',
                        paginate: {
                            first: 'Primero',
                            last: 'Último',
                            next: 'Siguiente',
                            previous: 'Anterior'
                        }
                    }
                });
            }
        });
    </script>
@endsection

