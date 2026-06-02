<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsurePasswordHasBeenChanged
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();

        if (!$user || !$user->must_change_password) {
            return $next($request);
        }

        if ($request->routeIs('password.force.*') || $request->routeIs('logout')) {
            return $next($request);
        }

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Debe actualizar su contrasena antes de continuar.',
            ], 409);
        }

        return redirect()->route('password.force.show');
    }
}
