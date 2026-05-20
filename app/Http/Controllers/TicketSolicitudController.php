<?php

namespace App\Http\Controllers;

use App\Models\TicketSolicitud;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

class TicketSolicitudController extends Controller
{
    public function index(Request $request): View
    {
        $filtros = $request->only(['categoria', 'estado', 'desde', 'hasta', 'buscar']);
        $setupPending = !Schema::hasTable('ticket_solicitudes');

        if ($setupPending) {
            return view('tickets.index', [
                'filtros' => $filtros,
                'solicitudes' => new LengthAwarePaginator([], 0, 20, 1, [
                    'path' => $request->url(),
                    'query' => $request->query(),
                ]),
                'stats' => $this->emptyStats(),
                'setupPending' => true,
            ]);
        }

        $baseQuery = TicketSolicitud::query()->filtro($filtros);

        $stats = [
            'total' => (clone $baseQuery)->count(),
            'pendientes' => (clone $baseQuery)->where('estado', TicketSolicitud::ESTADO_PENDIENTE)->count(),
            'pagados' => (clone $baseQuery)->where('estado', TicketSolicitud::ESTADO_PAGADO)->count(),
            'nulos' => (clone $baseQuery)->where('estado', TicketSolicitud::ESTADO_NULO)->count(),
            'pagar' => (clone $baseQuery)->where('categoria', TicketSolicitud::CATEGORIA_PAGAR)->count(),
            'anular' => (clone $baseQuery)->where('categoria', TicketSolicitud::CATEGORIA_ANULAR)->count(),
        ];

        $solicitudes = (clone $baseQuery)
            ->with('procesadoPor:id,name')
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('tickets.index', [
            'filtros' => $filtros,
            'solicitudes' => $solicitudes,
            'stats' => $stats,
            'setupPending' => $setupPending,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        if (!Schema::hasTable('ticket_solicitudes')) {
            return back()->withErrors(['tickets' => 'La tabla del modulo aun no existe. Ejecuta las migraciones.']);
        }

        $validated = $request->validate([
            'categoria' => 'required|in:pagar_ticket,anular_ticket',
            'ticket_numero' => 'required|string|max:80',
            'phone' => 'nullable|string|max:32',
            'mensaje_original' => 'nullable|string|max:1000',
            'attachment_url' => 'nullable|url|max:1200',
        ]);

        TicketSolicitud::create([
            'phone' => preg_replace('/\D+/', '', (string) ($validated['phone'] ?? '')) ?: 'manual',
            'categoria' => $validated['categoria'],
            'ticket_numero' => trim($validated['ticket_numero']),
            'estado' => TicketSolicitud::ESTADO_PENDIENTE,
            'mensaje_original' => $validated['mensaje_original'] ?? 'Registro manual',
            'attachment_url' => $validated['attachment_url'] ?? null,
        ]);

        return back()->with('success', 'Solicitud de ticket registrada correctamente.');
    }

    public function updateEstado(Request $request, TicketSolicitud $ticket): RedirectResponse
    {
        if (!Schema::hasTable('ticket_solicitudes')) {
            return back()->withErrors(['tickets' => 'La tabla del modulo aun no existe. Ejecuta las migraciones.']);
        }

        $validated = $request->validate([
            'estado' => 'required|in:pendiente,pagado,nulo',
            'notas' => 'nullable|string|max:1000',
        ]);

        $ticket->fill([
            'estado' => $validated['estado'],
            'notas' => $validated['notas'] ?? $ticket->notas,
        ]);

        if ($validated['estado'] === TicketSolicitud::ESTADO_PENDIENTE) {
            $ticket->procesado_por_id = null;
            $ticket->procesado_at = null;
        } else {
            $ticket->procesado_por_id = auth()->id();
            $ticket->procesado_at = now();
        }

        $ticket->save();

        return back()->with('success', 'Estado del ticket actualizado.');
    }

    private function emptyStats(): array
    {
        return [
            'total' => 0,
            'pendientes' => 0,
            'pagados' => 0,
            'nulos' => 0,
            'pagar' => 0,
            'anular' => 0,
        ];
    }
}
