<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserHasRole
{
    public function handle(Request $request, Closure $next, string $role, ?string $guard = null): Response
    {
        $guard = $guard ?: $role;

        $user = Auth::guard($guard)->user();

        if (! $user) {
            return redirect()->route($role . '.login');
        }

        if (method_exists($user, 'isActive') && ! $user->isActive()) {
            Auth::guard($guard)->logout();

            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()
                ->route($role . '.login')
                ->withErrors([
                    'email' => 'Tu cuenta no está activa. Contacta al administrador.',
                ]);
        }

        if ($guard === 'admin') {
            return $next($request);
        }

        if (method_exists($user, 'hasRole') && ! $user->hasRole($role)) {
            Auth::guard($guard)->logout();

            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()
                ->route($role . '.login')
                ->withErrors([
                    'email' => 'No tienes permisos para acceder a este portal.',
                ]);
        }

        return $next($request);
    }
}