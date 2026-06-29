@extends('layouts.app')

@section('title', 'Années scolaires')

@section('content')
<h1 class="page-title mb-4">Années scolaires & trimestres</h1>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
    <div class="card p-5">
        <h2 class="font-semibold text-slate-900 mb-4">Années scolaires</h2>
        <table class="data-table mb-4">
            <thead><tr><th>Libellé</th><th>Période</th><th>Active</th><th></th></tr></thead>
            <tbody>
                @forelse($anneesScolaires as $annee)
                    <tr>
                        <td>{{ $annee->libelle }}</td>
                        <td>{{ $annee->date_debut->format('d/m/Y') }} → {{ $annee->date_fin->format('d/m/Y') }}</td>
                        <td>@if($annee->active)<span class="badge-green">Active</span>@endif</td>
                        <td class="text-right">
                            <x-delete-button :action="route('annees-scolaires.destroy', $annee)">Supprimer</x-delete-button>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="text-center text-slate-400 py-6">Aucune année scolaire.</td></tr>
                @endforelse
            </tbody>
        </table>

        <form method="POST" action="{{ route('annees-scolaires.store') }}" class="grid grid-cols-2 gap-3">
            @csrf
            <div class="col-span-2">
                <label class="form-label">Libellé</label>
                <input type="text" name="libelle" class="form-input" placeholder="2026-2027" required>
            </div>
            <div>
                <label class="form-label">Début</label>
                <input type="date" name="date_debut" class="form-input" required>
            </div>
            <div>
                <label class="form-label">Fin</label>
                <input type="date" name="date_fin" class="form-input" required>
            </div>
            <div class="col-span-2 flex items-center gap-2">
                <input type="checkbox" name="active" value="1" id="active">
                <label for="active" class="text-sm">Définir comme année active</label>
            </div>
            <div class="col-span-2">
                <button type="submit" class="btn-primary">Ajouter</button>
            </div>
        </form>
    </div>

    <div class="card p-5">
        <h2 class="font-semibold text-slate-900 mb-4">Trimestres</h2>
        <table class="data-table mb-4">
            <thead><tr><th>Nom</th><th>Année</th><th>Période</th><th></th></tr></thead>
            <tbody>
                @forelse($trimestres as $trimestre)
                    <tr>
                        <td>{{ $trimestre->nom }}</td>
                        <td>{{ $trimestre->anneeScolaire->libelle }}</td>
                        <td>{{ $trimestre->date_debut->format('d/m/Y') }} → {{ $trimestre->date_fin->format('d/m/Y') }}</td>
                        <td class="text-right">
                            <x-delete-button :action="route('trimestres.destroy', $trimestre)">Supprimer</x-delete-button>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="text-center text-slate-400 py-6">Aucun trimestre.</td></tr>
                @endforelse
            </tbody>
        </table>

        <form method="POST" action="{{ route('trimestres.store') }}" class="grid grid-cols-2 gap-3">
            @csrf
            <div class="col-span-2">
                <label class="form-label">Année scolaire</label>
                <select name="annee_scolaire_id" class="form-select" required>
                    @foreach($anneesScolaires as $annee)
                        <option value="{{ $annee->id }}">{{ $annee->libelle }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-span-2">
                <label class="form-label">Nom</label>
                <input type="text" name="nom" class="form-input" placeholder="1er trimestre" required>
            </div>
            <div>
                <label class="form-label">Ordre</label>
                <input type="number" name="ordre" min="1" max="4" class="form-input" required>
            </div>
            <div></div>
            <div>
                <label class="form-label">Début</label>
                <input type="date" name="date_debut" class="form-input" required>
            </div>
            <div>
                <label class="form-label">Fin</label>
                <input type="date" name="date_fin" class="form-input" required>
            </div>
            <div class="col-span-2">
                <button type="submit" class="btn-primary">Ajouter</button>
            </div>
        </form>
    </div>
</div>
@endsection
