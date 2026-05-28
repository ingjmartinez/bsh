@extends('app')

@section('content')
    <div class="main-content">
        <div class="page-content">
            <div class="container-fluid">

                <div class="row">
                    <div class="col-12">
                        <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                            <h4 class="mb-sm-0">Procesar Incentivos Automáticamente</h4>
                            <div class="page-title-right">
                                <ol class="breadcrumb m-0">
                                    <li class="breadcrumb-item"><a href="{{ route('inicio.index') }}">Inicio</a></li>
                                    <li class="breadcrumb-item"><a href="{{ route('incentivos.index') }}">Incentivos</a></li>
                                    <li class="breadcrumb-item active">Procesar Incentivos</li>
                                </ol>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-lg-12">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="card-title mb-0">Ejecutar todos los cálculos de incentivos</h5>
                            </div>
                            <div class="card-body">
                                <div class="row mb-3">
                                    <div class="col-2">
                                        <label for="inputYear" class="form-label">Año</label>
                                        <select id="inputYear" class="form-control">
                                            <option value="2026">2026</option>
                                            <option value="2025">2025</option>
                                            <option value="2024">2024</option>
                                            <option value="2023">2023</option>
                                            <option value="2022">2022</option>
                                            <option value="2021">2021</option>
                                        </select>
                                    </div>

                                    <div class="col-2">
                                        <label for="inputMes" class="form-label">Mes</label>
                                        <select id="inputMes" class="form-control">
                                            <option value="1">Enero</option>
                                            <option value="2">Febrero</option>
                                            <option value="3">Marzo</option>
                                            <option value="4">Abril</option>
                                            <option value="5">Mayo</option>
                                            <option value="6">Junio</option>
                                            <option value="7">Julio</option>
                                            <option value="8">Agosto</option>
                                            <option value="9">Septiembre</option>
                                            <option value="10">Octubre</option>
                                            <option value="11">Noviembre</option>
                                            <option value="12">Diciembre</option>
                                        </select>
                                    </div>

                                    <div class="col-8">
                                        <label class="form-label">&nbsp;</label>
                                        <div>
                                            <button id="btnProcesarTodo" class="btn btn-primary">Procesar Todo</button>
                                            <button id="btnExcluirProductos" class="btn btn-secondary">Excluir Productos</button>
                                            <a href="{{ url('/incentivos/reporte-pagos') }}" class="btn btn-info">Ver Reportes</a>
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-12 col-lg-5 mb-3">
                                        <div class="card">
                                            <div class="card-body">
                                                <h6>Estados por módulo</h6>
                                                <div style="max-height:400px; overflow:auto;">
                                                    <table class="table table-sm table-bordered" id="statusTable">
                                                        <thead>
                                                            <tr>
                                                                <th style="width:1%">#</th>
                                                                <th>Módulo</th>
                                                                <th style="width:1%">Estado</th>
                                                                <th>Detalle</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            <!-- filas generadas por JS -->
                                                        </tbody>
                                                    </table>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-12 col-lg-7">
                                        <div class="card">
                                            <div class="card-body">
                                                <h6>Registro de ejecución</h6>
                                                <div id="logContainer" style="max-height:400px; overflow:auto; background:#f8f9fa; padding:10px; border-radius:4px;">
                                                    <!-- logs -->
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <!-- Modal Excluir Productos -->
    <div id="myModalExcluir" class="modal fade" tabindex="-1" aria-labelledby="myModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="myModalLabel">Excluir Productos</h5>
                    <button type="button" id="btnCloseExcluidos" class="btn-close"
                        data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="card-body">
                        <table id="tableProductosExcluir"
                            class="table table-bordered dt-responsive nowrap table-striped align-middle"
                            style="width:100%">
                            <thead>
                                <tr>
                                    <th>
                                        {{-- Seleccionar Todos --}}
                                        <input type="checkbox" id="chkSeleccionarTodos">
                                    </th>
                                    <th>Producto ID</th>
                                    <th>Nombre Producto</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($productosExcluidos as $producto)
                                    <tr>
                                        <td>
                                            <input type="checkbox" class="chkProductoExcluir"
                                                data-producto-id="{{ $producto->producto_id }}">
                                        </td>
                                        <td>{{ $producto->producto_id }}</td>
                                        <td>{{ $producto->descripcion }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div><!-- /.modal-content -->
        </div><!-- /.modal-dialog -->
    </div><!-- /.modal -->

@endsection

@section('script')
    <script>
        // Lista de módulos de incentivos en el orden de ejecución
        const modules = [
            { name: 'Incentivos Base', type: 'list', url: '/incentivos/list', saveUrl: '/incentivos/save' },
            { name: 'Plan Agencia', type: 'list', url: '/incentivos/list/plan-agencia', saveUrl: '/incentivos/save/plan-agencia' },
            { name: 'Efectividad', type: 'list', url: '/incentivos/list/efectividad-usuario', saveUrl: '/incentivos/save/efectividad' },
            { name: 'Pago Agente (Lotobet Real)', type: 'list', url: '/incentivos/list/pago-incentivos-agente', saveUrl: '/incentivos/save/pago-incentivos-agente', sistema: 'Lotobet' },
            { name: 'Pago Agente (Lotonet)', type: 'list', url: '/incentivos/list/pago-incentivos-agente', saveUrl: '/incentivos/save/pago-incentivos-agente', sistema: 'Lotonet' },
            { name: 'Pago Coordinador (Lotobet Real)', type: 'list', url: '/incentivos/list/pago-incentivos-coordinador', saveUrl: '/incentivos/save/pago-incentivos-coordinador', sistema: 'Lotobet' },
            { name: 'Pago Coordinador (Lotonet)', type: 'list', url: '/incentivos/list/pago-incentivos-coordinador', saveUrl: '/incentivos/save/pago-incentivos-coordinador', sistema: 'Lotonet' },
            { name: 'Pago Admin (Lotobet Real)', type: 'list', url: '/incentivos/list/pago-incentivos-admin', saveUrl: '/incentivos/save/pago-incentivos-admin', sistema: 'Lotobet' },
            { name: 'Pago Admin (Lotonet)', type: 'list', url: '/incentivos/list/pago-incentivos-admin', saveUrl: '/incentivos/save/pago-incentivos-admin', sistema: 'Lotonet' }
        ];

        const logContainer = document.getElementById('logContainer');

        // Render initial status table rows
        const statusTableBody = document.querySelector('#statusTable tbody');
        function initStatusTable() {
            statusTableBody.innerHTML = '';
            modules.forEach((m, idx) => {
                const tr = document.createElement('tr');
                tr.setAttribute('data-module', m.name);
                tr.innerHTML = `
                    <td>${idx + 1}</td>
                    <td>${m.name}</td>
                    <td class="status-cell"><span class="badge bg-secondary">Pendiente</span></td>
                    <td class="detail-cell">-</td>
                `;
                statusTableBody.appendChild(tr);
            });
        }

        function setStatus(moduleName, status, detail) {
            const row = statusTableBody.querySelector(`tr[data-module="${moduleName}"]`);
            if (!row) return;
            const statusCell = row.querySelector('.status-cell');
            const detailCell = row.querySelector('.detail-cell');
            let badgeClass = 'bg-secondary';
            if (status === 'OK') badgeClass = 'bg-success';
            if (status === 'Error') badgeClass = 'bg-danger';
            if (status === 'Ejecutando') badgeClass = 'bg-info';
            statusCell.innerHTML = `<span class="badge ${badgeClass}">${status}</span>`;
            detailCell.textContent = detail || '';
        }

        function addLog(text, level = 'info') {
            const time = new Date().toLocaleString();
            const el = document.createElement('div');
            el.style.padding = '6px 4px';
            el.style.borderBottom = '1px solid #e9ecef';
            el.innerHTML = `<strong>[${time}]</strong> <span style="color:${level === 'error' ? '#c92a2a' : '#212529'}">${text}</span>`;
            logContainer.prepend(el);
        }

        // Initialize status table on load
        initStatusTable();

        // Checkbox Seleccionar Todos
        document.getElementById('chkSeleccionarTodos').addEventListener('change', function() {
            const checkboxes = document.querySelectorAll('.chkProductoExcluir');
            checkboxes.forEach(checkbox => {
                checkbox.checked = this.checked;
            });
        });

        document.getElementById('btnExcluirProductos').addEventListener('click', () => {
            var myModalExcluir = new bootstrap.Modal(document.getElementById('myModalExcluir'), {
                keyboard: false
            });
            myModalExcluir.show();
        });

        function chunkArray(arr, size) {
            const chunks = [];
            for (let i = 0; i < arr.length; i += size) {
                chunks.push(arr.slice(i, i + size));
            }
            return chunks;
        }

        async function fetchWithTimeout(url, options = {}, timeoutMs = 300000) {
            const controller = new AbortController();
            const id = setTimeout(() => controller.abort(), timeoutMs);
            try {
                const res = await fetch(url, { ...options, signal: controller.signal });
                return res;
            } finally {
                clearTimeout(id);
            }
        }

        async function processModule(mod, params, productosExcluidos) {
            const { mes, year } = params;
            
            setStatus(mod.name, 'Ejecutando', 'Consultando...');
            addLog(`-> Consultando ${mod.name} (${mod.url})`);
            
            try {
                // Construir URL según el tipo de módulo
                let listUrl = `${mod.url}?mes=${mes}&year=${year}`;
                
                // Agregar parámetros específicos según el módulo
                if (mod.name === 'Incentivos Base' || mod.name.includes('Efectividad')) {
                    listUrl += `&excluidos=${productosExcluidos}`;
                }
                
                // Agregar sistema si el módulo lo requiere
                if (mod.sistema) {
                    listUrl += `&sistema=${mod.sistema}`;
                }
                
                // Consultar datos
                const listRes = await fetchWithTimeout(listUrl, {}, 300000);
                const listText = await listRes.text();
                let listData = null;
                
                try { 
                    listData = JSON.parse(listText); 
                } catch(e) { 
                    listData = null; 
                }
                
                if (!listRes.ok) {
                    const noDataMessage = listData?.message || '';
                    const isNoData = listRes.status === 404 && noDataMessage.toLowerCase().includes('no hay datos');
                    if (isNoData) {
                        const msg = noDataMessage || 'No hay datos para procesar';
                        addLog(`INFO ${mod.name}: ${msg}`);
                        setStatus(mod.name, 'OK', msg);
                        return { ok: true, message: msg };
                    }

                    addLog(`ERROR ${mod.name}: HTTP ${listRes.status} - ${listText}`, 'error');
                    setStatus(mod.name, 'Error', `HTTP ${listRes.status}`);
                    return { ok: false, message: listText };
                }
                
                // Verificar si hay mensaje de error en la respuesta
                if (listData && 'message' in listData && listData.message.includes('Error')) {
                    addLog(`ERROR ${mod.name}: ${listData.message}`, 'error');
                    setStatus(mod.name, 'Error', listData.message);
                    return { ok: false, message: listData.message };
                }
                
                // Si no hay datos, reportar
                if (!listData || (Array.isArray(listData) && listData.length === 0)) {
                    const msg = 'No hay datos para procesar';
                    addLog(`INFO ${mod.name}: ${msg}`);
                    setStatus(mod.name, 'OK', msg);
                    return { ok: true, message: msg };
                }
                
                const dataCount = Array.isArray(listData) ? listData.length : 'N/A';
                addLog(`INFO ${mod.name}: ${dataCount} registros obtenidos`);
                
                // Guardar datos
                const dataArray = Array.isArray(listData) ? listData : [listData];
                const chunkSize = 2000;
                const chunks = chunkArray(dataArray, chunkSize);

                let lastSaveData = null;

                for (let c = 0; c < chunks.length; c++) {
                    const chunk = chunks[c];
                    const progress = `${c + 1}/${chunks.length}`;
                    setStatus(mod.name, 'Ejecutando', `Guardando... (${progress})`);
                    addLog(`-> Guardando ${mod.name} (${mod.saveUrl}) ${progress}`);

                    const saveRes = await fetchWithTimeout(mod.saveUrl, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({
                            datos: chunk,
                            mes: mes,
                            year: year,
                            sistema: mod.sistema || null,
                            reset: c === 0
                        })
                    }, 300000);

                    const saveText = await saveRes.text();
                    let saveData = null;

                    try { 
                        saveData = JSON.parse(saveText); 
                    } catch(e) { 
                        saveData = null; 
                    }

                    if (!saveRes.ok) {
                        addLog(`ERROR ${mod.name} al guardar: HTTP ${saveRes.status} - ${saveText}`, 'error');
                        setStatus(mod.name, 'Error', `HTTP ${saveRes.status}`);
                        return { ok: false, message: saveText };
                    }

                    lastSaveData = saveData;
                }

                const msg = lastSaveData?.message || 'Guardado exitosamente';
                addLog(`OK ${mod.name}: ${msg}`);
                setStatus(mod.name, 'OK', msg);

                return { ok: true, data: lastSaveData };
                
            } catch (err) {
                addLog(`EXCEPCIÓN ${mod.name}: ${err.message}`, 'error');
                setStatus(mod.name, 'Error', err.message);
                return { ok: false, message: err.message };
            }
        }

        async function processAll() {
            const mes = document.getElementById('inputMes').value;
            const year = document.getElementById('inputYear').value;
            
            if (!mes) {
                Swal.fire({
                    title: 'Error',
                    text: 'Selecciona un mes para procesar',
                    icon: 'error'
                });
                return;
            }
            
            addLog(`Iniciando procesamiento de incentivos para ${mes}/${year}`);
            
            // Obtener productos excluidos
            let checks = document.querySelectorAll('.chkProductoExcluir');
            let productosExcluidos = [];
            checks.forEach(function(check) {
                if (check.checked) {
                    productosExcluidos.push(check.getAttribute('data-producto-id'));
                }
            });
            let excluidos = productosExcluidos.join(',');
            
            const results = [];
            modules.forEach(m => setStatus(m.name, 'Pendiente', '-'));
            
            for (let i = 0; i < modules.length; i++) {
                const mod = modules[i];
                const result = await processModule(mod, { mes, year }, excluidos);
                results.push({ module: mod.name, ...result });
            }
            
            addLog(`Finalizado procesamiento para ${mes}/${year}`);
            
            // resumen
            const okCount = results.filter(r => r.ok).length;
            const errCount = results.filter(r => !r.ok).length;
            addLog(`Resumen: OK=${okCount} Error=${errCount}`);
            
            Swal.fire({
                title: 'Resumen',
                html: `Mes/Año: <strong>${mes}/${year}</strong><br>OK: <strong>${okCount}</strong><br>Errores: <strong>${errCount}</strong>`,
                icon: errCount > 0 ? 'warning' : 'success'
            });
            
            return results;
        }

        document.getElementById('btnProcesarTodo').addEventListener('click', async () => {
            Swal.fire({
                title: 'Procesando...',
                allowOutsideClick: false,
                didOpen: () => Swal.showLoading()
            });
            
            try {
                await processAll();
                Swal.close();
            } catch (e) {
                Swal.fire({
                    title: 'Error',
                    text: e.message || e,
                    icon: 'error'
                });
            }
        });
    </script>
@endsection

