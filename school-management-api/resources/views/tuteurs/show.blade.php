@extends('layouts.app')

@section('title', $tuteur->nomComplet())

@section('content')
<div class="flex items-center justify-between mb-4">
    <div>
        <h1 class="page-title">{{ $tuteur->nomComplet() }}</h1>
        <p class="text-sm text-slate-500">{{ $tuteur->telephone }} · {{ $tuteur->email ?? 'sans email' }}</p>
    </div>
    <a href="{{ route('tuteurs.edit', $tuteur) }}" class="btn-secondary"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>Modifier</a>
</div>

<div class="card p-5">
    <h2 class="font-semibold text-slate-900 mb-3">Enfants</h2>
    <table class="data-table">
        <thead><tr><th>Nom</th><th>Classe</th><th>Lien</th></tr></thead>
        <tbody>
            @forelse($tuteur->eleves as $eleve)
                <tr>
                    <td><a href="{{ route('eleves.show', $eleve) }}" class="text-brand-600 hover:underline">{{ $eleve->nomComplet() }}</a></td>
                    <td>{{ $eleve->classe?->nom ?? '—' }}</td>
                    <td>{{ $eleve->pivot->lien_parente }}</td>
                </tr>
            @empty
                <tr><td colspan="3" class="text-center text-slate-400 py-6">Aucun enfant associé.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
