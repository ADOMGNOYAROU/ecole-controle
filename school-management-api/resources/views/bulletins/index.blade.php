@extends('layouts.app')

@section('title', 'Bulletins')

@section('content')
<h1 class="page-title mb-4">Génération des bulletins</h1>

<div class="card p-6 max-w-xl mb-6">
    <p class="text-sm text-slate-600 mb-4">Génère les bulletins PDF de tous les élèves actifs d'une classe pour un trimestre donné, avec calcul automatique de la moyenne générale et du rang.</p>

    <div class="space-y-4">
        @foreach($classes as $classe)
            <form method="POST" class="flex items-center justify-between gap-3"
                  data-url-template="{{ route('bulletins.generer', ['classe' => $classe, 'trimestre' => '__TRIMESTRE__']) }}"
                  onsubmit="this.action = this.dataset.urlTemplate.replace('__TRIMESTRE__', this.trimestre_id.value)">
                @csrf
                <span class="font-medium text-slate-800">{{ $classe->nom }}</span>
                <select name="trimestre_id" class="form-select w-48">
                    @foreach($trimestres as $trimestre)
                        <option value="{{ $trimestre->id }}" @selected($trimestre->id === ($trimestreActuel?->id))>{{ $trimestre->nom }}</option>
                    @endforeach
                </select>
                <button type="submit" class="btn-primary">Générer</button>
            </form>
        @endforeach
    </div>
</div>

<div class="flex items-center justify-between mb-3">
    <h2 class="page-title text-lg">Bulletins générés</h2>
</div>

<form method="GET" class="filter-bar mb-4">
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
        <label class="form-label">Trimestre</label>
        <select name="trimestre_id" class="form-select">
            <option value="">Tous</option>
            @foreach($trimestres as $trimestre)
                <option value="{{ $trimestre->id }}" @selected(request('trimestre_id') == $trimestre->id)>{{ $trimestre->nom }}</option>
            @endforeach
        </select>
    </div>
    <button type="submit" class="btn-secondary">Filtrer</button>
</form>

<div class="card overflow-hidden">
    <table class="data-table">
        <thead><tr><th>Élève</th><th>Classe</th><th>Trimestre</th><th>Moyenne</th><th>Rang</th><th>Généré le</th><th></th></tr></thead>
        <tbody>
            @forelse($bulletins as $bulletin)
                <tr>
                    <td>
                        <div class="flex items-center gap-3">
                            <x-avatar :name="$bulletin->eleve->nomComplet()" />
                            <span class="font-medium text-slate-900">{{ $bulletin->eleve->nomComplet() }}</span>
                        </div>
                    </td>
                    <td>{{ $bulletin->eleve->classe?->nom ?? '—' }}</td>
                    <td>{{ $bulletin->trimestre->nom }}</td>
                    <td>{{ $bulletin->moyenne_generale !== null ? $bulletin->moyenne_generale.'/20' : '—' }}</td>
                    <td>{{ $bulletin->rang ?? '—' }}</td>
                    <td class="text-slate-500">{{ $bulletin->genere_le->format('d/m/Y à H:i') }}</td>
                    <td class="text-right whitespace-nowrap">
                        <x-action-link :href="route('bulletins.show', ['eleve' => $bulletin->eleve, 'trimestre' => $bulletin->trimestre])" type="view" target="_blank">Voir le PDF</x-action-link>
                    </td>
                </tr>
            @empty
                <tr><td colspan="7" class="text-center text-slate-400 py-6">Aucun bulletin généré pour le moment.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="mt-4">{{ $bulletins->links() }}</div>
@endsection
