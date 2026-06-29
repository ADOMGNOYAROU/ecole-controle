@extends('layouts.app')

@section('title', 'Factures')

@section('content')
<h1 class="page-title mb-4">Factures</h1>

<div class="grid grid-cols-1 sm:grid-cols-4 gap-4 mb-6">
    <div class="card p-5">
        <p class="text-sm text-slate-500">En attente</p>
        <p class="text-2xl font-semibold">{{ $stats['en_attente'] }}</p>
    </div>
    <div class="card p-5">
        <p class="text-sm text-slate-500">En retard</p>
        <p class="text-2xl font-semibold {{ $stats['en_retard'] > 0 ? 'text-red-600' : '' }}">{{ $stats['en_retard'] }}</p>
    </div>
    <div class="card p-5">
        <p class="text-sm text-slate-500">Montant en attente</p>
        <p class="text-2xl font-semibold">{{ number_format($stats['montant_en_attente'], 0, ',', ' ') }} F</p>
    </div>
    <div class="card p-5">
        <p class="text-sm text-slate-500">Payées ce mois</p>
        <p class="text-2xl font-semibold text-green-600">{{ number_format($stats['payees_ce_mois'], 0, ',', ' ') }} F</p>
    </div>
</div>

<form method="GET" class="filter-bar mb-4">
    <div>
        <label class="form-label">École</label>
        <select name="ecole_id" class="form-select">
            <option value="">Toutes</option>
            @foreach($ecoles as $ecoleOption)
                <option value="{{ $ecoleOption->id }}" @selected(request('ecole_id') == $ecoleOption->id)>{{ $ecoleOption->nom }}</option>
            @endforeach
        </select>
    </div>
    <div>
        <label class="form-label">Statut</label>
        <select name="statut" class="form-select">
            <option value="">Tous</option>
            <option value="en_attente" @selected(request('statut') === 'en_attente')>En attente</option>
            <option value="payee" @selected(request('statut') === 'payee')>Payée</option>
            <option value="en_retard" @selected(request('statut') === 'en_retard')>En retard</option>
            <option value="annulee" @selected(request('statut') === 'annulee')>Annulée</option>
        </select>
    </div>
    <button type="submit" class="btn-secondary">Filtrer</button>
</form>

<div class="card overflow-hidden">
    <table class="data-table">
        <thead><tr><th>École</th><th>Montant</th><th>Échéance</th><th>Statut</th><th></th></tr></thead>
        <tbody>
            @forelse($factures as $facture)
                <tr>
                    <td>{{ $facture->ecole->nom }}</td>
                    <td>{{ number_format($facture->montant, 0, ',', ' ') }} FCFA</td>
                    <td>{{ $facture->date_echeance->format('d/m/Y') }}</td>
                    <td><span class="badge-{{ match($facture->statut) { 'payee' => 'green', 'en_retard' => 'red', 'annulee' => 'slate', default => 'yellow' } }}">{{ $facture->statut }}</span></td>
                    <td class="text-right">
                        @if($facture->statut === 'en_attente')
                            <form method="POST" action="{{ route('super-admin.factures.confirmer', $facture) }}" class="flex items-center justify-end gap-2">
                                @csrf
                                <select name="methode_paiement" class="form-select w-32" required>
                                    <option value="flooz">Flooz</option>
                                    <option value="tmoney">TMoney</option>
                                    <option value="virement">Virement</option>
                                    <option value="especes">Espèces</option>
                                    <option value="autre">Autre</option>
                                </select>
                                <input type="text" name="reference_transaction" placeholder="Référence" class="form-input w-32">
                                <button type="submit" class="btn-primary">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                    Confirmer le paiement
                                </button>
                            </form>
                        @else
                            <span class="text-slate-400 text-sm">{{ $facture->methode_paiement }} — {{ $facture->payee_le?->format('d/m/Y') }}</span>
                        @endif
                    </td>
                </tr>
            @empty
                <tr><td colspan="5" class="text-center text-slate-400 py-6">Aucune facture.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="mt-4">{{ $factures->links() }}</div>
@endsection
