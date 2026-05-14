@extends('app')

@section('content')
    <div class="main-content">

        <div class="page-content">
            <div class="container-fluid">

                <div class="row">
                    <div class="col-lg-12">
                        <div class="card">
                            <div class="card-header d-flex align-items-center justify-content-between">
                                <h5 class="card-title mb-0">Ventas por Usuarios</h5>
                            </div>
                            <div class="card-body">
                                <div style="transform: scale(0.8); transform-origin: top left; width: 125%; height: 1000px;">
                                    <iframe title="INFORME_FALTANTE_GJ - copia" width="100%" height="800"
                                        src="https://app.powerbi.com/view?r=eyJrIjoiYmI5Zjk5MWQtZDQ2MC00ZmFmLTg0NjUtZWJlNjVkZjIwYTFhIiwidCI6IjU0OGNhODQyLTdiMzctNDEzNi1hYTY1LTZkMDljYzRlYzc0OSIsImMiOjJ9"
                                        frameborder="0" allowfullscreen="true">
                                    </iframe>
                                </div>

                            </div>
                        </div>
                    </div>
                </div><!--end row-->
            </div>
            <!-- container-fluid -->
        </div>

    </div>
@endsection

@section('script')
@endsection
