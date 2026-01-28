@extends('layouts.app')

@section('title', 'Gestion des Enseignants - School Manager')

@section('content')
<div x-data="teachersManager()" x-init="init()">
    <!-- Page Header -->
    <div class="mb-6 flex justify-between items-center">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Gestion des Enseignants</h1>
            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                Gérez tous les enseignants de l'établissement
            </p>
        </div>
        @if(auth()->user()->isAdmin())
        <button @click="openCreateModal()" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg flex items-center">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
            </svg>
            Nouvel Enseignant
        </button>
        @endif
    </div>

    <!-- Filters -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-4 mb-6 border border-gray-200 dark:border-gray-700">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Recherche</label>
                <input type="text" x-model="filters.search" @input="loadTeachers()" 
                       placeholder="Rechercher un enseignant..." 
                       class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Spécialité</label>
                <select x-model="filters.specialite" @change="loadTeachers()" 
                        class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white">
                    <option value="">Toutes les spécialités</option>
                    <option value="Mathématiques">Mathématiques</option>
                    <option value="Physique">Physique</option>
                    <option value="Chimie">Chimie</option>
                    <option value="Français">Français</option>
                    <option value="Anglais">Anglais</option>
                    <option value="Histoire">Histoire</option>
                    <option value="Géographie">Géographie</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Statut</label>
                <select x-model="filters.statut" @change="loadTeachers()" 
                        class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white">
                    <option value="">Tous les statuts</option>
                    <option value="actif">Actif</option>
                    <option value="inactif">Inactif</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Classes assignées</label>
                <select x-model="filters.has_classes" @change="loadTeachers()" 
                        class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white">
                    <option value="">Tous</option>
                    <option value="yes">Avec classes</option>
                    <option value="no">Sans classes</option>
                </select>
            </div>
        </div>
    </div>

    <!-- Teachers Table -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm overflow-hidden border border-gray-200 dark:border-gray-700">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="bg-gray-50 dark:bg-gray-900">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                            Enseignant
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                            Matricule
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                            Spécialité
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                            Classes
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                            Contact
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                            Date d'embauche
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
                    <template x-for="teacher in teachers" :key="teacher.id">
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0 h-10 w-10">
                                        <div class="h-10 w-10 rounded-full bg-gradient-to-r from-green-500 to-teal-600 flex items-center justify-center">
                                            <span class="text-sm font-medium text-white" x-text="teacher.user.name.charAt(0)"></span>
                                        </div>
                                    </div>
                                    <div class="ml-4">
                                        <div class="text-sm font-medium text-gray-900 dark:text-white" x-text="teacher.user.name"></div>
                                        <div class="text-sm text-gray-500 dark:text-gray-400" x-text="teacher.user.email"></div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white" x-text="teacher.matricule"></td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 py-1 text-xs font-medium bg-purple-100 text-purple-800 dark:bg-purple-900/20 dark:text-purple-400 rounded-full" x-text="teacher.specialite || 'Non spécifiée'"></span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900 dark:text-white">
                                    <span x-text="teacher.classes ? teacher.classes.length : 0"></span> classe(s)
                                </div>
                                <div class="text-xs text-gray-500 dark:text-gray-400" x-show="teacher.classes && teacher.classes.length > 0">
                                    <template x-for="classe in teacher.classes.slice(0, 2)" :key="classe.id">
                                        <span x-text="classe.nom"></span><span x-show="$index < Math.min(teacher.classes.length - 1, 1)">, </span>
                                    </template>
                                    <span x-show="teacher.classes && teacher.classes.length > 2">...</span>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white" x-text="teacher.user.telephone || 'Non spécifié'"></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white" x-text="formatDate(teacher.date_embauche)"></td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 py-1 text-xs font-medium rounded-full"
                                      :class="teacher.statut === 'actif' ? 'bg-green-100 text-green-800 dark:bg-green-900/20 dark:text-green-400' : 'bg-red-100 text-red-800 dark:bg-red-900/20 dark:text-red-400'"
                                      x-text="teacher.statut === 'actif' ? 'Actif' : 'Inactif'"></span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <div class="flex justify-end space-x-2">
                                    <button @click="viewTeacher(teacher)" class="text-blue-600 hover:text-blue-900 dark:text-blue-400 dark:hover:text-blue-300" title="Voir">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                        </svg>
                                    </button>
                                    @if(auth()->user()->isAdmin())
                                    <button @click="editTeacher(teacher)" class="text-indigo-600 hover:text-indigo-900 dark:text-indigo-400 dark:hover:text-indigo-300" title="Modifier">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                        </svg>
                                    </button>
                                    <button @click="deleteTeacher(teacher)" class="text-red-600 hover:text-red-900 dark:text-red-400 dark:hover:text-red-300" title="Supprimer">
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
                <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4" x-text="editingTeacher ? 'Modifier l\'enseignant' : 'Nouvel enseignant'"></h3>
                
                <form @submit.prevent="saveTeacher()">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Nom</label>
                            <input type="text" x-model="form.name" required
                                   class="mt-1 block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white">
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Email</label>
                            <input type="email" x-model="form.email" required
                                   class="mt-1 block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white">
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Téléphone</label>
                            <input type="text" x-model="form.telephone"
                                   class="mt-1 block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white">
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Matricule</label>
                            <input type="text" x-model="form.matricule" required
                                   class="mt-1 block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white">
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Spécialité</label>
                            <select x-model="form.specialite"
                                    class="mt-1 block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white">
                                <option value="">Sélectionner une spécialité</option>
                                <option value="Mathématiques">Mathématiques</option>
                                <option value="Physique">Physique</option>
                                <option value="Chimie">Chimie</option>
                                <option value="Français">Français</option>
                                <option value="Anglais">Anglais</option>
                                <option value="Histoire">Histoire</option>
                                <option value="Géographie">Géographie</option>
                            </select>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Date d'embauche</label>
                            <input type="date" x-model="form.date_embauche"
                                   class="mt-1 block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white">
                        </div>
                        
                        <div x-show="!editingTeacher">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Mot de passe</label>
                            <input type="password" x-model="form.password" :required="!editingTeacher"
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
function teachersManager() {
    return {
        teachers: [],
        showModal: false,
        editingTeacher: null,
        loading: false,
        filters: {
            search: '',
            specialite: '',
            statut: '',
            has_classes: ''
        },
        form: {
            name: '',
            email: '',
            telephone: '',
            matricule: '',
            specialite: '',
            date_embauche: '',
            password: '',
            statut: 'actif'
        },
        
        async init() {
            await this.loadTeachers();
        },
        
        async loadTeachers() {
            try {
                // Simuler des données - remplacer par de vrais appels API
                this.teachers = [
                    {
                        id: 1,
                        matricule: 'ENS2024001',
                        specialite: 'Mathématiques',
                        date_embauche: '2020-09-01',
                        statut: 'actif',
                        user: {
                            id: 1,
                            name: 'Martin Dupont',
                            email: 'martin.dupont@school.com',
                            telephone: '77 123 45 67'
                        },
                        classes: [
                            { id: 1, nom: '6ème A' },
                            { id: 2, nom: '5ème B' }
                        ]
                    },
                    {
                        id: 2,
                        matricule: 'ENS2024002',
                        specialite: 'Français',
                        date_embauche: '2019-08-15',
                        statut: 'actif',
                        user: {
                            id: 2,
                            name: 'Sophie Martin',
                            email: 'sophie.martin@school.com',
                            telephone: '77 234 56 78'
                        },
                        classes: [
                            { id: 3, nom: 'CM1' },
                            { id: 4, nom: 'CP' }
                        ]
                    },
                    {
                        id: 3,
                        matricule: 'ENS2024003',
                        specialite: 'Physique',
                        date_embauche: '2021-10-10',
                        statut: 'actif',
                        user: {
                            id: 3,
                            name: 'Pierre Bernard',
                            email: 'pierre.bernard@school.com',
                            telephone: '77 345 67 89'
                        },
                        classes: []
                    }
                ];
            } catch (error) {
                console.error('Error loading teachers:', error);
            }
        },
        
        openCreateModal() {
            this.editingTeacher = null;
            this.form = {
                name: '',
                email: '',
                telephone: '',
                matricule: '',
                specialite: '',
                date_embauche: '',
                password: '',
                statut: 'actif'
            };
            this.showModal = true;
        },
        
        editTeacher(teacher) {
            this.editingTeacher = teacher;
            this.form = {
                name: teacher.user.name,
                email: teacher.user.email,
                telephone: teacher.user.telephone,
                matricule: teacher.matricule,
                specialite: teacher.specialite,
                date_embauche: teacher.date_embauche,
                password: '',
                statut: teacher.statut
            };
            this.showModal = true;
        },
        
        closeModal() {
            this.showModal = false;
            this.editingTeacher = null;
        },
        
        async saveTeacher() {
            this.loading = true;
            try {
                // Simuler une sauvegarde - remplacer par de vrais appels API
                await new Promise(resolve => setTimeout(resolve, 1000));
                
                if (this.editingTeacher) {
                    // Update existing teacher
                    const index = this.teachers.findIndex(t => t.id === this.editingTeacher.id);
                    if (index !== -1) {
                        this.teachers[index] = {
                            ...this.teachers[index],
                            matricule: this.form.matricule,
                            specialite: this.form.specialite,
                            date_embauche: this.form.date_embauche,
                            statut: this.form.statut,
                            user: {
                                ...this.teachers[index].user,
                                name: this.form.name,
                                email: this.form.email,
                                telephone: this.form.telephone
                            }
                        };
                    }
                } else {
                    // Create new teacher
                    this.teachers.push({
                        id: Date.now(),
                        matricule: this.form.matricule,
                        specialite: this.form.specialite,
                        date_embauche: this.form.date_embauche,
                        statut: this.form.statut,
                        user: {
                            id: Date.now(),
                            name: this.form.name,
                            email: this.form.email,
                            telephone: this.form.telephone
                        },
                        classes: []
                    });
                }
                
                this.closeModal();
                // Show success message
            } catch (error) {
                console.error('Error saving teacher:', error);
            } finally {
                this.loading = false;
            }
        },
        
        async deleteTeacher(teacher) {
            if (confirm(`Êtes-vous sûr de vouloir supprimer l'enseignant "${teacher.user.name}" ?`)) {
                try {
                    // Simuler une suppression - remplacer par de vrais appels API
                    this.teachers = this.teachers.filter(t => t.id !== teacher.id);
                } catch (error) {
                    console.error('Error deleting teacher:', error);
                }
            }
        },
        
        viewTeacher(teacher) {
            // Navigate to teacher details
            window.location.href = `/enseignants/${teacher.id}`;
        },
        
        formatDate(dateString) {
            const date = new Date(dateString);
            return date.toLocaleDateString('fr-FR');
        }
    }
}
</script>
@endsection
