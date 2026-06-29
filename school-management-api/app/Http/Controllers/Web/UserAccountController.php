<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Mail\UserAccountCreated;
use App\Models\Eleve;
use App\Models\Enseignant;
use App\Models\Tuteur;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\View\View;

class UserAccountController extends Controller
{
    public function index(): View
    {
        $this->authorize('create', User::class);

        return view('comptes.index', [
            'elevesSansCompte' => Eleve::whereNull('user_id')->whereNotNull('email')->orderBy('nom')->get(),
            'enseignantsSansCompte' => Enseignant::whereNull('user_id')->whereNotNull('email')->orderBy('nom')->get(),
            'tuteursSansCompte' => Tuteur::whereNull('user_id')->whereNotNull('email')->orderBy('nom')->get(),
            'utilisateurs' => User::where('ecole_id', Auth::user()->ecole_id)->orderBy('name')->paginate(20),
        ]);
    }

    public function generer(Request $request): RedirectResponse
    {
        $this->authorize('create', User::class);

        $request->validate([
            'type' => ['required', 'in:eleve,enseignant,tuteur'],
            'id' => ['required', 'integer'],
        ]);

        $profil = match ($request->type) {
            'eleve' => Eleve::findOrFail($request->id),
            'enseignant' => Enseignant::findOrFail($request->id),
            'tuteur' => Tuteur::findOrFail($request->id),
        };

        if (! $profil->email) {
            return back()->with('error', 'Cette personne n\'a pas d\'adresse email enregistrée.');
        }

        $motDePasse = $this->genererMotDePasse();

        $user = User::create([
            'ecole_id' => Auth::user()->ecole_id,
            'name' => $profil->nomComplet(),
            'email' => $profil->email,
            'password' => Hash::make($motDePasse),
            'role' => $request->type === 'tuteur' ? User::ROLE_PARENT : $request->type,
            'must_change_password' => true,
        ]);

        $profil->update(['user_id' => $user->id]);

        Mail::to($user->email)->send(new UserAccountCreated([
            'name' => $user->name,
            'email' => $user->email,
            'password' => $motDePasse,
            'type' => match ($request->type) {
                'eleve' => 'Élève',
                'enseignant' => 'Enseignant',
                'tuteur' => 'Parent',
            },
        ]));

        return back()->with('success', "Compte créé pour {$user->name}. Les identifiants ont été envoyés par email.");
    }

    public function reinitialiserMotDePasse(User $user): RedirectResponse
    {
        $this->authorize('update', $user);

        $motDePasse = $this->genererMotDePasse();

        $user->update([
            'password' => Hash::make($motDePasse),
            'must_change_password' => true,
        ]);

        Mail::to($user->email)->send(new UserAccountCreated([
            'name' => $user->name,
            'email' => $user->email,
            'password' => $motDePasse,
            'type' => 'Réinitialisation',
        ]));

        return back()->with('success', "Mot de passe réinitialisé pour {$user->name} et envoyé par email.");
    }

    public function destroy(User $user): RedirectResponse
    {
        $this->authorize('delete', $user);

        $user->delete();

        return back()->with('success', 'Compte supprimé.');
    }

    private function genererMotDePasse(): string
    {
        return Str::password(14);
    }
}
