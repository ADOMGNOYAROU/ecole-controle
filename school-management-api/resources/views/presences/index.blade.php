@extends('layouts.app')

@section('title', 'Présences')

@section('content')
<div class="flex items-center justify-between mb-4">
    <h1 class="page-title">Présences</h1>
    <div class="flex gap-2">
        <x-export-pdf-button :href="route('presences.rapport', request()->query())" />
        <a href="{{ route('presences.bulk') }}" class="btn-secondary">Faire l'appel</a>
        <a href="{{ route('presences.create') }}" class="btn-primary">+ Nouvelle saisie</a>
    </div>
</div>

<form method="GET" class="card p-4 mb-4 flex flex-wrap gap-3 items-end">
    <div>
        <label class="form-label">Classe</label>
        <select name="classe_id" class="form-select">
            <option value="">Toutes</option>
            @foreach($classes as $classe)
                <option value="{{ $classe->id }}" @selected(request('classe_id') == $classe->id)>{{ $classe->nom }}</option>
            @endforeach
        </select>
    </div>
    <div>
        <label class="form-label">Date</label>
        <input type="date" name="date" value="{{ request('date') }}" class="form-input">
    </div>
    <button type="submit" class="btn-secondary">Filtrer</button>
</form>

<div class="card overflow-hidden">
    <table class="data-table">
        <thead><tr><th>Élève</th><th>Classe</th><th>Date</th><th>Statut</th><th></th></tr></thead>
        <tbody>
            @forelse($presences as $presence)
                <tr>
                    <td>
                        <div class="flex items-center gap-3">
                            <x-avatar :name="$presence->eleve->nomComplet()" />
                            <span class="font-medium text-slate-900">{{ $presence->eleve->nomComplet() }}</span>
                        </div>
                    </td>
                    <td>{{ $presence->classe->nom }}</td>
                    <td class="text-slate-500">{{ $presence->date->format('d/m/Y') }}</td>
                    <td>
                        <span class="badge-{{ match($presence->statut) { 'present' => 'green', 'retard' => 'yellow', default => 'red' } }}">{{ $presence->statut }}</span>
                    </td>
                    <td class="text-right whitespace-nowrap">
                        @can('update', $presence)
                            <x-action-link :href="route('presences.edit', $presence)" type="edit">Modifier</x-action-link>
                        @endcan
                    </td>
                </tr>
            @empty
                <tr><td colspan="5" class="text-center text-slate-400 py-6">Aucune présence enregistrée.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="mt-4">{{ $presences->links() }}</div>
@endsection
