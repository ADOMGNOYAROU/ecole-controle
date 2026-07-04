@extends('layouts.app')

@section('title', 'Progression — '.$eleve->nomComplet())

@section('content')
<div class="flex items-center justify-between mb-5">
    <div>
        <h1 class="page-title">Progression de {{ $eleve->nomComplet() }}</h1>
        <p class="text-sm text-slate-500">{{ $eleve->matricule }} · {{ $eleve->classe?->nom ?? 'Sans classe' }}</p>
    </div>
    <a href="{{ route('eleves.show', $eleve) }}" class="btn-secondary">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
        Retour à la fiche
    </a>
</div>

@if($risque['en_risque'])
<div class="mb-5 rounded-xl border border-red-200 bg-red-50 p-4 flex gap-4">
    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-red-100 text-red-600">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
        </svg>
    </div>
    <div>
        <p class="font-semibold text-red-800">
            @if($risque['niveau'] === 'eleve') Risque d'échec élevé
            @elseif($risque['niveau'] === 'moyen') Risque d'échec modéré
            @else Surveillance recommandée
            @endif
        </p>
        <p class="text-sm text-red-700 mt-0.5">
            @if($risque['trimestres_sous_seuil'] >= 2)
                {{ $risque['trimestres_sous_seuil'] }} trimestres consécutifs avec une moyenne inférieure à {{ $risque['seuil'] }}/20.
            @else
                Moyenne actuelle ({{ $risque['derniere_moyenne'] }}/20) inférieure au seuil de passage ({{ $risque['seuil'] }}/20).
            @endif
            Un accompagnement personnalisé est recommandé.
        </p>
    </div>
</div>
@endif

{{-- Cartes de résumé --}}
<div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
    <div class="card p-5">
        <p class="text-sm text-slate-500 mb-1">Tendance générale</p>
        @php
            [$tCls, $tLabel, $tSub] = match($tendance) {
                'hausse' => ['text-green-600', '↑ En hausse', 'La moyenne progresse'],
                'baisse' => ['text-red-600', '↓ En baisse', 'La moyenne régresse'],
                default  => ['text-slate-600', '→ Stable', 'Pas d\'évolution notable'],
            };
        @endphp
        <p class="text-xl font-semibold {{ $tCls }}">{{ $tLabel }}</p>
        <p class="text-xs text-slate-500 mt-1">{{ $tSub }}</p>
    </div>

    <div class="card p-5">
        <p class="text-sm text-slate-500 mb-1">Dernière moyenne</p>
        @php
            $derniere = $moyennesParTrimestre->last();
            $moy = $derniere['moyenne'] ?? null;
            $moyCls = $moy === null ? 'text-slate-400' : ($moy >= 14 ? 'text-green-600' : ($moy >= 10 ? 'text-amber-600' : 'text-red-600'));
        @endphp
        <p class="text-2xl font-bold {{ $moyCls }}">{{ $moy !== null ? $moy.'/20' : '—' }}</p>
        <p class="text-xs text-slate-500 mt-1">{{ $derniere['label'] ?? 'Aucune donnée' }}</p>
    </div>

    <div class="card p-5">
        <p class="text-sm text-slate-500 mb-1">Présence (dernier trimestre)</p>
        @php
            $dernierTaux = $tauxPresence->last();
            $taux = $dernierTaux['taux_presence'] ?? null;
            $tauxCls = $taux === null ? 'text-slate-400' : ($taux >= 90 ? 'text-green-600' : ($taux >= 75 ? 'text-amber-600' : 'text-red-600'));
        @endphp
        <p class="text-2xl font-bold {{ $tauxCls }}">{{ $taux !== null ? $taux.'%' : '—' }}</p>
        <p class="text-xs text-slate-500 mt-1">{{ isset($dernierTaux['trimestre']) ? $dernierTaux['trimestre']->nom : '' }}</p>
    </div>
</div>

{{-- Graphique + Points forts / faibles --}}
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
    <div class="card p-5 lg:col-span-2">
        <h2 class="font-semibold text-slate-900 mb-4">Évolution de la moyenne générale</h2>
        @if($moyennesParTrimestre->count() >= 2)
            <div style="position: relative; height: 260px;">
                <canvas id="progression-chart"
                    data-labels='@json($moyennesParTrimestre->pluck('label'))'
                    data-moyennes='@json($moyennesParTrimestre->pluck('moyenne'))'
                    data-seuil='{{ \App\Services\ProgressionService::SEUIL_RISQUE }}'
                ></canvas>
            </div>
        @else
            <div class="flex items-center justify-center h-40 rounded-lg bg-slate-50 text-slate-400 text-sm">
                Minimum 2 trimestres de données nécessaires pour afficher le graphique.
            </div>
        @endif
    </div>

    <div class="space-y-4">
        <div class="card p-5">
            <h2 class="font-semibold text-slate-900 mb-3 flex items-center gap-2">
                <span class="flex h-7 w-7 items-center justify-center rounded-lg bg-green-100 text-green-600">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                </span>
                Points forts
            </h2>
            <ul class="space-y-2.5">
                @forelse($pointsForts as $item)
                    <li class="flex items-center justify-between text-sm">
                        <span class="text-slate-700">{{ $item['matiere']->nom }}</span>
                        <span class="font-semibold text-green-600">{{ $item['moyenne'] }}/20</span>
                    </li>
                @empty
                    <li class="text-slate-400 text-sm">Aucune donnée.</li>
                @endforelse
            </ul>
        </div>

        <div class="card p-5">
            <h2 class="font-semibold text-slate-900 mb-3 flex items-center gap-2">
                <span class="flex h-7 w-7 items-center justify-center rounded-lg bg-red-100 text-red-600">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 17h8m0 0V9m0 8l-8-8-4 4-6-6"/></svg>
                </span>
                À améliorer
            </h2>
            <ul class="space-y-2.5">
                @forelse($pointsFaibles as $item)
                    <li class="flex items-center justify-between text-sm">
                        <span class="text-slate-700">{{ $item['matiere']->nom }}</span>
                        <span class="font-semibold {{ $item['moyenne'] < 10 ? 'text-red-600' : 'text-amber-600' }}">{{ $item['moyenne'] }}/20</span>
                    </li>
                @empty
                    <li class="text-slate-400 text-sm">Aucune donnée.</li>
                @endforelse
            </ul>
        </div>
    </div>
</div>

{{-- Tableau complet par matière --}}
<div class="card overflow-hidden">
    <div class="p-5 pb-0 flex items-center justify-between">
        <h2 class="font-semibold text-slate-900">Moyennes par matière (toutes périodes)</h2>
        <span class="text-xs text-slate-400">{{ $moyennesParMatiere->count() }} matière(s)</span>
    </div>
    <table class="data-table">
        <thead>
            <tr>
                <th>Matière</th>
                <th class="text-center">Coeff.</th>
                <th class="text-center">Notes</th>
                <th class="text-right">Moyenne</th>
                <th>Progression</th>
            </tr>
        </thead>
        <tbody>
            @forelse($moyennesParMatiere->sortByDesc('moyenne') as $item)
                @php
                    $pct      = round(($item['moyenne'] / 20) * 100);
                    $barCls   = $item['moyenne'] >= 14 ? 'bg-green-500' : ($item['moyenne'] >= 10 ? 'bg-amber-400' : 'bg-red-500');
                    $textCls  = $item['moyenne'] >= 14 ? 'text-green-600' : ($item['moyenne'] >= 10 ? 'text-amber-600' : 'text-red-600');
                @endphp
                <tr>
                    <td class="font-medium text-slate-900">{{ $item['matiere']->nom }}</td>
                    <td class="text-center text-slate-500">{{ $item['matiere']->coefficient_defaut }}</td>
                    <td class="text-center text-slate-500">{{ $item['nombre_notes'] }}</td>
                    <td class="text-right font-semibold {{ $textCls }}">{{ $item['moyenne'] }}/20</td>
                    <td style="min-width: 140px;">
                        <div class="flex items-center gap-2">
                            <div class="h-1.5 flex-1 rounded-full bg-slate-100 overflow-hidden">
                                <div class="h-full rounded-full {{ $barCls }}" style="width: {{ $pct }}%"></div>
                            </div>
                            <span class="text-xs text-slate-400 w-8 text-right">{{ $pct }}%</span>
                        </div>
                    </td>
                </tr>
            @empty
                <tr><td colspan="5" class="text-center text-slate-400 py-6">Aucune note enregistrée.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
    const canvas = document.getElementById('progression-chart');
    if (!canvas || !window.Chart) return;

    const labels   = JSON.parse(canvas.dataset.labels);
    const moyennes = JSON.parse(canvas.dataset.moyennes).map(Number);
    const seuil    = parseFloat(canvas.dataset.seuil);

    new Chart(canvas, {
        type: 'line',
        data: {
            labels,
            datasets: [
                {
                    label: 'Moyenne générale',
                    data: moyennes,
                    borderColor: '#2647d6',
                    backgroundColor: 'rgba(38,71,214,0.07)',
                    borderWidth: 2.5,
                    pointRadius: 6,
                    pointHoverRadius: 8,
                    pointBackgroundColor: moyennes.map(v =>
                        v < seuil ? '#ef4444' : (v >= 14 ? '#16a34a' : '#d97706')
                    ),
                    pointBorderColor: '#fff',
                    pointBorderWidth: 2,
                    tension: 0.3,
                    fill: true,
                },
                {
                    label: `Seuil de passage (${seuil}/20)`,
                    data: Array(labels.length).fill(seuil),
                    borderColor: '#ef4444',
                    borderWidth: 1.5,
                    borderDash: [5, 4],
                    pointRadius: 0,
                    fill: false,
                },
            ],
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            interaction: { mode: 'index', intersect: false },
            plugins: {
                legend: { position: 'bottom', labels: { boxWidth: 10, usePointStyle: true, padding: 16 } },
                tooltip: {
                    callbacks: {
                        label: ctx => `${ctx.dataset.label}: ${ctx.parsed.y}/20`,
                    },
                },
            },
            scales: {
                y: {
                    min: 0,
                    max: 20,
                    grid: { color: '#f1f5f9' },
                    ticks: { callback: v => v + '/20', stepSize: 4 },
                },
                x: { grid: { display: false } },
            },
        },
    });
});
</script>
@endpush
@endsection
