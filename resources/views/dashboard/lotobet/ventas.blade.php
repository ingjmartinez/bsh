@extends('app')

@section('title', 'Dashboard Financiero')

@section('content')
    <style>
        /* Mejorar tablas en mÃ³vil */
        @media (max-width: 767px) {
            .table {
                font-size: 0.85rem;
            }
            
            .table thead {
                font-size: 0.75rem;
                font-weight: 600;
            }
            
            .table tbody td {
                padding: 0.5rem 0.25rem !important;
                word-break: break-word;
            }
            
            /* Ocultar columnas menos importantes en mÃ³vil */
            .table th:nth-child(3),
            .table td:nth-child(3),
            .table th:nth-child(4),
            .table td:nth-child(4) {
                display: none;
            }
            
            /* Hacer visible la columna de tipo y total */
            .table th:nth-child(1),
            .table td:nth-child(1),
            .table th:nth-child(2),
            .table td:nth-child(2) {
                min-width: 60px;
            }
        }
        
        .dataTables_wrapper {
            position: relative;
        }
        
        @media (max-width: 767px) {
            .dataTables_wrapper .dataTables_length,
            .dataTables_wrapper .dataTables_filter {
                float: none;
                text-align: center;
                margin-bottom: 1rem;
            }
            
            .dataTables_wrapper .dataTables_info,
            .dataTables_wrapper .dataTables_paginate {
                float: none;
                text-align: center;
                margin-top: 1rem;
                font-size: 0.85rem;
            }
        }

        .tipo-card-wrap {
            margin-bottom: 0.5rem;
        }

        .tipo-card {
            height: auto;
        }

        .tipo-card .card-body {
            padding: 0.65rem 0.8rem;
        }

        .tipo-card .card-title {
            font-size: 0.95rem;
            line-height: 1.2;
        }

        @media (max-width: 767.98px) {
            .tipo-card {
                height: auto;
            }
        }
    </style>
    <div class="main-content">
        <div class="page-content">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-12">
                        <div class="d-flex justify-content-between align-items-start align-items-lg-center flex-column flex-lg-row gap-3 text-center text-lg-start mb-3">
                            <div>
                                <h1 class="h3 mb-1">Dashboard Financiero LotoBet - Ventas por Tipo de Producto</h1>
                                <ol class="breadcrumb m-0 justify-content-center justify-content-lg-start">
                                    <li class="breadcrumb-item"><a href="{{ route('inicio.index') }}">Inicio</a></li>
                                    <li class="breadcrumb-item"><a href="{{ route('dashboard.index') }}">Dashboard</a></li>
                                    <li class="breadcrumb-item active">Lotobet Ventas</li>
                                </ol>
                            </div>
                            <div class="d-flex align-items-center gap-2 flex-wrap justify-content-center">
                                <span id="badge-agencia" class="badge bg-primary fs-6" style="display: none; padding: 8px 12px;">Agencia: <span id="agencia-id-badge" style="font-weight: bold;"></span></span>
                                <button id="btn-limpiar-agencia" class="btn btn-outline-secondary btn-sm" style="display: none;">Limpiar agencia</button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Datepicker -->
                <div class="row mb-4 g-3">
                    <div class="col-12 col-md-4 col-lg-3">
                        <label for="fecha_inicio" class="form-label">Fecha Inicio</label>
                        <input type="date" id="fecha_inicio" class="form-control" value="{{ date('Y-m-d') }}">
                    </div>
                    <div class="col-12 col-md-4 col-lg-3">
                        <label for="fecha_fin" class="form-label">Fecha Fin</label>
                        <input type="date" id="fecha_fin" class="form-control" value="{{ date('Y-m-d') }}">
                    </div>
                    <div class="col-12 col-md-4 col-lg-3">
                        <label for="empresa" class="form-label">Empresa</label>
                        <select id="empresa" class="form-select">
                            <option value="todas" selected>Todas</option>
                            <option value="negosur">Negosur</option>
                            <option value="joselito">BSH Support</option>
                        </select>
                    </div>
                    <div class="col-12 col-md-4 col-lg-3 col-xl-2 d-flex align-items-end">
                        <button id="filtrar-btn" class="btn btn-primary w-100">Filtrar</button>
                    </div>
                </div>

                <!-- KPIs -->
                <div class="row mb-4 g-3">
                    <div class="col-12 col-sm-6 col-xl-3">
                        <div class="card shadow-sm">
                            <div class="card-body">
                                <h5 class="card-title">Total Vendido</h5>
                                <h3 id="kpi-total" class="text-primary">RD$ 0.00</h3>
                            </div>
                        </div>
                    </div>
                    <div class="col-12 col-sm-6 col-xl-3">
                        <div class="card shadow-sm">
                            <div class="card-body">
                                <h5 class="card-title">Transacciones</h5>
                                <h3 id="kpi-transacciones" class="text-success">0</h3>
                            </div>
                        </div>
                    </div>
                    <div class="col-12 col-sm-6 col-xl-3">
                        <div class="card shadow-sm">
                            <div class="card-body">
                                <h5 class="card-title">Ticket Promedio</h5>
                                <h3 id="kpi-ticket" class="text-info">RD$ 0.00</h3>
                            </div>
                        </div>
                    </div>
                    <div class="col-12 col-sm-6 col-xl-3">
                        <div class="card shadow-sm">
                            <div class="card-body">
                                <h5 class="card-title">Total Agencias</h5>
                                <h3 id="kpi-agencias" class="text-warning">0</h3>
                                <div class="small text-muted">Agencias en cero: <span id="kpi-agencias-cero">0</span></div>
                                <div class="mt-2">
                                    <button id="btn-ver-agencias-cero" class="btn btn-sm btn-outline-secondary">Ver Agencias en Cero</button>
                                    <button id="btn-export-agencias-cero-dia" class="btn btn-sm btn-outline-dark">Exportar por DÃ­a</button>
                                    <button id="btn-ver-agencias" class="btn btn-sm btn-outline-warning">Ver Detalle</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Cards por Tipo de Producto -->
                <div class="row mb-4">
                    <div class="col-12">
                        <h5 class="mb-3">Ventas por Tipo de Producto</h5>
                    </div>
                    <div id="cards-container" class="row g-3 w-100 mx-0">
                        <!-- Las cards se generarÃ¡n dinÃ¡micamente aquÃ­ -->
                    </div>
                </div>

                <!-- Chart Diario -->
                <div class="row mb-4">
                    <div class="col-md-12">
                        <div class="card shadow-sm">
                            <div class="card-header">
                                <h5>Ventas por DÃ­a (LÃ­nea)</h5>
                            </div>
                            <div class="card-body">
                                <canvas id="chart-diario" style="height: 250px; max-height: 250px;"></canvas>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-12">
                        <div class="card shadow-sm">
                            <div class="card-header">
                                <h5>Ventas por DÃ­a (Barras)</h5>
                            </div>
                            <div class="card-body">
                                <canvas id="chart-diario-bar" style="height: 250px; max-height: 250px;"></canvas>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
@endsection

<!-- Modal para Agencias -->
<div class="modal fade" id="modalAgencias" tabindex="-1" aria-labelledby="modalAgenciasLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalAgenciasLabel">Agencias con Ventas</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="table-responsive">
                    <table id="tabla-agencias" class="table w-100 table-striped table-hover">
                        <thead>
                            <tr>
                                <th>ID Agencia</th>
                                <th>Total Ventas</th>
                                <th>AcciÃ³n</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal para Agencias en Cero -->
<div class="modal fade" id="modalAgenciasCero" tabindex="-1" aria-labelledby="modalAgenciasCeroLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalAgenciasCeroLabel">Agencias en Cero</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="table-responsive">
                    <table id="tabla-agencias-cero" class="table w-100 table-striped table-hover">
                        <thead>
                            <tr>
                                <th>ID Agencia</th>
                                <th>Nombre</th>
                                <th>Total Ventas</th>
                                <th>AcciÃ³n</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>

@section('script')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js"></script>
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css">

    <script>
        let chartDiarioInstance = null;
        let chartDiarioBarInstance = null;
        let agenciasData = [];
        let agenciasCeroData = [];
        let currentAgenciaId = null;
        const badgeAgencia = document.getElementById('badge-agencia');
        const badgeAgenciaText = document.getElementById('agencia-id-badge');
        const limpiarAgenciaBtn = document.getElementById('btn-limpiar-agencia');
        const tablaAgenciasColumns = [
            { title: 'ID Agencia' },
            { title: 'Total Ventas' },
            { title: 'AcciÃ³n', orderable: false, searchable: false }
        ];
        const tablaAgenciasCeroColumns = [
            { title: 'ID Agencia' },
            { title: 'Nombre' },
            { title: 'Total Ventas' },
            { title: 'AcciÃ³n', orderable: false, searchable: false }
        ];

        const agenciasTableInstance = $('#tabla-agencias').DataTable({
            data: [],
            columns: tablaAgenciasColumns,
            paging: true,
            searching: true,
            ordering: true,
            responsive: true,
            scrollX: true,
            columnDefs: [
                { targets: [1, 2], visible: $(window).width() > 768 }
            ],
            language: { url: '//cdn.datatables.net/plug-ins/1.11.5/i18n/es-ES.json' }
        });
        const agenciasCeroTableInstance = $('#tabla-agencias-cero').DataTable({
            data: [],
            columns: tablaAgenciasCeroColumns,
            paging: true,
            searching: true,
            ordering: true,
            responsive: true,
            scrollX: true,
            columnDefs: [
                { targets: [2, 3], visible: $(window).width() > 768 }
            ],
            language: { url: '//cdn.datatables.net/plug-ins/1.11.5/i18n/es-ES.json' }
        });

        function formatCurrency(value) {
            return new Intl.NumberFormat('es-DO', {
                style: 'currency',
                currency: 'DOP'
            }).format(value);
        }

        function loadData(fecha_inicio, fecha_fin, agencia_id = null, empresa = null) {
            currentAgenciaId = agencia_id;
            const empresaValue = empresa || document.getElementById('empresa').value || 'todas';
            
            let url = `/ventas-lotobet-dashboard/data?fecha_inicio=${fecha_inicio}&fecha_fin=${fecha_fin}&empresa=${encodeURIComponent(empresaValue)}`;
            if (agencia_id) {
                url += `&agencia_id=${agencia_id}`;
            }

            Swal.fire({
                title: agencia_id ? `Cargando datos de agencia ${agencia_id}...` : 'Cargando datos...',
                text: 'Por favor espera mientras se consultan las ventas.',
                allowOutsideClick: false,
                showConfirmButton: false,
                willOpen: () => {
                    Swal.showLoading();
                }
            });

            fetch(url)
                .then(response => response.json())
                .then(data => {
                    Swal.close();
                    if (!agencia_id) {
                        agenciasData = Array.isArray(data.agencias) ? data.agencias : [];
                        agenciasCeroData = Array.isArray(data.agencias_cero) ? data.agencias_cero : [];
                    }

                    // Mostrar/ocultar badge de agencia
                    if (agencia_id) {
                        badgeAgencia.style.display = 'inline-block';
                        badgeAgenciaText.textContent = agencia_id;
                        limpiarAgenciaBtn.style.display = 'inline-flex';
                    } else {
                        badgeAgencia.style.display = 'none';
                        badgeAgenciaText.textContent = '';
                        limpiarAgenciaBtn.style.display = 'none';
                    }

                    // KPIs
                    document.getElementById('kpi-total').textContent = formatCurrency(data.kpis.total);
                    document.getElementById('kpi-transacciones').textContent = data.kpis.transacciones;
                    document.getElementById('kpi-ticket').textContent = formatCurrency(data.kpis.ticket_promedio);
                    if (!agencia_id) {
                        document.getElementById('kpi-agencias').textContent = data.kpis.total_agencias;
                        document.getElementById('kpi-agencias-cero').textContent = data.kpis.agencias_en_cero ?? 0;
                    }

                    // Cards por tipo
                    const coloresCards = ['#FF6384', '#36A2EB', '#FFCE56', '#4BC0C0', '#9966FF', '#FF9F40'];
                    const containerCards = document.getElementById('cards-container');
                    containerCards.innerHTML = '';
                    
                    data.tabla.forEach((item, index) => {
                        const color = coloresCards[index % coloresCards.length];
                        const card = document.createElement('div');
                        card.className = 'col-12 col-sm-6 col-lg-4 tipo-card-wrap';
                        card.innerHTML = `
                            <div class="card shadow-sm tipo-card" style="border-left: 5px solid ${color};">
                                <div class="card-body d-flex flex-column">
                                    <h5 class="card-title" style="color: ${color}; margin-bottom: 0.75rem;">${item.tipo}</h5>
                                    <div class="mb-2">
                                        <small class="text-muted d-block" style="font-size: 0.8rem;">Total Vendido</small>
                                        <h5 class="text-primary mb-0" style="word-break: break-word;">${formatCurrency(item.total)}</h5>
                                    </div>
                                    <div class="row g-2 mt-2">
                                        <div class="col-6">
                                            <small class="text-muted d-block" style="font-size: 0.75rem;">Transacciones</small>
                                            <p class="mb-0" style="font-weight: 600; font-size: 0.95rem;">${item.transacciones}</p>
                                        </div>
                                        <div class="col-6">
                                            <small class="text-muted d-block" style="font-size: 0.75rem;">% del Total</small>
                                            <p class="mb-0" style="font-weight: 600; font-size: 0.95rem;">${item.porcentaje.toFixed(2)}%</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        `;
                        containerCards.appendChild(card);
                    });

                    // Chart Diario (LÃ­nea - Totales)
                    if (chartDiarioInstance) {
                        chartDiarioInstance.destroy();
                        chartDiarioInstance = null;
                    }
                    const ctxDiario = document.getElementById('chart-diario').getContext('2d');
                    
                    // Crear datasets base
                    let datasetsLinea = [{
                        data: data.chart_diario.values,
                        backgroundColor: 'rgba(54, 162, 235, 0.2)',
                        borderColor: 'rgba(54, 162, 235, 1)',
                        borderWidth: 2,
                        fill: true,
                        pointBackgroundColor: data.chart_diario.values.map(v => v > data.chart_diario.promedio_mes_anterior ? 'red' : 'blue'),
                        pointBorderColor: data.chart_diario.values.map(v => v > data.chart_diario.promedio_mes_anterior ? 'red' : 'blue'),
                        label: 'Ventas Diarias'
                    }, {
                        data: Array(data.chart_diario.labels.length).fill(data.chart_diario.promedio_mes_anterior),
                        borderColor: 'orange',
                        borderWidth: 3,
                        fill: false,
                        pointRadius: 0,
                        label: `Promedio Diario Mes Anterior: ${formatCurrency(data.chart_diario.promedio_mes_anterior)}`
                    }];
                    
                    chartDiarioInstance = new Chart(ctxDiario, {
                        type: 'line',
                        data: {
                            labels: data.chart_diario.labels,
                            datasets: datasetsLinea
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: {
                                legend: {
                                    position: 'top',
                                },
                                tooltip: {
                                    callbacks: {
                                        label: function(context) {
                                            return formatCurrency(context.parsed.y);
                                        }
                                    }
                                }
                            },
                            scales: {
                                x: {
                                    type: 'category'
                                },
                                y: {
                                    beginAtZero: true,
                                    ticks: {
                                        callback: function(value) {
                                            return formatCurrency(value);
                                        }
                                    }
                                }
                            }
                        }
                    });

                    // Chart Diario Barras (Separado por Tipo)
                    if (chartDiarioBarInstance) {
                        chartDiarioBarInstance.destroy();
                        chartDiarioBarInstance = null;
                    }
                    const ctxDiarioBar = document.getElementById('chart-diario-bar').getContext('2d');
                    chartDiarioBarInstance = new Chart(ctxDiarioBar, {
                        type: 'bar',
                        data: {
                            labels: data.chart_diario_tipos.labels,
                            datasets: data.chart_diario_tipos.datasets
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: {
                                legend: {
                                    position: 'top',
                                },
                                tooltip: {
                                    callbacks: {
                                        label: function(context) {
                                            return context.dataset.label + ': ' + formatCurrency(context.parsed.y);
                                        }
                                    }
                                }
                            },
                            scales: {
                                x: {
                                    type: 'category',
                                    stacked: false
                                },
                                y: {
                                    beginAtZero: true,
                                    stacked: false,
                                    ticks: {
                                        callback: function(value) {
                                            return formatCurrency(value);
                                        }
                                    }
                                }
                            }
                        }
                    });

                })
                .catch(error => {
                    Swal.close();
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Hubo un problema al cargar los datos. IntÃ©ntalo de nuevo.',
                    });
                    console.error('Error:', error);
                });
        }

        document.getElementById('filtrar-btn').addEventListener('click', function() {
            const fecha_inicio = document.getElementById('fecha_inicio').value;
            const fecha_fin = document.getElementById('fecha_fin').value;
            const empresa = document.getElementById('empresa').value;
            loadData(fecha_inicio, fecha_fin, null, empresa);
        });

        limpiarAgenciaBtn.addEventListener('click', function() {
            if (!currentAgenciaId) {
                return;
            }
            const fecha_inicio = document.getElementById('fecha_inicio').value;
            const fecha_fin = document.getElementById('fecha_fin').value;
            const empresa = document.getElementById('empresa').value;
            currentAgenciaId = null;
            loadData(fecha_inicio, fecha_fin, null, empresa);
        });

        // Evento para ver detalle de agencias
        document.getElementById('btn-ver-agencias').addEventListener('click', function() {
            if (agenciasData.length === 0) {
                Swal.fire({
                    icon: 'info',
                    title: 'Sin datos',
                    text: 'No hay agencias con ventas en el rango de fechas seleccionado.',
                });
                return;
            }

            // Limpiar y repoblar tabla de agencias
            agenciasTableInstance.clear().rows.add(agenciasData.map(agencia => [
                agencia.agencia_id,
                formatCurrency(agencia.total),
                `<button class="btn btn-sm btn-primary btn-filtrar-agencia" data-agencia-id="${agencia.agencia_id}">Ver GrÃ¡ficos</button>`
            ])).draw();

            // Mostrar modal
            const modal = new bootstrap.Modal(document.getElementById('modalAgencias'));
            modal.show();
        });
        document.getElementById('btn-ver-agencias-cero').addEventListener('click', function() {
            if (agenciasCeroData.length === 0) {
                Swal.fire({
                    icon: 'info',
                    title: 'Sin agencias en cero',
                    text: 'No hay agencias en cero en el rango de fechas seleccionado.',
                });
                return;
            }

            agenciasCeroTableInstance.clear().rows.add(agenciasCeroData.map(agencia => [
                agencia.agencia_id,
                agencia.nombre_agencia || agencia.agencia_id,
                formatCurrency(agencia.total ?? 0),
                `<button class="btn btn-sm btn-primary btn-filtrar-agencia" data-agencia-id="${agencia.agencia_id}">Ver GrÃ¡ficos</button>`
            ])).draw();

            const modalCero = new bootstrap.Modal(document.getElementById('modalAgenciasCero'));
            modalCero.show();
        });
        document.getElementById('btn-export-agencias-cero-dia').addEventListener('click', function() {
            const fecha_inicio = document.getElementById('fecha_inicio').value;
            const fecha_fin = document.getElementById('fecha_fin').value;
            const empresa = document.getElementById('empresa').value;
            const params = new URLSearchParams({
                fecha_inicio,
                fecha_fin,
                empresa,
                plataforma: 'bet',
            });

            window.location.href = `/ventas-lotobet-dashboard/export-agencias-cero-por-dia?${params.toString()}`;
        });

        // Evento para filtrar por agencia (delegado)
        document.addEventListener('click', function(e) {
            if (e.target.classList.contains('btn-filtrar-agencia')) {
                const agenciaId = e.target.getAttribute('data-agencia-id');
                const fecha_inicio = document.getElementById('fecha_inicio').value;
                const fecha_fin = document.getElementById('fecha_fin').value;
                const empresa = document.getElementById('empresa').value;

                // Cerrar modal
                const modal = bootstrap.Modal.getInstance(document.getElementById('modalAgencias'));
                if (modal) {
                    modal.hide();
                }
                const modalCero = bootstrap.Modal.getInstance(document.getElementById('modalAgenciasCero'));
                if (modalCero) {
                    modalCero.hide();
                }

                // Cargar datos de la agencia
                loadData(fecha_inicio, fecha_fin, agenciaId, empresa);
            }
        });

        // Cargar datos iniciales
        const fecha_inicio = document.getElementById('fecha_inicio').value;
        const fecha_fin = document.getElementById('fecha_fin').value;
        // loadData(fecha_inicio, fecha_fin);
    </script>
@endsection

