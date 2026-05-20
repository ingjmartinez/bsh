@extends('app')

@section('content')
    <div class="main-content">
        <div class="page-content">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-12">
                        <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                            <h4 class="mb-sm-0">Vista Gerencial</h4>
                            <div class="page-title-right">
                                <ol class="breadcrumb m-0">
                                    <li class="breadcrumb-item"><a href="{{ route('inicio.index') }}">Inicio</a></li>
                                    <li class="breadcrumb-item"><a href="{{ route('gerencia.index') }}">Gerencia</a></li>
                                    <li class="breadcrumb-item active">Gerencial</li>
                                </ol>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-lg-12">
                        <div class="card">
                            <div class="card-body">
                                <form id="form-filtros-gerencial" class="row g-2 align-items-end">
                                    <div class="col-12 col-md-4 col-lg-2">
                                        <label class="form-label">Ano</label>
                                        <input type="number"
                                            min="2000"
                                            max="2100"
                                            class="form-control"
                                            id="anio"
                                            name="anio"
                                            value="{{ $anioSeleccionado ?? now()->year }}">
                                    </div>
                                    <div class="col-12 col-md-4 col-lg-2">
                                        <label class="form-label">Mes inicial</label>
                                        <select id="mes_inicio" name="mes_inicio" class="form-select">
                                            <option value="">Seleccionar</option>
                                            @for ($mes = 1; $mes <= 12; $mes++)
                                                <option value="{{ $mes }}" {{ (int) ($mesInicioSeleccionado ?? 0) === $mes ? 'selected' : '' }}>
                                                    {{ \Carbon\Carbon::create()->month($mes)->locale('es')->translatedFormat('F') }}
                                                </option>
                                            @endfor
                                        </select>
                                    </div>
                                    <div class="col-12 col-md-4 col-lg-2">
                                        <label class="form-label">Mes final</label>
                                        <select id="mes_fin" name="mes_fin" class="form-select">
                                            <option value="">Seleccionar</option>
                                            @for ($mes = 1; $mes <= 12; $mes++)
                                                <option value="{{ $mes }}" {{ (int) ($mesFinSeleccionado ?? 0) === $mes ? 'selected' : '' }}>
                                                    {{ \Carbon\Carbon::create()->month($mes)->locale('es')->translatedFormat('F') }}
                                                </option>
                                            @endfor
                                        </select>
                                    </div>
                                    <div class="col-12 col-lg-6">
                                        <label class="form-label d-none d-lg-block">Acciones</label>
                                        <div class="d-flex flex-wrap gap-2">
                                            <button type="submit" class="btn btn-primary" id="btn-filtrar-gerencial">
                                                <i class="ri-search-line me-1"></i>Buscar
                                            </button>
                                            <button type="button" class="btn btn-info" data-bs-toggle="modal" data-bs-target="#modal-configuracion-gerencial">
                                                <i class="ri-settings-3-line me-1"></i>Configurar
                                            </button>
                                            <a href="{{ route('gerencia.gerencial') }}" class="btn btn-light">Limpiar</a>
                                            <button type="button" class="btn btn-danger" id="btn-generar-pdf">
                                                <i class="ri-file-pdf-2-line me-1"></i>Generar PDF
                                            </button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-12">
                        <div class="card border-info border-opacity-25">
                            <div class="card-header bg-info-subtle">
                                <h6 class="card-title mb-0">Parametros de Clasificacion (A, B, C, D)</h6>
                            </div>
                            <div class="card-body">
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <div class="p-3 border rounded h-100">
                                            <div class="fw-semibold mb-2">Agencia</div>
                                            <div class="small text-muted mb-2">Monto minimo para entrar en cada categoria:</div>
                                            <div class="d-flex flex-wrap gap-2">
                                                <span class="badge bg-primary-subtle text-primary" id="badge-agencia-a"></span>
                                                <span class="badge bg-success-subtle text-success" id="badge-agencia-b"></span>
                                                <span class="badge bg-warning-subtle text-warning" id="badge-agencia-c"></span>
                                                <span class="badge bg-danger-subtle text-danger" id="badge-agencia-d"></span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="p-3 border rounded h-100">
                                            <div class="fw-semibold mb-2">Agente de venta (Cedula)</div>
                                            <div class="small text-muted mb-2">Monto minimo para entrar en cada categoria:</div>
                                            <div class="d-flex flex-wrap gap-2">
                                                <span class="badge bg-primary-subtle text-primary" id="badge-agente-a"></span>
                                                <span class="badge bg-success-subtle text-success" id="badge-agente-b"></span>
                                                <span class="badge bg-warning-subtle text-warning" id="badge-agente-c"></span>
                                                <span class="badge bg-danger-subtle text-danger" id="badge-agente-d"></span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-12">
                        <div class="card border-primary border-opacity-25">
                            <div class="card-header bg-primary-subtle">
                                <h6 class="card-title mb-0 fs-5">Analisis de Movimiento de Agencias por Categoria</h6>
                            </div>
                            <div class="card-body">
                                <div class="row g-3">
                                    <div class="col-md-6 col-xl-3">
                                        <div class="p-3 border rounded h-100">
                                            <div class="fw-semibold fs-5 mb-2">Categoria A</div>
                                            <div class="small mb-1 text-success">Suben: <span id="cat-a-suben">0</span></div>
                                            <div class="small mb-1 text-muted" id="cat-a-suben-detalle">-</div>
                                            <div class="small mb-1 text-danger">Bajan: <span id="cat-a-bajan">0</span></div>
                                            <div class="small mb-1 text-muted" id="cat-a-bajan-detalle">-</div>
                                            <div class="small text-muted">Sin cambios: <span id="cat-a-igual">0</span></div>
                                        </div>
                                    </div>
                                    <div class="col-md-6 col-xl-3">
                                        <div class="p-3 border rounded h-100">
                                            <div class="fw-semibold fs-5 mb-2">Categoria B</div>
                                            <div class="small mb-1 text-success">Suben: <span id="cat-b-suben">0</span></div>
                                            <div class="small mb-1 text-muted" id="cat-b-suben-detalle">-</div>
                                            <div class="small mb-1 text-danger">Bajan: <span id="cat-b-bajan">0</span></div>
                                            <div class="small mb-1 text-muted" id="cat-b-bajan-detalle">-</div>
                                            <div class="small text-muted">Sin cambios: <span id="cat-b-igual">0</span></div>
                                        </div>
                                    </div>
                                    <div class="col-md-6 col-xl-3">
                                        <div class="p-3 border rounded h-100">
                                            <div class="fw-semibold fs-5 mb-2">Categoria C</div>
                                            <div class="small mb-1 text-success">Suben: <span id="cat-c-suben">0</span></div>
                                            <div class="small mb-1 text-muted" id="cat-c-suben-detalle">-</div>
                                            <div class="small mb-1 text-danger">Bajan: <span id="cat-c-bajan">0</span></div>
                                            <div class="small mb-1 text-muted" id="cat-c-bajan-detalle">-</div>
                                            <div class="small text-muted">Sin cambios: <span id="cat-c-igual">0</span></div>
                                        </div>
                                    </div>
                                    <div class="col-md-6 col-xl-3">
                                        <div class="p-3 border rounded h-100">
                                            <div class="fw-semibold fs-5 mb-2">Categoria D</div>
                                            <div class="small mb-1 text-success">Suben: <span id="cat-d-suben">0</span></div>
                                            <div class="small mb-1 text-muted" id="cat-d-suben-detalle">-</div>
                                            <div class="small mb-1 text-danger">Bajan: <span id="cat-d-bajan">0</span></div>
                                            <div class="small mb-1 text-muted" id="cat-d-bajan-detalle">-</div>
                                            <div class="small text-muted">Sin cambios: <span id="cat-d-igual">0</span></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-12">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="card-title mb-0" id="titulo-comparativa">Clasificacion Gerencial (Selecciona meses para comparar)</h5>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-bordered table-striped align-middle mb-0" id="table-gerencial">
                                        <thead>
                                            <tr>
                                                <th>Tipo Conteo</th>
                                                <th>Clasificacion</th>
                                                <th id="th-mes-inicio">Mes inicial</th>
                                                <th id="th-mes-fin">Mes final</th>
                                                <th>Crecimiento</th>
                                                <th>% Crecimiento</th>
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
    </div>

    <div class="modal fade" id="modal-configuracion-gerencial" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Configurar Parametros de Clasificacion</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-info py-2 mb-3">
                        Solo se manejan las categorias A, B, C y D. Debes ingresar valores descendentes: A > B > C y D es el limite maximo para ventas bajas.
                    </div>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <h6 class="mb-2">Agencia</h6>
                            <div class="mb-2">
                                <label class="form-label">A (minimo)</label>
                                <input type="number" min="1" class="form-control" id="cfg-agencia-a">
                            </div>
                            <div class="mb-2">
                                <label class="form-label">B (minimo)</label>
                                <input type="number" min="1" class="form-control" id="cfg-agencia-b">
                            </div>
                            <div class="mb-2">
                                <label class="form-label">C (minimo)</label>
                                <input type="number" min="1" class="form-control" id="cfg-agencia-c">
                            </div>
                            <div class="mb-0">
                                <label class="form-label">D (maximo)</label>
                                <input type="number" min="1" class="form-control" id="cfg-agencia-d">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <h6 class="mb-2">Agente de venta (Cedula)</h6>
                            <div class="mb-2">
                                <label class="form-label">A (minimo)</label>
                                <input type="number" min="1" class="form-control" id="cfg-agente-a">
                            </div>
                            <div class="mb-2">
                                <label class="form-label">B (minimo)</label>
                                <input type="number" min="1" class="form-control" id="cfg-agente-b">
                            </div>
                            <div class="mb-2">
                                <label class="form-label">C (minimo)</label>
                                <input type="number" min="1" class="form-control" id="cfg-agente-c">
                            </div>
                            <div class="mb-0">
                                <label class="form-label">D (maximo)</label>
                                <input type="number" min="1" class="form-control" id="cfg-agente-d">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-primary" id="btn-guardar-config">Guardar parametros</button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modal-detalle-movimientos" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-scrollable modal-fullscreen-md-down">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modal-detalle-movimientos-titulo">Detalle de movimiento de agencias</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-info py-2 mb-3">
                        <div class="fw-semibold mb-1">Referencia de clasificacion por monto</div>
                        <div class="d-flex flex-wrap gap-2" id="referencia-rangos-categorias"></div>
                    </div>
                    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
                        <div class="text-muted" id="modal-detalle-movimientos-resumen">0 agencias</div>
                        <button type="button" class="btn btn-success btn-sm" id="btn-exportar-detalle-excel">
                            <i class="ri-file-excel-2-line me-1"></i>Descargar Excel
                        </button>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped table-sm align-middle mb-0 w-100" id="table-detalle-movimientos" style="width: 100%;">
                            <thead>
                                <tr>
                                    <th style="width: 160px;">Codigo agencia</th>
                                    <th>Nombre agencia</th>
                                    <th style="width: 140px;">Categoria inicio</th>
                                    <th style="width: 200px;">Rango inicio</th>
                                    <th style="width: 140px;">Categoria fin</th>
                                    <th style="width: 200px;">Rango fin</th>
                                    <th style="width: 170px;">Movimiento</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cerrar</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('script')
    <!-- jsPDF y AutoTable desde CDN -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.7.0/jspdf.plugin.autotable.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/xlsx@0.18.5/dist/xlsx.full.min.js"></script>
@php
    $configuracionInicial = $configuracionClasificacion ?? [
        'agencia' => ['A' => 150000, 'B' => 110000, 'C' => 60001, 'D' => 60000],
        'agente' => ['A' => 150000, 'B' => 110000, 'C' => 60001, 'D' => 60000],
    ];
@endphp
<script>
    document.addEventListener('DOMContentLoaded', function () {
        if (!(window.$ && $.fn.DataTable)) {
            return;
        }

        // Evento para generar PDF con confirmación
        const btnGenerarPDF = document.getElementById('btn-generar-pdf');
        btnGenerarPDF?.addEventListener('click', function () {
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    title: '¿Deseas generar el PDF?',
                    text: 'Se descargará el reporte actual en formato PDF.',
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonText: 'Sí, generar',
                    cancelButtonText: 'Cancelar',
                }).then((result) => {
                    if (result.isConfirmed) {
                        generarPDF();
                    }
                });
            } else {
                generarPDF();
            }
        });

        // NUEVA FUNCIÓN generarPDF()
        function generarPDF() {
            let jsPDFConstructor = null;

            if (window.jspdf && window.jspdf.jsPDF) {
                jsPDFConstructor = window.jspdf.jsPDF;
            } else if (window.jsPDF) {
                jsPDFConstructor = window.jsPDF;
            }

            if (!jsPDFConstructor) {
                Swal.fire('Error', 'jsPDF no está cargado en la página.', 'error');
                return;
            }

            const doc = new jsPDFConstructor({
                orientation: 'portrait',
                unit: 'pt',
                format: 'a4'
            });

            const pageWidth = doc.internal.pageSize.getWidth();
            const pageHeight = doc.internal.pageSize.getHeight();
            const margin = 20;
            const contentWidth = pageWidth - (margin * 2);

            let y = 24;

            // =========================
            // Helpers
            // =========================
            function drawWrappedText(text, x, y, maxWidth, lineHeight = 11) {
                const lines = doc.splitTextToSize(String(text || '-'), maxWidth);
                doc.text(lines, x, y);
                return lines.length * lineHeight;
            }

            function getWrappedTextHeight(text, maxWidth, lineHeight = 11) {
                const lines = doc.splitTextToSize(String(text || '-'), maxWidth);
                return lines.length * lineHeight;
            }

            function getCardHeight(w, bodyLines = [], titleFontSize = 13) {
                let totalHeight = 20 + 18 + 12;
                const bodyWidth = w - 24;

                bodyLines.forEach(line => {
                    const fontSize = line.fontSize || 9;
                    const lineHeight = line.lineHeight || 10;
                    totalHeight += getWrappedTextHeight(line.text, bodyWidth, lineHeight);
                    totalHeight += line.gapAfter ?? 3;
                    totalHeight += Math.max(0, fontSize - 9) * 0.5;
                });

                return Math.max(totalHeight + 12, titleFontSize + 50);
            }

            function ensureVerticalSpace(requiredHeight, resetY = 35) {
                const bottomLimit = pageHeight - 40;
                if ((y + requiredHeight) > bottomLimit) {
                    doc.addPage();
                    y = resetY;
                }
            }

            function drawCard({
                x,
                y,
                w,
                h,
                title,
                titleColor = [0, 0, 0],
                fillColor = [245, 245, 245],
                borderColor = [200, 200, 200],
                bodyLines = []
            }) {
                doc.setFillColor(...fillColor);
                doc.setDrawColor(...borderColor);
                doc.roundedRect(x, y, w, h, 10, 10, 'FD');

                let currentY = y + 20;

                doc.setFontSize(13);
                doc.setTextColor(...titleColor);
                doc.text(title, x + 12, currentY);

                currentY += 18;

                bodyLines.forEach(line => {
                    if (line.type === 'label') {
                        doc.setFontSize(line.fontSize || 9);
                        doc.setTextColor(...(line.color || [100, 100, 100]));
                        const usedHeight = drawWrappedText(line.text, x + 12, currentY, w - 24, line.lineHeight || 10);
                        currentY += usedHeight + (line.gapAfter || 3);
                    } else {
                        doc.setFontSize(line.fontSize || 9);
                        doc.setTextColor(...(line.color || [0, 0, 0]));
                        const usedHeight = drawWrappedText(line.text, x + 12, currentY, w - 24, line.lineHeight || 10);
                        currentY += usedHeight + (line.gapAfter || 3);
                    }
                });
            }

            function safeText(id, fallback = '-') {
                return document.getElementById(id)?.textContent?.trim() || fallback;
            }

            function truncateText(text, maxLen = 46) {
                const value = String(text || '-');
                return value.length > maxLen ? value.substring(0, maxLen - 3) + '...' : value;
            }

            // =========================
            // Obtener periodo analizado
            // =========================
            const inputAnio = document.getElementById('anio');
            const inputMesInicio = document.getElementById('mes_inicio');
            const inputMesFin = document.getElementById('mes_fin');
            const mapMeses = {
                1: 'Enero', 2: 'Febrero', 3: 'Marzo', 4: 'Abril', 5: 'Mayo', 6: 'Junio',
                7: 'Julio', 8: 'Agosto', 9: 'Septiembre', 10: 'Octubre', 11: 'Noviembre', 12: 'Diciembre'
            };
            const anio = inputAnio?.value || '';
            const mesInicio = mapMeses[Number(inputMesInicio?.value)] || '';
            const mesFin = mapMeses[Number(inputMesFin?.value)] || '';
            let periodo = '';
            if (anio && mesInicio && mesFin) {
                periodo = `Periodo analizado: ${mesInicio} vs ${mesFin} de ${anio}`;
            }
            // =========================
            // Título
            // =========================
            doc.setFontSize(22);
            doc.setTextColor(0, 0, 0);
            doc.text('Business Support Hub', margin, y);
            y += 26;
            doc.setFontSize(16);
            doc.text('Vista Gerencial', margin, y);
            y += 22;
            if (periodo) {
                doc.setFontSize(13);
                doc.setTextColor(41, 128, 185);
                doc.text(periodo, margin, y);
                y += 18;
            } else {
                y += 10;
            }
            y += 18;

            // =========================
            // Tarjetas superiores
            // =========================
            const topGap = 12;
            const topCardW = (contentWidth - topGap) / 2;
            const agenciaBodyLines = [
                {
                    type: 'label',
                    text: 'Monto minimo para entrar en cada categoria:',
                    color: [100, 100, 100],
                    fontSize: 10,
                    gapAfter: 7
                },
                {
                    text: `${badgeAgenciaA?.textContent || ''}    ${badgeAgenciaB?.textContent || ''}`,
                    color: [0, 0, 0],
                    fontSize: 11,
                    gapAfter: 6
                },
                {
                    text: `${badgeAgenciaC?.textContent || ''}    ${badgeAgenciaD?.textContent || ''}`,
                    color: [0, 0, 0],
                    fontSize: 11,
                    gapAfter: 0
                }
            ];
            const agenteBodyLines = [
                {
                    type: 'label',
                    text: 'Monto minimo para entrar en cada categoria:',
                    color: [100, 100, 100],
                    fontSize: 10,
                    gapAfter: 7
                },
                {
                    text: `${badgeAgenteA?.textContent || ''}    ${badgeAgenteB?.textContent || ''}`,
                    color: [0, 0, 0],
                    fontSize: 11,
                    gapAfter: 6
                },
                {
                    text: `${badgeAgenteC?.textContent || ''}    ${badgeAgenteD?.textContent || ''}`,
                    color: [0, 0, 0],
                    fontSize: 11,
                    gapAfter: 0
                }
            ];
            const topCardH = Math.min(100, Math.max(
                getCardHeight(topCardW, agenciaBodyLines),
                getCardHeight(topCardW, agenteBodyLines)
            ));

            ensureVerticalSpace(topCardH + 28);

            drawCard({
                x: margin,
                y,
                w: topCardW,
                h: topCardH,
                title: 'Agencia',
                titleColor: [41, 128, 185],
                fillColor: [232, 240, 254],
                borderColor: [41, 128, 185],
                bodyLines: agenciaBodyLines
            });

            drawCard({
                x: margin + topCardW + topGap,
                y,
                w: topCardW,
                h: topCardH,
                title: 'Agente de venta (Cédula)',
                titleColor: [41, 128, 185],
                fillColor: [232, 240, 254],
                borderColor: [41, 128, 185],
                bodyLines: agenteBodyLines
            });

            y += topCardH + 28;

            // =========================
            // Título sección análisis
            // =========================
            ensureVerticalSpace(30);
            doc.setFontSize(17);
            doc.setTextColor(41, 128, 185);
            doc.text('Análisis de Movimiento de Agencias por Categoría', margin, y);

            y += 16;

            // =========================
            // Tarjetas de análisis
            // =========================
            const analisis = [
                {
                    cat: 'A',
                    color: [13, 110, 253],
                    suben: safeText('cat-a-suben', '0'),
                    subenDetalle: safeText('cat-a-suben-detalle', '-'),
                    bajan: safeText('cat-a-bajan', '0'),
                    bajanDetalle: safeText('cat-a-bajan-detalle', '-'),
                    igual: safeText('cat-a-igual', '0')
                },
                {
                    cat: 'B',
                    color: [25, 135, 84],
                    suben: safeText('cat-b-suben', '0'),
                    subenDetalle: safeText('cat-b-suben-detalle', '-'),
                    bajan: safeText('cat-b-bajan', '0'),
                    bajanDetalle: safeText('cat-b-bajan-detalle', '-'),
                    igual: safeText('cat-b-igual', '0')
                },
                {
                    cat: 'C',
                    color: [255, 193, 7],
                    suben: safeText('cat-c-suben', '0'),
                    subenDetalle: safeText('cat-c-suben-detalle', '-'),
                    bajan: safeText('cat-c-bajan', '0'),
                    bajanDetalle: safeText('cat-c-bajan-detalle', '-'),
                    igual: safeText('cat-c-igual', '0')
                },
                {
                    cat: 'D',
                    color: [220, 53, 69],
                    suben: safeText('cat-d-suben', '0'),
                    subenDetalle: safeText('cat-d-suben-detalle', '-'),
                    bajan: safeText('cat-d-bajan', '0'),
                    bajanDetalle: safeText('cat-d-bajan-detalle', '-'),
                    igual: safeText('cat-d-igual', '0')
                }
            ];

            const analisisGap = 16;
            const analisisCardW = (contentWidth - analisisGap) / 2;
            const analisisCardH = 96;
            ensureVerticalSpace((analisisCardH * 2) + analisisGap + 22);

            analisis.forEach((a, i) => {
                const x = margin + ((i % 2) * (analisisCardW + analisisGap));
                const cardY = y + (Math.floor(i / 2) * (analisisCardH + analisisGap));

                drawCard({
                    x,
                    y: cardY,
                    w: analisisCardW,
                    h: analisisCardH,
                    title: `Categoría ${a.cat}`,
                    titleColor: a.color,
                    fillColor: [248, 249, 250],
                    borderColor: a.color,
                    bodyLines: [
                        {
                            text: `Suben: ${a.suben}`,
                            color: [25, 135, 84],
                            fontSize: 8,
                            gapAfter: 1
                        },
                        {
                            text: truncateText(a.subenDetalle, 52),
                            color: [100, 100, 100],
                            fontSize: 7,
                            gapAfter: 2
                        },
                        {
                            text: `Bajan: ${a.bajan}`,
                            color: [220, 53, 69],
                            fontSize: 8,
                            gapAfter: 1
                        },
                        {
                            text: truncateText(a.bajanDetalle, 52),
                            color: [100, 100, 100],
                            fontSize: 7,
                            gapAfter: 2
                        },
                        {
                            text: `Sin cambios: ${a.igual}`,
                            color: [108, 117, 125],
                            fontSize: 8,
                            gapAfter: 0
                        }
                    ]
                });
            });

            y += (analisisCardH * 2) + analisisGap + 22;

            // =========================
            // Tabla
            // =========================
            const table = document.getElementById('table-gerencial');

            if (!table) {
                Swal.fire('Error', 'No se encontró la tabla de datos.', 'error');
                return;
            }

            const headers = Array.from(table.querySelectorAll('thead th')).map(th => th.textContent.trim());
            let rows = [];

            if (dataTable) {
                rows = dataTable.rows({ search: 'applied', order: 'applied' }).data().toArray().map(function (row) {
                    const tipoConteo = String(row?.tipo_conteo || '').toUpperCase() === 'AGENTE'
                        ? 'Agente de venta'
                        : (String(row?.tipo_conteo || '').toUpperCase() === 'AGENCIA' ? 'Agencia' : (row?.tipo_conteo || '-'));

                    const crecimiento = Number(row?.crecimiento || 0).toLocaleString('en-US');
                    const porcCrecimiento = row?.porc_crecimiento === null || row?.porc_crecimiento === undefined
                        ? '-'
                        : (formatoNumero(row?.porc_crecimiento) + '%');

                    return [
                        tipoConteo,
                        row?.clasificacion || '-',
                        Number(row?.conteo_mes_inicio || 0).toLocaleString('en-US'),
                        Number(row?.conteo_mes_fin || 0).toLocaleString('en-US'),
                        crecimiento,
                        porcCrecimiento
                    ];
                });
            }

            if (!rows.length) {
                rows = Array.from(table.querySelectorAll('tbody tr')).map(tr =>
                    Array.from(tr.querySelectorAll('td')).map(td => td.textContent.trim())
                );
            }

            if (!rows.length) {
                doc.setFontSize(11);
                doc.setTextColor(120, 120, 120);
                doc.text('No hay datos disponibles para mostrar en la tabla.', margin, y);
                doc.save('reporte_gerencial.pdf');
                return;
            }

            if (typeof doc.autoTable === 'function') {
                // Obtener hora actual
                const now = new Date();
                const hora = now.toLocaleTimeString('es-ES', { hour: '2-digit', minute: '2-digit', second: '2-digit' });
                const estimatedHeaderHeight = 24;
                const estimatedRowHeight = 18;
                const footerSpace = 30;
                const tableEstimatedHeight = estimatedHeaderHeight + (rows.length * estimatedRowHeight);
                const availableHeightCurrentPage = pageHeight - y - footerSpace;
                let tableStartY = y + 6;

                if (tableEstimatedHeight > availableHeightCurrentPage) {
                    doc.addPage();
                    tableStartY = 22;
                }

                doc.autoTable({
                    head: [headers],
                    body: rows,
                    startY: tableStartY,
                    margin: { left: margin, right: margin, top: 20, bottom: 20 },
                    theme: 'grid',
                    tableWidth: contentWidth,
                    pageBreak: 'auto',
                    rowPageBreak: 'auto',
                    styles: {
                        fontSize: 8.8,
                        cellPadding: 3.2,
                        overflow: 'ellipsize',
                        valign: 'middle',
                        textColor: [40, 40, 40],
                        lineColor: [220, 220, 220],
                        lineWidth: 0.5
                    },
                    headStyles: {
                        fillColor: [41, 128, 185],
                        textColor: [255, 255, 255],
                        fontSize: 9.5,
                        halign: 'center',
                        valign: 'middle'
                    },
                    bodyStyles: {
                        halign: 'left'
                    },
                    alternateRowStyles: {
                        fillColor: [248, 249, 250]
                    },
                    columnStyles: {
                        0: { cellWidth: 120 },
                        1: { cellWidth: 80, halign: 'center' },
                        2: { cellWidth: 80, halign: 'right' },
                        3: { cellWidth: 80, halign: 'right' },
                        4: { cellWidth: 80, halign: 'right' },
                        5: { cellWidth: 95, halign: 'right' }
                    },
                    didDrawPage: function () {
                        doc.setFontSize(10);
                        doc.setTextColor(120, 120, 120);
                        doc.text('Reporte Gerencial', margin, pageHeight - 24);
                        doc.text('Hora de generación: ' + hora, margin, pageHeight - 12);
                    }
                });
            } else {
                doc.setFontSize(9);
                doc.setTextColor(0, 0, 0);
                doc.text(headers.join(' | '), margin, y);
                y += 15;

                rows.forEach(row => {
                    const line = row.join(' | ');
                    const lines = doc.splitTextToSize(line, contentWidth);
                    doc.text(lines, margin, y);
                    y += (lines.length * 11) + 5;
                });
            }

            doc.save('reporte_gerencial.pdf');
        }

        const form = document.getElementById('form-filtros-gerencial');
        const inputAnio = document.getElementById('anio');
        const inputMesInicio = document.getElementById('mes_inicio');
        const inputMesFin = document.getElementById('mes_fin');
        const thMesInicio = document.getElementById('th-mes-inicio');
        const thMesFin = document.getElementById('th-mes-fin');
        const tituloComparativa = document.getElementById('titulo-comparativa');
        const btnGuardarConfig = document.getElementById('btn-guardar-config');
        const modalConfigElement = document.getElementById('modal-configuracion-gerencial');
        let configuracion = @json($configuracionInicial);

        const inputCfgAgenciaA = document.getElementById('cfg-agencia-a');
        const inputCfgAgenciaB = document.getElementById('cfg-agencia-b');
        const inputCfgAgenciaC = document.getElementById('cfg-agencia-c');
        const inputCfgAgenciaD = document.getElementById('cfg-agencia-d');
        const inputCfgAgenteA = document.getElementById('cfg-agente-a');
        const inputCfgAgenteB = document.getElementById('cfg-agente-b');
        const inputCfgAgenteC = document.getElementById('cfg-agente-c');
        const inputCfgAgenteD = document.getElementById('cfg-agente-d');

        const badgeAgenciaA = document.getElementById('badge-agencia-a');
        const badgeAgenciaB = document.getElementById('badge-agencia-b');
        const badgeAgenciaC = document.getElementById('badge-agencia-c');
        const badgeAgenciaD = document.getElementById('badge-agencia-d');
        const badgeAgenteA = document.getElementById('badge-agente-a');
        const badgeAgenteB = document.getElementById('badge-agente-b');
        const badgeAgenteC = document.getElementById('badge-agente-c');
        const badgeAgenteD = document.getElementById('badge-agente-d');
        const analisisRefs = {
            A: {
                suben: document.getElementById('cat-a-suben'),
                bajan: document.getElementById('cat-a-bajan'),
                igual: document.getElementById('cat-a-igual'),
                subenDetalle: document.getElementById('cat-a-suben-detalle'),
                bajanDetalle: document.getElementById('cat-a-bajan-detalle'),
            },
            B: {
                suben: document.getElementById('cat-b-suben'),
                bajan: document.getElementById('cat-b-bajan'),
                igual: document.getElementById('cat-b-igual'),
                subenDetalle: document.getElementById('cat-b-suben-detalle'),
                bajanDetalle: document.getElementById('cat-b-bajan-detalle'),
            },
            C: {
                suben: document.getElementById('cat-c-suben'),
                bajan: document.getElementById('cat-c-bajan'),
                igual: document.getElementById('cat-c-igual'),
                subenDetalle: document.getElementById('cat-c-suben-detalle'),
                bajanDetalle: document.getElementById('cat-c-bajan-detalle'),
            },
            D: {
                suben: document.getElementById('cat-d-suben'),
                bajan: document.getElementById('cat-d-bajan'),
                igual: document.getElementById('cat-d-igual'),
                subenDetalle: document.getElementById('cat-d-suben-detalle'),
                bajanDetalle: document.getElementById('cat-d-bajan-detalle'),
            },
        };
        const modalDetalleMovimientosElement = document.getElementById('modal-detalle-movimientos');
        const modalDetalleMovimientosTitulo = document.getElementById('modal-detalle-movimientos-titulo');
        const modalDetalleMovimientosResumen = document.getElementById('modal-detalle-movimientos-resumen');
        const btnExportarDetalleExcel = document.getElementById('btn-exportar-detalle-excel');
        const referenciaRangosCategorias = document.getElementById('referencia-rangos-categorias');

        let detalleTransicionesAgencias = [];
        let detalleActualModal = [];
        const modalDetalleMovimientos = (window.bootstrap && modalDetalleMovimientosElement)
            ? new bootstrap.Modal(modalDetalleMovimientosElement)
            : null;

        const dataTableDetalleMovimientos = $('#table-detalle-movimientos').DataTable({
            responsive: true,
            scrollX: true,
            autoWidth: false,
            pageLength: 10,
            lengthChange: false,
            searching: false,
            info: true,
            order: [[1, 'asc'], [0, 'asc']],
            data: [],
            columns: [
                { data: 'codigo_agencia', defaultContent: '-', className: 'text-nowrap' },
                {
                    data: 'nombre_agencia',
                    defaultContent: '-',
                    render: function (data) {
                        return renderTextoTruncado(data || '-', 280);
                    }
                },
                { data: 'categoria_inicio', defaultContent: '-', className: 'text-center text-nowrap' },
                {
                    data: 'rango_inicio',
                    defaultContent: '-',
                    render: function (data) {
                        return renderTextoTruncado(data || '-', 210);
                    }
                },
                { data: 'categoria_fin', defaultContent: '-', className: 'text-center text-nowrap' },
                {
                    data: 'rango_fin',
                    defaultContent: '-',
                    render: function (data) {
                        return renderTextoTruncado(data || '-', 210);
                    }
                },
                { data: 'movimiento', defaultContent: '-', className: 'text-nowrap' }
            ],
            columnDefs: [
                { targets: 0, width: '140px', responsivePriority: 2 },
                { targets: 1, width: '280px', responsivePriority: 1 },
                { targets: 2, width: '120px', responsivePriority: 3 },
                { targets: 3, width: '220px', responsivePriority: 5 },
                { targets: 4, width: '120px', responsivePriority: 4 },
                { targets: 5, width: '220px', responsivePriority: 6 },
                { targets: 6, width: '130px', responsivePriority: 2 }
            ],
            language: {
                paginate: {
                    first: 'Primero',
                    last: 'Ultimo',
                    next: 'Siguiente',
                    previous: 'Anterior'
                },
                info: 'Mostrando _START_ a _END_ de _TOTAL_ agencias',
                infoEmpty: 'Mostrando 0 a 0 de 0 agencias',
                emptyTable: 'No hay agencias para este movimiento'
            }
        });

        const mapMeses = {
            1: 'Enero',
            2: 'Febrero',
            3: 'Marzo',
            4: 'Abril',
            5: 'Mayo',
            6: 'Junio',
            7: 'Julio',
            8: 'Agosto',
            9: 'Septiembre',
            10: 'Octubre',
            11: 'Noviembre',
            12: 'Diciembre'
        };

        function validarFiltros() {
            const year = Number(inputAnio?.value || 0);
            if (!Number.isInteger(year) || year < 2000 || year > 2100) {
                Swal.fire('Ano invalido', 'El ano debe estar entre 2000 y 2100.', 'warning');
                return false;
            }

            const mesInicio = Number(inputMesInicio?.value || 0);
            const mesFin = Number(inputMesFin?.value || 0);

            if (!mesInicio || !mesFin) {
                Swal.fire('Meses requeridos', 'Debes elegir mes inicial y mes final.', 'warning');
                return false;
            }

            if (mesInicio === mesFin) {
                Swal.fire('Comparacion invalida', 'El mes inicial y el mes final deben ser diferentes.', 'warning');
                return false;
            }

            return true;
        }

        function formatoNumero(valor) {
            return Number(valor || 0).toLocaleString('en-US', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            });
        }

        function formatoEntero(valor) {
            return Number(valor || 0).toLocaleString('en-US');
        }

        function escapeHtml(texto) {
            return String(texto ?? '')
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#39;');
        }

        function renderTextoTruncado(valor, maxWidthPx) {
            const texto = String(valor ?? '-');
            const limpio = escapeHtml(texto);
            return '<span class="d-inline-block text-truncate align-middle" style="max-width:' + maxWidthPx + 'px;" title="' + limpio + '">' + limpio + '</span>';
        }

        function getRangoCategoria(categoria) {
            const cat = String(categoria || '').toUpperCase();
            const cfg = configuracion.agencia || { A: 0, B: 0, C: 0, D: 0 };

            if (cat === 'A') {
                return '>= ' + formatoEntero(cfg.A);
            }

            if (cat === 'B') {
                return '>= ' + formatoEntero(cfg.B) + ' y < ' + formatoEntero(cfg.A);
            }

            if (cat === 'C') {
                return '>= ' + formatoEntero(cfg.C) + ' y < ' + formatoEntero(cfg.B);
            }

            if (cat === 'D') {
                return '<= ' + formatoEntero(cfg.D);
            }

            return '-';
        }

        function renderReferenciaRangosModal() {
            if (!referenciaRangosCategorias) return;

            referenciaRangosCategorias.innerHTML = '';
            ['A', 'B', 'C', 'D'].forEach(function (cat) {
                const span = document.createElement('span');
                span.className = 'badge bg-light text-dark border';
                span.textContent = cat + ': ' + getRangoCategoria(cat);
                referenciaRangosCategorias.appendChild(span);
            });
        }

        function cargarConfiguracionEnModal() {
            inputCfgAgenciaA.value = configuracion.agencia.A;
            inputCfgAgenciaB.value = configuracion.agencia.B;
            inputCfgAgenciaC.value = configuracion.agencia.C;
            inputCfgAgenciaD.value = configuracion.agencia.D;

            inputCfgAgenteA.value = configuracion.agente.A;
            inputCfgAgenteB.value = configuracion.agente.B;
            inputCfgAgenteC.value = configuracion.agente.C;
            inputCfgAgenteD.value = configuracion.agente.D;
        }

        function actualizarTarjetaParametros() {
            badgeAgenciaA.textContent = 'A >= ' + formatoEntero(configuracion.agencia.A);
            badgeAgenciaB.textContent = 'B >= ' + formatoEntero(configuracion.agencia.B);
            badgeAgenciaC.textContent = 'C >= ' + formatoEntero(configuracion.agencia.C);
            badgeAgenciaD.textContent = 'D <= ' + formatoEntero(configuracion.agencia.D);

            badgeAgenteA.textContent = 'A >= ' + formatoEntero(configuracion.agente.A);
            badgeAgenteB.textContent = 'B >= ' + formatoEntero(configuracion.agente.B);
            badgeAgenteC.textContent = 'C >= ' + formatoEntero(configuracion.agente.C);
            badgeAgenteD.textContent = 'D <= ' + formatoEntero(configuracion.agente.D);
        }

        function obtenerConfiguracionDesdeModal() {
            return {
                agencia: {
                    A: Number(inputCfgAgenciaA.value || 0),
                    B: Number(inputCfgAgenciaB.value || 0),
                    C: Number(inputCfgAgenciaC.value || 0),
                    D: Number(inputCfgAgenciaD.value || 0),
                },
                agente: {
                    A: Number(inputCfgAgenteA.value || 0),
                    B: Number(inputCfgAgenteB.value || 0),
                    C: Number(inputCfgAgenteC.value || 0),
                    D: Number(inputCfgAgenteD.value || 0),
                }
            };
        }

        function configuracionValida(cfg) {
            const agenciaValida = cfg.agencia.A > cfg.agencia.B && cfg.agencia.B > cfg.agencia.C && cfg.agencia.C > cfg.agencia.D && cfg.agencia.D > 0;
            const agenteValida = cfg.agente.A > cfg.agente.B && cfg.agente.B > cfg.agente.C && cfg.agente.C > cfg.agente.D && cfg.agente.D > 0;

            return agenciaValida && agenteValida;
        }

        function resolverTipoMovimiento(categoriaInicio, categoriaFin) {
            const orden = ['A', 'B', 'C', 'D'];
            const inicio = String(categoriaInicio || '').toUpperCase();
            const fin = String(categoriaFin || '').toUpperCase();
            const idxInicio = orden.indexOf(inicio);
            const idxFin = orden.indexOf(fin);

            if (idxInicio === -1 || idxFin === -1) {
                return '';
            }

            if (idxFin < idxInicio) {
                return 'suben';
            }

            if (idxFin > idxInicio) {
                return 'bajan';
            }

            return 'igual';
        }

        function abrirModalDetalleMovimiento(categoria, tipoMovimiento) {
            const categoriaBase = String(categoria || '').toUpperCase();
            const tipoBase = String(tipoMovimiento || '').toLowerCase();

            if (tipoBase !== 'suben' && tipoBase !== 'bajan') {
                return;
            }

            detalleActualModal = detalleTransicionesAgencias
                .filter(function (item) {
                    const catInicio = String(item.categoria_inicio || '').toUpperCase();
                    const mov = resolverTipoMovimiento(item.categoria_inicio, item.categoria_fin);
                    return catInicio === categoriaBase && mov === tipoBase;
                })
                .map(function (item) {
                    return {
                        codigo_agencia: item.codigo_agencia || '-',
                        nombre_agencia: item.nombre_agencia || '-',
                        categoria_inicio: item.categoria_inicio || '-',
                        rango_inicio: getRangoCategoria(item.categoria_inicio),
                        categoria_fin: item.categoria_fin || '-',
                        rango_fin: getRangoCategoria(item.categoria_fin),
                        movimiento: tipoBase === 'suben' ? 'Subio' : (tipoBase === 'bajan' ? 'Bajo' : 'Sin cambios')
                    };
                });

            dataTableDetalleMovimientos.clear();
            dataTableDetalleMovimientos.rows.add(detalleActualModal).draw();

            const tipoTexto = tipoBase === 'suben' ? 'subieron' : (tipoBase === 'bajan' ? 'bajaron' : 'sin cambios');
            if (modalDetalleMovimientosTitulo) {
                modalDetalleMovimientosTitulo.textContent = 'Agencias categoria ' + categoriaBase + ' que ' + tipoTexto;
            }
            if (modalDetalleMovimientosResumen) {
                modalDetalleMovimientosResumen.textContent = detalleActualModal.length + ' agencias encontradas';
            }

            renderReferenciaRangosModal();

            if (modalDetalleMovimientos) {
                modalDetalleMovimientos.show();
            }
        }

        function exportarDetalleActualExcel() {
            if (!detalleActualModal.length) {
                Swal.fire('Sin datos', 'No hay agencias para exportar en este detalle.', 'warning');
                return;
            }

            const filas = detalleActualModal.map(function (item) {
                return {
                    codigo_agencia: item.codigo_agencia,
                    nombre_agencia: item.nombre_agencia,
                    categoria_inicio: item.categoria_inicio,
                    rango_inicio: item.rango_inicio,
                    categoria_fin: item.categoria_fin,
                    rango_fin: item.rango_fin,
                    movimiento: item.movimiento
                };
            });

            if (typeof XLSX !== 'undefined' && XLSX.utils) {
                const ws = XLSX.utils.json_to_sheet(filas);
                const wb = XLSX.utils.book_new();
                XLSX.utils.book_append_sheet(wb, ws, 'Detalle');
                XLSX.writeFile(wb, 'detalle_movimiento_agencias.xlsx');
                return;
            }

            const headers = ['codigo_agencia', 'nombre_agencia', 'categoria_inicio', 'rango_inicio', 'categoria_fin', 'rango_fin', 'movimiento'];
            const csv = [headers.join(',')]
                .concat(filas.map(function (row) {
                    return headers.map(function (key) {
                        const value = String(row[key] ?? '').replace(/"/g, '""');
                        return '"' + value + '"';
                    }).join(',');
                }))
                .join('\n');

            const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
            const url = URL.createObjectURL(blob);
            const link = document.createElement('a');
            link.href = url;
            link.download = 'detalle_movimiento_agencias.csv';
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
            URL.revokeObjectURL(url);
        }

        function conectarClicksAnalisis() {
            ['A', 'B', 'C', 'D'].forEach(function (categoria) {
                const ref = analisisRefs[categoria];
                if (!ref) return;

                ['suben', 'bajan'].forEach(function (tipo) {
                    const nodo = ref[tipo];
                    if (!nodo || nodo.dataset.modalBound === '1') return;

                    nodo.dataset.modalBound = '1';
                    nodo.addEventListener('click', function () {
                        const total = Number(nodo.dataset.total || 0);
                        if (total <= 0) {
                            return;
                        }
                        abrirModalDetalleMovimiento(categoria, tipo);
                    });
                });
            });
        }

        function actualizarTarjetasAnalisisAgencias(transiciones) {
            const orden = ['A', 'B', 'C', 'D'];
            const resumen = {
                A: { suben: 0, bajan: 0, igual: 0, subenDetalle: {}, bajanDetalle: {} },
                B: { suben: 0, bajan: 0, igual: 0, subenDetalle: {}, bajanDetalle: {} },
                C: { suben: 0, bajan: 0, igual: 0, subenDetalle: {}, bajanDetalle: {} },
                D: { suben: 0, bajan: 0, igual: 0, subenDetalle: {}, bajanDetalle: {} },
            };

            (transiciones || []).forEach(function (item) {
                const inicio = String(item.categoria_inicio || '').toUpperCase();
                const fin = String(item.categoria_fin || '').toUpperCase();
                const total = Number(item.total || 0);

                if (!resumen[inicio] || !orden.includes(fin) || total <= 0) {
                    return;
                }

                const idxInicio = orden.indexOf(inicio);
                const idxFin = orden.indexOf(fin);

                if (idxFin < idxInicio) {
                    resumen[inicio].suben += total;
                    resumen[inicio].subenDetalle[fin] = (resumen[inicio].subenDetalle[fin] || 0) + total;
                } else if (idxFin > idxInicio) {
                    resumen[inicio].bajan += total;
                    resumen[inicio].bajanDetalle[fin] = (resumen[inicio].bajanDetalle[fin] || 0) + total;
                } else {
                    resumen[inicio].igual += total;
                }
            });

            function detalleTexto(categoriaOrigen, detalleMap, verbo) {
                const destinos = Object.keys(detalleMap || {}).sort(function (a, b) {
                    return orden.indexOf(a) - orden.indexOf(b);
                });

                if (!destinos.length) {
                    return '-';
                }

                return destinos
                    .map(function (destino, index) {
                        const base = (index === 0)
                            ? ('De ' + categoriaOrigen + ' ' + verbo + ' a ' + destino + ': ')
                            : ('a ' + destino + ': ');
                        return base + detalleMap[destino];
                    })
                    .join(' | ');
            }

            ['A', 'B', 'C', 'D'].forEach(function (cat) {
                const ref = analisisRefs[cat];
                if (!ref) return;
                if (ref.suben) {
                    ref.suben.textContent = String(resumen[cat].suben);
                    ref.suben.dataset.total = String(resumen[cat].suben);
                    ref.suben.style.cursor = resumen[cat].suben > 0 ? 'pointer' : 'default';
                    ref.suben.classList.toggle('text-decoration-underline', resumen[cat].suben > 0);
                }
                if (ref.bajan) {
                    ref.bajan.textContent = String(resumen[cat].bajan);
                    ref.bajan.dataset.total = String(resumen[cat].bajan);
                    ref.bajan.style.cursor = resumen[cat].bajan > 0 ? 'pointer' : 'default';
                    ref.bajan.classList.toggle('text-decoration-underline', resumen[cat].bajan > 0);
                }
                if (ref.igual) {
                    ref.igual.textContent = String(resumen[cat].igual);
                    ref.igual.style.cursor = 'default';
                    ref.igual.classList.remove('text-decoration-underline');
                }
                if (ref.subenDetalle) ref.subenDetalle.textContent = detalleTexto(cat, resumen[cat].subenDetalle, 'subieron');
                if (ref.bajanDetalle) ref.bajanDetalle.textContent = detalleTexto(cat, resumen[cat].bajanDetalle, 'bajaron');
            });
        }

        const dataTable = $('#table-gerencial').DataTable({
            responsive: true,
            pageLength: 25,
            order: [[0, 'asc'], [1, 'asc']],
            data: [],
            columns: [
                {
                    data: 'tipo_conteo',
                    render: function (data) {
                        if (String(data || '').toUpperCase() === 'AGENTE') {
                            return 'Agente de venta';
                        }

                        if (String(data || '').toUpperCase() === 'AGENCIA') {
                            return 'Agencia';
                        }

                        return data || '-';
                    }
                },
                { data: 'clasificacion' },
                {
                    data: 'conteo_mes_inicio',
                    render: function (data) {
                        return Number(data || 0).toLocaleString('en-US');
                    }
                },
                {
                    data: 'conteo_mes_fin',
                    render: function (data) {
                        return Number(data || 0).toLocaleString('en-US');
                    }
                },
                {
                    data: 'crecimiento',
                    render: function (data) {
                        const valor = Number(data || 0);
                        const clase = valor < 0 ? 'text-danger fw-semibold' : 'text-success fw-semibold';
                        return '<span class="' + clase + '">' + Number(valor).toLocaleString('en-US') + '</span>';
                    }
                },
                {
                    data: 'porc_crecimiento',
                    render: function (data) {
                        if (data === null || data === undefined) {
                            return '-';
                        }

                        const valor = Number(data || 0);
                        const clase = valor < 0 ? 'text-danger fw-semibold' : 'text-success fw-semibold';
                        return '<span class="' + clase + '">' + formatoNumero(valor) + '%</span>';
                    }
                }
            ],
            language: {
                search: 'Buscar:',
                lengthMenu: 'Mostrar _MENU_ registros',
                info: 'Mostrando _START_ a _END_ de _TOTAL_ registros',
                infoEmpty: 'Mostrando 0 a 0 de 0 registros',
                emptyTable: 'No hay datos disponibles',
                paginate: {
                    first: 'Primero',
                    last: 'Ultimo',
                    next: 'Siguiente',
                    previous: 'Anterior'
                }
            },
            deferLoading: 0
        });

        function actualizarEtiquetasMeses() {
            const mesInicio = Number(inputMesInicio?.value || 0);
            const mesFin = Number(inputMesFin?.value || 0);
            const nombreInicio = mapMeses[mesInicio] || 'Mes inicial';
            const nombreFin = mapMeses[mesFin] || 'Mes final';

            thMesInicio.textContent = nombreInicio;
            thMesFin.textContent = nombreFin;

            if (tituloComparativa) {
                tituloComparativa.textContent = 'Clasificacion Gerencial (' + nombreInicio + ' vs ' + nombreFin + ')';
            }
        }

        async function cargarDatos() {
            const params = new URLSearchParams({
                anio: inputAnio?.value || '{{ now()->year }}',
                mes_inicio: inputMesInicio?.value || '',
                mes_fin: inputMesFin?.value || '',
                agencia_a: configuracion.agencia.A,
                agencia_b: configuracion.agencia.B,
                agencia_c: configuracion.agencia.C,
                agencia_d: configuracion.agencia.D,
                agente_a: configuracion.agente.A,
                agente_b: configuracion.agente.B,
                agente_c: configuracion.agente.C,
                agente_d: configuracion.agente.D
            });

            const response = await fetch('{{ route('gerencia.gerencial.data') }}?' + params.toString(), {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });

            if (!response.ok) {
                let detalle = '';
                try {
                    detalle = await response.text();
                } catch (_error) {
                    detalle = '';
                }

                throw new Error('No se pudieron cargar los datos (HTTP ' + response.status + '). ' + (detalle ? 'Detalle: ' + detalle.substring(0, 180) : ''));
            }

            const payload = await response.json();
            const filas = Array.isArray(payload?.data) ? payload.data : [];
            const transiciones = Array.isArray(payload?.transiciones_agencias) ? payload.transiciones_agencias : [];
            detalleTransicionesAgencias = Array.isArray(payload?.transiciones_agencias_detalle)
                ? payload.transiciones_agencias_detalle
                : [];
            dataTable.clear();
            dataTable.rows.add(filas).draw();
            actualizarTarjetasAnalisisAgencias(transiciones);
        }

        if (form) {
            form.addEventListener('submit', async function (event) {
                event.preventDefault();
                if (!validarFiltros()) {
                    return;
                }

                actualizarEtiquetasMeses();

                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        title: 'Generando datos...',
                        text: 'Estamos procesando la consulta, por favor espera.',
                        allowOutsideClick: false,
                        allowEscapeKey: false,
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });
                }

                try {
                    await cargarDatos();
                    if (typeof Swal !== 'undefined') {
                        Swal.close();
                    }
                } catch (error) {
                    if (typeof Swal !== 'undefined') {
                        Swal.close();
                    }
                    Swal.fire('Error', error.message || 'Ocurrio un error al cargar los datos.', 'error');
                }
            });
        }

        if (btnGuardarConfig) {
            btnGuardarConfig.addEventListener('click', function () {
                const nuevaConfig = obtenerConfiguracionDesdeModal();
                if (!configuracionValida(nuevaConfig)) {
                    Swal.fire('Parametros invalidos', 'Asegura que A > B > C > D y que todos los valores sean mayores que 0. D se usa como limite maximo para ventas bajas.', 'warning');
                    return;
                }

                configuracion = nuevaConfig;
                actualizarTarjetaParametros();
                renderReferenciaRangosModal();

                if (window.bootstrap && modalConfigElement) {
                    const instanciaModal = bootstrap.Modal.getInstance(modalConfigElement);
                    if (instanciaModal) {
                        instanciaModal.hide();
                    }
                }

                Swal.fire('Configuracion guardada', 'Los nuevos parametros se aplicaran en la proxima consulta.', 'success');
            });
        }

        if (btnExportarDetalleExcel) {
            btnExportarDetalleExcel.addEventListener('click', function () {
                exportarDetalleActualExcel();
            });
        }

        if (modalConfigElement) {
            modalConfigElement.addEventListener('show.bs.modal', function () {
                cargarConfiguracionEnModal();
            });
        }

        if (modalDetalleMovimientosElement) {
            modalDetalleMovimientosElement.addEventListener('shown.bs.modal', function () {
                dataTableDetalleMovimientos.columns.adjust();
                if (dataTableDetalleMovimientos.responsive) {
                    dataTableDetalleMovimientos.responsive.recalc();
                }
                dataTableDetalleMovimientos.draw(false);
            });
        }

        actualizarEtiquetasMeses();
        actualizarTarjetaParametros();
        cargarConfiguracionEnModal();
        renderReferenciaRangosModal();
        conectarClicksAnalisis();
        actualizarTarjetasAnalisisAgencias([]);
    });
</script>
@endsection

