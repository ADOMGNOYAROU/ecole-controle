<?php

namespace App\Http\Middleware;

use App\Models\Ecole;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EnsureEcoleActive
{
    public function handle(Request $request, Closure $next)
    {
        $user = Auth::user();

        if (! $user || $user->isSuperAdmin() || $request->routeIs('abonnement.*', 'logout')) {
            return $next($request);
        }

        if ($user->ecole && $user->ecole->statut === Ecole::STATUT_SUSPENDU) {
            return redirect()->route('abonnement.index')
                ->with('error', 'L\'accès de votre école est suspendu (abonnement impayé). Contactez l\'administration pour le réactiver.');
        }

        return $next($request);
    }
}
