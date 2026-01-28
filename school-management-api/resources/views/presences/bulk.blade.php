@extends('layouts.app')

@section('title', 'Appel en masse - School Manager')

@section('content')
<div class="container mx-auto px-4 py-6">
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-900">Appel en masse</h1>
        <p class="text-gray-600 mt-2">Enregistrez les présences pour toute une classe</p>
    </div>

    <div class="bg-white shadow rounded-lg">
        <form method="POST" action="{{ route('presences.bulk.store') }}" class="p-6">
            @csrf
            
            <!-- Classe -->
            <div class="mb-4">
                <label for="classe_id" class="block text-sm font-medium text-gray-700 mb-2">Classe *</label>
                <select id="classe_id" name="classe_id" required
                        class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                    <option value="">Sélectionnez une classe</option>
                    @foreach($classes as $classe)
                        <option value="{{ $classe->id }}">{{ $classe->nom }} ({{ $classe->niveau }})</option>
                    @endforeach
                </select>
            </div>

            <!-- Date -->
            <div class="mb-4">
                <label for="date" class="block text-sm font-medium text-gray-700 mb-2">Date *</label>
                <input type="date" id="date" name="date" value="{{ now()->format('Y-m-d') }}" required
                       class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
            </div>

            <!-- Liste des élèves -->
            <div class="mb-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Présences des élèves</h3>
                <div id="eleves_list" class="space-y-2">
                    <p class="text-gray-500">Sélectionnez une classe pour voir la liste des élèves</p>
                </div>
            </div>

            <!-- Boutons -->
            <div class="flex justify-end space-x-3">
                <a href="{{ route('presences.index') }}" 
                   class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50">
                    Annuler
                </a>
                <button type="submit" 
                        class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700">
                    Enregistrer l'appel
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
                            <div class="flex space-x-4">
                                <label class="flex items-center">
                                    <input type="radio" name="presences[${eleve.id}][statut]" value="present" checked class="mr-1">
                                    <span class="text-green-600">Présent</span>
                                </label>
                                <label class="flex items-center">
                                    <input type="radio" name="presences[${eleve.id}][statut]" value="absent" class="mr-1">
                                    <span class="text-red-600">Absent</span>
                                </label>
                                <label class="flex items-center">
                                    <input type="radio" name="presences[${eleve.id}][statut]" value="retard" class="mr-1">
                                    <span class="text-yellow-600">Retard</span>
                                </label>
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
