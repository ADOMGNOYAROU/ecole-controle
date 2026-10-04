@extends('layouts.app')

@section('title', 'Paiement PayDunya')

@section('content')
<div class="max-w-2xl mx-auto py-8 px-4">
    <div class="bg-white rounded-lg shadow-md p-6">
        <h1 class="text-2xl font-bold text-gray-900 mb-6">Paiement de l'abonnement Premium</h1>

        <div class="mb-6">
            <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                <h2 class="font-semibold text-blue-900 mb-2">Détails de la facture</h2>
                <div class="space-y-2 text-sm">
                    <p><span class="font-medium">Facture n°:</span> {{ $facture->id }}</p>
                    <p><span class="font-medium">École:</span> {{ $facture->ecole->nom }}</p>
                    <p><span class="font-medium">Montant:</span> {{ number_format($facture->montant, 0, ',', ' ') }} FCFA</p>
                    <p><span class="font-medium">Date d'échéance:</span> {{ $facture->date_echeance->format('d/m/Y') }}</p>
                </div>
            </div>
        </div>

        <div class="mb-6">
            <p class="text-gray-600 mb-4">Vous allez être redirigé vers la page de paiement sécurisée de PayDunya.</p>
            <p class="text-sm text-gray-500">Moyens de paiement disponibles: Mobile Money (T Money, Moov Togo), Carte bancaire</p>
        </div>

        <div class="flex justify-end space-x-6">
            <a href="{{ route('abonnement.index') }}" class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50">
                Annuler
            </a>
            <a href="{{ $invoiceUrl }}" target="_blank" class="px-6 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700">
                Payer sur PayDunya
            </a>
        </div>
    </div>
</div>

<script>
    setTimeout(function() {
        window.location.href = '{{ $invoiceUrl }}';
    }, 3000);
</script>
@endsection
