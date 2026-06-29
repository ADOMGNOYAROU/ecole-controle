@extends('layouts.app')

@section('title', 'Matières')

@section('content')
<div class="flex items-center justify-between mb-4">
    <h1 class="page-title">Matières</h1>
    <div class="flex items-center gap-2">
        <x-export-pdf-button :href="route('matieres.rapport')" />
        <a href="{{ route('matieres.create') }}" class="btn-primary">+ Nouvelle matière</a>
    </div>
</div>

<div class="card overflow-hidden">
    <table class="data-table">
        <thead><tr><th>Nom</th><th>Code</th><th>Coefficient</th><th></th></tr></thead>
        <tbody>
            @forelse($matieres as $matiere)
                <tr>
                    <td class="font-medium text-slate-900">{{ $matiere->nom }}</td>
                    <td>{{ $matiere->code }}</td>
                    <td>{{ $matiere->coefficient_defaut }}</td>
                    <td class="text-right space-x-1.5 whitespace-nowrap">
                        <x-action-link :href="route('matieres.edit', $matiere)" type="edit">Modifier</x-action-link>
                        <x-delete-button :action="route('matieres.destroy', $matiere)">Supprimer</x-delete-button>
                    </td>
                </tr>
            @empty
                <tr><td colspan="4" class="text-center text-slate-400 py-6">Aucune matière.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="mt-4">{{ $matieres->links() }}</div>
@endsection
