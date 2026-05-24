<?php

namespace App\Http\Controllers;

use App\Models\ServicioGeneralRequerimiento;
use App\Models\User;
use App\Notifications\ServicioGeneralRequerimientoActualizadaNotification;
use App\Notifications\ServicioGeneralRequerimientoAsignadaNotification;
use App\Notifications\ServicioGeneralRequerimientoCierreSolicitadoNotification;
use App\Notifications\ServicioGeneralRequerimientoFinalizadaNotification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use App\Services\WhatsAppService;

class ServicioGeneralRequerimientoController extends Controller
{
    private const STAFF_ROLE_KEYWORDS = [
        'servicios generales',
        'servicios_generales',
        'mantenimiento',
        'tecnico',
        'técnico',
        'tecnicos',
        'técnicos',
        'soporte',
        'operaciones',
    ];

    private const ADMIN_ROLE_NAMES = ['superadmin', 'admin', 'superior'];

    private const TIPOS_VALIDOS = [
        'internet',
        'electricidad',
        'sistema_frizado',
        'inversor',
    ];

    public function __construct(private readonly WhatsAppService $whatsAppService)
    {
    }

    public function index()
    {
        return view('servicios-generales.requerimientos', [
            'asignables' => $this->staffUsers(),
            'stats' => $this->statsFor(auth()->user()),
            'puedeVerTodo' => $this->puedeVerTodo(auth()->user()),
            'setupPending' => !$this->tablaExiste(),
            'puedeFinalizarGlobal' => $this->esAdmin(auth()->user()),
        ]);
    }

    public function list(Request $request): JsonResponse
    {
        if (!$this->tablaExiste()) {
            return response()->json([
                'data' => [],
                'stats' => $this->emptyStats(),
                'setup_pending' => true,
                'message' => 'La tabla de requerimientos de Servicios Generales aun no ha sido creada.',
            ]);
        }

        $query = $this->visibleQueryFor(auth()->user())
            ->with(['creador:id,name,email', 'asignado:id,name,email', 'cierreSolicitadoPor:id,name']);

        if ($request->filled('requerimiento_id') && is_numeric($request->requerimiento_id)) {
            $query->where('id', (int) $request->requerimiento_id);
        }

        if ($request->filled('tipo')) {
            $query->where('tipo', $request->tipo);
        }

        if ($request->filled('estado')) {
            $query->where('estado', $request->estado);
        }

        if ($request->filled('prioridad')) {
            $query->where('prioridad', $request->prioridad);
        }

        if ($request->filled('asignado_id')) {
            $query->where('asignado_id', $request->asignado_id);
        }

        if ($request->boolean('solo_mias')) {
            $query->where(function ($subQuery) {
                $subQuery->where('user_id', auth()->id())
                    ->orWhere('asignado_id', auth()->id());
            });
        }

        if ($request->filled('search')) {
            $search = trim((string) $request->search);

            $query->where(function ($subQuery) use ($search) {
                if (preg_match('/^REQ-(\d+)$/i', $search, $matches)) {
                    $subQuery->orWhere('id', (int) $matches[1]);
                }

                if (is_numeric($search)) {
                    $subQuery->orWhere('id', (int) $search);
                }

                $subQuery->orWhere('titulo', 'like', '%' . $search . '%')
                    ->orWhere('descripcion', 'like', '%' . $search . '%')
                    ->orWhereHas('creador', fn($userQuery) => $userQuery->where('name', 'like', '%' . $search . '%'))
                    ->orWhereHas('asignado', fn($userQuery) => $userQuery->where('name', 'like', '%' . $search . '%'));
            });
        }

        $requerimientos = $query
            ->orderByDesc('created_at')
            ->get()
            ->map(fn(ServicioGeneralRequerimiento $r) => $this->serialize($r));

        return response()->json([
            'data' => $requerimientos,
            'stats' => $this->statsFor(auth()->user()),
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        if (!$this->tablaExiste()) {
            return response()->json([
                'success' => false,
                'message' => 'La tabla de requerimientos aun no existe. Ejecuta la migracion pendiente.',
            ], 503);
        }

        $validated = $request->validate([
            'tipo' => 'required|in:' . implode(',', self::TIPOS_VALIDOS),
            'titulo' => 'required|in:Averia,Solicitud',
            'descripcion' => 'required|string|max:5000',
            'prioridad' => 'required|in:baja,media,alta,critica',
            'asignado_id' => 'required|exists:users,id',
        ]);

        $requerimiento = ServicioGeneralRequerimiento::create([
            'user_id' => auth()->id(),
            'tipo' => $validated['tipo'],
            'titulo' => $validated['titulo'],
            'descripcion' => $validated['descripcion'],
            'prioridad' => $validated['prioridad'],
            'asignado_id' => $validated['asignado_id'],
            'estado' => 'asignada',
            'progreso' => 0,
            'asignado_at' => now(),
        ]);

        $requerimiento->load(['creador:id,name,email', 'asignado:id,name,email', 'cierreSolicitadoPor:id,name']);

        if ((int) $requerimiento->asignado_id !== (int) auth()->id() && $requerimiento->asignado) {
            $requerimiento->asignado->notify(
                new ServicioGeneralRequerimientoAsignadaNotification($requerimiento, auth()->user())
            );
        }

        return response()->json([
            'success' => true,
            'message' => 'Requerimiento registrado y enviado al personal de Servicios Generales.',
            'requerimiento' => $this->serialize($requerimiento),
        ]);
    }

    public function show(ServicioGeneralRequerimiento $requerimiento): JsonResponse
    {
        abort_unless($this->puedeAcceder(auth()->user(), $requerimiento), 403);

        $requerimiento->load(['creador:id,name,email', 'asignado:id,name,email', 'cierreSolicitadoPor:id,name']);

        return response()->json([
            'requerimiento' => $this->serialize($requerimiento),
        ]);
    }

    public function solicitarCierre(ServicioGeneralRequerimiento $requerimiento): JsonResponse
    {
        if (!$this->tablaExiste()) {
            return response()->json([
                'success' => false,
                'message' => 'La tabla de requerimientos aun no existe. Ejecuta la migracion pendiente.',
            ], 503);
        }

        abort_unless($this->puedeSolicitarCierre(auth()->user(), $requerimiento), 403);

        if ($requerimiento->estado === 'resuelta') {
            return response()->json([
                'success' => false,
                'message' => 'Este ticket ya fue finalizado.',
            ], 422);
        }

        if ($requerimiento->estado === 'solicitud_cierre') {
            return response()->json([
                'success' => false,
                'message' => 'El ticket ya tiene una solicitud de cierre pendiente.',
            ], 422);
        }

        $requerimiento->estado = 'solicitud_cierre';
        $requerimiento->cierre_solicitado_at = now();
        $requerimiento->cierre_solicitado_por = auth()->id();
        $requerimiento->save();
        $requerimiento->load(['creador:id,name,email', 'asignado:id,name,email', 'cierreSolicitadoPor:id,name']);

        $this->notifyRequesterByWhatsApp($requerimiento, 'Se solicito el cierre de tu requerimiento.');

        if ($requerimiento->creador && (int) $requerimiento->creador->id !== (int) auth()->id()) {
            $requerimiento->creador->notify(
                new ServicioGeneralRequerimientoCierreSolicitadoNotification($requerimiento, auth()->user())
            );
        }

        return response()->json([
            'success' => true,
            'message' => 'Se envio la solicitud de cierre para validacion.',
            'requerimiento' => $this->serialize($requerimiento),
        ]);
    }

    public function finalizar(ServicioGeneralRequerimiento $requerimiento): JsonResponse
    {
        if (!$this->tablaExiste()) {
            return response()->json([
                'success' => false,
                'message' => 'La tabla de requerimientos aun no existe. Ejecuta la migracion pendiente.',
            ], 503);
        }

        abort_unless($this->puedeFinalizar(auth()->user(), $requerimiento), 403);

        if ($requerimiento->estado !== 'solicitud_cierre' && !$this->esAdmin(auth()->user())) {
            return response()->json([
                'success' => false,
                'message' => 'El ticket debe estar en solicitud de cierre antes de finalizarse.',
            ], 422);
        }

        $requerimiento->estado = 'resuelta';
        $requerimiento->progreso = 100;
        $requerimiento->resuelto_at = now();
        $requerimiento->cerrado_por = auth()->id();
        if (empty($requerimiento->cierre_solicitado_at)) {
            $requerimiento->cierre_solicitado_at = now();
            $requerimiento->cierre_solicitado_por = auth()->id();
        }
        $requerimiento->save();
        $requerimiento->load(['creador:id,name,email', 'asignado:id,name,email', 'cierreSolicitadoPor:id,name']);

        $this->notifyRequesterByWhatsApp($requerimiento, 'Tu requerimiento fue finalizado.');

        $destinatarios = collect([
            $requerimiento->creador,
            $requerimiento->asignado,
        ])->filter()
            ->unique('id')
            ->reject(fn(User $user) => (int) $user->id === (int) auth()->id());

        $destinatarios->each(function (User $user) use ($requerimiento) {
            $user->notify(new ServicioGeneralRequerimientoFinalizadaNotification($requerimiento, auth()->user()));
        });

        return response()->json([
            'success' => true,
            'message' => 'El ticket fue finalizado correctamente.',
            'requerimiento' => $this->serialize($requerimiento),
        ]);
    }

    public function update(Request $request, ServicioGeneralRequerimiento $requerimiento): JsonResponse
    {
        if (!$this->tablaExiste()) {
            return response()->json([
                'success' => false,
                'message' => 'La tabla de requerimientos aun no existe. Ejecuta la migracion pendiente.',
            ], 503);
        }

        abort_unless($this->puedeEditar(auth()->user(), $requerimiento), 403);

        $validated = $request->validate([
            'tipo' => 'required|in:' . implode(',', self::TIPOS_VALIDOS),
            'titulo' => 'required|in:Averia,Solicitud',
            'descripcion' => 'required|string|max:5000',
            'prioridad' => 'required|in:baja,media,alta,critica',
            'asignado_id' => 'required|exists:users,id',
            'estado' => 'required|in:pendiente,asignada,en_progreso,en_espera,solicitud_cierre,cancelada',
            'progreso' => 'nullable|integer|min:0|max:100',
            'detalle_solucion' => 'nullable|string|max:5000',
        ]);

        $validated['progreso'] = (int) ($validated['progreso'] ?? $requerimiento->progreso ?? 0);
        if (($validated['estado'] ?? null) === 'resuelta') {
            $validated['progreso'] = 100;
        }

        if (!$this->puedeGestionarProgreso(auth()->user(), $requerimiento) && (int) $validated['progreso'] !== (int) $requerimiento->progreso) {
            return response()->json([
                'success' => false,
                'message' => 'Solo el usuario asignado o un administrador puede actualizar el progreso.',
            ], 403);
        }

        if ($validated['estado'] === 'solicitud_cierre' && !$this->puedeSolicitarCierre(auth()->user(), $requerimiento)) {
            return response()->json([
                'success' => false,
                'message' => 'Solo el responsable puede solicitar el cierre del ticket.',
            ], 403);
        }

        $estadoCambio = $requerimiento->estado !== $validated['estado'];
        $asignadoCambio = (int) $requerimiento->asignado_id !== (int) $validated['asignado_id'];
        $detalleCambio = trim((string) $requerimiento->detalle_solucion) !== trim((string) ($validated['detalle_solucion'] ?? ''));
        $progresoCambio = (int) $requerimiento->progreso !== (int) ($validated['progreso'] ?? 0);

        $requerimiento->fill($validated);

        if ($asignadoCambio) {
            $requerimiento->asignado_at = now();
        }

        if ($validated['estado'] === 'solicitud_cierre') {
            $requerimiento->cierre_solicitado_at = now();
            $requerimiento->cierre_solicitado_por = auth()->id();
        } elseif ($estadoCambio && $requerimiento->estado !== 'solicitud_cierre') {
            $requerimiento->cierre_solicitado_at = null;
            $requerimiento->cierre_solicitado_por = null;
        }

        $requerimiento->resuelto_at = null;
        $requerimiento->cerrado_por = null;

        $requerimiento->save();
        $requerimiento->load(['creador:id,name,email', 'asignado:id,name,email', 'cierreSolicitadoPor:id,name']);

        if ($asignadoCambio && $requerimiento->asignado && (int) $requerimiento->asignado_id !== (int) auth()->id()) {
            $requerimiento->asignado->notify(
                new ServicioGeneralRequerimientoAsignadaNotification($requerimiento, auth()->user())
            );
        }

        $destinatarios = collect([
            $requerimiento->creador,
            $requerimiento->asignado,
        ])->filter()
            ->unique('id')
            ->reject(fn(User $user) => (int) $user->id === (int) auth()->id());

        if ($destinatarios->isNotEmpty() && ($estadoCambio || $asignadoCambio || $detalleCambio)) {
            $cambios = [
                'estado' => $estadoCambio,
                'asignado' => $asignadoCambio,
                'detalle_solucion' => $detalleCambio,
                'progreso' => $progresoCambio,
            ];

            $destinatarios->each(function (User $user) use ($requerimiento, $cambios) {
                $user->notify(new ServicioGeneralRequerimientoActualizadaNotification($requerimiento, auth()->user(), $cambios));
            });
        }

        if ($estadoCambio || $asignadoCambio || $detalleCambio || $progresoCambio) {
            $this->notifyRequesterByWhatsApp($requerimiento, 'Tu requerimiento fue actualizado.');
        }

        if ($requerimiento->estado === 'solicitud_cierre' && $requerimiento->creador && (int) $requerimiento->creador->id !== (int) auth()->id()) {
            $requerimiento->creador->notify(
                new ServicioGeneralRequerimientoCierreSolicitadoNotification($requerimiento, auth()->user())
            );
        }

        return response()->json([
            'success' => true,
            'message' => 'Requerimiento actualizado correctamente.',
            'requerimiento' => $this->serialize($requerimiento),
        ]);
    }

    private function serialize(ServicioGeneralRequerimiento $r): array
    {
        return [
            'id' => $r->id,
            'ticket_codigo' => $r->ticket_codigo,
            'tipo' => $r->tipo,
            'tipo_label' => $r->tipo_label,
            'badge_tipo' => $r->badge_tipo,
            'titulo' => $r->titulo,
            'descripcion' => $r->descripcion,
            'prioridad' => $r->prioridad,
            'badge_prioridad' => $r->badge_prioridad,
            'estado' => $r->estado,
            'badge_estado' => $r->badge_estado,
            'progreso' => (int) $r->progreso,
            'detalle_solucion' => $r->detalle_solucion,
            'attachment_url' => $r->attachment_url,
            'attachment_message_id' => $r->attachment_message_id,
            'solicitante' => $r->creador->name ?? 'N/D',
            'solicitante_email' => $r->creador->email ?? '',
            'asignado_id' => $r->asignado_id,
            'asignado' => $r->asignado->name ?? 'Sin asignar',
            'asignado_email' => $r->asignado->email ?? '',
            'cierre_solicitado_por' => $r->cierreSolicitadoPor->name ?? '',
            'cierre_solicitado_at' => optional($r->cierre_solicitado_at)->format('d/m/Y h:i A'),
            'creado_en' => optional($r->created_at)->format('d/m/Y h:i A'),
            'actualizado_en' => optional($r->updated_at)->format('d/m/Y h:i A'),
            'resuelto_en' => optional($r->resuelto_at)->format('d/m/Y h:i A'),
            'can_edit' => $this->puedeEditar(auth()->user(), $r),
            'can_manage_progress' => $this->puedeGestionarProgreso(auth()->user(), $r),
            'can_request_close' => $this->puedeSolicitarCierre(auth()->user(), $r)
                && $r->estado !== 'resuelta'
                && $r->estado !== 'solicitud_cierre',
            'can_finalize' => $this->puedeFinalizar(auth()->user(), $r)
                && $r->estado === 'solicitud_cierre',
        ];
    }

    private function visibleQueryFor(User $user)
    {
        $query = ServicioGeneralRequerimiento::query();

        if ($this->puedeVerTodo($user)) {
            return $query;
        }

        return $query->where(function ($subQuery) use ($user) {
            $subQuery->where('user_id', $user->id)
                ->orWhere('asignado_id', $user->id);
        });
    }

    private function statsFor(User $user): array
    {
        if (!$this->tablaExiste()) {
            return $this->emptyStats();
        }

        $query = $this->visibleQueryFor($user);

        return [
            'total' => (clone $query)->count(),
            'pendientes' => (clone $query)->whereIn('estado', ['pendiente', 'asignada', 'solicitud_cierre'])->count(),
            'en_progreso' => (clone $query)->whereIn('estado', ['en_progreso', 'en_espera'])->count(),
            'resueltas' => (clone $query)->where('estado', 'resuelta')->count(),
        ];
    }

    private function emptyStats(): array
    {
        return [
            'total' => 0,
            'pendientes' => 0,
            'en_progreso' => 0,
            'resueltas' => 0,
        ];
    }

    private function tablaExiste(): bool
    {
        return Schema::hasTable('servicios_generales_requerimientos');
    }

    private function staffUsers(): Collection
    {
        $staffQuery = User::query()
            ->whereHas('roles', function ($roleQuery) {
                $roleQuery->where(function ($nestedQuery) {
                    foreach (self::STAFF_ROLE_KEYWORDS as $index => $keyword) {
                        $method = $index === 0 ? 'where' : 'orWhere';
                        $nestedQuery->{$method}('name', 'like', '%' . $keyword . '%');
                    }
                });
            })
            ->orderBy('name');

        if ($staffQuery->exists()) {
            return $staffQuery->get(['id', 'name', 'email']);
        }

        return User::query()
            ->orderBy('name')
            ->get(['id', 'name', 'email']);
    }

    private function puedeVerTodo(User $user): bool
    {
        return $this->esAdmin($user) || $this->esStaff($user);
    }

    private function puedeAcceder(User $user, ServicioGeneralRequerimiento $r): bool
    {
        return $this->puedeVerTodo($user)
            || (int) $r->user_id === (int) $user->id
            || (int) $r->asignado_id === (int) $user->id;
    }

    private function puedeEditar(User $user, ServicioGeneralRequerimiento $r): bool
    {
        return $this->esAdmin($user)
            || $this->esStaff($user)
            || (int) $r->user_id === (int) $user->id
            || (int) $r->asignado_id === (int) $user->id;
    }

    private function puedeGestionarProgreso(User $user, ServicioGeneralRequerimiento $r): bool
    {
        return $this->esAdmin($user)
            || (int) $r->asignado_id === (int) $user->id;
    }

    private function puedeSolicitarCierre(User $user, ServicioGeneralRequerimiento $r): bool
    {
        return $this->esAdmin($user)
            || (int) $r->asignado_id === (int) $user->id;
    }

    private function puedeFinalizar(User $user, ServicioGeneralRequerimiento $r): bool
    {
        return $this->esAdmin($user)
            || (int) $r->user_id === (int) $user->id;
    }

    private function esAdmin(User $user): bool
    {
        return $user->hasAnyRole(self::ADMIN_ROLE_NAMES);
    }

    private function esStaff(User $user): bool
    {
        $user->loadMissing('roles:id,name');

        return $user->roles->contains(function ($role) {
            $roleName = Str::lower((string) $role->name);

            foreach (self::STAFF_ROLE_KEYWORDS as $keyword) {
                if (Str::contains($roleName, Str::lower($keyword))) {
                    return true;
                }
            }

            return false;
        });
    }

    private function notifyRequesterByWhatsApp(ServicioGeneralRequerimiento $requerimiento, string $intro): void
    {
        $recipient = $this->formatWhatsappRecipient((string) $requerimiento->whatsapp_phone);

        if ($recipient === null) {
            return;
        }

        $message = "{$intro}\n\n"
            . "Codigo: {$requerimiento->ticket_codigo}\n"
            . "Tipo: {$requerimiento->tipo_label}\n"
            . "Estado: " . $this->estadoLabel((string) $requerimiento->estado) . "\n"
            . "Progreso: {$requerimiento->progreso}%\n"
            . "Asignado: " . ($requerimiento->asignado->name ?? 'Sin asignar');

        if (trim((string) $requerimiento->detalle_solucion) !== '') {
            $message .= "\nDetalle: " . trim((string) $requerimiento->detalle_solucion);
        }

        try {
            $this->whatsAppService->sendText($recipient, $message);
        } catch (\Throwable $e) {
            report($e);
        }
    }

    private function formatWhatsappRecipient(string $phone): ?string
    {
        $digits = preg_replace('/\D+/', '', $phone) ?? '';

        if ($digits === '' || strlen($digits) < 8) {
            return null;
        }

        return str_starts_with($phone, '+') ? $phone : '+' . $digits;
    }

    private function estadoLabel(string $estado): string
    {
        return match ($estado) {
            'asignada' => 'Asignada',
            'en_progreso' => 'En progreso',
            'en_espera' => 'En espera',
            'solicitud_cierre' => 'Solicitud de cierre',
            'resuelta' => 'Resuelta',
            'cancelada' => 'Cancelada',
            default => 'Pendiente',
        };
    }
}
