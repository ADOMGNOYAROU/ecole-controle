@extends('layouts.app')

@section('title', 'Tableau de bord')

@section('content')
@php
    $trendDeNombre = function (int $delta, string $suffix = ' ce mois') {
        if ($delta === 0) {
            return ['Stable', 'flat'];
        }
        return ["+{$delta}{$suffix}", 'up'];
    };
    [$trendEleves, $dirEleves] = $trendDeNombre($deltas['eleves']);
    [$trendEnseignants, $dirEnseignants] = $trendDeNombre($deltas['enseignants']);
    [$trendClasses, $dirClasses] = $trendDeNombre($deltas['classes']);
    [$trendRetard, $dirRetard] = $deltas['paiements_en_retard'] > 0
        ? ["+{$deltas['paiements_en_retard']} ce mois", 'down']
        : ['Stable', 'flat'];
@endphp

<div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
    <x-stat-card label="Élèves actifs" :value="$stats['eleves']" icon="users" color="brand"
                 :trend="$trendEleves" :trend-direction="$dirEleves" :spark="$sparklines['eleves']" />
    <x-stat-card label="Enseignants" :value="$stats['enseignants']" icon="academic-cap" color="violet"
                 :trend="$trendEnseignants" :trend-direction="$dirEnseignants" :spark="$sparklines['enseignants']" />
    <x-stat-card label="Classes" :value="$stats['classes']" icon="building" color="amber"
                 :trend="$trendClasses" :trend-direction="$dirClasses" :spark="$sparklines['classes']" />
    <x-stat-card label="Paiements en retard" :value="$stats['paiements_en_retard']" icon="currency" color="red"
                 :trend="$trendRetard" :trend-direction="$dirRetard" :spark="$sparklines['paiements_en_retard']" />
</div>

<div class="mt-6 grid grid-cols-1 gap-6 lg:grid-cols-3">
    <div class="card p-5 lg:col-span-2">
        <x-card-header title="Dernières notes saisies" icon="list">
            @if($trimestre)
                <span class="badge-slate">{{ $trimestre->nom }}</span>
            @endif
        </x-card-header>
        <table class="data-table">
            <thead><tr><th>Élève</th><th>Matière</th><th>Note</th><th>Enseignant</th><th></th></tr></thead>
            <tbody>
                @forelse($dernieresNotes as $note)
                    @php
                        $sur20 = $note->noteSur20();
                        $pillClass = match (true) {
                            $sur20 >= 14 => 'stat-pill-good',
                            $sur20 >= 10 => 'stat-pill-mid',
                            default => 'stat-pill-bad',
                        };
                    @endphp
                    <tr>
                        <td>
                            <div class="flex items-center gap-3">
                                <x-avatar :name="$note->eleve->nomComplet()" />
                                <span class="font-medium text-slate-900">{{ $note->eleve->nomComplet() }}</span>
                            </div>
                        </td>
                        <td>{{ $note->matiere->nom }}</td>
                        <td><span class="{{ $pillClass }}">{{ $sur20 }}/20</span></td>
                        <td class="text-slate-500">{{ $note->enseignant->nomComplet() }}</td>
                        <td class="text-right" x-data="{ open: false }" @click.outside="open = false">
                            <div class="relative inline-block">
                                <button @click="open = !open" class="flex h-8 w-8 items-center justify-center rounded-lg text-slate-400 hover:bg-slate-100 hover:text-slate-600">
                                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M12 6a2 2 0 110-4 2 2 0 010 4zm0 8a2 2 0 110-4 2 2 0 010 4zm0 8a2 2 0 110-4 2 2 0 010 4z"/></svg>
                                </button>
                                <div x-show="open" x-cloak class="absolute right-0 z-10 mt-1 w-36 overflow-hidden rounded-lg border border-slate-200 bg-white shadow-lg">
                                    @can('update', $note)
                                        <a href="{{ route('notes.edit', $note) }}" class="block px-3 py-2 text-sm text-slate-700 hover:bg-slate-50">Modifier</a>
                                    @endcan
                                    @can('delete', $note)
                                        <form method="POST" action="{{ route('notes.destroy', $note) }}" onsubmit="return confirm('Supprimer ?');">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="block w-full px-3 py-2 text-left text-sm text-red-600 hover:bg-red-50">Supprimer</button>
                                        </form>
                                    @endcan
                                </div>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="text-center text-slate-400 py-6">Aucune note enregistrée.</td></tr>
                @endforelse
            </tbody>
        </table>
        <div class="mt-3 text-center">
            <a href="{{ route('notes.index') }}" class="inline-flex items-center gap-1 text-sm font-medium text-brand-600 hover:underline">
                Voir toutes les notes
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            </a>
        </div>
    </div>

    <div class="space-y-6">
        <div class="card p-5">
            <x-card-header title="Taux de présence" icon="presence" color="green" />
            <p class="text-3xl font-bold text-green-600">{{ $tauxPresenceGlobal !== null ? $tauxPresenceGlobal.'%' : '—' }}</p>
            <div class="mt-3 flex items-center gap-3">
                <div class="h-2 flex-1 rounded-full bg-slate-100 overflow-hidden">
                    <div class="h-full rounded-full bg-gradient-to-r from-green-400 to-emerald-600" style="width: {{ $tauxPresenceGlobal ?? 0 }}%"></div>
                </div>
                <span class="text-xs font-semibold text-slate-500 w-12 text-right">{{ $tauxPresenceGlobal !== null ? $tauxPresenceGlobal.'%' : '—' }}</span>
            </div>
            <p class="text-sm text-slate-500 mt-2">Sur le trimestre en cours</p>
        </div>

        <div class="card p-5">
            <x-card-header title="Annonces récentes" icon="megaphone" color="amber" />
            <ul class="space-y-3">
                @forelse($annonces as $annonce)
                    <li class="flex gap-2.5 text-sm">
                        <span class="mt-1.5 h-1.5 w-1.5 shrink-0 rounded-full bg-amber-500"></span>
                        <div>
                            <p class="font-medium text-slate-800">{{ $annonce->titre }}</p>
                            <p class="text-slate-500">{{ $annonce->date_publication->format('d/m/Y') }} — {{ $annonce->auteur->name }}</p>
                        </div>
                    </li>
                @empty
                    <li class="text-sm text-slate-400">Aucune annonce.</li>
                @endforelse
            </ul>
            <div class="mt-3 text-center">
                <a href="{{ route('annonces.index') }}" class="inline-flex items-center gap-1 text-sm font-medium text-brand-600 hover:underline">
                    Voir toutes les annonces
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                </a>
            </div>
        </div>
    </div>
</div>

<div class="mt-6 grid grid-cols-1 gap-6 lg:grid-cols-3">
    <div class="card p-5 lg:col-span-2">
        <x-card-header title="Aperçu rapide" icon="chart" />
        <p class="text-xs text-slate-400 -mt-3 mb-3">Inscriptions, notes saisies et paiements — semaine par semaine, {{ now()->locale('fr')->isoFormat('MMMM YYYY') }}</p>
        <div class="relative" style="height: 260px;">
            <canvas id="apercu-rapide-chart"
                    data-labels='@json($apercuRapide['labels'])'
                    data-inscriptions='@json($apercuRapide['inscriptions'])'
                    data-notes='@json($apercuRapide['notesSaisies'])'
                    data-paiements='@json($apercuRapide['paiements'])'></canvas>
        </div>
    </div>

    <div class="card p-5">
        <x-card-header title="Prochaines échéances" icon="list" color="violet" />
        <ul class="space-y-3">
            @forelse($prochainesEcheances as $echeance)
                <li class="flex items-center gap-3">
                    <div class="flex h-12 w-12 shrink-0 flex-col items-center justify-center rounded-lg border border-violet-200 bg-violet-50 text-violet-700">
                        <span class="text-[10px] font-bold uppercase leading-none">{{ $echeance['date']->locale('fr')->isoFormat('MMM') }}</span>
                        <span class="text-base font-bold leading-tight">{{ $echeance['date']->format('d') }}</span>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-slate-800">{{ $echeance['titre'] }}</p>
                        <p class="text-xs text-slate-500">{{ $echeance['date']->locale('fr')->isoFormat('dddd D MMMM YYYY') }}</p>
                    </div>
                </li>
            @empty
                <li class="text-sm text-slate-400">Aucune échéance à venir.</li>
            @endforelse
        </ul>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
    const canvas = document.getElementById('apercu-rapide-chart');
    if (!canvas || !window.Chart) return;

    new Chart(canvas, {
        type: 'line',
        data: {
            labels: JSON.parse(canvas.dataset.labels),
            datasets: [
                {
                    label: 'Inscriptions',
                    data: JSON.parse(canvas.dataset.inscriptions),
                    borderColor: '#2647d6',
                    backgroundColor: 'rgba(38, 71, 214, 0.08)',
                    tension: 0.35,
                    fill: true,
                },
                {
                    label: 'Notes saisies',
                    data: JSON.parse(canvas.dataset.notes),
                    borderColor: '#7c3aed',
                    backgroundColor: 'rgba(124, 58, 237, 0.06)',
                    tension: 0.35,
                    fill: true,
                },
                {
                    label: 'Paiements',
                    data: JSON.parse(canvas.dataset.paiements),
                    borderColor: '#d97706',
                    backgroundColor: 'rgba(217, 119, 6, 0.06)',
                    tension: 0.35,
                    fill: true,
                },
            ],
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            interaction: { mode: 'index', intersect: false },
            plugins: { legend: { position: 'bottom', labels: { boxWidth: 10, usePointStyle: true } } },
            scales: {
                y: { beginAtZero: true, ticks: { precision: 0 }, grid: { color: '#f1f5f9' } },
                x: { grid: { display: false } },
            },
        },
    });
});
</script>
@endpush
@endsection
