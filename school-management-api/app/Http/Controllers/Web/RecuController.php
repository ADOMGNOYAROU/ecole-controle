<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Paiement;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Reçu de paiement de scolarité en PDF : téléchargé par l'école, le parent ou l'élève,
 * ou ouvert sans compte depuis le lien signé envoyé sur WhatsApp.
 */
class RecuController extends Controller
{
    public function telecharger(Paiement $paiement): Response
    {
        abort_unless($this->peutVoir(Auth::user(), $paiement), 403);

        return $this->pdf($paiement);
    }

    /** Route protégée par le middleware « signed » : le lien ne peut être ni deviné ni modifié. */
    public function public(Paiement $paiement): Response
    {
        return $this->pdf($paiement);
    }

    private function peutVoir(User $user, Paiement $paiement): bool
    {
        if ($user->isAdmin()) {
            return $paiement->ecole_id === $user->ecole_id;
        }

        if ($user->role === User::ROLE_PARENT) {
            return (bool) $user->tuteur?->eleves()->where('eleves.id', $paiement->eleve_id)->exists();
        }

        if ($user->role === User::ROLE_ELEVE) {
            return $user->eleve?->id === $paiement->eleve_id;
        }

        return false;
    }

    private function pdf(Paiement $paiement): Response
    {
        $paiement->loadMissing(['eleve.classe', 'ecole', 'anneeScolaire']);

        return Pdf::loadView('recus.paiement', ['paiement' => $paiement])
            ->setPaper('a5', 'landscape')
            ->stream($paiement->numeroRecu().'.pdf');
    }
}
