@extends('app')

@section('content')
    <div class="main-content">
        <div class="page-content">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-12">
                        <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                            <h4 class="mb-sm-0">Movimiento del Mayor</h4>
                            <div class="page-title-right">
                                <ol class="breadcrumb m-0">
                                    <li class="breadcrumb-item"><a href="{{ route('inicio.index') }}">Inicio</a></li>
                                    <li class="breadcrumb-item"><a href="{{ route('contabilidad.index') }}">Contabilidad</a></li>
                                    <li class="breadcrumb-item active">Movimiento del Mayor</li>
                                </ol>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-lg-12">
                        <div class="card">
                            <div class="card-header">
                                <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                                    <div>
                                        <h5 class="card-title mb-0">Movimiento del Mayor</h5>
                                        <small class="text-muted">Consulta local por rango o sincroniza desde el API contable.</small>
                                    </div>
                                    <div class="d-flex gap-2 flex-wrap">
                                        <button type="button" class="btn btn-primary" id="btnConsultarEntradasDiario">
                                            Consultar data local
                                        </button>
                                        <button type="button" class="btn btn-info" id="btnSincronizarEntradasDiario">
                                            Sincronizar API
                                        </button>
                                        <button type="button" class="btn btn-danger" id="btnEliminarEntradasDiario">
                                            Eliminar data
                                        </button>
                                    </div>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="row g-2 align-items-end mb-3">
                                    <div class="col-12 col-md-2">
                                        <label class="form-label">Empresa</label>
                                        <select id="entradaDiarioEmpresa" class="form-select form-select-sm">
                                            <option value="126" selected>126 - Business Support Hub</option>
                                            <option value="100">100 - Empresa 100</option>
                                        </select>
                                    </div>
                                    <div class="col-12 col-md-2">
                                        <label class="form-label">Fecha inicio</label>
                                        <input type="date" class="form-control form-control-sm" id="entradaDiarioFechaInicio" value="<?php echo date('Y-m-d'); ?>">
                                    </div>
                                    <div class="col-12 col-md-2">
                                        <label class="form-label">Fecha fin</label>
                                        <input type="date" class="form-control form-control-sm" id="entradaDiarioFechaFin" value="<?php echo date('Y-m-d'); ?>">
                                    </div>
                                    <div class="col-12 col-md-3">
                                        <label class="form-label">Cuenta</label>
                                        <input type="text" class="form-control form-control-sm" id="entradaDiarioCuenta" placeholder="Opcional">
                                    </div>
                                    <div class="col-12 col-md-3">
                                        <label class="form-label">Centro costo</label>
                                        <input type="text" class="form-control form-control-sm" id="entradaDiarioCentroCosto" placeholder="Opcional">
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label">Tipos contables</label>
                                        <div class="d-flex flex-wrap gap-3 pt-1" id="entradaDiarioTipos">
                                            <div class="form-check form-check-inline mb-0">
                                                <input class="form-check-input check-entrada-tipo" type="checkbox" value="Todos" id="entrada-tipo-todos" checked>
                                                <label class="form-check-label" for="entrada-tipo-todos">Todos</label>
                                            </div>
                                            <div class="form-check form-check-inline mb-0">
                                                <input class="form-check-input check-entrada-tipo" type="checkbox" value="Ingreso" id="entrada-tipo-ingreso">
                                                <label class="form-check-label" for="entrada-tipo-ingreso">Ingresos</label>
                                            </div>
                                            <div class="form-check form-check-inline mb-0">
                                                <input class="form-check-input check-entrada-tipo" type="checkbox" value="Costo" id="entrada-tipo-costo">
                                                <label class="form-check-label" for="entrada-tipo-costo">Costos</label>
                                            </div>
                                            <div class="form-check form-check-inline mb-0">
                                                <input class="form-check-input check-entrada-tipo" type="checkbox" value="Gasto" id="entrada-tipo-gasto">
                                                <label class="form-check-label" for="entrada-tipo-gasto">Gastos</label>
                                            </div>
                                        </div>
                                        <div class="form-text">Todos viene activo por defecto. Desactivalo para consultar solo bloques especificos.</div>
                                    </div>
                                </div>

                                <div class="row g-3 mb-3">
                                    <div class="col-md-4">
                                        <div class="border rounded p-3 bg-light">
                                            <small class="text-muted text-uppercase fw-semibold">Registros</small>
                                            <div class="fs-4 fw-bold" id="entradaDiarioTotalRegistros">0</div>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="border rounded p-3 bg-light">
                                            <small class="text-muted text-uppercase fw-semibold">Debito</small>
                                            <div class="fs-4 fw-bold text-success" id="entradaDiarioTotalDebito">0.00</div>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="border rounded p-3 bg-light">
                                            <small class="text-muted text-uppercase fw-semibold">Credito</small>
                                            <div class="fs-4 fw-bold text-danger" id="entradaDiarioTotalCredito">0.00</div>
                                        </div>
                                    </div>
                                </div>

                                <table id="tableEntradasDiario"
                                    class="table table-bordered nowrap table-striped align-middle"
                                    style="width:100%">
                                    <thead>
                                        <tr>
                                            <th>NoAsiento</th>
                                            <th>Fecha</th>
                                            <th>Ref</th>
                                            <th>NoRef</th>
                                            <th>Cuenta</th>
                                            <th>Debito</th>
                                            <th>Credito</th>
                                            <th>Descripcion</th>
                                            <th>Centro Costo</th>
                                            <th>IdCentroCosto</th>
                                            <th>IdViejo</th>
                                            <th>Grupo</th>
                                            <th>SubGrupo</th>
                                            <th>IdSubGrupo</th>
                                            <th>Division</th>
                                            <th>Modulo</th>
                                            <th>Creado Por</th>
                                            <th>Fecha Grabado</th>
                                            <th>Fecha Modificado</th>
                                            <th>Sociedad</th>
                                        </tr>
                                    </thead>
                                    <tbody></tbody>
                                </table>
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
        const csrfToken = '{{ csrf_token() }}';

        document.getElementById('btnConsultarEntradasDiario').addEventListener('click', consultarEntradasDiarioLocal);
        document.getElementById('btnSincronizarEntradasDiario').addEventListener('click', sincronizarEntradasDiario);
        document.getElementById('btnEliminarEntradasDiario').addEventListener('click', eliminarEntradasDiario);
        document.querySelectorAll('.check-entrada-tipo').forEach(check => {
            check.addEventListener('change', sincronizarChecksTipos);
        });

        function sincronizarChecksTipos(event) {
            const checkTodos = document.getElementById('entrada-tipo-todos');
            const checksDetalle = Array.from(document.querySelectorAll('.check-entrada-tipo:not(#entrada-tipo-todos)'));

            if (event?.target === checkTodos && checkTodos.checked) {
                checksDetalle.forEach(check => check.checked = false);
                return;
            }

            if (event?.target !== checkTodos && event?.target.checked) {
                checkTodos.checked = false;
            }

            if (!checkTodos.checked && checksDetalle.every(check => !check.checked)) {
                checkTodos.checked = true;
            }
        }

        function obtenerTiposEntradasDiario() {
            const checkTodos = document.getElementById('entrada-tipo-todos');

            if (checkTodos.checked) {
                return [];
            }

            return Array.from(document.querySelectorAll('.check-entrada-tipo:not(#entrada-tipo-todos):checked'))
                .map(check => check.value)
                .filter(Boolean);
        }

        function getEntradasDiarioFiltros() {
            const empresa = document.getElementById('entradaDiarioEmpresa').value;
            const fechaInicio = document.getElementById('entradaDiarioFechaInicio').value;
            const fechaFin = document.getElementById('entradaDiarioFechaFin').value;
            const cuenta = document.getElementById('entradaDiarioCuenta').value.trim();
            const centroCosto = document.getElementById('entradaDiarioCentroCosto').value.trim();
            const tipos = obtenerTiposEntradasDiario();

            if (!fechaInicio || !fechaFin) {
                throw new Error('Selecciona fecha inicio y fecha fin.');
            }

            if (fechaInicio > fechaFin) {
                throw new Error('La fecha inicio no puede ser mayor que la fecha fin.');
            }

            const params = new URLSearchParams({ empresa, fecha_inicio: fechaInicio, fecha_fin: fechaFin });

            if (cuenta !== '') params.set('cuenta', cuenta);
            if (centroCosto !== '') params.set('centro_costo', centroCosto);
            tipos.forEach(tipo => params.append('tipos[]', tipo));

            return { empresa, fechaInicio, fechaFin, cuenta, centroCosto, tipos, params };
        }

        async function parseJsonResponse(response, fallbackMessage) {
            const data = await response.json().catch(() => ({}));
            if (!response.ok) {
                throw new Error(data.message || fallbackMessage);
            }
            return data;
        }

        function parseFechaIso(fecha) {
            const [year, month, day] = fecha.split('-').map(Number);
            return new Date(year, month - 1, day);
        }

        function formatFechaIso(fecha) {
            const year = fecha.getFullYear();
            const month = String(fecha.getMonth() + 1).padStart(2, '0');
            const day = String(fecha.getDate()).padStart(2, '0');
            return `${year}-${month}-${day}`;
        }

        function sumarDias(fecha, dias) {
            const nuevaFecha = new Date(fecha.getTime());
            nuevaFecha.setDate(nuevaFecha.getDate() + dias);
            return nuevaFecha;
        }

        function diasEntreIso(fechaInicio, fechaFin) {
            const inicio = parseFechaIso(fechaInicio);
            const fin = parseFechaIso(fechaFin);
            return Math.floor((fin - inicio) / 86400000) + 1;
        }

        function crearBloquesFecha(fechaInicio, fechaFin, tamanoBloque = 5) {
            const bloques = [];
            let actual = parseFechaIso(fechaInicio);
            const fin = parseFechaIso(fechaFin);

            while (actual <= fin) {
                const inicioBloque = new Date(actual.getTime());
                const finBloque = sumarDias(inicioBloque, tamanoBloque - 1);
                const cierreBloque = finBloque > fin ? fin : finBloque;

                bloques.push({
                    inicio: formatFechaIso(inicioBloque),
                    fin: formatFechaIso(cierreBloque),
                    dias: diasEntreIso(formatFechaIso(inicioBloque), formatFechaIso(cierreBloque)),
                });

                actual = sumarDias(cierreBloque, 1);
            }

            return bloques;
        }

        function partirBloqueFecha(bloque) {
            if (bloque.dias <= 1) {
                return [bloque];
            }

            const inicio = parseFechaIso(bloque.inicio);
            const mitad = sumarDias(inicio, Math.floor((bloque.dias - 1) / 2));
            const siguiente = sumarDias(mitad, 1);

            return [
                {
                    inicio: bloque.inicio,
                    fin: formatFechaIso(mitad),
                    dias: diasEntreIso(bloque.inicio, formatFechaIso(mitad)),
                },
                {
                    inicio: formatFechaIso(siguiente),
                    fin: bloque.fin,
                    dias: diasEntreIso(formatFechaIso(siguiente), bloque.fin),
                },
            ];
        }

        function renderProgresoSincronizacion(progreso) {
            const porcentaje = progreso.totalDias > 0
                ? Math.min(100, Math.round((progreso.diasProcesados / progreso.totalDias) * 100))
                : 0;
            const faltantes = Math.max(progreso.totalDias - progreso.diasProcesados, 0);
            const bloqueActual = progreso.bloqueActual
                ? `${progreso.bloqueActual.inicio} / ${progreso.bloqueActual.fin}`
                : 'Preparando...';

            return `
                <div class="text-start">
                    <div class="mb-2"><strong>Bloque actual:</strong> ${bloqueActual}</div>
                    <div class="progress mb-2" style="height: 18px;">
                        <div class="progress-bar progress-bar-striped progress-bar-animated" role="progressbar" style="width: ${porcentaje}%;">${porcentaje}%</div>
                    </div>
                    <div class="small text-muted mb-3">
                        Procesados: ${progreso.diasProcesados.toLocaleString('es-DO')} de ${progreso.totalDias.toLocaleString('es-DO')} dias.
                        Faltan: ${faltantes.toLocaleString('es-DO')}.
                    </div>
                    <div class="row g-2 small">
                        <div class="col-6"><strong>Recibidos:</strong> ${progreso.total_recibidos.toLocaleString('es-DO')}</div>
                        <div class="col-6"><strong>Creados:</strong> ${progreso.creados.toLocaleString('es-DO')}</div>
                        <div class="col-6"><strong>Actualizados:</strong> ${progreso.actualizados.toLocaleString('es-DO')}</div>
                        <div class="col-6"><strong>Sin cambios:</strong> ${progreso.sin_cambios.toLocaleString('es-DO')}</div>
                        <div class="col-6"><strong>Omitidos:</strong> ${progreso.omitidos.toLocaleString('es-DO')}</div>
                        <div class="col-6"><strong>Bloques pendientes:</strong> ${progreso.bloquesPendientes.toLocaleString('es-DO')}</div>
                    </div>
                </div>
            `;
        }

        function actualizarSwalSincronizacion(progreso) {
            if (typeof Swal === 'undefined') return;

            Swal.update({
                title: 'Sincronizando movimiento',
                html: renderProgresoSincronizacion(progreso),
            });
            Swal.showLoading();
        }

        function limpiarTablaEntradasDiario() {
            if ($.fn.DataTable.isDataTable('#tableEntradasDiario')) {
                $('#tableEntradasDiario').DataTable().clear().destroy();
            }

            document.querySelector('#tableEntradasDiario tbody').innerHTML = '';
            document.getElementById('entradaDiarioTotalRegistros').textContent = '0';
            document.getElementById('entradaDiarioTotalDebito').textContent = '0.00';
            document.getElementById('entradaDiarioTotalCredito').textContent = '0.00';
        }

        async function sincronizarBloqueEntradasDiario(filtros, bloque) {
            const response = await fetch('/api-entradas-diario/sync', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json',
                },
                body: JSON.stringify({
                    empresa: filtros.empresa,
                    fecha_inicio: bloque.inicio,
                    fecha_fin: bloque.fin,
                    cuenta: filtros.cuenta,
                    centro_costo: filtros.centroCosto,
                    tipos: filtros.tipos,
                }),
                });

            return parseJsonResponse(response, 'No se pudo sincronizar movimiento del mayor.');
        }

        async function consultarEntradasDiarioLocal(mostrarExito = true) {
            let filtros;
            try {
                filtros = getEntradasDiarioFiltros();
            } catch (error) {
                alert(error.message);
                return;
            }

            if (mostrarExito) {
                const confirmado = await confirmarConsultaEntradasDiarioLocal(filtros);
                if (!confirmado) return;
            }

            const boton = document.getElementById('btnConsultarEntradasDiario');
            const textoOriginal = boton.innerText;
            boton.disabled = true;
            boton.innerText = 'Consultando...';

            try {
                if (mostrarExito && typeof Swal !== 'undefined') {
                    Swal.fire({
                        title: 'Consultando data local',
                        html: 'Buscando movimientos guardados con los filtros seleccionados...',
                        allowOutsideClick: false,
                        allowEscapeKey: false,
                        didOpen: () => Swal.showLoading(),
                    });
                }

                const response = await fetch('/api-entradas-diario?' + filtros.params.toString(), {
                    headers: { 'Accept': 'application/json' },
                });
                const data = await parseJsonResponse(response, 'No se pudo consultar la data local.');
                renderEntradasDiario(data.data || [], data.summary || {});

                if (mostrarExito && typeof Swal !== 'undefined') {
                    const detalle = data.truncated
                        ? `<br>Mostrando ${(data.displayed || 0).toLocaleString('es-DO')} de ${(data.summary?.registros || 0).toLocaleString('es-DO')} registros. Usa cuenta o centro costo para filtrar mas.`
                        : '';
                    Swal.fire('Consulta completada', `Registros: ${(data.summary?.registros || 0).toLocaleString('es-DO')}${detalle}`, 'success');
                }
            } catch (error) {
                if (typeof Swal !== 'undefined') {
                    Swal.fire('Error', error.message || 'No se pudo consultar la data local.', 'error');
                } else {
                    alert(error.message || 'No se pudo consultar la data local.');
                }
            } finally {
                boton.disabled = false;
                boton.innerText = textoOriginal;
            }
        }

        async function confirmarConsultaEntradasDiarioLocal(filtros) {
            if (typeof Swal === 'undefined') {
                return confirm('Consultar data local del movimiento del mayor?');
            }

            const detalleTipos = filtros.tipos.length > 0 ? filtros.tipos.join(', ') : 'Todos';
            const detalleCuenta = filtros.cuenta !== '' ? filtros.cuenta : 'Todas';
            const detalleCentroCosto = filtros.centroCosto !== '' ? filtros.centroCosto : 'Todos';

            return Swal.fire({
                icon: 'question',
                title: 'Consultar data local',
                html:
                    `Se consultara la data local del movimiento del mayor.<br>` +
                    `<strong>Empresa:</strong> ${filtros.empresa}<br>` +
                    `<strong>Periodo:</strong> ${filtros.fechaInicio} a ${filtros.fechaFin}<br>` +
                    `<strong>Cuenta:</strong> ${detalleCuenta}<br>` +
                    `<strong>Centro costo:</strong> ${detalleCentroCosto}<br>` +
                    `<strong>Tipos:</strong> ${detalleTipos}`,
                showCancelButton: true,
                confirmButtonText: 'Consultar',
                cancelButtonText: 'Cancelar',
            }).then(result => result.isConfirmed);
        }

        async function sincronizarEntradasDiario() {
            let filtros;
            try {
                filtros = getEntradasDiarioFiltros();
            } catch (error) {
                alert(error.message);
                return;
            }

            const totalDias = diasEntreIso(filtros.fechaInicio, filtros.fechaFin);

            const confirmado = typeof Swal !== 'undefined'
                ? await Swal.fire({
                    icon: 'question',
                    title: 'Sincronizar movimiento del mayor',
                    html: `Esto consultara el API por bloques y actualizara registros locales si detecta cambios.<br><strong>Dias a procesar: ${totalDias.toLocaleString('es-DO')}</strong>`,
                    showCancelButton: true,
                    confirmButtonText: 'Sincronizar',
                    cancelButtonText: 'Cancelar',
                }).then(result => result.isConfirmed)
                : confirm('Sincronizar movimiento del mayor desde el API?');

            if (!confirmado) return;

            const boton = document.getElementById('btnSincronizarEntradasDiario');
            const textoOriginal = boton.innerText;
            boton.disabled = true;
            boton.innerText = 'Sincronizando...';
            limpiarTablaEntradasDiario();

            const colaBloques = crearBloquesFecha(filtros.fechaInicio, filtros.fechaFin, 1);
            const progreso = {
                totalDias,
                diasProcesados: 0,
                bloqueActual: null,
                bloquesPendientes: colaBloques.length,
                total_recibidos: 0,
                creados: 0,
                actualizados: 0,
                sin_cambios: 0,
                omitidos: 0,
            };

            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    title: 'Sincronizando movimiento',
                    html: renderProgresoSincronizacion(progreso),
                    allowOutsideClick: false,
                    allowEscapeKey: false,
                    didOpen: () => Swal.showLoading(),
                });
            }

            try {
                while (colaBloques.length > 0) {
                    const bloque = colaBloques.shift();
                    progreso.bloqueActual = bloque;
                    progreso.bloquesPendientes = colaBloques.length;
                    actualizarSwalSincronizacion(progreso);

                    try {
                        const data = await sincronizarBloqueEntradasDiario(filtros, bloque);

                        progreso.total_recibidos += Number(data.total_recibidos || 0);
                        progreso.creados += Number(data.creados || 0);
                        progreso.actualizados += Number(data.actualizados || 0);
                        progreso.sin_cambios += Number(data.sin_cambios || 0);
                        progreso.omitidos += Number(data.omitidos || 0);
                        progreso.diasProcesados += bloque.dias;
                        progreso.bloquesPendientes = colaBloques.length;
                        actualizarSwalSincronizacion(progreso);
                    } catch (error) {
                        if (bloque.dias > 1) {
                            const partes = partirBloqueFecha(bloque);
                            colaBloques.unshift(...partes);
                            progreso.bloquesPendientes = colaBloques.length;
                            actualizarSwalSincronizacion(progreso);
                            continue;
                        }

                        throw error;
                    }
                }

                if (typeof Swal !== 'undefined') {
                    Swal.fire(
                        'Sincronizacion completada',
                        `Dias procesados: ${progreso.diasProcesados.toLocaleString('es-DO')} de ${progreso.totalDias.toLocaleString('es-DO')}<br>` +
                        `Recibidos: ${progreso.total_recibidos.toLocaleString('es-DO')}<br>` +
                        `Creados: ${progreso.creados.toLocaleString('es-DO')}<br>` +
                        `Actualizados: ${progreso.actualizados.toLocaleString('es-DO')}<br>` +
                        `Sin cambios: ${progreso.sin_cambios.toLocaleString('es-DO')}<br>` +
                        `Omitidos: ${progreso.omitidos.toLocaleString('es-DO')}`,
                        'success'
                    );
                } else {
                    alert('Sincronizacion completada.');
                }

                await consultarEntradasDiarioLocal(false);
            } catch (error) {
                if (typeof Swal !== 'undefined') {
                    Swal.fire('Error', error.message || 'Error sincronizando movimiento del mayor.', 'error');
                } else {
                    alert(error.message || 'Error sincronizando movimiento del mayor.');
                }
            } finally {
                boton.disabled = false;
                boton.innerText = textoOriginal;
            }
        }

        async function eliminarEntradasDiario() {
            let filtros;
            try {
                filtros = getEntradasDiarioFiltros();
            } catch (error) {
                alert(error.message);
                return;
            }

            const confirmado = typeof Swal !== 'undefined'
                ? await Swal.fire({
                    icon: 'warning',
                    title: 'Eliminar data local',
                    html: `Se eliminara la data local del movimiento del mayor de la empresa <strong>${filtros.empresa}</strong> entre <strong>${filtros.fechaInicio}</strong> y <strong>${filtros.fechaFin}</strong>.`,
                    showCancelButton: true,
                    confirmButtonText: 'Si, eliminar',
                    cancelButtonText: 'Cancelar',
                    confirmButtonColor: '#dc3545',
                }).then(result => result.isConfirmed)
                : confirm('Eliminar data local del movimiento del mayor para el rango seleccionado?');

            if (!confirmado) return;

            const boton = document.getElementById('btnEliminarEntradasDiario');
            const textoOriginal = boton.innerText;
            boton.disabled = true;
            boton.innerText = 'Eliminando...';

            try {
                const response = await fetch('/api-entradas-diario', {
                    method: 'DELETE',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({
                        empresa: filtros.empresa,
                        fecha_inicio: filtros.fechaInicio,
                        fecha_fin: filtros.fechaFin,
                        cuenta: filtros.cuenta,
                        centro_costo: filtros.centroCosto,
                        tipos: filtros.tipos,
                    }),
                });
                const data = await parseJsonResponse(response, 'No se pudo eliminar la data local.');
                renderEntradasDiario([], { registros: 0, debito: 0, credito: 0 });

                if (typeof Swal !== 'undefined') {
                    Swal.fire('Eliminacion completada', `Eliminados: ${(data.eliminados ?? 0).toLocaleString('es-DO')}`, 'success');
                } else {
                    alert('Eliminados: ' + (data.eliminados ?? 0));
                }
            } catch (error) {
                alert(error.message || 'No se pudo eliminar la data local.');
            } finally {
                boton.disabled = false;
                boton.innerText = textoOriginal;
            }
        }

        function renderEntradasDiario(items, summary) {
            const tableBody = document.querySelector('#tableEntradasDiario tbody');
            tableBody.innerHTML = '';

            items.forEach(item => {
                const row = document.createElement('tr');
                row.innerHTML = `
                    <td>${escapeHtml(item.NoAsiento)}</td>
                    <td>${escapeHtml(item.Fecha)}</td>
                    <td>${escapeHtml(item.Ref)}</td>
                    <td>${escapeHtml(item.NoRef)}</td>
                    <td>${escapeHtml(item.Cuenta)}</td>
                    <td class="text-end">${formatMoney(item.Debito)}</td>
                    <td class="text-end">${formatMoney(item.Credito)}</td>
                    <td>${escapeHtml(item.Descripcion)}</td>
                    <td>${escapeHtml(item.CentroCosto)}</td>
                    <td>${escapeHtml(item.IdCentroCosto)}</td>
                    <td>${escapeHtml(item.IdViejo)}</td>
                    <td>${escapeHtml(item.Grupo)}</td>
                    <td>${escapeHtml(item.SubGrupo)}</td>
                    <td>${escapeHtml(item.IdSubGrupo)}</td>
                    <td>${escapeHtml(item.Division)}</td>
                    <td>${escapeHtml(item.Modulo)}</td>
                    <td>${escapeHtml(item.CreadoPor)}</td>
                    <td>${escapeHtml(item.FechaGrabado)}</td>
                    <td>${escapeHtml(item.FechaModificado)}</td>
                    <td>${escapeHtml(item.Sociedad)}</td>
                `;
                tableBody.appendChild(row);
            });

            document.getElementById('entradaDiarioTotalRegistros').textContent = Number(summary.registros || items.length || 0).toLocaleString('es-DO');
            document.getElementById('entradaDiarioTotalDebito').textContent = formatMoney(summary.debito || 0);
            document.getElementById('entradaDiarioTotalCredito').textContent = formatMoney(summary.credito || 0);

            $('#tableEntradasDiario').DataTable({
                destroy: true,
                responsive: false,
                scrollX: true,
                pageLength: 10,
                columnDefs: [
                    { targets: [5, 6], className: 'text-end' },
                    { targets: '_all', className: 'text-nowrap align-middle' }
                ],
                dom: 'Bfrtip',
                buttons: ['copy', 'csv', 'excel', 'pdf', 'print']
            });
        }

        function escapeHtml(value) {
            return String(value ?? '')
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#039;');
        }

        function formatMoney(value) {
            return Number(value || 0).toLocaleString('en-US', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2,
            });
        }
    </script>
@endsection
