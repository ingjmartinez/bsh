<?php

namespace App\Http\Controllers;

use App\Notifications\TareaAsignadaNotification;
use App\Notifications\TareaCerradaPorAdminNotification;
use App\Notifications\TareaComentarioNotification;
use App\Notifications\TareaProgresoActualizadoNotification;
use App\Notifications\TareaSolicitudCierreNotification;
use App\Models\Tarea;
use App\Models\TareaComentario;
use App\Models\DepartamentoCrm;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Role;

class TareaController extends Controller
{
    /**
     * Vista principal — Dashboard + Gantt.
     */
    public function index()
    {
        $departamentos = DepartamentoCrm::activos()->orderBy('nombre')->get();
        $usuarios = User::orderBy('name')->get();
        $rolesCierre = Role::query()
            ->whereIn('name', ['superadmin', 'admin', 'superior'])
            ->pluck('name')
            ->all();

        $esAdminSuperior = auth()->check() && !empty($rolesCierre)
            ? auth()->user()->hasAnyRole($rolesCierre)
            : false;

        // Estadísticas rápidas
        $stats = [
            'total' => Tarea::count(),
            'pendientes' => Tarea::where('estado', 'pendiente')->count(),
            'en_progreso' => Tarea::where('estado', 'en_progreso')->count(),
            'completadas' => Tarea::where('estado', 'completada')->count(),
            'atrasadas' => Tarea::atrasadas()->count(),
        ];

        return view('tareas.index', compact('departamentos', 'usuarios', 'stats', 'esAdminSuperior'));
    }

    /**
     * API — Tareas para el diagrama Gantt (JSON).
     */
    public function ganttData(Request $request)
    {
        $query = Tarea::with(['departamento', 'creador', 'asignado', 'subtareas'])
                      ->principales();

        // Filtro por departamento
        if ($request->filled('departamento_id')) {
            $query->delDepartamento($request->departamento_id);
        }

        // Filtro por estado
        if ($request->filled('estado')) {
            $query->where('estado', $request->estado);
        }

        // Filtro por usuario asignado
        if ($request->filled('asignado_id')) {
            $query->where('asignado_id', $request->asignado_id);
        }

        // Filtro solo atrasadas
        if ($request->boolean('atrasadas')) {
            $query->atrasadas();
        }

        // Filtro rango de fechas
        if ($request->filled('fecha_desde') && $request->filled('fecha_hasta')) {
            $query->where(function ($q) use ($request) {
                $q->whereBetween('fecha_inicio', [$request->fecha_desde, $request->fecha_hasta])
                  ->orWhereBetween('fecha_fin', [$request->fecha_desde, $request->fecha_hasta]);
            });
        }

        $tareas = $query->orderBy('fecha_inicio')->get();

        $data = [];
        foreach ($tareas as $tarea) {
            $data[] = $this->formatTareaGantt($tarea);

            // Incluir subtareas
            foreach ($tarea->subtareas as $sub) {
                $data[] = $this->formatTareaGantt($sub, $tarea->id);
            }
        }

        return response()->json($data);
    }

    /**
     * API — Listado para DataTable.
     */
    public function list(Request $request)
    {
        $query = Tarea::with(['departamento', 'creador', 'asignado', 'cierreSolicitadoPor']);

        $tareaId = $request->input('tarea_id');

        if (!empty($tareaId) && is_numeric($tareaId)) {
            $query->where('id', (int) $tareaId);
        }

        if (empty($tareaId) && $request->filled('departamento_id')) {
            $query->delDepartamento($request->departamento_id);
        }
        if (empty($tareaId) && $request->filled('estado')) {
            $query->where('estado', $request->estado);
        }

        // Búsqueda
        if (empty($tareaId) && $request->has('search') && $request->search['value']) {
            $search = $request->search['value'];
            $query->where(function ($q) use ($search) {
                if (is_numeric($search)) {
                    $q->orWhere('id', (int) $search);
                }

                $q->where('titulo', 'like', "%{$search}%")
                  ->orWhereHas('departamento', fn($d) => $d->where('nombre', 'like', "%{$search}%"))
                  ->orWhereHas('asignado', fn($u) => $u->where('name', 'like', "%{$search}%"));
            });
        }

        $totalRecords = Tarea::count();
        $filteredRecords = $query->count();

        $columns = ['id', 'titulo', 'departamento_id', 'estado', 'prioridad', 'progreso', 'fecha_inicio', 'fecha_fin', 'created_at'];
        $orderColumn = $columns[$request->input('order.0.column', 0)] ?? 'id';
        $orderDir = $request->input('order.0.dir', 'desc');

        $start = $request->input('start', 0);
        $length = $request->input('length', 10);

        $tareas = $query->orderBy($orderColumn, $orderDir)
                        ->skip($start)
                        ->take($length)
                        ->get()
                        ->map(function ($tarea) {
                            return [
                                'id' => $tarea->id,
                                'titulo' => $tarea->titulo,
                                'departamento' => $tarea->departamento->nombre ?? '-',
                                'depto_color' => $tarea->departamento->color ?? '#405189',
                                'asignado' => $tarea->asignado->name ?? 'Sin asignar',
                                'estado' => $tarea->estado,
                                'badge_estado' => $tarea->badge_estado,
                                'prioridad' => $tarea->prioridad,
                                'progreso' => $tarea->progreso,
                                'fecha_inicio' => $tarea->fecha_inicio->format('d/m/Y'),
                                'fecha_fin' => $tarea->fecha_fin->format('d/m/Y'),
                                'atrasada' => $tarea->atrasada,
                                'dias_atraso' => $tarea->dias_atraso,
                                'cierre_solicitado_at' => optional($tarea->cierre_solicitado_at)->format('d/m/Y H:i'),
                                'cierre_solicitado_por' => $tarea->cierreSolicitadoPor?->name,
                            ];
                        });

        return response()->json([
            'draw' => intval($request->input('draw')),
            'recordsTotal' => $totalRecords,
            'recordsFiltered' => $filteredRecords,
            'data' => $tareas,
        ]);
    }

    /**
     * Guardar nueva tarea.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'titulo' => 'required|string|max:255',
            'descripcion' => 'nullable|string',
            'adjunto' => 'nullable|file|max:10240',
            'departamento_id' => 'required|exists:departamentos,id',
            'asignado_id' => 'nullable|exists:users,id',
            'tarea_padre_id' => 'nullable|exists:tareas,id',
            'prioridad' => 'required|in:baja,media,alta,critica',
            'fecha_inicio' => 'required|date',
            'fecha_fin' => 'required|date|after_or_equal:fecha_inicio',
        ]);

        if ($request->hasFile('adjunto')) {
            $archivo = $request->file('adjunto');
            $validated['adjunto_path'] = $archivo->store('tareas_adjuntos', 'public');
            $validated['adjunto_nombre'] = $archivo->getClientOriginalName();
        }

        $validated['user_id'] = auth()->id();
        $validated['estado'] = 'pendiente';
        $validated['progreso'] = 0;

        $tarea = Tarea::create($validated);

        // Registrar en historial
        TareaComentario::create([
            'tarea_id' => $tarea->id,
            'user_id' => auth()->id(),
            'comentario' => 'Tarea creada.',
            'tipo' => 'cambio_estado',
        ]);

        if (!empty($tarea->asignado_id) && (int) $tarea->asignado_id !== (int) auth()->id()) {
            $asignado = User::find($tarea->asignado_id);
            if ($asignado) {
                $asignado->notify(new TareaAsignadaNotification($tarea, auth()->user()));
            }
        }

        return response()->json(['success' => true, 'message' => 'Tarea creada exitosamente.', 'tarea' => $tarea]);
    }

    /**
     * Detalle de una tarea (JSON).
     */
    public function show(Tarea $tarea)
    {
        $tarea->load(['departamento', 'creador', 'asignado', 'cierreSolicitadoPor', 'subtareas.asignado', 'comentarios.usuario']);

        return response()->json([
            'tarea' => $tarea,
            'atrasada' => $tarea->atrasada,
            'dias_atraso' => $tarea->dias_atraso,
        ]);
    }

    public function solicitarCierre(Tarea $tarea)
    {
        $actor = auth()->user();

        $puedeSolicitar = (int) $actor->id === (int) $tarea->asignado_id || (int) $actor->id === (int) $tarea->user_id;
        if (!$puedeSolicitar) {
            return response()->json([
                'success' => false,
                'message' => 'Solo el usuario asignado o creador puede solicitar el cierre.',
            ], 403);
        }

        if ((int) $tarea->progreso < 100) {
            return response()->json([
                'success' => false,
                'message' => 'La tarea debe tener 100% de progreso para solicitar cierre.',
            ], 422);
        }

        if (in_array($tarea->estado, ['completada', 'cancelada'])) {
            return response()->json([
                'success' => false,
                'message' => 'Esta tarea ya está cerrada y no requiere solicitud.',
            ], 422);
        }

        $tarea->update([
            'cierre_solicitado_at' => now(),
            'cierre_solicitado_por' => $actor->id,
        ]);

        TareaComentario::create([
            'tarea_id' => $tarea->id,
            'user_id' => $actor->id,
            'comentario' => 'Se solicitó cierre de la tarea al completar 100% del progreso.',
            'tipo' => 'comentario',
        ]);

        $rolesCierre = Role::query()
            ->whereIn('name', ['superadmin', 'admin', 'superior'])
            ->pluck('name')
            ->all();

        $admins = empty($rolesCierre)
            ? collect()
            : User::role($rolesCierre)->get();

        foreach ($admins as $admin) {
            if ((int) $admin->id !== (int) $actor->id) {
                $admin->notify(new TareaSolicitudCierreNotification($tarea, $actor));
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Solicitud de cierre enviada a administración.',
        ]);
    }

    public function finalizarPorAdmin(Tarea $tarea)
    {
        $actor = auth()->user();

        $rolesCierre = Role::query()
            ->whereIn('name', ['superadmin', 'admin', 'superior'])
            ->pluck('name')
            ->all();

        if (!$actor || empty($rolesCierre) || !$actor->hasAnyRole($rolesCierre)) {
            return response()->json([
                'success' => false,
                'message' => 'No tienes permisos para finalizar tareas.',
            ], 403);
        }

        if ($tarea->estado === 'completada') {
            return response()->json([
                'success' => true,
                'message' => 'La tarea ya estaba finalizada.',
            ]);
        }

        $tarea->update([
            'estado' => 'completada',
            'progreso' => 100,
            'fecha_completada' => now()->toDateString(),
            'cierre_solicitado_at' => $tarea->cierre_solicitado_at ?? now(),
            'cierre_solicitado_por' => $tarea->cierre_solicitado_por,
        ]);

        TareaComentario::create([
            'tarea_id' => $tarea->id,
            'user_id' => $actor->id,
            'comentario' => 'Tarea finalizada por administración.',
            'tipo' => 'cambio_estado',
        ]);

        $destinatarios = collect([$tarea->asignado_id, $tarea->cierre_solicitado_por])
            ->filter()
            ->unique();

        User::whereIn('id', $destinatarios)->get()->each(function ($usuario) use ($tarea, $actor) {
            if ((int) $usuario->id !== (int) $actor->id) {
                $usuario->notify(new TareaCerradaPorAdminNotification($tarea, $actor));
            }
        });

        return response()->json([
            'success' => true,
            'message' => 'Tarea finalizada correctamente por administración.',
        ]);
    }

    /**
     * Actualizar tarea.
     */
    public function update(Request $request, Tarea $tarea)
    {
        $validated = $request->validate([
            'titulo' => 'required|string|max:255',
            'descripcion' => 'nullable|string',
            'adjunto' => 'nullable|file|max:10240',
            'departamento_id' => 'required|exists:departamentos,id',
            'asignado_id' => 'nullable|exists:users,id',
            'prioridad' => 'required|in:baja,media,alta,critica',
            'estado' => 'required|in:pendiente,en_progreso,completada,cancelada',
            'progreso' => 'required|integer|min:0|max:100',
            'fecha_inicio' => 'required|date',
            'fecha_fin' => 'required|date|after_or_equal:fecha_inicio',
        ]);

        if ($request->hasFile('adjunto')) {
            if (!empty($tarea->adjunto_path)) {
                Storage::disk('public')->delete($tarea->adjunto_path);
            }

            $archivo = $request->file('adjunto');
            $validated['adjunto_path'] = $archivo->store('tareas_adjuntos', 'public');
            $validated['adjunto_nombre'] = $archivo->getClientOriginalName();
        }

        $estadoAnterior = $tarea->estado;
        $progresoAnterior = $tarea->progreso;
        $asignadoAnterior = $tarea->asignado_id;

        // Si se marca como completada, registrar fecha
        if ($validated['estado'] === 'completada' && $estadoAnterior !== 'completada') {
            $validated['fecha_completada'] = Carbon::today();
            $validated['progreso'] = 100;
        }

        // Si se cambia de completada a otro estado, limpiar fecha
        if ($estadoAnterior === 'completada' && $validated['estado'] !== 'completada') {
            $validated['fecha_completada'] = null;
        }

        $tarea->update($validated);

        // Registrar cambios en historial
        if ($estadoAnterior !== $validated['estado']) {
            TareaComentario::create([
                'tarea_id' => $tarea->id,
                'user_id' => auth()->id(),
                'comentario' => "Estado cambiado de \"{$estadoAnterior}\" a \"{$validated['estado']}\".",
                'tipo' => 'cambio_estado',
            ]);
        }

        if ($progresoAnterior !== $validated['progreso']) {
            TareaComentario::create([
                'tarea_id' => $tarea->id,
                'user_id' => auth()->id(),
                'comentario' => "Progreso actualizado de {$progresoAnterior}% a {$validated['progreso']}%.",
                'tipo' => 'cambio_progreso',
            ]);

            if (!empty($tarea->user_id) && (int) $tarea->user_id !== (int) auth()->id()) {
                $creador = User::find($tarea->user_id);
                if ($creador) {
                    $creador->notify(new TareaProgresoActualizadoNotification(
                        $tarea,
                        auth()->user(),
                        (int) $progresoAnterior,
                        (int) $validated['progreso']
                    ));
                }
            }
        }

        if (
            !empty($validated['asignado_id'])
            && (int) $validated['asignado_id'] !== (int) $asignadoAnterior
            && (int) $validated['asignado_id'] !== (int) auth()->id()
        ) {
            $nuevoAsignado = User::find($validated['asignado_id']);
            if ($nuevoAsignado) {
                $nuevoAsignado->notify(new TareaAsignadaNotification($tarea, auth()->user()));
            }
        }

        return response()->json(['success' => true, 'message' => 'Tarea actualizada exitosamente.']);
    }

    /**
     * Eliminar tarea.
     */
    public function destroy(Tarea $tarea)
    {
        if (!empty($tarea->adjunto_path)) {
            Storage::disk('public')->delete($tarea->adjunto_path);
        }

        $tarea->delete();
        return response()->json(['success' => true, 'message' => 'Tarea eliminada exitosamente.']);
    }

    /**
     * Agregar comentario a una tarea.
     */
    public function addComentario(Request $request, Tarea $tarea)
    {
        $validated = $request->validate([
            'comentario' => 'required|string|max:1000',
        ]);

        $comentario = TareaComentario::create([
            'tarea_id' => $tarea->id,
            'user_id' => auth()->id(),
            'comentario' => $validated['comentario'],
            'tipo' => 'comentario',
        ]);

        $comentario->load('usuario');

        $destinatarios = collect([$tarea->user_id, $tarea->asignado_id])
            ->filter()
            ->unique()
            ->reject(fn($id) => (int) $id === (int) auth()->id())
            ->values();

        if ($destinatarios->isNotEmpty()) {
            User::whereIn('id', $destinatarios)->get()->each(function ($usuario) use ($tarea, $validated) {
                $usuario->notify(new TareaComentarioNotification($tarea, auth()->user(), $validated['comentario']));
            });
        }

        return response()->json(['success' => true, 'comentario' => $comentario]);
    }

    public function listNotificaciones()
    {
        $user = auth()->user();
        $modulosPermitidos = ['tareas', 'tecnologia'];

        $notificaciones = $user->notifications()
            ->latest()
            ->take(15)
            ->get()
            ->filter(fn($notification) => in_array(($notification->data['module'] ?? null), $modulosPermitidos, true))
            ->values()
            ->map(function ($notification) {
                return [
                    'id' => $notification->id,
                    'title' => $notification->data['title'] ?? 'Notificación',
                    'message' => $notification->data['message'] ?? '',
                    'url' => $notification->data['url'] ?? url('/tareas'),
                    'task_id' => $notification->data['task_id'] ?? null,
                    'type' => $notification->data['type'] ?? 'general',
                    'read_at' => optional($notification->read_at)?->toDateTimeString(),
                    'created_at' => $notification->created_at->diffForHumans(),
                ];
            });

        $noLeidas = $user->unreadNotifications()
            ->get()
            ->filter(fn($notification) => in_array(($notification->data['module'] ?? null), $modulosPermitidos, true))
            ->count();

        return response()->json([
            'unread' => $noLeidas,
            'items' => $notificaciones,
        ]);
    }

    public function marcarNotificacionLeida(string $notificationId)
    {
        $notification = auth()->user()
            ->notifications()
            ->where('id', $notificationId)
            ->firstOrFail();

        if (is_null($notification->read_at)) {
            $notification->markAsRead();
        }

        return response()->json(['success' => true]);
    }

    public function marcarTodasLeidas()
    {
        $modulosPermitidos = ['tareas', 'tecnologia'];

        auth()->user()
            ->unreadNotifications
            ->filter(fn($notification) => in_array(($notification->data['module'] ?? null), $modulosPermitidos, true))
            ->each
            ->markAsRead();

        return response()->json(['success' => true]);
    }

    /**
     * API — Estadísticas para dashboard.
     */
    public function stats(Request $request)
    {
        $query = Tarea::query();

        if ($request->filled('departamento_id')) {
            $query->delDepartamento($request->departamento_id);
        }

        $total = (clone $query)->count();
        $porEstado = [
            'pendientes' => (clone $query)->where('estado', 'pendiente')->count(),
            'en_progreso' => (clone $query)->where('estado', 'en_progreso')->count(),
            'completadas' => (clone $query)->where('estado', 'completada')->count(),
            'canceladas' => (clone $query)->where('estado', 'cancelada')->count(),
        ];
        $atrasadas = (clone $query)->atrasadas()->count();

        // Tareas por departamento
        $porDepartamento = DepartamentoCrm::activos()
            ->withCount('tareas')
            ->orderByDesc('tareas_count')
            ->get()
            ->map(fn($d) => ['nombre' => $d->nombre, 'color' => $d->color, 'total' => $d->tareas_count]);

        return response()->json([
            'total' => $total,
            'por_estado' => $porEstado,
            'atrasadas' => $atrasadas,
            'por_departamento' => $porDepartamento,
        ]);
    }

    /* ───────────── CRUD Departamentos ───────────── */

    public function departamentos()
    {
        return response()->json(DepartamentoCrm::activos()->orderBy('nombre')->get());
    }

    public function usuarios()
    {
        return response()->json(
            User::query()
                ->select('id', 'name')
                ->orderBy('name')
                ->get()
        );
    }

    public function storeDepartamento(Request $request)
    {
        $validated = $request->validate([
            'nombre' => 'required|string|max:100|unique:departamentos,nombre',
            'descripcion' => 'nullable|string|max:255',
            'color' => 'required|string|max:7',
        ]);

        $depto = DepartamentoCrm::create($validated);
        return response()->json(['success' => true, 'departamento' => $depto]);
    }

    public function updateDepartamento(Request $request, DepartamentoCrm $departamento)
    {
        $validated = $request->validate([
            'nombre' => 'required|string|max:100|unique:departamentos,nombre,' . $departamento->id,
            'descripcion' => 'nullable|string|max:255',
            'color' => 'required|string|max:7',
        ]);

        $departamento->update($validated);
        return response()->json(['success' => true, 'departamento' => $departamento]);
    }

    public function destroyDepartamento(DepartamentoCrm $departamento)
    {
        if ($departamento->tareas()->count() > 0) {
            return response()->json(['success' => false, 'message' => 'No se puede eliminar un departamento con tareas asignadas.'], 422);
        }
        $departamento->delete();
        return response()->json(['success' => true, 'message' => 'Departamento eliminado.']);
    }

    /* ───────────── Helpers ───────────── */

    private function formatTareaGantt(Tarea $tarea, $parentId = null): array
    {
        $color = $tarea->atrasada ? '#f06548' : $tarea->color_prioridad;

        return [
            'id' => $tarea->id,
            'text' => $tarea->titulo,
            'start_date' => $tarea->fecha_inicio->format('Y-m-d'),
            'end_date' => $tarea->fecha_fin->addDay()->format('Y-m-d'), // Gantt usa end exclusive
            'progress' => $tarea->progreso / 100,
            'parent' => $parentId,
            'color' => $color,
            'estado' => $tarea->estado,
            'prioridad' => $tarea->prioridad,
            'departamento' => $tarea->departamento->nombre ?? '',
            'depto_color' => $tarea->departamento->color ?? '#405189',
            'asignado' => $tarea->asignado->name ?? 'Sin asignar',
            'atrasada' => $tarea->atrasada,
            'dias_atraso' => $tarea->dias_atraso,
        ];
    }
}
