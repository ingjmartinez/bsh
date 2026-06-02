<!doctype html>
<html lang="es">

<head>
    <meta charset="utf-8" />
    <title>Cambiar clave | Business Support Hub</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="shortcut icon" href="{{ asset('images/favicon.ico') }}">
    <link href="{{ asset('css/bootstrap.min.css') }}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('css/icons.min.css') }}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('css/app.min.css') }}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('css/custom.min.css') }}" rel="stylesheet" type="text/css" />
    <style>
        body {
            min-height: 100vh;
            background:
                radial-gradient(circle at 20% 18%, rgba(236, 190, 19, 0.26), transparent 28%),
                radial-gradient(circle at 82% 20%, rgba(164, 57, 85, 0.28), transparent 30%),
                linear-gradient(135deg, #2C2B26 0%, #6A6B5F 48%, #738C79 72%, #A43955 100%);
        }

        .modal-content {
            border: 0;
            box-shadow: 0 24px 80px rgba(15, 23, 42, 0.28);
        }
    </style>
</head>

<body>
    <div class="modal fade" id="forcePasswordModal" tabindex="-1" aria-labelledby="forcePasswordModalLabel" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <div>
                        <h5 class="modal-title" id="forcePasswordModalLabel">Actualiza tu clave</h5>
                        <p class="text-muted mb-0 small">Debes definir una clave personal antes de continuar.</p>
                    </div>
                </div>
                <form action="{{ route('password.force.update') }}" method="POST">
                    @csrf
                    <div class="modal-body">
                        @if ($errors->any())
                            <div class="alert alert-danger py-2">
                                @foreach ($errors->all() as $error)
                                    <div>{{ $error }}</div>
                                @endforeach
                            </div>
                        @endif

                        <div class="mb-3">
                            <label for="password" class="form-label">Nueva clave</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="ri-lock-2-line"></i></span>
                                <input type="password" class="form-control @error('password') is-invalid @enderror" id="password" name="password" minlength="8" required autofocus>
                                <button class="btn btn-light" type="button" onclick="togglePassword('password', 'passwordIcon')">
                                    <i class="ri-eye-off-line" id="passwordIcon"></i>
                                </button>
                            </div>
                        </div>

                        <div class="mb-0">
                            <label for="password_confirmation" class="form-label">Confirmar clave</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="ri-lock-check-line"></i></span>
                                <input type="password" class="form-control" id="password_confirmation" name="password_confirmation" minlength="8" required>
                                <button class="btn btn-light" type="button" onclick="togglePassword('password_confirmation', 'passwordConfirmIcon')">
                                    <i class="ri-eye-off-line" id="passwordConfirmIcon"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="ri-save-line me-1"></i> Actualizar clave
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <form id="logoutForm" action="{{ route('logout') }}" method="POST" class="d-none">
        @csrf
    </form>

    <script src="{{ asset('libs/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
    <script>
        function togglePassword(inputId, iconId) {
            const input = document.getElementById(inputId);
            const icon = document.getElementById(iconId);

            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.remove('ri-eye-off-line');
                icon.classList.add('ri-eye-line');
            } else {
                input.type = 'password';
                icon.classList.remove('ri-eye-line');
                icon.classList.add('ri-eye-off-line');
            }
        }

        document.addEventListener('DOMContentLoaded', function () {
            const modal = new bootstrap.Modal(document.getElementById('forcePasswordModal'));
            modal.show();
        });
    </script>
</body>

</html>
