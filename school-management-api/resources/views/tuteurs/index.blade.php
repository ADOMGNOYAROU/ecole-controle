@extends('layouts.app')

@section('title', 'Parents / Tuteurs')

@section('content')
<div class="flex items-center justify-between mb-4">
    <h1 class="page-title">Parents / Tuteurs</h1>
    <div class="flex items-center gap-2">
        <x-export-pdf-button :href="route('tuteurs.rapport')" />
        <a href="{{ route('tuteurs.create') }}" class="btn-primary">+ Nouveau tuteur</a>
    </div>
</div>

<div class="card overflow-hidden">
    <table class="data-table">
        <thead><tr><th>Nom</th><th>Téléphone</th><th>Email</th><th>Enfants</th><th></th></tr></thead>
        <tbody>
            @forelse($tuteurs as $tuteur)
                <tr>
                    <td>
                        <div class="flex items-center gap-3">
                            <x-avatar :name="$tuteur->nomComplet()" />
                            <span class="font-medium text-slate-900">{{ $tuteur->nomComplet() }}</span>
                        </div>
                    </td>
                    <td>{{ $tuteur->telephone }}</td>
                    <td>{{ $tuteur->email ?? '—' }}</td>
                    <td>{{ $tuteur->eleves_count }}</td>
                    <td class="text-right space-x-1.5 whitespace-nowrap">
                        <x-action-link :href="route('tuteurs.show', $tuteur)" type="view">Voir</x-action-link>
                        <x-action-link :href="route('tuteurs.edit', $tuteur)" type="edit">Modifier</x-action-link>
                    </td>
                </tr>
            @empty
                <tr><td colspan="5" class="text-center text-slate-400 py-6">Aucun tuteur.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="mt-4">{{ $tuteurs->links() }}</div>
@endsection
