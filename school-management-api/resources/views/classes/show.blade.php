@extends('layouts.app')

@section('title', $classe->nom)

@section('content')
<div class="flex items-center justify-between mb-4">
    <div>
        <h1 class="page-title">{{ $classe->nom }}</h1>
        <p class="text-sm text-slate-500">{{ $classe->niveau }} · {{ $classe->anneeScolaire->libelle }} · Prof. principal : {{ $classe->enseignantPrincipal?->nomComplet() ?? '—' }}</p>
    </div>
    <div class="flex gap-2">
        <a href="{{ route('emploi-du-temps.index', $classe) }}" class="btn-secondary">Emploi du temps</a>
        @can('update', $classe)
            <a href="{{ route('classes.edit', $classe) }}" class="btn-secondary"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>Modifier</a>
        @endcan
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
    <div class="card p-5">
        <h2 class="font-semibold text-slate-900 mb-4">Élèves ({{ $classe->eleves->count() }})</h2>
        <table class="data-table">
            <thead><tr><th>Matricule</th><th>Nom</th><th>Statut</th></tr></thead>
            <tbody>
                @forelse($classe->eleves as $eleve)
                    <tr>
                        <td>{{ $eleve->matricule }}</td>
                        <td><a href="{{ route('eleves.show', $eleve) }}" class="text-brand-600 hover:underline">{{ $eleve->nomComplet() }}</a></td>
                        <td><span class="badge-{{ $eleve->statut === 'actif' ? 'green' : 'slate' }}">{{ $eleve->statut }}</span></td>
                    </tr>
                @empty
                    <tr><td colspan="3" class="text-center text-slate-400 py-6">Aucun élève.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="card p-5">
        <h2 class="font-semibold text-slate-900 mb-4">Matières & enseignants</h2>
        <table class="data-table">
            <thead><tr><th>Matière</th><th>Enseignant</th></tr></thead>
            <tbody>
                @forelse($classe->matieres as $matiere)
                    <tr>
                        <td>{{ $matiere->nom }}</td>
                        <td>{{ \App\Models\Enseignant::find($matiere->pivot->enseignant_id)?->nomComplet() ?? '—' }}</td>
                    </tr>
                @empty
                    <tr><td colspan="2" class="text-center text-slate-400 py-6">Aucune matière affectée.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
