<?php

namespace App\Http\Middleware;

use App\Models\User;
use App\Services\AccesSupport;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

/**
 * Pendant un accès support, la requête est servie avec le compte du directeur de l'école,
 * en lecture seule : seules les pages sont autorisées (pas de POST/PUT/DELETE), les
 * messages privés restent fermés, et tout ce que la page écrirait en base est annulé.
 */
class ModeSupport
{
    public function __construct(private AccesSupport $support) {}

    public function handle(Request $request, Closure $next): Response
    {
        $acces = $this->support->actif();

        if (! $acces) {
            return $next($request);
        }

        $superAdmin = Auth::guard('web')->user();

        if (! $superAdmin instanceof User || ! $superAdmin->isSuperAdmin() || $superAdmin->id !== $acces['super_admin_id']) {
            session()->forget('acces_support');

            return $next($request);
        }

        if ($request->routeIs('support.quitter')) {
            return $next($request);
        }

        if ($request->routeIs('logout')) {
            $this->support->terminer('deconnexion', $superAdmin);

            return $next($request);
        }

        $directeur = User::find($acces['directeur_id']);

        if ($this->support->expire() || ! $directeur) {
            $ecoleId = $this->support->terminer($directeur ? 'duree_ecoulee' : 'compte_introuvable', $superAdmin);

            return redirect()->route('super-admin.ecoles.show', $ecoleId)
                ->with('success', 'Le mode support est terminé (30 minutes écoulées). Relancez-le si besoin.');
        }

        if ($request->routeIs('messagerie.*')) {
            return redirect()->route('dashboard')->with('error', 'Les messages privés ne sont jamais accessibles en mode support.');
        }

        if (! $request->isMethodSafe()) {
            return back()->with('error', 'Mode support : lecture seule, aucune modification n\'est possible.');
        }

        Auth::guard('web')->setUser($directeur);

        // Lecture seule garantie : même une page qui écrit en base (ex. génération de PDF) est annulée
        DB::beginTransaction();

        try {
            $reponse = $next($request);
        } finally {
            DB::rollBack();
            Auth::guard('web')->setUser($superAdmin);
        }

        $this->support->pageVue('/'.$request->path(), $superAdmin);

        return $reponse;
    }
}
