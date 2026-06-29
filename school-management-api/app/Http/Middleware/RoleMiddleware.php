<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RoleMiddleware
{
    /**
     * @param  string  ...$roles  Comma-separated lists are flattened, e.g. role:admin,enseignant
     */
    public function handle(Request $request, Closure $next, string ...$roles)
    {
        if (! Auth::check()) {
            return redirect()->route('login');
        }

        $allowedRoles = collect($roles)
            ->flatMap(fn (string $role) => explode(',', $role))
            ->map(fn (string $role) => trim($role))
            ->all();

        if (in_array(Auth::user()->role, $allowedRoles, true)) {
            return $next($request);
        }

        abort(403, 'Accès non autorisé.');
    }
}
