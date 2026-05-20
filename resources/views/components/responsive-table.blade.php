{{-- Componente de Tabla Responsiva para Móvil --}}
<div class="table-responsive">
    <table {{ $attributes->merge(['class' => 'table table-bordered table-striped align-middle mobile-table']) }}>
        <thead class="table-light d-none d-md-table-header-group">
            <tr>
                {{ $head }}
            </tr>
        </thead>
        <tbody>
            {{ $slot }}
        </tbody>
    </table>
</div>

<style>
    @media (max-width: 767px) {
        .mobile-table tbody tr {
            display: block;
            border: 1px solid #dee2e6;
            margin-bottom: 15px;
            border-radius: 4px;
            overflow: hidden;
        }

        .mobile-table tbody td {
            display: block;
            text-align: right;
            padding-left: 50%;
            position: relative;
            border: none;
            border-bottom: 1px solid #f1f3f5;
        }

        .mobile-table tbody td::before {
            content: attr(data-label);
            position: absolute;
            left: 0;
            width: 50%;
            padding-right: 10px;
            padding-left: 10px;
            font-weight: 600;
            text-align: left;
            color: #495057;
            font-size: 0.75rem;
        }

        .mobile-table tbody tr td:last-child {
            border-bottom: 0;
        }

        .mobile-table tbody td.text-center {
            text-align: center;
        }

        .mobile-table tbody td.text-center::before {
            text-align: left;
        }
    }
</style>

