@extends('app')

@section('content')
    @php
        $estadoAgencias = [];

        foreach (($reporte ?? collect()) as $itemReporte) {
            $agenciaId = (string) ($itemReporte->agencia_id ?? '');
            $metaItem = (float) ($itemReporte->meta_incremental ?? 0);
            $ventaPosteriorItem = (float) ($itemReporte->total_venta_mes_posterior ?? 0);
            $cumpleItem = $metaItem <= 0 || $ventaPosteriorItem >= $metaItem;

            if (!array_key_exists($agenciaId, $estadoAgencias)) {
                $estadoAgencias[$agenciaId] = true;
            }

            if (!$cumpleItem) {
                $estadoAgencias[$agenciaId] = false;
            }
        }

        $agenciasCumplen = count(array_filter($estadoAgencias, fn($estado) => $estado === true));
        $agenciasNoCumplen = count(array_filter($estadoAgencias, fn($estado) => $estado === false));
        $totalAgenciasEvaluadas = $agenciasCumplen + $agenciasNoCumplen;
        $porcentajeGlobalCumplimientoAgencias = $totalAgenciasEvaluadas > 0
            ? ($agenciasCumplen / $totalAgenciasEvaluadas) * 100
            : 0;

        $metaGlobalTotal = collect($reporte ?? [])->sum(function ($item) {
            return (float) ($item->meta_incremental ?? 0);
        });

        $ventaGlobalPosteriorTotal = collect($reporte ?? [])->sum(function ($item) {
            return (float) ($item->total_venta_mes_posterior ?? 0);
        });

        if ($metaGlobalTotal <= 0) {
            $porcentajeGlobalMeta = 100;
        } else {
            $porcentajeGlobalMeta = ($ventaGlobalPosteriorTotal / $metaGlobalTotal) * 100;
        }

        $mesPosteriorNombre = \Carbon\Carbon::create((int) $anio, (int) $mes, 1)
            ->addMonth()
            ->locale('es')
            ->translatedFormat('F');
        $etiquetaMesPosterior = 'Ventas de ' . ucfirst($mesPosteriorNombre);
    @endphp

    <div class="main-content">
        <div class="page-content">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-12">
                        <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                            <h4 class="mb-sm-0">Meta Incentivo</h4>
                            <div class="page-title-right">
                                <ol class="breadcrumb m-0">
                                    <li class="breadcrumb-item"><a href="{{ route('inicio.index') }}">Inicio</a></li>
                                    <li class="breadcrumb-item"><a href="{{ route('comercial.index') }}">Comercial</a></li>
                                    <li class="breadcrumb-item active">Meta Incentivo</li>
                                </ol>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-body">
                                <form method="GET" action="{{ route('comercial.meta-incentivo') }}" class="row g-2 align-items-end" id="form-filtro-meta-incentivo">
                                    <input type="hidden" name="aplicar" value="1">
                                    <input type="hidden" id="filtroBaseAnio" value="{{ $anio }}">
                                    <input type="hidden" id="filtroBaseMes" value="{{ $mes }}">
                                    <input type="hidden" id="filtroBaseSistema" value="{{ $sistema }}">
                                    <div class="col-12 col-md-6 col-xl-2">
                                        <label class="form-label">Año</label>
                                        <input type="number" min="2000" max="2100" name="anio" class="form-control" value="{{ $anio }}" required>
                                    </div>
                                    <div class="col-12 col-md-6 col-xl-2">
                                        <label class="form-label">Mes</label>
                                        <select name="mes" class="form-select" required>
                                            @for($m = 1; $m <= 12; $m++)
                                                <option value="{{ $m }}" {{ (int) $mes === $m ? 'selected' : '' }}>
                                                    {{ str_pad((string) $m, 2, '0', STR_PAD_LEFT) }}
                                                </option>
                                            @endfor
                                        </select>
                                    </div>
                                    <div class="col-12 col-md-6 col-xl-2">
                                        <label class="form-label">Sistema</label>
                                        <select name="sistema" class="form-select">
                                            <option value="">Todos</option>
                                            @foreach($sistemas as $itemSistema)
                                                <option value="{{ $itemSistema }}" {{ $sistema === $itemSistema ? 'selected' : '' }}>{{ $itemSistema }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-12 col-md-6 col-xl-2">
                                        <label class="form-label">Coordinador</label>
                                        <select name="coordinador" class="form-select">
                                            <option value="">Todos</option>
                                            @foreach(($coordinadores ?? []) as $itemCoordinador)
                                                <option value="{{ $itemCoordinador }}" {{ ($coordinador ?? '') === $itemCoordinador ? 'selected' : '' }}>{{ $itemCoordinador }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-12 col-md-6 col-xl-2 d-grid">
                                        <button type="submit" class="btn btn-primary w-100" id="btn-filtrar-meta-incentivo">
                                            <i class="ri-filter-3-line me-1"></i>Filtrar
                                        </button>
                                    </div>
                                </form>
                                <div id="aviso-filtro-local" class="alert alert-info py-2 mt-3 mb-0 d-none" role="alert">
                                    <i class="ri-flashlight-line me-1"></i>
                                    <strong>Filtro local aplicado:</strong> coordinador filtrado sin recargar la data.
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-12">
                        @if($errors->any())
                            <div class="alert alert-danger py-2 mb-3" role="alert">
                                {{ $errors->first() }}
                            </div>
                        @endif
                        @if(session('success'))
                            <div class="alert alert-success py-2 mb-3" role="alert">
                                {{ session('success') }}
                            </div>
                        @endif
                        @if(session('error'))
                            <div class="alert alert-danger py-2 mb-3" role="alert">
                                {{ session('error') }}
                            </div>
                        @endif
                        @if($filtrosAplicados ?? false)
                            <div class="alert alert-info py-2 mb-3" role="alert">
                                <strong>Rango aplicado (3 meses):</strong> {{ $fechaInicio }} al {{ $fechaFin }}
                            </div>
                            @if(!empty($coordinador))
                                <div class="alert alert-primary d-flex align-items-center py-2 mb-3" role="alert">
                                    <div class="form-check form-switch mb-0 me-2">
                                        <input class="form-check-input" type="checkbox" role="switch" checked disabled>
                                    </div>
                                    <div>
                                        <strong>Filtro por coordinador activo:</strong> {{ $coordinador }}
                                    </div>
                                </div>
                            @endif
                        @else
                            <div class="alert alert-warning py-2 mb-3" role="alert">
                                <strong>Sin resultados cargados:</strong> selecciona los filtros y presiona <strong>Filtrar</strong> para consultar la información.
                            </div>
                        @endif
                    </div>

                    <div class="col-12">
                        <div class="row g-2 mb-4">
                            <div class="col-12 col-md-6 col-xl-3">
                                <div class="card border border-success-subtle mb-0">
                                    <div class="card-body py-2 px-3">
                                        <small class="text-muted d-block">Agencias que cumplen</small>
                                        <h6 class="mb-0 text-success" id="resumenAgenciasCumplen">{{ number_format($agenciasCumplen, 0) }}</h6>
                                    </div>
                                </div>
                            </div>
                            <div class="col-12 col-md-6 col-xl-3">
                                <div class="card border border-danger-subtle mb-0">
                                    <div class="card-body py-2 px-3">
                                        <small class="text-muted d-block">Agencias que no cumplen</small>
                                        <h6 class="mb-0 text-danger" id="resumenAgenciasNoCumplen">{{ number_format($agenciasNoCumplen, 0) }}</h6>
                                    </div>
                                </div>
                            </div>
                            <div class="col-12 col-md-6 col-xl-3">
                                <div class="card border border-warning-subtle mb-0">
                                    <div class="card-body py-2 px-3">
                                        <small class="text-muted d-block">Cumplimiento global meta</small>
                                        <h6 class="mb-0 text-warning" id="resumenCumplimientoMeta">{{ number_format($porcentajeGlobalMeta, 2) }}%</h6>
                                    </div>
                                </div>
                            </div>
                            <div class="col-12 col-md-6 col-xl-3">
                                <div class="card border border-primary-subtle mb-0">
                                    <div class="card-body py-2 px-3">
                                        <small class="text-muted d-block">Cumplimiento por agencias</small>
                                        <h6 class="mb-0 text-primary" id="resumenCumplimientoAgencias">{{ number_format($porcentajeGlobalCumplimientoAgencias, 2) }}%</h6>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-12">
                        <div class="card">
                            <div class="card-header d-flex align-items-center justify-content-between">
                                <div>
                                    <h5 class="card-title mb-0">Proceso del Calculo de Meta</h5>
                                </div>
                                <div class="row g-2 w-100 mt-2 mt-md-0 justify-content-md-end">
                                    <div class="col-12 col-md-6 col-xl-3 d-grid">
                                        <select class="form-select form-select-sm h-100" id="filtro-cumplimiento-meta-incentivo" name="cumplimiento" form="form-filtro-meta-incentivo">
                                            <option value="" {{ ($cumplimiento ?? '') === '' ? 'selected' : '' }}>Todas las agencias</option>
                                            <option value="cumple" {{ ($cumplimiento ?? '') === 'cumple' ? 'selected' : '' }}>Agencias que cumplen</option>
                                            <option value="no-cumple" {{ ($cumplimiento ?? '') === 'no-cumple' ? 'selected' : '' }}>Agencias que no cumplen</option>
                                        </select>
                                    </div>
                                    <div class="col-12 col-md-6 col-xl-3 d-grid">
                                        <input type="text" class="form-control form-control-sm h-100" id="buscar-agencia-meta-incentivo" placeholder="Buscar por nombre o código">
                                    </div>
                                    <div class="col-12 col-md-6 col-xl-3 d-grid">
                                        <span class="badge bg-primary-subtle text-primary d-flex align-items-center justify-content-center w-100">{{ number_format(($reporte ?? collect())->count(), 0) }} registros</span>
                                    </div>
                                    <div class="col-12 col-md-6 col-xl-3 d-grid">
                                        @if(($filtrosAplicados ?? false) && ($reporte ?? collect())->count() > 0)
                                            <a href="{{ route('comercial.meta-incentivo.export', ['anio' => $anio, 'mes' => $mes, 'sistema' => $sistema, 'coordinador' => ($coordinador ?? ''), 'cumplimiento' => ($cumplimiento ?? '')]) }}" class="btn btn-success btn-sm w-100">
                                                <i class="ri-file-excel-2-line me-1"></i>Exportar a Excel
                                            </a>
                                        @else
                                            <button type="button" class="btn btn-success btn-sm w-100" id="btn-exportar-meta-incentivo-bloqueado">
                                                <i class="ri-file-excel-2-line me-1"></i>Exportar a Excel
                                            </button>
                                        @endif
                                    </div>
                                    <div class="col-12 col-md-6 col-xl-3 d-grid">
                                        @if(($filtrosAplicados ?? false) && ($reporte ?? collect())->count() > 0)
                                            <form method="POST" action="{{ route('comercial.meta-incentivo.send-mail') }}" class="w-100" id="form-enviar-mail-meta-incentivo">
                                                @csrf
                                                <input type="hidden" name="anio" value="{{ $anio }}">
                                                <input type="hidden" name="mes" value="{{ $mes }}">
                                                <input type="hidden" name="sistema" value="{{ $sistema }}">
                                                <input type="hidden" name="coordinador" value="{{ $coordinador ?? '' }}" id="input-coordinador-enviar-mail-meta-incentivo">
                                                <input type="hidden" name="cumplimiento" value="{{ $cumplimiento ?? '' }}">
                                                <button type="submit" class="btn btn-primary btn-sm w-100" id="btn-enviar-mail-meta-incentivo">
                                                    <i class="ri-mail-send-line me-1"></i>Enviar por correo
                                                </button>
                                            </form>
                                        @else
                                            <button type="button" class="btn btn-primary btn-sm w-100" id="btn-enviar-mail-meta-incentivo-bloqueado">
                                                <i class="ri-mail-send-line me-1"></i>Enviar por correo
                                            </button>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-bordered table-striped align-middle mb-0" id="table-meta-incentivo">
                                        <thead>
                                            <tr>
                                                <th>Agencia</th>
                                                <th>Coordinador</th>
                                                <th>Tipo</th>
                                                <th>Ventas Trimestral</th>
                                                <th>Promedio Trimestral</th>
                                                <th>Ventas Base</th>
                                                <th>Nivel</th>
                                                <th>Meta Incremetal</th>
                                                <th>Plan Meta</th>
                                                <th>{{ $etiquetaMesPosterior }}</th>
                                                <th>Cumplimiento Meta</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse($reporte as $row)
                                                @php
                                                    $metaIncremental = (float) ($row->meta_incremental ?? 0);
                                                    $ventaPosterior = (float) ($row->total_venta_mes_posterior ?? 0);

                                                    if ($metaIncremental <= 0) {
                                                        $cumpleMeta = true;
                                                        $porcentajeCumplido = 100.0;
                                                        $porcentajeFaltante = 0.0;
                                                    } else {
                                                        $porcentajeCumplido = min(100, ($ventaPosterior / $metaIncremental) * 100);
                                                        $porcentajeFaltante = max(0, 100 - $porcentajeCumplido);
                                                        $cumpleMeta = $ventaPosterior >= $metaIncremental;
                                                    }
                                                @endphp
                                                <tr data-cumplimiento="{{ $cumpleMeta ? 'cumple' : 'no-cumple' }}" data-agencia-id="{{ $row->agencia_id }}" data-meta="{{ (float) ($row->meta_incremental ?? 0) }}" data-venta="{{ (float) ($row->total_venta_mes_posterior ?? 0) }}" data-coordinador="{{ strtolower(trim((string) ($row->coordinador ?? ''))) }}">
                                                    <td>
                                                        <div class="fw-medium">{{ $row->nombre_agencia }}</div>
                                                        <small class="text-muted">Código: {{ $row->agencia_id }}</small>
                                                    </td>
                                                    <td>{{ $row->coordinador ?: '-' }}</td>
                                                    <td class="text-capitalize">{{ $row->tipo ?: '-' }}</td>
                                                    <td>RD$ {{ number_format((float) $row->ventas_3_meses, 2) }}</td>
                                                    <td>RD$ {{ number_format((float) $row->promedio_3_meses, 2) }}</td>
                                                    <td>RD$ {{ number_format((float) ($row->ventas_base ?? 0), 2) }}</td>
                                                    <td>{{ $row->nivel ?: '-' }}</td>
                                                    <td>RD$ {{ number_format((float) $row->incremetal, 2) }}</td>
                                                    <td>RD$ {{ number_format((float) $row->meta_incremental, 2) }}</td>
                                                    <td>RD$ {{ number_format((float) $row->total_venta_mes_posterior, 2) }}</td>
                                                    <td>
                                                        @if($cumpleMeta)
                                                            <span class="badge bg-success">Cumple 100%</span>
                                                        @else
                                                            <span class="badge bg-danger">Falta {{ number_format($porcentajeFaltante, 2) }}% para alcanzar el 100%</span>
                                                        @endif
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="11" class="text-center text-muted">
                                                        {{ ($filtrosAplicados ?? false) ? 'No hay datos para los filtros seleccionados.' : 'Aplique los filtros y presione Filtrar para cargar la información.' }}
                                                    </td>
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

@section('script')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const formFiltro = document.getElementById('form-filtro-meta-incentivo');
        const btnFiltrar = document.getElementById('btn-filtrar-meta-incentivo');
        const inputBuscarAgencia = document.getElementById('buscar-agencia-meta-incentivo');
        const selectCumplimiento = document.getElementById('filtro-cumplimiento-meta-incentivo');
        const selectCoordinador = formFiltro.querySelector('select[name="coordinador"]');
        const inputAnio = formFiltro.querySelector('input[name="anio"]');
        const selectMes = formFiltro.querySelector('select[name="mes"]');
        const selectSistema = formFiltro.querySelector('select[name="sistema"]');
        const filtroBaseAnio = document.getElementById('filtroBaseAnio');
        const filtroBaseMes = document.getElementById('filtroBaseMes');
        const filtroBaseSistema = document.getElementById('filtroBaseSistema');
        const avisoFiltroLocal = document.getElementById('aviso-filtro-local');
        const resumenAgenciasCumplen = document.getElementById('resumenAgenciasCumplen');
        const resumenAgenciasNoCumplen = document.getElementById('resumenAgenciasNoCumplen');
        const resumenCumplimientoMeta = document.getElementById('resumenCumplimientoMeta');
        const resumenCumplimientoAgencias = document.getElementById('resumenCumplimientoAgencias');
        const formEnviarCorreo = document.getElementById('form-enviar-mail-meta-incentivo');
        const btnEnviarCorreo = document.getElementById('btn-enviar-mail-meta-incentivo');
        const inputCoordinadorEnviarCorreo = document.getElementById('input-coordinador-enviar-mail-meta-incentivo');
        const btnExportarBloqueado = document.getElementById('btn-exportar-meta-incentivo-bloqueado');
        const btnEnviarBloqueado = document.getElementById('btn-enviar-mail-meta-incentivo-bloqueado');
        let dt = null;

        if (!formFiltro || !btnFiltrar) return;

        const filtroCoordinadorEnMemoria = function (settings, data, dataIndex) {
            if (!dt || !settings || !settings.nTable || settings.nTable.id !== 'table-meta-incentivo') {
                return true;
            }

            const valorCoordinador = (selectCoordinador && selectCoordinador.value ? selectCoordinador.value : '').toLowerCase().trim();
            if (!valorCoordinador) {
                return true;
            }

            const fila = dt.row(dataIndex).node();
            if (!fila) {
                return true;
            }

            const coordinadorFila = (fila.getAttribute('data-coordinador') || '').toLowerCase().trim();
            return coordinadorFila.includes(valorCoordinador);
        };

        function actualizarTarjetasResumen() {
            if (!dt) {
                return;
            }

            const filas = dt.rows({ search: 'applied' }).nodes().toArray();
            const estadoAgencias = {};
            let metaGlobalTotal = 0;
            let ventaGlobalTotal = 0;

            filas.forEach(function (fila) {
                const agenciaId = (fila.getAttribute('data-agencia-id') || '').trim();
                if (!agenciaId) {
                    return;
                }

                const cumple = (fila.getAttribute('data-cumplimiento') || '') === 'cumple';
                const meta = parseFloat(fila.getAttribute('data-meta') || '0') || 0;
                const venta = parseFloat(fila.getAttribute('data-venta') || '0') || 0;

                if (!(agenciaId in estadoAgencias)) {
                    estadoAgencias[agenciaId] = true;
                }

                if (!cumple) {
                    estadoAgencias[agenciaId] = false;
                }

                metaGlobalTotal += meta;
                ventaGlobalTotal += venta;
            });

            const agenciasCumplen = Object.values(estadoAgencias).filter(Boolean).length;
            const agenciasNoCumplen = Object.values(estadoAgencias).filter(function (estado) { return !estado; }).length;
            const totalAgencias = agenciasCumplen + agenciasNoCumplen;

            const porcentajeGlobalMeta = metaGlobalTotal <= 0 ? 100 : (ventaGlobalTotal / metaGlobalTotal) * 100;
            const porcentajeGlobalAgencias = totalAgencias > 0 ? (agenciasCumplen / totalAgencias) * 100 : 0;

            if (resumenAgenciasCumplen) {
                resumenAgenciasCumplen.textContent = agenciasCumplen.toLocaleString('es-DO');
            }
            if (resumenAgenciasNoCumplen) {
                resumenAgenciasNoCumplen.textContent = agenciasNoCumplen.toLocaleString('es-DO');
            }
            if (resumenCumplimientoMeta) {
                resumenCumplimientoMeta.textContent = `${porcentajeGlobalMeta.toLocaleString('es-DO', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}%`;
            }
            if (resumenCumplimientoAgencias) {
                resumenCumplimientoAgencias.textContent = `${porcentajeGlobalAgencias.toLocaleString('es-DO', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}%`;
            }
        }

        if (window.$ && $.fn && $.fn.dataTable) {
            $.fn.dataTable.ext.search.push(filtroCoordinadorEnMemoria);
        }

        formFiltro.addEventListener('submit', function (event) {
            const anioSinCambio = String(inputAnio?.value || '') === String(filtroBaseAnio?.value || '');
            const mesSinCambio = String(selectMes?.value || '') === String(filtroBaseMes?.value || '');
            const sistemaSinCambio = String(selectSistema?.value || '') === String(filtroBaseSistema?.value || '');
            const soloFiltroEnMemoria = !!dt && anioSinCambio && mesSinCambio && sistemaSinCambio;

            if (soloFiltroEnMemoria) {
                event.preventDefault();
                dt.draw();
                actualizarTarjetasResumen();

                if (avisoFiltroLocal) {
                    avisoFiltroLocal.classList.remove('d-none');
                }
                return;
            }

            if (avisoFiltroLocal) {
                avisoFiltroLocal.classList.add('d-none');
            }

            btnFiltrar.disabled = true;
            const coordinadorSeleccionado = (selectCoordinador && selectCoordinador.value) ? selectCoordinador.value : '';

            const htmlSwitchAlert = coordinadorSeleccionado
                ? `
                    <div class="d-flex align-items-center justify-content-center gap-2">
                        <div class="form-check form-switch mb-0">
                            <input class="form-check-input" type="checkbox" role="switch" checked disabled>
                        </div>
                        <span>Espere mientras aplicamos el filtro por coordinador: <strong>${coordinadorSeleccionado}</strong>.</span>
                    </div>
                `
                : null;

            Swal.fire({
                title: coordinadorSeleccionado ? 'Filtrando por coordinador...' : 'Cargando...',
                text: coordinadorSeleccionado ? undefined : 'Aplicando filtros, por favor espera.',
                html: htmlSwitchAlert,
                allowOutsideClick: false,
                allowEscapeKey: false,
                didOpen: () => Swal.showLoading(),
            });
        });

        function actualizarEstadoBotonEnviarCorreo() {
            if (!btnEnviarCorreo || !selectCoordinador || !inputCoordinadorEnviarCorreo) {
                return;
            }

            const coordinadorSeleccionado = (selectCoordinador.value || '').trim();
            inputCoordinadorEnviarCorreo.value = coordinadorSeleccionado;
        }

        if (selectCoordinador) {
            selectCoordinador.addEventListener('change', actualizarEstadoBotonEnviarCorreo);
            actualizarEstadoBotonEnviarCorreo();
        }

        if (formEnviarCorreo && btnEnviarCorreo) {
            formEnviarCorreo.addEventListener('submit', function (event) {
                event.preventDefault();
                const coordinadorSeleccionado = (selectCoordinador?.value || '').trim();

                if (coordinadorSeleccionado === '') {
                    Swal.fire('Falta seleccionar coordinador', 'Para enviar por correo debes elegir un coordinador en el filtro.', 'warning');
                    return;
                }

                Swal.fire({
                    title: '¿Enviar mini reporte por correo?',
                    text: 'Se enviará al correo del coordinador filtrado.',
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonText: 'Sí, enviar',
                    cancelButtonText: 'Cancelar',
                    reverseButtons: true,
                }).then(function (result) {
                    if (!result.isConfirmed) {
                        return;
                    }

                    btnEnviarCorreo.disabled = true;
                    btnEnviarCorreo.innerHTML = '<i class="ri-loader-4-line ri-spin me-1"></i>Enviando...';

                    Swal.fire({
                        title: 'Enviando correo...',
                        text: 'Esto puede tardar unos segundos.',
                        allowOutsideClick: false,
                        allowEscapeKey: false,
                        didOpen: () => Swal.showLoading(),
                    });

                    $.ajax({
                        url: formEnviarCorreo.action,
                        method: 'POST',
                        data: $(formEnviarCorreo).serialize(),
                        headers: {
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest'
                        },
                        success: function (resp) {
                            const mensaje = resp?.message || 'Mini reporte enviado correctamente.';
                            Swal.fire('Correo enviado', mensaje, 'success');
                        },
                        error: function (xhr) {
                            const mensaje = xhr?.responseJSON?.message || 'No se pudo enviar el correo.';
                            Swal.fire('Error', mensaje, 'error');
                        },
                        complete: function () {
                            btnEnviarCorreo.innerHTML = '<i class="ri-mail-send-line me-1"></i>Enviar por correo';
                            actualizarEstadoBotonEnviarCorreo();
                        }
                    });
                });
            });
        }

        if (btnExportarBloqueado) {
            btnExportarBloqueado.addEventListener('click', function () {
                Swal.fire('Exportación no disponible', 'Primero aplica filtros para cargar datos y luego podrás exportar a Excel.', 'info');
            });
        }

        if (btnEnviarBloqueado) {
            btnEnviarBloqueado.addEventListener('click', function () {
                Swal.fire('Envío no disponible', 'Primero aplica filtros para cargar datos y luego podrás enviar el mini reporte.', 'info');
            });
        }

        if (window.$ && $.fn.DataTable && $('#table-meta-incentivo').length) {
            dt = $('#table-meta-incentivo').DataTable({
                destroy: true,
                responsive: true,
                language: {
                    url: '/json/es-DO.json',
                    search: 'Buscar:',
                    lengthMenu: 'Mostrar _MENU_ registros',
                    info: 'Mostrando _START_ a _END_ de _TOTAL_ registros',
                    paginate: { first: 'Primera', last: 'Última', next: 'Siguiente', previous: 'Anterior' }
                },
                dom: 'lrtip',
                order: [[3, 'desc']],
            });

            const filtroCumplimientoDt = function (settings, data, dataIndex) {
                if (!settings || !settings.nTable || settings.nTable.id !== 'table-meta-incentivo') {
                    return true;
                }

                const valorFiltro = (selectCumplimiento && selectCumplimiento.value) ? selectCumplimiento.value : '';
                if (!valorFiltro) {
                    return true;
                }

                const fila = dt.row(dataIndex).node();
                if (!fila) {
                    return true;
                }

                const estadoCumplimiento = fila.getAttribute('data-cumplimiento') || '';
                return estadoCumplimiento === valorFiltro;
            };

            $.fn.dataTable.ext.search.push(filtroCumplimientoDt);

            if (inputBuscarAgencia) {
                inputBuscarAgencia.addEventListener('input', function () {
                    dt.search(this.value || '').draw();
                });
            }

            if (selectCumplimiento) {
                selectCumplimiento.addEventListener('change', function () {
                    dt.draw();
                    actualizarTarjetasResumen();
                });
            }

            dt.on('draw', function () {
                actualizarTarjetasResumen();
            });

            actualizarTarjetasResumen();
        }
    });
</script>
@endsection

