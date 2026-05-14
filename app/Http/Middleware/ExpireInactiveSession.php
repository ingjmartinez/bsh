<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class ExpireInactiveSession
{
    private const LAST_ACTIVITY_KEY = 'auth_last_activity_at';

    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::check()) {
            if (Auth::viaRemember()) {
                return $this->expireSession($request);
            }

            $timeout = (int) config('session.lifetime', 60) * 60;
            $lastActivity = (int) $request->session()->get(self::LAST_ACTIVITY_KEY, time());

            if (time() - $lastActivity > $timeout) {
                return $this->expireSession($request);
            }

            if ($this->countsAsUserActivity($request)) {
                $request->session()->put(self::LAST_ACTIVITY_KEY, time());
            }
        }

        $response = $next($request);

        return $this->addNoCacheHeaders($response);
    }

    private function expireSession(Request $request): Response
    {
        Auth::guard()->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'La sesion expiro por inactividad.',
            ], 419);
        }

        return redirect()
            ->route('login')
            ->with('status', 'La sesion expiro por inactividad. Inicia sesion nuevamente.');
    }

    private function countsAsUserActivity(Request $request): bool
    {
        if ($request->is('tareas/notificaciones*')) {
            return false;
        }

        return true;
    }

    private function addNoCacheHeaders(Response $response): Response
    {
        $response->headers->set('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0');
        $response->headers->set('Pragma', 'no-cache');
        $response->headers->set('Expires', 'Fri, 01 Jan 1990 00:00:00 GMT');

        return $response;
    }
}
