@extends('app')

@section('content')
    <style>
        .raza-board { background:#fff; color:#171717; padding:18px; }
        .raza-panel { border:1.5px solid #202020; border-radius:10px; background:#fff; height:100%; padding:10px 12px; }
        .raza-panel h5 { color:#111; font-size:.86rem; font-weight:700; margin:0 0 7px; }
        .raza-value { font-size:1.35rem; font-weight:500; line-height:1.15; }
        .raza-small { color:#777; font-size:.76rem; }
        .raza-filter-label { color:#111; display:block; font-size:.78rem; font-weight:700; margin-bottom:5px; text-align:center; }
        .raza-board .form-select,.raza-board .form-control { border-color:#ddd; border-radius:0; font-size:.75rem; min-height:34px; }
        .raza-stat-grid { display:grid; grid-template-columns:1fr 1fr; gap:6px 16px; }
        .raza-stat-grid strong { display:block; font-size:1rem; font-weight:500; }
        .raza-divider { border-top:1px solid #d5d5d5; margin:7px 0 4px; }
        .raza-variation { font-size:1rem; font-weight:600; }
        .raza-variation.positive { color:#2f7d55; } .raza-variation.negative { color:#b33a2f; }
        .raza-jackpot { align-items:center; display:flex; flex-direction:column; justify-content:center; min-height:125px; }
        .raza-board.loading { opacity:.58; pointer-events:none; }
        .raza-header-grid { display:grid; grid-template-columns:190px minmax(0,1fr); gap:8px; align-items:stretch; }
        .raza-filter-grid { display:grid; grid-template-columns:.8fr 1.25fr 1.15fr 1.25fr .9fr; gap:8px; align-items:end; }
        .raza-filter-grid > div { margin-top:0!important; max-width:none!important; width:auto!important; }
        .raza-filter-grid .raza-date { max-width:120px!important; }
        .raza-content-grid { display:grid; grid-template-columns:190px 255px 145px minmax(420px,1fr); gap:8px; margin-top:8px; align-items:stretch; }
        .raza-content-grid > div { max-width:none!important; width:auto!important; }
        .raza-summary-stack { display:grid; gap:8px; grid-template-rows:1fr 1fr; }
        .raza-chart-panel { min-width:0; }
        @media (max-width:1399.98px) {
            .raza-content-grid { grid-template-columns:190px 1fr 150px; }
            .raza-chart-panel { grid-column:1/-1; min-height:260px; }
        }
        @media (max-width:991.98px) {
            .raza-header-grid,.raza-content-grid { grid-template-columns:1fr; }
            .raza-filter-grid { grid-template-columns:repeat(2,minmax(0,1fr)); }
            .raza-chart-panel { grid-column:auto; }
        }
    </style>

    <div class="main-content"><div class="page-content"><div class="container-fluid">
        <div class="card border-0 shadow-sm mb-0"><div class="card-body raza-board" id="razaBoard">
            <div class="raza-header-grid">
                <div><div class="raza-panel"><h5>Virtuales</h5><div class="raza-stat-grid">
                    <div><strong id="razaActivas">0</strong><span class="raza-small">Activas hoy</span></div><div><strong id="razaVendieron">0</strong><span class="raza-small">Vendieron</span></div>
                    <div><strong id="razaEnlazadas">0</strong><span class="raza-small">Enlazadas</span></div><div><strong id="razaVendieronRaza">0</strong><span class="raza-small">Vendieron Raza</span></div>
                </div></div></div>
                <div><div class="raza-filter-grid">
                    <div class="col-md-2"><span class="raza-filter-label">Sistema</span><select class="form-select" disabled><option>Real</option></select></div>
                    <div class="col-md-2"><label class="raza-filter-label" for="razaGrupo">Grupo</label><select id="razaGrupo" class="form-select raza-filter"><option value="">Todas</option>@foreach($filtros['grupos'] as $item)<option value="{{ $item }}">{{ $item }}</option>@endforeach</select></div>
                    <div class="col-md-2"><label class="raza-filter-label" for="razaCentral">Central/Ruta</label><select id="razaCentral" class="form-select raza-filter"><option value="">Todas</option>@foreach($filtros['centrales'] as $item)<option value="{{ $item }}">{{ $item }}</option>@endforeach</select></div>
                    <div class="col-md-3"><label class="raza-filter-label" for="razaGerente">Gerente de servicio</label><select id="razaGerente" class="form-select raza-filter"><option value="">Todos</option>@foreach($filtros['gerentes'] as $item)<option value="{{ $item }}">{{ $item }}</option>@endforeach</select></div>
                    <div class="col-md-3"><label class="raza-filter-label" for="razaTerminal">Terminales</label><select id="razaTerminal" class="form-select raza-filter"><option value="">Terminal</option>@foreach($filtros['terminales'] as $item)<option value="{{ $item }}">{{ $item }}</option>@endforeach</select></div>
                    <div class="raza-date"><input type="date" id="razaDesde" class="form-control raza-filter" value="{{ $ultimaFecha }}"></div>
                    <div class="raza-date"><input type="date" id="razaHasta" class="form-control raza-filter" value="{{ $ultimaFecha }}"></div>
                </div></div>
            </div>

            <div class="raza-content-grid">
                <div><div class="raza-panel"><h5>Ventas</h5><div class="raza-value" id="razaVentas">$0</div><div class="raza-small">Venta Raza</div><div class="raza-value mt-2" id="razaVentaPromedio">$0</div><div class="raza-small">Promedio x enlazada</div><div class="raza-small mt-3">Periodo vs semana pasada</div><div class="raza-divider"></div><div class="raza-variation" id="razaVariacion">—</div></div></div>
                <div class="raza-summary-stack"><div class="raza-panel"><h5>Premios</h5><div class="d-flex justify-content-between gap-2"><div><div class="raza-value" id="razaPremios">$0</div><div class="raza-small">Premios Raza</div></div><div><div class="raza-value" id="razaPremioPromedio">$0</div><div class="raza-small">Promedio x enlazada</div></div></div></div><div class="raza-panel"><h5>Resultado bruto</h5><div class="d-flex justify-content-between gap-2"><div><div class="raza-value" id="razaResultado">$0</div><div class="raza-small">Resultado bruto</div></div><div><div class="raza-value" id="razaResultadoPromedio">$0</div><div class="raza-small">Promedio x enlazada</div></div></div></div></div>
                <div><div class="raza-panel raza-jackpot"><h5>Jackpot pagado</h5><div class="raza-value mt-auto mb-auto" id="razaJackpot">$0</div><button type="button" class="btn btn-outline-dark btn-sm w-100" disabled>Mostrar Jackpot pagado x mes</button></div></div>
                <div class="raza-chart-panel"><div class="raza-panel"><h5>Últimos 7 días</h5><div id="razaChart"></div></div></div>
            </div>
        </div></div>
    </div></div></div>

    <script src="{{ asset('libs/apexcharts/apexcharts.min.js') }}"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const board=document.getElementById('razaBoard'); let chart=null;
            const money=value=>`$${new Intl.NumberFormat('en-US',{maximumFractionDigits:2}).format(Number(value||0))}`;
            const integer=value=>new Intl.NumberFormat('en-US').format(Number(value||0));
            function render(data){
                razaActivas.textContent=integer(data.virtuales.activas); razaVendieron.textContent=integer(data.virtuales.vendieron); razaEnlazadas.textContent=integer(data.virtuales.enlazadas); razaVendieronRaza.textContent=integer(data.virtuales.vendieron_raza);
                razaVentas.textContent=money(data.ventas.total); razaVentaPromedio.textContent=money(data.ventas.promedio_enlazada); razaPremios.textContent=money(data.premios.total); razaPremioPromedio.textContent=money(data.premios.promedio_enlazada); razaResultado.textContent=money(data.resultado.total); razaResultadoPromedio.textContent=money(data.resultado.promedio_enlazada); razaJackpot.textContent=money(data.jackpot);
                razaVariacion.className='raza-variation '+(data.ventas.variacion===null?'':data.ventas.variacion>=0?'positive':'negative'); razaVariacion.textContent=data.ventas.variacion===null?'—':`${data.ventas.variacion>=0?'▲':'▼'} ${Math.abs(data.ventas.variacion).toFixed(0)} %`;
                if(typeof ApexCharts==='undefined')return; if(chart)chart.destroy(); chart=new ApexCharts(document.querySelector('#razaChart'),{chart:{type:'area',height:230,toolbar:{show:false}},series:[{name:'Ventas Raza',data:data.grafico.map(i=>i.total)}],xaxis:{categories:data.grafico.map(i=>i.dia),axisBorder:{show:false},axisTicks:{show:false}},yaxis:{labels:{formatter:v=>`$${(v/1000000).toFixed(0)} mill.`}},colors:['#e4cf6d'],stroke:{width:2},fill:{type:'solid',opacity:.3},dataLabels:{enabled:true,formatter:v=>money(v),background:{enabled:true,foreColor:'#fff',borderRadius:3,borderWidth:0},style:{fontSize:'9px'}},grid:{borderColor:'#ddd',strokeDashArray:2},legend:{show:false}}); chart.render();
            }
            async function consultar(){const p=new URLSearchParams({fecha_desde:razaDesde.value,fecha_hasta:razaHasta.value,grupo:razaGrupo.value,central:razaCentral.value,gerente:razaGerente.value,terminal:razaTerminal.value});board.classList.add('loading');try{const r=await fetch(`{{ route('bi.lotobet-real.raza-data') }}?${p}`,{headers:{Accept:'application/json'}});if(!r.ok)throw new Error('No fue posible consultar Ventas Raza.');render(await r.json());}catch(e){Swal.fire('Error',e.message,'error');}finally{board.classList.remove('loading')}}
            document.querySelectorAll('.raza-filter').forEach(i=>i.addEventListener('change',consultar)); consultar();
        });
    </script>
@endsection
