<?php

namespace App\Http\Controllers;

use App\Models\TecnologiaSolicitud;
use App\Models\TipoSolicitudTecnologia;
use App\Models\User;
use App\Notifications\TecnologiaSolicitudActualizadaNotification;
use App\Notifications\TecnologiaSolicitudAsignadaNotification;
use App\Notifications\TecnologiaSolicitudCierreSolicitadoNotification;
use App\Notifications\TecnologiaSolicitudFinalizadaNotification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class TecnologiaSolicitudController extends Controller
{
    private const STAFF_ROLE_KEYWORDS = [
        'tecnologia',
        'tecnología',
        'soporte',
        'desarrollo',
        'developer',
        'desarrollador',
        'informatica',
        'informática',
        'sistemas',
    ];

    private const ADMIN_ROLE_NAMES = ['superadmin', 'admin', 'superior'];

    public function index()
    {
        return view('tecnologia.solicitudes', [
            'asignables' => $this->tecnologiaStaffUsers(),
            'stats' => $this->statsFor(auth()->user()),
            'puedeVerTodo' => $this->puedeVerTodo(auth()->user()),
            'setupPending' => !$this->solicitudesTableExists(),
            'puedeFinalizarGlobal' => $this->esAdmin(auth()->user()),
        ]);
    }

    public function list(Request $request): JsonResponse
    {
        if (!$this->solicitudesTableExists()) {
            return response()->json([
                'data' => [],
                'stats' => $this->emptyStats(),
                'setup_pending' => true,
                'message' => 'La tabla de solicitudes de Tecnologia aun no ha sido creada.',
            ]);
        }

        $query = $this->visibleQueryFor(auth()->user())
            ->with(['creador:id,name,email', 'asignado:id,name,email', 'tipoSolicitud:id,nombre,requiere_progreso', 'cierreSolicitadoPor:id,name']);

        if ($request->filled('solicitud_id') && is_numeric($request->solicitud_id)) {
            $query->where('id', (int) $request->solicitud_id);
        }

        if ($request->filled('tipo')) {
            $query->where('tipo_solicitud_id', $this->tipoSolicitudId((string) $request->tipo));
        }

        if ($request->filled('estado')) {
            $query->where('estado', $request->estado);
        }

        if ($request->filled('prioridad')) {
            $query->where('prioridad', $request->prioridad);
        }

        if ($request->filled('asignado_id')) {
            $query->where('asignado_a_id', $request->asignado_id);
        }

        if ($request->boolean('solo_mias')) {
            $query->where(function ($subQuery) {
                $subQuery->where('solicitante_id', auth()->id())
                    ->orWhere('asignado_a_id', auth()->id());
            });
        }

        if ($request->filled('search')) {
            $search = trim((string) $request->search);

            $query->where(function ($subQuery) use ($search) {
                if (preg_match('/^TEC-(\d+)$/i', $search, $matches)) {
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

        $solicitudes = $query
            ->orderByDesc('created_at')
            ->get()
            ->map(fn(TecnologiaSolicitud $solicitud) => $this->serializeSolicitud($solicitud));

        return response()->json([
            'data' => $solicitudes,
            'stats' => $this->statsFor(auth()->user()),
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        if (!$this->solicitudesTableExists()) {
            return response()->json([
                'success' => false,
                'message' => 'La tabla de solicitudes de Tecnologia aun no existe. Ejecuta la migracion pendiente.',
            ], 503);
        }

        $validated = $request->validate([
            'tipo' => 'required|in:averia,desarrollo',
            'titulo' => 'required|string|max:160',
            'descripcion' => 'required|string|max:5000',
            'prioridad' => 'required|in:baja,media,alta,critica',
            'asignado_id' => 'required|exists:users,id',
        ]);

        $solicitud = TecnologiaSolicitud::create([
            'solicitante_id' => auth()->id(),
            'tipo_solicitud_id' => $this->tipoSolicitudId($validated['tipo']),
            'titulo' => $validated['titulo'],
            'descripcion' => $validated['descripcion'],
            'prioridad' => $validated['prioridad'],
            'asignado_a_id' => $validated['asignado_id'],
            'estado' => 'asignada',
            'progreso' => $validated['tipo'] === 'desarrollo' ? 5 : 0,
            'asignado_at' => now(),
        ]);

        $solicitud->load(['creador:id,name,email', 'asignado:id,name,email', 'tipoSolicitud:id,nombre,requiere_progreso', 'cierreSolicitadoPor:id,name']);

        if ((int) $solicitud->asignado_a_id !== (int) auth()->id() && $solicitud->asignado) {
            $solicitud->asignado->notify(
                new TecnologiaSolicitudAsignadaNotification($solicitud, auth()->user())
            );
        }

        return response()->json([
            'success' => true,
            'message' => 'Solicitud registrada y enviada al personal de Tecnologia.',
            'solicitud' => $this->serializeSolicitud($solicitud),
        ]);
    }

    public function show(TecnologiaSolicitud $solicitud): JsonResponse
    {
        abort_unless($this->puedeAccederSolicitud(auth()->user(), $solicitud), 403);

        $solicitud->load(['creador:id,name,email', 'asignado:id,name,email', 'tipoSolicitud:id,nombre,requiere_progreso', 'cierreSolicitadoPor:id,name']);

        return response()->json([
            'solicitud' => $this->serializeSolicitud($solicitud),
        ]);
    }

    public function solicitarCierre(TecnologiaSolicitud $solicitud): JsonResponse
    {
        if (!$this->solicitudesTableExists()) {
            return response()->json([
                'success' => false,
                'message' => 'La tabla de solicitudes de Tecnologia aun no existe. Ejecuta la migracion pendiente.',
            ], 503);
        }

        abort_unless($this->puedeSolicitarCierre(auth()->user(), $solicitud), 403);

        if ($solicitud->estado === 'resuelta') {
            return response()->json([
                'success' => false,
                'message' => 'Este ticket ya fue finalizado.',
            ], 422);
        }

        if ($solicitud->estado === 'solicitud_cierre') {
            return response()->json([
                'success' => false,
                'message' => 'El ticket ya tiene una solicitud de cierre pendiente.',
            ], 422);
        }

        if ($solicitud->tipo === 'desarrollo' && (int) $solicitud->progreso < 100) {
            return response()->json([
                'success' => false,
                'message' => 'Para solicitar cierre en un desarrollo el progreso debe estar en 100%.',
            ], 422);
        }

        $solicitud->estado = 'solicitud_cierre';
        $solicitud->cierre_solicitado_at = now();
        $solicitud->cierre_solicitado_por_id = auth()->id();
        $solicitud->save();
        $solicitud->load(['creador:id,name,email', 'asignado:id,name,email', 'tipoSolicitud:id,nombre,requiere_progreso', 'cierreSolicitadoPor:id,name']);

        if ($solicitud->creador && (int) $solicitud->creador->id !== (int) auth()->id()) {
            $solicitud->creador->notify(
                new TecnologiaSolicitudCierreSolicitadoNotification($solicitud, auth()->user())
            );
        }

        return response()->json([
            'success' => true,
            'message' => 'Se envio la solicitud de cierre para validacion.',
            'solicitud' => $this->serializeSolicitud($solicitud),
        ]);
    }

    public function finalizar(TecnologiaSolicitud $solicitud): JsonResponse
    {
        if (!$this->solicitudesTableExists()) {
            return response()->json([
                'success' => false,
                'message' => 'La tabla de solicitudes de Tecnologia aun no existe. Ejecuta la migracion pendiente.',
            ], 503);
        }

        abort_unless($this->puedeFinalizarSolicitud(auth()->user(), $solicitud), 403);

        if ($solicitud->estado !== 'solicitud_cierre' && !$this->esAdmin(auth()->user())) {
            return response()->json([
                'success' => false,
                'message' => 'El ticket debe estar en solicitud de cierre antes de finalizarse.',
            ], 422);
        }

        $solicitud->estado = 'resuelta';
        $solicitud->progreso = $solicitud->tipo === 'desarrollo' ? 100 : $solicitud->progreso;
        $solicitud->fecha_completada = now()->toDateString();
        $solicitud->cerrado_por_id = auth()->id();
        if (empty($solicitud->cierre_solicitado_at)) {
            $solicitud->cierre_solicitado_at = now();
            $solicitud->cierre_solicitado_por_id = auth()->id();
        }
        $solicitud->save();
        $solicitud->load(['creador:id,name,email', 'asignado:id,name,email', 'tipoSolicitud:id,nombre,requiere_progreso', 'cierreSolicitadoPor:id,name']);

        $destinatarios = collect([
            $solicitud->creador,
            $solicitud->asignado,
        ])->filter()
            ->unique('id')
            ->reject(fn(User $user) => (int) $user->id === (int) auth()->id());

        $destinatarios->each(function (User $user) use ($solicitud) {
            $user->notify(new TecnologiaSolicitudFinalizadaNotification($solicitud, auth()->user()));
        });

        return response()->json([
            'success' => true,
            'message' => 'El ticket fue finalizado correctamente.',
            'solicitud' => $this->serializeSolicitud($solicitud),
        ]);
    }

    public function update(Request $request, TecnologiaSolicitud $solicitud): JsonResponse
    {
        if (!$this->solicitudesTableExists()) {
            return response()->json([
                'success' => false,
                'message' => 'La tabla de solicitudes de Tecnologia aun no existe. Ejecuta la migracion pendiente.',
            ], 503);
        }

        abort_unless($this->puedeEditarSolicitud(auth()->user(), $solicitud), 403);

        $validated = $request->validate([
            'tipo' => 'required|in:averia,desarrollo',
            'titulo' => 'required|string|max:160',
            'descripcion' => 'required|string|max:5000',
            'prioridad' => 'required|in:baja,media,alta,critica',
            'asignado_id' => 'required|exists:users,id',
            'estado' => 'required|in:pendiente,asignada,en_progreso,en_espera,solicitud_cierre,cancelada',
            'progreso' => 'nullable|integer|min:0|max:100',
            'detalle_solucion' => 'nullable|string|max:5000',
        ]);

        if ($solicitud->tipo === 'desarrollo' || $validated['tipo'] === 'desarrollo') {
            $validated['progreso'] = (int) ($validated['progreso'] ?? $solicitud->progreso ?? 0);
        } else {
            $validated['progreso'] = 0;
        }

        if (!$this->puedeGestionarProgreso(auth()->user(), $solicitud) && (int) $validated['progreso'] !== (int) $solicitud->progreso) {
            return response()->json([
                'success' => false,
                'message' => 'Solo el usuario asignado o un administrador puede actualizar el progreso.',
            ], 403);
        }

        if ($validated['estado'] === 'solicitud_cierre' && !$this->puedeSolicitarCierre(auth()->user(), $solicitud)) {
            return response()->json([
                'success' => false,
                'message' => 'Solo el responsable puede solicitar el cierre del ticket.',
            ], 403);
        }

        if ($validated['estado'] === 'solicitud_cierre' && $validated['tipo'] === 'desarrollo' && (int) $validated['progreso'] < 100) {
            return response()->json([
                'success' => false,
                'message' => 'Para solicitar cierre en un desarrollo el progreso debe estar en 100%.',
            ], 422);
        }

        $estadoCambio = $solicitud->estado !== $validated['estado'];
        $asignadoCambio = (int) $solicitud->asignado_a_id !== (int) $validated['asignado_id'];
        $detalleCambio = trim((string) $solicitud->detalle_solucion) !== trim((string) ($validated['detalle_solucion'] ?? ''));
        $progresoCambio = (int) $solicitud->progreso !== (int) ($validated['progreso'] ?? 0);

        $solicitud->fill([
            'tipo_solicitud_id' => $this->tipoSolicitudId($validated['tipo']),
            'titulo' => $validated['titulo'],
            'descripcion' => $validated['descripcion'],
            'prioridad' => $validated['prioridad'],
            'asignado_a_id' => $validated['asignado_id'],
            'estado' => $validated['estado'],
            'progreso' => $validated['progreso'],
            'detalle_solucion' => $validated['detalle_solucion'] ?? null,
        ]);

        if ($asignadoCambio) {
            $solicitud->asignado_at = now();
        }

        if ($solicitud->tipo !== 'desarrollo') {
            $solicitud->progreso = 0;
        }

        if ((int) $solicitud->progreso >= 100 && $solicitud->tipo === 'desarrollo' && $solicitud->estado === 'en_progreso') {
            $solicitud->progreso = 100;
        }

        if ($validated['estado'] === 'solicitud_cierre') {
            $solicitud->cierre_solicitado_at = now();
            $solicitud->cierre_solicitado_por_id = auth()->id();
        } elseif ($estadoCambio && $solicitud->estado !== 'solicitud_cierre') {
            $solicitud->cierre_solicitado_at = null;
            $solicitud->cierre_solicitado_por_id = null;
        }

        $solicitud->fecha_completada = null;
        $solicitud->cerrado_por_id = null;

        $solicitud->save();
        $solicitud->load(['creador:id,name,email', 'asignado:id,name,email', 'tipoSolicitud:id,nombre,requiere_progreso', 'cierreSolicitadoPor:id,name']);

        if ($asignadoCambio && $solicitud->asignado && (int) $solicitud->asignado_a_id !== (int) auth()->id()) {
            $solicitud->asignado->notify(
                new TecnologiaSolicitudAsignadaNotification($solicitud, auth()->user())
            );
        }

        $destinatarios = collect([
            $solicitud->creador,
            $solicitud->asignado,
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

            $destinatarios->each(function (User $user) use ($solicitud, $cambios) {
                $user->notify(new TecnologiaSolicitudActualizadaNotification($solicitud, auth()->user(), $cambios));
            });
        }

        if ($solicitud->estado === 'solicitud_cierre' && $solicitud->creador && (int) $solicitud->creador->id !== (int) auth()->id()) {
            $solicitud->creador->notify(
                new TecnologiaSolicitudCierreSolicitadoNotification($solicitud, auth()->user())
            );
        }

        return response()->json([
            'success' => true,
            'message' => 'Solicitud actualizada correctamente.',
            'solicitud' => $this->serializeSolicitud($solicitud),
        ]);
    }

    private function serializeSolicitud(TecnologiaSolicitud $solicitud): array
    {
        return [
            'id' => $solicitud->id,
            'ticket_codigo' => $solicitud->ticket_codigo,
            'tipo' => $solicitud->tipo,
            'badge_tipo' => $solicitud->badge_tipo,
            'titulo' => $solicitud->titulo,
            'descripcion' => $solicitud->descripcion,
            'prioridad' => $solicitud->prioridad,
            'badge_prioridad' => $solicitud->badge_prioridad,
            'estado' => $solicitud->estado,
            'badge_estado' => $solicitud->badge_estado,
            'progreso' => (int) $solicitud->progreso,
            'detalle_solucion' => $solicitud->detalle_solucion,
            'solicitante' => $solicitud->creador->name ?? 'N/D',
            'solicitante_email' => $solicitud->creador->email ?? '',
            'asignado_id' => $solicitud->asignado_a_id,
            'asignado' => $solicitud->asignado->name ?? 'Sin asignar',
            'asignado_email' => $solicitud->asignado->email ?? '',
            'cierre_solicitado_por' => $solicitud->cierreSolicitadoPor->name ?? '',
            'cierre_solicitado_at' => optional($solicitud->cierre_solicitado_at)->format('d/m/Y h:i A'),
            'creado_en' => optional($solicitud->created_at)->format('d/m/Y h:i A'),
            'actualizado_en' => optional($solicitud->updated_at)->format('d/m/Y h:i A'),
            'resuelto_en' => optional($solicitud->fecha_completada)->format('d/m/Y'),
            'can_edit' => $this->puedeEditarSolicitud(auth()->user(), $solicitud),
            'can_manage_progress' => $this->puedeGestionarProgreso(auth()->user(), $solicitud),
            'can_request_close' => $this->puedeSolicitarCierre(auth()->user(), $solicitud)
                && $solicitud->estado !== 'resuelta'
                && $solicitud->estado !== 'solicitud_cierre'
                && ($solicitud->tipo !== 'desarrollo' || (int) $solicitud->progreso >= 100),
            'can_finalize' => $this->puedeFinalizarSolicitud(auth()->user(), $solicitud)
                && $solicitud->estado === 'solicitud_cierre',
        ];
    }

    private function visibleQueryFor(User $user)
    {
        $query = TecnologiaSolicitud::query();

        if ($this->puedeVerTodo($user)) {
            return $query;
        }

        return $query->where(function ($subQuery) use ($user) {
            $subQuery->where('solicitante_id', $user->id)
                ->orWhere('asignado_a_id', $user->id);
        });
    }

    private function statsFor(User $user): array
    {
        if (!$this->solicitudesTableExists()) {
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

    private function solicitudesTableExists(): bool
    {
        return Schema::hasTable('tecnologia_solicitudes');
    }

    private function tipoSolicitudId(string $tipo): int
    {
        $nombre = $tipo === 'desarrollo' ? 'Desarrollo' : 'Averia';

        $tipoSolicitud = TipoSolicitudTecnologia::query()->firstOrCreate(
            ['nombre' => $nombre],
            [
                'activo' => true,
                'requiere_progreso' => $tipo === 'desarrollo',
            ]
        );

        return (int) $tipoSolicitud->id;
    }

    private function tecnologiaStaffUsers(): Collection
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
        return $this->esAdmin($user) || $this->esStaffTecnologia($user);
    }

    private function puedeAccederSolicitud(User $user, TecnologiaSolicitud $solicitud): bool
    {
        return $this->puedeVerTodo($user)
            || (int) $solicitud->solicitante_id === (int) $user->id
            || (int) $solicitud->asignado_a_id === (int) $user->id;
    }

    private function puedeEditarSolicitud(User $user, TecnologiaSolicitud $solicitud): bool
    {
        return $this->esAdmin($user)
            || $this->esStaffTecnologia($user)
            || (int) $solicitud->solicitante_id === (int) $user->id
            || (int) $solicitud->asignado_a_id === (int) $user->id;
    }

    private function puedeGestionarProgreso(User $user, TecnologiaSolicitud $solicitud): bool
    {
        return $this->esAdmin($user)
            || (int) $solicitud->asignado_a_id === (int) $user->id;
    }

    private function puedeSolicitarCierre(User $user, TecnologiaSolicitud $solicitud): bool
    {
        return $this->esAdmin($user)
            || (int) $solicitud->asignado_a_id === (int) $user->id;
    }

    private function puedeFinalizarSolicitud(User $user, TecnologiaSolicitud $solicitud): bool
    {
        return $this->esAdmin($user)
            || (int) $solicitud->solicitante_id === (int) $user->id;
    }

    private function esAdmin(User $user): bool
    {
        return $user->hasAnyRole(self::ADMIN_ROLE_NAMES);
    }

    private function esStaffTecnologia(User $user): bool
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
}
