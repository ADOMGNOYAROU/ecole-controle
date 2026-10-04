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
            'mot_de_passe' => ['nullable', 'string', 'min:6'],
            'email_manuel' => ['nullable', 'email'],
        ]);

        $profil = match ($request->type) {
            'eleve' => Eleve::findOrFail($request->id),
            'enseignant' => Enseignant::findOrFail($request->id),
            'tuteur' => Tuteur::findOrFail($request->id),
        };

        // Priorité : email saisi manuellement > email du profil
        $email = $request->filled('email_manuel') ? $request->email_manuel : $profil->email;

        if (! $email) {
            return back()->with('error', "Cette personne n'a pas d'adresse email. Saisissez-en une manuellement.");
        }

        if ($profil->user_id) {
            return back()->with('error', 'Cette personne a déjà un compte.');
        }

        if (User::where('email', $email)->exists()) {
            return back()->with('error', "L'adresse {$email} est déjà utilisée par un autre compte.");
        }

        // Priorité : mot de passe saisi > mot de passe aléatoire
        $motDePasse = $request->filled('mot_de_passe')
            ? $request->mot_de_passe
            : $this->genererMotDePasse();

        $user = User::create([
            'ecole_id' => Auth::user()->ecole_id,
            'name' => $profil->nomComplet(),
            'email' => $email,
            'password' => Hash::make($motDePasse),
            'role' => $request->type === 'tuteur' ? User::ROLE_PARENT : $request->type,
            'must_change_password' => ! $request->filled('mot_de_passe'),
        ]);

        $profil->update(['user_id' => $user->id, 'email' => $email]);

        // Tentative d'envoi email (silencieuse si non configuré)
        try {
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
        } catch (\Throwable) {
            // Email non configuré — les identifiants sont affichés à l'écran
        }

        // Affichage des identifiants à l'écran (toujours, car email peut échouer)
        return back()->with('compte_cree', [
            'name' => $user->name,
            'email' => $user->email,
            'password' => $motDePasse,
        ]);
    }

    public function reinitialiserMotDePasse(Request $request, User $user): RedirectResponse
    {
        $this->authorize('update', $user);

        $request->validate([
            'nouveau_mot_de_passe' => ['nullable', 'string', 'min:6'],
        ]);

        $motDePasse = $request->filled('nouveau_mot_de_passe')
            ? $request->nouveau_mot_de_passe
            : $this->genererMotDePasse();

        $user->update([
            'password' => Hash::make($motDePasse),
            'must_change_password' => ! $request->filled('nouveau_mot_de_passe'),
        ]);

        try {
            Mail::to($user->email)->send(new UserAccountCreated([
                'name' => $user->name,
                'email' => $user->email,
                'password' => $motDePasse,
                'type' => 'Réinitialisation',
            ]));
        } catch (\Throwable) {
            // silencieux si email non configuré
        }

        return back()->with('compte_cree', [
            'name' => $user->name,
            'email' => $user->email,
            'password' => $motDePasse,
        ]);
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
