@extends('layouts.app')

@section('title', 'Paiement annulé')

@section('content')
<div class="max-w-2xl mx-auto py-8 px-4">
    <div class="bg-white rounded-lg shadow-md p-6">
        <div class="text-center mb-6">
            <div class="inline-flex items-center justify-center w-16 h-16 bg-yellow-100 rounded-full mb-4">
                <svg class="w-8 h-8 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                </svg>
            </div>
            <h1 class="text-2xl font-bold text-gray-900">Paiement annulé</h1>
        </div>

        <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 mb-6">
            <p class="text-sm text-yellow-900">
                Le paiement a été annulé. Votre facture reste en attente et vous pourrez la payer ultérieurement.
            </p>
        </div>

        <div class="flex justify-center space-x-4">
            <a href="{{ route('abonnement.index') }}" class="px-6 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700">
                Retour aux abonnements
            </a>
            <a href="{{ route('paydunya.initiate', $facture) }}" class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                Réessayer le paiement
            </a>
        </div>
    </div>
</div>
@endsection
