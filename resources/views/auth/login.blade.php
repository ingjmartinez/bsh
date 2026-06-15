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
        :root {
            --bsh-charcoal: #22231f;
            --bsh-olive: #738c79;
            --bsh-moss: #566b5c;
            --bsh-gold: #ecbe13;
            --bsh-rose: #a43955;
            --bsh-ink: #171914;
            --bsh-paper: #f6f3ea;
        }

        body {
            background: var(--bsh-charcoal);
        }

        .login-shell {
            position: relative;
            min-height: 100vh;
            overflow: hidden;
            background:
                linear-gradient(125deg, rgba(34, 35, 31, 0.94), rgba(49, 57, 48, 0.9) 44%, rgba(91, 55, 67, 0.88)),
                url("{{ asset('images/bsh2.png') }}");
            background-size: cover;
            background-position: center;
            color: #ffffff;
        }

        .login-shell::before {
            content: "";
            position: absolute;
            inset: 0;
            background-image:
                linear-gradient(rgba(255, 255, 255, 0.045) 1px, transparent 1px),
                linear-gradient(90deg, rgba(255, 255, 255, 0.04) 1px, transparent 1px);
            background-size: 42px 42px;
            mask-image: linear-gradient(to bottom, rgba(0, 0, 0, 0.76), transparent 86%);
            pointer-events: none;
        }

        .login-shell::after {
            content: "";
            position: absolute;
            inset: 0;
            background:
                radial-gradient(circle at 12% 18%, rgba(236, 190, 19, 0.24), transparent 24%),
                radial-gradient(circle at 88% 18%, rgba(164, 57, 85, 0.25), transparent 28%),
                linear-gradient(to bottom, rgba(34, 35, 31, 0.1), rgba(34, 35, 31, 0.78));
            pointer-events: none;
        }

        .login-frame {
            position: relative;
            z-index: 2;
            width: min(1160px, calc(100% - 32px));
            margin: 0 auto;
            padding: 42px 0 28px;
        }

        .login-card {
            display: grid;
            grid-template-columns: minmax(0, 1.04fr) minmax(380px, 0.86fr);
            min-height: 690px;
            overflow: hidden;
            border: 1px solid rgba(255, 255, 255, 0.18);
            border-radius: 8px;
            background: rgba(255, 255, 255, 0.08);
            box-shadow: 0 28px 80px rgba(0, 0, 0, 0.34);
            backdrop-filter: blur(18px);
        }

        .brand-panel {
            position: relative;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            gap: 32px;
            padding: 46px;
            background:
                linear-gradient(150deg, rgba(23, 25, 20, 0.2), rgba(23, 25, 20, 0.78)),
                url("{{ asset('images/bsh.png') }}");
            background-size: cover;
            background-position: center;
            isolation: isolate;
        }

        .brand-panel::before {
            content: "";
            position: absolute;
            inset: 0;
            z-index: -1;
            background:
                linear-gradient(90deg, rgba(23, 25, 20, 0.56), rgba(23, 25, 20, 0.18) 58%, rgba(23, 25, 20, 0.54)),
                radial-gradient(circle at 24% 28%, rgba(236, 190, 19, 0.16), transparent 26%);
        }

        .brand-mark {
            display: inline-flex;
            align-items: center;
            gap: 14px;
            color: #ffffff;
        }

        .brand-mark:hover {
            color: #ffffff;
        }

        .brand-icon {
            width: 50px;
            height: 50px;
            border: 1px solid rgba(236, 190, 19, 0.44);
            border-radius: 8px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background: rgba(236, 190, 19, 0.16);
            color: var(--bsh-gold);
            font-size: 1.45rem;
        }

        .brand-name {
            display: block;
            font-size: 1.45rem;
            font-weight: 800;
            line-height: 1;
        }

        .brand-subtitle {
            display: block;
            margin-top: 5px;
            color: rgba(255, 255, 255, 0.7);
            font-size: 0.78rem;
            letter-spacing: 0.08em;
            text-transform: uppercase;
        }

        .signal-grid {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 12px;
        }

        .signal-item {
            min-height: 104px;
            padding: 18px;
            border: 1px solid rgba(255, 255, 255, 0.18);
            border-radius: 8px;
            background: rgba(255, 255, 255, 0.1);
        }

        .signal-item i {
            display: block;
            margin-bottom: 14px;
            color: var(--bsh-gold);
            font-size: 1.35rem;
        }

        .signal-item strong {
            display: block;
            color: #ffffff;
            font-size: 1.1rem;
            line-height: 1.1;
        }

        .signal-item span {
            display: block;
            margin-top: 5px;
            color: rgba(255, 255, 255, 0.66);
            font-size: 0.8rem;
        }

        .form-panel {
            display: flex;
            align-items: center;
            padding: 42px;
            background: rgba(255, 255, 255, 0.94);
            color: var(--bsh-ink);
        }

        .form-inner {
            width: 100%;
        }

        .form-kicker {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 18px;
            padding: 8px 12px;
            border-radius: 999px;
            background: rgba(115, 140, 121, 0.12);
            color: var(--bsh-moss);
            font-size: 0.78rem;
            font-weight: 700;
            text-transform: uppercase;
        }

        .form-panel h2 {
            margin-bottom: 8px;
            color: var(--bsh-ink);
            font-size: 2rem;
            font-weight: 800;
            line-height: 1.12;
        }

        .form-panel .lead-copy {
            margin-bottom: 28px;
            color: #697168;
            line-height: 1.55;
        }

        .login-section {
            padding: 26px;
            border: 1px solid #e6e1d4;
            border-radius: 8px;
            background: #ffffff;
            box-shadow: 0 18px 42px rgba(23, 25, 20, 0.08);
        }

        .reset-section {
            margin-top: 16px;
            padding: 22px;
            border: 1px solid rgba(164, 57, 85, 0.18);
            border-radius: 8px;
            background: #fbf7ef;
        }

        .section-title {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 16px;
            color: var(--bsh-ink);
            font-size: 0.95rem;
            font-weight: 800;
        }

        .section-title i {
            color: var(--bsh-rose);
            font-size: 1.15rem;
        }

        .form-label {
            color: #343830;
            font-weight: 700;
        }

        .form-control {
            min-height: 48px;
            border-color: #d9d3c6;
            border-radius: 8px;
            color: var(--bsh-ink);
            background-color: #fffdf8;
        }

        .form-control:focus {
            border-color: var(--bsh-olive);
            box-shadow: 0 0 0 0.2rem rgba(115, 140, 121, 0.16);
        }

        .password-toggle {
            height: 48px;
            width: 48px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            color: #687166;
        }

        .btn-login {
            min-height: 50px;
            border: 0;
            border-radius: 8px;
            background: linear-gradient(135deg, var(--bsh-olive), var(--bsh-moss));
            color: #ffffff;
            font-weight: 800;
            box-shadow: 0 12px 24px rgba(86, 107, 92, 0.22);
        }

        .btn-login:hover,
        .btn-login:focus {
            color: #ffffff;
            background: linear-gradient(135deg, #819b86, #4d604f);
        }

        .btn-reset {
            min-height: 48px;
            border-color: rgba(164, 57, 85, 0.36);
            border-radius: 8px;
            color: var(--bsh-rose);
            font-weight: 800;
            background: #ffffff;
        }

        .btn-reset:hover,
        .btn-reset:focus {
            border-color: var(--bsh-rose);
            color: #ffffff;
            background: var(--bsh-rose);
        }

        .alert {
            border-radius: 8px;
        }

        .login-footer {
            position: relative;
            z-index: 2;
            margin-top: 20px;
            color: rgba(255, 255, 255, 0.66);
            text-align: center;
        }

        @media (max-width: 991.98px) {
            .login-frame {
                padding-top: 24px;
            }

            .login-card {
                grid-template-columns: 1fr;
                min-height: 0;
            }

            .brand-panel {
                min-height: 420px;
                padding: 34px;
            }

            .signal-grid {
                grid-template-columns: 1fr;
            }

            .signal-item {
                min-height: 0;
            }

            .form-panel {
                padding: 30px 22px;
            }
        }

        @media (max-width: 575.98px) {
            .login-frame {
                width: min(100% - 18px, 1160px);
                padding: 10px 0 20px;
            }

            .brand-panel {
                min-height: 360px;
                padding: 24px;
            }

            .brand-name {
                font-size: 1.12rem;
            }

            .brand-subtitle {
                font-size: 0.7rem;
            }

            .login-section,
            .reset-section {
                padding: 18px;
            }
        }
    </style>
</head>

<body>
    <main class="login-shell d-flex align-items-center">
        <div class="login-frame">
            <div class="login-card">
                <section class="brand-panel">
                    <div>
                        <a href="/" class="brand-mark text-decoration-none">
                            <span class="brand-icon"><i class="ri-shield-user-line"></i></span>
                            <span>
                                <span class="brand-name">Business Support Hub</span>
                                <span class="brand-subtitle">ERP operativo</span>
                            </span>
                        </a>
                    </div>

                    <div class="signal-grid" aria-label="Modulos principales del sistema">
                        <div class="signal-item">
                            <i class="ri-line-chart-line"></i>
                            <strong>Ventas</strong>
                            <span>Indicadores y reportes</span>
                        </div>
                        <div class="signal-item">
                            <i class="ri-store-2-line"></i>
                            <strong>Agencias</strong>
                            <span>Seguimiento operativo</span>
                        </div>
                        <div class="signal-item">
                            <i class="ri-customer-service-2-line"></i>
                            <strong>Tickets</strong>
                            <span>Soporte y control</span>
                        </div>
                    </div>
                </section>

                <section class="form-panel">
                    <div class="form-inner">
                        <span class="form-kicker">
                            <i class="ri-lock-2-line"></i>
                            Acceso seguro
                        </span>
                        <h2>Bienvenido de nuevo</h2>
                        <p class="lead-copy">Inicia sesion para continuar en el ERP de BSH Support.</p>

                        @if ($errors->any())
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
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
                            <div class="alert alert-success alert-dismissible fade show" role="alert">
                                <div class="d-flex align-items-center">
                                    <i class="ri-checkbox-circle-line me-2 fs-5"></i>
                                    <div>{{ session('status') }}</div>
                                </div>
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        @endif

                        <div class="login-section">
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
                                    <div class="position-relative auth-pass-inputgroup">
                                        <input type="password" class="form-control pe-5 @error('password') is-invalid @enderror"
                                            placeholder="Ingresa tu contrasena" id="password" name="password" required>
                                        <button class="btn btn-link position-absolute end-0 top-0 text-decoration-none password-toggle"
                                            type="button" id="password-addon" onclick="togglePassword()" aria-label="Mostrar u ocultar contrasena">
                                            <i class="ri-eye-off-line align-middle" id="password-icon"></i>
                                        </button>
                                    </div>
                                </div>

                                <button class="btn btn-login w-100 mt-2" type="submit">
                                    <i class="ri-login-circle-line me-1"></i> Iniciar Sesion
                                </button>
                            </form>
                        </div>

                        <div class="reset-section">
                            <div class="section-title">
                                <i class="ri-key-2-line"></i>
                                Olvidaste tu contrasena?
                            </div>
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

                                <button class="btn btn-reset w-100" type="submit">
                                    <i class="ri-mail-send-line me-1"></i> Enviar codigo de reset
                                </button>
                            </form>
                        </div>
                    </div>
                </section>
            </div>

            <footer class="login-footer">
                <p class="mb-0">&copy; {{ date('Y') }} Business Support Hub. Todos los derechos reservados.</p>
            </footer>
        </div>
    </main>

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
