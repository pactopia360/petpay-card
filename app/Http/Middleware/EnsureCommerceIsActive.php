<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureCommerceIsActive
{
    public function handle(
        Request $request,
        Closure $next
    ): Response|RedirectResponse {
        $commerce = $request->user('comercio');

        if (! $commerce) {
            return redirect()->route('comercio.login');
        }

        if (! $commerce->canAccessPortal()) {
            return redirect()->route(
                'comercio.registration.pending'
            );
        }

        return $next($request);
    }
}
