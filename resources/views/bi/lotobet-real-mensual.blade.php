@extends('app')

@section('content')
    <style>
        .monthly-bi { background: #fff; color: #151515; padding: 18px; }
        .monthly-bi .filter-label { color: #111; display: block; font-size: .8rem; font-weight: 700; margin-bottom: 5px; text-align: center; }
        .monthly-bi .form-select { border-color: #ddd; border-radius: 0; font-size: .76rem; min-height: 34px; }
        .monthly-chart-card { border: 1.5px solid #202020; border-radius: 11px; background: #fff; padding: 8px 10px 2px; }
        .monthly-chart-card + .monthly-chart-card { margin-top: 10px; }
        .monthly-chart { min-height: 290px; }
        .monthly-bi.is-loading { opacity: .58; pointer-events: none; }
    </style>

    <div class="main-content">
        <div class="page-content">
            <div class="container-fluid">
                <div class="card border-0 shadow-sm mb-0">
                    <div class="card-body monthly-bi" id="monthlyBiBoard">
                        <div class="row g-2 mb-3 align-items-end">
                            <div class="col-xl col-md-4">
                                <span class="filter-label">Sistema</span>
                                <select class="form-select" disabled><option>Real</option></select>
                            </div>
                            <div class="col-xl col-md-4">
                                <label class="filter-label" for="monthlyGrupo">Grupo</label>
                                <select class="form-select monthly-filter" id="monthlyGrupo"><option value="">Todos</option>@foreach ($filtros['grupos'] as $item)<option value="{{ $item }}">{{ $item }}</option>@endforeach</select>
                            </div>
                            <div class="col-xl col-md-4">
                                <label class="filter-label" for="monthlyCentral">Central</label>
                                <select class="form-select monthly-filter" id="monthlyCentral"><option value="">Todas</option>@foreach ($filtros['centrales'] as $item)<option value="{{ $item }}">{{ $item }}</option>@endforeach</select>
                            </div>
                            <div class="col-xl col-md-4">
                                <span class="filter-label">Ruta</span>
                                <select class="form-select" disabled title="La maestra actual no contiene rutas"><option>Todas</option></select>
                            </div>
                            <div class="col-xl col-md-4">
                                <label class="filter-label" for="monthlyGerente">Gerente de servicio</label>
                                <select class="form-select monthly-filter" id="monthlyGerente"><option value="">Todos</option>@foreach ($filtros['gerentes'] as $item)<option value="{{ $item }}">{{ $item }}</option>@endforeach</select>
                            </div>
                            <div class="col-xl col-md-4">
                                <span class="filter-label">Gerente de zona</span>
                                <select class="form-select" disabled title="La maestra actual no contiene gerente de zona"><option>Todos</option></select>
                            </div>
                            <div class="col-xl col-md-4">
                                <label class="filter-label" for="monthlyTerminal">Terminales</label>
                                <select class="form-select monthly-filter" id="monthlyTerminal"><option value="">Terminal</option>@foreach ($filtros['terminales'] as $item)<option value="{{ $item }}">{{ $item }}</option>@endforeach</select>
                            </div>
                        </div>

                        <div class="monthly-chart-card">
                            <div class="monthly-chart" id="monthlyGlobalChart"></div>
                        </div>
                        <div class="monthly-chart-card">
                            <div class="monthly-chart" id="monthlyAverageChart"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="{{ asset('libs/apexcharts/apexcharts.min.js') }}"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const board = document.getElementById('monthlyBiBoard');
            const money = value => `$${new Intl.NumberFormat('en-US', { maximumFractionDigits: 0 }).format(Number(value || 0))}`;
            let globalChart = null;
            let averageChart = null;

            function baseOptions(labels, series, yTitle) {
                return {
                    chart: { height: 285, toolbar: { show: false }, fontFamily: 'inherit', animations: { enabled: true } },
                    series: [{ name: yTitle, data: series }],
                    xaxis: { categories: labels, title: { text: 'Mes', style: { fontWeight: 700 } }, axisBorder: { show: false }, axisTicks: { show: false }, labels: { style: { fontWeight: 600 } } },
                    yaxis: { title: { text: yTitle, style: { fontWeight: 700 } }, labels: { formatter: value => `$${(value / 1000000).toFixed(1)} mill.` } },
                    colors: ['#e4cf6d'],
                    dataLabels: {
                        enabled: true,
                        formatter: value => money(value),
                        offsetY: -12,
                        background: { enabled: true, foreColor: '#111', borderRadius: 3, padding: 4, opacity: 1, borderWidth: 0 },
                        style: { fontSize: '10px', fontFamily: 'inherit', fontWeight: 600 }
                    },
                    grid: { borderColor: '#dedede', strokeDashArray: 2 },
                    tooltip: { y: { formatter: value => money(value) } },
                    legend: { show: false }
                };
            }

            function render(data) {
                if (typeof ApexCharts === 'undefined') {
                    document.getElementById('monthlyGlobalChart').innerHTML = '<div class="text-center text-muted py-5">No fue posible cargar los gráficos.</div>';
                    return;
                }

                const labels = data.meses.map(item => item.etiqueta);
                if (globalChart) globalChart.destroy();
                if (averageChart) averageChart.destroy();

                globalChart = new ApexCharts(document.querySelector('#monthlyGlobalChart'), {
                    ...baseOptions(labels, data.meses.map(item => item.total), 'Venta Global'),
                    chart: { ...baseOptions(labels, [], '').chart, type: 'bar' },
                    plotOptions: { bar: { columnWidth: '76%', borderRadius: 0, dataLabels: { position: 'top' } } },
                    dataLabels: { enabled: false },
                    annotations: {
                        points: data.meses.filter(item => Number(item.total) > 0).map(item => ({
                            x: item.etiqueta,
                            y: Number(item.total),
                            marker: { size: 0 },
                            label: {
                                text: money(item.total),
                                borderColor: '#e4cf6d',
                                offsetY: -10,
                                style: { background: '#e4cf6d', color: '#111', fontSize: '10px', fontFamily: 'inherit', fontWeight: 600 }
                            }
                        }))
                    },
                    grid: { ...baseOptions(labels, [], '').grid, padding: { top: 32 } }
                });
                averageChart = new ApexCharts(document.querySelector('#monthlyAverageChart'), {
                    ...baseOptions(labels, data.meses.map(item => item.promedio), 'Promedio'),
                    chart: { ...baseOptions(labels, [], '').chart, type: 'area' },
                    stroke: { width: 2, curve: 'straight' },
                    fill: { type: 'solid', opacity: .28 }
                });
                globalChart.render();
                averageChart.render();
            }

            async function consultar() {
                const params = new URLSearchParams({
                    grupo: document.getElementById('monthlyGrupo').value,
                    central: document.getElementById('monthlyCentral').value,
                    gerente: document.getElementById('monthlyGerente').value,
                    terminal: document.getElementById('monthlyTerminal').value
                });
                board.classList.add('is-loading');
                try {
                    const response = await fetch(`{{ route('bi.lotobet-real.monthly-data') }}?${params.toString()}`, { headers: { Accept: 'application/json' } });
                    if (!response.ok) throw new Error('No fue posible consultar la tendencia mensual.');
                    render(await response.json());
                } catch (error) {
                    Swal.fire('Error', error.message, 'error');
                } finally {
                    board.classList.remove('is-loading');
                }
            }

            document.querySelectorAll('.monthly-filter').forEach(filter => filter.addEventListener('change', consultar));
            consultar();
        });
    </script>
@endsection
