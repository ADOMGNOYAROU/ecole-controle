@extends('layouts.app')

@section('title', 'Notes en masse - School Manager')

@section('content')
<div class="container mx-auto px-4 py-6">
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-900">Notes en masse</h1>
        <p class="text-gray-600 mt-2">Enregistrez des notes pour toute une classe</p>
    </div>

    <div class="bg-white shadow rounded-lg">
        <form method="POST" action="{{ route('notes.bulk.store') }}" class="p-6">
            @csrf
            
            <!-- Matière -->
            <div class="mb-4">
                <label for="matiere_id" class="block text-sm font-medium text-gray-700 mb-2">Matière *</label>
                <select id="matiere_id" name="matiere_id" required
                        class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                    <option value="">Sélectionnez une matière</option>
                    @foreach(\App\Models\Matiere::all() as $matiere)
                        <option value="{{ $matiere->id }}">{{ $matiere->nom }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Type d'évaluation -->
            <div class="mb-4">
                <label for="type_evaluation" class="block text-sm font-medium text-gray-700 mb-2">Type d'évaluation *</label>
                <select id="type_evaluation" name="type_evaluation" required
                        class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                    <option value="">Sélectionnez un type</option>
                    <option value="Devoir">Devoir</option>
                    <option value="Interrogation">Interrogation</option>
                    <option value="Examen">Examen</option>
                    <option value="TP">Travaux Pratiques</option>
                    <option value="Oral">Oral</option>
                </select>
            </div>

            <!-- Date d'évaluation -->
            <div class="mb-4">
                <label for="date_evaluation" class="block text-sm font-medium text-gray-700 mb-2">Date d'évaluation *</label>
                <input type="date" id="date_evaluation" name="date_evaluation" value="{{ now()->format('Y-m-d') }}" required
                       class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
            </div>

            <!-- Coefficient -->
            <div class="mb-4">
                <label for="coefficient" class="block text-sm font-medium text-gray-700 mb-2">Coefficient *</label>
                <input type="number" id="coefficient" name="coefficient" value="1" required
                       min="0.1" max="10" step="0.1"
                       class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
            </div>

            <!-- Classe -->
            <div class="mb-4">
                <label for="classe_id" class="block text-sm font-medium text-gray-700 mb-2">Classe *</label>
                <select id="classe_id" name="classe_id" required
                        class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                    <option value="">Sélectionnez une classe</option>
                    @foreach(\App\Models\Classe::all() as $classe)
                        <option value="{{ $classe->id }}">{{ $classe->nom }} ({{ $classe->niveau }})</option>
                    @endforeach
                </select>
            </div>

            <!-- Liste des élèves -->
            <div class="mb-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Notes des élèves</h3>
                <div id="eleves_list" class="space-y-2">
                    <p class="text-gray-500">Sélectionnez une classe pour voir la liste des élèves</p>
                </div>
            </div>

            <!-- Boutons -->
            <div class="flex justify-end space-x-3">
                <a href="{{ route('notes.index') }}" 
                   class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50">
                    Annuler
                </a>
                <button type="submit" 
                        class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700">
                    Enregistrer les notes
                </button>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const classeSelect = document.getElementById('classe_id');
    const elevesList = document.getElementById('eleves_list');
    
    classeSelect.addEventListener('change', function() {
        const classeId = this.value;
        
        if (!classeId) {
            elevesList.innerHTML = '<p class="text-gray-500">Sélectionnez une classe pour voir la liste des élèves</p>';
            return;
        }
        
        // Charger les élèves de la classe
        fetch(`/api/classes/${classeId}/eleves`)
            .then(response => response.json())
            .then(data => {
                let html = '';
                data.forEach(eleve => {
                    html += `
                        <div class="flex items-center justify-between p-3 border rounded-lg">
                            <div class="flex items-center">
                                <span class="font-medium">${eleve.nom} ${eleve.prenom}</span>
                            </div>
                            <div class="flex items-center space-x-3">
                                <input type="number" 
                                       name="notes[${eleve.id}][note]" 
                                       min="0" max="20" step="0.5" 
                                       placeholder="Note/20"
                                       class="w-20 px-2 py-1 border rounded text-center">
                                <input type="hidden" name="notes[${eleve.id}][eleve_id]" value="${eleve.id}">
                            </div>
                        </div>
                    `;
                });
                elevesList.innerHTML = html;
            })
            .catch(error => {
                console.error('Erreur:', error);
                elevesList.innerHTML = '<p class="text-red-500">Erreur lors du chargement des élèves</p>';
            });
    });
});
</script>
@endsection
