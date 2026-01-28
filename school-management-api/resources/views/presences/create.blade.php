@extends('layouts.app')

@section('title', 'Ajouter une présence - School Manager')

@section('content')
<div class="container mx-auto px-4 py-6">
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-900">Ajouter une présence</h1>
        <p class="text-gray-600 mt-2">Enregistrez la présence d'un élève</p>
    </div>

    <div class="bg-white shadow rounded-lg">
        <form method="POST" action="{{ route('presences.store') }}" class="p-6">
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

            <!-- Date -->
            <div class="mb-4">
                <label for="date" class="block text-sm font-medium text-gray-700 mb-2">Date *</label>
                <input type="date" id="date" name="date" value="{{ old('date', now()->format('Y-m-d')) }}" required
                       class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm @error('date') border-red-500 @enderror">
                @error('date')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Statut -->
            <div class="mb-6">
                <label class="block text-sm font-medium text-gray-700 mb-2">Statut *</label>
                <div class="flex space-x-4">
                    <label class="flex items-center">
                        <input type="radio" name="statut" value="present" {{ old('statut') == 'present' ? 'checked' : '' }} required
                               class="mr-2">
                        <span class="flex items-center">
                            <svg class="w-5 h-5 text-green-500 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                            </svg>
                            Présent
                        </span>
                    </label>
                    <label class="flex items-center">
                        <input type="radio" name="statut" value="absent" {{ old('statut') == 'absent' ? 'checked' : '' }} required
                               class="mr-2">
                        <span class="flex items-center">
                            <svg class="w-5 h-5 text-red-500 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                            </svg>
                            Absent
                        </span>
                    </label>
                    <label class="flex items-center">
                        <input type="radio" name="statut" value="retard" {{ old('statut') == 'retard' ? 'checked' : '' }} required
                               class="mr-2">
                        <span class="flex items-center">
                            <svg class="w-5 h-5 text-yellow-500 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"/>
                            </svg>
                            En retard
                        </span>
                    </label>
                </div>
                @error('statut')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Motif (si absent ou en retard) -->
            <div class="mb-6" id="motif_field" style="display: none;">
                <label for="motif" class="block text-sm font-medium text-gray-700 mb-2">Motif</label>
                <textarea id="motif" name="motif" rows="3"
                          class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">{{ old('motif') }}</textarea>
                @error('motif')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Boutons -->
            <div class="flex justify-end space-x-3">
                <a href="{{ route('presences.index') }}" 
                   class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50">
                    Annuler
                </a>
                <button type="submit" 
                        class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700">
                    Enregistrer la présence
                </button>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const statutRadios = document.querySelectorAll('input[name="statut"]');
    const motifField = document.getElementById('motif_field');
    
    function toggleMotifField() {
        const selectedStatut = document.querySelector('input[name="statut"]:checked').value;
        if (selectedStatut === 'absent' || selectedStatut === 'retard') {
            motifField.style.display = 'block';
        } else {
            motifField.style.display = 'none';
        }
    }
    
    statutRadios.forEach(radio => {
        radio.addEventListener('change', toggleMotifField);
    });
    
    // Initial check
    toggleMotifField();
});
</script>
@endsection
