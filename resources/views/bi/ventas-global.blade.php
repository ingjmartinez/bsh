@extends('app')

@section('content')
    <style>
        .bi-board { --bi-line: #202020; --bi-muted: #747474; background: #fff; color: #171717; padding: 18px; }
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
                                        <label class="bi-filter-label" for="filtroSistema">Sistema</label>
                                        <select class="form-select bi-filter" id="filtroSistema"><option value="">Todos</option>@foreach ($plataformas as $key => $nombre)<option value="{{ $key }}">{{ $nombre }}</option>@endforeach</select>
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
                            @foreach ([['venta', 'Venta global', 'Venta global', 'ventaGlobal'], ['premios', 'Premios', 'Premios globales', 'premiosGlobal'], ['resultado', 'Resultado bruto', 'Ventas - premios', 'resultadoBruto']] as [$prefix, $titulo, $caption, $id])
                                <div class="col-xl-4 col-md-6">
                                    <div class="bi-panel">
                                        <h5>{{ $titulo }}</h5>
                                        <div class="bi-value" id="{{ $prefix }}Total">$0</div><div class="bi-caption">{{ $caption }}</div>
                                        <div class="bi-value bi-value-sm mt-2" id="{{ $prefix }}Promedio">$0</div><div class="bi-caption">Promedio x terminal</div>
                                        <div class="bi-caption mt-3">Periodo vs semana pasada</div><div class="bi-divider"></div>
                                        <div class="bi-variation neutral" id="{{ $prefix }}Variacion">—</div>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <div class="row g-2 mt-0">
                            @foreach ($plataformas as $key => $nombre)
                                <div class="col-xl-4 col-md-6" id="{{ $key }}Card">
                                    <div class="bi-panel">
                                        <h5>{{ $nombre }}</h5>
                                        <div class="d-flex justify-content-between gap-3">
                                            <div><div class="bi-value bi-value-sm" id="{{ $key }}Venta">$0</div><div class="bi-caption">Ventas</div></div>
                                            <div><div class="bi-value bi-value-sm" id="{{ $key }}Premios">$0</div><div class="bi-caption">Premios</div></div>
                                        </div>
                                        <div class="d-flex justify-content-between gap-3 mt-2">
                                            <div><div class="bi-value bi-value-sm" id="{{ $key }}Promedio">$0</div><div class="bi-caption">Promedio x terminal</div></div>
                                            <div><div class="bi-value bi-value-sm" id="{{ $key }}Terminales">0 / 0</div><div class="bi-caption">Vendieron / Activas</div></div>
                                        </div>
                                        <div class="bi-caption mt-2">Periodo vs semana pasada</div><div class="bi-divider"></div>
                                        <div class="bi-variation neutral" id="{{ $key }}Variacion">—</div>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <div class="bi-chart-wrap">
                            <h5 class="fw-bold mb-0">Ventas últimos 7 días por plataforma</h5>
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
            const plataformas = @json(array_keys($plataformas));
            const nombres = @json($plataformas);
            const colores = { real: '#dfc963', lotodom: '#202020', delta: '#8a8a8a' };
            let chart = null;
            const currency = new Intl.NumberFormat('es-DO', { style: 'currency', currency: 'DOP', maximumFractionDigits: 0 });
            const integer = new Intl.NumberFormat('es-DO');
            const money = value => currency.format(Number(value || 0)).replace('DOP', '$');
            const text = (id, value) => { document.getElementById(id).textContent = value; };

            function variation(id, value) {
                const element = document.getElementById(id);
                element.className = 'bi-variation ' + (value === null ? 'neutral' : value >= 0 ? 'positive' : 'negative');
                element.textContent = value === null ? '—' : `${value >= 0 ? '▲' : '▼'} ${Math.abs(value).toFixed(0)} %`;
            }

            function metric(prefix, data) {
                text(`${prefix}Total`, money(data.total));
                text(`${prefix}Promedio`, money(data.promedio_terminal));
                variation(`${prefix}Variacion`, data.variacion);
            }

            function render(data) {
                ['Activas', 'Inactivas', 'Vendieron'].forEach(label => text(`terminales${label}`, integer.format(data.terminales[label.toLowerCase()])));
                text('terminalesSinVentas', integer.format(data.terminales.sin_ventas));
                metric('venta', data.venta_global);
                metric('premios', data.premios);
                metric('resultado', data.resultado_bruto);

                const visibles = data.plataformas.map(item => item.key);
                plataformas.forEach(key => document.getElementById(`${key}Card`).classList.toggle('d-none', !visibles.includes(key)));

                data.plataformas.forEach(item => {
                    text(`${item.key}Venta`, money(item.venta));
                    text(`${item.key}Premios`, money(item.premios));
                    text(`${item.key}Promedio`, money(item.promedio_terminal));
                    text(`${item.key}Terminales`, `${integer.format(item.terminales.vendieron)} / ${integer.format(item.terminales.activas)}`);
                    variation(`${item.key}Variacion`, item.variacion);
                });

                if (typeof ApexCharts === 'undefined') {
                    document.getElementById('graficoVentasBi').innerHTML = '<div class="text-center text-muted py-5">No fue posible cargar el gráfico.</div>';
                    return;
                }

                if (chart) chart.destroy();
                chart = new ApexCharts(document.querySelector('#graficoVentasBi'), {
                    chart: { type: 'bar', stacked: true, height: 315, toolbar: { show: false }, fontFamily: 'inherit' },
                    series: visibles.map(key => ({ name: nombres[key], data: data.grafico.map(item => item[key]) })),
                    xaxis: { categories: data.grafico.map(item => item.dia), axisBorder: { show: false }, axisTicks: { show: false } },
                    yaxis: { labels: { formatter: value => `$${(value / 1000000).toFixed(0)} mill.` } },
                    colors: visibles.map(key => colores[key]),
                    plotOptions: { bar: { columnWidth: '45%' } },
                    dataLabels: { enabled: false },
                    grid: { borderColor: '#e6e6e6', strokeDashArray: 2 },
                    legend: { position: 'top', horizontalAlign: 'left' },
                    tooltip: { shared: true, intersect: false, y: { formatter: value => money(value) } }
                });
                chart.render();
            }

            async function consultar() {
                const params = new URLSearchParams({
                    fecha_desde: document.getElementById('fechaDesde').value,
                    fecha_hasta: document.getElementById('fechaHasta').value,
                    sistema: document.getElementById('filtroSistema').value,
                    grupo: document.getElementById('filtroGrupo').value,
                    central: document.getElementById('filtroCentral').value,
                    gerente: document.getElementById('filtroGerente').value,
                    tipo_pago: document.getElementById('filtroPago').value
                });
                board.classList.add('bi-loading');
                try {
                    const response = await fetch(`{{ route('bi.ventas-global.data') }}?${params.toString()}`, { headers: { Accept: 'application/json' } });
                    if (!response.ok) throw new Error('No fue posible consultar el tablero.');
                    render(await response.json());
                } catch (error) {
                    Swal.fire('Error', error.message, 'error');
                } finally {
                    board.classList.remove('bi-loading');
                }
            }

            document.getElementById('btnConsultar').addEventListener('click', consultar);
            consultar();
        });
    </script>
@endsection
