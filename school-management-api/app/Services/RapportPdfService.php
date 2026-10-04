<?php

namespace App\Services;

use Barryvdh\DomPDF\Facade\Pdf;
use Barryvdh\DomPDF\PDF as DomPdf;
use Illuminate\Support\Facades\Auth;

class RapportPdfService
{
    /**
     * Au-delà, dompdf dépasse les limites courantes d'un hébergement (128 Mo, 30 s) :
     * 500 lignes ≈ 7 s et 90 Mo. L'utilisateur est invité à filtrer le rapport.
     */
    public const MAX_LIGNES = 500;

    /** Lignes par tableau : un tableau par page évite l'explosion du temps de rendu de dompdf. */
    public const LIGNES_PAR_PAGE = 30;

    /**
     * Génère un PDF listant des lignes de données sous forme de tableau,
     * avec un en-tête École Manager + titre + sous-titre optionnel.
     *
     * @param  string[]  $colonnes  Libellés des colonnes.
     * @param  array<int, array<int, string>>  $lignes  Une ligne = un tableau de cellules, dans l'ordre des colonnes.
     */
    public function listePdf(string $titre, array $colonnes, array $lignes, ?string $sousTitre = null): DomPdf
    {
        $total = count($lignes);

        return Pdf::loadView('rapports.liste', [
            'titre' => $titre,
            'sousTitre' => $sousTitre,
            'colonnes' => $colonnes,
            'pages' => array_chunk(array_slice($lignes, 0, self::MAX_LIGNES), self::LIGNES_PAR_PAGE),
            'total' => $total,
            'tronque' => $total > self::MAX_LIGNES,
            'maxLignes' => self::MAX_LIGNES,
            'ecole' => Auth::user()?->ecole,
            'genereLe' => now(),
            'genereParNom' => Auth::user()?->name,
        ])->setPaper('a4', count($colonnes) > 5 ? 'landscape' : 'portrait');
    }
}
