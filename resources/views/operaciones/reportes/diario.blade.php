@extends('app')

@section('content')
    <link rel="stylesheet" href="{{ asset('libs/dropzone/dropzone.css') }}">
    <div class="main-content">
        <div class="page-content">
            <div class="container-fluid">
                <style>
                    #modalCuadreRutas .dropzone {
                        min-height: 140px;
                        padding: 0.75rem;
                    }

                    #modalCuadreRutas .dropzone .dz-message {
                        margin: 0;
                    }

                    #modalCuadreRutas .dropzone-message {
                        min-height: 120px;
                        display: flex;
                        flex-direction: column;
                        align-items: center;
                        justify-content: center;
                        gap: 0.35rem;
                        border: 1px solid #6c757d;
                        color: #1f2937;
                        font-size: 1.1rem;
                        font-weight: 500;
                        line-height: 1.25;
                        text-align: center;
                        padding: 0.5rem;
                    }

                    #modalCuadreRutas .dropzone-message i {
                        font-size: 1.7rem;
                        line-height: 1;
                    }
                </style>
                <div class="row">
                    <div class="col-12">
                        <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                            <h4 class="mb-sm-0">Reporte Diario de Operaciones</h4>
                            <div class="page-title-right">
                                <ol class="breadcrumb m-0">
                                    <li class="breadcrumb-item"><a href="{{ route('inicio.index') }}">Inicio</a></li>
                                    <li class="breadcrumb-item"><a href="{{ route('operaciones.index') }}">Operaciones</a></li>
                                    <li class="breadcrumb-item active">Reporte Diario</li>
                                </ol>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-lg-12">
                        <div class="card">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h5 class="card-title mb-0">Consolidado Diario</h5>
                                <div class="d-flex gap-2">
                                    <a href="{{ route('operaciones.reporte.diario.export.excel', ['fecha' => $fechaFiltro ?? now()->format('Y-m-d')]) }}" class="btn btn-outline-success">
                                        <i class="ri-file-excel-2-line align-bottom me-1"></i>Descargar Excel
                                    </a>
                                    <button type="button" class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#modalBancos">
                                        <i class="ri-bank-line align-bottom me-1"></i>Bancos
                                    </button>
                                    <a href="{{ route('operaciones.reporte.diario.export.pdf', ['fecha' => $fechaFiltro ?? now()->format('Y-m-d')]) }}" class="btn btn-outline-danger">
                                        <i class="ri-file-pdf-2-line align-bottom me-1"></i>Generar PDF
                                    </a>
                                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalCuadreRutas">
                                        <i class="ri-file-list-3-line align-bottom me-1"></i>Cuadre de rutas
                                    </button>
                                    <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#modalEnvioCorreos">
                                        <i class="ri-mail-send-line align-bottom me-1"></i>Envio por correo
                                    </button>
                                </div>
                            </div>
                            <div class="card-body">
                                @if(session('success'))
                                    <div class="alert alert-success">{{ session('success') }}</div>
                                @endif

                                @if(session('error'))
                                    <div class="alert alert-danger">{{ session('error') }}</div>
                                @endif

                                <p class="mb-3">Registra el cuadre diario de cada ruta, guarda el informe y envialo por correo al operador.</p>

                                <form method="GET" action="{{ route('operaciones.reporte.diario') }}" class="row g-2 align-items-end mb-3">
                                    <div class="col-12 col-md-3">
                                        <label class="form-label">Fecha</label>
                                        <input type="date" class="form-control" name="fecha" value="{{ $fechaFiltro ?? now()->format('Y-m-d') }}">
                                    </div>
                                    <div class="col-12 col-md-2">
                                        <button type="submit" class="btn btn-info w-100">
                                            <i class="ri-search-line align-bottom me-1"></i>Filtrar
                                        </button>
                                    </div>
                                </form>

                                @php
                                    $totalProcesado = (float) collect($ultimosReportes ?? [])->sum('procesado');
                                    $totalEntregado = (float) collect($ultimosReportes ?? [])->sum('entregado');
                                    $totalGasto = (float) collect($ultimosReportes ?? [])->sum('gasto');
                                    $totalDiferencia = (float) collect($ultimosReportes ?? [])->sum('diferencia');
                                @endphp

                                <div class="row g-2 mb-3">
                                    <div class="col-12 col-md-6 col-xl-3">
                                        <div class="border rounded p-2 h-100 bg-light">
                                            <div class="small text-muted">Monto Procesado en Agencia</div>
                                            <div class="fs-5 fw-semibold text-end">{{ number_format($totalProcesado, 2) }}</div>
                                        </div>
                                    </div>
                                    <div class="col-12 col-md-6 col-xl-3">
                                        <div class="border rounded p-2 h-100 bg-light">
                                            <div class="small text-muted">Monto Entregado en Banco</div>
                                            <div class="fs-5 fw-semibold text-end">{{ number_format($totalEntregado, 2) }}</div>
                                        </div>
                                    </div>
                                    <div class="col-12 col-md-6 col-xl-3">
                                        <div class="border rounded p-2 h-100 bg-light">
                                            <div class="small text-muted">Gasto</div>
                                            <div class="fs-5 fw-semibold text-end">{{ number_format($totalGasto, 2) }}</div>
                                        </div>
                                    </div>
                                    <div class="col-12 col-md-6 col-xl-3">
                                        <div class="border rounded p-2 h-100 bg-light">
                                            <div class="small text-muted">Diferencia</div>
                                            <div class="fs-5 fw-semibold text-end {{ abs($totalDiferencia) > 0.00001 ? 'text-danger' : 'text-success' }}">{{ number_format($totalDiferencia, 2) }}</div>
                                        </div>
                                    </div>
                                </div>

                                <div class="table-responsive">
                                    <table class="table table-bordered table-striped align-middle mb-0">
                                        <thead class="table-light">
                                            <tr>
                                                <th>Fecha</th>
                                                <th>Serial Ruta</th>
                                                <th>Ruta</th>
                                                <th>Empresa</th>
                                                <th>Operador</th>
                                                <th>Banco</th>
                                                <th class="text-end">Monto Procesado en Agencia</th>
                                                <th class="text-end">Monto Entregado en Banco</th>
                                                <th class="text-end">Gasto</th>
                                                <th class="text-end">Diferencia</th>
                                                <th>Estatus</th>
                                                <th>Accion</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse(($ultimosReportes ?? []) as $item)
                                                @php
                                                    $diferenciaActual = (float) $item->diferencia;
                                                    $esPendiente = abs($diferenciaActual) > 0.00001;
                                                @endphp
                                                <tr>
                                                    <td>{{ optional($item->fecha)->format('d/m/Y') }}</td>
                                                    <td>{{ $item->serial_ruta ?: '-' }}</td>
                                                    <td>{{ $item->ruta->nombre_ruta ?? '-' }}</td>
                                                    <td>{{ $item->ruta->empresa ?? '-' }}</td>
                                                    <td>{{ trim((($item->operador->nombre ?? '') . ' ' . ($item->operador->apellido ?? ''))) ?: '-' }}</td>
                                                    <td>{{ $item->banco_nombre ?: '-' }}</td>
                                                    <td class="text-end">{{ number_format((float) $item->procesado, 2) }}</td>
                                                    <td class="text-end">{{ number_format((float) $item->entregado, 2) }}</td>
                                                    <td class="text-end">{{ number_format((float) ($item->gasto ?? 0), 2) }}</td>
                                                    <td class="text-end {{ abs((float) $item->diferencia) > 0.00001 ? 'text-danger' : 'text-success' }}">{{ number_format((float) $item->diferencia, 2) }}</td>
                                                    <td>
                                                        @if($esPendiente)
                                                            <button
                                                                type="button"
                                                                class="btn btn-sm btn-warning status-pendiente"
                                                                data-bs-toggle="modal"
                                                                data-bs-target="#modalEstadoPendiente"
                                                                data-fecha="{{ optional($item->fecha)->format('d/m/Y') }}"
                                                                data-ruta="{{ $item->ruta->nombre_ruta ?? '-' }}"
                                                                data-operador="{{ trim((($item->operador->nombre ?? '') . ' ' . ($item->operador->apellido ?? ''))) ?: '-' }}"
                                                                data-entregado="{{ number_format((float) $item->entregado, 2) }}"
                                                                data-procesado="{{ number_format((float) $item->procesado, 2) }}"
                                                                data-gasto="{{ number_format((float) ($item->gasto ?? 0), 2) }}"
                                                                data-diferencia="{{ number_format((float) $item->diferencia, 2) }}"
                                                                data-observacion="{{ $item->observacion ?? '' }}"
                                                            >
                                                                Estado Pendiente
                                                            </button>
                                                        @else
                                                            <span class="badge bg-success-subtle text-success">Completada</span>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        <div class="d-flex flex-nowrap align-items-center gap-1">
                                                            <button
                                                                type="button"
                                                                class="btn btn-sm btn-info btn-ver-comprobantes"
                                                                data-bs-toggle="modal"
                                                                data-bs-target="#modalVerComprobantes"
                                                                data-id="{{ $item->id }}"
                                                                data-ruta="{{ $item->ruta->nombre_ruta ?? '-' }}"
                                                                data-operador="{{ trim((($item->operador->nombre ?? '') . ' ' . ($item->operador->apellido ?? ''))) ?: '-' }}"
                                                                data-entregado="{{ number_format((float) $item->entregado, 2, '.', '') }}"
                                                                data-procesado="{{ number_format((float) $item->procesado, 2, '.', '') }}"
                                                                data-gasto="{{ number_format((float) ($item->gasto ?? 0), 2, '.', '') }}"
                                                                data-diferencia="{{ number_format((float) $item->diferencia, 2, '.', '') }}"
                                                                data-comprobantes-url="{{ route('operaciones.reporte.diario.comprobantes', ['reporte_diario_ruta' => $item->id]) }}"
                                                            >
                                                                Ver
                                                            </button>
                                                            <button
                                                                type="button"
                                                                class="btn btn-sm btn-primary btn-editar-gasto"
                                                                data-bs-toggle="modal"
                                                                data-bs-target="#modalEditarGasto"
                                                                data-id="{{ $item->id }}"
                                                                data-fecha="{{ optional($item->fecha)->format('d/m/Y') }}"
                                                                data-ruta="{{ $item->ruta->nombre_ruta ?? '-' }}"
                                                                data-operador="{{ trim((($item->operador->nombre ?? '') . ' ' . ($item->operador->apellido ?? ''))) ?: '-' }}"
                                                                data-banco="{{ $item->banco_nombre ?: '-' }}"
                                                                data-entregado="{{ number_format((float) $item->entregado, 2, '.', '') }}"
                                                                data-procesado="{{ number_format((float) $item->procesado, 2, '.', '') }}"
                                                                data-gasto="{{ number_format((float) ($item->gasto ?? 0), 2, '.', '') }}"
                                                            >
                                                                Editar
                                                            </button>
                                                        </div>
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="12" class="text-center text-muted">No hay informes para la fecha seleccionada.</td>
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

    <div class="modal fade" id="modalBancos" tabindex="-1" aria-labelledby="modalBancosLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <form method="POST" action="{{ route('operaciones.reporte.diario.bancos.guardar') }}">
                    @csrf
                    <input type="hidden" name="fecha" value="{{ $fechaFiltro ?? now()->format('Y-m-d') }}">
                    <div class="modal-header">
                        <h5 class="modal-title" id="modalBancosLabel">Gestion de bancos</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <label class="form-label">Nombre del banco</label>
                        <input
                            type="text"
                            name="nombre_banco"
                            class="form-control"
                            maxlength="150"
                            placeholder="Ej: Banco Popular"
                            value="{{ old('nombre_banco') }}"
                            required
                        >
                        @if($errors->guardarBanco->has('nombre_banco'))
                            <div class="text-danger small mt-1">{{ $errors->guardarBanco->first('nombre_banco') }}</div>
                        @endif

                        <div class="mt-3">
                            <div class="small text-muted mb-2">Bancos guardados:</div>
                            <div class="d-flex flex-wrap gap-2">
                                @forelse(($bancos ?? []) as $banco)
                                    <span class="badge bg-light text-dark border">{{ $banco->nombre }}</span>
                                @empty
                                    <span class="text-muted small">No hay bancos registrados.</span>
                                @endforelse
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                        <button type="submit" class="btn btn-primary">Guardar banco</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @php
        $rutaSeleccionadaModal = collect($rutas ?? [])->first(function ($ruta) {
            return (string) $ruta->id === (string) old('ruta_id');
        });
        $empresaRutaSeleccionada = (string) ($rutaSeleccionadaModal->empresa ?? '');
        $correoRutaSeleccionada = (string) ($rutaSeleccionadaModal->operadorAsignado->correo ?? '');
        $operadorRutaSeleccionadoId = (string) ($rutaSeleccionadaModal->operador_ruta_id ?? ($rutaSeleccionadaModal->operadorAsignado->id ?? ''));
        $operadorRutaSeleccionadoNombre = trim((($rutaSeleccionadaModal->operadorAsignado->nombre ?? '') . ' ' . ($rutaSeleccionadaModal->operadorAsignado->apellido ?? '')));
    @endphp

    <div class="modal fade" id="modalCuadreRutas" tabindex="-1" aria-labelledby="modalCuadreRutasLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <form method="POST" action="{{ route('operaciones.reporte.diario.guardar') }}" id="formCuadreRutas" data-ruta-detalle-template="{{ route('ruta.detalle', ['ruta' => '__ID__']) }}">
                    @csrf
                    <input type="hidden" name="accion" id="accionFormulario" value="guardar">
                    <input type="hidden" name="comprobante_entregado_path" id="comprobante_entregado_path" value="{{ old('comprobante_entregado_path') }}">
                    <input type="hidden" name="comprobante_diferencia_path" id="comprobante_diferencia_path" value="{{ old('comprobante_diferencia_path') }}">
                    <div class="modal-header">
                        <h5 class="modal-title" id="modalCuadreRutasLabel">Cuadre de rutas</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label">Fecha <span class="text-danger">*</span></label>
                                <input type="date" name="fecha" class="form-control" value="{{ old('fecha', now()->format('Y-m-d')) }}" required>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label">Nombre de la ruta <span class="text-danger">*</span></label>
                                <select name="ruta_id" id="ruta_id" class="form-select" required>
                                    <option value="">Seleccione una ruta</option>
                                    @foreach(($rutas ?? []) as $ruta)
                                        <option
                                            value="{{ $ruta->id }}"
                                            data-operador-id="{{ $ruta->operador_ruta_id ?? ($ruta->operadorAsignado->id ?? '') }}"
                                            data-operador-nombre="{{ trim((($ruta->operadorAsignado->nombre ?? '') . ' ' . ($ruta->operadorAsignado->apellido ?? ''))) }}"
                                            data-operador-correo="{{ $ruta->operadorAsignado->correo ?? '' }}"
                                            data-empresa="{{ $ruta->empresa ?? '' }}"
                                            @selected((string) old('ruta_id') === (string) $ruta->id)
                                        >
                                            {{ $ruta->nombre_ruta }}
                                        </option>
                                    @endforeach
                                </select>
                                <div class="form-text">Al seleccionar la ruta se mostrara la empresa asignada.</div>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label">Empresa de la ruta</label>
                                <input type="text" id="empresa_ruta_preview" class="form-control" value="{{ $empresaRutaSeleccionada }}" placeholder="{{ $empresaRutaSeleccionada !== '' ? '' : 'Seleccione una ruta' }}" readonly>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label">Serial Ruta <span class="text-danger">*</span></label>
                                <input
                                    type="text"
                                    name="serial_ruta"
                                    class="form-control"
                                    value="{{ old('serial_ruta') }}"
                                    maxlength="20"
                                    inputmode="numeric"
                                    pattern="[0-9]{1,20}"
                                    oninput="this.value=this.value.replace(/\D/g,'').slice(0,20)"
                                    placeholder="Hasta 20 digitos"
                                    required>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label">Nombre del operador <span class="text-danger">*</span></label>
                                <select name="operador_ruta_id" id="operador_ruta_id" class="form-select" required>
                                    @if($operadorRutaSeleccionadoId !== '' && $operadorRutaSeleccionadoNombre !== '')
                                        <option value="{{ $operadorRutaSeleccionadoId }}" selected>{{ $operadorRutaSeleccionadoNombre }}</option>
                                    @else
                                        <option value="">Seleccione una ruta primero</option>
                                    @endif
                                </select>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label">Correo del operador</label>
                                <input type="text" id="correo_operador_preview" class="form-control" value="{{ $correoRutaSeleccionada }}" placeholder="{{ $correoRutaSeleccionada !== '' ? '' : 'Seleccione una ruta' }}" readonly>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label">Monto Procesado en Agencia <span class="text-danger">*</span></label>
                                <input type="number" min="0" step="0.01" name="procesado" id="procesado" class="form-control" value="{{ old('procesado') }}" required>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label">Monto Entregado en Banco</label>
                                <input type="number" min="0" step="0.01" name="entregado" id="entregado" class="form-control" value="{{ old('entregado') }}">
                            </div>

                            <div class="col-md-4">
                                <label class="form-label">Gasto</label>
                                <input type="number" min="0" step="0.01" name="gasto" id="gasto" class="form-control" value="{{ old('gasto', 0) }}">
                            </div>

                            <div class="col-md-4">
                                <label class="form-label">Diferencia</label>
                                <input type="text" id="diferencia_preview" class="form-control" value="0.00" readonly>
                            </div>

                            <div class="col-md-12">
                                <label class="form-label">Observacion</label>
                                <input type="text" name="observacion" class="form-control" value="{{ old('observacion') }}" maxlength="1000" placeholder="Comentario opcional del cuadre diario">
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Comprobante deposito (Banco)</label>
                                <div id="dropzoneEntregado" class="dropzone border rounded p-3"></div>
                                <img id="previewEntregado" alt="Vista previa comprobante entregado" class="img-fluid rounded border mt-2 d-none" style="max-height: 170px;">
                                @error('comprobante_entregado_path')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Comprobante deposito (Gastos)</label>
                                <div id="dropzoneDiferencia" class="dropzone border rounded p-3"></div>
                                <img id="previewDiferencia" alt="Vista previa comprobante diferencia" class="img-fluid rounded border mt-2 d-none" style="max-height: 170px;">
                                @error('comprobante_diferencia_path')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-info" id="btnGuardarInforme">Guardar informe</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modalEnvioCorreos" tabindex="-1" aria-labelledby="modalEnvioCorreosLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalEnvioCorreosLabel">Envio por correo de informes</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form method="POST" action="{{ route('operaciones.reporte.diario.enviar-todo') }}" class="row g-2 align-items-end mb-3 border rounded p-2 bg-light">
                        @csrf
                        <input type="hidden" name="fecha" value="{{ $fechaFiltro ?? now()->format('Y-m-d') }}">
                        <div class="col-12 col-md-8">
                            <label class="form-label mb-1">Correo destino para envio completo</label>
                            <input type="email" name="correo_destino" class="form-control" placeholder="ejemplo@dominio.com" required>
                            @error('correo_destino')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-12 col-md-4">
                            <button type="submit" class="btn btn-success w-100">
                                <i class="ri-mail-send-line align-bottom me-1"></i>Enviar todo el reporte
                            </button>
                        </div>
                    </form>

                    <p class="text-muted mb-3">Seleccione el informe del dia filtrado que desea enviar por correo al operador.</p>
                    <div class="table-responsive">
                        <table class="table table-sm table-bordered align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Ruta</th>
                                    <th>Operador</th>
                                    <th>Correo</th>
                                    <th class="text-end">Diferencia</th>
                                    <th class="text-center">Enviar</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse(($ultimosReportes ?? []) as $item)
                                    <tr>
                                        <td>{{ $item->ruta->nombre_ruta ?? '-' }}</td>
                                        <td>{{ trim((($item->operador->nombre ?? '') . ' ' . ($item->operador->apellido ?? ''))) ?: '-' }}</td>
                                        <td>{{ $item->correo_destino }}</td>
                                        <td class="text-end {{ abs((float) $item->diferencia) > 0.00001 ? 'text-danger' : 'text-success' }}">{{ number_format((float) $item->diferencia, 2) }}</td>
                                        <td class="text-center">
                                            <form method="POST" action="{{ route('operaciones.reporte.diario.enviar', $item->id) }}">
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn-primary">
                                                    <i class="ri-mail-send-line align-bottom me-1"></i>Enviar
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center text-muted">No hay informes disponibles para envio en la fecha seleccionada.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modalEstadoPendiente" tabindex="-1" aria-labelledby="modalEstadoPendienteLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalEstadoPendienteLabel">Detalle de estado pendiente</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-2"><strong>Fecha:</strong> <span id="detallePendienteFecha">-</span></div>
                    <div class="mb-2"><strong>Ruta:</strong> <span id="detallePendienteRuta">-</span></div>
                    <div class="mb-2"><strong>Operador:</strong> <span id="detallePendienteOperador">-</span></div>
                    <div class="mb-2"><strong>Monto Procesado en Agencia:</strong> <span id="detallePendienteProcesado">0.00</span></div>
                    <div class="mb-2"><strong>Monto Entregado en Banco:</strong> <span id="detallePendienteEntregado">0.00</span></div>
                    <div class="mb-2"><strong>Gasto:</strong> <span id="detallePendienteGasto">0.00</span></div>
                    <div class="mb-2"><strong>Diferencia:</strong> <span id="detallePendienteDiferencia" class="text-danger fw-semibold">0.00</span></div>
                    <div class="mt-3">
                        <strong>Motivo:</strong>
                        <div id="detallePendienteMotivo" class="border rounded p-2 mt-1 bg-light">Pendiente de conciliacion por diferencia detectada.</div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modalVerComprobantes" tabindex="-1" aria-labelledby="modalVerComprobantesLabel" aria-hidden="true" data-upload-template="{{ route('operaciones.reporte.diario.comprobante.upload', ['reporte_diario_ruta' => '__ID__']) }}" data-update-banco-template="{{ route('operaciones.reporte.diario.actualizar-banco', ['reporte_diario_ruta' => '__ID__']) }}" data-update-gasto-template="{{ route('operaciones.reporte.diario.actualizar-gasto', ['reporte_diario_ruta' => '__ID__']) }}">
        <div class="modal-dialog modal-xl modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalVerComprobantesLabel">Ver comprobantes</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <div><strong>Ruta:</strong> <span id="verCompRuta">-</span></div>
                        <div><strong>Operador:</strong> <span id="verCompOperador">-</span></div>
                    </div>
                    <div id="verCompSinArchivos" class="alert alert-warning py-2 px-3 d-none">
                        Este reporte no tiene comprobantes cargados.
                    </div>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="border rounded p-2 h-100">
                                <div class="fw-semibold mb-2">Comprobante deposito (Banco)</div>
                                <div class="mb-2">
                                    <label class="form-label mb-1">Banco</label>
                                    <div class="d-flex align-items-center gap-2">
                                        <select id="verCompBancoSelect" class="form-select form-select-sm" style="max-width: 340px;">
                                            <option value="">Seleccione un banco</option>
                                            @foreach(($bancos ?? []) as $banco)
                                                <option value="{{ $banco->nombre }}">{{ $banco->nombre }}</option>
                                            @endforeach
                                        </select>
                                        <button type="button" id="btnGuardarBancoComprobante" class="btn btn-sm btn-primary">
                                            Guardar banco
                                        </button>
                                    </div>
                                    <div id="verCompBancoEstado" class="small text-muted mt-1">Selecciona y guarda el banco del comprobante.</div>
                                </div>
                                <button type="button" id="btnSubirCompEntregado" class="btn btn-sm btn-outline-primary mb-2">Cargar imagen</button>
                                <input type="file" id="inputSubirCompEntregado" accept=".jpg,.jpeg,.png,.webp,.heic,.heif,image/*" class="d-none">
                                <img id="verCompEntregadoImg" alt="Comprobante entregado" class="img-fluid rounded border d-none" style="max-height: 420px; width: 100%; object-fit: contain;">
                                <a id="verCompEntregadoLink" href="#" target="_blank" rel="noopener" class="btn btn-sm btn-outline-primary mt-2 d-none">Abrir imagen</a>
                                <div id="verCompMontoEntregadoWrap" class="mt-3 d-none">
                                    <label class="form-label mb-1">Digitaste el monto del deposito</label>
                                    <input type="number" min="0" step="0.01" id="verCompMontoEntregado" class="form-control" placeholder="0.00">
                                </div>
                                <div id="verCompEntregadoEmpty" class="text-muted">No tiene comprobante.</div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="border rounded p-2 h-100">
                                <div class="fw-semibold mb-2">Comprobante deposito (Gastos)</div>
                                <button type="button" id="btnSubirCompDiferencia" class="btn btn-sm btn-outline-primary mb-2">Cargar imagen</button>
                                <input type="file" id="inputSubirCompDiferencia" accept=".jpg,.jpeg,.png,.webp,.heic,.heif,image/*" class="d-none">
                                <img id="verCompDiferenciaImg" alt="Comprobante diferencia" class="img-fluid rounded border d-none" style="max-height: 420px; width: 100%; object-fit: contain;">
                                <a id="verCompDiferenciaLink" href="#" target="_blank" rel="noopener" class="btn btn-sm btn-outline-primary mt-2 d-none">Abrir imagen</a>
                                <div id="verCompMontoGastoWrap" class="mt-3 d-none">
                                    <label class="form-label mb-1">Digitaste el monto del gasto</label>
                                    <input type="number" min="0" step="0.01" id="verCompMontoGasto" class="form-control" placeholder="0.00">
                                </div>
                                <div id="verCompDiferenciaEmpty" class="text-muted">No tiene comprobante.</div>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="border rounded p-2 bg-light-subtle">
                                <div class="d-grid d-md-flex justify-content-md-end">
                                        <button type="button" id="btnGuardarMontosComprobante" class="btn btn-primary">Guardar montos</button>
                                </div>
                                <div id="verCompMontoEstado" class="small text-muted mt-2">Carga un comprobante y digita su monto para actualizar el cuadre.</div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modalEditarGasto" tabindex="-1" aria-labelledby="modalEditarGastoLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <form method="POST" id="formEditarGasto" data-action-template="{{ route('operaciones.reporte.diario.actualizar-gasto', ['reporte_diario_ruta' => '__ID__']) }}">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title" id="modalEditarGastoLabel">Procesar Depositos y gastos</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-2"><strong>Fecha:</strong> <span id="editarGastoFecha">-</span></div>
                        <div class="mb-2"><strong>Ruta:</strong> <span id="editarGastoRuta">-</span></div>
                        <div class="mb-2"><strong>Operador:</strong> <span id="editarGastoOperador">-</span></div>
                        <div class="mb-3"><strong>Banco:</strong> <span id="editarGastoBanco">-</span></div>

                        <div class="row g-2">
                            <div class="col-md-6">
                                <label class="form-label">Monto Entregado en Banco</label>
                                <input type="number" min="0" step="0.01" name="entregado" id="editarGastoEntregado" class="form-control">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Monto Procesado en Agencia</label>
                                <input type="text" id="editarGastoProcesado" class="form-control" readonly>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Gasto</label>
                                <input type="number" min="0" step="0.01" name="gasto" id="editarGastoInput" class="form-control" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Diferencia</label>
                                <input type="text" id="editarGastoDiferencia" class="form-control" readonly>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary">Guardar cambios</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@section('script')
<script src="{{ asset('libs/dropzone/dropzone-min.js') }}"></script>
@php
    $rutasPayload = collect($rutas ?? [])->mapWithKeys(function ($ruta) {
        $nombreOperador = trim((($ruta->operadorAsignado->nombre ?? '') . ' ' . ($ruta->operadorAsignado->apellido ?? '')));
        $operadorId = (string) ($ruta->operador_ruta_id ?? ($ruta->operadorAsignado->id ?? ''));

        return [
            (string) $ruta->id => [
                'operador_id' => $operadorId,
                'operador_nombre' => $nombreOperador,
                'operador_correo' => (string) ($ruta->operadorAsignado->correo ?? ''),
                'empresa' => (string) ($ruta->empresa ?? ''),
            ],
        ];
    })->all();
@endphp
<script>
    document.addEventListener('DOMContentLoaded', function () {
        if (typeof Dropzone !== 'undefined') {
            Dropzone.autoDiscover = false;
        }

        const rutaSelect = document.getElementById('ruta_id');
        const operadorSelect = document.getElementById('operador_ruta_id');
        const correoPreview = document.getElementById('correo_operador_preview');
        const empresaRutaPreview = document.getElementById('empresa_ruta_preview');
        const entregadoInput = document.getElementById('entregado');
        const procesadoInput = document.getElementById('procesado');
        const gastoInput = document.getElementById('gasto');
        const diferenciaPreview = document.getElementById('diferencia_preview');
        const accionFormulario = document.getElementById('accionFormulario');
        const btnGuardarInforme = document.getElementById('btnGuardarInforme');
        const comprobanteEntregadoPath = document.getElementById('comprobante_entregado_path');
        const comprobanteDiferenciaPath = document.getElementById('comprobante_diferencia_path');
        const modalCuadreRutas = document.getElementById('modalCuadreRutas');
        const previewEntregado = document.getElementById('previewEntregado');
        const previewDiferencia = document.getElementById('previewDiferencia');
        const formCuadreRutas = document.getElementById('formCuadreRutas');
        const rutaDetalleTemplate = formCuadreRutas?.dataset?.rutaDetalleTemplate || '';
        const detallePendienteFecha = document.getElementById('detallePendienteFecha');
        const detallePendienteRuta = document.getElementById('detallePendienteRuta');
        const detallePendienteOperador = document.getElementById('detallePendienteOperador');
        const detallePendienteProcesado = document.getElementById('detallePendienteProcesado');
        const detallePendienteEntregado = document.getElementById('detallePendienteEntregado');
        const detallePendienteGasto = document.getElementById('detallePendienteGasto');
        const detallePendienteDiferencia = document.getElementById('detallePendienteDiferencia');
        const detallePendienteMotivo = document.getElementById('detallePendienteMotivo');
        const formEditarGasto = document.getElementById('formEditarGasto');
        const editarGastoFecha = document.getElementById('editarGastoFecha');
        const editarGastoRuta = document.getElementById('editarGastoRuta');
        const editarGastoOperador = document.getElementById('editarGastoOperador');
        const editarGastoBanco = document.getElementById('editarGastoBanco');
        const editarGastoEntregado = document.getElementById('editarGastoEntregado');
        const editarGastoProcesado = document.getElementById('editarGastoProcesado');
        const editarGastoInput = document.getElementById('editarGastoInput');
        const editarGastoDiferencia = document.getElementById('editarGastoDiferencia');
        const verCompRuta = document.getElementById('verCompRuta');
        const verCompOperador = document.getElementById('verCompOperador');
        const verCompEntregadoImg = document.getElementById('verCompEntregadoImg');
        const verCompDiferenciaImg = document.getElementById('verCompDiferenciaImg');
        const verCompEntregadoLink = document.getElementById('verCompEntregadoLink');
        const verCompDiferenciaLink = document.getElementById('verCompDiferenciaLink');
        const btnSubirCompEntregado = document.getElementById('btnSubirCompEntregado');
        const btnSubirCompDiferencia = document.getElementById('btnSubirCompDiferencia');
        const inputSubirCompEntregado = document.getElementById('inputSubirCompEntregado');
        const inputSubirCompDiferencia = document.getElementById('inputSubirCompDiferencia');
        const verCompBancoSelect = document.getElementById('verCompBancoSelect');
        const btnGuardarBancoComprobante = document.getElementById('btnGuardarBancoComprobante');
        const verCompBancoEstado = document.getElementById('verCompBancoEstado');
        const modalVerComprobantes = document.getElementById('modalVerComprobantes');
        const verCompEntregadoEmpty = document.getElementById('verCompEntregadoEmpty');
        const verCompDiferenciaEmpty = document.getElementById('verCompDiferenciaEmpty');
        const verCompSinArchivos = document.getElementById('verCompSinArchivos');
        const verCompMontoEntregadoWrap = document.getElementById('verCompMontoEntregadoWrap');
        const verCompMontoEntregado = document.getElementById('verCompMontoEntregado');
        const verCompMontoGastoWrap = document.getElementById('verCompMontoGastoWrap');
        const verCompMontoGasto = document.getElementById('verCompMontoGasto');
        const verCompMontoEstado = document.getElementById('verCompMontoEstado');
        const btnGuardarMontosComprobante = document.getElementById('btnGuardarMontosComprobante');
        let reporteActualComprobantesId = '';
        let botonActualComprobantes = null;
        let procesadoActualComprobantes = 0;
        const rutasData = @json($rutasPayload);

        rutaSelect?.addEventListener('change', poblarOperadorPorRuta);
        modalCuadreRutas?.addEventListener('shown.bs.modal', poblarOperadorPorRuta);
        poblarOperadorPorRuta();

        function csrfToken() {
            const tokenInput = document.querySelector('input[name="_token"]');
            return tokenInput ? tokenInput.value : '';
        }

        const uploadUrl = "{{ route('operaciones.reporte.diario.upload-comprobante') }}";
        let uploadsPendientes = 0;
        let dzEntregado = null;
        let dzDiferencia = null;

        function mostrarPreview(file, imageElement) {
            if (!file || !imageElement) {
                return;
            }
            const reader = new FileReader();
            reader.onload = function (event) {
                imageElement.src = event.target?.result || '';
                imageElement.classList.remove('d-none');
            };
            reader.readAsDataURL(file);
        }

        function ocultarPreview(imageElement) {
            if (!imageElement) {
                return;
            }
            imageElement.src = '';
            imageElement.classList.add('d-none');
        }

        if (typeof Dropzone !== 'undefined') {
        dzEntregado = new Dropzone('#dropzoneEntregado', {
            url: uploadUrl,
            paramName: 'file',
            maxFiles: 1,
            acceptedFiles: '.jpg,.jpeg,.png,.webp,.heic,.heif,image/*',
            addRemoveLinks: true,
            headers: {
                'X-CSRF-TOKEN': csrfToken(),
            },
            dictDefaultMessage: '<div class="dropzone-message"><i class="ri-upload-cloud-2-line"></i><span>Sube el comprobante de deposito (Entregado)</span></div>',
            init: function () {
                this.on('sending', function (file, xhr, formData) {
                    uploadsPendientes += 1;
                    formData.append('tipo', 'entregado');
                });
                this.on('addedfile', function (file) {
                    mostrarPreview(file, previewEntregado);
                });
                this.on('thumbnail', function (file) {
                    if (file) {
                        mostrarPreview(file, previewEntregado);
                    }
                });
                this.on('success', function (file, response) {
                    comprobanteEntregadoPath.value = response.path || '';
                    uploadsPendientes = Math.max(0, uploadsPendientes - 1);
                });
                this.on('error', function (file, message, xhr) {
                    comprobanteEntregadoPath.value = '';
                    ocultarPreview(previewEntregado);
                    uploadsPendientes = Math.max(0, uploadsPendientes - 1);
                    let detalle = 'No se pudo cargar el comprobante entregado.';
                    if (xhr && xhr.responseText) {
                        try {
                            const payload = JSON.parse(xhr.responseText);
                            if (payload?.message) {
                                detalle = payload.message;
                            } else if (payload?.errors?.file?.[0]) {
                                detalle = payload.errors.file[0];
                            }
                        } catch (_e) {
                            if (typeof message === 'string' && message.trim() !== '') {
                                detalle = message;
                            }
                        }
                    } else if (typeof message === 'string' && message.trim() !== '') {
                        detalle = message;
                    }
                    if (typeof Swal !== 'undefined') {
                        Swal.fire('Error al subir', detalle, 'error');
                    }
                });
                this.on('maxfilesexceeded', function (file) {
                    this.removeAllFiles();
                    this.addFile(file);
                });
                this.on('removedfile', function () {
                    comprobanteEntregadoPath.value = '';
                    ocultarPreview(previewEntregado);
                });
            }
        });

        dzDiferencia = new Dropzone('#dropzoneDiferencia', {
            url: uploadUrl,
            paramName: 'file',
            maxFiles: 1,
            acceptedFiles: '.jpg,.jpeg,.png,.webp,.heic,.heif,image/*',
            addRemoveLinks: true,
            headers: {
                'X-CSRF-TOKEN': csrfToken(),
            },
            dictDefaultMessage: '<div class="dropzone-message"><i class="ri-upload-cloud-2-line"></i><span>Sube el comprobante de deposito (Diferencia)</span></div>',
            init: function () {
                this.on('sending', function (file, xhr, formData) {
                    uploadsPendientes += 1;
                    formData.append('tipo', 'diferencia');
                });
                this.on('addedfile', function (file) {
                    mostrarPreview(file, previewDiferencia);
                });
                this.on('thumbnail', function (file) {
                    if (file) {
                        mostrarPreview(file, previewDiferencia);
                    }
                });
                this.on('success', function (file, response) {
                    comprobanteDiferenciaPath.value = response.path || '';
                    uploadsPendientes = Math.max(0, uploadsPendientes - 1);
                });
                this.on('error', function (file, message, xhr) {
                    comprobanteDiferenciaPath.value = '';
                    ocultarPreview(previewDiferencia);
                    uploadsPendientes = Math.max(0, uploadsPendientes - 1);
                    let detalle = 'No se pudo cargar el comprobante diferencia.';
                    if (xhr && xhr.responseText) {
                        try {
                            const payload = JSON.parse(xhr.responseText);
                            if (payload?.message) {
                                detalle = payload.message;
                            } else if (payload?.errors?.file?.[0]) {
                                detalle = payload.errors.file[0];
                            }
                        } catch (_e) {
                            if (typeof message === 'string' && message.trim() !== '') {
                                detalle = message;
                            }
                        }
                    } else if (typeof message === 'string' && message.trim() !== '') {
                        detalle = message;
                    }
                    if (typeof Swal !== 'undefined') {
                        Swal.fire('Error al subir', detalle, 'error');
                    }
                });
                this.on('maxfilesexceeded', function (file) {
                    this.removeAllFiles();
                    this.addFile(file);
                });
                this.on('removedfile', function () {
                    comprobanteDiferenciaPath.value = '';
                    ocultarPreview(previewDiferencia);
                });
            }
        });
        }

        async function obtenerDetalleRuta(rutaId) {
            if (!rutaId || !rutaDetalleTemplate || rutaDetalleTemplate.indexOf('__ID__') === -1) {
                return null;
            }

            const detalleUrl = rutaDetalleTemplate.replace('__ID__', rutaId);

            try {
                const response = await fetch(detalleUrl, {
                    method: 'GET',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json',
                    },
                });

                if (!response.ok) {
                    return null;
                }

                return await response.json();
            } catch (_error) {
                return null;
            }
        }

        async function poblarOperadorPorRuta() {
            if (!rutaSelect || !operadorSelect) {
                return;
            }

            const rutaId = rutaSelect.value || '';
            if (!rutaId) {
                operadorSelect.innerHTML = '';
                const optionVacia = document.createElement('option');
                optionVacia.value = '';
                optionVacia.textContent = 'Seleccione una ruta primero';
                operadorSelect.appendChild(optionVacia);
                if (correoPreview) {
                    correoPreview.value = '';
                }
                if (empresaRutaPreview) {
                    empresaRutaPreview.value = '';
                    empresaRutaPreview.placeholder = 'Seleccione una ruta';
                }
                return;
            }

            const optionSeleccionada = rutaSelect.options[rutaSelect.selectedIndex] || null;
            const infoRuta = rutasData[rutaId] || null;
            let operadorId = optionSeleccionada?.dataset?.operadorId || infoRuta?.operador_id || '';
            let operadorNombre = optionSeleccionada?.dataset?.operadorNombre || infoRuta?.operador_nombre || '';
            let operadorCorreo = optionSeleccionada?.dataset?.operadorCorreo || infoRuta?.operador_correo || '';
            let empresaRuta = optionSeleccionada?.dataset?.empresa || infoRuta?.empresa || '';

            if (empresaRutaPreview) {
                empresaRutaPreview.value = empresaRuta;
                empresaRutaPreview.placeholder = empresaRuta ? '' : 'Ruta sin empresa asignada';
            }

            if (correoPreview) {
                correoPreview.value = operadorCorreo;
                correoPreview.placeholder = operadorCorreo ? '' : 'Ruta sin correo asignado';
            }

            if (!operadorId || !operadorNombre || !operadorCorreo || !empresaRuta) {
                const detalleRuta = await obtenerDetalleRuta(rutaId);
                if (detalleRuta) {
                    operadorId = detalleRuta.operador_id || operadorId;
                    operadorNombre = detalleRuta.operador_nombre || operadorNombre;
                    operadorCorreo = detalleRuta.operador_correo || operadorCorreo;
                    empresaRuta = detalleRuta.empresa || empresaRuta;

                    rutasData[rutaId] = {
                        operador_id: operadorId,
                        operador_nombre: operadorNombre,
                        operador_correo: operadorCorreo,
                        empresa: empresaRuta,
                    };

                    if (optionSeleccionada?.dataset) {
                        optionSeleccionada.dataset.operadorId = operadorId;
                        optionSeleccionada.dataset.operadorNombre = operadorNombre;
                        optionSeleccionada.dataset.operadorCorreo = operadorCorreo;
                        optionSeleccionada.dataset.empresa = empresaRuta;
                    }
                }
            }

            if (empresaRutaPreview) {
                empresaRutaPreview.value = empresaRuta;
                empresaRutaPreview.placeholder = empresaRuta ? '' : 'Ruta sin empresa asignada';
            }

            operadorSelect.innerHTML = '';

            if (!operadorId || !operadorNombre) {
                const optionVacia = document.createElement('option');
                optionVacia.value = '';
                optionVacia.textContent = 'Seleccione una ruta primero';
                operadorSelect.appendChild(optionVacia);
                if (correoPreview) {
                    correoPreview.value = '';
                }
                return;
            }

            const optionOperador = document.createElement('option');
            optionOperador.value = operadorId;
            optionOperador.textContent = operadorNombre;
            optionOperador.selected = true;
            operadorSelect.appendChild(optionOperador);

            if (correoPreview) {
                correoPreview.value = operadorCorreo;
            }
        }

        function calcularDiferencia() {
            const entregado = parseFloat(entregadoInput?.value || '0') || 0;
            const procesado = parseFloat(procesadoInput?.value || '0') || 0;
            const gasto = parseFloat(gastoInput?.value || '0') || 0;
            const diferencia = procesado - (entregado + gasto);

            if (diferenciaPreview) {
                diferenciaPreview.value = diferencia.toFixed(2);
                diferenciaPreview.classList.remove('text-success', 'text-danger');
                diferenciaPreview.classList.add(Math.abs(diferencia) > 0.00001 ? 'text-danger' : 'text-success');
            }
        }

        entregadoInput?.addEventListener('input', calcularDiferencia);
        procesadoInput?.addEventListener('input', calcularDiferencia);
        gastoInput?.addEventListener('input', calcularDiferencia);
        btnGuardarInforme?.addEventListener('click', function () {
            accionFormulario.value = 'guardar';
        });

        document.querySelectorAll('.status-pendiente').forEach(function (button) {
            button.addEventListener('click', function () {
                detallePendienteFecha.textContent = this.dataset.fecha || '-';
                detallePendienteRuta.textContent = this.dataset.ruta || '-';
                detallePendienteOperador.textContent = this.dataset.operador || '-';
                detallePendienteProcesado.textContent = this.dataset.procesado || '0.00';
                detallePendienteEntregado.textContent = this.dataset.entregado || '0.00';
                detallePendienteGasto.textContent = this.dataset.gasto || '0.00';
                detallePendienteDiferencia.textContent = this.dataset.diferencia || '0.00';

                const motivo = (this.dataset.observacion || '').trim();
                detallePendienteMotivo.textContent = motivo !== ''
                    ? motivo
                    : 'Pendiente de conciliacion por diferencia detectada.';
            });
        });

        function calcularDiferenciaEdicion() {
            const entregado = parseFloat(editarGastoEntregado?.value || '0') || 0;
            const procesado = parseFloat(editarGastoProcesado?.value || '0') || 0;
            const gasto = parseFloat(editarGastoInput?.value || '0') || 0;
            const diferencia = procesado - (entregado + gasto);

            if (editarGastoDiferencia) {
                editarGastoDiferencia.value = diferencia.toFixed(2);
                editarGastoDiferencia.classList.remove('text-success', 'text-danger');
                editarGastoDiferencia.classList.add(Math.abs(diferencia) > 0.00001 ? 'text-danger' : 'text-success');
            }
        }

        editarGastoInput?.addEventListener('input', calcularDiferenciaEdicion);
        editarGastoEntregado?.addEventListener('input', calcularDiferenciaEdicion);

        document.querySelectorAll('.btn-editar-gasto').forEach(function (button) {
            button.addEventListener('click', function () {
                const id = this.dataset.id || '';
                const actionTemplate = formEditarGasto?.dataset?.actionTemplate || '';

                if (formEditarGasto && actionTemplate !== '' && id !== '') {
                    formEditarGasto.action = actionTemplate.replace('__ID__', id);
                }

                if (editarGastoFecha) editarGastoFecha.textContent = this.dataset.fecha || '-';
                if (editarGastoRuta) editarGastoRuta.textContent = this.dataset.ruta || '-';
                if (editarGastoOperador) editarGastoOperador.textContent = this.dataset.operador || '-';
                if (editarGastoBanco) editarGastoBanco.textContent = this.dataset.banco || '-';
                if (editarGastoEntregado) editarGastoEntregado.value = this.dataset.entregado || '0.00';
                if (editarGastoProcesado) editarGastoProcesado.value = this.dataset.procesado || '0.00';
                if (editarGastoInput) editarGastoInput.value = this.dataset.gasto || '0.00';

                calcularDiferenciaEdicion();
            });
        });

        function renderComprobante(url, imageElement, emptyElement, linkElement) {
            if (!imageElement || !emptyElement) {
                return;
            }

            if (url && url.trim() !== '') {
                imageElement.onerror = function () {
                    imageElement.src = '';
                    imageElement.classList.add('d-none');
                    emptyElement.classList.remove('d-none');
                    if (linkElement) {
                        linkElement.classList.add('d-none');
                        linkElement.setAttribute('href', '#');
                    }
                };
                imageElement.src = url;
                imageElement.classList.remove('d-none');
                imageElement.style.cursor = 'zoom-in';
                imageElement.onclick = function () {
                    window.open(url, '_blank', 'noopener');
                };
                emptyElement.classList.add('d-none');
                if (linkElement) {
                    linkElement.setAttribute('href', url);
                    linkElement.classList.remove('d-none');
                }
            } else {
                imageElement.src = '';
                imageElement.classList.add('d-none');
                imageElement.style.cursor = 'default';
                imageElement.onclick = null;
                emptyElement.classList.remove('d-none');
                if (linkElement) {
                    linkElement.classList.add('d-none');
                    linkElement.setAttribute('href', '#');
                }
            }
        }

        function obtenerUploadComprobanteUrl() {
            if (!modalVerComprobantes) {
                return '';
            }
            const template = modalVerComprobantes.dataset.uploadTemplate || '';
            if (!template || !reporteActualComprobantesId) {
                return '';
            }
            return template.replace('__ID__', reporteActualComprobantesId);
        }

        function obtenerActualizarBancoUrl() {
            if (!modalVerComprobantes) {
                return '';
            }
            const template = modalVerComprobantes.dataset.updateBancoTemplate || '';
            if (!template || !reporteActualComprobantesId) {
                return '';
            }
            return template.replace('__ID__', reporteActualComprobantesId);
        }

        function setEstadoBanco(mensaje, tipo = 'muted') {
            if (!verCompBancoEstado) {
                return;
            }

            verCompBancoEstado.textContent = mensaje;
            verCompBancoEstado.classList.remove('text-muted', 'text-success', 'text-danger', 'text-warning');
            if (tipo === 'success') {
                verCompBancoEstado.classList.add('text-success');
            } else if (tipo === 'danger') {
                verCompBancoEstado.classList.add('text-danger');
            } else if (tipo === 'warning') {
                verCompBancoEstado.classList.add('text-warning');
            } else {
                verCompBancoEstado.classList.add('text-muted');
            }
        }

        function setEstadoMontos(mensaje, tipo = 'muted') {
            if (!verCompMontoEstado) {
                return;
            }

            verCompMontoEstado.textContent = mensaje;
            verCompMontoEstado.classList.remove('text-muted', 'text-success', 'text-danger', 'text-warning');
            if (tipo === 'success') {
                verCompMontoEstado.classList.add('text-success');
            } else if (tipo === 'danger') {
                verCompMontoEstado.classList.add('text-danger');
            } else if (tipo === 'warning') {
                verCompMontoEstado.classList.add('text-warning');
            } else {
                verCompMontoEstado.classList.add('text-muted');
            }
        }

        function formatMoney(value) {
            const number = parseFloat(value || '0') || 0;
            return number.toFixed(2);
        }

        function actualizarTotalesConsolidado() {
            const tbody = document.querySelector('table tbody');
            if (!tbody) {
                return;
            }

            let procesadoTotal = 0;
            let entregadoTotal = 0;
            let gastoTotal = 0;
            let diferenciaTotal = 0;

            tbody.querySelectorAll('tr').forEach(function (fila) {
                const celdas = fila.querySelectorAll('td');
                if (celdas.length < 10) {
                    return;
                }

                procesadoTotal += parseFloat((celdas[6].textContent || '0').replace(/,/g, '')) || 0;
                entregadoTotal += parseFloat((celdas[7].textContent || '0').replace(/,/g, '')) || 0;
                gastoTotal += parseFloat((celdas[8].textContent || '0').replace(/,/g, '')) || 0;
                diferenciaTotal += parseFloat((celdas[9].textContent || '0').replace(/,/g, '')) || 0;
            });

            const totalCards = document.querySelectorAll('.row.g-2.mb-3 .fs-5.fw-semibold');
            if (totalCards.length < 4) {
                return;
            }

            totalCards[0].textContent = formatMoney(procesadoTotal);
            totalCards[1].textContent = formatMoney(entregadoTotal);
            totalCards[2].textContent = formatMoney(gastoTotal);
            totalCards[3].textContent = formatMoney(diferenciaTotal);
            totalCards[3].classList.remove('text-success', 'text-danger');
            totalCards[3].classList.add(Math.abs(diferenciaTotal) > 0.00001 ? 'text-danger' : 'text-success');
        }

        function toggleMontoFields() {
            const tieneEntregado = !!(verCompEntregadoImg?.src && !verCompEntregadoImg.classList.contains('d-none'));
            const tieneGasto = !!(verCompDiferenciaImg?.src && !verCompDiferenciaImg.classList.contains('d-none'));

            verCompMontoEntregadoWrap?.classList.toggle('d-none', !tieneEntregado);
            verCompMontoGastoWrap?.classList.toggle('d-none', !tieneGasto);

            if (!tieneEntregado && verCompMontoEntregado) {
                verCompMontoEntregado.value = '0.00';
            }

            if (!tieneGasto && verCompMontoGasto) {
                verCompMontoGasto.value = '0.00';
            }
        }

        function obtenerActualizarGastoUrl() {
            if (!modalVerComprobantes) {
                return '';
            }
            const template = modalVerComprobantes.dataset.updateGastoTemplate || '';
            if (!template || !reporteActualComprobantesId) {
                return '';
            }
            return template.replace('__ID__', reporteActualComprobantesId);
        }

        function actualizarFilaConsolidado(payload) {
            if (!botonActualComprobantes) {
                return;
            }

            const fila = botonActualComprobantes.closest('tr');
            if (!fila) {
                return;
            }

            const celdas = fila.querySelectorAll('td');
            if (celdas.length < 12) {
                return;
            }

            celdas[7].textContent = payload.entregado ?? '0.00';
            celdas[8].textContent = payload.gasto ?? '0.00';
            celdas[9].textContent = payload.diferencia ?? '0.00';
            celdas[9].classList.remove('text-success', 'text-danger');
            celdas[9].classList.add(Math.abs(parseFloat(payload.diferencia || '0')) > 0.00001 ? 'text-danger' : 'text-success');

            const estatusCell = celdas[10];
            if (payload.estatus === 'Completada') {
                estatusCell.innerHTML = '<span class="badge bg-success-subtle text-success">Completada</span>';
            } else {
                const fecha = celdas[0].textContent.trim() || '-';
                const ruta = celdas[2].textContent.trim() || '-';
                const operador = celdas[4].textContent.trim() || '-';
                const botonPendiente = document.createElement('button');
                botonPendiente.type = 'button';
                botonPendiente.className = 'btn btn-sm btn-warning status-pendiente';
                botonPendiente.setAttribute('data-bs-toggle', 'modal');
                botonPendiente.setAttribute('data-bs-target', '#modalEstadoPendiente');
                botonPendiente.dataset.fecha = fecha;
                botonPendiente.dataset.ruta = ruta;
                botonPendiente.dataset.operador = operador;
                botonPendiente.dataset.entregado = payload.entregado ?? '0.00';
                botonPendiente.dataset.procesado = payload.procesado ?? (celdas[6].textContent.trim() || '0.00');
                botonPendiente.dataset.gasto = payload.gasto ?? '0.00';
                botonPendiente.dataset.diferencia = payload.diferencia ?? '0.00';
                botonPendiente.dataset.observacion = '';
                botonPendiente.textContent = 'Estado Pendiente';
                botonPendiente.addEventListener('click', function () {
                    detallePendienteFecha.textContent = this.dataset.fecha || '-';
                    detallePendienteRuta.textContent = this.dataset.ruta || '-';
                    detallePendienteOperador.textContent = this.dataset.operador || '-';
                    detallePendienteProcesado.textContent = this.dataset.procesado || '0.00';
                    detallePendienteEntregado.textContent = this.dataset.entregado || '0.00';
                    detallePendienteGasto.textContent = this.dataset.gasto || '0.00';
                    detallePendienteDiferencia.textContent = this.dataset.diferencia || '0.00';
                    detallePendienteMotivo.textContent = 'Pendiente de conciliacion por diferencia detectada.';
                });
                estatusCell.innerHTML = '';
                estatusCell.appendChild(botonPendiente);
            }

            botonActualComprobantes.dataset.entregado = payload.entregado ?? '0.00';
            botonActualComprobantes.dataset.gasto = payload.gasto ?? '0.00';
            botonActualComprobantes.dataset.diferencia = payload.diferencia ?? '0.00';

            const btnEditar = fila.querySelector('.btn-editar-gasto');
            if (btnEditar) {
                btnEditar.dataset.entregado = payload.entregado ?? '0.00';
                btnEditar.dataset.gasto = payload.gasto ?? '0.00';
            }

            actualizarTotalesConsolidado();
        }

        async function guardarMontosDesdeComprobantes() {
            if (!reporteActualComprobantesId) {
                return;
            }

            const updateUrl = obtenerActualizarGastoUrl();
            if (!updateUrl) {
                return;
            }

            try {
                const entregado = parseFloat(verCompMontoEntregado?.value || '0') || 0;
                const gasto = parseFloat(verCompMontoGasto?.value || '0') || 0;
                const tieneComprobanteEntregado = !verCompMontoEntregadoWrap?.classList.contains('d-none');
                const tieneComprobanteGasto = !verCompMontoGastoWrap?.classList.contains('d-none');

                if (tieneComprobanteEntregado && entregado <= 0) {
                    setEstadoMontos('Digita el monto del deposito del banco para guardar.', 'warning');
                    return;
                }

                if (tieneComprobanteGasto && gasto <= 0) {
                    setEstadoMontos('Digita el monto del gasto para guardar.', 'warning');
                    return;
                }

                if (btnGuardarMontosComprobante) {
                    btnGuardarMontosComprobante.disabled = true;
                }
                setEstadoMontos('Guardando montos del comprobante...', 'muted');

                const response = await fetch(updateUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken(),
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({
                        entregado: entregado,
                        gasto: gasto,
                    }),
                });

                const payload = await response.json();
                if (!response.ok) {
                    throw new Error(payload?.message || payload?.errors?.gasto?.[0] || 'No se pudieron guardar los montos.');
                }

                if (verCompMontoEntregado) {
                    verCompMontoEntregado.value = formatMoney(payload.entregado ?? '0');
                }
                if (verCompMontoGasto) {
                    verCompMontoGasto.value = formatMoney(payload.gasto ?? '0');
                }
                procesadoActualComprobantes = parseFloat(payload.procesado ?? procesadoActualComprobantes ?? 0) || 0;
                actualizarFilaConsolidado(payload);
                setEstadoMontos(payload.message || 'Montos actualizados correctamente.', 'success');
            } catch (error) {
                setEstadoMontos(error.message || 'No se pudieron guardar los montos.', 'danger');
                if (typeof Swal !== 'undefined') {
                    Swal.fire('Error', error.message || 'No se pudieron guardar los montos.', 'error');
                }
            } finally {
                if (btnGuardarMontosComprobante) {
                    btnGuardarMontosComprobante.disabled = false;
                }
            }
        }

        async function subirComprobanteDesdeModal(tipo, file) {
            if (!file || !reporteActualComprobantesId) {
                return;
            }

            const uploadComprobanteUrl = obtenerUploadComprobanteUrl();
            if (!uploadComprobanteUrl) {
                return;
            }

            const formData = new FormData();
            formData.append('file', file);
            formData.append('tipo', tipo);

            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    title: 'Subiendo comprobante...',
                    allowOutsideClick: false,
                    allowEscapeKey: false,
                    didOpen: () => Swal.showLoading(),
                });
            }

            try {
                const response = await fetch(uploadComprobanteUrl, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken(),
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    body: formData,
                });

                const payload = await response.json();
                if (!response.ok) {
                    throw new Error(payload?.message || payload?.errors?.file?.[0] || 'No se pudo subir el comprobante.');
                }

                if (tipo === 'entregado') {
                    renderComprobante(payload.url || '', verCompEntregadoImg, verCompEntregadoEmpty, verCompEntregadoLink);
                } else {
                    renderComprobante(payload.url || '', verCompDiferenciaImg, verCompDiferenciaEmpty, verCompDiferenciaLink);
                }

                toggleMontoFields();

                if (verCompSinArchivos) {
                    verCompSinArchivos.classList.add('d-none');
                }

                if (typeof Swal !== 'undefined') {
                    Swal.fire('Comprobante actualizado', payload?.message || 'La imagen se cargÃ³ correctamente.', 'success');
                }
            } catch (error) {
                if (typeof Swal !== 'undefined') {
                    Swal.fire('Error al subir', error.message || 'No se pudo cargar el comprobante.', 'error');
                }
            }
        }

        document.querySelectorAll('.btn-ver-comprobantes').forEach(function (button) {
            button.addEventListener('click', async function () {
                const comprobantesUrl = this.dataset.comprobantesUrl || '';
                reporteActualComprobantesId = this.dataset.id || '';
                botonActualComprobantes = this;
                procesadoActualComprobantes = parseFloat(this.dataset.procesado || '0') || 0;

                if (verCompRuta) verCompRuta.textContent = this.dataset.ruta || '-';
                if (verCompOperador) verCompOperador.textContent = this.dataset.operador || '-';
                if (verCompMontoEntregado) verCompMontoEntregado.value = this.dataset.entregado || '0.00';
                if (verCompMontoGasto) verCompMontoGasto.value = this.dataset.gasto || '0.00';
                if (verCompBancoSelect) verCompBancoSelect.value = '';
                setEstadoBanco('Selecciona y guarda el banco del comprobante.', 'muted');
                setEstadoMontos('Carga un comprobante y digita su monto para actualizar el cuadre.', 'muted');

                if (verCompSinArchivos) {
                    verCompSinArchivos.classList.add('d-none');
                }

                renderComprobante('', verCompEntregadoImg, verCompEntregadoEmpty, verCompEntregadoLink);
                renderComprobante('', verCompDiferenciaImg, verCompDiferenciaEmpty, verCompDiferenciaLink);
                if (verCompEntregadoEmpty) verCompEntregadoEmpty.textContent = 'Cargando...';
                if (verCompDiferenciaEmpty) verCompDiferenciaEmpty.textContent = 'Cargando...';

                let urlEntregado = '';
                let urlDiferencia = '';

                if (comprobantesUrl.trim() !== '') {
                    try {
                        const response = await fetch(comprobantesUrl, {
                            method: 'GET',
                            headers: {
                                'X-Requested-With': 'XMLHttpRequest'
                            }
                        });

                        if (response.ok) {
                            const payload = await response.json();
                            urlEntregado = payload?.entregado_url || '';
                            urlDiferencia = payload?.diferencia_url || '';
                            if (verCompMontoEntregado) {
                                verCompMontoEntregado.value = formatMoney(payload?.entregado || this.dataset.entregado || '0');
                            }
                            if (verCompMontoGasto) {
                                verCompMontoGasto.value = formatMoney(payload?.gasto || this.dataset.gasto || '0');
                            }
                            procesadoActualComprobantes = parseFloat(payload?.procesado || this.dataset.procesado || '0') || 0;
                            if (verCompBancoSelect) {
                                verCompBancoSelect.value = payload?.banco_nombre || '';
                            }
                            if ((payload?.banco_nombre || '') !== '') {
                                setEstadoBanco('Banco cargado: ' + payload.banco_nombre, 'muted');
                            }
                        }
                    } catch (error) {
                        // Keep graceful fallback with empty values
                    }
                }

                if (verCompEntregadoEmpty) verCompEntregadoEmpty.textContent = 'No tiene comprobante.';
                if (verCompDiferenciaEmpty) verCompDiferenciaEmpty.textContent = 'No tiene comprobante.';

                if (verCompSinArchivos) {
                    const sinArchivos = urlEntregado.trim() === '' && urlDiferencia.trim() === '';
                    verCompSinArchivos.classList.toggle('d-none', !sinArchivos);
                }

                renderComprobante(urlEntregado, verCompEntregadoImg, verCompEntregadoEmpty, verCompEntregadoLink);
                renderComprobante(urlDiferencia, verCompDiferenciaImg, verCompDiferenciaEmpty, verCompDiferenciaLink);
                toggleMontoFields();
            });
        });

        btnSubirCompEntregado?.addEventListener('click', function () {
            inputSubirCompEntregado?.click();
        });

        btnSubirCompDiferencia?.addEventListener('click', function () {
            inputSubirCompDiferencia?.click();
        });

        inputSubirCompEntregado?.addEventListener('change', async function () {
            const file = this.files?.[0];
            if (file) {
                await subirComprobanteDesdeModal('entregado', file);
            }
            this.value = '';
        });

        inputSubirCompDiferencia?.addEventListener('change', async function () {
            const file = this.files?.[0];
            if (file) {
                await subirComprobanteDesdeModal('diferencia', file);
            }
            this.value = '';
        });

        btnGuardarMontosComprobante?.addEventListener('click', guardarMontosDesdeComprobantes);

        async function guardarBancoSeleccionado() {
            if (!reporteActualComprobantesId) {
                return;
            }

            const actualizarBancoUrl = obtenerActualizarBancoUrl();
            if (!actualizarBancoUrl) {
                return;
            }

            try {
                const bancoSeleccionado = verCompBancoSelect?.value || '';
                if (bancoSeleccionado === '') {
                    setEstadoBanco('Selecciona un banco antes de guardar.', 'warning');
                    return;
                }

                if (btnGuardarBancoComprobante) {
                    btnGuardarBancoComprobante.disabled = true;
                }
                setEstadoBanco('Guardando banco...', 'muted');

                const response = await fetch(actualizarBancoUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken(),
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    body: JSON.stringify({
                        banco_nombre: bancoSeleccionado,
                    }),
                });

                const payload = await response.json();
                if (!response.ok) {
                    throw new Error(payload?.message || payload?.errors?.banco_nombre?.[0] || 'No se pudo actualizar el banco.');
                }
                setEstadoBanco('Banco guardado correctamente.', 'success');
            } catch (error) {
                setEstadoBanco(error.message || 'No se pudo actualizar el banco.', 'danger');
                if (typeof Swal !== 'undefined') {
                    Swal.fire('Error', error.message || 'No se pudo actualizar el banco.', 'error');
                }
            } finally {
                if (btnGuardarBancoComprobante) {
                    btnGuardarBancoComprobante.disabled = false;
                }
            }
        }

        btnGuardarBancoComprobante?.addEventListener('click', guardarBancoSeleccionado);

        verCompBancoSelect?.addEventListener('change', function () {
            setEstadoBanco('Banco seleccionado. Haz clic en "Guardar banco" para confirmar.', 'warning');
        });

        formCuadreRutas?.addEventListener('submit', function (event) {
            const tieneEntregadoSeleccionado = dzEntregado ? dzEntregado.getAcceptedFiles().length > 0 : false;
            const tieneDiferenciaSeleccionado = dzDiferencia ? dzDiferencia.getAcceptedFiles().length > 0 : false;

            if (uploadsPendientes > 0) {
                event.preventDefault();
                if (typeof Swal !== 'undefined') {
                    Swal.fire('Carga en progreso', 'Espera a que termine la subida de imagenes antes de guardar.', 'info');
                }
                return;
            }

            if (tieneEntregadoSeleccionado && !(comprobanteEntregadoPath?.value || '').trim()) {
                event.preventDefault();
                if (typeof Swal !== 'undefined') {
                    Swal.fire('Comprobante pendiente', 'El comprobante entregado no se subio correctamente. Intenta nuevamente.', 'warning');
                }
                return;
            }

            if (tieneDiferenciaSeleccionado && !(comprobanteDiferenciaPath?.value || '').trim()) {
                event.preventDefault();
                if (typeof Swal !== 'undefined') {
                    Swal.fire('Comprobante pendiente', 'El comprobante diferencia no se subio correctamente. Intenta nuevamente.', 'warning');
                }
            }
        });

        calcularDiferencia();

        @if($errors->hasBag('guardarBanco') && $errors->guardarBanco->any())
            const modalBancos = new bootstrap.Modal(document.getElementById('modalBancos'));
            modalBancos.show();
        @elseif($errors->has('correo_destino'))
            const modalEnvio = new bootstrap.Modal(document.getElementById('modalEnvioCorreos'));
            modalEnvio.show();
        @elseif($errors->any())
            const modalCuadre = new bootstrap.Modal(document.getElementById('modalCuadreRutas'));
            modalCuadre.show();
        @endif
    });
</script>
@endsection

