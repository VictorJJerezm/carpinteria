<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class InactividadSesion
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle($request, Closure $next)
    {
        // Solo aplica en rutas web con sesión iniciada
        if (Auth::check()) {
            $lifetimeSec = config('session.lifetime') * 60; // minutos → segundos
            $last = (int) $request->session()->get('last_activity', 0);
            $now  = time();

            if ($last && ($now - $last) > $lifetimeSec) {
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                $request->session()->flash('info', 'Tu sesión se cerró por inactividad.');
                return redirect()->route('login', ['timeout' => 1]);
            }

            // Actualiza marca de actividad
            $request->session()->put('last_activity', $now);
        }

        return $next($request);
    }
}
