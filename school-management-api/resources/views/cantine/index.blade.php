@extends('layouts.app')

@section('title', 'Cantine')

@section('content')
@php
    $libelleJour = $date->isToday() ? "aujourd'hui" : 'le '.$date->format('d/m/Y');
    $filtres = array_filter(['date' => $date->isToday() ? null : $date->toDateString(), 'classe_id' => $classeId]);
@endphp

<div class="flex flex-wrap items-center justify-between gap-3 mb-4">
    <div>
        <h1 class="page-title">Cantine</h1>
        <p class="page-subtitle">Cochez un élève dès que son parent a payé : il sort de la liste. Ceux qui restent n'ont pas payé {{ $libelleJour }}.</p>
    </div>
    <a href="{{ route('cantine.parametres') }}" class="btn-secondary">Réglages : prix et élèves inscrits</a>
</div>

<form method="GET" action="{{ route('cantine.index') }}" class="card p-4 mb-4 flex flex-wrap items-end gap-4">
    <div>
        <label class="form-label" for="date">Jour</label>
        <input type="date" id="date" name="date" value="{{ $date->toDateString() }}" class="form-input" onchange="this.form.submit()">
    </div>
    <div>
        <label class="form-label" for="classe_id">Classe</label>
        <select id="classe_id" name="classe_id" class="form-select" onchange="this.form.submit()">
            <option value="">Toutes les classes</option>
            @foreach($classes as $classe)
                <option value="{{ $classe->id }}" @selected($classeId === $classe->id)>{{ $classe->nom }}</option>
            @endforeach
        </select>
    </div>
    @unless($date->isToday())
        <a href="{{ route('cantine.index', array_filter(['classe_id' => $classeId])) }}" class="btn-secondary">Revenir à aujourd'hui</a>
    @endunless
</form>

@if($prix === null)
    <div class="card p-4 mb-4 border-amber-300 bg-amber-50 text-amber-800">
        Le prix de la cantine n'est pas encore défini. <a href="{{ route('cantine.parametres') }}" class="font-semibold underline">Définir le prix et les élèves inscrits</a>
    </div>
@endif

<div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
    <div class="card p-5"><p class="text-sm text-slate-500">Prix d'un jour</p><p class="text-2xl font-semibold">{{ $prix !== null ? number_format($prix, 0, ',', ' ').' F' : '—' }}</p></div>
    <div class="card p-5"><p class="text-sm text-slate-500">Pas encore payé</p><p class="text-2xl font-semibold text-red-600">{{ $nonPayes->count() }}</p></div>
    <div class="card p-5"><p class="text-sm text-slate-500">Payé</p><p class="text-2xl font-semibold text-green-600">{{ $payes->count() }}</p></div>
    <div class="card p-5"><p class="text-sm text-slate-500">Encaissé {{ $libelleJour }}</p><p class="text-2xl font-semibold">{{ number_format($payes->sum('montant'), 0, ',', ' ') }} F</p></div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <div class="card overflow-hidden lg:col-span-2" x-data="{ recherche: '' }">
        <div class="p-4 border-b border-slate-100 flex flex-wrap items-center justify-between gap-3">
            <h2 class="font-semibold text-slate-900">Pas encore payé ({{ $nonPayes->count() }})</h2>
            <input type="search" x-model="recherche" placeholder="Rechercher un élève…" class="form-input max-w-xs" aria-label="Rechercher un élève">
        </div>
        <table class="data-table">
            <tbody>
                @forelse($nonPayes as $eleve)
                    <tr x-show="'{{ Str::lower(addslashes($eleve->nomComplet().' '.$eleve->matricule)) }}'.includes(recherche.toLowerCase())">
                        <td>
                            <div class="flex items-center gap-3">
                                <x-avatar :name="$eleve->nomComplet()" />
                                <div>
                                    <p class="font-medium text-slate-900">{{ $eleve->nomComplet() }}</p>
                                    <p class="text-xs text-slate-500">{{ $eleve->classe?->nom ?? 'Sans classe' }} · {{ $eleve->matricule }}</p>
                                </div>
                            </div>
                        </td>
                        <td class="text-right">
                            <form method="POST" action="{{ route('cantine.payer', [$eleve] + $filtres) }}">
                                @csrf
                                <button type="submit" class="btn-primary" @disabled($prix === null)>✓ Payé</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="2" class="text-center text-slate-500 py-8">
                            @if($payes->isNotEmpty())
                                Tout le monde a payé {{ $libelleJour }}.
                            @else
                                Aucun élève inscrit à la cantine. <a href="{{ route('cantine.parametres') }}" class="text-brand-700 font-medium underline">Inscrire des élèves</a>
                            @endif
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="card overflow-hidden">
        <div class="p-4 border-b border-slate-100">
            <h2 class="font-semibold text-slate-900">Payé {{ $libelleJour }} ({{ $payes->count() }})</h2>
        </div>
        <ul class="divide-y divide-slate-100">
            @forelse($payes as $paiement)
                <li class="px-4 py-3 flex items-center justify-between gap-3">
                    <div>
                        <p class="font-medium text-slate-900">{{ $paiement->eleve->nomComplet() }}</p>
                        <p class="text-xs text-slate-500">{{ $paiement->eleve->classe?->nom }} · {{ number_format($paiement->montant, 0, ',', ' ') }} F · {{ $paiement->created_at->format('H:i') }}</p>
                    </div>
                    <form method="POST" action="{{ route('cantine.annuler', [$paiement] + $filtres) }}" onsubmit="return confirm('Annuler ce paiement ? L\'élève reviendra dans la liste des non payés.')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="text-xs text-red-600 hover:underline">Annuler</button>
                    </form>
                </li>
            @empty
                <li class="px-4 py-6 text-center text-slate-400">Aucun paiement enregistré.</li>
            @endforelse
        </ul>
    </div>
</div>
@endsection
