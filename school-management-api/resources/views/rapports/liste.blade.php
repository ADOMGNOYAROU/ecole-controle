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
        table.liste { width: 100%; border-collapse: collapse; }
        table.liste th, table.liste td { border: 1px solid #d1d5db; padding: 5px 7px; text-align: left; }
        table.liste th { background-color: #eef2ff; color: #312e81; font-size: 10px; text-transform: uppercase; }
        table.liste tr:nth-child(even) td { background-color: #f9fafb; }
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

    @if(count($lignes) > 0)
        <table class="liste">
            <thead>
                <tr>
                    @foreach($colonnes as $colonne)
                        <th>{{ $colonne }}</th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @foreach($lignes as $ligne)
                    <tr>
                        @foreach($ligne as $cellule)
                            <td>{{ $cellule }}</td>
                        @endforeach
                    </tr>
                @endforeach
            </tbody>
        </table>
    @else
        <p class="vide">Aucune donnée à afficher pour ce rapport.</p>
    @endif

    <div class="footer">
        {{ count($lignes) }} ligne(s) — généré le {{ $genereLe->format('d/m/Y à H:i') }} par {{ $genereParNom ?? 'système' }}
    </div>
</body>
</html>
