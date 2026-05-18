<!doctype html>
<html lang="es" data-layout="vertical" data-topbar="light" data-sidebar="dark" data-sidebar-size="lg"
    data-sidebar-image="none">

<head>
    <meta charset="utf-8" />
    <title>Confirmar Reset de ContraseÃ±a | Business Support Hub</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="ERP BSH Support - Confirmar reset de contraseÃ±a" />
    <link rel="shortcut icon" href="{{ asset('images/favicon.ico') }}">
    <link href="{{ asset('css/bootstrap.min.css') }}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('css/icons.min.css') }}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('css/app.min.css') }}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('css/custom.min.css') }}" rel="stylesheet" type="text/css" />

    <style>
        .auth-page-wrapper {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background:
                radial-gradient(circle at 18% 20%, rgba(236, 190, 19, 0.32) 0, rgba(236, 190, 19, 0) 28%),
                radial-gradient(circle at 84% 18%, rgba(164, 57, 85, 0.34) 0, rgba(164, 57, 85, 0) 30%),
                linear-gradient(135deg, #2C2B26 0%, #6A6B5F 42%, #738C79 68%, #A43955 100%);
        }

        .auth-card {
            max-width: 500px;
            width: 100%;
        }

        .auth-logo {
            text-align: center;
            margin-bottom: 1.5rem;
        }

        .auth-logo h2 {
            color: #2C2B26;
            font-weight: 700;
        }
    </style>
</head>

<body>
    <div class="auth-page-wrapper">
        <div class="auth-card">
            <div class="card shadow-lg border-0 rounded-4 overflow-hidden">
                <div class="card-body p-4 p-sm-5">
                    <div class="auth-logo">
                        <a href="/login">
                            <h2><i class="ri-shield-user-line me-2"></i>Business Support Hub</h2>
                        </a>
                        <p class="text-muted mt-2 mb-0">Confirma el cÃ³digo y crea tu nueva contraseÃ±a</p>
                    </div>

                    @if ($errors->any())
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <div class="d-flex align-items-center">
                                <i class="ri-error-warning-line me-2 fs-5"></i>
                                <div>
                                    @foreach ($errors->all() as $error)
                                        <div>{{ $error }}</div>
                                    @endforeach
                                </div>
                            </div>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif

                    @if (session('status'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <div class="d-flex align-items-center">
                                <i class="ri-checkbox-circle-line me-2 fs-5"></i>
                                <div>{{ session('status') }}</div>
                            </div>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif

                    <form action="{{ route('login.reset-password.confirm') }}" method="POST">
                        @csrf

                        <div class="mb-3">
                            <label for="email" class="form-label">Correo electrÃ³nico</label>
                            <input type="email" class="form-control @error('email') is-invalid @enderror" id="email"
                                name="email" value="{{ old('email', $email) }}" required>
                        </div>

                        <div class="mb-3">
                            <label for="codigo" class="form-label">Ingrese el cÃ³digo enviado por correo</label>
                            <input type="text" class="form-control @error('codigo') is-invalid @enderror" id="codigo"
                                name="codigo" value="{{ old('codigo') }}" placeholder="Ej: A1B2C3D4" required>
                        </div>

                        <div class="mb-3">
                            <label for="password" class="form-label">Nueva clave</label>
                            <input type="password" class="form-control @error('password') is-invalid @enderror"
                                id="password" name="password" required>
                        </div>

                        <div class="mb-4">
                            <label for="password_confirmation" class="form-label">Confirmar clave</label>
                            <input type="password" class="form-control" id="password_confirmation"
                                name="password_confirmation" required>
                        </div>

                        <button class="btn btn-primary w-100 py-2" type="submit">
                            <i class="ri-check-line me-1"></i> Guardar nueva contraseÃ±a
                        </button>
                    </form>

                    <div class="text-center mt-3">
                        <a href="{{ route('login') }}" class="text-muted">
                            <i class="ri-arrow-left-line"></i> Volver al inicio de sesiÃ³n
                        </a>
                    </div>
                </div>
            </div>

            <div class="text-center mt-4">
                <p class="text-white-50 mb-0">
                    &copy; {{ date('Y') }} Business Support Hub. Todos los derechos reservados.
                </p>
            </div>
        </div>
    </div>

    <script src="{{ asset('libs/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
</body>

</html>

