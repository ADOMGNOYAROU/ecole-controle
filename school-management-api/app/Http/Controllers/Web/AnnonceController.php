<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\AnnonceRequest;
use App\Models\Annonce;
use App\Models\Classe;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AnnonceController extends Controller
{
    public function index(): View
    {
        $this->authorize('viewAny', Annonce::class);

        $user = Auth::user();

        $annonces = Annonce::with('auteur', 'classe')
            ->where(function ($q) use ($user) {
                $q->where('cible', 'tous');

                match (true) {
                    $user->isParent() => $q->orWhere('cible', 'parents'),
                    $user->isEnseignant() => $q->orWhere('cible', 'enseignants'),
                    $user->isEleve() => $q->orWhere('cible', 'eleves')
                        ->orWhere(fn ($q2) => $q2->where('cible', 'classe')->where('classe_id', $user->eleve?->classe_id)),
                    default => $q,
                };

                if ($user->isAdmin()) {
                    $q->orWhere('cible', '!=', null);
                }
            })
            ->latest('date_publication')
            ->paginate(15);

        return view('annonces.index', compact('annonces'));
    }

    public function create(): View
    {
        $this->authorize('create', Annonce::class);

        return view('annonces.create', ['classes' => Classe::orderBy('nom')->get()]);
    }

    public function store(AnnonceRequest $request): RedirectResponse
    {
        $this->authorize('create', Annonce::class);

        $annonce = Annonce::create([
            ...$request->validated(),
            'auteur_id' => Auth::id(),
            'date_publication' => now(),
        ]);

        $this->notifierDestinataires($annonce);

        return redirect()->route('annonces.index')->with('success', 'Annonce publiée.');
    }

    public function destroy(Annonce $annonce): RedirectResponse
    {
        $this->authorize('delete', $annonce);

        $annonce->delete();

        return redirect()->route('annonces.index')->with('success', 'Annonce supprimée.');
    }

    private function notifierDestinataires(Annonce $annonce): void
    {
        $destinataires = User::query()
            ->where('ecole_id', $annonce->ecole_id)
            ->when($annonce->cible === 'parents', fn ($q) => $q->where('role', User::ROLE_PARENT))
            ->when($annonce->cible === 'enseignants', fn ($q) => $q->where('role', User::ROLE_ENSEIGNANT))
            ->when($annonce->cible === 'eleves', fn ($q) => $q->where('role', User::ROLE_ELEVE))
            ->when($annonce->cible === 'classe', fn ($q) => $q->where('role', User::ROLE_ELEVE)
                ->whereHas('eleve', fn ($q2) => $q2->where('classe_id', $annonce->classe_id)))
            ->when($annonce->cible === 'tous', fn ($q) => $q->whereIn('role', [User::ROLE_PARENT, User::ROLE_ENSEIGNANT, User::ROLE_ELEVE]))
            ->pluck('id');

        foreach ($destinataires as $userId) {
            Notification::create([
                'user_id' => $userId,
                'titre' => 'Nouvelle annonce',
                'message' => $annonce->titre,
                'type' => 'annonce',
                'lien' => route('annonces.index'),
            ]);
        }
    }
}
