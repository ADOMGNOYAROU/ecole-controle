@extends('layouts.app')

@section('title', 'Tableau de bord plateforme')

@use('App\Support\WhatsApp')

@php
    $couleurSante = fn (?array $s) => match ($s['niveau'] ?? 'faible') { 'bonne' => 'green', 'moyenne' => 'yellow', default => 'red' };
    $maxRevenu = max(array_values($revenus) ?: [0]) ?: 1;
    $nomsMois = ['01' => 'janv.', '02' => 'févr.', '03' => 'mars', '04' => 'avr.', '05' => 'mai', '06' => 'juin', '07' => 'juil.', '08' => 'août', '09' => 'sept.', '10' => 'oct.', '11' => 'nov.', '12' => 'déc.'];
@endphp

@section('content')
<h1 class="page-title mb-5">Tableau de bord plateforme</h1>

<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
    <x-stat-card label="Écoles inscrites" :value="$stats['ecoles_total']" icon="building" :trend="'+'.$stats['nouvelles_ce_mois'].' ce mois'" trendDirection="up" />
    <x-stat-card label="Écoles actives" :value="$stats['ecoles_actives']" icon="check-circle" color="green" />
    <x-stat-card label="En essai" :value="$stats['ecoles_essai']" icon="wallet" color="amber" />
    <x-stat-card label="Suspendues" :value="$stats['ecoles_suspendues']" icon="users" color="red" />
</div>

<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
    <div class="card p-5">
        <p class="text-sm text-slate-500">Revenu mensuel (abonnements actifs)</p>
        <p class="text-2xl font-semibold text-brand-600">{{ number_format($mrrEstime, 0, ',', ' ') }} F <span class="text-sm font-normal text-slate-500">/ mois</span></p>
        <p class="text-xs text-slate-400 mt-1">{{ number_format($revenuCeMois, 0, ',', ' ') }} F encaissés ce mois</p>
    </div>
    <div class="card p-5">
        <p class="text-sm text-slate-500">Conversion essai → payant</p>
        <p class="text-2xl font-semibold">{{ $conversion['taux'] }} %</p>
        <p class="text-xs text-slate-400 mt-1">{{ $conversion['payantes'] }} école(s) payante(s) sur {{ $conversion['inscrites'] }} · {{ $conversion['essais_perdus'] }} essai(s) fini(s) sans paiement</p>
    </div>
    <div class="card p-5">
        <p class="text-sm text-slate-500">Paiements en ligne ce mois</p>
        <p class="text-2xl font-semibold text-green-600">{{ $payDunya['reussis'] }} <span class="text-sm font-normal text-slate-500">réussi(s)</span></p>
        <p class="text-xs mt-1 {{ $payDunya['non_finalises'] > 0 ? 'text-red-600' : 'text-slate-400' }}">{{ $payDunya['non_finalises'] }} commencé(s) mais non finalisé(s)</p>
    </div>
    <div class="card p-5">
        <p class="text-sm text-slate-500">Factures en attente</p>
        <p class="text-2xl font-semibold {{ $facturesEnRetard > 0 ? 'text-red-600' : 'text-slate-900' }}">
            {{ $facturesEnAttente }} <span class="text-sm font-normal text-slate-500">({{ $facturesEnRetard }} en retard)</span>
        </p>
        <p class="text-xs text-slate-400 mt-1">{{ number_format($montantEnAttente, 0, ',', ' ') }} F à encaisser</p>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
    <div class="card overflow-hidden">
        <x-card-header icon="clock" title="Essais qui se terminent dans 7 jours" />
        <table class="data-table">
            <thead><tr><th>École</th><th>Fin d'essai</th><th></th></tr></thead>
            <tbody>
                @forelse($essaisQuiFinissent as $ecole)
                    <tr>
                        <td><a href="{{ route('super-admin.ecoles.show', $ecole) }}" class="font-medium text-slate-900 hover:underline">{{ $ecole->nom }}</a></td>
                        <td class="text-amber-700 font-medium">{{ $ecole->trial_ends_at->format('d/m/Y') }} <span class="text-xs text-slate-400">({{ $ecole->joursEssaiRestants() }} j)</span></td>
                        <td class="text-right">
                            @if($ecole->telephone)
                                <a href="{{ WhatsApp::lien($ecole->telephone, "Bonjour, ici l'équipe École Manager. Votre essai gratuit se termine le {$ecole->trial_ends_at->format('d/m/Y')}. Avez-vous des questions avant de passer au Premium (25 000 F pour 2 mois) ? Nous pouvons vous aider.") }}" target="_blank" rel="noopener" class="btn-secondary text-xs">WhatsApp</a>
                            @else
                                <span class="text-xs text-slate-400">pas de téléphone</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="3" class="text-center text-slate-400 py-6">Aucun essai ne se termine cette semaine.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="card overflow-hidden">
        <x-card-header icon="bell" title="Écoles qui dorment (aucune connexion depuis 14 jours)" />
        <table class="data-table">
            <thead><tr><th>École</th><th>Dernière connexion</th><th></th></tr></thead>
            <tbody>
                @forelse($ecolesEndormies as $ecole)
                    <tr>
                        <td><a href="{{ route('super-admin.ecoles.show', $ecole) }}" class="font-medium text-slate-900 hover:underline">{{ $ecole->nom }}</a></td>
                        <td class="text-red-600">{{ $sante[$ecole->id]['derniere_connexion']?->format('d/m/Y') ?? 'jamais' }}</td>
                        <td class="text-right">
                            @if($ecole->telephone)
                                <a href="{{ WhatsApp::lien($ecole->telephone, "Bonjour, ici l'équipe École Manager. Nous avons remarqué que vous ne vous êtes pas connecté récemment. Rencontrez-vous une difficulté ? Nous pouvons vous aider à prendre l'outil en main.") }}" target="_blank" rel="noopener" class="btn-secondary text-xs">WhatsApp</a>
                            @else
                                <span class="text-xs text-slate-400">pas de téléphone</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="3" class="text-center text-slate-400 py-6">Toutes les écoles se sont connectées récemment.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
    <div class="card p-5">
        <div class="flex items-baseline justify-between mb-4">
            <h2 class="font-semibold text-slate-900">Revenus encaissés sur 12 mois</h2>
            <span class="text-sm text-slate-500">Total : {{ number_format(array_sum($revenus), 0, ',', ' ') }} F</span>
        </div>
        <div class="flex items-end gap-1.5 h-40">
            @foreach($revenus as $mois => $montant)
                <div class="flex-1 flex flex-col items-center justify-end h-full" title="{{ $nomsMois[substr($mois, 5)] }} {{ substr($mois, 0, 4) }} : {{ number_format($montant, 0, ',', ' ') }} F">
                    <div class="w-full rounded-t bg-brand-500" style="height: {{ max(2, round($montant / $maxRevenu * 100)) }}%"></div>
                    <span class="mt-1 text-[10px] text-slate-400">{{ $nomsMois[substr($mois, 5)] }}</span>
                </div>
            @endforeach
        </div>
    </div>

    <div class="card overflow-hidden">
        <x-card-header icon="star" title="Écoles à surveiller (santé)" />
        <table class="data-table">
            <thead><tr><th>École</th><th>Santé</th><th>Ce qui manque (30 jours)</th></tr></thead>
            <tbody>
                @forelse($ecolesFragiles as $ecole)
                    @php $s = $sante[$ecole->id]; @endphp
                    <tr>
                        <td><a href="{{ route('super-admin.ecoles.show', $ecole) }}" class="font-medium text-slate-900 hover:underline">{{ $ecole->nom }}</a></td>
                        <td><span class="badge-{{ $couleurSante($s) }}">{{ $s['score'] }}/100</span></td>
                        <td class="text-xs text-slate-500">
                            {{ collect([
                                $s['eleves'] === 0 ? 'aucun élève' : null,
                                $s['notes'] === 0 ? 'pas de notes' : null,
                                $s['presences'] === 0 ? 'pas d\'appel' : null,
                                $s['paiements'] === 0 ? 'pas de paiements' : null,
                            ])->filter()->join(', ') ?: 'connexions rares' }}
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="3" class="text-center text-slate-400 py-6">Toutes les écoles utilisent bien le système.</td></tr>
                @endforelse
            </tbody>
        </table>
        <p class="px-5 py-3 text-xs text-slate-400 border-t border-slate-100">Santé sur 100 : connexion récente, élèves saisis, notes, appels et paiements enregistrés dans les 30 derniers jours (20 points chacun).</p>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
    <div class="card overflow-hidden">
        <x-card-header icon="building" title="Dernières écoles inscrites" />
        <table class="data-table">
            <thead><tr><th>École</th><th>Statut</th><th>Santé</th><th></th></tr></thead>
            <tbody>
                @forelse($dernieresEcoles as $ecole)
                    <tr>
                        <td class="font-medium text-slate-900">{{ $ecole->nom }}</td>
                        <td><span class="badge-{{ match($ecole->statut) { 'actif' => 'green', 'suspendu' => 'red', 'expire' => 'yellow', default => 'slate' } }}">{{ $ecole->statut }}</span></td>
                        <td><span class="badge-{{ $couleurSante($sante[$ecole->id] ?? null) }}">{{ $sante[$ecole->id]['score'] ?? 0 }}/100</span></td>
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
                        <td>{{ number_format($facture->montant, 0, ',', ' ') }} F</td>
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
