@extends('app')

@section('content')
    <div class="main-content">
        <div class="page-content">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-12">
                        <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                            <h4 class="mb-sm-0">Flujo de ruta</h4>
                            <ol class="breadcrumb m-0">
                                <li class="breadcrumb-item"><a href="{{ route('inicio.index') }}">Inicio</a></li>
                                <li class="breadcrumb-item"><a href="{{ route('contabilidad.index') }}">Contabilidad</a></li>
                                <li class="breadcrumb-item active">Flujo de ruta</li>
                            </ol>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="card-title mb-0">Filtros</h5>
                            </div>
                            <div class="card-body">
                                <form id="form-estado-resultado" class="row g-3 align-items-end">
                                    <div class="col-md-3">
                                        <label class="form-label">Fecha desde</label>
                                        <input type="date" class="form-control" id="fecha_desde" value="{{ now()->startOfMonth()->format('Y-m-d') }}">
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label">Fecha hasta</label>
                                        <input type="date" class="form-control" id="fecha_hasta" value="{{ now()->endOfMonth()->format('Y-m-d') }}">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">Filtro fijo</label>
                                        <div class="border rounded px-3 py-2 bg-light">
                                            <div class="fw-semibold">Tipo contable: Activo</div>
                                            <div class="text-muted small">Cuentas control fijas: 10013 y 10021</div>
                                        </div>
                                        <input type="hidden" id="tipo-fijo-activo" value="Activo">
                                    </div>
                                    <div class="col-md-2">
                                        <div class="d-grid gap-2">
                                            <button type="submit" class="btn btn-primary" id="btn-consultar">
                                                Consultar
                                            </button>
                                            <button type="button" class="btn btn-danger" id="btn-generar-pdf">
                                                Generar PDF
                                            </button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row g-3 mb-3">
                    <div class="col-md-3">
                        <div class="card border-info border-opacity-25 h-100">
                            <div class="card-body">
                                <div class="text-muted small">Activo 10013 (Debito)</div>
                                <div class="fs-3 fw-semibold text-info" id="resumen-activos">0.00</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card border-warning border-opacity-25 h-100">
                            <div class="card-body">
                                <div class="text-muted small">Depositos a Bancos 10021 (Credito)</div>
                                <div class="fs-3 fw-semibold text-warning" id="resumen-10021">0.00</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card border-primary border-opacity-25 h-100" id="card-resumen-balance">
                            <div class="card-body">
                                <div class="text-muted small" id="resumen-balance-titulo">Balance</div>
                                <div class="fs-3 fw-semibold text-primary" id="resumen-balance">0.00</div>
                                <div class="small mt-1" id="resumen-balance-estado"></div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h5 class="card-title mb-0">Detalle</h5>
                                <span class="badge bg-info-subtle text-info" id="texto-periodo">-</span>
                            </div>
                            <div class="card-body">
                                <div id="estado-resultado-loader" class="text-center py-4 d-none">
                                    <div class="spinner-border text-primary" role="status"></div>
                                    <div class="mt-2 text-muted">Cargando flujo de ruta...</div>
                                </div>

                                <div id="estado-resultado-vacio" class="alert alert-warning d-none mb-0">
                                    No hay datos para el periodo seleccionado.
                                </div>

                                <div class="table-responsive d-none" id="estado-resultado-contenido">
                                    <table class="table table-bordered table-striped align-middle mb-0">
                                        <thead>
                                            <tr>
                                                <th style="width: 140px;">Cuenta</th>
                                                <th>Descripcion</th>
                                                <th style="width: 160px;" id="th-periodo">Debito</th>
                                                <th style="width: 180px;" id="th-credito">Credito</th>
                                                <th style="width: 140px;">Acumulado</th>
                                            </tr>
                                        </thead>
                                        <tbody id="tbody-estado-resultado"></tbody>
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
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.7.0/jspdf.plugin.autotable.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const form = document.getElementById('form-estado-resultado');
            const inputFechaDesde = document.getElementById('fecha_desde');
            const inputFechaHasta = document.getElementById('fecha_hasta');
            const btnConsultar = document.getElementById('btn-consultar');
            const btnGenerarPdf = document.getElementById('btn-generar-pdf');
            const loader = document.getElementById('estado-resultado-loader');
            const vacio = document.getElementById('estado-resultado-vacio');
            const contenido = document.getElementById('estado-resultado-contenido');
            const tbody = document.getElementById('tbody-estado-resultado');
            const textoPeriodo = document.getElementById('texto-periodo');
            const thPeriodo = document.getElementById('th-periodo');
            const thCredito = document.getElementById('th-credito');
            const resumenActivos = document.getElementById('resumen-activos');
            const resumen10021 = document.getElementById('resumen-10021');
            const resumenBalance = document.getElementById('resumen-balance');
            const cardResumenBalance = document.getElementById('card-resumen-balance');
            const resumenBalanceTitulo = document.getElementById('resumen-balance-titulo');
            const resumenBalanceEstado = document.getElementById('resumen-balance-estado');

            function actualizarEstadoTarjetaBalance(balanceValor) {
                const balance = Number(balanceValor || 0);
                const descuadre = Math.abs(balance) > 0.009;

                if (!cardResumenBalance || !resumenBalance || !resumenBalanceTitulo) {
                    return;
                }

                cardResumenBalance.classList.remove('border-primary', 'border-warning', 'border-success');
                resumenBalance.classList.remove('text-primary', 'text-warning', 'text-success');

                if (descuadre) {
                    cardResumenBalance.classList.add('border-warning');
                    resumenBalance.classList.add('text-warning');
                    resumenBalanceTitulo.textContent = 'Naranja';

                    if (resumenBalanceEstado) {
                        resumenBalanceEstado.className = 'small mt-1 text-warning';
                        resumenBalanceEstado.textContent = 'Pendiente por conciliar: ' + formatoMonto(balance);
                    }

                    return;
                }

                cardResumenBalance.classList.add('border-success');
                resumenBalance.classList.add('text-success');
                resumenBalanceTitulo.textContent = 'Balance';

                if (resumenBalanceEstado) {
                    resumenBalanceEstado.className = 'small mt-1 text-success';
                    resumenBalanceEstado.textContent = 'Conciliado: balance en cero.';
                }
            }

            function formatoMonto(valor) {
                return Number(valor || 0).toLocaleString('en-US', {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2,
                });
            }

            function obtenerTiposSeleccionados() {
                return ['Activo'];
            }

            function renderGrupo(grupo) {
                const filas = [];
                filas.push(`
                    <tr class="table-light">
                        <td colspan="5" class="fw-bold">${grupo.titulo}</td>
                    </tr>
                `);

                (grupo.cuentas || []).forEach(function (cuenta) {
                    filas.push(`
                        <tr>
                            <td>${cuenta.cuenta || ''}</td>
                            <td>${cuenta.descripcion || ''}</td>
                            <td class="text-end">${formatoMonto(cuenta.debito_columna)}</td>
                            <td class="text-end">${formatoMonto(cuenta.credito_columna)}</td>
                            <td class="text-end">${formatoMonto(cuenta.acumulado)}</td>
                        </tr>
                    `);
                });

                filas.push(`
                    <tr class="table-secondary fw-semibold">
                        <td colspan="2">Total ${grupo.titulo}</td>
                        <td class="text-end">${formatoMonto(grupo.total_periodo)}</td>
                        <td class="text-end">${formatoMonto(grupo.total_credito_columna)}</td>
                        <td class="text-end">${formatoMonto(grupo.total_acumulado)}</td>
                    </tr>
                `);

                return filas.join('');
            }

            function crearResumenVacio() {
                return {
                    activos_periodo: 0,
                    activos_acumulado: 0,
                    cuenta_10021_periodo: 0,
                    cuenta_10021_acumulado: 0,
                    balance_periodo: 0,
                    balance_acumulado: 0,
                    creditos_bancos_periodo: 0,
                    creditos_bancos_acumulado: 0,
                };
            }

            function acumularResumen(destino, fuente) {
                Object.keys(destino).forEach(function (key) {
                    destino[key] = Number(destino[key] || 0) + Number(fuente?.[key] || 0);
                });
            }

            function actualizarEstadoSwal(tipo, indice, total) {
                if (typeof Swal === 'undefined' || !Swal.isVisible()) {
                    return;
                }

                const html = Swal.getHtmlContainer();
                if (!html) {
                    return;
                }

                html.innerHTML = `
                    <div class="text-start">
                        <div><strong>Bloque actual:</strong> ${tipo}</div>
                        <div><strong>Progreso:</strong> ${indice} de ${total}</div>
                        <div class="text-muted small mt-2">La consulta avanza por bloque y conserva cada resultado en memoria.</div>
                    </div>
                `;
            }

            function construirDetalleDebug(debug) {
                if (!debug || typeof debug !== 'object') {
                    return '';
                }

                const cuentas = Number(debug.cuentas || 0);
                const dias = Math.round(Number(debug.dias || 0));
                const llamadas = Math.round(Number(debug.llamadas_estimadas || 0));
                const maxLlamadas = Math.round(Number(debug.max_llamadas_api_en_vivo || 0));

                if (!cuentas && !dias && !llamadas && !maxLlamadas) {
                    return '';
                }

                let detalle = `\n\nDetalle: cuentas=${cuentas}, dias=${dias}, llamadas_estimadas=${llamadas}`;
                if (maxLlamadas > 0) {
                    detalle += `, max_permitidas=${maxLlamadas}`;
                }

                return detalle;
            }

            function obtenerTextoTipos() {
                return 'Activo (Cuentas control 10013 y 10021)';
            }

            async function obtenerMetaCuentas(tiposSeleccionados) {
                const params = new URLSearchParams();
                tiposSeleccionados.forEach(function (tipo) {
                    params.append('tipos[]', tipo);
                });

                const response = await fetch("{{ route('contabilidad.reportes.flujo-ruta.meta') }}?" + params.toString(), {
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                    }
                });

                const payload = await response.json();
                if (!response.ok) {
                    throw new Error(payload?.message || 'No se pudo obtener la meta de cuentas.');
                }

                return {
                    totalCuentas: Number(payload?.total_cuentas || 0),
                    conteoPorTipo: payload?.conteo_por_tipo || {},
                };
            }

            function parseFechaIso(fecha) {
                const partes = String(fecha || '').split('-').map(Number);
                if (partes.length !== 3) {
                    return null;
                }

                const [anio, mes, dia] = partes;
                if (!anio || !mes || !dia) {
                    return null;
                }

                return new Date(anio, mes - 1, dia);
            }

            function formatFechaIso(date) {
                const y = date.getFullYear();
                const m = String(date.getMonth() + 1).padStart(2, '0');
                const d = String(date.getDate()).padStart(2, '0');
                return `${y}-${m}-${d}`;
            }

            function diasEntreIso(fechaDesde, fechaHasta) {
                const desde = parseFechaIso(fechaDesde);
                const hasta = parseFechaIso(fechaHasta);
                if (!desde || !hasta) {
                    return 0;
                }

                const ms = hasta.getTime() - desde.getTime();
                return Math.floor(ms / 86400000) + 1;
            }

            function partirRangoMitad(fechaDesde, fechaHasta) {
                const desde = parseFechaIso(fechaDesde);
                const hasta = parseFechaIso(fechaHasta);
                if (!desde || !hasta) {
                    return null;
                }

                const dias = diasEntreIso(fechaDesde, fechaHasta);
                if (dias <= 1) {
                    return null;
                }

                const mitad = Math.floor((dias - 1) / 2);
                const corte = new Date(desde);
                corte.setDate(corte.getDate() + mitad);

                const inicioSegundo = new Date(corte);
                inicioSegundo.setDate(inicioSegundo.getDate() + 1);

                return [
                    { fecha_desde: fechaDesde, fecha_hasta: formatFechaIso(corte) },
                    { fecha_desde: formatFechaIso(inicioSegundo), fecha_hasta: fechaHasta }
                ];
            }

            function crearVentanasCuentas(totalCuentas, tamanoBloque) {
                const ventanas = [];
                const total = Math.max(0, Number(totalCuentas || 0));
                const tam = Math.max(1, Number(tamanoBloque || 1));

                for (let offset = 0; offset < total; offset += tam) {
                    ventanas.push({
                        offset: offset,
                        limit: Math.min(tam, total - offset),
                        inicio: offset + 1,
                        fin: Math.min(offset + tam, total),
                        total: total,
                    });
                }

                return ventanas;
            }

            function esErrorDivisible(status, message, payload) {
                const texto = String(message || payload?.message || '').toLowerCase();
                return status === 422
                    || texto.includes('maximum execution time')
                    || texto.includes('demasiado amplia')
                    || texto.includes('timed out')
                    || texto.includes('timeout');
            }

            async function consultarBloqueRango(tipo, fechaDesde, fechaHasta, cuentaOffset, cuentaLimit) {
                const params = new URLSearchParams();
                params.set('fecha_desde', fechaDesde);
                params.set('fecha_hasta', fechaHasta);
                params.append('tipos[]', tipo);
                params.set('cuenta_offset', String(cuentaOffset || 0));
                params.set('cuenta_limit', String(cuentaLimit || 0));

                const response = await fetch("{{ route('contabilidad.reportes.flujo-ruta.data') }}?" + params.toString(), {
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                    }
                });

                const contentType = String(response.headers.get('content-type') || '');
                const payload = contentType.includes('application/json')
                    ? await response.json()
                    : { message: await response.text() };

                if (!response.ok) {
                    const error = new Error(payload?.message || ('No se pudo consultar el bloque ' + tipo + '.'));
                    error.status = response.status;
                    error.payload = payload;
                    throw error;
                }

                return payload;
            }

            function acumularGruposPorCuenta(gruposMap, grupos) {
                (grupos || []).forEach(function (grupo) {
                    const claveGrupo = String(grupo.titulo || '').trim() || 'Grupo';

                    if (!gruposMap.has(claveGrupo)) {
                        gruposMap.set(claveGrupo, {
                            titulo: claveGrupo,
                            orden: gruposMap.size + 1,
                            total_periodo: 0,
                            total_credito_columna: 0,
                            total_acumulado: 0,
                            cuentasMap: new Map(),
                        });
                    }

                    const acumuladoGrupo = gruposMap.get(claveGrupo);
                    acumuladoGrupo.total_periodo += Number(grupo.total_periodo || 0);
                    acumuladoGrupo.total_credito_columna += Number(grupo.total_credito_columna || 0);
                    acumuladoGrupo.total_acumulado += Number(grupo.total_acumulado || 0);

                    (grupo.cuentas || []).forEach(function (cuenta) {
                        const claveCuenta = String(cuenta.cuenta || '').trim() || String(cuenta.descripcion || '').trim();
                        if (!acumuladoGrupo.cuentasMap.has(claveCuenta)) {
                            acumuladoGrupo.cuentasMap.set(claveCuenta, {
                                cuenta: cuenta.cuenta || '',
                                descripcion: cuenta.descripcion || '',
                                periodo: 0,
                                debito_columna: 0,
                                credito_columna: 0,
                                acumulado: 0,
                            });
                        }

                        const acumuladoCuenta = acumuladoGrupo.cuentasMap.get(claveCuenta);
                        acumuladoCuenta.periodo += Number(cuenta.periodo || 0);
                        acumuladoCuenta.debito_columna += Number(cuenta.debito_columna || 0);
                        acumuladoCuenta.credito_columna += Number(cuenta.credito_columna || 0);
                        acumuladoCuenta.acumulado += Number(cuenta.acumulado || 0);
                    });
                });
            }

            function serializarGrupos(gruposMap) {
                return Array.from(gruposMap.values()).map(function (grupo) {
                    return {
                        titulo: grupo.titulo,
                        orden: grupo.orden,
                        total_periodo: Number(grupo.total_periodo || 0),
                        total_credito_columna: Number(grupo.total_credito_columna || 0),
                        total_acumulado: Number(grupo.total_acumulado || 0),
                        cuentas: Array.from(grupo.cuentasMap.values()).map(function (cuenta) {
                            return {
                                cuenta: cuenta.cuenta,
                                descripcion: cuenta.descripcion,
                                periodo: Number(cuenta.periodo || 0),
                                debito_columna: Number(cuenta.debito_columna || 0),
                                credito_columna: Number(cuenta.credito_columna || 0),
                                acumulado: Number(cuenta.acumulado || 0),
                            };
                        })
                    };
                });
            }

            async function consultarBloqueConSegmentacion(tipo, fechaDesde, fechaHasta, cuentaOffset, cuentaLimit, onEstado) {
                const cola = [{ fecha_desde: fechaDesde, fecha_hasta: fechaHasta }];
                const resultados = [];

                while (cola.length) {
                    const tramo = cola.shift();
                    const diasTramo = diasEntreIso(tramo.fecha_desde, tramo.fecha_hasta);

                    onEstado(tipo, `Procesando ${tramo.fecha_desde} a ${tramo.fecha_hasta}`);

                    try {
                        const payload = await consultarBloqueRango(tipo, tramo.fecha_desde, tramo.fecha_hasta, cuentaOffset, cuentaLimit);
                        resultados.push(payload);
                    } catch (error) {
                        const status = Number(error?.status || 0);
                        const payload = error?.payload || {};
                        const message = String(error?.message || 'Error consultando bloque');

                        if (diasTramo > 1 && esErrorDivisible(status, message, payload)) {
                            const dividido = partirRangoMitad(tramo.fecha_desde, tramo.fecha_hasta);
                            if (dividido) {
                                onEstado(tipo, `Dividiendo tramo ${tramo.fecha_desde} a ${tramo.fecha_hasta}`);
                                cola.unshift(dividido[1]);
                                cola.unshift(dividido[0]);
                                continue;
                            }
                        }

                        const detalleDebug = construirDetalleDebug(payload?.debug);
                        throw new Error((payload?.message || message || ('No se pudo consultar el bloque ' + tipo + '.')) + detalleDebug);
                    }
                }

                return resultados;
            }

            function actualizarProgresoGlobal(tipo, cuentasCompletadas, totalCuentas, estadoBloque, cuentasBloque, rangoCuentas) {
                if (typeof Swal === 'undefined' || !Swal.isVisible()) {
                    return;
                }

                const porcentaje = totalCuentas > 0 ? Math.min(100, Math.round((cuentasCompletadas / totalCuentas) * 100)) : 0;
                const html = Swal.getHtmlContainer();
                if (!html) {
                    return;
                }

                html.innerHTML = `
                    <div class="text-start">
                        <div><strong>Bloque actual:</strong> ${tipo}</div>
                        <div><strong>Cuentas del bloque:</strong> ${cuentasBloque}</div>
                        <div><strong>Sub-bloque actual:</strong> ${rangoCuentas || '-'}</div>
                        <div><strong>Progreso:</strong> ${cuentasCompletadas} de ${totalCuentas} cuentas (${porcentaje}%)</div>
                        <div class="progress mt-2" style="height:10px;">
                            <div class="progress-bar" role="progressbar" style="width: ${porcentaje}%;" aria-valuenow="${porcentaje}" aria-valuemin="0" aria-valuemax="100"></div>
                        </div>
                        <div class="text-muted small mt-2">${estadoBloque || 'Procesando...'}</div>
                    </div>
                `;
            }

            function generarPdfEstadoResultado() {
                if (contenido.classList.contains('d-none') || !tbody.querySelector('tr')) {
                    Swal.fire('Sin datos', 'Primero consulta el reporte antes de generar el PDF.', 'warning');
                    return;
                }

                let jsPDFConstructor = null;
                if (window.jspdf && window.jspdf.jsPDF) {
                    jsPDFConstructor = window.jspdf.jsPDF;
                } else if (window.jsPDF) {
                    jsPDFConstructor = window.jsPDF;
                }

                if (!jsPDFConstructor) {
                    Swal.fire('Error', 'jsPDF no esta cargado en la pagina.', 'error');
                    return;
                }

                const doc = new jsPDFConstructor({
                    orientation: 'landscape',
                    unit: 'pt',
                    format: 'a4'
                });

                const margin = 30;
                const pageWidth = doc.internal.pageSize.getWidth();
                const resumen = [
                    ['Activos', resumenActivos.textContent || '0.00'],
                ];

                let y = 36;
                doc.setFontSize(20);
                doc.text('Flujo de ruta Grupo Joselito', margin, y);
                y += 20;
                doc.setFontSize(10);
                doc.setTextColor(90, 90, 90);
                doc.text(`Periodo: ${textoPeriodo.textContent || '-'}`, margin, y);
                y += 14;
                doc.text(`Tipos contables: ${obtenerTextoTipos()}`, margin, y);
                y += 18;

                doc.autoTable({
                    startY: y,
                    head: [['Bloque', 'Monto']],
                    body: resumen,
                    theme: 'grid',
                    margin: { left: margin, right: margin },
                    styles: {
                        fontSize: 10,
                        cellPadding: 5
                    },
                    headStyles: {
                        fillColor: [52, 73, 94],
                        textColor: [255, 255, 255]
                    },
                    columnStyles: {
                        0: { cellWidth: 200 },
                        1: { halign: 'right' }
                    }
                });

                const headers = [
                    'Cuenta',
                    'Descripcion',
                    thPeriodo.textContent || 'Balance',
                    thCredito?.textContent || 'Credito',
                    'Acumulado'
                ];
                const rows = [];

                Array.from(tbody.querySelectorAll('tr')).forEach(function (tr) {
                    const cells = Array.from(tr.querySelectorAll('td'));
                    const textCells = cells.map(function (td) {
                        return td.textContent.trim().replace(/\s+/g, ' ');
                    });

                    if (cells.length === 1 && cells[0].hasAttribute('colspan')) {
                        rows.push([
                            {
                                content: textCells[0],
                                colSpan: 5,
                                styles: {
                                    fillColor: [248, 249, 250],
                                    textColor: [33, 37, 41],
                                    fontStyle: 'bold',
                                    halign: 'left'
                                }
                            }
                        ]);
                        return;
                    }

                    if (cells.length === 4) {
                        rows.push([
                            {
                                content: textCells[0],
                                colSpan: 2,
                                styles: {
                                    fillColor: [233, 236, 239],
                                    fontStyle: 'bold',
                                    halign: 'left'
                                }
                            },
                            {
                                content: textCells[1],
                                styles: {
                                    fillColor: [233, 236, 239],
                                    fontStyle: 'bold',
                                    halign: 'right'
                                }
                            },
                            {
                                content: textCells[2],
                                styles: {
                                    fillColor: [233, 236, 239],
                                    fontStyle: 'bold',
                                    halign: 'right'
                                }
                            },
                            {
                                content: textCells[3],
                                styles: {
                                    fillColor: [233, 236, 239],
                                    fontStyle: 'bold',
                                    halign: 'right'
                                }
                            }
                        ]);
                        return;
                    }

                    if (cells.length === 5) {
                        rows.push(textCells);
                    }
                });

                doc.autoTable({
                    startY: doc.lastAutoTable.finalY + 18,
                    head: [headers],
                    body: rows,
                    theme: 'grid',
                    margin: { left: margin, right: margin },
                    styles: {
                        fontSize: 8.5,
                        cellPadding: 4,
                        overflow: 'linebreak'
                    },
                    headStyles: {
                        fillColor: [41, 128, 185],
                        textColor: [255, 255, 255]
                    },
                    columnStyles: {
                        0: { cellWidth: 90 },
                        1: { cellWidth: pageWidth - (margin * 2) - 360 },
                        2: { cellWidth: 90, halign: 'right' },
                        3: { cellWidth: 90, halign: 'right' },
                        4: { cellWidth: 90, halign: 'right' }
                    }
                });

                doc.save('flujo_ruta.pdf');
            }

            async function cargarEstadoResultado() {
                const fechaDesde = String(inputFechaDesde?.value || '').trim();
                const fechaHasta = String(inputFechaHasta?.value || '').trim();
                const tiposSeleccionados = obtenerTiposSeleccionados();

                if (!fechaDesde || !/^\d{4}-\d{2}-\d{2}$/.test(fechaDesde)) {
                    Swal.fire('Fecha invalida', 'Debes indicar una fecha desde valida.', 'warning');
                    return;
                }

                if (!fechaHasta || !/^\d{4}-\d{2}-\d{2}$/.test(fechaHasta)) {
                    Swal.fire('Fecha invalida', 'Debes indicar una fecha hasta valida.', 'warning');
                    return;
                }

                if (!tiposSeleccionados.length) {
                    Swal.fire('Tipos requeridos', 'Debes seleccionar al menos un bloque contable.', 'warning');
                    return;
                }

                loader.classList.remove('d-none');
                vacio.classList.add('d-none');
                contenido.classList.add('d-none');
                tbody.innerHTML = '';
                btnConsultar.disabled = true;

                try {
                    const gruposMap = new Map();
                    const resumenAcumulado = crearResumenVacio();
                    const erroresPorBloque = [];
                    let periodoTexto = '-';
                    let columnaPeriodo = 'Debito';
                    let columnaCredito = 'Credito';
                    const metaCuentas = await obtenerMetaCuentas(tiposSeleccionados);
                    const totalCuentasProceso = Number(metaCuentas.totalCuentas || 0);
                    const conteoPorTipo = metaCuentas.conteoPorTipo || {};
                    let cuentasCompletadas = 0;
                    const tamanoSubBloque = 8;

                    if (typeof Swal !== 'undefined') {
                        Swal.fire({
                            title: 'Consultando flujo de ruta',
                            html: '<div class="text-start"><strong>Preparando bloques...</strong></div>',
                            allowOutsideClick: false,
                            allowEscapeKey: false,
                            didOpen: function () {
                                Swal.showLoading();
                            }
                        });
                    }

                    for (let index = 0; index < tiposSeleccionados.length; index += 1) {
                        const tipo = tiposSeleccionados[index];
                        const cuentasBloque = Number(conteoPorTipo[tipo] || 0);
                        const ventanasCuentas = crearVentanasCuentas(cuentasBloque, tamanoSubBloque);
                        let cuentasProcesadasBloque = 0;

                        actualizarProgresoGlobal(
                            tipo,
                            cuentasCompletadas,
                            totalCuentasProceso,
                            `Procesando bloque ${index + 1} de ${tiposSeleccionados.length}`,
                            cuentasBloque,
                            ventanasCuentas.length ? `${ventanasCuentas[0].inicio}-${ventanasCuentas[0].fin}` : '-'
                        );

                        try {
                            for (let ventanaIndex = 0; ventanaIndex < ventanasCuentas.length; ventanaIndex += 1) {
                                const ventana = ventanasCuentas[ventanaIndex];
                                const rangoCuentas = `${ventana.inicio}-${ventana.fin} de ${ventana.total}`;

                                const payloads = await consultarBloqueConSegmentacion(
                                    tipo,
                                    fechaDesde,
                                    fechaHasta,
                                    ventana.offset,
                                    ventana.limit,
                                    function (_tipo, estadoBloque) {
                                        actualizarProgresoGlobal(
                                            tipo,
                                            cuentasCompletadas + cuentasProcesadasBloque,
                                            totalCuentasProceso,
                                            estadoBloque || `Procesando sub-bloque ${ventanaIndex + 1} de ${ventanasCuentas.length}`,
                                            cuentasBloque,
                                            rangoCuentas
                                        );
                                    }
                                );

                                payloads.forEach(function (payload) {
                                    periodoTexto = payload?.periodo_texto || periodoTexto;
                                    columnaPeriodo = payload?.columnas?.periodo || columnaPeriodo;
                                    columnaCredito = payload?.columnas?.credito || columnaCredito;
                                    acumularResumen(resumenAcumulado, payload?.resumen || {});
                                    acumularGruposPorCuenta(gruposMap, Array.isArray(payload?.grupos) ? payload.grupos : []);
                                });

                                cuentasProcesadasBloque += Number(ventana.limit || 0);
                                actualizarProgresoGlobal(
                                    tipo,
                                    cuentasCompletadas + cuentasProcesadasBloque,
                                    totalCuentasProceso,
                                    `Sub-bloque ${ventanaIndex + 1} completado`,
                                    cuentasBloque,
                                    rangoCuentas
                                );
                            }

                            cuentasCompletadas += cuentasProcesadasBloque;
                            actualizarProgresoGlobal(
                                tipo,
                                cuentasCompletadas,
                                totalCuentasProceso,
                                'Bloque completado',
                                cuentasBloque,
                                `${cuentasBloque ? 1 : 0}-${cuentasBloque}`
                            );
                        } catch (errorBloque) {
                            erroresPorBloque.push(`<strong>${tipo}</strong>: ${String(errorBloque?.message || ('No se pudo consultar el bloque ' + tipo + '.'))}`);
                        }
                    }

                    if (typeof Swal !== 'undefined') {
                        Swal.close();
                    }

                    textoPeriodo.textContent = periodoTexto;
                    thPeriodo.textContent = columnaPeriodo;
                    if (thCredito) {
                        thCredito.textContent = columnaCredito;
                    }
                    resumenActivos.textContent = formatoMonto(resumenAcumulado.activos_periodo);
                    resumen10021.textContent = formatoMonto(resumenAcumulado.cuenta_10021_periodo);
                    resumenBalance.textContent = formatoMonto(resumenAcumulado.balance_periodo);
                    actualizarEstadoTarjetaBalance(resumenAcumulado.balance_periodo);

                    const gruposAcumulados = serializarGrupos(gruposMap);

                    if (!gruposAcumulados.length) {
                        if (erroresPorBloque.length) {
                            Swal.fire({
                                title: 'Error',
                                html: erroresPorBloque.join('<br><br>'),
                                icon: 'error'
                            });
                        }
                        vacio.classList.remove('d-none');
                        return;
                    }

                    tbody.innerHTML = gruposAcumulados.map(renderGrupo).join('') + `
                        <tr class="table-primary fw-bold">
                            <td colspan="2">Neto conciliacion (10013 - 10021)</td>
                            <td class="text-end">${formatoMonto(resumenAcumulado.activos_periodo)}</td>
                            <td class="text-end">${formatoMonto(resumenAcumulado.cuenta_10021_periodo)}</td>
                            <td class="text-end">${formatoMonto(resumenAcumulado.balance_periodo)}</td>
                        </tr>
                        <tr class="table-info fw-semibold">
                            <td colspan="2">Depositos a Bancos</td>
                            <td class="text-end">-</td>
                            <td class="text-end">${formatoMonto(resumenAcumulado.cuenta_10021_periodo)}</td>
                            <td class="text-end">${formatoMonto(resumenAcumulado.cuenta_10021_acumulado)}</td>
                        </tr>
                    `;
                    contenido.classList.remove('d-none');

                    if (erroresPorBloque.length) {
                        Swal.fire({
                            title: 'Consulta parcial',
                            html: 'Se cargaron los bloques disponibles.<br><br>' + erroresPorBloque.join('<br><br>'),
                            icon: 'warning'
                        });
                    }
                } catch (error) {
                    if (typeof Swal !== 'undefined') {
                        Swal.close();
                    }
                    Swal.fire('Error', error.message || 'No se pudo cargar el reporte.', 'error');
                } finally {
                    loader.classList.add('d-none');
                    btnConsultar.disabled = false;
                }
            }

            if (form) {
                form.addEventListener('submit', function (event) {
                    event.preventDefault();
                    cargarEstadoResultado();
                });
            }

            if (btnGenerarPdf) {
                btnGenerarPdf.addEventListener('click', function () {
                    generarPdfEstadoResultado();
                });
            }
        });
    </script>
@endsection
