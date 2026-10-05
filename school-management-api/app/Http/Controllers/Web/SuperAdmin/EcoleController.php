<?php

namespace App\Http\Controllers\Web\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Classe;
use App\Models\Ecole;
use App\Models\Eleve;
use App\Models\Enseignant;
use App\Models\JournalAudit;
use App\Models\NoteInterne;
use App\Models\User;
use App\Services\AccesSupport;
use App\Services\JournalAuditeur;
use App\Services\SuiviEcoles;
use App\Support\WhatsApp;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\View\View;

class EcoleController extends Controller
{
    public function __construct(private JournalAuditeur $auditeur) {}

    public function index(Request $request, SuiviEcoles $suivi): View
    {
        $ecoles = Ecole::withCount('users')
            ->when($request->filled('recherche'), function ($q) use ($request) {
                $terme = $request->recherche;
                $q->where(fn ($q2) => $q2->where('nom', 'like', "%{$terme}%")
                    ->orWhere('ville', 'like', "%{$terme}%"));
            })
            ->when($request->filled('statut'), fn ($q) => $q->where('statut', $request->statut))
            ->when($request->filled('plan'), fn ($q) => $q->where('plan', $request->plan))
            ->orderByDesc('created_at')
            ->paginate(20)
            ->withQueryString();

        return view('super-admin.ecoles.index', ['ecoles' => $ecoles, 'sante' => $suivi->sante()]);
    }

    public function show(Ecole $ecole, SuiviEcoles $suivi): View
    {
        $this->auditeur->enregistrer('consultation', $ecole);

        $ecole->load(['abonnements' => fn ($q) => $q->orderByDesc('date_fin'), 'factures' => fn ($q) => $q->orderByDesc('created_at')]);

        $stats = [
            'eleves' => Eleve::where('ecole_id', $ecole->id)->count(),
            'enseignants' => Enseignant::where('ecole_id', $ecole->id)->count(),
            'classes' => Classe::where('ecole_id', $ecole->id)->count(),
            'utilisateurs' => $ecole->users()->count(),
        ];

        return view('super-admin.ecoles.show', [
            'ecole' => $ecole,
            'stats' => $stats,
            'abonnementActif' => $ecole->abonnementActif(),
            'sante' => $suivi->sante()[$ecole->id] ?? null,
            'directeur' => $ecole->directeur(),
            'utilisateurs' => $ecole->users()
                ->orderByRaw("case role when 'admin' then 1 when 'enseignant' then 2 when 'parent' then 3 else 4 end")
                ->orderBy('name')
                ->get(),
            'notesInternes' => $ecole->notesInternes()->with('auteur:id,name')->latest('id')->get(),
            'activite' => JournalAudit::where('ecole_id', $ecole->id)->latest('id')->limit(20)->get(),
        ]);
    }

    public function suspendre(Ecole $ecole): RedirectResponse
    {
        $ecole->update(['statut' => Ecole::STATUT_SUSPENDU]);

        return back()->with('success', "École {$ecole->nom} suspendue.");
    }

    public function activer(Ecole $ecole): RedirectResponse
    {
        $ecole->update(['statut' => Ecole::STATUT_ACTIF]);

        return back()->with('success', "École {$ecole->nom} réactivée.");
    }

    public function prolongerEssai(Request $request, Ecole $ecole): RedirectResponse
    {
        $request->validate(['jours' => ['required', 'integer', 'in:7,15,30']]);

        if (! in_array($ecole->statut, [Ecole::STATUT_ESSAI, Ecole::STATUT_EXPIRE], true)) {
            return back()->with('error', "Seule une école en essai ou à l'essai expiré peut être prolongée.");
        }

        $jours = $request->integer('jours');
        $avant = $ecole->trial_ends_at;
        $depart = $avant?->isFuture() ? $avant->copy() : now();

        $ecole->update([
            'trial_ends_at' => $depart->addDays($jours),
            'statut' => Ecole::STATUT_ESSAI,
            'plan' => Ecole::PLAN_PREMIUM,
        ]);

        $this->auditeur->enregistrer('prolongation_essai', $ecole, [
            'jours' => $jours,
            'avant' => $avant?->format('d/m/Y'),
            'apres' => $ecole->trial_ends_at->format('d/m/Y'),
        ]);

        return back()->with('success', "Essai prolongé de {$jours} jours, jusqu'au {$ecole->trial_ends_at->format('d/m/Y')}.");
    }

    public function reinitialiserAcces(Ecole $ecole, User $utilisateur): RedirectResponse
    {
        abort_unless($utilisateur->ecole_id === $ecole->id, 404);

        $motDePasse = Str::password(10, symbols: false);

        $utilisateur->update([
            'password' => Hash::make($motDePasse),
            'must_change_password' => true,
        ]);

        $this->auditeur->enregistrer('reinitialisation_acces', $utilisateur, ['email' => $utilisateur->email], ecoleId: $ecole->id);

        $message = "Bonjour {$utilisateur->name}, votre accès à École Manager a été réinitialisé.\n"
            ."Identifiant : {$utilisateur->email}\nMot de passe temporaire : {$motDePasse}\n"
            .'Changez-le dès votre prochaine connexion : '.route('login');

        // Affiché une seule fois au super administrateur, jamais enregistré en clair
        return back()->with('acces_temporaire', [
            'nom' => $utilisateur->name,
            'email' => $utilisateur->email,
            'mot_de_passe' => $motDePasse,
            'whatsapp' => WhatsApp::lien($utilisateur->phone ?? $ecole->telephone, $message),
        ]);
    }

    public function ajouterNote(Request $request, Ecole $ecole): RedirectResponse
    {
        $donnees = $request->validate(['contenu' => ['required', 'string', 'max:2000']]);

        $note = $ecole->notesInternes()->create([
            'auteur_id' => Auth::id(),
            'contenu' => $donnees['contenu'],
        ]);

        $this->auditeur->enregistrer('note_interne', $ecole, ['note' => Str::limit($note->contenu, 120)]);

        return back()->with('success', 'Note ajoutée.');
    }

    public function supprimerNote(Ecole $ecole, NoteInterne $note): RedirectResponse
    {
        abort_unless($note->ecole_id === $ecole->id, 404);

        $note->delete();

        return back()->with('success', 'Note supprimée.');
    }

    public function demarrerSupport(Request $request, Ecole $ecole, AccesSupport $support): RedirectResponse
    {
        $donnees = $request->validate(['motif' => ['required', 'string', 'min:10', 'max:255']], [
            'motif.required' => 'Indiquez pourquoi vous accédez à cette école.',
            'motif.min' => 'Le motif doit être un peu plus précis (10 caractères minimum).',
        ]);

        $directeur = $ecole->directeur();

        if (! $directeur) {
            return back()->with('error', "Cette école n'a pas de compte administrateur : le mode support est impossible.");
        }

        $support->demarrer($ecole, $directeur, Auth::user(), $donnees['motif']);

        return redirect()->route('dashboard')
            ->with('success', "Mode support : vous voyez {$ecole->nom} comme {$directeur->name}, en lecture seule, pendant ".AccesSupport::DUREE_MINUTES.' minutes.');
    }
}
