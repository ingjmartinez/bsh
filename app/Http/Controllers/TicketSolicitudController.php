<?php

namespace App\Http\Controllers;

use App\Models\ChatbotSession;
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
    private const RECHAZO_MOTIVOS_PAGO = [
        'RECHAZADO: EL TICKET DEBE ESTAR VISIBLE Y COMPLETO.',
        'RECHAZADO: ESCRIBIR EN EL CENTRO LA PALABRA PAGAR CON LAPICERO.',
        'RECHAZADO: ESCRIBA SU NUMERO DE TERMINAL CORRECTAMENTE.',
        'RECHAZADO: TICKET NO TIENE PREMIO.',
        'RECHAZADO: TICKET YA FUE PAGADO.',
        'RECHAZADO: TICKET YA POSEE UN TOKEN DE PAGO ACTIVO.',
        'RECHAZADO: ESCRIBA SU NUMERO DE TERMINAL CORRECTAMENTE.',
    ];

    private const RECHAZO_MOTIVOS_NULO = [
        'RECHAZADO: ESCRIBIR EN EL CENTRO LA PALABRA ANULAR CON LAPICERO.',
        'RECHAZADO: NO HA REALIZADO LA JUGADA OTRA VEZ.',
        'RECHAZADO: NO MARCO EL TICKET A ANULAR EN LA RELACION DE TICKET.',
        'RECHAZADO: SOLICITUD TARDIA, SORTEO CERRADO.',
        'RECHAZADO: NO SE ANULAN RECARGAS.',
        'RECHAZADO: NO SE ANULAN NO TRADICIONAL.',
        'RECHAZADO. IMPRIMA UNA RELACION DE TICKET Y MARQUE LA JUGADA QUE DESEA ANULAR',
    ];

    public function __construct(private readonly WhatsAppService $whatsAppService)
    {
        $this->middleware('role_or_permission:superadmin|admin|tickets.view')->only(['index', 'activity', 'dashboard']);
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
                'rechazoMotivos' => $this->rechazoMotivos(),
            ]);
        }

        $baseQuery = TicketSolicitud::query()->filtro($filtros);
        $stats = $this->ticketStats($baseQuery);

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
            'rechazoMotivos' => $this->rechazoMotivos(),
        ]);
    }

    public function dashboard(Request $request): View
    {
        $filtros = $request->only(['estado', 'desde', 'hasta', 'buscar']);
        $setupPending = !Schema::hasTable('ticket_solicitudes');

        if ($setupPending) {
            return view('dashboard.tickets.index', [
                'filtros' => $filtros,
                'stats' => $this->emptyStats(),
                'dashboard' => $this->emptyDashboardData(),
                'setupPending' => true,
                'ticketFeedSignature' => null,
                'ticketActivityUrl' => null,
            ]);
        }

        $baseQuery = TicketSolicitud::query()->filtro($filtros);
        $ticketFeedSignature = $this->buildFeedSignature($baseQuery);

        return view('dashboard.tickets.index', [
            'filtros' => $filtros,
            'stats' => $this->ticketStats($baseQuery),
            'dashboard' => $this->ticketDashboardData($baseQuery),
            'setupPending' => false,
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
            'attachment_url' => 'required|url|max:1200',
        ], [
            'attachment_url.required' => 'Debes agregar la imagen del comprobante para registrar pagos o anulaciones.',
            'attachment_url.url' => 'La imagen del comprobante debe ser un enlace valido.',
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
                Rule::requiredIf($this->requiresTerminalDetail($request, $ticket)),
                'nullable',
                'string',
                'max:1000',
            ],
            'rechazo_motivo' => [
                Rule::requiredIf($this->requiresRejectReason($request)),
                'nullable',
                Rule::in($this->rejectReasonsForCategory((string) $ticket->categoria)),
            ],
        ], [
            'estado.in' => 'El estado seleccionado no es valido para esta categoria de ticket.',
            'notas.required' => 'Debes completar la informacion solicitada antes de actualizar el ticket.',
            'rechazo_motivo.required' => 'Debes seleccionar el motivo del rechazo.',
            'rechazo_motivo.in' => 'El motivo de rechazo seleccionado no es valido para esta categoria.',
        ]);

        if ($this->isEstadoCerrado($ticket)) {
            return back()->withErrors(['tickets' => 'Este ticket ya fue gestionado y no puede cambiar de estado.']);
        }

        if (
            $ticket->estado === TicketSolicitud::ESTADO_TICKET_PAGADO
            && $validated['estado'] !== TicketSolicitud::ESTADO_PAGADO
        ) {
            return back()->withErrors(['tickets' => 'Este ticket solo puede cerrarse como pagado.']);
        }

        if (
            $validated['estado'] === TicketSolicitud::ESTADO_TOKEN_ENVIADO
            && $ticket->estado !== TicketSolicitud::ESTADO_TOKEN_ENVIADO
        ) {
            return back()->withErrors(['tickets' => 'El estado Token enviado solo se asigna despues de enviar el token por WhatsApp.']);
        }

        if (
            $validated['estado'] === TicketSolicitud::ESTADO_TOKEN_NO_FUNCIONO
            && $ticket->estado !== TicketSolicitud::ESTADO_TOKEN_NO_FUNCIONO
        ) {
            return back()->withErrors(['tickets' => 'El estado Token No Funciono solo se asigna cuando el cliente indica que el token no le funciono.']);
        }

        $estadoAnterior = (string) $ticket->estado;
        $estadoDestino = $validated['estado'];
        $tokenEnviado = false;

        if ($estadoDestino === TicketSolicitud::ESTADO_RECHAZADO) {
            $validated['notas'] = $validated['rechazo_motivo'];
        }

        if ($this->requiresTokenSend($request, $ticket)) {
            $token = trim((string) ($validated['notas'] ?? ''));
            $sendResult = $this->sendTokenByWhatsApp($ticket, $token);

            if (!($sendResult['success'] ?? false)) {
                return back()->withErrors([
                    'tickets' => 'No se pudo enviar el token por WhatsApp. El ticket no fue actualizado.',
                ]);
            }

            $estadoDestino = TicketSolicitud::ESTADO_TOKEN_ENVIADO;
            $validated['notas'] = "Token enviado: {$token}";
            $tokenEnviado = true;
        }

        $ticket->fill([
            'estado' => $estadoDestino,
            'notas' => $validated['notas'] ?? $ticket->notas,
        ]);

        if ($estadoDestino === TicketSolicitud::ESTADO_PENDIENTE) {
            $ticket->procesado_por_id = null;
            $ticket->procesado_at = null;
        } else {
            $ticket->procesado_por_id = auth()->id();
            $ticket->procesado_at = now();
        }

        $ticket->save();
        if (!$tokenEnviado) {
            $this->notifyResolutionByWhatsApp($ticket, $estadoAnterior);
        }

        if ($estadoDestino === TicketSolicitud::ESTADO_RECHAZADO) {
            $this->closeChatbotSessionForTicket($ticket);
        }

        return back()->with('success', 'Estado del ticket actualizado.');
    }

    private function sendTokenByWhatsApp(TicketSolicitud $ticket, string $token): array
    {
        $recipient = $this->formatRecipient((string) $ticket->phone);

        if ($recipient === null) {
            return [
                'success' => false,
                'message' => 'Telefono invalido.',
            ];
        }

        $message = "Hola, hemos recibido tu solicitud {$ticket->codigo}.\n\n"
            . "Token: {$token}\n"
            . "Codigo terminal: {$ticket->ticket_numero}\n\n"
            . "Utiliza este token para continuar con la gestion de tu ticket.\n\n"
            . "1- si me funciono ticket pagado\n"
            . "2- no me funciono solicitar token";

        try {
            return $this->whatsAppService->sendText($recipient, $message);
        } catch (\Throwable $e) {
            Log::error('Error enviando token de ticket por WhatsApp', [
                'ticket_id' => $ticket->id,
                'phone' => $ticket->phone,
                'message' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'message' => $e->getMessage(),
            ];
        }
    }

    private function notifyResolutionByWhatsApp(TicketSolicitud $ticket, string $estadoAnterior): void
    {
        if ($estadoAnterior === (string) $ticket->estado) {
            return;
        }

        if (!in_array($ticket->estado, [
            TicketSolicitud::ESTADO_PENDIENTE,
            TicketSolicitud::ESTADO_PAGADO,
            TicketSolicitud::ESTADO_TOKEN_ENVIADO,
            TicketSolicitud::ESTADO_TOKEN_NO_FUNCIONO,
            TicketSolicitud::ESTADO_TICKET_PAGADO,
            TicketSolicitud::ESTADO_NULO,
            TicketSolicitud::ESTADO_EN_PROCESO,
            TicketSolicitud::ESTADO_AVERIA_CERRADA,
            TicketSolicitud::ESTADO_RECHAZADO,
        ], true)) {
            return;
        }

        if (
            in_array($ticket->estado, [TicketSolicitud::ESTADO_PENDIENTE, TicketSolicitud::ESTADO_EN_PROCESO, TicketSolicitud::ESTADO_AVERIA_CERRADA], true)
            && $ticket->categoria !== TicketSolicitud::CATEGORIA_AVERIA
        ) {
            return;
        }

        $recipient = $this->formatRecipient((string) $ticket->phone);

        if ($recipient === null) {
            return;
        }

        $terminalDetail = $this->terminalDetailLine($ticket);

        if (
            $estadoAnterior === TicketSolicitud::ESTADO_TICKET_PAGADO
            && $ticket->estado === TicketSolicitud::ESTADO_PAGADO
        ) {
            $message = "Hola, tu orden {$ticket->codigo} fue cerrada.\n\n"
                . "Codigo terminal: {$ticket->ticket_numero}\n\n"
                . "Estado: Finalizado\n\n"
                . "Gracias por comunicarte con nosotros.";
        } elseif ($ticket->estado === TicketSolicitud::ESTADO_RECHAZADO) {
            $message = "Hola, tu solicitud {$ticket->codigo} fue rechazada.\n\n"
                . "Categoria: {$ticket->categoria_label}\n"
                . "Codigo terminal: {$ticket->ticket_numero}\n\n"
                . "Motivo: " . trim((string) $ticket->notas) . "\n\n"
                . "La sesion fue cerrada para que puedas crear otra solicitud.";
        } else {
            $message = "Hola, tu solicitud {$ticket->codigo} fue actualizada.\n\n"
                . "Categoria: {$ticket->categoria_label}\n"
                . "Codigo terminal: {$ticket->ticket_numero}\n"
                . "Estado actual: {$ticket->estado_label}\n\n"
                . ($terminalDetail !== null ? $terminalDetail . "\n\n" : '')
                . "Gracias por comunicarte con nosotros.";
        }

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

    private function terminalDetailLine(TicketSolicitud $ticket): ?string
    {
        $requiresDetail = $ticket->estado === TicketSolicitud::ESTADO_TICKET_PAGADO
            || ($ticket->categoria === TicketSolicitud::CATEGORIA_ANULAR && $ticket->estado === TicketSolicitud::ESTADO_NULO);

        if (!$requiresDetail) {
            return null;
        }

        $notas = trim((string) $ticket->notas);

        return $notas !== '' ? $notas : null;
    }

    private function requiresTerminalDetail(Request $request, TicketSolicitud $ticket): bool
    {
        $estado = (string) $request->input('estado');

        return $estado === TicketSolicitud::ESTADO_TICKET_PAGADO
            || $this->requiresTokenSend($request, $ticket)
            || ($ticket->categoria === TicketSolicitud::CATEGORIA_ANULAR && $estado === TicketSolicitud::ESTADO_NULO);
    }

    private function requiresTokenSend(Request $request, TicketSolicitud $ticket): bool
    {
        return $ticket->categoria === TicketSolicitud::CATEGORIA_PAGAR
            && in_array($ticket->estado, [
                TicketSolicitud::ESTADO_PENDIENTE,
                TicketSolicitud::ESTADO_TOKEN_NO_FUNCIONO,
            ], true)
            && (string) $request->input('estado') === TicketSolicitud::ESTADO_PAGADO;
    }

    private function requiresRejectReason(Request $request): bool
    {
        return (string) $request->input('estado') === TicketSolicitud::ESTADO_RECHAZADO;
    }

    private function rejectReasonsForCategory(string $categoria): array
    {
        return match ($categoria) {
            TicketSolicitud::CATEGORIA_PAGAR => self::RECHAZO_MOTIVOS_PAGO,
            TicketSolicitud::CATEGORIA_ANULAR => self::RECHAZO_MOTIVOS_NULO,
            default => [],
        };
    }

    private function rechazoMotivos(): array
    {
        return [
            TicketSolicitud::CATEGORIA_PAGAR => self::RECHAZO_MOTIVOS_PAGO,
            TicketSolicitud::CATEGORIA_ANULAR => self::RECHAZO_MOTIVOS_NULO,
        ];
    }

    private function closeChatbotSessionForTicket(TicketSolicitud $ticket): void
    {
        $phone = preg_replace('/\D+/', '', (string) $ticket->phone) ?? '';

        if ($phone === '') {
            return;
        }

        ChatbotSession::query()
            ->where('phone', $phone)
            ->update([
                'step' => 'inicio',
                'context' => json_encode([]),
                'last_message' => 'Sesion cerrada por rechazo de ticket',
                'last_interaction_at' => now(),
            ]);
    }

    private function emptyStats(): array
    {
        return [
            'total' => 0,
            'pendientes' => 0,
            'pagados' => 0,
            'nulos' => 0,
            'rechazados' => 0,
            'token_no_funciono' => 0,
            'pagar' => 0,
            'anular' => 0,
            'averia' => 0,
        ];
    }

    private function emptyDashboardData(): array
    {
        return [
            'categorias' => [],
            'estados' => [],
            'recientesCerrados' => collect(),
            'topGestores' => collect(),
            'pendientesAntiguos' => collect(),
        ];
    }

    private function ticketStats(Builder $baseQuery): array
    {
        return [
            'total' => (clone $baseQuery)->count(),
            'pendientes' => (clone $baseQuery)->where('estado', TicketSolicitud::ESTADO_PENDIENTE)->count(),
            'pagados' => (clone $baseQuery)->whereIn('estado', [TicketSolicitud::ESTADO_PAGADO, TicketSolicitud::ESTADO_TICKET_PAGADO])->count(),
            'nulos' => (clone $baseQuery)->where('estado', TicketSolicitud::ESTADO_NULO)->count(),
            'rechazados' => (clone $baseQuery)->where('estado', TicketSolicitud::ESTADO_RECHAZADO)->count(),
            'token_no_funciono' => (clone $baseQuery)->where('estado', TicketSolicitud::ESTADO_TOKEN_NO_FUNCIONO)->count(),
            'pagar' => (clone $baseQuery)->where('categoria', TicketSolicitud::CATEGORIA_PAGAR)->count(),
            'anular' => (clone $baseQuery)->where('categoria', TicketSolicitud::CATEGORIA_ANULAR)->count(),
            'averia' => (clone $baseQuery)->where('categoria', TicketSolicitud::CATEGORIA_AVERIA)->count(),
        ];
    }

    private function ticketDashboardData(Builder $baseQuery): array
    {
        $closedStates = [
            TicketSolicitud::ESTADO_PAGADO,
            TicketSolicitud::ESTADO_TICKET_PAGADO,
            TicketSolicitud::ESTADO_NULO,
            TicketSolicitud::ESTADO_AVERIA_CERRADA,
            TicketSolicitud::ESTADO_RECHAZADO,
        ];

        $estadoCounts = (clone $baseQuery)
            ->selectRaw('estado, COUNT(*) as total')
            ->groupBy('estado')
            ->pluck('total', 'estado')
            ->map(fn ($total) => (int) $total)
            ->all();

        $categorias = collect([
            TicketSolicitud::CATEGORIA_PAGAR => [
                'label' => 'Pagar ticket',
                'icon' => 'ri-bank-card-line',
                'color' => 'success',
            ],
            TicketSolicitud::CATEGORIA_ANULAR => [
                'label' => 'Anular ticket',
                'icon' => 'ri-close-circle-line',
                'color' => 'danger',
            ],
            TicketSolicitud::CATEGORIA_AVERIA => [
                'label' => 'Reportar averia',
                'icon' => 'ri-tools-line',
                'color' => 'warning',
            ],
        ])->map(function (array $meta, string $categoria) use ($baseQuery, $closedStates): array {
            $query = (clone $baseQuery)->where('categoria', $categoria);
            $total = (clone $query)->count();
            $pendientes = (clone $query)->where('estado', TicketSolicitud::ESTADO_PENDIENTE)->count();
            $tokenNoFunciono = (clone $query)->where('estado', TicketSolicitud::ESTADO_TOKEN_NO_FUNCIONO)->count();
            $cerrados = (clone $query)->whereIn('estado', $closedStates)->count();
            $enProceso = (clone $query)->where('estado', TicketSolicitud::ESTADO_EN_PROCESO)->count();
            $porcentajeCierre = $total > 0 ? round(($cerrados / $total) * 100) : 0;

            $estados = (clone $query)
                ->selectRaw('estado, COUNT(*) as total')
                ->groupBy('estado')
                ->pluck('total', 'estado')
                ->map(fn ($count) => (int) $count);

            return array_merge($meta, [
                'key' => $categoria,
                'total' => $total,
                'pendientes' => $pendientes,
                'token_no_funciono' => $tokenNoFunciono,
                'cerrados' => $cerrados,
                'en_proceso' => $enProceso,
                'porcentaje_cierre' => $porcentajeCierre,
                'estados' => $estados,
                'ultimos' => (clone $query)->latest()->limit(5)->get(),
                'pendientes_antiguos' => (clone $query)
                    ->where('estado', TicketSolicitud::ESTADO_PENDIENTE)
                    ->oldest()
                    ->limit(5)
                    ->get(),
            ]);
        })->values();

        return [
            'categorias' => $categorias,
            'estados' => $estadoCounts,
            'recientesCerrados' => (clone $baseQuery)
                ->whereIn('estado', $closedStates)
                ->with('procesadoPor:id,name')
                ->latest('procesado_at')
                ->limit(8)
                ->get(),
            'topGestores' => (clone $baseQuery)
                ->whereNotNull('procesado_por_id')
                ->with('procesadoPor:id,name')
                ->selectRaw('procesado_por_id, COUNT(*) as total')
                ->groupBy('procesado_por_id')
                ->orderByDesc('total')
                ->limit(6)
                ->get(),
            'pendientesAntiguos' => (clone $baseQuery)
                ->where('estado', TicketSolicitud::ESTADO_PENDIENTE)
                ->oldest()
                ->limit(8)
                ->get(),
        ];
    }

    private function allowedEstadosForCategoria(string $categoria): array
    {
        return match ($categoria) {
            TicketSolicitud::CATEGORIA_ANULAR => [
                TicketSolicitud::ESTADO_PENDIENTE,
                TicketSolicitud::ESTADO_NULO,
                TicketSolicitud::ESTADO_RECHAZADO,
            ],
            TicketSolicitud::CATEGORIA_AVERIA => [
                TicketSolicitud::ESTADO_PENDIENTE,
                TicketSolicitud::ESTADO_EN_PROCESO,
                TicketSolicitud::ESTADO_AVERIA_CERRADA,
            ],
            TicketSolicitud::CATEGORIA_PAGAR => [
                TicketSolicitud::ESTADO_PENDIENTE,
                TicketSolicitud::ESTADO_PAGADO,
                TicketSolicitud::ESTADO_TOKEN_ENVIADO,
                TicketSolicitud::ESTADO_TOKEN_NO_FUNCIONO,
                TicketSolicitud::ESTADO_TICKET_PAGADO,
                TicketSolicitud::ESTADO_RECHAZADO,
            ],
            default => [
                TicketSolicitud::ESTADO_PENDIENTE,
                TicketSolicitud::ESTADO_PAGADO,
                TicketSolicitud::ESTADO_TOKEN_ENVIADO,
                TicketSolicitud::ESTADO_TOKEN_NO_FUNCIONO,
                TicketSolicitud::ESTADO_TICKET_PAGADO,
                TicketSolicitud::ESTADO_NULO,
                TicketSolicitud::ESTADO_RECHAZADO,
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

    private function isEstadoCerrado(TicketSolicitud $ticket): bool
    {
        return in_array($ticket->estado, [
            TicketSolicitud::ESTADO_PAGADO,
            TicketSolicitud::ESTADO_NULO,
            TicketSolicitud::ESTADO_AVERIA_CERRADA,
            TicketSolicitud::ESTADO_RECHAZADO,
        ], true);
    }
}
