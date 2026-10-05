<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <style>
        @page { margin: 22px 28px; }
        body { font-family: DejaVu Sans, sans-serif; font-size: 11px; color: #1f2937; }
        .entete { width: 100%; border-bottom: 2px solid #1d4a37; padding-bottom: 8px; margin-bottom: 12px; }
        .entete td { vertical-align: top; }
        .ecole { font-size: 15px; font-weight: bold; color: #1d4a37; }
        .muted { color: #6b7280; font-size: 10px; }
        .titre { text-align: right; }
        .titre .grand { font-size: 16px; font-weight: bold; letter-spacing: 1px; }
        .infos { width: 100%; margin-bottom: 12px; }
        .infos td { padding: 3px 0; }
        .montants { width: 100%; border-spacing: 0; margin-bottom: 12px; }
        .montants th, .montants td { border-bottom: 1px solid #d1d5db; padding: 6px 8px; text-align: left; }
        .montants th { background-color: #e3eee7; font-size: 10px; text-transform: uppercase; }
        .montants .n { text-align: right; }
        .total td { font-weight: bold; font-size: 12px; }
        .statut { display: inline-block; padding: 2px 8px; border-radius: 4px; font-weight: bold; }
        .paye { background-color: #e3f2e8; color: #1f7a45; }
        .reste { background-color: #fbe9e7; color: #b3261e; }
        .pied { margin-top: 18px; font-size: 9px; color: #6b7280; }
    </style>
</head>
<body>
    @php
        $eleve = $paiement->eleve;
        $ecole = $paiement->ecole;
        $f = fn ($montant) => number_format((float) $montant, 0, ',', ' ').' FCFA';
        $solde = $paiement->solde();
    @endphp

    <table class="entete">
        <tr>
            <td>
                <div class="ecole">{{ $ecole->nom }}</div>
                <div class="muted">
                    {{ collect([$ecole->adresse, $ecole->ville])->filter()->implode(', ') }}
                    @if($ecole->telephone) · Tél. {{ $ecole->telephone }} @endif
                    @if($ecole->email_contact) · {{ $ecole->email_contact }} @endif
                </div>
            </td>
            <td class="titre">
                <div class="grand">REÇU DE PAIEMENT</div>
                <div>N° {{ $paiement->numeroRecu() }}</div>
                <div class="muted">Émis le {{ now()->format('d/m/Y') }}</div>
            </td>
        </tr>
    </table>

    <table class="infos">
        <tr>
            <td><strong>Élève :</strong> {{ $eleve->nomComplet() }}</td>
            <td><strong>Matricule :</strong> {{ $eleve->matricule }}</td>
        </tr>
        <tr>
            <td><strong>Classe :</strong> {{ $eleve->classe?->nom ?? '—' }}</td>
            <td><strong>Année scolaire :</strong> {{ $paiement->anneeScolaire?->libelle ?? '—' }}</td>
        </tr>
    </table>

    <table class="montants">
        <thead>
            <tr><th>Objet</th><th>Échéance</th><th>Date du paiement</th><th class="n">Montant dû</th><th class="n">Montant payé</th></tr>
        </thead>
        <tbody>
            <tr>
                <td>{{ ucfirst($paiement->type) }}@if($paiement->commentaire) — {{ $paiement->commentaire }}@endif</td>
                <td>{{ $paiement->date_echeance?->format('d/m/Y') }}</td>
                <td>{{ $paiement->date_paiement?->format('d/m/Y') ?? '—' }}</td>
                <td class="n">{{ $f($paiement->montant) }}</td>
                <td class="n">{{ $f($paiement->montant_paye) }}</td>
            </tr>
            <tr class="total">
                <td colspan="4">Reste à payer</td>
                <td class="n">{{ $f(max(0, $solde)) }}</td>
            </tr>
        </tbody>
    </table>

    @if($solde <= 0)
        <span class="statut paye">Entièrement réglé</span>
    @else
        <span class="statut reste">Paiement partiel : reste {{ $f($solde) }}</span>
    @endif

    <p class="pied">
        Ce reçu atteste les sommes enregistrées par l'établissement à la date d'émission. Il a été produit avec École Manager.
        Pour toute question, contactez le secrétariat de l'établissement.
    </p>
</body>
</html>
