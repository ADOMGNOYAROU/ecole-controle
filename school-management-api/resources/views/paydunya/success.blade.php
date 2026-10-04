@extends('layouts.app')

@section('title', 'Paiement réussi')

@section('content')
<div class="max-w-2xl mx-auto py-8 px-4">
    <div class="bg-white rounded-lg shadow-md p-6">
        <div class="text-center mb-6">
            <div class="inline-flex items-center justify-center w-16 h-16 bg-green-100 rounded-full mb-4">
                <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                </svg>
            </div>
            <h1 class="text-2xl font-bold text-gray-900">Paiement réussi !</h1>
        </div>

        <div class="bg-green-50 border border-green-200 rounded-lg p-4 mb-6">
            <h2 class="font-semibold text-green-900 mb-2">Détails du paiement</h2>
            <div class="space-y-2 text-sm">
                <p><span class="font-medium">Facture n°:</span> {{ $facture->id }}</p>
                <p><span class="font-medium">Montant payé:</span> {{ number_format($facture->montant, 0, ',', ' ') }} FCFA</p>
                <p><span class="font-medium">Reçu PayDunya:</span> {{ $receipt }}</p>
                <p><span class="font-medium">Date:</span> {{ $facture->payee_le ? $facture->payee_le->format('d/m/Y H:i') : 'N/A' }}</p>
            </div>
        </div>

        <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6">
            <p class="text-sm text-blue-900">
                Votre abonnement Premium est maintenant actif. Vous pouvez accéder à toutes les fonctionnalités Premium.
            </p>
        </div>

        <div class="flex justify-center">
            <a href="{{ route('dashboard') }}" class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                Retour au tableau de bord
            </a>
        </div>
    </div>
</div>
@endsection
