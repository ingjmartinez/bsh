@extends('app')

@section('content')
    <style>
        .bi-report-card {
            border: 1px solid #dedede;
            transition: transform .18s ease, box-shadow .18s ease, border-color .18s ease;
        }

        .bi-report-card:hover {
            border-color: #202020;
            box-shadow: 0 10px 24px rgba(15, 23, 42, .1);
            transform: translateY(-3px);
        }

        .bi-report-icon {
            align-items: center;
            background: #202020;
            color: #fff;
            display: flex;
            height: 48px;
            justify-content: center;
            width: 48px;
        }
    </style>

    <div class="main-content">
        <div class="page-content">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-12">
                        <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                            <div>
                                <h4 class="mb-1">Lotobet Real</h4>
                                <p class="text-muted mb-0">Reportes disponibles</p>
                            </div>
                            <div class="page-title-right">
                                <ol class="breadcrumb m-0">
                                    <li class="breadcrumb-item"><a href="{{ route('bi.index') }}">BI</a></li>
                                    <li class="breadcrumb-item active">Lotobet Real</li>
                                </ol>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row g-3">
                    <div class="col-xl-3 col-lg-4 col-md-6">
                        <a href="{{ route('bi.lotobet-real.dashboard') }}" class="text-decoration-none">
                            <div class="card h-100 bi-report-card">
                                <div class="card-body">
                                    <div class="d-flex align-items-start gap-3">
                                        <div class="bi-report-icon rounded flex-shrink-0">
                                            <i class="ri-line-chart-line fs-4"></i>
                                        </div>
                                        <div class="flex-grow-1">
                                            <div class="d-flex justify-content-between gap-2">
                                                <h5 class="text-dark mb-1">Tablero de ventas</h5>
                                                <i class="ri-arrow-right-up-line text-muted"></i>
                                            </div>
                                            <p class="text-muted mb-0">
                                                Indicadores de ventas, terminales, productos y comparación semanal.
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </a>
                    </div>
                    <div class="col-xl-3 col-lg-4 col-md-6">
                        <a href="{{ route('bi.lotobet-real.monthly') }}" class="text-decoration-none">
                            <div class="card h-100 bi-report-card">
                                <div class="card-body">
                                    <div class="d-flex align-items-start gap-3">
                                        <div class="bi-report-icon rounded flex-shrink-0">
                                            <i class="ri-bar-chart-grouped-line fs-4"></i>
                                        </div>
                                        <div class="flex-grow-1">
                                            <div class="d-flex justify-content-between gap-2">
                                                <h5 class="text-dark mb-1">Tendencia mensual</h5>
                                                <i class="ri-arrow-right-up-line text-muted"></i>
                                            </div>
                                            <p class="text-muted mb-0">
                                                Venta global y promedio diario de los últimos doce meses.
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </a>
                    </div>
                    <div class="col-xl-3 col-lg-4 col-md-6">
                        <a href="{{ route('bi.lotobet-real.raza') }}" class="text-decoration-none">
                            <div class="card h-100 bi-report-card">
                                <div class="card-body">
                                    <div class="d-flex align-items-start gap-3">
                                        <div class="bi-report-icon rounded flex-shrink-0"><i class="ri-radar-line fs-4"></i></div>
                                        <div class="flex-grow-1">
                                            <div class="d-flex justify-content-between gap-2"><h5 class="text-dark mb-1">Ventas Raza</h5><i class="ri-arrow-right-up-line text-muted"></i></div>
                                            <p class="text-muted mb-0">Ventas, premios y resultado de DS Virtual por terminal.</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </a>
                    </div>
                    <div class="col-xl-3 col-lg-4 col-md-6"><a href="{{ route('bi.lotobet-real.productos') }}" class="text-decoration-none"><div class="card h-100 bi-report-card"><div class="card-body"><div class="d-flex align-items-start gap-3"><div class="bi-report-icon rounded flex-shrink-0"><i class="ri-pie-chart-line fs-4"></i></div><div class="flex-grow-1"><div class="d-flex justify-content-between gap-2"><h5 class="text-dark mb-1">Por Productos</h5><i class="ri-arrow-right-up-line text-muted"></i></div><p class="text-muted mb-0">Ventas tradicionales y no tradicionales distribuidas por producto.</p></div></div></div></div></a></div>
                </div>
            </div>
        </div>
    </div>
@endsection
