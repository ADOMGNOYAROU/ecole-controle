<?php

namespace App\Services;

use Barryvdh\DomPDF\Facade\Pdf;
use Barryvdh\DomPDF\PDF as DomPdf;
use Illuminate\Support\Facades\Auth;

class RapportPdfService
{
    /**
     * Génère un PDF listant des lignes de données sous forme de tableau,
     * avec un en-tête École Manager + titre + sous-titre optionnel.
     *
     * @param  string[]  $colonnes  Libellés des colonnes.
     * @param  array<int, array<int, string>>  $lignes  Une ligne = un tableau de cellules, dans l'ordre des colonnes.
     */
    public function listePdf(string $titre, array $colonnes, array $lignes, ?string $sousTitre = null): DomPdf
    {
        return Pdf::loadView('rapports.liste', [
            'titre' => $titre,
            'sousTitre' => $sousTitre,
            'colonnes' => $colonnes,
            'lignes' => $lignes,
            'ecole' => Auth::user()?->ecole,
            'genereLe' => now(),
            'genereParNom' => Auth::user()?->name,
        ])->setPaper('a4', count($colonnes) > 5 ? 'landscape' : 'portrait');
    }
}
