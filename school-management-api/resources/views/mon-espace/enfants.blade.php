@extends('layouts.app')

@section('title', 'Mes enfants')

@section('content')
<h1 class="page-title mb-4">Mes enfants</h1>

<div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
    @forelse($enfants as $enfant)
        <div class="card p-5">
            <p class="font-semibold text-slate-900">{{ $enfant->nomComplet() }}</p>
            <p class="text-sm text-slate-500">{{ $enfant->classe?->nom ?? 'Sans classe' }} · {{ $enfant->matricule }}</p>
            <a href="{{ route('mes-enfants.show', $enfant) }}" class="mt-3 inline-block text-sm text-brand-600 hover:underline">Voir le détail →</a>
        </div>
    @empty
        <div class="card p-6 text-slate-400">Aucun enfant lié à votre compte.</div>
    @endforelse
</div>
@endsection
