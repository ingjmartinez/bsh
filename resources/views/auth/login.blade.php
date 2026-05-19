<!doctype html>
<html lang="es" data-layout="vertical" data-topbar="light" data-sidebar="dark" data-sidebar-size="lg"
    data-sidebar-image="none">

<head>
    <meta charset="utf-8" />
    <title>Iniciar Sesion | Business Support Hub</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="ERP BSH Support - Inicio de Sesion" />
    <link rel="shortcut icon" href="{{ asset('images/favicon.ico') }}">

    <link href="{{ asset('css/bootstrap.min.css') }}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('css/icons.min.css') }}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('css/app.min.css') }}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('css/custom.min.css') }}" rel="stylesheet" type="text/css" />

    <style>
        .brand-wordmark {
            font-family: 'Poppins', 'Inter', system-ui, -apple-system, "Segoe UI", Roboto, sans-serif;
            font-weight: 700;
            font-size: 1.75rem;
            letter-spacing: -0.02em;
            color: #ffffff;
            line-height: 1;
            display: inline-flex;
            align-items: center;
            gap: 0.55rem;
        }

        .brand-wordmark .brand-icon {
            width: 38px;
            height: 38px;
            border-radius: 10px;
            background: rgba(255, 255, 255, 0.12);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 1.15rem;
        }

        .brand-wordmark .brand-suffix {
            font-weight: 500;
            opacity: 0.85;
        }

        .auth-bg-cover {
            background:
                radial-gradient(circle at 18% 22%, rgba(236, 190, 19, 0.35) 0, rgba(236, 190, 19, 0) 28%),
                radial-gradient(circle at 82% 18%, rgba(164, 57, 85, 0.36) 0, rgba(164, 57, 85, 0) 30%),
                linear-gradient(135deg, #2C2B26 0%, #6A6B5F 42%, #738C79 68%, #A43955 100%);
        }

        .auth-bg-cover > .bg-overlay {
            opacity: 0.34;
            background-color: #2C2B26;
            background-blend-mode: multiply;
        }

        .auth-one-bg {
            background-image:
                linear-gradient(145deg, rgba(44, 43, 38, 0.22), rgba(44, 43, 38, 0.58)),
                url("{{ asset('images/bsh2.png') }}");
        }

        .auth-one-bg .bg-overlay {
            background:
                radial-gradient(circle at 14% 16%, rgba(236, 190, 19, 0.42) 0, rgba(236, 190, 19, 0) 30%),
                linear-gradient(135deg, rgba(44, 43, 38, 0.94) 0%, rgba(106, 107, 95, 0.88) 48%, rgba(164, 57, 85, 0.82) 100%);
            opacity: 0.92;
        }

        .brand-wordmark .brand-icon {
            background: rgba(236, 190, 19, 0.18);
            color: #ECBE13;
        }
    </style>
</head>

<body>

    <div class="auth-page-wrapper auth-bg-cover py-5 d-flex justify-content-center align-items-center min-vh-100">
        <div class="bg-overlay"></div>

        <div class="auth-page-content overflow-hidden pt-lg-5">
            <div class="container">
                <div class="row">
                    <div class="col-lg-12">
                        <div class="card overflow-hidden">
                            <div class="row g-0">

                                <div class="col-lg-6">
                                    <div class="p-lg-5 p-4 auth-one-bg h-100">
                                        <div class="bg-overlay"></div>
                                        <div class="position-relative h-100 d-flex flex-column">
                                            <div class="mb-4">
                                                <a href="/" class="d-block">
                                                    <span class="brand-wordmark">
                                                        <span class="brand-icon"><i class="ri-shield-user-line"></i></span>
                                                        Business <span class="brand-suffix">Support Hub</span>
                                                    </span>
                                                </a>
                                            </div>
                                            <div class="mt-auto">
                                                <div class="mb-3">
                                                    <i class="ri-double-quotes-l display-4 text-success"></i>
                                                </div>

                                                <div id="qoutescarouselIndicators" class="carousel slide" data-bs-ride="carousel">
                                                    <div class="carousel-indicators">
                                                        <button type="button" data-bs-target="#qoutescarouselIndicators" data-bs-slide-to="0" class="active" aria-current="true" aria-label="Slide 1"></button>
                                                        <button type="button" data-bs-target="#qoutescarouselIndicators" data-bs-slide-to="1" aria-label="Slide 2"></button>
                                                        <button type="button" data-bs-target="#qoutescarouselIndicators" data-bs-slide-to="2" aria-label="Slide 3"></button>
                                                    </div>
                                                    <div class="carousel-inner text-center text-white-50 pb-5">
                                                        <div class="carousel-item active">
                                                            <p class="fs-15 fst-italic">" Tu ERP operativo unificado: ventas, agencias, RRHH y servicios generales en un solo lugar. "</p>
                                                        </div>
                                                        <div class="carousel-item">
                                                            <p class="fs-15 fst-italic">" Indicadores en tiempo real, control de tickets y seguimiento de incentivos para todo el equipo. "</p>
                                                        </div>
                                                        <div class="carousel-item">
                                                            <p class="fs-15 fst-italic">" Plataforma desarrollada para Business Support Hub - confiable, segura y al servicio de tu operacion. "</p>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-lg-6">
                                    <div class="p-lg-5 p-4">
                                        <div>
                                            <h5 class="text-primary">Bienvenido!</h5>
                                            <p class="text-muted">Inicia sesion para continuar en el ERP de BSH Support.</p>
                                        </div>

                                        @if ($errors->any())
                                            <div class="alert alert-danger alert-dismissible fade show mt-3" role="alert">
                                                <div class="d-flex align-items-center">
                                                    <i class="ri-error-warning-line me-2 fs-5"></i>
                                                    <div>
                                                        @foreach ($errors->all() as $error)
                                                            <span>{{ $error }}</span>
                                                        @endforeach
                                                    </div>
                                                </div>
                                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                            </div>
                                        @endif

                                        @if (session('status'))
                                            <div class="alert alert-success alert-dismissible fade show mt-3" role="alert">
                                                <div class="d-flex align-items-center">
                                                    <i class="ri-checkbox-circle-line me-2 fs-5"></i>
                                                    <div>{{ session('status') }}</div>
                                                </div>
                                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                            </div>
                                        @endif

                                        <div class="mt-4">
                                            <form action="{{ route('login') }}" method="POST">
                                                @csrf

                                                <div class="mb-3">
                                                    <label for="email" class="form-label">Correo Electronico</label>
                                                    <input type="email" class="form-control @error('email') is-invalid @enderror"
                                                        id="email" name="email" placeholder="correo@ejemplo.com"
                                                        value="{{ old('email') }}" required autofocus>
                                                </div>

                                                <div class="mb-3">
                                                    <label class="form-label" for="password">Contrasena</label>
                                                    <div class="position-relative auth-pass-inputgroup mb-3">
                                                        <input type="password" class="form-control pe-5 @error('password') is-invalid @enderror"
                                                            placeholder="Ingresa tu contrasena" id="password" name="password" required>
                                                        <button class="btn btn-link position-absolute end-0 top-0 text-decoration-none text-muted"
                                                            type="button" id="password-addon" onclick="togglePassword()">
                                                            <i class="ri-eye-off-line align-middle" id="password-icon"></i>
                                                        </button>
                                                    </div>
                                                </div>

                                                <div class="mt-4">
                                                    <button class="btn btn-success w-100" type="submit">
                                                        <i class="ri-login-circle-line me-1"></i> Iniciar Sesion
                                                    </button>
                                                </div>
                                            </form>
                                        </div>

                                        <hr class="my-4">

                                        <div>
                                            <h6 class="mb-3">Olvidaste tu contrasena?</h6>
                                            <form action="{{ route('login.reset-password') }}" method="POST">
                                                @csrf
                                                <div class="mb-3">
                                                    <label for="email_reset" class="form-label">Correo para reset</label>
                                                    <input type="email" class="form-control @error('email_reset') is-invalid @enderror"
                                                        id="email_reset" name="email_reset" placeholder="correo@ejemplo.com"
                                                        value="{{ old('email_reset', old('email')) }}" required>
                                                    @error('email_reset')
                                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                                    @enderror
                                                </div>

                                                <button class="btn btn-outline-warning w-100" type="submit">
                                                    <i class="ri-mail-send-line me-1"></i> Enviar codigo de reset
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                </div>

                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <footer class="footer">
            <div class="container">
                <div class="row">
                    <div class="col-lg-12">
                        <div class="text-center">
                            <p class="mb-0 text-white-50">
                                &copy; {{ date('Y') }} Business Support Hub. Todos los derechos reservados.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </footer>
    </div>

    <script src="{{ asset('libs/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
    <script>
        function togglePassword() {
            const passwordInput = document.getElementById('password');
            const icon = document.getElementById('password-icon');

            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                icon.classList.remove('ri-eye-off-line');
                icon.classList.add('ri-eye-line');
            } else {
                passwordInput.type = 'password';
                icon.classList.remove('ri-eye-line');
                icon.classList.add('ri-eye-off-line');
            }
        }
    </script>
</body>

</html>

