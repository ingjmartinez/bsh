@extends('app')

@section('content')
    <style>
        #modalEntrevista {
            z-index: 1065;
        }

        .swal2-container {
            z-index: 2000;
        }
    </style>

    <div class="main-content">
        <div class="page-content">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-12">
                        <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                            <h4 class="mb-sm-0">Entrevista Online</h4>
                            <div class="page-title-right">
                                <ol class="breadcrumb m-0">
                                    <li class="breadcrumb-item"><a href="{{ route('inicio.index') }}">Inicio</a></li>
                                    <li class="breadcrumb-item"><a href="{{ route('recursos-humanos.index') }}">Recursos Humanos</a></li>
                                    <li class="breadcrumb-item active">Entrevista Online</li>
                                </ol>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row g-3 mb-3">
                    <div class="col-12">
                        <div class="card overflow-hidden border-0 shadow-sm" style="background: linear-gradient(135deg, #0f766e 0%, #1d4ed8 55%, #312e81 100%);">
                            <div class="card-body p-4 p-lg-5 text-white position-relative">
                                <div class="row align-items-center g-4">
                                    <div class="col-lg-8">
                                        <span class="badge rounded-pill bg-white bg-opacity-10 text-white mb-3">Recursos Humanos</span>
                                        <h2 class="fw-semibold text-white mb-2">Entrevistas Online a Candidatos</h2>
                                        <p class="mb-0 text-white text-opacity-75">
                                            Registra y gestiona las entrevistas telefónicas y online realizadas a los candidatos. Captura su perfil personal y la evaluación respecto a la vacante solicitada.
                                        </p>
                                    </div>
                                    <div class="col-lg-4">
                                        <div class="d-grid gap-2">
                                            <button type="button" class="btn btn-light text-primary fw-semibold" id="btnNueva">
                                                <i class="ri-add-line align-bottom me-1"></i> Nueva entrevista
                                            </button>
                                            <button type="button" class="btn btn-outline-light fw-semibold" id="btnRefrescar">
                                                <i class="ri-refresh-line align-bottom me-1"></i> Actualizar listado
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row g-3">
                    <div class="col-12">
                        <div class="card border-0 shadow-sm">
                            <div class="card-header d-flex align-items-center justify-content-between">
                                <h5 class="card-title mb-0">Listado de entrevistas</h5>
                                <span class="badge bg-primary-subtle text-primary" id="badgeTotal">0 registros</span>
                            </div>
                            <div class="card-body">
                                <table id="tableEntrevistas" class="table table-bordered dt-responsive nowrap table-striped align-middle" style="width:100%">
                                    <thead>
                                        <tr>
                                            <th>Fecha llamada</th>
                                            <th>Nombre</th>
                                            <th>Teléfono</th>
                                            <th>Vacante</th>
                                            <th>Entrevistado por</th>
                                            <th>Excel</th>
                                            <th class="text-center">Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody></tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        @include('entrevista-online.modal-form')

        <footer class="footer">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-sm-6">
                        <script>
                            document.write(new Date().getFullYear())
                        </script> © Velzon.
                    </div>
                    <div class="col-sm-6">
                        <div class="text-sm-end d-none d-sm-block">
                            Design & Develop by Themesbrand
                        </div>
                    </div>
                </div>
            </div>
        </footer>
    </div>
@endsection

@section('script')
    <script>
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '{{ csrf_token() }}';
        const modalEl = document.getElementById('modalEntrevista');
        if (modalEl && modalEl.parentElement !== document.body) {
            document.body.appendChild(modalEl);
        }

        const modalEntrevista = bootstrap.Modal.getOrCreateInstance(modalEl);
        const formEntrevista = document.getElementById('formEntrevista');
        const inputId = document.getElementById('entrevista_id');
        const tituloModal = document.getElementById('modalEntrevistaLabel');
        const btnGuardar = document.querySelector('button[form="formEntrevista"][type="submit"]');
        const textoBtnGuardar = btnGuardar?.innerHTML || '';

        const camposFormulario = [
            'nombre_completo','edad','telefono','direccion','estado_civil','hijos',
            'estudia_actualmente','licencia_vehiculo','laborando_actualmente',
            'ultimo_empleo_posicion','tiempo','salario','fecha_salida_motivo',
            'comentarios','fecha_llamada','entrevistado_por','vacante_aplica',
            'experiencia_demostrable','conoce_del_area','fortalezas','debilidades',
            'manejo_excel'
        ];

        function limpiarFormulario() {
            formEntrevista.reset();
            inputId.value = '';
            limpiarErrores();
        }

        function limpiarErrores() {
            formEntrevista.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
            formEntrevista.querySelectorAll('.invalid-feedback').forEach(el => el.textContent = '');
        }

        function pintarErrores(errores) {
            limpiarErrores();
            Object.keys(errores).forEach(campo => {
                const input = formEntrevista.querySelector(`[name="${campo}"]`);
                if (input) {
                    input.classList.add('is-invalid');
                    const feedback = input.parentElement.querySelector('.invalid-feedback');
                    if (feedback) {
                        feedback.textContent = errores[campo][0];
                    }
                }
            });
        }

        function limpiarBackdropsBootstrap() {
            if (document.querySelector('.modal.show')) {
                return;
            }

            document.querySelectorAll('.modal-backdrop').forEach(backdrop => backdrop.remove());
            document.body.classList.remove('modal-open');
            document.body.style.removeProperty('overflow');
            document.body.style.removeProperty('padding-right');
        }

        function cambiarEstadoGuardando(guardando) {
            if (!btnGuardar) return;

            btnGuardar.disabled = guardando;
            btnGuardar.innerHTML = guardando
                ? '<span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span> Guardando...'
                : textoBtnGuardar;
        }

        function cerrarModalEntrevista() {
            if (!modalEl.classList.contains('show')) {
                return Promise.resolve();
            }

            return new Promise(resolve => {
                modalEl.addEventListener('hidden.bs.modal', resolve, { once: true });
                modalEntrevista.hide();
            });
        }

        async function parsearJson(response, contextoError) {
            const cuerpo = await response.text();
            let payload = null;
            if (cuerpo) {
                try { payload = JSON.parse(cuerpo); } catch (_e) { payload = null; }
            }

            if (response.status === 422 && payload?.errors) {
                pintarErrores(payload.errors);
                throw new Error(payload.message || 'Hay datos inválidos en el formulario.');
            }

            if (!response.ok) {
                const detalle = payload?.message || payload?.error || '';
                throw new Error(contextoError + ' (HTTP ' + response.status + ')' + (detalle ? ': ' + detalle : ''));
            }

            return payload || {};
        }

        function cargarListado() {
            fetch('{{ route('entrevistas-online.list') }}', {
                headers: { 'Accept': 'application/json' }
            })
                .then(response => parsearJson(response, 'Error al cargar entrevistas'))
                .then(data => {
                    const tbody = document.querySelector('#tableEntrevistas tbody');
                    if ($.fn.DataTable.isDataTable('#tableEntrevistas')) {
                        $('#tableEntrevistas').DataTable().clear().destroy();
                    }

                    tbody.innerHTML = '';
                    document.getElementById('badgeTotal').textContent = (data.length || 0) + ' registros';

                    data.forEach(item => {
                        const tr = document.createElement('tr');
                        tr.innerHTML = `
                            <td>${item.fecha_llamada ?? ''}</td>
                            <td>${escapeHtml(item.nombre_completo ?? '')}</td>
                            <td>${escapeHtml(item.telefono ?? '')}</td>
                            <td>${escapeHtml(item.vacante_aplica ?? '')}</td>
                            <td>${escapeHtml(item.entrevistado_por ?? '')}</td>
                            <td class="text-center">${item.manejo_excel ? '<span class="badge bg-info-subtle text-info">' + item.manejo_excel + '/10</span>' : ''}</td>
                            <td class="text-center">
                                <button type="button" class="btn btn-sm btn-soft-primary" data-action="editar" data-id="${item.id}">
                                    <i class="ri-pencil-line"></i>
                                </button>
                                <button type="button" class="btn btn-sm btn-soft-danger" data-action="eliminar" data-id="${item.id}">
                                    <i class="ri-delete-bin-line"></i>
                                </button>
                            </td>
                        `;
                        tbody.appendChild(tr);
                    });

                    $('#tableEntrevistas').DataTable({
                        responsive: true,
                        scrollX: true,
                        pageLength: 10,
                        order: [[0, 'desc']],
                        dom: 'Bfrtip',
                        buttons: ['copy', 'csv', 'excel', 'pdf', 'print']
                    });
                })
                .catch(error => {
                    console.error(error);
                    Swal.fire('Error', error.message || 'No se pudo cargar el listado.', 'error');
                });
        }

        function escapeHtml(str) {
            return String(str ?? '').replace(/[&<>"']/g, s => ({
                '&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'
            })[s]);
        }

        function abrirNueva() {
            limpiarBackdropsBootstrap();
            limpiarFormulario();
            tituloModal.textContent = 'Nueva entrevista online';
            const hoy = new Date().toISOString().slice(0, 10);
            formEntrevista.querySelector('[name="fecha_llamada"]').value = hoy;
            modalEntrevista.show();
        }

        function abrirEditar(id) {
            limpiarFormulario();
            tituloModal.textContent = 'Editar entrevista';

            fetch(`/entrevistas-online/${id}`, { headers: { 'Accept': 'application/json' } })
                .then(response => parsearJson(response, 'Error al cargar la entrevista'))
                .then(data => {
                    inputId.value = data.id;
                    camposFormulario.forEach(campo => {
                        const input = formEntrevista.querySelector(`[name="${campo}"]`);
                        if (input) {
                            input.value = campo === 'fecha_llamada' && data[campo]
                                ? String(data[campo]).slice(0, 10)
                                : data[campo] ?? '';
                        }
                    });
                    modalEntrevista.show();
                })
                .catch(error => {
                    Swal.fire('Error', error.message || 'No se pudo cargar la entrevista.', 'error');
                });
        }

        async function guardar(e) {
            e.preventDefault();
            limpiarErrores();
            cambiarEstadoGuardando(true);

            const formData = new FormData(formEntrevista);
            const id = inputId.value;
            const url = id ? `/entrevistas-online/${id}` : '{{ route('entrevistas-online.store') }}';
            const metodo = id ? 'PUT' : 'POST';

            const payload = {};
            formData.forEach((value, key) => {
                payload[key] = value === '' ? null : value;
            });
            delete payload.id;

            fetch(url, {
                method: metodo,
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify(payload)
            })
                .then(response => parsearJson(response, 'Error al guardar la entrevista'))
                .then(async data => {
                    await cerrarModalEntrevista();
                    await Swal.fire('Listo', data.message || 'Entrevista guardada.', 'success');
                    cargarListado();
                })
                .catch(error => {
                    if (error.message && !error.message.startsWith('Hay datos inválidos')) {
                        Swal.fire('Error', error.message, 'error');
                    }
                })
                .finally(() => cambiarEstadoGuardando(false));
        }

        function eliminar(id) {
            Swal.fire({
                title: '¿Eliminar entrevista?',
                text: 'Esta acción no se puede deshacer.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc3545',
                confirmButtonText: 'Sí, eliminar',
                cancelButtonText: 'Cancelar'
            }).then(result => {
                if (!result.isConfirmed) return;

                fetch(`/entrevistas-online/${id}`, {
                    method: 'DELETE',
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                    .then(response => parsearJson(response, 'Error al eliminar'))
                    .then(data => {
                        Swal.fire('Eliminada', data.message || 'Entrevista eliminada.', 'success');
                        cargarListado();
                    })
                    .catch(error => Swal.fire('Error', error.message, 'error'));
            });
        }

        document.getElementById('btnNueva').addEventListener('click', abrirNueva);
        document.getElementById('btnRefrescar').addEventListener('click', cargarListado);
        modalEl.addEventListener('hidden.bs.modal', limpiarBackdropsBootstrap);
        formEntrevista.addEventListener('submit', guardar);

        document.querySelector('#tableEntrevistas tbody').addEventListener('click', function (e) {
            const btn = e.target.closest('button[data-action]');
            if (!btn) return;
            const id = btn.dataset.id;
            if (btn.dataset.action === 'editar') abrirEditar(id);
            if (btn.dataset.action === 'eliminar') eliminar(id);
        });

        document.addEventListener('DOMContentLoaded', cargarListado);
    </script>
@endsection
