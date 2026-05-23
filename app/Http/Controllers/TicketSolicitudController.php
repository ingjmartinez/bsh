<?php

namespace App\Http\Controllers;

use App\Models\TicketSolicitud;
use App\Services\WhatsAppService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class TicketSolicitudController extends Controller
{
    public function __construct(private readonly WhatsAppService $whatsAppService)
    {
        $this->middleware('role_or_permission:superadmin|admin|tickets.view')->only(['index', 'activity']);
        $this->middleware('role_or_permission:superadmin|admin|tickets.manage')->only(['store', 'updateEstado']);
    }

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
            'pagados' => (clone $baseQuery)->whereIn('estado', [TicketSolicitud::ESTADO_PAGADO, TicketSolicitud::ESTADO_TICKET_PAGADO])->count(),
            'nulos' => (clone $baseQuery)->where('estado', TicketSolicitud::ESTADO_NULO)->count(),
            'pagar' => (clone $baseQuery)->where('categoria', TicketSolicitud::CATEGORIA_PAGAR)->count(),
            'anular' => (clone $baseQuery)->where('categoria', TicketSolicitud::CATEGORIA_ANULAR)->count(),
        ];

        $solicitudes = (clone $baseQuery)
            ->with('procesadoPor:id,name')
            ->latest()
            ->paginate(20)
            ->withQueryString();

        $ticketFeedSignature = $this->buildFeedSignature($baseQuery);

        return view('tickets.index', [
            'filtros' => $filtros,
            'solicitudes' => $solicitudes,
            'stats' => $stats,
            'setupPending' => $setupPending,
            'ticketFeedSignature' => $ticketFeedSignature,
            'ticketActivityUrl' => route('tickets.activity', $filtros),
        ]);
    }

    public function activity(Request $request): JsonResponse
    {
        if (!Schema::hasTable('ticket_solicitudes')) {
            return response()->json([
                'setup_pending' => true,
                'signature' => 'setup-pending',
            ]);
        }

        $filtros = $request->only(['categoria', 'estado', 'desde', 'hasta', 'buscar']);
        $baseQuery = TicketSolicitud::query()->filtro($filtros);

        return response()->json([
            'setup_pending' => false,
            'signature' => $this->buildFeedSignature($baseQuery),
            'server_time' => now()->toIso8601String(),
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

        $estadosPermitidos = $this->allowedEstadosForCategoria((string) $ticket->categoria);

        $validated = $request->validate([
            'estado' => ['required', Rule::in($estadosPermitidos)],
            'notas' => [
                Rule::requiredIf($request->input('estado') === TicketSolicitud::ESTADO_TICKET_PAGADO),
                'nullable',
                'string',
                'max:1000',
            ],
        ], [
            'estado.in' => 'El estado seleccionado no es valido para esta categoria de ticket.',
            'notas.required' => 'Debes indicar la terminal que pago.',
        ]);

        $estadoAnterior = (string) $ticket->estado;

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
        $this->notifyResolutionByWhatsApp($ticket, $estadoAnterior);

        return back()->with('success', 'Estado del ticket actualizado.');
    }

    private function notifyResolutionByWhatsApp(TicketSolicitud $ticket, string $estadoAnterior): void
    {
        if ($estadoAnterior === (string) $ticket->estado) {
            return;
        }

        if (!in_array($ticket->estado, [TicketSolicitud::ESTADO_PAGADO, TicketSolicitud::ESTADO_TICKET_PAGADO, TicketSolicitud::ESTADO_NULO], true)) {
            return;
        }

        $recipient = $this->formatRecipient((string) $ticket->phone);

        if ($recipient === null) {
            return;
        }

        $terminalPago = $this->terminalPagoLine($ticket);

        $message = "Hola, tu solicitud {$ticket->codigo} ya fue resuelta.\n\n"
            . "Categoria: {$ticket->categoria_label}\n"
            . "Codigo terminal: {$ticket->ticket_numero}\n"
            . "Estado final: {$ticket->estado_label}\n\n"
            . ($terminalPago !== null ? $terminalPago . "\n\n" : '')
            . "Gracias por comunicarte con nosotros.";

        try {
            $result = $this->whatsAppService->sendText($recipient, $message);

            if (!($result['success'] ?? false)) {
                Log::warning('No se pudo enviar notificacion de resolucion de ticket', [
                    'ticket_id' => $ticket->id,
                    'phone' => $ticket->phone,
                    'estado' => $ticket->estado,
                    'provider_status' => $result['status'] ?? null,
                    'provider_message' => $result['message'] ?? null,
                ]);
            }
        } catch (\Throwable $e) {
            Log::error('Error enviando notificacion de resolucion de ticket', [
                'ticket_id' => $ticket->id,
                'phone' => $ticket->phone,
                'estado' => $ticket->estado,
                'message' => $e->getMessage(),
            ]);
        }
    }

    private function formatRecipient(string $phone): ?string
    {
        $digits = preg_replace('/\D+/', '', $phone) ?? '';

        if ($digits === '' || strlen($digits) < 8) {
            return null;
        }

        return str_starts_with($phone, '+') ? $phone : '+' . $digits;
    }

    private function terminalPagoLine(TicketSolicitud $ticket): ?string
    {
        if ($ticket->estado !== TicketSolicitud::ESTADO_TICKET_PAGADO) {
            return null;
        }

        $notas = trim((string) $ticket->notas);

        return $notas !== '' ? $notas : null;
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

    private function allowedEstadosForCategoria(string $categoria): array
    {
        return match ($categoria) {
            TicketSolicitud::CATEGORIA_ANULAR => [
                TicketSolicitud::ESTADO_PENDIENTE,
                TicketSolicitud::ESTADO_NULO,
            ],
            TicketSolicitud::CATEGORIA_PAGAR => [
                TicketSolicitud::ESTADO_PENDIENTE,
                TicketSolicitud::ESTADO_PAGADO,
                TicketSolicitud::ESTADO_TICKET_PAGADO,
            ],
            default => [
                TicketSolicitud::ESTADO_PENDIENTE,
                TicketSolicitud::ESTADO_PAGADO,
                TicketSolicitud::ESTADO_TICKET_PAGADO,
                TicketSolicitud::ESTADO_NULO,
            ],
        };
    }

    private function buildFeedSignature(Builder $query): string
    {
        $snapshot = (clone $query)
            ->selectRaw('COUNT(*) as total, MAX(updated_at) as last_activity_at, MAX(id) as max_id')
            ->first();

        $total = (int) ($snapshot->total ?? 0);
        $lastActivity = (string) ($snapshot->last_activity_at ?? '');
        $maxId = (int) ($snapshot->max_id ?? 0);

        return sha1($total . '|' . $lastActivity . '|' . $maxId);
    }
}
