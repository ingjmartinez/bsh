@extends('app')

@section('content')
    <style>
        .rrhh-card {
            transition: transform 0.18s ease, box-shadow 0.18s ease, border-color 0.18s ease;
        }

        .rrhh-card .rrhh-icon .avatar-title,
        .rrhh-card .rrhh-icon .avatar-title i {
            color: #fff !important;
            opacity: 1 !important;
            visibility: visible !important;
        }

        .rrhh-card .rrhh-icon .avatar-title {
            display: flex !important;
            align-items: center;
            justify-content: center;
            width: 100%;
            height: 100%;
            background-color: var(--vz-primary) !important;
        }

        .rrhh-card .avatar-title,
        .rrhh-card .rrhh-arrow {
            transition: transform 0.18s ease, background-color 0.18s ease, color 0.18s ease;
        }

        .rrhh-card:hover {
            transform: translateY(-4px);
            border-color: rgba(64, 81, 137, 0.35) !important;
            box-shadow: 0 12px 28px rgba(15, 23, 42, 0.12);
        }

        .rrhh-card:hover .avatar-title {
            transform: scale(1.06);
            background-color: var(--vz-primary) !important;
            color: #fff !important;
        }

        .rrhh-card:hover .rrhh-arrow {
            transform: translate(3px, -3px);
            color: var(--vz-primary) !important;
        }
    </style>

    <div class="main-content">
        <div class="page-content">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-12">
                        <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                            <h4 class="mb-sm-0">Recursos Humanos</h4>
                            <div class="page-title-right">
                                <ol class="breadcrumb m-0">
                                    <li class="breadcrumb-item"><a href="{{ route('inicio.index') }}">Inicio</a></li>
                                    <li class="breadcrumb-item active">Recursos Humanos</li>
                                </ol>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row g-3 mb-4">
                    <div class="col-lg-7">
                        <div class="search-box">
                            <input type="text" id="buscarModuloRrhh" class="form-control"
                                placeholder="Buscar por nombre, categoria o descripcion...">
                            <i class="ri-search-line search-icon"></i>
                        </div>
                    </div>
                    <div class="col-lg-5">
                        <div class="d-flex flex-wrap gap-2 justify-content-lg-end" id="filtrosRrhh">
                            <button type="button" class="btn btn-primary btn-sm rrhh-filtro active" data-categoria="todos">
                                Todos
                            </button>
                            @foreach ($categorias as $categoria)
                                <button type="button" class="btn btn-soft-primary btn-sm rrhh-filtro"
                                    data-categoria="{{ strtolower($categoria) }}">
                                    {{ $categoria }}
                                </button>
                            @endforeach
                        </div>
                    </div>
                </div>

                <div class="row g-3 g-xl-4" id="listaModulosRrhh">
                    @foreach ($modulos as $modulo)
                        @php
                            $textoBusqueda = strtolower(implode(' ', [
                                $modulo['nombre'] ?? '',
                                $modulo['descripcion'] ?? '',
                                $modulo['categoria'] ?? '',
                                implode(' ', $modulo['tags'] ?? []),
                            ]));
                        @endphp

                        <div class="col-xxl-3 col-xl-4 col-md-6 rrhh-item"
                            data-categoria="{{ strtolower($modulo['categoria'] ?? '') }}"
                            data-search="{{ $textoBusqueda }}">
                            <a href="{{ $modulo['url'] }}" class="text-decoration-none">
                                <div class="card border h-100 rrhh-card">
                                    <div class="card-body">
                                        <div class="d-flex align-items-start gap-3">
                                            <div class="avatar-sm flex-shrink-0 rrhh-icon">
                                                <span class="avatar-title bg-primary text-white rounded">
                                                    <i class="{{ $modulo['icono'] ?? 'ri-user-settings-line' }} fs-4 text-white"></i>
                                                </span>
                                            </div>
                                            <div class="flex-grow-1">
                                                <div class="d-flex align-items-start justify-content-between gap-2">
                                                    <h5 class="mb-1 text-dark">{{ $modulo['nombre'] }}</h5>
                                                    <i class="ri-arrow-right-up-line text-muted rrhh-arrow"></i>
                                                </div>
                                                <span class="badge bg-light text-muted mb-2">
                                                    {{ $modulo['categoria'] }}
                                                </span>
                                                <p class="text-muted mb-0">
                                                    {{ $modulo['descripcion'] }}
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </a>
                        </div>
                    @endforeach
                </div>

                <div id="sinResultadosRrhh" class="text-center py-5 d-none">
                    <div class="avatar-md mx-auto mb-3">
                        <div class="avatar-title bg-light text-muted rounded-circle fs-2">
                            <i class="ri-search-eye-line"></i>
                        </div>
                    </div>
                    <h5 class="mb-1">Sin resultados</h5>
                    <p class="text-muted mb-0">No hay opciones de Recursos Humanos que coincidan con la busqueda actual.</p>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const input = document.getElementById('buscarModuloRrhh');
            const filtros = document.querySelectorAll('.rrhh-filtro');
            const items = document.querySelectorAll('.rrhh-item');
            const empty = document.getElementById('sinResultadosRrhh');
            let categoriaActiva = 'todos';

            function aplicarFiltros() {
                const texto = (input.value || '').toLowerCase().trim();
                let visibles = 0;

                items.forEach(function (item) {
                    const coincideTexto = !texto || (item.dataset.search || '').includes(texto);
                    const coincideCategoria = categoriaActiva === 'todos' || item.dataset.categoria === categoriaActiva;
                    const visible = coincideTexto && coincideCategoria;

                    item.classList.toggle('d-none', !visible);

                    if (visible) {
                        visibles++;
                    }
                });

                empty.classList.toggle('d-none', visibles > 0);
            }

            input.addEventListener('input', aplicarFiltros);

            filtros.forEach(function (button) {
                button.addEventListener('click', function () {
                    categoriaActiva = this.dataset.categoria || 'todos';

                    filtros.forEach(function (item) {
                        item.classList.remove('active', 'btn-primary');
                        item.classList.add('btn-soft-primary');
                    });

                    this.classList.add('active', 'btn-primary');
                    this.classList.remove('btn-soft-primary');

                    aplicarFiltros();
                });
            });
        });
    </script>
@endsection
