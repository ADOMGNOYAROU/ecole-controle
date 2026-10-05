<?php

namespace App\Http\Controllers\Web\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Ecole;
use App\Models\JournalAudit;
use App\Services\JournalAuditeur;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Journal d'audit de toute la plateforme, réservé au super administrateur.
 */
class JournalController extends Controller
{
    public function index(Request $request): View
    {
        $entrees = $this->filtrer($request)
            ->with('ecole:id,nom')
            ->latest('id')
            ->paginate(50)
            ->withQueryString();

        return view('super-admin.journal.index', [
            'entrees' => $entrees,
            'ecoles' => Ecole::orderBy('nom')->get(['id', 'nom']),
            'actions' => JournalAudit::LIBELLES_ACTIONS,
            'types' => JournalAudit::query()->whereNotNull('objet_type')->distinct()->orderBy('objet_type')->pluck('objet_type'),
        ]);
    }

    public function export(Request $request, JournalAuditeur $auditeur): StreamedResponse
    {
        $auditeur->enregistrer('export_journal', changements: array_filter($request->only(['ecole_id', 'action', 'objet_type', 'utilisateur', 'du', 'au'])));

        $requete = $this->filtrer($request)->with('ecole:id,nom')->latest('id');

        return response()->streamDownload(function () use ($requete) {
            $sortie = fopen('php://output', 'w');
            fwrite($sortie, "\xEF\xBB\xBF");
            fputcsv($sortie, ['Date', 'École', 'Utilisateur', 'Rôle', 'Action', 'Élément', 'Libellé', 'Détails', 'Adresse IP'], ';');

            $requete->chunk(500, function ($entrees) use ($sortie) {
                foreach ($entrees as $entree) {
                    fputcsv($sortie, [
                        $entree->created_at->format('d/m/Y H:i:s'),
                        $entree->ecole?->nom ?? '—',
                        $entree->user_nom ?? '—',
                        $entree->user_role ?? '—',
                        $entree->libelleAction(),
                        $entree->objet_type ? $entree->objet_type.' #'.$entree->objet_id : '—',
                        $entree->objet_libelle ?? '—',
                        $entree->changements ? json_encode($entree->changements, JSON_UNESCAPED_UNICODE) : '',
                        $entree->ip ?? '',
                    ], ';');
                }
            });

            fclose($sortie);
        }, 'journal-audit-'.now()->format('Y-m-d-His').'.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    private function filtrer(Request $request): Builder
    {
        $request->validate([
            'ecole_id' => ['nullable', 'integer'],
            'du' => ['nullable', 'date'],
            'au' => ['nullable', 'date'],
        ]);

        return JournalAudit::query()
            ->when($request->filled('ecole_id'), fn ($q) => $q->where('ecole_id', $request->integer('ecole_id')))
            ->when($request->filled('action'), fn ($q) => $q->where('action', $request->input('action')))
            ->when($request->filled('objet_type'), fn ($q) => $q->where('objet_type', $request->input('objet_type')))
            ->when($request->filled('utilisateur'), fn ($q) => $q->where('user_nom', 'like', '%'.$request->input('utilisateur').'%'))
            ->when($request->filled('du'), fn ($q) => $q->where('created_at', '>=', $request->date('du')->startOfDay()))
            ->when($request->filled('au'), fn ($q) => $q->where('created_at', '<=', $request->date('au')->endOfDay()));
    }
}
