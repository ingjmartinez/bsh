<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class PreventAdmin2Deletion
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user?->hasRole('admin2')) {
            return $next($request);
        }

        $route = $request->route();
        $routeDescriptor = implode(' ', array_filter([
            $request->path(),
            $route?->getName(),
            $route?->getActionName(),
        ]));

        $isDestructiveMethod = in_array($request->method(), ['DELETE'], true);
        $isDestructiveRoute = preg_match('/(^|[.\/_-])(delete|destroy|eliminar|borrar|vaciar|truncate|remove)([.\/_-]|$)/i', $routeDescriptor) === 1;

        if ($isDestructiveMethod || $isDestructiveRoute) {
            $message = 'El rol admin2 no tiene permiso para eliminar contenido.';

            if ($request->expectsJson()) {
                return response()->json(['message' => $message], Response::HTTP_FORBIDDEN);
            }

            abort(Response::HTTP_FORBIDDEN, $message);
        }

        return $next($request);
    }
}
