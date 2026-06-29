@extends('layouts.app')

@section('title', 'Élèves')

@section('content')
<div class="flex items-center justify-between mb-4">
    <h1 class="page-title">Élèves</h1>
    <div class="flex items-center gap-2">
        <x-export-pdf-button :href="route('eleves.rapport', request()->query())" />
        @can('create', \App\Models\Eleve::class)
            <a href="{{ route('eleves.create') }}" class="btn-primary">+ Nouvel élève</a>
        @endcan
    </div>
</div>

<form method="GET" class="card p-4 mb-4 flex flex-wrap gap-3 items-end">
    <div>
        <label class="form-label">Recherche</label>
        <input type="text" name="recherche" value="{{ request('recherche') }}" class="form-input" placeholder="Nom, prénom, matricule">
    </div>
    <div>
        <label class="form-label">Classe</label>
        <select name="classe_id" class="form-select">
            <option value="">Toutes</option>
            @foreach($classes as $classe)
                <option value="{{ $classe->id }}" @selected(request('classe_id') == $classe->id)>{{ $classe->nom }}</option>
            @endforeach
        </select>
    </div>
    <button type="submit" class="btn-secondary">Filtrer</button>
</form>

<div class="card overflow-hidden">
    <table class="data-table">
        <thead><tr><th>Matricule</th><th>Nom</th><th>Classe</th><th>Statut</th><th></th></tr></thead>
        <tbody>
            @forelse($eleves as $eleve)
                <tr>
                    <td class="text-slate-500">{{ $eleve->matricule }}</td>
                    <td>
                        <div class="flex items-center gap-3">
                            <x-avatar :name="$eleve->nomComplet()" />
                            <span class="font-medium text-slate-900">{{ $eleve->nomComplet() }}</span>
                        </div>
                    </td>
                    <td>{{ $eleve->classe?->nom ?? '—' }}</td>
                    <td><span class="badge-{{ $eleve->statut === 'actif' ? 'green' : 'slate' }}">{{ $eleve->statut }}</span></td>
                    <td class="text-right space-x-1.5 whitespace-nowrap">
                        <x-action-link :href="route('eleves.show', $eleve)" type="view">Voir</x-action-link>
                        @can('update', $eleve)
                            <x-action-link :href="route('eleves.edit', $eleve)" type="edit">Modifier</x-action-link>
                        @endcan
                    </td>
                </tr>
            @empty
                <tr><td colspan="5" class="text-center text-slate-400 py-6">Aucun élève trouvé.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="mt-4">{{ $eleves->links() }}</div>
@endsection
