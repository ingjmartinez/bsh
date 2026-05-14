{{-- Componente de Grupo de Botones Responsivo --}}
<div {{ $attributes->merge(['class' => 'button-group responsive-button-group']) }}>
    {{ $slot }}
</div>

<style>
    @media (max-width: 767px) {
        .responsive-button-group {
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
            width: 100%;
        }

        .responsive-button-group .btn,
        .responsive-button-group a.btn {
            width: 100%;
            display: block;
            text-align: center;
            padding: 0.5rem 1rem;
            font-size: 0.9rem;
        }

        .responsive-button-group .btn-sm {
            padding: 0.4rem 0.75rem;
            font-size: 0.8rem;
        }

        .responsive-button-group.horizontal {
            flex-direction: row;
            flex-wrap: wrap;
        }

        .responsive-button-group.horizontal .btn {
            flex: 1;
            min-width: 80px;
        }
    }

    @media (min-width: 768px) {
        .responsive-button-group {
            display: flex;
            gap: 0.5rem;
            flex-wrap: wrap;
        }

        .responsive-button-group.horizontal {
            flex-direction: row;
        }
    }

    /* Touch targets mínimo */
    .responsive-button-group .btn {
        min-height: 44px;
        min-width: 44px;
    }
</style>
