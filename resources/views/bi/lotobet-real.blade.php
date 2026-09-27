@extends('app')

@section('content')
    <style>
        .bi-board { --bi-line: #202020; --bi-muted: #747474; --bi-accent: #ead986; background: #fff; color: #171717; padding: 18px; }
        .bi-panel { border: 1.5px solid var(--bi-line); border-radius: 11px; background: #fff; padding: 10px 12px; height: 100%; }
        .bi-panel h5 { color: #111; font-size: .86rem; font-weight: 700; margin: 0 0 7px; }
        .bi-value { font-size: 1.42rem; line-height: 1.12; font-weight: 500; white-space: nowrap; }
        .bi-value-sm { font-size: 1.12rem; }
        .bi-caption { color: var(--bi-muted); font-size: .78rem; line-height: 1.2; }
        .bi-divider { border-top: 1px solid #d5d5d5; margin: 7px 0 3px; }
        .bi-variation { font-size: 1rem; font-weight: 600; }
        .bi-variation.positive { color: #2f7d55; }
        .bi-variation.negative { color: #b33a2f; }
        .bi-variation.neutral { color: #555; }
        .bi-filter-label { color: #111; font-size: .78rem; font-weight: 700; margin-bottom: 5px; }
        .bi-board .form-select, .bi-board .form-control { border-color: #ddd; border-radius: 0; font-size: .75rem; min-height: 34px; }
        .bi-stat-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 5px 14px; }
        .bi-stat-grid strong { display: block; font-size: 1rem; font-weight: 500; }
        .bi-logo { font-size: 3rem; font-weight: 900; letter-spacing: -.25rem; line-height: .9; text-align: center; }
        .bi-logo span { color: #e9b62d; }
        .bi-logo small { display: block; color: #222; font-size: .7rem; letter-spacing: .02rem; margin-top: 8px; }
        .bi-action { border: 1.5px solid #202020; border-radius: 10px; background: #fff; color: #111; font-weight: 700; width: 100%; padding: 8px; }
        .bi-chart-wrap { min-height: 330px; margin-top: 24px; }
        .bi-loading { opacity: .55; pointer-events: none; }
        @media (max-width: 1199.98px) { .bi-logo { font-size: 2.2rem; } }
    </style>

    <div class="main-content">
        <div class="page-content">
            <div class="container-fluid">
                <div class="card border-0 shadow-sm mb-0">
                    <div class="card-body bi-board" id="biBoard">
                        <div class="row g-2 align-items-stretch">
                            <div class="col-xl-2 col-lg-4">
                                <div class="bi-panel">
                                    <h5>Terminales</h5>
                                    <div class="bi-stat-grid">
                                        <div><strong id="terminalesActivas">0</strong><span class="bi-caption">Activas</span></div>
                                        <div><strong id="terminalesInactivas">0</strong><span class="bi-caption">Inactivas</span></div>
                                        <div><strong id="terminalesVendieron">0</strong><span class="bi-caption">Vendieron</span></div>
                                        <div><strong id="terminalesSinVentas">0</strong><span class="bi-caption">Sin ventas</span></div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-xl-8 col-lg-8">
                                <div class="row g-2">
                                    <div class="col-md-2">
                                        <div class="bi-filter-label">Sistema</div>
                                        <select class="form-select" disabled><option>Lotobet Real</option></select>
                                    </div>
                                    <div class="col-md-2">
                                        <label class="bi-filter-label" for="filtroGrupo">Grupo</label>
                                        <select class="form-select bi-filter" id="filtroGrupo"><option value="">Todos</option>@foreach ($filtros['grupos'] as $item)<option value="{{ $item }}">{{ $item }}</option>@endforeach</select>
                                    </div>
                                    <div class="col-md-2">
                                        <label class="bi-filter-label" for="filtroCentral">Central</label>
                                        <select class="form-select bi-filter" id="filtroCentral"><option value="">Todas</option>@foreach ($filtros['centrales'] as $item)<option value="{{ $item }}">{{ $item }}</option>@endforeach</select>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="bi-filter-label" for="filtroGerente">Gerente de servicio</label>
                                        <select class="form-select bi-filter" id="filtroGerente"><option value="">Todos</option>@foreach ($filtros['gerentes'] as $item)<option value="{{ $item }}">{{ $item }}</option>@endforeach</select>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="bi-filter-label" for="filtroPago">Pago</label>
                                        <select class="form-select bi-filter" id="filtroPago"><option value="">Todos</option>@foreach ($filtros['tiposPago'] as $item)<option value="{{ $item }}">{{ $item }}</option>@endforeach</select>
                                    </div>
                                    <div class="col-md-3 mt-3">
                                        <label class="bi-filter-label" for="fechaDesde">Desde</label>
                                        <input type="date" class="form-control bi-filter" id="fechaDesde" value="{{ $ultimaFecha }}">
                                    </div>
                                    <div class="col-md-3 mt-3">
                                        <label class="bi-filter-label" for="fechaHasta">Hasta</label>
                                        <input type="date" class="form-control bi-filter" id="fechaHasta" value="{{ $ultimaFecha }}">
                                    </div>
                                    <div class="col-md-3 mt-3 d-flex align-items-end">
                                        <button type="button" class="btn btn-dark btn-sm w-100" id="btnConsultar">Consultar</button>
                                    </div>
                                </div>
                            </div>
                            <div class="col-xl-2 d-none d-xl-flex align-items-center justify-content-center">
                                <div class="bi-logo">BS<span>H</span><small>BUSINESS SUPPORT HUB</small></div>
                            </div>
                        </div>

                        <div class="row g-2 mt-0">
                            <div class="col-xl-2 col-md-6"><div class="bi-panel"><h5>Venta global</h5><div class="bi-value" id="ventaGlobal">$0</div><div class="bi-caption">Venta global</div><div class="bi-value bi-value-sm mt-2" id="ventaPromedio">$0</div><div class="bi-caption">Promedio x terminal</div><div class="bi-caption mt-3">Periodo vs semana pasada</div><div class="bi-divider"></div><div class="bi-variation neutral" id="ventaVariacion">—</div></div></div>
                            <div class="col-xl-2 col-md-6"><div class="bi-panel"><h5>Premios</h5><div class="bi-value" id="premiosGlobal">$0</div><div class="bi-caption">Premios globales</div><div class="bi-value bi-value-sm mt-2" id="premiosPromedio">$0</div><div class="bi-caption">Promedio x terminal</div><div class="bi-caption mt-3">Periodo vs semana pasada</div><div class="bi-divider"></div><div class="bi-variation neutral" id="premiosVariacion">—</div></div></div>
                            <div class="col-xl-8">
                                <div class="row g-2">
                                    @foreach ([['tradicional', 'Tradicional'], ['recargas', 'Recargas'], ['raza', 'Raza'], ['no_tradicional', 'No tradicionales'], ['paqueticos', 'Paqueticos'], ['resultado_bruto', 'Resultado bruto']] as [$key, $label])
                                        <div class="col-lg-4 col-md-6">
                                            <div class="bi-panel">
                                                <h5>{{ $label }}</h5>
                                                <div class="d-flex justify-content-between gap-3">
                                                    <div><div class="bi-value bi-value-sm" id="{{ $key }}Total">$0</div><div class="bi-caption">Ventas</div></div>
                                                    <div><div class="bi-value bi-value-sm" id="{{ $key }}Promedio">$0</div><div class="bi-caption">Promedio x terminal</div></div>
                                                </div>
                                                <div class="bi-caption mt-2">Periodo vs semana pasada</div><div class="bi-divider"></div><div class="bi-variation neutral" id="{{ $key }}Variacion">—</div>
                                            </div>
                                        </div>
                                    @endforeach
                                    <div class="col-lg-4 col-md-6 ms-auto"><button type="button" class="bi-action" id="btnDetalle">Filtro detalle por terminal</button></div>
                                </div>
                            </div>
                        </div>

                        <div class="bi-chart-wrap">
                            <h5 class="fw-bold mb-0">Ventas últimos 7 días</h5>
                            <div id="graficoVentasBi"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="{{ asset('libs/apexcharts/apexcharts.min.js') }}"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const board = document.getElementById('biBoard');
            let chart = null;
            const currency = new Intl.NumberFormat('es-DO', { style: 'currency', currency: 'DOP', maximumFractionDigits: 0 });
            const integer = new Intl.NumberFormat('es-DO');
            const money = value => currency.format(Number(value || 0)).replace('DOP', '$');

            function variation(id, value) {
                const element = document.getElementById(id);
                element.className = 'bi-variation ' + (value === null ? 'neutral' : value >= 0 ? 'positive' : 'negative');
                element.textContent = value === null ? '—' : `${value >= 0 ? '▲' : '▼'} ${Math.abs(value).toFixed(0)} %`;
            }

            function metric(prefix, data) {
                document.getElementById(`${prefix}Total`).textContent = money(data.total);
                document.getElementById(`${prefix}Promedio`).textContent = money(data.promedio_terminal);
                variation(`${prefix}Variacion`, data.variacion);
            }

            function render(data) {
                document.getElementById('terminalesActivas').textContent = integer.format(data.terminales.activas);
                document.getElementById('terminalesInactivas').textContent = integer.format(data.terminales.inactivas);
                document.getElementById('terminalesVendieron').textContent = integer.format(data.terminales.vendieron);
                document.getElementById('terminalesSinVentas').textContent = integer.format(data.terminales.sin_ventas);
                document.getElementById('ventaGlobal').textContent = money(data.venta_global.total);
                document.getElementById('ventaPromedio').textContent = money(data.venta_global.promedio_terminal);
                variation('ventaVariacion', data.venta_global.variacion);
                document.getElementById('premiosGlobal').textContent = money(data.premios.total);
                document.getElementById('premiosPromedio').textContent = money(data.premios.promedio_terminal);
                variation('premiosVariacion', data.premios.variacion);
                ['tradicional', 'recargas', 'raza', 'no_tradicional', 'paqueticos'].forEach(key => metric(key, data.categorias[key]));
                metric('resultado_bruto', data.resultado_bruto);

                if (typeof ApexCharts === 'undefined') {
                    document.getElementById('graficoVentasBi').innerHTML = '<div class="text-center text-muted py-5">No fue posible cargar el gráfico.</div>';
                    return;
                }

                if (chart) chart.destroy();
                chart = new ApexCharts(document.querySelector('#graficoVentasBi'), {
                    chart: { type: 'area', height: 315, toolbar: { show: false }, fontFamily: 'inherit' },
                    series: [{ name: 'Ventas', data: data.grafico.map(item => item.total) }],
                    xaxis: { categories: data.grafico.map(item => item.dia), axisBorder: { show: false }, axisTicks: { show: false } },
                    yaxis: { labels: { formatter: value => `$${(value / 1000000).toFixed(0)} mill.` } },
                    colors: ['#dfc963'], stroke: { width: 2, curve: 'straight' }, fill: { type: 'solid', opacity: .32 },
                    dataLabels: { enabled: true, formatter: value => money(value), background: { enabled: true, foreColor: '#fff', borderRadius: 3, padding: 4, opacity: 1, borderWidth: 0 }, style: { fontSize: '10px' } },
                    grid: { borderColor: '#e6e6e6', strokeDashArray: 2 }, legend: { show: false },
                    tooltip: { y: { formatter: value => money(value) } }
                });
                chart.render();
            }

            async function consultar() {
                const params = new URLSearchParams({
                    fecha_desde: document.getElementById('fechaDesde').value,
                    fecha_hasta: document.getElementById('fechaHasta').value,
                    grupo: document.getElementById('filtroGrupo').value,
                    central: document.getElementById('filtroCentral').value,
                    gerente: document.getElementById('filtroGerente').value,
                    tipo_pago: document.getElementById('filtroPago').value
                });
                board.classList.add('bi-loading');
                try {
                    const response = await fetch(`{{ route('bi.lotobet-real.data') }}?${params.toString()}`, { headers: { Accept: 'application/json' } });
                    if (!response.ok) throw new Error('No fue posible consultar el tablero.');
                    render(await response.json());
                } catch (error) {
                    Swal.fire('Error', error.message, 'error');
                } finally {
                    board.classList.remove('bi-loading');
                }
            }

            document.getElementById('btnConsultar').addEventListener('click', consultar);
            document.getElementById('btnDetalle').addEventListener('click', () => Swal.fire('Próximamente', 'El detalle por terminal se incorporará en la siguiente fase.', 'info'));
            consultar();
        });
    </script>
@endsection
