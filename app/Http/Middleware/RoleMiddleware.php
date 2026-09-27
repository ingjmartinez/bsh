<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;
use Spatie\Permission\Middleware\RoleMiddleware as SpatieRoleMiddleware;

class RoleMiddleware extends SpatieRoleMiddleware
{
    public function handle($request, Closure $next, $role, $guard = null)
    {
        $user = Auth::guard($guard)->user();
        $roles = explode('|', self::parseRolesToString($role));

        if ($user?->hasRole('admin2') && array_intersect($roles, ['admin', 'superadmin']) !== []) {
            return $next($request);
        }

        return parent::handle($request, $next, $role, $guard);
    }
}
