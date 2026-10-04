<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 11px; color: #1f2937; }
        .header { text-align: center; margin-bottom: 18px; border-bottom: 2px solid #4338ca; padding-bottom: 10px; }
        .header h1 { font-size: 17px; margin: 0; color: #312e81; }
        .header p { margin: 2px 0; color: #6b7280; font-size: 11px; }
        .sous-titre { text-align: center; margin-bottom: 14px; font-size: 12px; color: #374151; font-weight: bold; }
        .avertissement { margin-bottom: 12px; padding: 6px 8px; border: 1px solid #f59e0b; background-color: #fffbeb; color: #92400e; font-size: 10px; }
        /* Styles volontairement simples : dompdf ralentit fortement avec border-collapse et les sélecteurs complexes */
        table.liste { width: 100%; border-spacing: 0; }
        table.liste th, table.liste td { border-bottom: 1px solid #d1d5db; padding: 5px 7px; text-align: left; }
        table.liste th { background-color: #eef2ff; color: #312e81; font-size: 10px; text-transform: uppercase; }
        table.liste tr.paire td { background-color: #f9fafb; }
        .saut { page-break-after: always; }
        .footer { margin-top: 20px; text-align: right; font-size: 10px; color: #6b7280; }
        .vide { text-align: center; color: #9ca3af; padding: 20px 0; }
    </style>
</head>
<body>
    <div class="header">
        <h1>{{ $ecole->nom ?? 'École Manager' }}</h1>
        <p>{{ $titre }}</p>
    </div>

    @if($sousTitre)
        <div class="sous-titre">{{ $sousTitre }}</div>
    @endif

    @if($tronque)
        <div class="avertissement">
            Rapport limité aux {{ $maxLignes }} premières lignes sur {{ $total }}.
            Utilisez les filtres (classe, matière, trimestre…) pour obtenir un rapport complet.
        </div>
    @endif

    @forelse($pages as $page)
        {{-- Un tableau par page (en-tête répété) : le rendu reste rapide quel que soit le volume --}}
        <table class="liste @unless($loop->last) saut @endunless">
            <thead>
                <tr>
                    @foreach($colonnes as $colonne)
                        <th>{{ $colonne }}</th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @foreach($page as $ligne)
                    <tr @if($loop->even) class="paire" @endif>
                        @foreach($ligne as $cellule)
                            <td>{{ $cellule }}</td>
                        @endforeach
                    </tr>
                @endforeach
            </tbody>
        </table>
    @empty
        <p class="vide">Aucune donnée à afficher pour ce rapport.</p>
    @endforelse

    <div class="footer">
        {{ $total }} ligne(s) — généré le {{ $genereLe->format('d/m/Y à H:i') }} par {{ $genereParNom ?? 'système' }}
    </div>
</body>
</html>
