<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @param  string  ...$roles
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next, ...$roles)
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $user = Auth::user();
        
        // Débogage
        \Log::info('RoleMiddleware - User role: ' . $user->role);
        \Log::info('RoleMiddleware - Required roles: ' . implode(', ', $roles));
        \Log::info('RoleMiddleware - Request URL: ' . $request->fullUrl());

        // Aplatir tous les rôles requis
        $allRequiredRoles = [];
        foreach ($roles as $role) {
            // Gérer les rôles multiples séparés par des virgules
            $roleArray = explode(',', $role);
            foreach ($roleArray as $r) {
                $allRequiredRoles[] = trim($r);
            }
        }

        // Vérifier si le rôle de l'utilisateur est dans la liste des rôles requis
        if (in_array($user->role, $allRequiredRoles)) {
            \Log::info('RoleMiddleware - Access granted for role: ' . $user->role);
            return $next($request);
        }

        // Si l'utilisateur n'a aucun des rôles requis
        \Log::error('RoleMiddleware - Access denied. User role: ' . $user->role . ', Required: ' . implode(', ', $allRequiredRoles));
        abort(403, 'Accès non autorisé');
    }
}
