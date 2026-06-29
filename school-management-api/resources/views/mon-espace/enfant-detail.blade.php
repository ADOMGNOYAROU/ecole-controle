@extends('layouts.app')

@section('title', $eleve->nomComplet())

@section('content')
<h1 class="page-title mb-4">{{ $eleve->nomComplet() }}</h1>
<p class="text-sm text-slate-500 mb-6">{{ $eleve->classe?->nom ?? 'Sans classe' }} · {{ $eleve->matricule }}</p>

<div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
    <div class="card p-5">
        <p class="text-sm text-slate-500">Moyenne {{ $trimestre?->nom ?? '' }}</p>
        <p class="text-2xl font-semibold">{{ $donnees['moyenne_generale'] ?? '—' }}{{ $donnees['moyenne_generale'] !== null ? '/20' : '' }}</p>
    </div>
    <div class="card p-5">
        <p class="text-sm text-slate-500">Taux de présence</p>
        <p class="text-2xl font-semibold">{{ $tauxPresence !== null ? $tauxPresence.'%' : '—' }}</p>
    </div>
    <div class="card p-5">
        @if($trimestre)
            <a href="{{ route('bulletins.show', ['eleve' => $eleve, 'trimestre' => $trimestre]) }}" class="btn-secondary w-full justify-center">Télécharger le bulletin</a>
        @endif
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
    <div class="card p-5">
        <h2 class="font-semibold text-slate-900 mb-3">Notes par matière</h2>
        <table class="data-table">
            <thead><tr><th>Matière</th><th>Moyenne</th></tr></thead>
            <tbody>
                @forelse($donnees['matieres'] ?? [] as $entry)
                    <tr><td>{{ $entry['matiere']->nom }}</td><td>{{ $entry['moyenne'] }}/20</td></tr>
                @empty
                    <tr><td colspan="2" class="text-center text-slate-400 py-6">Aucune note.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="card p-5">
        <h2 class="font-semibold text-slate-900 mb-3">Présences récentes</h2>
        <table class="data-table">
            <thead><tr><th>Date</th><th>Statut</th></tr></thead>
            <tbody>
                @forelse($presences as $presence)
                    <tr><td>{{ $presence->date->format('d/m/Y') }}</td><td><span class="badge-{{ match($presence->statut) { 'present' => 'green', 'retard' => 'yellow', default => 'red' } }}">{{ $presence->statut }}</span></td></tr>
                @empty
                    <tr><td colspan="2" class="text-center text-slate-400 py-6">Aucune présence.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="card p-5 mt-6">
    <h2 class="font-semibold text-slate-900 mb-3">Paiements de scolarité</h2>
    <table class="data-table">
        <thead><tr><th>Type</th><th>Montant</th><th>Payé</th><th>Statut</th></tr></thead>
        <tbody>
            @forelse($paiements as $paiement)
                <tr>
                    <td>{{ $paiement->type }}</td>
                    <td>{{ number_format($paiement->montant, 0, ',', ' ') }} F</td>
                    <td>{{ number_format($paiement->montant_paye, 0, ',', ' ') }} F</td>
                    <td><span class="badge-{{ match($paiement->statut) { 'paye' => 'green', 'partiel' => 'yellow', 'retard' => 'red', default => 'slate' } }}">{{ $paiement->statut }}</span></td>
                </tr>
            @empty
                <tr><td colspan="4" class="text-center text-slate-400 py-6">Aucun paiement.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
