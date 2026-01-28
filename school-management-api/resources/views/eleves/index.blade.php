@extends('layouts.app')

@section('title', 'Gestion des Élèves - School Manager')

@section('content')
<div x-data="studentsManager()" x-init="init()">
    <!-- Page Header -->
    <div class="mb-6 flex justify-between items-center">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Gestion des Élèves</h1>
            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                Gérez tous les élèves de l'établissement
            </p>
        </div>
        @if(auth()->user()->isAdmin())
        <button @click="openCreateModal()" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg flex items-center">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
            </svg>
            Nouvel Élève
        </button>
        @endif
    </div>

    <!-- Filters -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-4 mb-6 border border-gray-200 dark:border-gray-700">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Recherche</label>
                <input type="text" x-model="filters.search" @input="loadStudents()" 
                       placeholder="Rechercher un élève..." 
                       class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Classe</label>
                <select x-model="filters.classe_id" @change="loadStudents()" 
                        class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white">
                    <option value="">Toutes les classes</option>
                    <template x-for="classe in classes" :key="classe.id">
                        <option :value="classe.id" x-text="classe.nom"></option>
                    </template>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Genre</label>
                <select x-model="filters.sexe" @change="loadStudents()" 
                        class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white">
                    <option value="">Tous les genres</option>
                    <option value="M">Masculin</option>
                    <option value="F">Féminin</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Statut</label>
                <select x-model="filters.statut" @change="loadStudents()" 
                        class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white">
                    <option value="">Tous les statuts</option>
                    <option value="actif">Actif</option>
                    <option value="inactif">Inactif</option>
                </select>
            </div>
        </div>
    </div>

    <!-- Students Table -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm overflow-hidden border border-gray-200 dark:border-gray-700">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="bg-gray-50 dark:bg-gray-900">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                            Élève
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                            Matricule
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                            Classe
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                            Date de naissance
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                            Contact
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                            Statut
                        </th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                            Actions
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                    <template x-for="student in students" :key="student.id">
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0 h-10 w-10">
                                        <div class="h-10 w-10 rounded-full bg-gradient-to-r from-blue-500 to-purple-600 flex items-center justify-center">
                                            <span class="text-sm font-medium text-white" x-text="student.prenom.charAt(0) + student.nom.charAt(0)"></span>
                                        </div>
                                    </div>
                                    <div class="ml-4">
                                        <div class="text-sm font-medium text-gray-900 dark:text-white" x-text="student.prenom + ' ' + student.nom"></div>
                                        <div class="text-sm text-gray-500 dark:text-gray-400" x-text="student.sexe === 'M' ? 'Masculin' : 'Féminin'"></div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white" x-text="student.matricule"></td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 py-1 text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900/20 dark:text-blue-400 rounded-full" 
                                      x-text="student.classe?.nom || 'Non assigné'"></span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white" x-text="formatDate(student.date_naissance)"></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white" x-text="student.parent_contact || 'Non spécifié'"></td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 py-1 text-xs font-medium rounded-full"
                                      :class="student.statut === 'actif' ? 'bg-green-100 text-green-800 dark:bg-green-900/20 dark:text-green-400' : 'bg-red-100 text-red-800 dark:bg-red-900/20 dark:text-red-400'"
                                      x-text="student.statut === 'actif' ? 'Actif' : 'Inactif'"></span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <div class="flex justify-end space-x-2">
                                    <button @click="viewStudent(student)" class="text-blue-600 hover:text-blue-900 dark:text-blue-400 dark:hover:text-blue-300" title="Voir">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                        </svg>
                                    </button>
                                    @if(auth()->user()->isAdmin())
                                    <button @click="editStudent(student)" class="text-indigo-600 hover:text-indigo-900 dark:text-indigo-400 dark:hover:text-indigo-300" title="Modifier">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                        </svg>
                                    </button>
                                    <button @click="deleteStudent(student)" class="text-red-600 hover:text-red-900 dark:text-red-400 dark:hover:text-red-300" title="Supprimer">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                        </svg>
                                    </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    </template>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Create/Edit Modal -->
    <div x-show="showModal" x-transition:enter="transition ease-out duration-300" 
         x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-200" 
         x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
         class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">
        <div class="flex items-center justify-center min-h-screen px-4">
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75" @click="closeModal()"></div>
            
            <div class="relative bg-white dark:bg-gray-800 rounded-lg shadow-xl max-w-2xl w-full p-6 max-h-[90vh] overflow-y-auto">
                <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4" x-text="editingStudent ? 'Modifier l\'élève' : 'Nouvel élève'"></h3>
                
                <form @submit.prevent="saveStudent()">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Nom</label>
                            <input type="text" x-model="form.nom" required
                                   class="mt-1 block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white">
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Prénom</label>
                            <input type="text" x-model="form.prenom" required
                                   class="mt-1 block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white">
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Matricule</label>
                            <input type="text" x-model="form.matricule" required
                                   class="mt-1 block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white">
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Date de naissance</label>
                            <input type="date" x-model="form.date_naissance" required
                                   class="mt-1 block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white">
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Lieu de naissance</label>
                            <input type="text" x-model="form.lieu_naissance"
                                   class="mt-1 block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white">
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Genre</label>
                            <select x-model="form.sexe" required
                                    class="mt-1 block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white">
                                <option value="">Sélectionner</option>
                                <option value="M">Masculin</option>
                                <option value="F">Féminin</option>
                            </select>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Classe</label>
                            <select x-model="form.classe_id"
                                    class="mt-1 block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white">
                                <option value="">Sélectionner une classe</option>
                                <template x-for="classe in classes" :key="classe.id">
                                    <option :value="classe.id" x-text="classe.nom"></option>
                                </template>
                            </select>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Contact parent</label>
                            <input type="text" x-model="form.parent_contact"
                                   class="mt-1 block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white">
                        </div>
                        
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Adresse</label>
                            <input type="text" x-model="form.adresse"
                                   class="mt-1 block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white">
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Statut</label>
                            <select x-model="form.statut" required
                                    class="mt-1 block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white">
                                <option value="actif">Actif</option>
                                <option value="inactif">Inactif</option>
                            </select>
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
function studentsManager() {
    return {
        students: [],
        classes: [],
        showModal: false,
        editingStudent: null,
        loading: false,
        filters: {
            search: '',
            classe_id: '',
            sexe: '',
            statut: ''
        },
        form: {
            nom: '',
            prenom: '',
            matricule: '',
            date_naissance: '',
            lieu_naissance: '',
            sexe: '',
            classe_id: '',
            parent_contact: '',
            adresse: '',
            statut: 'actif'
        },
        
        async init() {
            await this.loadClasses();
            await this.loadStudents();
        },
        
        async loadClasses() {
            try {
                // Simuler des données - remplacer par de vrais appels API
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
                // Simuler des données - remplacer par de vrais appels API
                this.students = [
                    {
                        id: 1,
                        nom: 'Dupont',
                        prenom: 'Jean',
                        matricule: 'ELE2024001',
                        date_naissance: '2010-05-15',
                        lieu_naissance: 'Dakar',
                        sexe: 'M',
                        classe_id: 1,
                        classe: { id: 1, nom: '6ème A' },
                        parent_contact: '77 123 45 67',
                        adresse: 'Mermoz, Dakar',
                        statut: 'actif'
                    },
                    {
                        id: 2,
                        nom: 'Diop',
                        prenom: 'Fatou',
                        matricule: 'ELE2024002',
                        date_naissance: '2011-03-22',
                        lieu_naissance: 'Thiès',
                        sexe: 'F',
                        classe_id: 2,
                        classe: { id: 2, nom: '5ème B' },
                        parent_contact: '77 234 56 78',
                        adresse: 'Plateau, Dakar',
                        statut: 'actif'
                    },
                    {
                        id: 3,
                        nom: 'Ba',
                        prenom: 'Moussa',
                        matricule: 'ELE2024003',
                        date_naissance: '2012-08-10',
                        lieu_naissance: 'Saint-Louis',
                        sexe: 'M',
                        classe_id: 3,
                        classe: { id: 3, nom: 'CM1' },
                        parent_contact: '77 345 67 89',
                        adresse: 'Pikine, Dakar',
                        statut: 'actif'
                    }
                ];
            } catch (error) {
                console.error('Error loading students:', error);
            }
        },
        
        openCreateModal() {
            this.editingStudent = null;
            this.form = {
                nom: '',
                prenom: '',
                matricule: '',
                date_naissance: '',
                lieu_naissance: '',
                sexe: '',
                classe_id: '',
                parent_contact: '',
                adresse: '',
                statut: 'actif'
            };
            this.showModal = true;
        },
        
        editStudent(student) {
            this.editingStudent = student;
            this.form = { ...student };
            this.showModal = true;
        },
        
        closeModal() {
            this.showModal = false;
            this.editingStudent = null;
        },
        
        async saveStudent() {
            this.loading = true;
            try {
                // Simuler une sauvegarde - remplacer par de vrais appels API
                await new Promise(resolve => setTimeout(resolve, 1000));
                
                if (this.editingStudent) {
                    // Update existing student
                    const index = this.students.findIndex(s => s.id === this.editingStudent.id);
                    if (index !== -1) {
                        this.students[index] = { ...this.form, id: this.editingStudent.id };
                    }
                } else {
                    // Create new student
                    this.students.push({
                        ...this.form,
                        id: Date.now(),
                        classe: this.classes.find(c => c.id === this.form.classe_id)
                    });
                }
                
                this.closeModal();
                // Show success message
            } catch (error) {
                console.error('Error saving student:', error);
            } finally {
                this.loading = false;
            }
        },
        
        async deleteStudent(student) {
            if (confirm(`Êtes-vous sûr de vouloir supprimer l'élève "${student.prenom} ${student.nom}" ?`)) {
                try {
                    // Simuler une suppression - remplacer par de vrais appels API
                    this.students = this.students.filter(s => s.id !== student.id);
                } catch (error) {
                    console.error('Error deleting student:', error);
                }
            }
        },
        
        viewStudent(student) {
            // Navigate to student details
            window.location.href = `/eleves/${student.id}`;
        },
        
        formatDate(dateString) {
            const date = new Date(dateString);
            return date.toLocaleDateString('fr-FR');
        }
    }
}
</script>
@endsection
