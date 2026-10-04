@extends('layouts.app')

@section('title', 'Erreur de paiement')

@section('content')
<div class="max-w-2xl mx-auto py-8 px-4">
    <div class="bg-white rounded-lg shadow-md p-6">
        <div class="text-center mb-6">
            <div class="inline-flex items-center justify-center w-16 h-16 bg-red-100 rounded-full mb-4">
                <svg class="w-8 h-8 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </div>
            <h1 class="text-2xl font-bold text-gray-900">Erreur de paiement</h1>
        </div>

        <div class="bg-red-50 border border-red-200 rounded-lg p-4 mb-6">
            <p class="text-sm text-red-900">
                {{ $message }}
            </p>
        </div>

        <div class="flex justify-center">
            <a href="{{ route('abonnement.index') }}" class="px-6 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700">
                Retour aux abonnements
            </a>
        </div>
    </div>
</div>
@endsection
