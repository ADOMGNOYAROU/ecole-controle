@extends('layouts.app')

@section('title', $ecole->nom)

@section('content')
<div class="flex items-center justify-between mb-4">
    <div>
        <h1 class="page-title">{{ $ecole->nom }}</h1>
        <p class="text-sm text-slate-500">{{ $ecole->ville ?? 'Ville non renseignée' }} · {{ $ecole->email_contact ?? 'sans email' }} · {{ $ecole->telephone ?? 'sans téléphone' }}</p>
    </div>
    <div class="flex items-center gap-2">
        @if($ecole->statut === 'suspendu')
            <form method="PATCH" action="{{ route('super-admin.ecoles.activer', $ecole) }}">
                @csrf
                <x-icon-button icon="check" variant="success">Activer</x-icon-button>
            </form>
        @else
            <form method="PATCH" action="{{ route('super-admin.ecoles.suspendre', $ecole) }}" onsubmit="return confirm('Suspendre cette école ?');">
                @csrf
                <x-icon-button icon="pause" variant="warning">Suspendre</x-icon-button>
            </form>
        @endif
    </div>
</div>

<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
    <x-stat-card label="Élèves" :value="$stats['eleves']" icon="users" />
    <x-stat-card label="Enseignants" :value="$stats['enseignants']" icon="academic-cap" color="violet" />
    <x-stat-card label="Classes" :value="$stats['classes']" icon="building" color="amber" />
    <x-stat-card label="Utilisateurs" :value="$stats['utilisateurs']" icon="chart" color="slate" />
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <div class="card p-5">
        <h2 class="font-semibold text-slate-900 mb-3">Abonnement</h2>
        <dl class="space-y-2 text-sm">
            <div class="flex justify-between"><dt class="text-slate-500">Plan</dt><dd><span class="badge-{{ $ecole->plan === 'premium' ? 'brand' : 'slate' }}">{{ $ecole->plan }}</span></dd></div>
            <div class="flex justify-between"><dt class="text-slate-500">Statut école</dt><dd><span class="badge-{{ match($ecole->statut) { 'actif' => 'green', 'suspendu' => 'red', 'expire' => 'yellow', default => 'slate' } }}">{{ $ecole->statut }}</span></dd></div>
            <div class="flex justify-between"><dt class="text-slate-500">Accès Premium</dt><dd>{{ $ecole->aAccesPremium() ? 'Oui' : 'Non' }}</dd></div>
            @if($ecole->estEnEssai())
                <div class="flex justify-between"><dt class="text-slate-500">Essai</dt><dd>{{ $ecole->joursEssaiRestants() }} jour(s) restant(s)</dd></div>
            @endif
            @if($abonnementActif)
                <div class="flex justify-between"><dt class="text-slate-500">Abonnement actif jusqu'au</dt><dd>{{ $abonnementActif->date_fin->format('d/m/Y') }}</dd></div>
            @endif
            <div class="flex justify-between"><dt class="text-slate-500">Inscrite le</dt><dd>{{ $ecole->created_at->format('d/m/Y') }}</dd></div>
        </dl>
    </div>

    <div class="card overflow-hidden lg:col-span-2">
        <div class="p-5 pb-0"><h2 class="font-semibold text-slate-900">Historique des factures</h2></div>
        <table class="data-table">
            <thead><tr><th>Montant</th><th>Échéance</th><th>Statut</th><th>Méthode</th><th>Payée le</th></tr></thead>
            <tbody>
                @forelse($ecole->factures as $facture)
                    <tr>
                        <td>{{ number_format($facture->montant, 0, ',', ' ') }} FCFA</td>
                        <td class="{{ $facture->statut === 'en_attente' && $facture->date_echeance->isPast() ? 'text-red-600 font-medium' : 'text-slate-500' }}">{{ $facture->date_echeance->format('d/m/Y') }}</td>
                        <td><span class="badge-{{ match($facture->statut) { 'payee' => 'green', 'en_retard' => 'red', 'annulee' => 'slate', default => 'yellow' } }}">{{ $facture->statut }}</span></td>
                        <td>{{ $facture->methode_paiement ?? '—' }}</td>
                        <td>{{ $facture->payee_le?->format('d/m/Y') ?? '—' }}</td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="text-center text-slate-400 py-6">Aucune facture pour cette école.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
