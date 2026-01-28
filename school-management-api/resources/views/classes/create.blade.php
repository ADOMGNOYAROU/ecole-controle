@extends('layouts.app')

@section('title', 'Ajouter une classe - School Manager')

@section('content')
<div class="container mx-auto px-4 py-6">
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-900">Ajouter une classe</h1>
        <p class="text-gray-600 mt-2">Créez une nouvelle classe dans l'établissement</p>
    </div>

    <div class="bg-white shadow rounded-lg">
        <form method="POST" action="{{ route('classes.store') }}" class="p-6">
            @csrf
            
            <!-- Nom -->
            <div class="mb-4">
                <label for="nom" class="block text-sm font-medium text-gray-700 mb-2">Nom de la classe *</label>
                <input type="text" id="nom" name="nom" value="{{ old('nom') }}" required
                       placeholder="Ex: 6ème A, Terminale S"
                       class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm @error('nom') border-red-500 @enderror">
                @error('nom')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Niveau -->
            <div class="mb-4">
                <label for="niveau" class="block text-sm font-medium text-gray-700 mb-2">Niveau *</label>
                <input type="text" id="niveau" name="niveau" value="{{ old('niveau') }}" required
                       placeholder="Ex: 6ème, Terminale"
                       class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm @error('niveau') border-red-500 @enderror">
                @error('niveau')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Capacité -->
            <div class="mb-6">
                <label for="capacite" class="block text-sm font-medium text-gray-700 mb-2">Capacité *</label>
                <input type="number" id="capacite" name="capacite" value="{{ old('capacite') }}" required
                       min="1" max="100" placeholder="Ex: 30"
                       class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm @error('capacite') border-red-500 @enderror">
                @error('capacite')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Boutons -->
            <div class="flex justify-end space-x-3">
                <a href="{{ route('classes.index') }}" 
                   class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50">
                    Annuler
                </a>
                <button type="submit" 
                        class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700">
                    Créer la classe
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
