@extends('layouts.app')

@section('title', 'Ajouter un élève - School Manager')

@section('content')
<div class="container mx-auto px-4 py-6">
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-900">Ajouter un élève</h1>
        <p class="text-gray-600 mt-2">Remplissez les informations pour ajouter un nouvel élève</p>
    </div>

    <div class="bg-white shadow rounded-lg">
        <form method="POST" action="{{ route('eleves.store') }}" class="p-6">
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

            <!-- Date de naissance -->
            <div class="mb-4">
                <label for="date_naissance" class="block text-sm font-medium text-gray-700 mb-2">Date de naissance *</label>
                <input type="date" id="date_naissance" name="date_naissance" value="{{ old('date_naissance') }}" required
                       class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm @error('date_naissance') border-red-500 @enderror">
                @error('date_naissance')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Sexe -->
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">Sexe *</label>
                <div class="flex space-x-4">
                    <label class="flex items-center">
                        <input type="radio" name="sexe" value="M" {{ old('sexe') == 'M' ? 'checked' : '' }} required
                               class="mr-2">
                        Masculin
                    </label>
                    <label class="flex items-center">
                        <input type="radio" name="sexe" value="F" {{ old('sexe') == 'F' ? 'checked' : '' }} required
                               class="mr-2">
                        Féminin
                    </label>
                </div>
                @error('sexe')
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

            <!-- Téléphone -->
            <div class="mb-4">
                <label for="telephone" class="block text-sm font-medium text-gray-700 mb-2">Téléphone</label>
                <input type="tel" id="telephone" name="telephone" value="{{ old('telephone') }}"
                       class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm @error('telephone') border-red-500 @enderror">
                @error('telephone')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Email -->
            <div class="mb-4">
                <label for="email" class="block text-sm font-medium text-gray-700 mb-2">Email</label>
                <input type="email" id="email" name="email" value="{{ old('email') }}"
                       class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm @error('email') border-red-500 @enderror">
                @error('email')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Classe -->
            <div class="mb-6">
                <label for="classe_id" class="block text-sm font-medium text-gray-700 mb-2">Classe *</label>
                <select id="classe_id" name="classe_id" required
                        class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm @error('classe_id') border-red-500 @enderror">
                    <option value="">Sélectionnez une classe</option>
                    @foreach(\App\Models\Classe::all() as $classe)
                        <option value="{{ $classe->id }}" {{ old('classe_id') == $classe->id ? 'selected' : '' }}>
                            {{ $classe->nom }} ({{ $classe->niveau }})
                        </option>
                    @endforeach
                </select>
                @error('classe_id')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Boutons -->
            <div class="flex justify-end space-x-3">
                <a href="{{ route('eleves.index') }}" 
                   class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50">
                    Annuler
                </a>
                <button type="submit" 
                        class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700">
                    Ajouter l'élève
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
