<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

class CheckRole
{
    public function handle($request, Closure $next, ...$roles)
    {
        $user = Auth::user();

        if (!$user) {
            return response()->json(['error' => 'No autenticado.'], 401);
        }

        foreach ($roles as $role) {
            if ($user->tieneRol($role)) {
                return $next($request);
            }
        }

        return response()->json(['error' => 'No tienes permiso para realizar esta acción.'], 403);
    }
}
