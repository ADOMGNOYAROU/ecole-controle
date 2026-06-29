@extends('layouts.app')

@section('title', 'Classes')

@section('content')
<div class="flex items-center justify-between mb-4">
    <h1 class="page-title">Classes</h1>
    <div class="flex items-center gap-2">
        <x-export-pdf-button :href="route('classes.rapport')" />
        @can('create', \App\Models\Classe::class)
            <a href="{{ route('classes.create') }}" class="btn-primary">+ Nouvelle classe</a>
        @endcan
    </div>
</div>

<div class="card overflow-hidden">
    <table class="data-table">
        <thead><tr><th>Nom</th><th>Niveau</th><th>Année scolaire</th><th>Effectif</th><th>Prof. principal</th><th></th></tr></thead>
        <tbody>
            @forelse($classes as $classe)
                <tr>
                    <td class="font-medium text-slate-900">{{ $classe->nom }}</td>
                    <td>{{ $classe->niveau }}</td>
                    <td>{{ $classe->anneeScolaire->libelle }}</td>
                    <td>{{ $classe->eleves_count }}</td>
                    <td>{{ $classe->enseignantPrincipal?->nomComplet() ?? '—' }}</td>
                    <td class="text-right space-x-1.5 whitespace-nowrap">
                        <x-action-link :href="route('classes.show', $classe)" type="view">Voir</x-action-link>
                        @can('update', $classe)
                            <x-action-link :href="route('classes.edit', $classe)" type="edit">Modifier</x-action-link>
                        @endcan
                    </td>
                </tr>
            @empty
                <tr><td colspan="6" class="text-center text-slate-400 py-6">Aucune classe.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="mt-4">{{ $classes->links() }}</div>
@endsection
