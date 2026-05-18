@extends('app')

@section('content')
    <style>
        .proyecto-bg {
            background: radial-gradient(circle at 18% 20%, #123a7a 0%, #0a2a57 38%, #081a34 72%, #050e1d 100%);
            min-height: calc(100vh - 140px);
            border-radius: 14px;
            padding: 1rem;
        }

        .proyecto-board {
            display: flex;
            gap: 1rem;
            overflow-x: auto;
            padding-bottom: 0.75rem;
        }

        .proyecto-col {
            min-width: 310px;
            max-width: 310px;
            background: rgba(8, 12, 20, 0.9);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 12px;
            color: #dce3ed;
            box-shadow: 0 10px 26px rgba(0, 0, 0, 0.3);
            display: flex;
            flex-direction: column;
            max-height: calc(100vh - 220px);
        }

        .proyecto-col-header {
            padding: 1rem 1rem 0.5rem;
            font-weight: 700;
            font-size: 1.05rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
            color: #f4f7fb;
        }

        .proyecto-col-cards {
            overflow-y: auto;
            padding: 0 0.75rem 0.5rem;
            min-height: 120px;
        }

        .proyecto-card {
            background: #202633;
            border: 1px solid #2b3447;
            border-radius: 10px;
            padding: 0.75rem;
            margin: 0.6rem 0;
            cursor: pointer;
        }

        .proyecto-card.dragging {
            opacity: 0.55;
        }

        .proyecto-col-cards.drop-target {
            outline: 2px dashed #6aa8ff;
            outline-offset: -4px;
            border-radius: 10px;
        }

        .proyecto-card-title {
            font-size: 1.05rem;
            color: #eef2f7;
            margin-bottom: 0.3rem;
        }

        .proyecto-card-meta {
            color: #aeb9cb;
            font-size: 0.88rem;
            display: flex;
            align-items: center;
            gap: 0.45rem;
        }

        .proyecto-add {
            border-top: 1px solid rgba(255, 255, 255, 0.08);
            padding: 0.75rem 1rem 1rem;
            color: #e4ecf8;
            font-size: 0.95rem;
            cursor: pointer;
        }

        .proyecto-new-list {
            min-width: 310px;
            max-width: 310px;
            height: fit-content;
            border-radius: 12px;
            border: 1px dashed rgba(255, 255, 255, 0.4);
            color: #dce6f6;
            background: rgba(255, 255, 255, 0.12);
            padding: 1rem;
            font-weight: 600;
            cursor: pointer;
        }

        .check-item {
            display: flex;
            align-items: center;
            justify-content: space-between;
            border: 1px solid #e9ebec;
            border-radius: 6px;
            padding: 0.4rem 0.5rem;
            margin-bottom: 0.4rem;
        }

        .proyecto-drag-ghost {
            background: #202633;
            border: 1px solid #3a4762;
            border-radius: 10px;
            color: #eef2f7;
            padding: 0.45rem 0.6rem;
            font-size: 0.82rem;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.35);
            max-width: 220px;
        }

        .mini-move-card {
            position: fixed;
            right: 1rem;
            bottom: 1rem;
            z-index: 2050;
            background: #1f2633;
            border: 1px solid #2f3c54;
            border-radius: 10px;
            color: #e6edf8;
            padding: 0.55rem 0.7rem;
            min-width: 220px;
            max-width: 280px;
            box-shadow: 0 12px 26px rgba(0, 0, 0, 0.35);
            animation: mini-card-in 0.2s ease-out;
        }

        .mini-move-card small {
            color: #9fb0c9;
            display: block;
            margin-top: 0.2rem;
            font-size: 0.72rem;
        }

        .mini-move-chip {
            display: inline-block;
            border-radius: 999px;
            padding: 0.08rem 0.45rem;
            font-size: 0.7rem;
            font-weight: 700;
            color: #fff;
            margin-top: 0.25rem;
        }

        .mini-move-progress {
            margin-top: 0.4rem;
            height: 6px;
            border-radius: 6px;
            background: rgba(255, 255, 255, 0.14);
            overflow: hidden;
        }

        .mini-move-progress > div {
            height: 100%;
            border-radius: 6px;
            transition: width 0.2s ease;
        }

        @keyframes mini-card-in {
            from {
                transform: translateY(10px);
                opacity: 0;
            }

            to {
                transform: translateY(0);
                opacity: 1;
            }
        }
    </style>

    <div class="main-content">
        <div class="page-content">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-12">
                        <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                            <h4 class="mb-sm-0">Proyecto</h4>
                            <div class="page-title-right">
                                <ol class="breadcrumb m-0">
                                    <li class="breadcrumb-item"><a href="{{ route('tareas.index') }}">Tareas</a></li>
                                    <li class="breadcrumb-item active">Proyecto</li>
                                </ol>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="proyecto-bg">
                    <div class="proyecto-board" id="proyectoBoard"></div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modalCardProyecto" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Tarjeta de proyecto</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Titulo</label>
                        <input type="text" class="form-control" id="cardTituloEdit">
                    </div>

                    <label class="form-label">Checklist</label>
                    <div id="cardChecklistWrap" class="mb-2"></div>

                    <div class="input-group mb-2">
                        <input type="text" class="form-control" id="nuevoChecklistTexto" placeholder="Nueva tarea de checklist">
                        <button type="button" class="btn btn-outline-primary" id="btnAgregarChecklist">Agregar</button>
                    </div>
                </div>
                <div class="modal-footer justify-content-between">
                    <button type="button" class="btn btn-danger" id="btnEliminarCard">Eliminar tarjeta</button>
                    <div class="d-flex gap-2">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cerrar</button>
                        <button type="button" class="btn btn-primary" id="btnGuardarCard">Guardar</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('script')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const STORAGE_KEY = 'crm_tablero_proyecto_v1';
            const boardEl = document.getElementById('proyectoBoard');
            const cardModalEl = document.getElementById('modalCardProyecto');
            const cardModal = new bootstrap.Modal(cardModalEl);
            const cardTituloEdit = document.getElementById('cardTituloEdit');
            const cardChecklistWrap = document.getElementById('cardChecklistWrap');
            const nuevoChecklistTexto = document.getElementById('nuevoChecklistTexto');
            const btnAgregarChecklist = document.getElementById('btnAgregarChecklist');
            const btnGuardarCard = document.getElementById('btnGuardarCard');
            const btnEliminarCard = document.getElementById('btnEliminarCard');
            const hasSwal = typeof Swal !== 'undefined';

            let board = cargarBoard();
            let cardEnEdicion = { listId: null, cardId: null };

            function uid() {
                return String(Date.now()) + String(Math.floor(Math.random() * 10000));
            }

            function boardBase() {
                return {
                    lists: [
                        {
                            id: uid(),
                            title: 'Estructurando Una Idea',
                            cards: [
                                { id: uid(), title: 'Monitoreo', checklist: [{ id: uid(), text: 'Paso 1', done: false }, { id: uid(), text: 'Paso 2', done: false }, { id: uid(), text: 'Paso 3', done: false }] },
                                { id: uid(), title: 'Contabilidad', checklist: [] },
                                { id: uid(), title: 'Finanzas', checklist: [{ id: uid(), text: 'Analisis', done: false }, { id: uid(), text: 'Aprobacion', done: false }] },
                            ],
                        },
                        {
                            id: uid(),
                            title: 'Lista de Proyecto',
                            cards: [
                                { id: uid(), title: 'Mercadeo', checklist: [{ id: uid(), text: 'Boceto', done: false }, { id: uid(), text: 'Piezas', done: false }, { id: uid(), text: 'Aprobacion', done: false }, { id: uid(), text: 'Publicacion', done: false }, { id: uid(), text: 'Seguimiento', done: false }, { id: uid(), text: 'Reporte', done: false }] },
                                { id: uid(), title: 'RRHH', checklist: [{ id: uid(), text: 'Perfil', done: false }, { id: uid(), text: 'Publicar', done: false }, { id: uid(), text: 'Entrevista', done: false }, { id: uid(), text: 'Seleccion', done: false }, { id: uid(), text: 'Oferta', done: false }, { id: uid(), text: 'Ingreso', done: false }] },
                            ],
                        },
                        {
                            id: uid(),
                            title: 'En Proceso',
                            cards: [
                                { id: uid(), title: 'Operaciones', checklist: [{ id: uid(), text: 'Task 1', done: true }, { id: uid(), text: 'Task 2', done: true }, { id: uid(), text: 'Task 3', done: true }, { id: uid(), text: 'Task 4', done: false }, { id: uid(), text: 'Task 5', done: false }, { id: uid(), text: 'Task 6', done: false }] },
                                { id: uid(), title: 'Desarrollo de ERP', checklist: Array.from({ length: 21 }).map(function (_, index) { return { id: uid(), text: 'Item ' + (index + 1), done: index < 14 }; }) },
                            ],
                        },
                        {
                            id: uid(),
                            title: 'Proyectos Terminado',
                            cards: [
                                { id: uid(), title: 'Requerimiento a SJ', checklist: [{ id: uid(), text: 'Req 1', done: true }, { id: uid(), text: 'Req 2', done: true }, { id: uid(), text: 'Req 3', done: true }] },
                            ],
                        },
                    ],
                };
            }

            function cargarBoard() {
                try {
                    const raw = localStorage.getItem(STORAGE_KEY);
                    if (!raw) {
                        return boardBase();
                    }

                    const parsed = JSON.parse(raw);
                    if (!parsed || !Array.isArray(parsed.lists)) {
                        return boardBase();
                    }

                    return parsed;
                } catch (_e) {
                    return boardBase();
                }
            }

            function guardarBoard() {
                localStorage.setItem(STORAGE_KEY, JSON.stringify(board));
            }

            function resumenChecklist(card) {
                const total = Array.isArray(card.checklist) ? card.checklist.length : 0;
                if (total === 0) {
                    return '';
                }
                const done = card.checklist.filter(function (c) { return !!c.done; }).length;
                return done + '/' + total;
            }

            function porcentajeChecklist(card) {
                const total = Array.isArray(card.checklist) ? card.checklist.length : 0;
                if (total === 0) {
                    return 0;
                }

                const done = card.checklist.filter(function (c) { return !!c.done; }).length;
                return Math.round((done / total) * 100);
            }

            function colorPorLista(nombreLista) {
                const nombre = String(nombreLista || '').toLowerCase();
                if (nombre.includes('idea')) return '#6f42c1';
                if (nombre.includes('proceso')) return '#0dcaf0';
                if (nombre.includes('terminado')) return '#198754';
                if (nombre.includes('lista')) return '#3b82f6';
                return '#64748b';
            }

            function renderBoard() {
                boardEl.innerHTML = '';

                board.lists.forEach(function (list) {
                    const listEl = document.createElement('div');
                    listEl.className = 'proyecto-col';
                    listEl.dataset.listId = list.id;

                    const cardsHtml = list.cards.map(function (card) {
                        const meta = resumenChecklist(card);
                        return `
                            <div class="proyecto-card" draggable="true" data-card-id="${card.id}" data-list-id="${list.id}">
                                <div class="proyecto-card-title">${escapeHtml(card.title)}</div>
                                ${meta !== '' ? `<div class="proyecto-card-meta"><i class="ri-checkbox-line"></i>${meta}</div>` : ''}
                            </div>
                        `;
                    }).join('');

                    listEl.innerHTML = `
                        <div class="proyecto-col-header">
                            <span>${escapeHtml(list.title)}</span>
                            <button type="button" class="btn btn-sm btn-soft-light btn-eliminar-lista" title="Eliminar lista"><i class="ri-delete-bin-line"></i></button>
                        </div>
                        <div class="proyecto-col-cards" data-drop-list="${list.id}">
                            ${cardsHtml}
                        </div>
                        <div class="proyecto-add" data-add-card="${list.id}">+ Anade una tarjeta</div>
                    `;

                    boardEl.appendChild(listEl);
                });

                const addListEl = document.createElement('div');
                addListEl.className = 'proyecto-new-list';
                addListEl.id = 'btnAddList';
                addListEl.textContent = '+ Anade otra lista';
                boardEl.appendChild(addListEl);

                enlazarEventos();
            }

            function showInfo(title, text) {
                if (hasSwal) {
                    Swal.fire({
                        icon: 'info',
                        title: title,
                        text: text,
                    });
                    return;
                }

                alert(text);
            }

            async function askText(title, label, placeholder) {
                if (hasSwal) {
                    const result = await Swal.fire({
                        title: title,
                        input: 'text',
                        inputLabel: label,
                        inputPlaceholder: placeholder || '',
                        showCancelButton: true,
                        confirmButtonText: 'Guardar',
                        cancelButtonText: 'Cancelar',
                        inputValidator: function (value) {
                            if (!value || value.trim() === '') {
                                return 'Este campo es obligatorio.';
                            }

                            return null;
                        }
                    });

                    if (!result.isConfirmed) {
                        return null;
                    }

                    return String(result.value || '').trim();
                }

                const value = prompt(label || title);
                if (!value || value.trim() === '') {
                    return null;
                }

                return value.trim();
            }

            async function askConfirm(title, text) {
                if (hasSwal) {
                    const result = await Swal.fire({
                        title: title,
                        text: text,
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonText: 'Si',
                        cancelButtonText: 'Cancelar',
                    });

                    return !!result.isConfirmed;
                }

                return confirm(text);
            }

            function mostrarMiniTarjetaMovimiento(titulo, desde, hacia, progresoTexto, progresoPct, colorDestino) {
                const existing = document.querySelector('.mini-move-card');
                if (existing) {
                    existing.remove();
                }

                const mini = document.createElement('div');
                mini.className = 'mini-move-card';
                mini.innerHTML = `
                    <div><strong>${escapeHtml(titulo)}</strong></div>
                    ${progresoTexto ? `<small>Checklist ${escapeHtml(progresoTexto)} (${progresoPct}%)</small>` : ''}
                    ${progresoTexto ? `<div class="mini-move-progress"><div style="width:${progresoPct}%; background:${escapeHtml(colorDestino)};"></div></div>` : ''}
                    <span class="mini-move-chip" style="background:${escapeHtml(colorDestino)};">${escapeHtml(hacia)}</span>
                    <small>Movida de ${escapeHtml(desde)} a ${escapeHtml(hacia)}</small>
                `;

                document.body.appendChild(mini);
                setTimeout(function () {
                    mini.remove();
                }, 1800);
            }

            function escapeHtml(text) {
                return String(text || '')
                    .replace(/&/g, '&amp;')
                    .replace(/</g, '&lt;')
                    .replace(/>/g, '&gt;')
                    .replace(/"/g, '&quot;')
                    .replace(/'/g, '&#039;');
            }

            function enlazarEventos() {
                document.querySelectorAll('[data-add-card]').forEach(function (btn) {
                    btn.addEventListener('click', async function () {
                        const listId = this.dataset.addCard;
                        const titulo = await askText('Nueva tarjeta', 'Titulo de la tarjeta', 'Ej: Monitoreo');
                        if (!titulo) {
                            return;
                        }

                        const list = board.lists.find(function (l) { return l.id === listId; });
                        if (!list) {
                            return;
                        }

                        list.cards.push({ id: uid(), title: titulo, checklist: [] });
                        guardarBoard();
                        renderBoard();
                    });
                });

                document.getElementById('btnAddList')?.addEventListener('click', async function () {
                    const titulo = await askText('Nueva lista', 'Nombre de la lista', 'Ej: Pendientes');
                    if (!titulo) {
                        return;
                    }

                    board.lists.push({ id: uid(), title: titulo, cards: [] });
                    guardarBoard();
                    renderBoard();
                });

                document.querySelectorAll('.btn-eliminar-lista').forEach(function (btn) {
                    btn.addEventListener('click', async function (event) {
                        event.stopPropagation();
                        const listId = this.closest('.proyecto-col')?.dataset?.listId || '';
                        if (!listId) {
                            return;
                        }

                        const ok = await askConfirm('Eliminar lista', 'Deseas eliminar esta lista y sus tarjetas?');
                        if (!ok) {
                            return;
                        }

                        board.lists = board.lists.filter(function (l) { return l.id !== listId; });
                        guardarBoard();
                        renderBoard();
                    });
                });

                document.querySelectorAll('.proyecto-card').forEach(function (cardEl) {
                    cardEl.addEventListener('click', function () {
                        abrirModalTarjeta(this.dataset.listId, this.dataset.cardId);
                    });

                    cardEl.addEventListener('dragstart', function (event) {
                        this.classList.add('dragging');

                        const tituloCard = this.querySelector('.proyecto-card-title')?.textContent || 'Tarjeta';
                        const ghost = document.createElement('div');
                        ghost.className = 'proyecto-drag-ghost';
                        ghost.textContent = tituloCard;
                        document.body.appendChild(ghost);
                        event.dataTransfer.setDragImage(ghost, 14, 14);
                        setTimeout(function () {
                            ghost.remove();
                        }, 0);

                        event.dataTransfer.setData('text/plain', JSON.stringify({
                            fromListId: this.dataset.listId,
                            cardId: this.dataset.cardId,
                        }));
                    });

                    cardEl.addEventListener('dragend', function () {
                        this.classList.remove('dragging');
                    });
                });

                document.querySelectorAll('[data-drop-list]').forEach(function (zone) {
                    zone.addEventListener('dragover', function (event) {
                        event.preventDefault();
                        this.classList.add('drop-target');
                    });

                    zone.addEventListener('dragleave', function () {
                        this.classList.remove('drop-target');
                    });

                    zone.addEventListener('drop', function (event) {
                        event.preventDefault();
                        this.classList.remove('drop-target');

                        const raw = event.dataTransfer.getData('text/plain');
                        if (!raw) {
                            return;
                        }

                        let payload = null;
                        try {
                            payload = JSON.parse(raw);
                        } catch (_e) {
                            return;
                        }

                        const toListId = this.dataset.dropList;
                        moverTarjeta(payload.fromListId, toListId, payload.cardId);
                    });
                });
            }

            function moverTarjeta(fromListId, toListId, cardId) {
                if (!fromListId || !toListId || !cardId) {
                    return;
                }

                const fromList = board.lists.find(function (l) { return l.id === fromListId; });
                const toList = board.lists.find(function (l) { return l.id === toListId; });
                if (!fromList || !toList) {
                    return;
                }

                const index = fromList.cards.findIndex(function (c) { return c.id === cardId; });
                if (index < 0) {
                    return;
                }

                const card = fromList.cards[index];
                fromList.cards.splice(index, 1);
                toList.cards.push(card);

                guardarBoard();
                renderBoard();
                const progresoTexto = resumenChecklist(card);
                const progresoPct = porcentajeChecklist(card);
                const colorDestino = colorPorLista(toList.title || '');
                mostrarMiniTarjetaMovimiento(
                    card.title || 'Tarjeta',
                    fromList.title || 'Lista',
                    toList.title || 'Lista',
                    progresoTexto,
                    progresoPct,
                    colorDestino
                );
            }

            function buscarTarjeta(listId, cardId) {
                const list = board.lists.find(function (l) { return l.id === listId; });
                if (!list) {
                    return null;
                }

                const card = list.cards.find(function (c) { return c.id === cardId; });
                if (!card) {
                    return null;
                }

                return { list: list, card: card };
            }

            function renderChecklistEnModal(card) {
                const checklist = Array.isArray(card.checklist) ? card.checklist : [];
                if (checklist.length === 0) {
                    cardChecklistWrap.innerHTML = '<div class="text-muted small">Sin checklist todavia.</div>';
                    return;
                }

                cardChecklistWrap.innerHTML = checklist.map(function (item) {
                    return `
                        <div class="check-item" data-check-id="${item.id}">
                            <label class="d-flex align-items-center gap-2 mb-0 w-100">
                                <input type="checkbox" class="form-check-input check-toggle" ${item.done ? 'checked' : ''}>
                                <span>${escapeHtml(item.text)}</span>
                            </label>
                            <button type="button" class="btn btn-sm btn-soft-danger check-delete"><i class="ri-close-line"></i></button>
                        </div>
                    `;
                }).join('');

                cardChecklistWrap.querySelectorAll('.check-toggle').forEach(function (checkbox) {
                    checkbox.addEventListener('change', function () {
                        const wrap = this.closest('[data-check-id]');
                        const itemId = wrap?.dataset?.checkId || '';
                        const ref = buscarTarjeta(cardEnEdicion.listId, cardEnEdicion.cardId);
                        if (!ref) {
                            return;
                        }

                        const item = ref.card.checklist.find(function (c) { return c.id === itemId; });
                        if (!item) {
                            return;
                        }

                        item.done = this.checked;
                        guardarBoard();
                        renderChecklistEnModal(ref.card);
                    });
                });

                cardChecklistWrap.querySelectorAll('.check-delete').forEach(function (btn) {
                    btn.addEventListener('click', function () {
                        const wrap = this.closest('[data-check-id]');
                        const itemId = wrap?.dataset?.checkId || '';
                        const ref = buscarTarjeta(cardEnEdicion.listId, cardEnEdicion.cardId);
                        if (!ref) {
                            return;
                        }

                        ref.card.checklist = ref.card.checklist.filter(function (c) { return c.id !== itemId; });
                        guardarBoard();
                        renderChecklistEnModal(ref.card);
                    });
                });
            }

            function abrirModalTarjeta(listId, cardId) {
                cardEnEdicion = { listId: listId, cardId: cardId };
                const ref = buscarTarjeta(listId, cardId);
                if (!ref) {
                    return;
                }

                cardTituloEdit.value = ref.card.title || '';
                nuevoChecklistTexto.value = '';
                renderChecklistEnModal(ref.card);
                cardModal.show();
            }

            btnAgregarChecklist.addEventListener('click', function () {
                const texto = (nuevoChecklistTexto.value || '').trim();
                if (texto === '') {
                    return;
                }

                const ref = buscarTarjeta(cardEnEdicion.listId, cardEnEdicion.cardId);
                if (!ref) {
                    return;
                }

                if (!Array.isArray(ref.card.checklist)) {
                    ref.card.checklist = [];
                }

                ref.card.checklist.push({ id: uid(), text: texto, done: false });
                nuevoChecklistTexto.value = '';
                guardarBoard();
                renderChecklistEnModal(ref.card);
            });

            btnGuardarCard.addEventListener('click', function () {
                const ref = buscarTarjeta(cardEnEdicion.listId, cardEnEdicion.cardId);
                if (!ref) {
                    return;
                }

                const titulo = (cardTituloEdit.value || '').trim();
                if (titulo === '') {
                    showInfo('Campo requerido', 'El titulo no puede estar vacio.');
                    return;
                }

                ref.card.title = titulo;
                guardarBoard();
                renderBoard();
                cardModal.hide();
            });

            btnEliminarCard.addEventListener('click', async function () {
                const ref = buscarTarjeta(cardEnEdicion.listId, cardEnEdicion.cardId);
                if (!ref) {
                    return;
                }

                const ok = await askConfirm('Eliminar tarjeta', 'Deseas eliminar esta tarjeta?');
                if (!ok) {
                    return;
                }

                ref.list.cards = ref.list.cards.filter(function (c) { return c.id !== ref.card.id; });
                guardarBoard();
                renderBoard();
                cardModal.hide();
            });

            renderBoard();
        });
    </script>
@endsection

