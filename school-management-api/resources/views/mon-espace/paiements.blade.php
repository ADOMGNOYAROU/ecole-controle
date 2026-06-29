@extends('layouts.app')

@section('title', 'Ma scolarité')

@section('content')
<h1 class="page-title mb-4">Ma scolarité</h1>

<div class="card overflow-hidden">
    <table class="data-table">
        <thead><tr><th>Type</th><th>Montant</th><th>Payé</th><th>Échéance</th><th>Statut</th></tr></thead>
        <tbody>
            @forelse($paiements as $paiement)
                <tr>
                    <td>{{ $paiement->type }}</td>
                    <td>{{ number_format($paiement->montant, 0, ',', ' ') }} F</td>
                    <td>{{ number_format($paiement->montant_paye, 0, ',', ' ') }} F</td>
                    <td>{{ $paiement->date_echeance->format('d/m/Y') }}</td>
                    <td><span class="badge-{{ match($paiement->statut) { 'paye' => 'green', 'partiel' => 'yellow', 'retard' => 'red', default => 'slate' } }}">{{ $paiement->statut }}</span></td>
                </tr>
            @empty
                <tr><td colspan="5" class="text-center text-slate-400 py-6">Aucun paiement enregistré.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
