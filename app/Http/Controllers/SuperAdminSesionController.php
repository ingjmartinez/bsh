<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class SuperAdminSesionController extends Controller
{
    public function index()
    {
        $lifetimeMinutes = (int) config('session.lifetime', 120);
        $activeThreshold = now()->subMinutes($lifetimeMinutes)->timestamp;

        $sessionsByUser = DB::table('sessions')
            ->select('user_id', DB::raw('MAX(last_activity) as last_activity'))
            ->whereNotNull('user_id')
            ->groupBy('user_id')
            ->pluck('last_activity', 'user_id');

        $usuarios = User::query()
            ->orderBy('name')
            ->get()
            ->map(function (User $user) use ($sessionsByUser, $activeThreshold) {
                $lastActivity = isset($sessionsByUser[$user->id])
                    ? Carbon::createFromTimestamp((int) $sessionsByUser[$user->id])
                    : null;

                $estaActivo = $lastActivity && $lastActivity->timestamp >= $activeThreshold;

                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'esta_activo' => $estaActivo,
                    'activo_desde' => $estaActivo ? $user->current_login_at : null,
                    'ultimo_inicio_sesion' => $user->current_login_at,
                    'inicio_anterior' => $user->last_login_at,
                    'ultima_actividad' => $lastActivity,
                ];
            });

        return view('superadmin.sesiones.index', compact('usuarios'));
    }
}

