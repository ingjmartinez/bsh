{{-- Componente de Formulario Responsivo --}}
<form {{ $attributes->merge(['class' => 'responsive-form']) }} method="{{ $method ?? 'POST' }}">
    @csrf
    @method($httpMethod ?? 'POST')
    
    {{ $slot }}
</form>

<style>
    @media (max-width: 767px) {
        .responsive-form .row {
            margin-left: -0.5rem;
            margin-right: -0.5rem;
        }

        .responsive-form .row [class*="col-"] {
            padding-left: 0.5rem;
            padding-right: 0.5rem;
        }

        .responsive-form .form-group {
            margin-bottom: 1rem;
        }

        .responsive-form .form-label {
            font-size: 0.9rem;
            margin-bottom: 0.4rem;
            display: block;
            font-weight: 500;
        }

        .responsive-form .form-control,
        .responsive-form .form-select {
            font-size: 16px;
            width: 100%;
            padding: 0.5rem 0.75rem;
            border-radius: 4px;
        }

        .responsive-form .form-check {
            padding-left: 0;
            margin-bottom: 0.75rem;
        }

        .responsive-form .form-check-input {
            margin-left: 0;
            margin-right: 0.5rem;
        }

        .responsive-form .form-text {
            display: block;
            font-size: 0.75rem;
            color: #6c757d;
            margin-top: 0.25rem;
        }

        /* En móvil, columnas full-width a menos que sean col-12 */
        .responsive-form .col-md-6,
        .responsive-form .col-lg-4,
        .responsive-form .col-lg-6 {
            flex: 0 0 100%;
            max-width: 100%;
        }

        /* Botones full-width en móvil */
        .responsive-form .btn-container {
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
        }

        .responsive-form .btn-container .btn {
            width: 100%;
        }
    }

    @media (max-width: 575px) {
        .responsive-form .form-control,
        .responsive-form .form-select,
        .responsive-form .form-check-input {
            font-size: 16px !important;
        }
    }
</style>
