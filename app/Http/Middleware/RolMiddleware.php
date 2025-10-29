<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

class RolMiddleware
{
    public function handle($request, Closure $next, ...$roles)
    {
        $user = Auth::user();
        if (!$user || !$user->rol) {
            abort(403, 'No autorizado');
        }

        $slug = $user->rol->slug;
        if (!in_array($slug, $roles)) {
            abort(403, 'No autorizado');
        }
        return $next($request);
    }
}
