<?php

namespace App\Http\Controllers;

use App\Mail\NuevoUsuarioMail;
use App\Mail\ResetClaveUsuarioMail;
use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rules\Password;
use Spatie\Permission\Models\Role;
use Throwable;

class UserController extends Controller
{
    private const DEFAULT_NEW_USER_PASSWORD = '0000';

    public function __construct()
    {
        $this->middleware('permission:usuarios.view')->only(['index']);
        $this->middleware('permission:usuarios.list')->only(['list']);
        $this->middleware('permission:usuarios.create')->only(['create', 'store']);
        $this->middleware('permission:usuarios.edit')->only(['edit', 'update', 'resetClave']);
        $this->middleware('permission:usuarios.delete')->only(['destroy']);
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return view('usuarios.index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $roles = $this->assignableRoles();

        return view('usuarios.create', compact('roles'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email',
            'roles' => 'nullable|array',
            'roles.*' => ['string', 'exists:roles,name', $this->forbiddenRolesRule()],
        ]);

        $plainPassword = self::DEFAULT_NEW_USER_PASSWORD;
        $validated['password'] = Hash::make($plainPassword);
        $validated['must_change_password'] = true;

        $user = User::create($validated);

        $user->syncRoles($validated['roles'] ?? []);

        // Enviar correo con los datos de acceso
        try {
            Mail::to($user->email)->send(new NuevoUsuarioMail($user, $plainPassword));
        } catch (\Exception $e) {
            return redirect()->route('usuarios.index')
                ->with('success', 'Usuario creado exitosamente.')
                ->with('error', 'No se pudo enviar el correo: '.$e->getMessage());
        }

        return redirect()->route('usuarios.index')
            ->with('success', 'Usuario creado exitosamente. Se envio un correo con la clave generica 0000.');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(User $usuario)
    {
        $this->abortIfSuperadminUserIsProtected($usuario);

        $roles = $this->assignableRoles();
        $userRoles = $usuario->roles->pluck('name')->toArray();

        return view('usuarios.edit', compact('usuario', 'roles', 'userRoles'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, User $usuario)
    {
        $this->abortIfSuperadminUserIsProtected($usuario);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,'.$usuario->id,
            'password' => ['nullable', 'confirmed', Password::min(8)],
            'roles' => 'nullable|array',
            'roles.*' => ['string', 'exists:roles,name', $this->forbiddenRolesRule()],
        ]);

        $usuario->name = $validated['name'];
        $usuario->email = $validated['email'];

        if (! empty($validated['password'])) {
            $usuario->password = Hash::make($validated['password']);
        }

        $usuario->save();

        $usuario->syncRoles($validated['roles'] ?? []);

        return redirect()->route('usuarios.index')
            ->with('success', 'Usuario actualizado exitosamente.');
    }

    public function resetClave(User $usuario)
    {
        $this->abortIfSuperadminUserIsProtected($usuario);
        $plainPassword = self::DEFAULT_NEW_USER_PASSWORD;

        $usuario->password = Hash::make($plainPassword);
        $usuario->must_change_password = true;
        $usuario->save();

        try {
            Mail::to($usuario->email)->send(new ResetClaveUsuarioMail($usuario, $plainPassword));
        } catch (Throwable $error) {
            return redirect()
                ->route('usuarios.index')
                ->with('error', 'La clave fue reseteada, pero no se pudo enviar el correo: '.$error->getMessage());
        }

        return redirect()
            ->route('usuarios.index')
            ->with('success', 'La clave del usuario fue reseteada a 0000 y se envio el correo correctamente.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(User $usuario)
    {
        $this->abortIfSuperadminUserIsProtected($usuario);

        // Evitar que el usuario elimine su propia cuenta
        if ($usuario->id === auth()->id()) {
            return redirect()->route('usuarios.index')
                ->with('error', 'No puedes eliminar tu propia cuenta.');
        }

        $usuario->delete();

        return redirect()->route('usuarios.index')
            ->with('success', 'Usuario eliminado exitosamente.');
    }

    /**
     * Get list of users for DataTable.
     */
    public function list(Request $request)
    {
        $query = User::query()->with('roles')->visibleTo(auth()->user());

        // Búsqueda
        if ($request->has('search') && $request->search['value']) {
            $search = $request->search['value'];
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        // Total de registros
        $totalRecords = User::query()->visibleTo(auth()->user())->count();
        $filteredRecords = $query->count();

        // Ordenamiento
        $columns = ['id', 'name', 'email', 'created_at'];
        $orderColumn = $columns[$request->input('order.0.column', 0)] ?? 'id';
        $orderDir = $request->input('order.0.dir', 'desc');

        // Paginación
        $start = $request->input('start', 0);
        $length = $request->input('length', 10);

        $users = $query->orderBy($orderColumn, $orderDir)
            ->skip($start)
            ->take($length)
            ->get()
            ->map(function ($user) {
                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'roles' => $user->roles->pluck('name')->implode(', '),
                    'must_change_password' => (bool) $user->must_change_password,
                    'created_at' => $user->created_at?->format('d/m/Y H:i'),
                ];
            });

        return response()->json([
            'draw' => intval($request->input('draw')),
            'recordsTotal' => $totalRecords,
            'recordsFiltered' => $filteredRecords,
            'data' => $users,
        ]);
    }

    /**
     * El rol superadmin solo puede asignarse o gestionarse por un superadmin.
     *
     * @return \Illuminate\Database\Eloquent\Collection<int, Role>
     */
    private function assignableRoles()
    {
        return Role::query()
            ->when(! $this->actorIsSuperadmin(), fn ($query) => $query->where('name', '!=', 'superadmin'))
            ->orderBy('name')
            ->get();
    }

    private function forbiddenRolesRule(): Closure
    {
        return function (string $attribute, mixed $value, Closure $fail): void {
            if ($value === 'superadmin' && ! $this->actorIsSuperadmin()) {
                $fail('No tienes permiso para asignar el rol superadmin.');
            }
        };
    }

    private function abortIfSuperadminUserIsProtected(User $usuario): void
    {
        abort_if(
            $usuario->hasRole('superadmin') && ! $this->actorIsSuperadmin(),
            403,
            'Un usuario superadmin no puede ser modificado.'
        );
    }

    private function actorIsSuperadmin(): bool
    {
        return (bool) auth()->user()?->hasRole('superadmin');
    }
}
