@extends('layouts.app')

@section('title', 'Paiements')

@section('content')
<div class="flex items-center justify-between mb-4">
    <h1 class="page-title">Paiements de scolarité</h1>
    <div class="flex items-center gap-2">
        <x-export-pdf-button :href="route('paiements.rapport', request()->query())" />
        <a href="{{ route('paiements.create') }}" class="btn-primary">+ Nouveau paiement</a>
    </div>
</div>

<div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
    <div class="card p-5"><p class="text-sm text-slate-500">Total attendu</p><p class="text-2xl font-semibold">{{ number_format($stats['total_attendu'], 0, ',', ' ') }} F</p></div>
    <div class="card p-5"><p class="text-sm text-slate-500">Total collecté</p><p class="text-2xl font-semibold text-green-600">{{ number_format($stats['total_collecte'], 0, ',', ' ') }} F</p></div>
    <div class="card p-5"><p class="text-sm text-slate-500">Paiements en retard</p><p class="text-2xl font-semibold text-red-600">{{ $stats['en_retard'] }}</p></div>
</div>

<div class="card overflow-hidden">
    <table class="data-table">
        <thead><tr><th>Élève</th><th>Type</th><th>Montant</th><th>Payé</th><th>Échéance</th><th>Statut</th><th></th></tr></thead>
        <tbody>
            @forelse($paiements as $paiement)
                <tr>
                    <td>
                        <div class="flex items-center gap-3">
                            <x-avatar :name="$paiement->eleve->nomComplet()" />
                            <span class="font-medium text-slate-900">{{ $paiement->eleve->nomComplet() }}</span>
                        </div>
                    </td>
                    <td>{{ $paiement->type }}</td>
                    <td>{{ number_format($paiement->montant, 0, ',', ' ') }} F</td>
                    <td>{{ number_format($paiement->montant_paye, 0, ',', ' ') }} F</td>
                    <td class="text-slate-500">{{ $paiement->date_echeance->format('d/m/Y') }}</td>
                    <td><span class="badge-{{ match($paiement->statut) { 'paye' => 'green', 'partiel' => 'yellow', 'retard' => 'red', default => 'slate' } }}">{{ $paiement->statut }}</span></td>
                    <td class="text-right whitespace-nowrap"><x-action-link :href="route('paiements.edit', $paiement)" type="edit">Modifier</x-action-link></td>
                </tr>
            @empty
                <tr><td colspan="7" class="text-center text-slate-400 py-6">Aucun paiement.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="mt-4">{{ $paiements->links() }}</div>
@endsection
