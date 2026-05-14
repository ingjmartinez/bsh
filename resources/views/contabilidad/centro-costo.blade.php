@extends('app')

@section('content')
    <div class="main-content">
        <div class="page-content">
            <div class="container-fluid">

                <div class="row">
                    <div class="col-12">
                        <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                            <h4 class="mb-sm-0">Centro de Costo</h4>
                            <div class="page-title-right">
                                <ol class="breadcrumb m-0">
                                    <li class="breadcrumb-item"><a href="{{ route('inicio.index') }}">Inicio</a></li>
                                    <li class="breadcrumb-item"><a href="{{ route('contabilidad.index') }}">Contabilidad</a></li>
                                    <li class="breadcrumb-item active">Centro de Costo</li>
                                </ol>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-lg-12">
                        <div class="card">
                            <div class="card-header">
                                <div class="d-flex justify-content-between align-items-center">
                                    <h5 class="card-title mb-0">Centros de Costo (Empresa 168)</h5>
                                    <div class="d-flex gap-2">
                                        <button type="button" class="btn btn-primary" id="btnConsultarCentros">
                                            Consultar data
                                        </button>
                                        <button type="button" class="btn btn-soft-secondary" id="btnConfigurarCentros">
                                            Configurar
                                        </button>
                                        <button type="button" class="btn btn-info" id="btnSincronizarCentros">
                                            Sincronizar
                                        </button>
                                    </div>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="row g-2 align-items-end mb-3">
                                    <div class="col-12 col-md-4 col-lg-3">
                                        <label class="form-label">Estado</label>
                                        <select id="filtroEstadoCentroCosto" class="form-select form-select-sm">
                                            <option value="">Todos</option>
                                            <option value="activo">Activo</option>
                                            <option value="inactivo">Inactivo</option>
                                        </select>
                                    </div>
                                </div>

                                <div>
                                    <table id="tableCentrosCosto"
                                        class="table table-bordered nowrap table-striped align-middle w-100"
                                        style="width:100%">
                                        <thead>
                                            <tr>
                                                <th>CompanyID</th>
                                                <th>Id Centro Costo</th>
                                                <th>Terminal</th>
                                                <th>Nombre Agencia</th>
                                                <th>Estado</th>
                                                <th>Ruta Empresa</th>
                                                <th>Id SubGrupo</th>
                                                <th>Ciudad</th>
                                                <th>Empresa</th>
                                                <th>Creado por</th>
                                                <th>Fecha Grabado</th>
                                                <th>Modificado por</th>
                                                <th>Fecha Modificado</th>
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

        <footer class="footer">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-sm-6">
                        <script>document.write(new Date().getFullYear())</script> Velzon.
                    </div>
                    <div class="col-sm-6">
                        <div class="text-sm-end d-none d-sm-block">
                            Design & Develop by Themesbrand
                        </div>
                    </div>
                </div>
            </div>
        </footer>
    </div>

    <div class="modal fade" id="modalConfigurarCentros" tabindex="-1" aria-labelledby="modalConfigurarCentrosLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalConfigurarCentrosLabel">Configurar centros de costo</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-2 align-items-end mb-3">
                        <div class="col-12 col-md-5">
                            <label class="form-label">Buscar por Terminal</label>
                            <input type="text" class="form-control form-control-sm" id="buscarConfigIdViejo" placeholder="Escribe la Terminal">
                        </div>
                        <div class="col-12 col-md-auto">
                            <button type="button" class="btn btn-soft-warning btn-sm" id="btnVerOcultosCentros">
                                Ver ocultos
                            </button>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-sm table-bordered align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Id Centro Costo</th>
                                    <th>Terminal</th>
                                    <th>Nombre Agencia</th>
                                    <th>Estado</th>
                                    <th class="text-center">Ocultar</th>
                                </tr>
                            </thead>
                            <tbody id="tablaConfigCentrosCosto"></tbody>
                        </table>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cerrar</button>
                    <button type="button" class="btn btn-primary" id="btnGuardarConfigCentros">Guardar cambios</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('script')
    <script>
        const csrfToken = '{{ csrf_token() }}';
        let centrosCostoTable = null;
        let centrosCostoData = [];
        let mostrarSoloOcultosConfig = false;
        let progresoSincronizacionTimer = null;
        let resizeCentrosCostoTimer = null;
        let resizeCentrosCostoBound = false;

        async function parsearRespuestaJson(response, contextoError) {
            const contentType = (response.headers.get('content-type') || '').toLowerCase();
            const cuerpo = await response.text();
            let payload = null;

            if (cuerpo) {
                try {
                    payload = JSON.parse(cuerpo);
                } catch (_errorParse) {
                    payload = null;
                }
            }

            if (!response.ok) {
                const mensajeServidor = payload?.message || payload?.error || '';
                const mensajeNoJson = !contentType.includes('application/json')
                    ? 'El servidor devolvio HTML/no JSON. Revisa storage/logs/laravel.log.'
                    : '';
                const detalle = [mensajeServidor, mensajeNoJson].filter(Boolean).join(' | ');
                throw new Error(contextoError + ' (HTTP ' + response.status + ')' + (detalle ? ': ' + detalle : ''));
            }

            if (!payload) {
                throw new Error(contextoError + ': Respuesta no valida en formato JSON.');
            }

            return payload;
        }

        document.getElementById('btnConsultarCentros').addEventListener('click', () => cargarCentrosCosto(true));
        document.getElementById('btnConfigurarCentros').addEventListener('click', abrirConfiguracionCentros);
        document.getElementById('btnGuardarConfigCentros').addEventListener('click', guardarConfiguracionCentros);
        document.getElementById('buscarConfigIdViejo').addEventListener('input', renderConfigCentros);
        document.getElementById('btnVerOcultosCentros').addEventListener('click', alternarOcultosConfig);
        document.getElementById('btnSincronizarCentros').addEventListener('click', sincronizarCentros);
        document.getElementById('filtroEstadoCentroCosto').addEventListener('change', aplicarFiltroEstadoCentroCosto);
        document.getElementById('tablaConfigCentrosCosto').addEventListener('change', manejarCambioOcultarCentro);

        function esOculto(valor) {
            if (valor === true || valor === 1) return true;
            if (typeof valor === 'string') {
                const normalizado = valor.trim().toLowerCase();
                return normalizado === '1' || normalizado === 'true' || normalizado === 'si' || normalizado === 'yes' || normalizado === 'on';
            }
            return false;
        }

        function cargarCentrosCosto(mostrarAlerta = true) {
            const boton = document.getElementById('btnConsultarCentros');
            const textoOriginal = boton.innerText;
            boton.disabled = true;
            boton.innerText = 'Consultando...';

            if (mostrarAlerta) {
                mostrarAlertaCarga('Consultando data', 'Leyendo centros de costo desde la base de datos local...');
            }

            return fetch('/api-centros-costo', {
                headers: {
                    'Accept': 'application/json',
                },
            })
                .then(response => parsearRespuestaJson(response, 'Error al consultar centros de costo'))
                .then(data => {
                    const tableBody = document.querySelector('#tableCentrosCosto tbody');
                    tableBody.innerHTML = '';

                    centrosCostoData = Array.isArray(data) ? data : [];
                    const dataFiltrada = centrosCostoData.filter(item => {
                        return String(item.IdViejo ?? '').trim() !== '' && !esOculto(item.Ocultar);
                    });

                    dataFiltrada.forEach(item => {
                        const inactivo = Boolean(item.Inactivo);
                        const estadoTexto = inactivo ? 'Inactivo' : 'Activo';
                        const estadoBadge = inactivo
                            ? '<span class="badge bg-danger">Inactivo</span>'
                            : '<span class="badge bg-success">Activo</span>';

                        const row = document.createElement('tr');
                        row.innerHTML = `
                            <td>${item.CompanyID ?? ''}</td>
                            <td>${item.IdCentroCosto ?? ''}</td>
                            <td>${item.IdViejo ?? ''}</td>
                            <td>${item.Descripcion ?? ''}</td>
                            <td data-search="${estadoTexto}">${estadoBadge}</td>
                            <td>${item.IdGrupo ?? ''}</td>
                            <td>${item.IdSubGrupo ?? ''}</td>
                            <td>${item.IdDivision ?? ''}</td>
                            <td>${item.IdSociedad ?? ''}</td>
                            <td>${item.CreadoPor ?? ''}</td>
                            <td>${item.FechaGrabado ?? ''}</td>
                            <td>${item.ModificadoPor ?? ''}</td>
                            <td>${item.FechaModificado ?? ''}</td>
                        `;
                        tableBody.appendChild(row);
                    });

                    if ($.fn.DataTable.isDataTable('#tableCentrosCosto')) {
                        $('#tableCentrosCosto').DataTable().destroy();
                    }

                    centrosCostoTable = $('#tableCentrosCosto').DataTable({
                        responsive: false,
                        scrollX: true,
                        scrollCollapse: true,
                        autoWidth: false,
                        deferRender: true,
                        pageLength: 25,
                        dom: 'Bfrtip',
                        buttons: ['copy', 'csv', 'excel', 'pdf', 'print'],
                        columnDefs: [
                            { targets: '_all', className: 'text-nowrap align-middle' }
                        ]
                    });

                    enlazarEventosTablaCentrosCosto();
                    ajustarLayoutTablaCentrosCosto();
                    aplicarFiltroEstadoCentroCosto();

                    if (mostrarAlerta) {
                        mostrarAlertaExito('Consulta completada', 'Registros cargados: ' + dataFiltrada.length.toLocaleString('es-DO'));
                    }
                })
                .catch(error => {
                    console.error('Error cargando centros de costo:', error);
                    mostrarAlertaError('No se pudieron cargar los centros de costo.');
                })
                .finally(() => {
                    boton.disabled = false;
                    boton.innerText = textoOriginal;
                });
        }

        function aplicarFiltroEstadoCentroCosto() {
            if (!centrosCostoTable) return;

            const estado = document.getElementById('filtroEstadoCentroCosto').value;
            const busqueda = estado === 'activo' ? '^Activo$' : (estado === 'inactivo' ? '^Inactivo$' : '');
            centrosCostoTable.column(4).search(busqueda, true, false).draw();
            ajustarLayoutTablaCentrosCosto();
        }

        function ajustarLayoutTablaCentrosCosto() {
            if (!centrosCostoTable) return;

            centrosCostoTable.columns.adjust();
        }

        function enlazarEventosTablaCentrosCosto() {
            const $tabla = $('#tableCentrosCosto');

            $tabla.off('draw.dt.centroCosto').on('draw.dt.centroCosto', function () {
                ajustarLayoutTablaCentrosCosto();
            });

            if (!resizeCentrosCostoBound) {
                window.addEventListener('resize', function () {
                    if (resizeCentrosCostoTimer) {
                        clearTimeout(resizeCentrosCostoTimer);
                    }

                    resizeCentrosCostoTimer = setTimeout(() => {
                        ajustarLayoutTablaCentrosCosto();
                    }, 120);
                });
                resizeCentrosCostoBound = true;
            }
        }

        async function abrirConfiguracionCentros() {
            if (!centrosCostoData.length) {
                await cargarCentrosCosto(false);
            }

            renderConfigCentros();

            const modalElement = document.getElementById('modalConfigurarCentros');
            const modal = bootstrap.Modal.getOrCreateInstance(modalElement);
            modal.show();
        }

        function renderConfigCentros() {
            const tbody = document.getElementById('tablaConfigCentrosCosto');
            const busqueda = String(document.getElementById('buscarConfigIdViejo').value || '').trim().toLowerCase();
            const registros = centrosCostoData.filter(item => {
                const idViejo = String(item.IdViejo ?? '').trim();
                if (idViejo === '') return false;
                if (mostrarSoloOcultosConfig && !esOculto(item.Ocultar)) return false;
                return busqueda === '' || idViejo.toLowerCase().includes(busqueda);
            }).sort((a, b) => compararIdViejo(a.IdViejo, b.IdViejo));

            tbody.innerHTML = registros.map(item => {
                const inactivo = Boolean(item.Inactivo);
                const estadoBadge = inactivo
                    ? '<span class="badge bg-danger">Inactivo</span>'
                    : '<span class="badge bg-success">Activo</span>';

                return `
                    <tr>
                        <td>${item.IdCentroCosto ?? ''}</td>
                        <td>${item.IdViejo ?? ''}</td>
                        <td>${item.Descripcion ?? ''}</td>
                        <td>${estadoBadge}</td>
                        <td class="text-center">
                            <input type="checkbox" class="form-check-input centro-ocultar-check"
                                data-id="${item.id}" ${esOculto(item.Ocultar) ? 'checked' : ''}>
                        </td>
                    </tr>
                `;
            }).join('');

            if (!registros.length) {
                tbody.innerHTML = '<tr><td colspan="5" class="text-center text-muted">No hay centros de costo para configurar.</td></tr>';
            }
        }

        function alternarOcultosConfig() {
            mostrarSoloOcultosConfig = !mostrarSoloOcultosConfig;

            const boton = document.getElementById('btnVerOcultosCentros');
            boton.classList.toggle('btn-warning', mostrarSoloOcultosConfig);
            boton.classList.toggle('btn-soft-warning', !mostrarSoloOcultosConfig);
            boton.textContent = mostrarSoloOcultosConfig ? 'Ver todos' : 'Ver ocultos';

            renderConfigCentros();
        }

        function compararIdViejo(a, b) {
            const valorA = String(a ?? '').trim();
            const valorB = String(b ?? '').trim();
            const numeroA = Number(valorA);
            const numeroB = Number(valorB);

            if (Number.isFinite(numeroA) && Number.isFinite(numeroB)) {
                return numeroA - numeroB;
            }

            return valorA.localeCompare(valorB, 'es', { numeric: true, sensitivity: 'base' });
        }

        function manejarCambioOcultarCentro(event) {
            const check = event.target.closest('.centro-ocultar-check');
            if (!check) return;

            const id = Number(check.dataset.id || 0);
            if (id <= 0) return;

            const centro = centrosCostoData.find(item => Number(item.id || 0) === id);
            if (!centro) return;

            centro.Ocultar = check.checked ? 1 : 0;
        }

        function guardarConfiguracionCentros() {
            const items = centrosCostoData
                .map(item => ({
                    id: Number(item.id || 0),
                    ocultar: esOculto(item.Ocultar)
                }))
                .filter(item => item.id > 0);

            mostrarAlertaCarga('Guardando configuracion', 'Actualizando visibilidad de centros de costo...');

            fetch('/api-centros-costo/visibilidad', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json',
                },
                body: JSON.stringify({ items })
            })
                .then(response => parsearRespuestaJson(response, 'Error al guardar configuracion de centros de costo'))
                .then(data => {
                    mostrarAlertaExito('Configuracion guardada', 'Registros actualizados: ' + (data.actualizados ?? 0));
                    return cargarCentrosCosto(false);
                })
                .catch(error => {
                    mostrarAlertaError(error.message || 'Error guardando configuracion.');
                });
        }

        async function sincronizarCentros() {
            const confirmado = await confirmarSincronizacion();
            if (!confirmado) return;

            const boton = document.getElementById('btnSincronizarCentros');
            const textoOriginal = boton.innerText;
            boton.disabled = true;
            boton.innerText = 'Sincronizando...';
            iniciarProgresoSincronizacion();

            fetch('/api-centros-costo/sync', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json',
                },
            })
                .then(response => parsearRespuestaJson(response, 'Error durante la sincronizacion de centros de costo'))
                .then(data => {
                    finalizarProgresoSincronizacion();
                    mostrarAlertaExito(
                        'Sincronizacion completada',
                        `Recibidos: ${data.total_recibidos ?? 0}<br>` +
                        `Creados: ${data.creados ?? 0}<br>` +
                        `Actualizados: ${data.actualizados ?? 0}<br>` +
                        `Omitidos: ${data.omitidos ?? 0}`
                    );
                    cargarCentrosCosto(false);
                })
                .catch(error => {
                    detenerProgresoSincronizacion();
                    mostrarAlertaError(error.message || 'Error sincronizando centros de costo.');
                })
                .finally(() => {
                    boton.disabled = false;
                    boton.innerText = textoOriginal;
                });
        }

        function mostrarAlertaCarga(titulo, texto) {
            if (typeof Swal === 'undefined') return;

            Swal.fire({
                title: titulo,
                text: texto,
                allowOutsideClick: false,
                allowEscapeKey: false,
                didOpen: () => Swal.showLoading()
            });
        }

        function mostrarAlertaExito(titulo, html) {
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    icon: 'success',
                    title: titulo,
                    html,
                    confirmButtonText: 'Cerrar'
                });
                return;
            }

            alert(String(html).replace(/<br>/g, '\n'));
        }

        function mostrarAlertaError(mensaje) {
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: mensaje,
                    confirmButtonText: 'Cerrar'
                });
                return;
            }

            alert(mensaje);
        }

        async function confirmarSincronizacion() {
            if (typeof Swal === 'undefined') {
                return confirm('Sincronizar centros de costo desde el API externo?');
            }

            const result = await Swal.fire({
                icon: 'question',
                title: 'Sincronizar centros de costo',
                text: 'Esto consultara el API externo y guardara datos nuevos o actualizados.',
                showCancelButton: true,
                confirmButtonText: 'Sincronizar',
                cancelButtonText: 'Cancelar'
            });

            return result.isConfirmed;
        }

        function iniciarProgresoSincronizacion() {
            let progreso = 0;
            mostrarProgresoSincronizacion(progreso);

            progresoSincronizacionTimer = setInterval(() => {
                progreso = Math.min(progreso + 5, 90);
                actualizarProgresoSincronizacion(progreso);
            }, 500);
        }

        function finalizarProgresoSincronizacion() {
            detenerProgresoSincronizacion();
            actualizarProgresoSincronizacion(100);
        }

        function detenerProgresoSincronizacion() {
            if (progresoSincronizacionTimer) {
                clearInterval(progresoSincronizacionTimer);
                progresoSincronizacionTimer = null;
            }
        }

        function mostrarProgresoSincronizacion(progreso) {
            if (typeof Swal === 'undefined') return;

            Swal.fire({
                title: 'Sincronizando centros de costo',
                html: `
                    <div class="progress" style="height: 20px;">
                        <div id="syncCentrosProgressBar" class="progress-bar progress-bar-striped progress-bar-animated"
                            role="progressbar" style="width: ${progreso}%">
                            ${progreso}%
                        </div>
                    </div>
                    <div class="text-muted mt-2">Guardando datos nuevos y actualizando existentes...</div>
                `,
                allowOutsideClick: false,
                allowEscapeKey: false,
                showConfirmButton: false
            });
        }

        function actualizarProgresoSincronizacion(progreso) {
            const progressBar = document.getElementById('syncCentrosProgressBar');
            if (!progressBar) return;

            progressBar.style.width = progreso + '%';
            progressBar.textContent = progreso + '%';
        }
    </script>
@endsection
