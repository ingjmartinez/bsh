@extends('app')

@section('content')
    <div class="main-content">
        <div class="page-content">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-12">
                        <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                            <h4 class="mb-sm-0">Reporte de Comisiones</h4>
                            <ol class="breadcrumb m-0">
                                <li class="breadcrumb-item"><a href="{{ route('inicio.index') }}">Inicio</a></li>
                                <li class="breadcrumb-item"><a href="{{ route('contabilidad.index') }}">Contabilidad</a></li>
                                <li class="breadcrumb-item active">Comisiones</li>
                            </ol>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="card-title mb-0">Filtros</h5>
                            </div>
                            <div class="card-body">
                                <form class="row g-3">
                                    <div class="col-md-3">
                                        <label class="form-label">Fecha inicio</label>
                                        <input type="date" class="form-control">
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label">Fecha fin</label>
                                        <input type="date" class="form-control">
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label">Agencia</label>
                                        <input type="text" class="form-control" placeholder="Nombre o cÃ³digo">
                                    </div>
                                    <div class="col-md-3 d-flex align-items-end">
                                        <button type="button" class="btn btn-primary w-100">Consultar</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="card-title mb-0">Detalle de Comisiones</h5>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-bordered table-striped align-middle mb-0">
                                        <thead>
                                            <tr>
                                                <th>Fecha</th>
                                                <th>Agencia</th>
                                                <th>Operador</th>
                                                <th>Ventas</th>
                                                <th>% ComisiÃ³n</th>
                                                <th>ComisiÃ³n</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <td colspan="6" class="text-center text-muted">Sin datos para mostrar.</td>
                                            </tr>
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
@endsection

