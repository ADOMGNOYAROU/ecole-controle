<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EnsurePremium
{
    public function handle(Request $request, Closure $next)
    {
        $user = Auth::user();

        if (! $user || $user->isSuperAdmin()) {
            return $next($request);
        }

        $ecole = $user->ecole;

        if ($ecole && $ecole->aAccesPremium()) {
            return $next($request);
        }

        return redirect()->route('abonnement.index')
            ->with('info', 'Cette fonctionnalité fait partie de l\'offre Premium. Passez au Premium pour y accéder.');
    }
}
