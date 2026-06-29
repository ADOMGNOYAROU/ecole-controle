<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; color: #1f2937; }
        .header { text-align: center; margin-bottom: 20px; }
        .header h1 { font-size: 18px; margin: 0; }
        .header p { margin: 2px 0; color: #6b7280; }
        .info-table { width: 100%; margin-bottom: 16px; }
        .info-table td { padding: 4px 0; }
        table.grades { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        table.grades th, table.grades td { border: 1px solid #d1d5db; padding: 6px 8px; text-align: left; }
        table.grades th { background-color: #eef4ff; }
        .summary { width: 100%; margin-top: 10px; }
        .summary td { padding: 6px 8px; }
        .summary .label { color: #6b7280; }
        .summary .value { font-weight: bold; font-size: 14px; }
        .footer { margin-top: 40px; text-align: right; font-size: 11px; color: #6b7280; }
    </style>
</head>
<body>
    <div class="header">
        <h1>Bulletin scolaire — {{ $trimestre->nom }}</h1>
        <p>{{ $trimestre->anneeScolaire->libelle }}</p>
    </div>

    <table class="info-table">
        <tr>
            <td><strong>Élève :</strong> {{ $eleve->nomComplet() }}</td>
            <td><strong>Matricule :</strong> {{ $eleve->matricule }}</td>
        </tr>
        <tr>
            <td><strong>Classe :</strong> {{ $eleve->classe?->nom }}</td>
            <td><strong>Taux de présence :</strong> {{ $tauxPresence !== null ? $tauxPresence.'%' : 'N/A' }}</td>
        </tr>
    </table>

    <table class="grades">
        <thead>
            <tr>
                <th>Matière</th>
                <th>Moyenne /20</th>
                <th>Coefficient</th>
            </tr>
        </thead>
        <tbody>
            @forelse($matieres as $entry)
                <tr>
                    <td>{{ $entry['matiere']->nom }}</td>
                    <td>{{ $entry['moyenne'] }}</td>
                    <td>{{ $entry['matiere']->coefficient_defaut }}</td>
                </tr>
            @empty
                <tr><td colspan="3">Aucune note enregistrée pour ce trimestre.</td></tr>
            @endforelse
        </tbody>
    </table>

    <table class="summary">
        <tr>
            <td class="label">Moyenne générale</td>
            <td class="value">{{ $moyenneGenerale !== null ? $moyenneGenerale.'/20' : 'N/A' }}</td>
            <td class="label">Rang</td>
            <td class="value">{{ $rang ?? 'N/A' }}</td>
            <td class="label">Appréciation</td>
            <td class="value">{{ $appreciation }}</td>
        </tr>
    </table>

    <div class="footer">
        Bulletin généré le {{ now()->format('d/m/Y à H:i') }}
    </div>
</body>
</html>
