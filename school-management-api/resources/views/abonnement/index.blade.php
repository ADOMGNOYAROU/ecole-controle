@extends('layouts.app')

@section('title', 'Abonnement')

@section('content')
<h1 class="page-title mb-4">Abonnement</h1>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <div class="card p-6 lg:col-span-2">
        <div class="flex items-center justify-between mb-4">
            <h2 class="font-semibold text-slate-900">Plan actuel</h2>
            <span class="badge-{{ $ecole->aAccesPremium() ? 'brand' : 'slate' }}">
                {{ $ecole->aAccesPremium() ? 'Premium' : 'Gratuit' }}
            </span>
        </div>

        @if($ecole->estEnEssai())
            <p class="text-sm text-yellow-700 bg-yellow-50 rounded-lg p-3 mb-4">
                Essai Premium en cours — {{ $ecole->joursEssaiRestants() }} jour(s) restant(s).
            </p>
        @elseif($ecole->statut === 'suspendu')
            <p class="text-sm text-red-700 bg-red-50 rounded-lg p-3 mb-4">
                Accès suspendu (abonnement impayé). Souscrivez ou réglez votre facture en attente pour réactiver l'accès.
            </p>
        @elseif(! $ecole->aAccesPremium())
            <p class="text-sm text-slate-600 bg-slate-50 rounded-lg p-3 mb-4">
                Vous utilisez actuellement le plan Gratuit : gestion des classes, élèves, enseignants,
                matières, notes, présences et emploi du temps, sans limite.
            </p>
        @else
            <p class="text-sm text-green-700 bg-green-50 rounded-lg p-3 mb-4">
                Abonnement Premium actif.
            </p>
        @endif

        <div class="border border-slate-200 rounded-lg p-4 mb-4">
            <h3 class="font-semibold text-slate-900 mb-2">Premium — {{ number_format($tarif, 0, ',', ' ') }} FCFA / trimestre</h3>
            <ul class="text-sm text-slate-600 space-y-1 list-disc list-inside">
                <li>Bulletins de notes PDF avec moyenne et rang</li>
                <li>Suivi des paiements de scolarité</li>
                <li>Annonces et notifications aux parents/élèves</li>
                <li>Espaces self-service élève et parent</li>
                <li>Gestion des comptes utilisateurs (élèves, enseignants, parents)</li>
            </ul>
        </div>

        @unless($ecole->aAccesPremium())
            <form method="POST" action="{{ route('abonnement.souscrire') }}">
                @csrf
                <button type="submit" class="btn-primary">Passer au Premium</button>
            </form>
        @endunless
    </div>

    <div class="card p-6">
        <h2 class="font-semibold text-slate-900 mb-4">Factures</h2>
        <ul class="space-y-3 text-sm">
            @forelse($factures as $facture)
                <li class="border-b border-slate-100 pb-2 last:border-0">
                    <div class="flex justify-between">
                        <span>{{ number_format($facture->montant, 0, ',', ' ') }} FCFA</span>
                        <span class="badge-{{ match($facture->statut) { 'payee' => 'green', 'en_retard' => 'red', 'annulee' => 'slate', default => 'yellow' } }}">{{ $facture->statut }}</span>
                    </div>
                    <p class="text-slate-400">Échéance : {{ $facture->date_echeance->format('d/m/Y') }}</p>
                    @if($facture->statut === 'en_attente')
                        <p class="text-xs text-slate-500 mt-1">
                            Payez via Flooz/TMoney puis communiquez la référence à l'administration
                            pour activation (confirmation manuelle en attendant l'intégration automatique).
                        </p>
                    @endif
                </li>
            @empty
                <li class="text-slate-400">Aucune facture pour le moment.</li>
            @endforelse
        </ul>
    </div>
</div>
@endsection
