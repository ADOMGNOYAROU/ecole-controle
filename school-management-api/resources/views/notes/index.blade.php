@extends('layouts.app')

@section('title', 'Notes')

@section('content')
<div class="flex items-center justify-between mb-5">
    <div>
        <h1 class="page-title">Notes</h1>
        <p class="page-subtitle">{{ $notes->total() }} note(s) enregistrée(s)</p>
    </div>
    <div class="flex gap-2">
        <x-export-pdf-button :href="route('notes.rapport', request()->query())" />
        <a href="{{ route('notes.reports') }}" class="btn-secondary">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17V7m0 10a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2h2a2 2 0 012 2m0 10a2 2 0 002 2h2a2 2 0 002-2M9 7a2 2 0 012-2h2a2 2 0 012 2m0 10V7m0 10a2 2 0 002 2h2a2 2 0 002-2V7a2 2 0 00-2-2h-2a2 2 0 00-2 2"/></svg>
            Rapports
        </a>
        <a href="{{ route('notes.bulk') }}" class="btn-secondary">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/></svg>
            Saisie groupée
        </a>
        <a href="{{ route('notes.create') }}" class="btn-primary">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Nouvelle note
        </a>
    </div>
</div>

<form method="GET" class="filter-bar">
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
        <label class="form-label">Matière</label>
        <select name="matiere_id" class="form-select">
            <option value="">Toutes</option>
            @foreach($matieres as $matiere)
                <option value="{{ $matiere->id }}" @selected(request('matiere_id') == $matiere->id)>{{ $matiere->nom }}</option>
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
    <button type="submit" class="btn-secondary">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/></svg>
        Filtrer
    </button>
</form>

<div class="card overflow-hidden">
    <table class="data-table">
        <thead><tr><th>Élève</th><th>Matière</th><th>Type</th><th>Note</th><th>Date</th><th class="text-right">Actions</th></tr></thead>
        <tbody>
            @forelse($notes as $note)
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
                    <td><span class="badge-slate capitalize">{{ $note->type }}</span></td>
                    <td><span class="{{ $pillClass }}">{{ $sur20 }}/20</span></td>
                    <td class="text-slate-500">{{ $note->date_evaluation->format('d/m/Y') }}</td>
                    <td class="text-right space-x-1.5 whitespace-nowrap">
                        @can('update', $note)
                            <a href="{{ route('notes.edit', $note) }}" class="btn-icon-edit">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                Modifier
                            </a>
                        @endcan
                        @can('delete', $note)
                            <form method="POST" action="{{ route('notes.destroy', $note) }}" class="inline" onsubmit="return confirm('Supprimer ?');">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn-icon-delete">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                    Supprimer
                                </button>
                            </form>
                        @endcan
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6">
                        <div class="empty-state">
                            <div class="empty-state-icon">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                            </div>
                            <p class="font-medium text-slate-500">Aucune note pour le moment</p>
                            <p class="text-sm">Commencez par ajouter une note ou utilisez la saisie groupée.</p>
                        </div>
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="mt-4">{{ $notes->links() }}</div>
@endsection
