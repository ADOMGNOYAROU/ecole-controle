@extends('layouts.app')

@section('title', 'Tableau de bord')

@section('content')
<div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
    @forelse($enfants as $enfant)
        <div class="card-hover p-5">
            <div class="flex items-center gap-3">
                <x-avatar :name="$enfant->nomComplet()" class="h-11 w-11 text-sm" />
                <div>
                    <p class="font-semibold text-slate-900">{{ $enfant->nomComplet() }}</p>
                    <p class="text-sm text-slate-500">{{ $enfant->classe?->nom ?? 'Sans classe' }}</p>
                </div>
            </div>
            <div class="mt-4 grid grid-cols-2 gap-3 text-sm">
                <div class="rounded-lg bg-slate-50 p-2.5">
                    <p class="text-slate-500 flex items-center gap-1.5">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                        Moyenne
                    </p>
                    <p class="font-semibold text-slate-900 mt-0.5">{{ $enfant->moyenne_courante !== null ? $enfant->moyenne_courante.'/20' : '—' }}</p>
                </div>
                <div class="rounded-lg bg-slate-50 p-2.5">
                    <p class="text-slate-500 flex items-center gap-1.5">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        Présence
                    </p>
                    <p class="font-semibold text-slate-900 mt-0.5">{{ $enfant->taux_presence_courant !== null ? $enfant->taux_presence_courant.'%' : '—' }}</p>
                </div>
            </div>
            <a href="{{ route('mes-enfants.show', $enfant) }}" class="mt-4 inline-flex items-center gap-1 text-sm font-medium text-brand-600 hover:underline">
                Voir le détail
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
            </a>
        </div>
    @empty
        <div class="card p-5 text-slate-400">Aucun enfant lié à votre compte.</div>
    @endforelse
</div>

<div class="card p-5 mt-6">
    <x-card-header title="Annonces récentes" icon="megaphone" color="amber" />
    <ul class="space-y-3">
        @forelse($annonces as $annonce)
            <li class="text-sm">
                <p class="font-medium text-slate-800">{{ $annonce->titre }}</p>
                <p class="text-slate-500">{{ $annonce->date_publication->format('d/m/Y') }}</p>
            </li>
        @empty
            <li class="text-sm text-slate-400">Aucune annonce.</li>
        @endforelse
    </ul>
</div>
@endsection
