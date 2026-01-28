@extends('layouts.app')

@section('title', 'Gestion des Présences - School Manager')

@section('content')
<div x-data="attendanceManager()" x-init="init()">
    <!-- Page Header -->
    <div class="mb-6 flex justify-between items-center">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Gestion des Présences</h1>
            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                Suivi des présences des élèves
            </p>
        </div>
        <div class="flex space-x-3">
            <button @click="openBulkModal()" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg flex items-center">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                </svg>
                Marquer en masse
            </button>
            <button @click="openCreateModal()" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg flex items-center">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                </svg>
                Marquer Présence
            </button>
        </div>
    </div>

    <!-- Filters -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-4 mb-6 border border-gray-200 dark:border-gray-700">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Date</label>
                <input type="date" x-model="filters.date" @change="loadAttendances()" 
                       class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Classe</label>
                <select x-model="filters.classe_id" @change="loadAttendances()" 
                        class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white">
                    <option value="">Toutes les classes</option>
                    <template x-for="classe in classes" :key="classe.id">
                        <option :value="classe.id" x-text="classe.nom"></option>
                    </template>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Statut</label>
                <select x-model="filters.statut" @change="loadAttendances()" 
                        class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white">
                    <option value="">Tous les statuts</option>
                    <option value="present">Présent</option>
                    <option value="absent">Absent</option>
                    <option value="retard">Retard</option>
                    <option value="absent_justifie">Absent justifié</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Recherche élève</label>
                <input type="text" x-model="filters.search" @input="loadAttendances()" 
                       placeholder="Nom ou matricule..." 
                       class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white">
            </div>
        </div>
    </div>

    <!-- Attendance Statistics -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
        <div class="bg-green-50 dark:bg-green-900/20 rounded-lg p-4 border border-green-200 dark:border-green-800">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <svg class="w-8 h-8 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-green-800 dark:text-green-200">Présents</p>
                    <p class="text-2xl font-bold text-green-900 dark:text-green-100" x-text="stats.present">0</p>
                </div>
            </div>
        </div>
        
        <div class="bg-red-50 dark:bg-red-900/20 rounded-lg p-4 border border-red-200 dark:border-red-800">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <svg class="w-8 h-8 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-red-800 dark:text-red-200">Absents</p>
                    <p class="text-2xl font-bold text-red-900 dark:text-red-100" x-text="stats.absent">0</p>
                </div>
            </div>
        </div>
        
        <div class="bg-yellow-50 dark:bg-yellow-900/20 rounded-lg p-4 border border-yellow-200 dark:border-yellow-800">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <svg class="w-8 h-8 text-yellow-600 dark:text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-yellow-800 dark:text-yellow-200">Retards</p>
                    <p class="text-2xl font-bold text-yellow-900 dark:text-yellow-100" x-text="stats.retard">0</p>
                </div>
            </div>
        </div>
        
        <div class="bg-blue-50 dark:bg-blue-900/20 rounded-lg p-4 border border-blue-200 dark:border-blue-800">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <svg class="w-8 h-8 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-blue-800 dark:text-blue-200">Taux de présence</p>
                    <p class="text-2xl font-bold text-blue-900 dark:text-blue-100" x-text="stats.rate + '%'">0%</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Attendances Table -->
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
                            Date
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                            Statut
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                            Motif
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                            Marqué par
                        </th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                            Actions
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                    <template x-for="attendance in attendances" :key="attendance.id">
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0 h-8 w-8">
                                        <div class="h-8 w-8 rounded-full bg-gradient-to-r from-blue-500 to-purple-600 flex items-center justify-center">
                                            <span class="text-xs font-medium text-white" x-text="attendance.eleve.prenom.charAt(0) + attendance.eleve.nom.charAt(0)"></span>
                                        </div>
                                    </div>
                                    <div class="ml-3">
                                        <div class="text-sm font-medium text-gray-900 dark:text-white" x-text="attendance.eleve.prenom + ' ' + attendance.eleve.nom"></div>
                                        <div class="text-xs text-gray-500 dark:text-gray-400" x-text="attendance.eleve.matricule"></div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 py-1 text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900/20 dark:text-blue-400 rounded-full" x-text="attendance.classe.nom"></span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white" x-text="formatDate(attendance.date)"></td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 py-1 text-xs font-medium rounded-full"
                                      :class="getStatusClass(attendance.statut)"
                                      x-text="getStatusText(attendance.statut)"></span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white" x-text="attendance.motif || '-'"></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white" x-text="attendance.enseignant?.user?.name || '-'"></td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <div class="flex justify-end space-x-2">
                                    <button @click="editAttendance(attendance)" class="text-indigo-600 hover:text-indigo-900 dark:text-indigo-400 dark:hover:text-indigo-300" title="Modifier">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                        </svg>
                                    </button>
                                    <button @click="deleteAttendance(attendance)" class="text-red-600 hover:text-red-900 dark:text-red-400 dark:hover:text-red-300" title="Supprimer">
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

    <!-- Single Attendance Modal -->
    <div x-show="showModal" x-transition:enter="transition ease-out duration-300" 
         x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-200" 
         x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
         class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">
        <div class="flex items-center justify-center min-h-screen px-4">
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75" @click="closeModal()"></div>
            
            <div class="relative bg-white dark:bg-gray-800 rounded-lg shadow-xl max-w-md w-full p-6">
                <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4" x-text="editingAttendance ? 'Modifier la présence' : 'Marquer une présence'"></h3>
                
                <form @submit.prevent="saveAttendance()">
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Élève</label>
                            <select x-model="form.eleve_id" required
                                    class="mt-1 block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white">
                                <option value="">Sélectionner un élève</option>
                                <template x-for="student in students" :key="student.id">
                                    <option :value="student.id" x-text="student.prenom + ' ' + student.nom + ' - ' + student.classe?.nom"></option>
                                </template>
                            </select>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Date</label>
                            <input type="date" x-model="form.date" required
                                   class="mt-1 block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white">
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Statut</label>
                            <select x-model="form.statut" required
                                    class="mt-1 block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white">
                                <option value="present">Présent</option>
                                <option value="absent">Absent</option>
                                <option value="retard">Retard</option>
                                <option value="absent_justifie">Absent justifié</option>
                            </select>
                        </div>
                        
                        <div x-show="form.statut === 'absent' || form.statut === 'absent_justifie' || form.statut === 'retard'">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Motif</label>
                            <textarea x-model="form.motif" rows="2"
                                      class="mt-1 block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white"
                                      placeholder="Raison de l'absence ou du retard..."></textarea>
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

    <!-- Bulk Attendance Modal -->
    <div x-show="showBulkModal" x-transition:enter="transition ease-out duration-300" 
         x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-200" 
         x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
         class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">
        <div class="flex items-center justify-center min-h-screen px-4">
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75" @click="closeBulkModal()"></div>
            
            <div class="relative bg-white dark:bg-gray-800 rounded-lg shadow-xl max-w-4xl w-full p-6 max-h-[90vh] overflow-y-auto">
                <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Marquer les présences en masse</h3>
                
                <form @submit.prevent="saveBulkAttendance()">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Classe</label>
                            <select x-model="bulkForm.classe_id" @change="loadClassStudents" required
                                    class="mt-1 block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white">
                                <option value="">Sélectionner une classe</option>
                                <template x-for="classe in classes" :key="classe.id">
                                    <option :value="classe.id" x-text="classe.nom"></option>
                                </template>
                            </select>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Date</label>
                            <input type="date" x-model="bulkForm.date" required
                                   class="mt-1 block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white">
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Statut par défaut</label>
                            <select x-model="bulkForm.default_statut"
                                    class="mt-1 block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white">
                                <option value="present">Présent</option>
                                <option value="absent">Absent</option>
                                <option value="retard">Retard</option>
                                <option value="absent_justifie">Absent justifié</option>
                            </select>
                        </div>
                    </div>
                    
                    <!-- Students List -->
                    <div x-show="bulkForm.classe_id" class="border border-gray-200 dark:border-gray-700 rounded-lg p-4 max-h-96 overflow-y-auto">
                        <h4 class="text-sm font-medium text-gray-900 dark:text-white mb-3">Élèves de la classe</h4>
                        <div class="space-y-2">
                            <template x-for="student in classStudents" :key="student.id">
                                <div class="flex items-center justify-between p-2 bg-gray-50 dark:bg-gray-700 rounded-lg">
                                    <div class="flex items-center">
                                        <div class="h-6 w-6 rounded-full bg-gradient-to-r from-blue-500 to-purple-600 flex items-center justify-center mr-3">
                                            <span class="text-xs font-medium text-white" x-text="student.prenom.charAt(0) + student.nom.charAt(0)"></span>
                                        </div>
                                        <span class="text-sm text-gray-900 dark:text-white" x-text="student.prenom + ' ' + student.nom"></span>
                                    </div>
                                    <select x-model="bulkForm.presences[student.id]" 
                                            class="px-2 py-1 text-xs border border-gray-300 dark:border-gray-600 rounded focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white">
                                        <option value="present">Présent</option>
                                        <option value="absent">Absent</option>
                                        <option value="retard">Retard</option>
                                        <option value="absent_justifie">Absent justifié</option>
                                    </select>
                                </div>
                            </template>
                        </div>
                    </div>
                    
                    <div class="mt-6 flex justify-end space-x-3">
                        <button type="button" @click="closeBulkModal()" 
                                class="px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-gray-700 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-600">
                            Annuler
                        </button>
                        <button type="submit" :disabled="loading || !bulkForm.classe_id"
                                class="px-4 py-2 text-sm font-medium text-white bg-green-600 rounded-lg hover:bg-green-700 disabled:opacity-50">
                            <span x-show="!loading">Marquer les présences</span>
                            <span x-show="loading">Enregistrement...</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
function attendanceManager() {
    return {
        attendances: [],
        classes: [],
        students: [],
        classStudents: [],
        showModal: false,
        showBulkModal: false,
        editingAttendance: null,
        loading: false,
        stats: {
            present: 0,
            absent: 0,
            retard: 0,
            rate: 0
        },
        filters: {
            date: new Date().toISOString().split('T')[0],
            classe_id: '',
            statut: '',
            search: ''
        },
        form: {
            eleve_id: '',
            date: new Date().toISOString().split('T')[0],
            statut: 'present',
            motif: ''
        },
        bulkForm: {
            classe_id: '',
            date: new Date().toISOString().split('T')[0],
            default_statut: 'present',
            presences: {}
        },
        
        async init() {
            await this.loadClasses();
            await this.loadStudents();
            await this.loadAttendances();
        },
        
        async loadClasses() {
            try {
                // Simuler des données
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
        
        async loadStudents() {
            try {
                // Simuler des données
                this.students = [
                    { id: 1, prenom: 'Jean', nom: 'Dupont', classe: { id: 1, nom: '6ème A' } },
                    { id: 2, prenom: 'Fatou', nom: 'Diop', classe: { id: 2, nom: '5ème B' } },
                    { id: 3, prenom: 'Moussa', nom: 'Ba', classe: { id: 3, nom: 'CM1' } }
                ];
            } catch (error) {
                console.error('Error loading students:', error);
            }
        },
        
        async loadAttendances() {
            try {
                // Simuler des données
                this.attendances = [
                    {
                        id: 1,
                        date: '2024-01-15',
                        statut: 'present',
                        motif: null,
                        eleve: { id: 1, prenom: 'Jean', nom: 'Dupont', matricule: 'ELE2024001' },
                        classe: { id: 1, nom: '6ème A' },
                        enseignant: { user: { name: 'Martin Dupont' } }
                    },
                    {
                        id: 2,
                        date: '2024-01-15',
                        statut: 'absent',
                        motif: 'Maladie',
                        eleve: { id: 2, prenom: 'Fatou', nom: 'Diop', matricule: 'ELE2024002' },
                        classe: { id: 2, nom: '5ème B' },
                        enseignant: { user: { name: 'Sophie Martin' } }
                    }
                ];
                
                this.calculateStats();
            } catch (error) {
                console.error('Error loading attendances:', error);
            }
        },
        
        async loadClassStudents() {
            if (!this.bulkForm.classe_id) {
                this.classStudents = [];
                return;
            }
            
            try {
                // Simuler le chargement des élèves d'une classe
                this.classStudents = this.students.filter(s => s.classe?.id == this.bulkForm.classe_id);
                
                // Initialiser les présences avec le statut par défaut
                this.bulkForm.presences = {};
                this.classStudents.forEach(student => {
                    this.bulkForm.presences[student.id] = this.bulkForm.default_statut;
                });
            } catch (error) {
                console.error('Error loading class students:', error);
            }
        },
        
        calculateStats() {
            const filtered = this.getFilteredAttendances();
            this.stats.present = filtered.filter(a => a.statut === 'present').length;
            this.stats.absent = filtered.filter(a => a.statut === 'absent' || a.statut === 'absent_justifie').length;
            this.stats.retard = filtered.filter(a => a.statut === 'retard').length;
            
            const total = filtered.length;
            this.stats.rate = total > 0 ? Math.round((this.stats.present / total) * 100) : 0;
        },
        
        getFilteredAttendances() {
            return this.attendances.filter(attendance => {
                if (this.filters.date && attendance.date !== this.filters.date) return false;
                if (this.filters.classe_id && attendance.classe.id != this.filters.classe_id) return false;
                if (this.filters.statut && attendance.statut !== this.filters.statut) return false;
                if (this.filters.search) {
                    const search = this.filters.search.toLowerCase();
                    return attendance.eleve.prenom.toLowerCase().includes(search) ||
                           attendance.eleve.nom.toLowerCase().includes(search) ||
                           attendance.eleve.matricule.toLowerCase().includes(search);
                }
                return true;
            });
        },
        
        openCreateModal() {
            this.editingAttendance = null;
            this.form = {
                eleve_id: '',
                date: this.filters.date || new Date().toISOString().split('T')[0],
                statut: 'present',
                motif: ''
            };
            this.showModal = true;
        },
        
        editAttendance(attendance) {
            this.editingAttendance = attendance;
            this.form = {
                eleve_id: attendance.eleve.id,
                date: attendance.date,
                statut: attendance.statut,
                motif: attendance.motif || ''
            };
            this.showModal = true;
        },
        
        openBulkModal() {
            this.bulkForm = {
                classe_id: '',
                date: this.filters.date || new Date().toISOString().split('T')[0],
                default_statut: 'present',
                presences: {}
            };
            this.showBulkModal = true;
        },
        
        closeModal() {
            this.showModal = false;
            this.editingAttendance = null;
        },
        
        closeBulkModal() {
            this.showBulkModal = false;
            this.bulkForm = {
                classe_id: '',
                date: new Date().toISOString().split('T')[0],
                default_statut: 'present',
                presences: {}
            };
        },
        
        async saveAttendance() {
            this.loading = true;
            try {
                await new Promise(resolve => setTimeout(resolve, 1000));
                
                if (this.editingAttendance) {
                    // Update existing attendance
                    const index = this.attendances.findIndex(a => a.id === this.editingAttendance.id);
                    if (index !== -1) {
                        this.attendances[index] = {
                            ...this.attendances[index],
                            date: this.form.date,
                            statut: this.form.statut,
                            motif: this.form.motif
                        };
                    }
                } else {
                    // Create new attendance
                    const student = this.students.find(s => s.id == this.form.eleve_id);
                    this.attendances.push({
                        id: Date.now(),
                        date: this.form.date,
                        statut: this.form.statut,
                        motif: this.form.motif,
                        eleve: student,
                        classe: student.classe,
                        enseignant: { user: { name: 'Current User' } }
                    });
                }
                
                this.calculateStats();
                this.closeModal();
            } catch (error) {
                console.error('Error saving attendance:', error);
            } finally {
                this.loading = false;
            }
        },
        
        async saveBulkAttendance() {
            this.loading = true;
            try {
                await new Promise(resolve => setTimeout(resolve, 1000));
                
                // Create bulk attendances
                Object.entries(this.bulkForm.presences).forEach(([studentId, statut]) => {
                    const student = this.classStudents.find(s => s.id == studentId);
                    if (student) {
                        this.attendances.push({
                            id: Date.now() + parseInt(studentId),
                            date: this.bulkForm.date,
                            statut: statut,
                            motif: null,
                            eleve: student,
                            classe: student.classe,
                            enseignant: { user: { name: 'Current User' } }
                        });
                    }
                });
                
                this.calculateStats();
                this.closeBulkModal();
            } catch (error) {
                console.error('Error saving bulk attendance:', error);
            } finally {
                this.loading = false;
            }
        },
        
        async deleteAttendance(attendance) {
            if (confirm('Êtes-vous sûr de vouloir supprimer cette présence ?')) {
                try {
                    this.attendances = this.attendances.filter(a => a.id !== attendance.id);
                    this.calculateStats();
                } catch (error) {
                    console.error('Error deleting attendance:', error);
                }
            }
        },
        
        getStatusClass(statut) {
            const classes = {
                present: 'bg-green-100 text-green-800 dark:bg-green-900/20 dark:text-green-400',
                absent: 'bg-red-100 text-red-800 dark:bg-red-900/20 dark:text-red-400',
                retard: 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/20 dark:text-yellow-400',
                absent_justifie: 'bg-blue-100 text-blue-800 dark:bg-blue-900/20 dark:text-blue-400'
            };
            return classes[statut] || '';
        },
        
        getStatusText(statut) {
            const texts = {
                present: 'Présent',
                absent: 'Absent',
                retard: 'Retard',
                absent_justifie: 'Absent justifié'
            };
            return texts[statut] || statut;
        },
        
        formatDate(dateString) {
            const date = new Date(dateString);
            return date.toLocaleDateString('fr-FR');
        }
    }
}
</script>
@endsection
