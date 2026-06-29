@extends('layouts.app')

@section('title', 'Tableau de bord plateforme')

@section('content')
<h1 class="page-title mb-5">Tableau de bord plateforme</h1>

<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
    <x-stat-card label="Écoles inscrites" :value="$stats['ecoles_total']" icon="building" :trend="'+'.$stats['nouvelles_ce_mois'].' ce mois'" trendDirection="up" />
    <x-stat-card label="Écoles actives" :value="$stats['ecoles_actives']" icon="check-circle" color="green" />
    <x-stat-card label="En essai" :value="$stats['ecoles_essai']" icon="wallet" color="amber" />
    <x-stat-card label="Suspendues" :value="$stats['ecoles_suspendues']" icon="users" color="red" />
</div>

<div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
    <div class="card p-5">
        <p class="text-sm text-slate-500">MRR estimé (abonnements actifs)</p>
        <p class="text-2xl font-semibold text-brand-600">{{ number_format($mrrEstime, 0, ',', ' ') }} FCFA / mois</p>
    </div>
    <div class="card p-5">
        <p class="text-sm text-slate-500">Revenu encaissé ce mois</p>
        <p class="text-2xl font-semibold text-green-600">{{ number_format($revenuCeMois, 0, ',', ' ') }} FCFA</p>
    </div>
    <div class="card p-5">
        <p class="text-sm text-slate-500">Factures en attente</p>
        <p class="text-2xl font-semibold {{ $facturesEnRetard > 0 ? 'text-red-600' : 'text-slate-900' }}">
            {{ $facturesEnAttente }} <span class="text-sm font-normal text-slate-500">({{ $facturesEnRetard }} en retard)</span>
        </p>
        <p class="text-xs text-slate-400 mt-1">{{ number_format($montantEnAttente, 0, ',', ' ') }} FCFA à encaisser</p>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
    <div class="card overflow-hidden">
        <x-card-header icon="building" title="Dernières écoles inscrites" />
        <table class="data-table">
            <thead><tr><th>École</th><th>Statut</th><th>Utilisateurs</th><th></th></tr></thead>
            <tbody>
                @forelse($dernieresEcoles as $ecole)
                    <tr>
                        <td class="font-medium text-slate-900">{{ $ecole->nom }}</td>
                        <td><span class="badge-{{ match($ecole->statut) { 'actif' => 'green', 'suspendu' => 'red', 'expire' => 'yellow', default => 'slate' } }}">{{ $ecole->statut }}</span></td>
                        <td>{{ $ecole->users_count }}</td>
                        <td class="text-right"><x-action-link :href="route('super-admin.ecoles.show', $ecole)" type="view">Voir</x-action-link></td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="text-center text-slate-400 py-6">Aucune école.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="card overflow-hidden">
        <x-card-header icon="receipt" title="Factures à traiter en priorité" />
        <table class="data-table">
            <thead><tr><th>École</th><th>Montant</th><th>Échéance</th><th></th></tr></thead>
            <tbody>
                @forelse($facturesUrgentes as $facture)
                    <tr>
                        <td class="font-medium text-slate-900">{{ $facture->ecole->nom }}</td>
                        <td>{{ number_format($facture->montant, 0, ',', ' ') }} FCFA</td>
                        <td class="{{ $facture->date_echeance->isPast() ? 'text-red-600 font-medium' : 'text-slate-500' }}">{{ $facture->date_echeance->format('d/m/Y') }}</td>
                        <td class="text-right"><x-action-link :href="route('super-admin.factures.index')" type="view">Voir</x-action-link></td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="text-center text-slate-400 py-6">Aucune facture en attente.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
