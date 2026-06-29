<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\InscriptionEcoleRequest;
use App\Models\AnneeScolaire;
use App\Models\Ecole;
use App\Models\Trimestre;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class InscriptionController extends Controller
{
    public function show(): View
    {
        return view('inscription.show');
    }

    public function store(InscriptionEcoleRequest $request): RedirectResponse
    {
        $donnees = $request->validated();

        $ecole = DB::transaction(function () use ($donnees) {
            $ecole = Ecole::create([
                'nom' => $donnees['nom_ecole'],
                'slug' => Ecole::genererSlug($donnees['nom_ecole']),
                'email_contact' => $donnees['admin_email'],
                'telephone' => $donnees['telephone'] ?? null,
                'ville' => $donnees['ville'] ?? null,
                'statut' => Ecole::STATUT_ESSAI,
                'plan' => Ecole::PLAN_PREMIUM,
                'trial_ends_at' => now()->addDays(30),
            ]);

            $admin = User::create([
                'ecole_id' => $ecole->id,
                'name' => $donnees['admin_nom'],
                'email' => $donnees['admin_email'],
                'password' => Hash::make($donnees['admin_password']),
                'role' => User::ROLE_ADMIN,
            ]);

            $anneeScolaire = AnneeScolaire::create([
                'ecole_id' => $ecole->id,
                'libelle' => now()->year.'-'.(now()->year + 1),
                'date_debut' => now()->startOfYear(),
                'date_fin' => now()->endOfYear(),
                'active' => true,
            ]);

            Trimestre::create([
                'ecole_id' => $ecole->id,
                'annee_scolaire_id' => $anneeScolaire->id,
                'nom' => '1er trimestre',
                'ordre' => 1,
                'date_debut' => now(),
                'date_fin' => now()->addMonths(3),
            ]);

            Auth::login($admin);

            return $ecole;
        });

        return redirect()->route('dashboard')
            ->with('success', "Bienvenue {$ecole->nom} ! Vous bénéficiez de 30 jours d'essai Premium gratuit.");
    }
}
