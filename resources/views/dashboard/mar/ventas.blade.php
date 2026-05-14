@extends('app')

@section('title', 'Dashboard Ventas MAR')

@section('content')
    <style>
        /* Mejorar tablas en móvil */
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
            
            /* Ocultar columnas menos importantes en móvil */
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

		.mar-tipo-card {
			height: auto;
		}

		.mar-tipo-card .card-body {
			padding: 0.85rem;
		}

		.mar-tipo-titulo {
			font-size: 0.95rem;
			line-height: 1.2;
			margin-bottom: 0.5rem;
		}

		.mar-tipo-total {
			font-size: 1.2rem;
			line-height: 1.1;
			margin-bottom: 0;
		}

		.mar-tipo-label {
			font-size: 0.72rem;
			margin-bottom: 0.1rem;
		}

		.mar-tipo-dato {
			font-size: 0.9rem;
			font-weight: 600;
			margin-bottom: 0;
		}

		.mar-tipo-grid {
			display: grid;
			grid-template-columns: repeat(2, minmax(0, 1fr));
			gap: 0.4rem;
			margin-top: 0.5rem;
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

			.mar-tipo-total {
				font-size: 1.05rem;
			}
        }
    </style>
	<div class="main-content">
		<div class="page-content">
			<div class="container-fluid">
				<div class="row">
					<div class="col-12">
						<div
							class="d-flex justify-content-between align-items-start align-items-lg-center flex-column flex-lg-row gap-3 text-center text-lg-start mb-3">
							<div>
								<h1 class="h3 mb-1">Dashboard Ventas MAR</h1>
								<ol class="breadcrumb m-0 justify-content-center justify-content-lg-start">
									<li class="breadcrumb-item"><a href="{{ route('inicio.index') }}">Inicio</a></li>
									<li class="breadcrumb-item"><a href="{{ route('dashboard.index') }}">Dashboard</a></li>
									<li class="breadcrumb-item active">Mar Ventas</li>
								</ol>
							</div>
							<div class="d-flex align-items-center gap-2 flex-wrap justify-content-center">
								<span id="badge-agencia" class="badge bg-primary fs-6" style="display: none; padding: 8px 12px;">
									Agencia: <span id="agencia-id-badge" style="font-weight: bold;"></span>
									<span id="agencia-banca-wrapper" class="ms-2" style="display: none;">
										Banca: <span id="agencia-banca-badge" style="font-weight: bold;"></span>
									</span>
								</span>
								<button id="btn-limpiar-agencia" class="btn btn-outline-secondary btn-sm" style="display: none;">
									Limpiar agencia
								</button>
							</div>
						</div>
					</div>
				</div>

				<div class="row mb-4 g-3">
					<div class="col-12 col-md-4 col-lg-3">
						<label for="fecha_inicio" class="form-label">Fecha Inicio</label>
						<input type="date" id="fecha_inicio" class="form-control" value="{{ date('Y-m-d') }}">
					</div>
					<div class="col-12 col-md-4 col-lg-3">
						<label for="fecha_fin" class="form-label">Fecha Fin</label>
						<input type="date" id="fecha_fin" class="form-control" value="{{ date('Y-m-d') }}">
					</div>
					<div class="col-12 col-md-4 col-lg-3 col-xl-2 d-flex align-items-end">
						<button id="filtrar-btn" class="btn btn-primary w-100">Filtrar</button>
					</div>
					<div class="col-12 col-xl-4 d-flex align-items-end justify-content-end">
						<button id="btn-ver-agencias" class="btn btn-outline-warning">Ver agencias con ventas</button>
					</div>
				</div>

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
							</div>
						</div>
					</div>
				</div>

				<div class="row mb-5">
					<div class="col-12">
						<h5 class="mb-3">Ventas por Tipo</h5>
					</div>
					<div id="cards-container" class="row g-3 w-100 mx-0"></div>
				</div>

				<div class="row mb-4 mt-3">
					<div class="col-md-12">
						<div class="card shadow-sm">
							<div class="card-header">
								<h5>Ventas por Día (Línea)</h5>
							</div>
							<div class="card-body">
								<canvas id="chart-diario" style="height: 250px; max-height: 250px;"></canvas>
							</div>
						</div>
					</div>
					<div class="col-md-12">
						<div class="card shadow-sm">
							<div class="card-header">
								<h5>Ventas por Día (Barras)</h5>
							</div>
							<div class="card-body">
								<canvas id="chart-diario-bar" style="height: 250px; max-height: 250px;"></canvas>
							</div>
						</div>
					</div>
				</div>

				<div class="row">
					<div class="col-12">
						<div class="card shadow-sm">
							<div class="card-header">
								<h5>Detalle por Tipo</h5>
							</div>
							<div class="card-body">
								<div class="table-responsive">
									<table id="tabla-ventas" class="table table-striped">
										<thead>
											<tr>
												<th>Tipo</th>
												<th>Total Ventas</th>
												<th>Transacciones</th>
												<th>Promedio</th>
												<th>% del Total</th>
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
@endsection

<div class="modal fade" id="modalAgencias" tabindex="-1" aria-labelledby="modalAgenciasLabel" aria-hidden="true">
	<div class="modal-dialog modal-lg">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title" id="modalAgenciasLabel">Agencias con Ventas MAR</h5>
				<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
			</div>
			<div class="modal-body">
				<div class="table-responsive">
					<table id="tabla-agencias" class="table w-100 table-striped table-hover">
						<thead>
							<tr>
								<th>ID Agencia</th>
								<th>Banca</th>
								<th>Total Ventas</th>
								<th>Acción</th>
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
		let tableInstance = null;
		let agenciasTableInstance = null;
		let agenciasData = [];
		let currentAgenciaId = null;
		const badgeAgencia = document.getElementById('badge-agencia');
		const badgeAgenciaText = document.getElementById('agencia-id-badge');
		const badgeAgenciaBancaWrapper = document.getElementById('agencia-banca-wrapper');
		const badgeAgenciaBanca = document.getElementById('agencia-banca-badge');
		const limpiarAgenciaBtn = document.getElementById('btn-limpiar-agencia');

		const tablaVentasColumns = [
			{ title: 'Tipo' },
			{ title: 'Total Ventas' },
			{ title: 'Transacciones' },
			{ title: 'Promedio' },
			{ title: '% del Total' }
		];

		const tablaAgenciasColumns = [
			{ title: 'ID Agencia' },
			{ title: 'Banca' },
			{ title: 'Total Ventas' },
			{ title: 'Acción', orderable: false, searchable: false }
		];

		tableInstance = $('#tabla-ventas').DataTable({
			data: [],
			columns: tablaVentasColumns,
			paging: true,
			searching: true,
			ordering: true,
			responsive: true,
			scrollX: true,
			columnDefs: [
				{ targets: [2, 3, 4], visible: $(window).width() > 768 }
			]
		});

		agenciasTableInstance = $('#tabla-agencias').DataTable({
			data: [],
			columns: tablaAgenciasColumns,
			paging: true,
			searching: true,
			ordering: true,
			responsive: true,
			scrollX: true,
			columnDefs: [
				{ targets: [2], visible: $(window).width() > 768 }
			]
		});

		function formatCurrency(value) {
			return new Intl.NumberFormat('es-DO', {
				style: 'currency',
				currency: 'DOP'
			}).format(value || 0);
		}

		function loadData(fecha_inicio, fecha_fin, agencia_id = null) {
			currentAgenciaId = agencia_id;
			let url = `/ventas-mar-dashboard/data?fecha_inicio=${fecha_inicio}&fecha_fin=${fecha_fin}`;
			if (agencia_id) {
				url += `&agencia_id=${agencia_id}`;
			}

			Swal.fire({
				title: agencia_id ? `Cargando agencia ${agencia_id}...` : 'Cargando ventas MAR...',
				text: 'Consultando información, por favor espere.',
				allowOutsideClick: false,
				showConfirmButton: false,
				didOpen: () => Swal.showLoading()
			});

			fetch(url)
				.then(response => {
					if (!response.ok) {
						throw new Error('Error consultando datos del dashboard');
					}
					return response.json();
				})
				.then(data => {
					Swal.close();

					if (agencia_id) {
						const agenciaInfo = data.agencia || null;
						badgeAgencia.style.display = 'inline-block';
						badgeAgenciaText.textContent = agenciaInfo?.agencia_id || agencia_id;
						if (agenciaInfo && agenciaInfo.banca) {
							badgeAgenciaBanca.textContent = agenciaInfo.banca;
							badgeAgenciaBancaWrapper.style.display = 'inline';
						} else {
							badgeAgenciaBanca.textContent = '';
							badgeAgenciaBancaWrapper.style.display = 'none';
						}
						limpiarAgenciaBtn.style.display = 'inline-flex';
					} else {
						badgeAgencia.style.display = 'none';
						badgeAgenciaText.textContent = '';
						badgeAgenciaBanca.textContent = '';
						badgeAgenciaBancaWrapper.style.display = 'none';
						limpiarAgenciaBtn.style.display = 'none';
						agenciasData = Array.isArray(data.agencias) ? data.agencias : [];
					}

					document.getElementById('kpi-total').textContent = formatCurrency(data.kpis.total);
					document.getElementById('kpi-transacciones').textContent = data.kpis.transacciones;
					document.getElementById('kpi-ticket').textContent = formatCurrency(data.kpis.ticket_promedio);
					if (!agencia_id) {
						document.getElementById('kpi-agencias').textContent = data.kpis.total_agencias;
					}

					const coloresCards = ['#FF6384', '#36A2EB', '#FFCE56'];
					const cardsContainer = document.getElementById('cards-container');
					cardsContainer.innerHTML = '';
					data.tabla.forEach((item, index) => {
						const color = coloresCards[index % coloresCards.length];
						const card = document.createElement('div');
						card.className = 'col-12 col-md-6 mb-3';
						card.innerHTML = `
							<div class="card shadow-sm mar-tipo-card" style="border-left: 4px solid ${color};">
								<div class="card-body d-flex flex-column">
									<h6 class="card-title mar-tipo-titulo" style="color: ${color};">${item.tipo}</h6>
									<div>
										<small class="text-muted d-block mar-tipo-label">Total Vendido</small>
										<div class="text-primary mar-tipo-total" style="word-break: break-word;">${formatCurrency(item.total)}</div>
									</div>
									<div class="mar-tipo-grid">
										<div>
											<small class="text-muted d-block mar-tipo-label">Transacciones</small>
											<p class="mar-tipo-dato">${item.transacciones}</p>
										</div>
										<div>
											<small class="text-muted d-block mar-tipo-label">% del Total</small>
											<p class="mar-tipo-dato">${item.porcentaje.toFixed(2)}%</p>
										</div>
									</div>
								</div>
							</div>
						`;
						cardsContainer.appendChild(card);
					});

					if (chartDiarioInstance) {
						chartDiarioInstance.destroy();
						chartDiarioInstance = null;
					}

					const ctxLine = document.getElementById('chart-diario').getContext('2d');
					const promedioAnterior = data.chart_diario.promedio_mes_anterior || 0;
					const puntoColores = data.chart_diario.values.map(value => value >= promedioAnterior ? '#ff6b6b' : '#1c84c6');

					chartDiarioInstance = new Chart(ctxLine, {
						type: 'line',
						data: {
							labels: data.chart_diario.labels,
							datasets: [
								{
									label: 'Total diario',
									data: data.chart_diario.values,
									borderColor: '#36A2EB',
									backgroundColor: 'rgba(54, 162, 235, 0.2)',
									borderWidth: 2,
									fill: true,
									pointBackgroundColor: puntoColores,
									pointBorderColor: puntoColores
								},
								{
									label: `Promedio Mes Anterior: ${formatCurrency(promedioAnterior)}`,
									data: Array(data.chart_diario.labels.length).fill(promedioAnterior),
									borderColor: '#ff9f40',
									borderWidth: 3,
									borderDash: [6, 6],
									pointRadius: 0,
									fill: false
								}
							]
						},
						options: {
							responsive: true,
							maintainAspectRatio: false,
							plugins: {
								legend: { position: 'top' },
								tooltip: {
									callbacks: {
										label: (context) => formatCurrency(context.parsed.y)
									}
								}
							},
							scales: {
								y: {
									beginAtZero: true,
									ticks: {
										callback: value => formatCurrency(value)
									}
								}
							}
						}
					});

					if (chartDiarioBarInstance) {
						chartDiarioBarInstance.destroy();
						chartDiarioBarInstance = null;
					}

					const ctxBar = document.getElementById('chart-diario-bar').getContext('2d');
					chartDiarioBarInstance = new Chart(ctxBar, {
						type: 'bar',
						data: {
							labels: data.chart_diario_tipos.labels,
							datasets: data.chart_diario_tipos.datasets
						},
						options: {
							responsive: true,
							maintainAspectRatio: false,
							plugins: {
								legend: { position: 'top' },
								tooltip: {
									callbacks: {
										label: (context) => `${context.dataset.label}: ${formatCurrency(context.parsed.y)}`
									}
								}
							},
							scales: {
								y: {
									beginAtZero: true,
									ticks: {
										callback: value => formatCurrency(value)
									}
								}
							}
						}
					});

					tableInstance.clear().rows.add(data.tabla.map(row => [
						row.tipo,
						formatCurrency(row.total),
						row.transacciones,
						formatCurrency(row.promedio),
						`${row.porcentaje.toFixed(2)}%`
					])).draw();
				})
				.catch(error => {
					Swal.close();
					Swal.fire({
						icon: 'error',
						title: 'Error',
						text: 'No se pudieron cargar los datos. Intente nuevamente.'
					});
					console.error('Error cargando dashboard MAR:', error);
				});
		}

		document.getElementById('filtrar-btn').addEventListener('click', () => {
			const fecha_inicio = document.getElementById('fecha_inicio').value;
			const fecha_fin = document.getElementById('fecha_fin').value;
			currentAgenciaId = null;
			loadData(fecha_inicio, fecha_fin);
		});

		limpiarAgenciaBtn.addEventListener('click', () => {
			if (!currentAgenciaId) {
				return;
			}
			const fecha_inicio = document.getElementById('fecha_inicio').value;
			const fecha_fin = document.getElementById('fecha_fin').value;
			currentAgenciaId = null;
			loadData(fecha_inicio, fecha_fin);
		});

		document.getElementById('btn-ver-agencias').addEventListener('click', () => {
			if (!agenciasData.length) {
				Swal.fire({
					icon: 'info',
					title: 'Sin datos',
					text: 'No hay agencias disponibles en el rango seleccionado.'
				});
				return;
			}

			agenciasTableInstance.clear().rows.add(agenciasData.map(agencia => [
				agencia.agencia_id,
				agencia.banca || '',
				formatCurrency(agencia.total),
				`<button class="btn btn-sm btn-primary btn-filtrar-agencia" data-agencia-id="${agencia.agencia_id}">Ver detalle</button>`
			])).draw();

			const modal = new bootstrap.Modal(document.getElementById('modalAgencias'));
			modal.show();
		});

		document.addEventListener('click', (event) => {
			if (event.target.classList.contains('btn-filtrar-agencia')) {
				const agenciaId = event.target.getAttribute('data-agencia-id');
				const fecha_inicio = document.getElementById('fecha_inicio').value;
				const fecha_fin = document.getElementById('fecha_fin').value;
				currentAgenciaId = agenciaId;

				const modal = bootstrap.Modal.getInstance(document.getElementById('modalAgencias'));
				modal.hide();

				loadData(fecha_inicio, fecha_fin, agenciaId);
			}
		});

		const fecha_inicio = document.getElementById('fecha_inicio').value;
		const fecha_fin = document.getElementById('fecha_fin').value;
		loadData(fecha_inicio, fecha_fin);
	</script>
@endsection
