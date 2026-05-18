@extends('app')

@section('content')
    <div class="main-content">
        <div class="page-content">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-12">
                        <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                            <h4 class="mb-sm-0">Ruta</h4>
                            <div class="page-title-right">
                                <ol class="breadcrumb m-0">
                                    <li class="breadcrumb-item"><a href="{{ route('operaciones.index') }}">Operaciones</a></li>
                                    <li class="breadcrumb-item active">Ruta</li>
                                </ol>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-lg-12">
                        <div class="card">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h5 class="card-title mb-0">Listado</h5>
                                <a href="{{ route('ruta.create') }}" class="btn btn-primary btn-sm">
                                    <i class="ri-add-line align-bottom me-1"></i>Nuevo
                                </a>
                            </div>
                            <div class="card-body">
                                @if(session('success'))
                                    <div class="alert alert-success">{{ session('success') }}</div>
                                @endif

                                @if(session('error'))
                                    <div class="alert alert-danger">{{ session('error') }}</div>
                                @endif

                                <div class="row mb-3">
                                    <div class="col-12 col-md-5 col-lg-4">
                                        <label for="buscarCoordinador" class="form-label">Buscar ruta</label>
                                        <input type="text" id="buscarCoordinador" class="form-control" placeholder="Escribe nombre de ruta u operador...">
                                    </div>
                                </div>

                                <div class="table-responsive">
                                    <table class="table table-bordered table-striped align-middle mb-0" id="tablaCoordinadorOperador">
                                        <thead class="table-light">
                                            <tr>
                                                <th class="text-center" style="width:80px;">ID</th>
                                                <th>Nombre de Ruta</th>
                                                <th>Empresa</th>
                                                <th>Operador Asignado</th>
                                                <th class="text-center">Agencias Asignadas</th>
                                                <th class="text-center" style="width:140px;">Acciones</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse($registros as $item)
                                                <tr>
                                                    <td class="text-center">{{ $item->id }}</td>
                                                    <td>{{ $item->nombre_ruta }}</td>
                                                    <td>{{ $item->empresa ?: '-' }}</td>
                                                    <td>{{ trim((($item->operadorAsignado->nombre ?? '') . ' ' . ($item->operadorAsignado->apellido ?? ''))) ?: '-' }}</td>
                                                    <td class="text-center">
                                                        <button
                                                            type="button"
                                                            class="btn btn-info btn-sm btn-ver-agencias"
                                                            title="Ver agencias asignadas"
                                                            data-nombre="{{ $item->nombre_ruta }}"
                                                            data-agencias='@json($item->agencias->map(fn($agencia) => ['terminal' => $agencia->terminal, 'nombre_agencia' => $agencia->nombre_agencia, 'agencia' => $agencia->agencia])->values())'>
                                                            {{ $item->agencias_count }}
                                                        </button>
                                                    </td>
                                                    <td class="text-center">
                                                        <div class="d-flex justify-content-center gap-1">
                                                            <button
                                                                type="button"
                                                                class="btn btn-info btn-sm btn-asignar-agencias"
                                                                title="Asignar agencias"
                                                                data-id="{{ $item->id }}"
                                                                data-coordinador-id="{{ $item->id }}"
                                                                data-nombre="{{ $item->nombre_ruta }}"
                                                                data-asignadas='@json($item->agencias->pluck('id')->values())'>
                                                                <i class="ri-building-line"></i>
                                                            </button>
                                                            <a href="{{ route('ruta.edit', $item->id) }}" class="btn btn-success btn-sm" title="Editar">
                                                                <i class="ri-pencil-line"></i>
                                                            </a>
                                                            <form action="{{ route('ruta.destroy', $item->id) }}" method="POST" onsubmit="return confirm('Â¿EstÃ¡ seguro de eliminar este registro?')">
                                                                @csrf
                                                                @method('DELETE')
                                                                <button type="submit" class="btn btn-danger btn-sm" title="Eliminar">
                                                                    <i class="ri-delete-bin-line"></i>
                                                                </button>
                                                            </form>
                                                        </div>
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="6" class="text-center text-muted">No hay registros disponibles.</td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>

                                <div class="mt-3">
                                    {{ $registros->links() }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="asignarAgenciasModal" tabindex="-1" aria-labelledby="asignarAgenciasModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <form id="formAsignarAgencias" method="POST">
                    @csrf
                    <input type="hidden" name="confirmar_reasignacion" id="confirmarReasignacion" value="0">
                    <div class="modal-header">
                        <h5 class="modal-title" id="asignarAgenciasModalLabel">Asignar agencias</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <p class="mb-2">
                            Asignando agencias a: <strong id="nombreAsignacion">-</strong>
                        </p>

                        <div class="mb-3">
                            <label for="buscarTerminalAgencia" class="form-label mb-1">Buscar por terminal</label>
                            <input type="text" id="buscarTerminalAgencia" class="form-control" placeholder="Escribe una terminal para filtrar...">
                        </div>

                        <div class="mb-3">
                            <label for="terminalesMasivos" class="form-label mb-1">AsignaciÃ³n masiva por terminal</label>
                            <textarea id="terminalesMasivos" class="form-control" rows="4" placeholder="Pega aquÃ­ los cÃ³digos de terminal desde Excel o TXT (uno por lÃ­nea, columna o separados por coma)"></textarea>
                            <div class="d-flex flex-wrap gap-2 mt-2">
                                <button type="button" class="btn btn-outline-primary btn-sm" id="btnAplicarTerminalesMasivos">Marcar terminales pegadas</button>
                                <button type="button" class="btn btn-outline-secondary btn-sm" id="btnLimpiarTerminalesMasivos">Limpiar pegado</button>
                            </div>
                            <small class="text-muted d-block mt-1" id="resumenTerminalesMasivos"></small>
                            <div class="mt-2" id="detalleTerminalesNoCoinciden"></div>
                        </div>

                        <div class="border rounded p-3" style="max-height: 380px; overflow-y: auto;">
                            <div class="row g-2" id="listaAgenciasAsignacion">
                                @forelse($agencias as $agencia)
                                    <div class="col-12 col-md-6 item-agencia" data-agencia-id="{{ $agencia->id }}" data-terminal="{{ strtolower($agencia->terminal ?? '') }}" data-texto="{{ strtolower(($agencia->terminal ?? '') . ' ' . ($agencia->nombre_agencia ?? '')) }}">
                                        <div class="form-check">
                                            <input class="form-check-input checkbox-agencia" type="checkbox" name="agencias[]" value="{{ $agencia->id }}" id="agencia_{{ $agencia->id }}">
                                            <label class="form-check-label" for="agencia_{{ $agencia->id }}">
                                                {{ $agencia->terminal ?: '-' }} - {{ $agencia->nombre_agencia ?: 'Sin nombre' }}
                                            </label>
                                        </div>
                                    </div>
                                @empty
                                    <div class="col-12 text-muted">
                                        No hay agencias disponibles para asignar.
                                    </div>
                                @endforelse
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary">Guardar asignaciÃ³n</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="verAgenciasAsignadasModal" tabindex="-1" aria-labelledby="verAgenciasAsignadasModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="verAgenciasAsignadasModalLabel">Agencias asignadas (<span id="contadorVerAgencias">0</span>)</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p class="mb-2">Usuario: <strong id="nombreVerAgencias">-</strong></p>
                    <div id="contenidoVerAgencias" class="border rounded p-2" style="max-height: 320px; overflow-y: auto;"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('script')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const asignacionesAgencia = @json($asignacionesAgencia ?? []);
        const modalElement = document.getElementById('asignarAgenciasModal');
        const modal = new bootstrap.Modal(modalElement);
        const modalVerAgenciasElement = document.getElementById('verAgenciasAsignadasModal');
        const modalVerAgencias = new bootstrap.Modal(modalVerAgenciasElement);
        const form = document.getElementById('formAsignarAgencias');
        const nombreAsignacion = document.getElementById('nombreAsignacion');
        const nombreVerAgencias = document.getElementById('nombreVerAgencias');
        const contadorVerAgencias = document.getElementById('contadorVerAgencias');
        const contenidoVerAgencias = document.getElementById('contenidoVerAgencias');
        const checkboxes = document.querySelectorAll('.checkbox-agencia');
        const buscarTerminalAgencia = document.getElementById('buscarTerminalAgencia');
        const itemsAgencia = document.querySelectorAll('.item-agencia');
        const buscarCoordinador = document.getElementById('buscarCoordinador');
        const filasTablaCoordinador = document.querySelectorAll('#tablaCoordinadorOperador tbody tr');
        const terminalesMasivos = document.getElementById('terminalesMasivos');
        const btnAplicarTerminalesMasivos = document.getElementById('btnAplicarTerminalesMasivos');
        const btnLimpiarTerminalesMasivos = document.getElementById('btnLimpiarTerminalesMasivos');
        const resumenTerminalesMasivos = document.getElementById('resumenTerminalesMasivos');
        const detalleTerminalesNoCoinciden = document.getElementById('detalleTerminalesNoCoinciden');
        const confirmarReasignacion = document.getElementById('confirmarReasignacion');

        function normalizarTerminal(valor) {
            return String(valor || '').trim().toLowerCase();
        }

        function extraerTerminalesPegadas(texto) {
            return Array.from(
                new Set(
                    String(texto || '')
                        .split(/[\s,;|]+/)
                        .map(normalizarTerminal)
                        .filter(Boolean)
                )
            );
        }

        function aplicarTerminalesMasivos() {
            const terminales = extraerTerminalesPegadas(terminalesMasivos?.value || '');

            if (!terminales.length) {
                if (resumenTerminalesMasivos) {
                    resumenTerminalesMasivos.textContent = 'No se detectaron terminales para procesar.';
                }
                return;
            }

            const mapaTerminales = new Set(terminales);
            const terminalesDisponibles = new Set(
                Array.from(itemsAgencia)
                    .map(function (item) { return normalizarTerminal(item.dataset.terminal || ''); })
                    .filter(Boolean)
            );
            let encontradas = 0;

            itemsAgencia.forEach(function (item) {
                const terminalItem = normalizarTerminal(item.dataset.terminal || '');
                const checkbox = item.querySelector('.checkbox-agencia');

                if (checkbox && terminalItem && mapaTerminales.has(terminalItem)) {
                    checkbox.checked = true;
                    encontradas++;
                }
            });

            if (resumenTerminalesMasivos) {
                resumenTerminalesMasivos.textContent = `Terminales procesadas: ${terminales.length}. Coincidencias marcadas: ${encontradas}.`;
            }

            const noCoinciden = terminales.filter(function (terminal) {
                return !terminalesDisponibles.has(terminal);
            });

            if (detalleTerminalesNoCoinciden) {
                if (!noCoinciden.length) {
                    detalleTerminalesNoCoinciden.innerHTML = '<small class="text-success">Detalle: todos los cÃ³digos coinciden con la tabla de agencias.</small>';
                } else {
                    const listado = noCoinciden
                        .map(function (terminal) {
                            return `<li>${escaparHtml(terminal)}</li>`;
                        })
                        .join('');

                    detalleTerminalesNoCoinciden.innerHTML = `
                        <details>
                            <summary class="text-danger" style="cursor:pointer;">Detalle: ${noCoinciden.length} terminal(es) no coinciden con la tabla de agencias</summary>
                            <div class="small text-muted mt-2" style="max-height: 130px; overflow-y: auto;">
                                <ul class="mb-0 ps-3">${listado}</ul>
                            </div>
                        </details>
                    `;
                }
            }
        }

        function limpiarTerminalesMasivos() {
            if (terminalesMasivos) {
                terminalesMasivos.value = '';
            }
            if (resumenTerminalesMasivos) {
                resumenTerminalesMasivos.textContent = '';
            }
            if (detalleTerminalesNoCoinciden) {
                detalleTerminalesNoCoinciden.innerHTML = '';
            }
        }

        function obtenerConflictosSeleccionados(coordinadorIdActual) {
            const conflictos = [];
            const asignadasIniciales = new Set(
                JSON.parse(form?.dataset?.asignadasIniciales || '[]').map(function (id) {
                    return Number(id);
                })
            );

            itemsAgencia.forEach(function (item) {
                const checkbox = item.querySelector('.checkbox-agencia');
                if (!checkbox || !checkbox.checked) {
                    return;
                }

                const agenciaId = Number(item.dataset.agenciaId || checkbox.value || 0);

                // Solo validamos agencias nuevas marcadas en esta asignacion.
                if (asignadasIniciales.has(agenciaId)) {
                    return;
                }

                const terminal = item.dataset.terminal || '-';
                const asignados = Array.isArray(asignacionesAgencia[String(agenciaId)])
                    ? asignacionesAgencia[String(agenciaId)]
                    : [];

                const asignadosOtros = asignados.filter(function (owner) {
                    return Number(owner.id) !== Number(coordinadorIdActual);
                });

                if (asignadosOtros.length) {
                    conflictos.push({
                        terminal: terminal || '-',
                        asignadosOtros: asignadosOtros,
                    });
                }
            });

            return conflictos;
        }

        function escaparHtml(texto) {
            return String(texto || '')
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/\"/g, '&quot;')
                .replace(/'/g, '&#039;');
        }

        function filtrarCoordinadorTabla() {
            const termino = (buscarCoordinador?.value || '').toLowerCase().trim();

            filasTablaCoordinador.forEach(function (fila) {
                const celdas = fila.querySelectorAll('td');
                if (!celdas.length || celdas.length < 3) {
                    return;
                }

                const nombreRuta = (celdas[1]?.textContent || '').toLowerCase();
                const empresa = (celdas[2]?.textContent || '').toLowerCase();
                const operador = (celdas[3]?.textContent || '').toLowerCase();
                const coincide = !termino || nombreRuta.includes(termino) || empresa.includes(termino) || operador.includes(termino);

                fila.style.display = coincide ? '' : 'none';
            });
        }

        function filtrarAgenciasModal() {
            const termino = (buscarTerminalAgencia?.value || '').toLowerCase().trim();

            itemsAgencia.forEach(function (item) {
                const texto = item.dataset.texto || '';
                item.style.display = texto.includes(termino) ? '' : 'none';
            });
        }

        document.querySelectorAll('.btn-asignar-agencias').forEach(function (button) {
            button.addEventListener('click', function () {
                const id = this.dataset.id;
                const nombre = this.dataset.nombre || '-';
                const asignadas = JSON.parse(this.dataset.asignadas || '[]');

                form.action = `/operaciones/ruta/${id}/asignar-agencias`;
                form.dataset.coordinadorId = String(this.dataset.coordinadorId || id || '0');
                form.dataset.asignadasIniciales = JSON.stringify(asignadas);
                nombreAsignacion.textContent = nombre;

                if (confirmarReasignacion) {
                    confirmarReasignacion.value = '0';
                }

                checkboxes.forEach(function (checkbox) {
                    checkbox.checked = asignadas.includes(Number(checkbox.value));
                });

                if (buscarTerminalAgencia) {
                    buscarTerminalAgencia.value = '';
                    filtrarAgenciasModal();
                }

                limpiarTerminalesMasivos();

                modal.show();
            });
        });

        if (form) {
            form.addEventListener('submit', function (event) {
                if (form.dataset.confirmadoReasignacion === '1') {
                    form.dataset.confirmadoReasignacion = '0';
                    return;
                }

                const coordinadorIdActual = Number(form.dataset.coordinadorId || 0);
                const conflictos = obtenerConflictosSeleccionados(coordinadorIdActual);

                if (!conflictos.length) {
                    if (confirmarReasignacion) {
                        confirmarReasignacion.value = '0';
                    }
                    return;
                }

                const detalle = conflictos
                    .slice(0, 8)
                    .map(function (conflicto) {
                        const duenos = conflicto.asignadosOtros
                            .map(function (owner) { return owner.nombre || 'Ruta'; })
                            .join(', ');
                        return {
                            terminal: conflicto.terminal,
                            duenos: duenos,
                        };
                    })
                    .map(function (item) {
                        return `<li class="mb-1"><strong>${escaparHtml(item.terminal)}</strong>: ${escaparHtml(item.duenos)}</li>`;
                    })
                    .join('');

                const excedente = conflictos.length > 8
                    ? `<p class="text-muted small mt-2 mb-0">... y ${conflictos.length - 8} mas.</p>`
                    : '';

                event.preventDefault();

                if (window.Swal && typeof window.Swal.fire === 'function') {
                    const htmlDetalle = `
                        <p class="mb-2">Estas agencias ya estan asignadas a otra ruta:</p>
                        <ul class="text-start ps-3 mb-0">${detalle}</ul>
                        ${excedente}
                    `;

                    window.Swal.fire({
                        icon: 'warning',
                        title: 'Reasignar agencias',
                        html: htmlDetalle,
                        showCancelButton: true,
                        confirmButtonText: 'Si, mover agencias',
                        cancelButtonText: 'Cancelar',
                        confirmButtonColor: '#0ab39c',
                        cancelButtonColor: '#f06548',
                        reverseButtons: true,
                    }).then(function (resultado) {
                        if (!resultado.isConfirmed) {
                            if (confirmarReasignacion) {
                                confirmarReasignacion.value = '0';
                            }
                            return;
                        }

                        if (confirmarReasignacion) {
                            confirmarReasignacion.value = '1';
                        }

                        form.dataset.confirmadoReasignacion = '1';
                        form.submit();
                    });
                    return;
                }

                // Si SweetAlert no esta disponible, no mostramos confirmacion nativa
                // para evitar el mensaje negro del navegador.
                if (confirmarReasignacion) {
                    confirmarReasignacion.value = '0';
                }
            });
        }

        if (buscarTerminalAgencia) {
            buscarTerminalAgencia.addEventListener('input', filtrarAgenciasModal);
        }

        if (buscarCoordinador) {
            buscarCoordinador.addEventListener('input', filtrarCoordinadorTabla);
        }

        if (btnAplicarTerminalesMasivos) {
            btnAplicarTerminalesMasivos.addEventListener('click', aplicarTerminalesMasivos);
        }

        if (btnLimpiarTerminalesMasivos) {
            btnLimpiarTerminalesMasivos.addEventListener('click', limpiarTerminalesMasivos);
        }

        document.querySelectorAll('.btn-ver-agencias').forEach(function (button) {
            button.addEventListener('click', function () {
                const nombre = this.dataset.nombre || '-';
                const agencias = JSON.parse(this.dataset.agencias || '[]');

                nombreVerAgencias.textContent = nombre;
                if (contadorVerAgencias) {
                    contadorVerAgencias.textContent = String(agencias.length || 0);
                }

                if (!agencias.length) {
                    contenidoVerAgencias.innerHTML = '<p class="text-muted mb-0">No tiene agencias asignadas.</p>';
                } else {
                    contenidoVerAgencias.innerHTML = agencias.map(function (agencia) {
                        const terminal = agencia.terminal || '-';
                        const nombreAgencia = agencia.nombre_agencia || agencia.agencia || 'Sin nombre';
                        return `<div class="py-1 border-bottom">${terminal} - ${nombreAgencia}</div>`;
                    }).join('');
                }

                modalVerAgencias.show();
            });
        });
    });
</script>
@endsection

