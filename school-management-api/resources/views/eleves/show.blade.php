@extends('layouts.app')

@section('title', $eleve->nomComplet())

@section('content')
<div class="flex items-center justify-between mb-4">
    <div>
        <h1 class="page-title">{{ $eleve->nomComplet() }}</h1>
        <p class="text-sm text-slate-500">{{ $eleve->matricule }} · {{ $eleve->classe?->nom ?? 'Sans classe' }}</p>
    </div>
    @can('update', $eleve)
        <a href="{{ route('eleves.edit', $eleve) }}" class="btn-secondary"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>Modifier</a>
    @endcan
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <div class="card p-5">
        <h2 class="font-semibold text-slate-900 mb-3">Informations</h2>
        <dl class="space-y-2 text-sm">
            <div class="flex justify-between"><dt class="text-slate-500">Sexe</dt><dd>{{ $eleve->sexe === 'M' ? 'Masculin' : 'Féminin' }}</dd></div>
            <div class="flex justify-between"><dt class="text-slate-500">Naissance</dt><dd>{{ $eleve->date_naissance->format('d/m/Y') }}</dd></div>
            <div class="flex justify-between"><dt class="text-slate-500">Email</dt><dd>{{ $eleve->email ?? '—' }}</dd></div>
            <div class="flex justify-between"><dt class="text-slate-500">Téléphone</dt><dd>{{ $eleve->telephone ?? '—' }}</dd></div>
            <div class="flex justify-between"><dt class="text-slate-500">Statut</dt><dd><span class="badge-{{ $eleve->statut === 'actif' ? 'green' : 'slate' }}">{{ $eleve->statut }}</span></dd></div>
        </dl>

        <h3 class="font-semibold text-slate-900 mt-5 mb-2">Tuteurs / Parents</h3>
        <ul class="text-sm space-y-1">
            @forelse($eleve->tuteurs as $tuteur)
                <li>{{ $tuteur->nomComplet() }} ({{ $tuteur->pivot->lien_parente }}) — {{ $tuteur->telephone }}</li>
            @empty
                <li class="text-slate-400">Aucun tuteur enregistré.</li>
            @endforelse
        </ul>
    </div>

    <div class="card p-5 lg:col-span-2">
        <div class="flex items-center justify-between mb-4">
            <h2 class="font-semibold text-slate-900">Moyenne {{ $trimestre?->nom ?? '' }}</h2>
            <span class="text-2xl font-semibold text-brand-600">{{ $moyenne !== null ? $moyenne.'/20' : '—' }}</span>
        </div>
        <p class="text-sm text-slate-500 mb-4">Taux de présence : {{ $tauxPresence !== null ? $tauxPresence.'%' : '—' }}</p>

        <table class="data-table">
            <thead><tr><th>Matière</th><th>Type</th><th>Note</th><th>Date</th></tr></thead>
            <tbody>
                @forelse($dernieresNotes as $note)
                    <tr>
                        <td>{{ $note->matiere->nom }}</td>
                        <td>{{ $note->type }}</td>
                        <td>{{ $note->noteSur20() }}/20</td>
                        <td>{{ $note->date_evaluation->format('d/m/Y') }}</td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="text-center text-slate-400 py-6">Aucune note.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
