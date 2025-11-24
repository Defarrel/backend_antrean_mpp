<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PermissionMiddleware
{
    /**
     * Handle an incoming request.
     *
     * Usage in route: ->middleware('permission:manage_roles')
     */
    public function handle(Request $request, Closure $next, $permission)
    {
        $user = Auth::user();

        if (!$user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        if (!$user->role) {
            return response()->json(['message' => 'Access denied. No role assigned.'], 403);
        }

        $role = $user->role->loadMissing('permissions');

        $has = $role->permissions->contains('name', $permission);

        if (! $has) {
            return response()->json(['message' => 'Access denied. Required permission: ' . $permission], 403);
        }

        return $next($request);
    }
}
