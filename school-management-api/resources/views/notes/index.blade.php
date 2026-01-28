@extends('layouts.app')

@section('title', 'Gestion des Notes - School Manager')

@section('content')
<div x-data="gradesManager()" x-init="init()">
    <!-- Page Header -->
    <div class="mb-6 flex justify-between items-center">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Gestion des Notes</h1>
            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                Saisie et suivi des notes des élèves
            </p>
        </div>
        <div class="flex space-x-3">
            <button @click="openBulkModal()" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg flex items-center">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                </svg>
                Saisie en masse
            </button>
            <button @click="openCreateModal()" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg flex items-center">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                </svg>
                Ajouter une note
            </button>
        </div>
    </div>

    <!-- Filters -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-4 mb-6 border border-gray-200 dark:border-gray-700">
        <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Classe</label>
                <select x-model="filters.classe_id" @change="loadGrades()" 
                        class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white">
                    <option value="">Toutes les classes</option>
                    <template x-for="classe in classes" :key="classe.id">
                        <option :value="classe.id" x-text="classe.nom"></option>
                    </template>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Matière</label>
                <select x-model="filters.matiere_id" @change="loadGrades()" 
                        class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white">
                    <option value="">Toutes les matières</option>
                    <template x-for="matiere in matieres" :key="matiere.id">
                        <option :value="matiere.id" x-text="matiere.nom"></option>
                    </template>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Trimestre</label>
                <select x-model="filters.trimestre" @change="loadGrades()" 
                        class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white">
                    <option value="">Tous les trimestres</option>
                    <option value="1">1er trimestre</option>
                    <option value="2">2ème trimestre</option>
                    <option value="3">3ème trimestre</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Type d'évaluation</label>
                <select x-model="filters.type_evaluation" @change="loadGrades()" 
                        class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white">
                    <option value="">Tous les types</option>
                    <option value="devoir">Devoir</option>
                    <option value="interrogation">Interrogation</option>
                    <option value="examen">Examen</option>
                    <option value="composition">Composition</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Recherche élève</label>
                <input type="text" x-model="filters.search" @input="loadGrades()" 
                       placeholder="Nom ou matricule..." 
                       class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white">
            </div>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
        <div class="bg-blue-50 dark:bg-blue-900/20 rounded-lg p-4 border border-blue-200 dark:border-blue-800">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <svg class="w-8 h-8 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-blue-800 dark:text-blue-200">Total notes</p>
                    <p class="text-2xl font-bold text-blue-900 dark:text-blue-100" x-text="stats.total">0</p>
                </div>
            </div>
        </div>
        
        <div class="bg-green-50 dark:bg-green-900/20 rounded-lg p-4 border border-green-200 dark:border-green-800">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <svg class="w-8 h-8 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-green-800 dark:text-green-200">Moyenne générale</p>
                    <p class="text-2xl font-bold text-green-900 dark:text-green-100" x-text="stats.average">0</p>
                </div>
            </div>
        </div>
        
        <div class="bg-yellow-50 dark:bg-yellow-900/20 rounded-lg p-4 border border-yellow-200 dark:border-yellow-800">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <svg class="w-8 h-8 text-yellow-600 dark:text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-yellow-800 dark:text-yellow-200">Note la plus haute</p>
                    <p class="text-2xl font-bold text-yellow-900 dark:text-yellow-100" x-text="stats.highest">0</p>
                </div>
            </div>
        </div>
        
        <div class="bg-red-50 dark:bg-red-900/20 rounded-lg p-4 border border-red-200 dark:border-red-800">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <svg class="w-8 h-8 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 17h8m0 0V9m0 8l-8-8-4 4-6-6"></path>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-red-800 dark:text-red-200">Note la plus basse</p>
                    <p class="text-2xl font-bold text-red-900 dark:text-red-100" x-text="stats.lowest">0</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Grades Table -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm overflow-hidden border border-gray-200 dark:border-gray-700">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="bg-gray-50 dark:bg-gray-900">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                            Élève
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                            Classe
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                            Matière
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                            Type
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                            Note
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                            Note/20
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                            Trimestre
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                            Date
                        </th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                            Actions
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                    <template x-for="grade in grades" :key="grade.id">
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0 h-8 w-8">
                                        <div class="h-8 w-8 rounded-full bg-gradient-to-r from-blue-500 to-purple-600 flex items-center justify-center">
                                            <span class="text-xs font-medium text-white" x-text="grade.eleve.prenom.charAt(0) + grade.eleve.nom.charAt(0)"></span>
                                        </div>
                                    </div>
                                    <div class="ml-3">
                                        <div class="text-sm font-medium text-gray-900 dark:text-white" x-text="grade.eleve.prenom + ' ' + grade.eleve.nom"></div>
                                        <div class="text-xs text-gray-500 dark:text-gray-400" x-text="grade.eleve.matricule"></div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 py-1 text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900/20 dark:text-blue-400 rounded-full" x-text="grade.classe.nom"></span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 py-1 text-xs font-medium bg-purple-100 text-purple-800 dark:bg-purple-900/20 dark:text-purple-400 rounded-full" x-text="grade.matiere.nom"></span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 py-1 text-xs font-medium rounded-full"
                                      :class="getTypeClass(grade.type_evaluation)"
                                      x-text="getTypeText(grade.type_evaluation)"></span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900 dark:text-white">
                                    <span x-text="grade.note"></span> / <span class="text-gray-500" x-text="grade.note_sur"></span>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <span class="text-sm font-medium text-gray-900 dark:text-white" x-text="getNoteSur20(grade.note, grade.note_sur).toFixed(2)"></span>
                                    <div class="ml-2 w-16 bg-gray-200 dark:bg-gray-700 rounded-full h-2">
                                        <div class="h-2 rounded-full" 
                                             :class="getNoteColor(getNoteSur20(grade.note, grade.note_sur))"
                                             :style="`width: ${Math.min(getNoteSur20(grade.note, grade.note_sur) * 5, 100)}%`"></div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 py-1 text-xs font-medium bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300 rounded-full" x-text="grade.trimestre + 'ème trimestre'"></span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white" x-text="formatDate(grade.date_evaluation)"></td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <div class="flex justify-end space-x-2">
                                    <button @click="editGrade(grade)" class="text-indigo-600 hover:text-indigo-900 dark:text-indigo-400 dark:hover:text-indigo-300" title="Modifier">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                        </svg>
                                    </button>
                                    <button @click="deleteGrade(grade)" class="text-red-600 hover:text-red-900 dark:text-red-400 dark:hover:text-red-300" title="Supprimer">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                        </svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </template>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Single Grade Modal -->
    <div x-show="showModal" x-transition:enter="transition ease-out duration-300" 
         x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-200" 
         x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
         class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">
        <div class="flex items-center justify-center min-h-screen px-4">
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75" @click="closeModal()"></div>
            
            <div class="relative bg-white dark:bg-gray-800 rounded-lg shadow-xl max-w-md w-full p-6">
                <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4" x-text="editingGrade ? 'Modifier la note' : 'Ajouter une note'"></h3>
                
                <form @submit.prevent="saveGrade()">
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Élève</label>
                            <select x-model="form.eleve_id" @change="updateClasse" required
                                    class="mt-1 block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white">
                                <option value="">Sélectionner un élève</option>
                                <template x-for="student in students" :key="student.id">
                                    <option :value="student.id" x-text="student.prenom + ' ' + student.nom + ' - ' + student.classe?.nom"></option>
                                </template>
                            </select>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Matière</label>
                            <select x-model="form.matiere_id" required
                                    class="mt-1 block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white">
                                <option value="">Sélectionner une matière</option>
                                <template x-for="matiere in matieres" :key="matiere.id">
                                    <option :value="matiere.id" x-text="matiere.nom"></option>
                                </template>
                            </select>
                        </div>
                        
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Note</label>
                                <input type="number" x-model="form.note" step="0.25" min="0" required
                                       class="mt-1 block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Note sur</label>
                                <input type="number" x-model="form.note_sur" min="1" required
                                       class="mt-1 block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white">
                            </div>
                        </div>
                        
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Type d'évaluation</label>
                                <select x-model="form.type_evaluation" required
                                        class="mt-1 block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white">
                                    <option value="devoir">Devoir</option>
                                    <option value="interrogation">Interrogation</option>
                                    <option value="examen">Examen</option>
                                    <option value="composition">Composition</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Trimestre</label>
                                <select x-model="form.trimestre" required
                                        class="mt-1 block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white">
                                    <option value="1">1er trimestre</option>
                                    <option value="2">2ème trimestre</option>
                                    <option value="3">3ème trimestre</option>
                                </select>
                            </div>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Date d'évaluation</label>
                            <input type="date" x-model="form.date_evaluation" required
                                   class="mt-1 block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white">
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Observation</label>
                            <textarea x-model="form.observation" rows="2"
                                      class="mt-1 block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white"
                                      placeholder="Commentaires sur la performance..."></textarea>
                        </div>
                    </div>
                    
                    <div class="mt-6 flex justify-end space-x-3">
                        <button type="button" @click="closeModal()" 
                                class="px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-gray-700 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-600">
                            Annuler
                        </button>
                        <button type="submit" :disabled="loading"
                                class="px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700 disabled:opacity-50">
                            <span x-show="!loading">Enregistrer</span>
                            <span x-show="loading">Enregistrement...</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
function gradesManager() {
    return {
        grades: [],
        classes: [],
        matieres: [],
        students: [],
        showModal: false,
        editingGrade: null,
        loading: false,
        stats: {
            total: 0,
            average: 0,
            highest: 0,
            lowest: 20
        },
        filters: {
            classe_id: '',
            matiere_id: '',
            trimestre: '',
            type_evaluation: '',
            search: ''
        },
        form: {
            eleve_id: '',
            matiere_id: '',
            note: '',
            note_sur: '20',
            type_evaluation: 'devoir',
            trimestre: '1',
            date_evaluation: new Date().toISOString().split('T')[0],
            observation: ''
        },
        
        async init() {
            await this.loadClasses();
            await this.loadMatieres();
            await this.loadStudents();
            await this.loadGrades();
        },
        
        async loadClasses() {
            try {
                this.classes = [
                    { id: 1, nom: '6ème A' },
                    { id: 2, nom: '5ème B' },
                    { id: 3, nom: 'CM1' },
                    { id: 4, nom: 'CP' }
                ];
            } catch (error) {
                console.error('Error loading classes:', error);
            }
        },
        
        async loadMatieres() {
            try {
                this.matieres = [
                    { id: 1, nom: 'Mathématiques' },
                    { id: 2, nom: 'Français' },
                    { id: 3, nom: 'Physique' },
                    { id: 4, nom: 'Anglais' }
                ];
            } catch (error) {
                console.error('Error loading matieres:', error);
            }
        },
        
        async loadStudents() {
            try {
                this.students = [
                    { id: 1, prenom: 'Jean', nom: 'Dupont', matricule: 'ELE2024001', classe: { id: 1, nom: '6ème A' } },
                    { id: 2, prenom: 'Fatou', nom: 'Diop', matricule: 'ELE2024002', classe: { id: 2, nom: '5ème B' } },
                    { id: 3, prenom: 'Moussa', nom: 'Ba', matricule: 'ELE2024003', classe: { id: 3, nom: 'CM1' } }
                ];
            } catch (error) {
                console.error('Error loading students:', error);
            }
        },
        
        async loadGrades() {
            try {
                // Simuler des données
                this.grades = [
                    {
                        id: 1,
                        note: 15,
                        note_sur: 20,
                        type_evaluation: 'devoir',
                        trimestre: 1,
                        date_evaluation: '2024-01-15',
                        observation: 'Bon travail',
                        eleve: { id: 1, prenom: 'Jean', nom: 'Dupont', matricule: 'ELE2024001' },
                        classe: { id: 1, nom: '6ème A' },
                        matiere: { id: 1, nom: 'Mathématiques' }
                    },
                    {
                        id: 2,
                        note: 12,
                        note_sur: 20,
                        type_evaluation: 'interrogation',
                        trimestre: 1,
                        date_evaluation: '2024-01-16',
                        observation: 'Peut mieux faire',
                        eleve: { id: 2, prenom: 'Fatou', nom: 'Diop', matricule: 'ELE2024002' },
                        classe: { id: 2, nom: '5ème B' },
                        matiere: { id: 2, nom: 'Français' }
                    }
                ];
                
                this.calculateStats();
            } catch (error) {
                console.error('Error loading grades:', error);
            }
        },
        
        calculateStats() {
            const filtered = this.getFilteredGrades();
            const notes = filtered.map(g => this.getNoteSur20(g.note, g.note_sur));
            
            this.stats.total = filtered.length;
            this.stats.average = notes.length > 0 ? (notes.reduce((a, b) => a + b, 0) / notes.length).toFixed(2) : 0;
            this.stats.highest = notes.length > 0 ? Math.max(...notes).toFixed(2) : 0;
            this.stats.lowest = notes.length > 0 ? Math.min(...notes).toFixed(2) : 0;
        },
        
        getFilteredGrades() {
            return this.grades.filter(grade => {
                if (this.filters.classe_id && grade.classe.id != this.filters.classe_id) return false;
                if (this.filters.matiere_id && grade.matiere.id != this.filters.matiere_id) return false;
                if (this.filters.trimestre && grade.trimestre != this.filters.trimestre) return false;
                if (this.filters.type_evaluation && grade.type_evaluation !== this.filters.type_evaluation) return false;
                if (this.filters.search) {
                    const search = this.filters.search.toLowerCase();
                    return grade.eleve.prenom.toLowerCase().includes(search) ||
                           grade.eleve.nom.toLowerCase().includes(search) ||
                           grade.eleve.matricule.toLowerCase().includes(search);
                }
                return true;
            });
        },
        
        openCreateModal() {
            this.editingGrade = null;
            this.form = {
                eleve_id: '',
                matiere_id: '',
                note: '',
                note_sur: '20',
                type_evaluation: 'devoir',
                trimestre: '1',
                date_evaluation: new Date().toISOString().split('T')[0],
                observation: ''
            };
            this.showModal = true;
        },
        
        editGrade(grade) {
            this.editingGrade = grade;
            this.form = {
                eleve_id: grade.eleve.id,
                matiere_id: grade.matiere.id,
                note: grade.note,
                note_sur: grade.note_sur,
                type_evaluation: grade.type_evaluation,
                trimestre: grade.trimestre,
                date_evaluation: grade.date_evaluation,
                observation: grade.observation || ''
            };
            this.showModal = true;
        },
        
        closeModal() {
            this.showModal = false;
            this.editingGrade = null;
        },
        
        async saveGrade() {
            this.loading = true;
            try {
                await new Promise(resolve => setTimeout(resolve, 1000));
                
                if (this.editingGrade) {
                    // Update existing grade
                    const index = this.grades.findIndex(g => g.id === this.editingGrade.id);
                    if (index !== -1) {
                        const student = this.students.find(s => s.id == this.form.eleve_id);
                        const matiere = this.matieres.find(m => m.id == this.form.matiere_id);
                        this.grades[index] = {
                            ...this.grades[index],
                            note: parseFloat(this.form.note),
                            note_sur: parseFloat(this.form.note_sur),
                            type_evaluation: this.form.type_evaluation,
                            trimestre: parseInt(this.form.trimestre),
                            date_evaluation: this.form.date_evaluation,
                            observation: this.form.observation,
                            eleve: student,
                            matiere: matiere,
                            classe: student.classe
                        };
                    }
                } else {
                    // Create new grade
                    const student = this.students.find(s => s.id == this.form.eleve_id);
                    const matiere = this.matieres.find(m => m.id == this.form.matiere_id);
                    this.grades.push({
                        id: Date.now(),
                        note: parseFloat(this.form.note),
                        note_sur: parseFloat(this.form.note_sur),
                        type_evaluation: this.form.type_evaluation,
                        trimestre: parseInt(this.form.trimestre),
                        date_evaluation: this.form.date_evaluation,
                        observation: this.form.observation,
                        eleve: student,
                        matiere: matiere,
                        classe: student.classe
                    });
                }
                
                this.calculateStats();
                this.closeModal();
            } catch (error) {
                console.error('Error saving grade:', error);
            } finally {
                this.loading = false;
            }
        },
        
        async deleteGrade(grade) {
            if (confirm('Êtes-vous sûr de vouloir supprimer cette note ?')) {
                try {
                    this.grades = this.grades.filter(g => g.id !== grade.id);
                    this.calculateStats();
                } catch (error) {
                    console.error('Error deleting grade:', error);
                }
            }
        },
        
        getNoteSur20(note, noteSur) {
            return (note / noteSur) * 20;
        },
        
        getNoteColor(note) {
            if (note >= 16) return 'bg-green-500';
            if (note >= 14) return 'bg-blue-500';
            if (note >= 10) return 'bg-yellow-500';
            return 'bg-red-500';
        },
        
        getTypeClass(type) {
            const classes = {
                devoir: 'bg-blue-100 text-blue-800 dark:bg-blue-900/20 dark:text-blue-400',
                interrogation: 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/20 dark:text-yellow-400',
                examen: 'bg-red-100 text-red-800 dark:bg-red-900/20 dark:text-red-400',
                composition: 'bg-purple-100 text-purple-800 dark:bg-purple-900/20 dark:text-purple-400'
            };
            return classes[type] || '';
        },
        
        getTypeText(type) {
            const texts = {
                devoir: 'Devoir',
                interrogation: 'Interrogation',
                examen: 'Examen',
                composition: 'Composition'
            };
            return texts[type] || type;
        },
        
        formatDate(dateString) {
            const date = new Date(dateString);
            return date.toLocaleDateString('fr-FR');
        },
        
        updateClasse() {
            // Auto-update classe when student is selected
            const student = this.students.find(s => s.id == this.form.eleve_id);
            if (student) {
                // This would normally update a hidden field or display
            }
        }
    }
}
</script>
@endsection
