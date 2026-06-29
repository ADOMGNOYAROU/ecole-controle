@extends('layouts.app')

@section('title', 'Enseignants')

@section('content')
<div class="flex items-center justify-between mb-4">
    <h1 class="page-title">Enseignants</h1>
    <div class="flex items-center gap-2">
        <x-export-pdf-button :href="route('enseignants.rapport')" />
        <a href="{{ route('enseignants.create') }}" class="btn-primary">+ Nouvel enseignant</a>
    </div>
</div>

<div class="card overflow-hidden">
    <table class="data-table">
        <thead><tr><th>Nom</th><th>Spécialité</th><th>Classes principales</th><th></th></tr></thead>
        <tbody>
            @forelse($enseignants as $enseignant)
                <tr>
                    <td>
                        <div class="flex items-center gap-3">
                            <x-avatar :name="$enseignant->nomComplet()" />
                            <span class="font-medium text-slate-900">{{ $enseignant->nomComplet() }}</span>
                        </div>
                    </td>
                    <td>{{ $enseignant->specialite ?? '—' }}</td>
                    <td>{{ $enseignant->classes_principales_count }}</td>
                    <td class="text-right space-x-1.5 whitespace-nowrap">
                        <x-action-link :href="route('enseignants.show', $enseignant)" type="view">Voir</x-action-link>
                        <x-action-link :href="route('enseignants.edit', $enseignant)" type="edit">Modifier</x-action-link>
                    </td>
                </tr>
            @empty
                <tr><td colspan="4" class="text-center text-slate-400 py-6">Aucun enseignant.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="mt-4">{{ $enseignants->links() }}</div>
@endsection
