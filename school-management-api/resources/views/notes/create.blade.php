@extends('layouts.app')

@section('title', 'Ajouter une note - School Manager')

@section('content')
<div class="container mx-auto px-4 py-6">
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-900">Ajouter une note</h1>
        <p class="text-gray-600 mt-2">Enregistrez une note pour un élève</p>
    </div>

    <div class="bg-white shadow rounded-lg">
        <form method="POST" action="{{ route('notes.store') }}" class="p-6">
            @csrf
            
            <!-- Élève -->
            <div class="mb-4">
                <label for="eleve_id" class="block text-sm font-medium text-gray-700 mb-2">Élève *</label>
                <select id="eleve_id" name="eleve_id" required
                        class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm @error('eleve_id') border-red-500 @enderror">
                    <option value="">Sélectionnez un élève</option>
                    @foreach(\App\Models\Eleve::with('classe')->get() as $eleve)
                        <option value="{{ $eleve->id }}" {{ old('eleve_id') == $eleve->id ? 'selected' : '' }}>
                            {{ $eleve->nom }} {{ $eleve->prenom }} ({{ $eleve->classe->nom ?? 'N/A' }})
                        </option>
                    @endforeach
                </select>
                @error('eleve_id')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Matière -->
            <div class="mb-4">
                <label for="matiere_id" class="block text-sm font-medium text-gray-700 mb-2">Matière *</label>
                <select id="matiere_id" name="matiere_id" required
                        class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm @error('matiere_id') border-red-500 @enderror">
                    <option value="">Sélectionnez une matière</option>
                    @foreach(\App\Models\Matiere::all() as $matiere)
                        <option value="{{ $matiere->id }}" {{ old('matiere_id') == $matiere->id ? 'selected' : '' }}>
                            {{ $matiere->nom }}
                        </option>
                    @endforeach
                </select>
                @error('matiere_id')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Note -->
            <div class="mb-4">
                <label for="note" class="block text-sm font-medium text-gray-700 mb-2">Note *</label>
                <input type="number" id="note" name="note" value="{{ old('note') }}" required
                       min="0" max="20" step="0.5" placeholder="Ex: 15.5"
                       class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm @error('note') border-red-500 @enderror">
                @error('note')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Type d'évaluation -->
            <div class="mb-4">
                <label for="type_evaluation" class="block text-sm font-medium text-gray-700 mb-2">Type d'évaluation *</label>
                <select id="type_evaluation" name="type_evaluation" required
                        class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm @error('type_evaluation') border-red-500 @enderror">
                    <option value="">Sélectionnez un type</option>
                    <option value="Devoir" {{ old('type_evaluation') == 'Devoir' ? 'selected' : '' }}>Devoir</option>
                    <option value="Interrogation" {{ old('type_evaluation') == 'Interrogation' ? 'selected' : '' }}>Interrogation</option>
                    <option value="Examen" {{ old('type_evaluation') == 'Examen' ? 'selected' : '' }}>Examen</option>
                    <option value="TP" {{ old('type_evaluation') == 'TP' ? 'selected' : '' }}>Travaux Pratiques</option>
                    <option value="Oral" {{ old('type_evaluation') == 'Oral' ? 'selected' : '' }}>Oral</option>
                </select>
                @error('type_evaluation')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Date d'évaluation -->
            <div class="mb-4">
                <label for="date_evaluation" class="block text-sm font-medium text-gray-700 mb-2">Date d'évaluation *</label>
                <input type="date" id="date_evaluation" name="date_evaluation" value="{{ old('date_evaluation', now()->format('Y-m-d')) }}" required
                       class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm @error('date_evaluation') border-red-500 @enderror">
                @error('date_evaluation')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Coefficient -->
            <div class="mb-6">
                <label for="coefficient" class="block text-sm font-medium text-gray-700 mb-2">Coefficient *</label>
                <input type="number" id="coefficient" name="coefficient" value="{{ old('coefficient', 1) }}" required
                       min="0.1" max="10" step="0.1" placeholder="Ex: 1"
                       class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm @error('coefficient') border-red-500 @enderror">
                @error('coefficient')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Boutons -->
            <div class="flex justify-end space-x-3">
                <a href="{{ route('notes.index') }}" 
                   class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50">
                    Annuler
                </a>
                <button type="submit" 
                        class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700">
                    Ajouter la note
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
