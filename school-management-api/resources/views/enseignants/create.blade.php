@extends('layouts.app')

@section('title', 'Ajouter un enseignant - School Manager')

@section('content')
<div class="container mx-auto px-4 py-6">
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-900">Ajouter un enseignant</h1>
        <p class="text-gray-600 mt-2">Enregistrez un nouvel enseignant dans l'établissement</p>
    </div>

    <div class="bg-white shadow rounded-lg">
        <form method="POST" action="{{ route('enseignants.store') }}" class="p-6">
            @csrf
            
            <!-- Nom -->
            <div class="mb-4">
                <label for="nom" class="block text-sm font-medium text-gray-700 mb-2">Nom *</label>
                <input type="text" id="nom" name="nom" value="{{ old('nom') }}" required
                       class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm @error('nom') border-red-500 @enderror">
                @error('nom')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Prénom -->
            <div class="mb-4">
                <label for="prenom" class="block text-sm font-medium text-gray-700 mb-2">Prénom *</label>
                <input type="text" id="prenom" name="prenom" value="{{ old('prenom') }}" required
                       class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm @error('prenom') border-red-500 @enderror">
                @error('prenom')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Email -->
            <div class="mb-4">
                <label for="email" class="block text-sm font-medium text-gray-700 mb-2">Email *</label>
                <input type="email" id="email" name="email" value="{{ old('email') }}" required
                       class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm @error('email') border-red-500 @enderror">
                @error('email')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Téléphone -->
            <div class="mb-4">
                <label for="telephone" class="block text-sm font-medium text-gray-700 mb-2">Téléphone</label>
                <input type="tel" id="telephone" name="telephone" value="{{ old('telephone') }}"
                       class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm @error('telephone') border-red-500 @enderror">
                @error('telephone')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Adresse -->
            <div class="mb-4">
                <label for="adresse" class="block text-sm font-medium text-gray-700 mb-2">Adresse</label>
                <textarea id="adresse" name="adresse" rows="3"
                          class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">{{ old('adresse') }}</textarea>
                @error('adresse')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Spécialité -->
            <div class="mb-4">
                <label for="specialite" class="block text-sm font-medium text-gray-700 mb-2">Spécialité *</label>
                <input type="text" id="specialite" name="specialite" value="{{ old('specialite') }}" required
                       placeholder="Ex: Mathématiques, Physique, Français"
                       class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm @error('specialite') border-red-500 @enderror">
                @error('specialite')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Date d'embauche -->
            <div class="mb-6">
                <label for="date_embauche" class="block text-sm font-medium text-gray-700 mb-2">Date d'embauche *</label>
                <input type="date" id="date_embauche" name="date_embauche" value="{{ old('date_embauche') }}" required
                       class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm @error('date_embauche') border-red-500 @enderror">
                @error('date_embauche')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Boutons -->
            <div class="flex justify-end space-x-3">
                <a href="{{ route('enseignants.index') }}" 
                   class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50">
                    Annuler
                </a>
                <button type="submit" 
                        class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700">
                    Ajouter l'enseignant
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
