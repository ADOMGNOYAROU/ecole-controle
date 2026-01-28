@extends('layouts.app')

@section('title', 'Profile - School Manager')

@section('content')
<div x-data="profileManager()" x-init="init()">
    <!-- Page Header -->
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Mon Profile</h1>
        <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
            Gérez vos informations personnelles et préférences
        </p>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Profile Card -->
        <div class="lg:col-span-1">
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-6 border border-gray-200 dark:border-gray-700">
                <div class="text-center">
                    <div class="mx-auto h-24 w-24 bg-gradient-to-r from-blue-500 to-purple-600 rounded-full flex items-center justify-center">
                        <span class="text-3xl font-bold text-white" x-text="user.name.charAt(0).toUpperCase()"></span>
                    </div>
                    <h3 class="mt-4 text-lg font-medium text-gray-900 dark:text-white" x-text="user.name"></h3>
                    <p class="text-sm text-gray-500 dark:text-gray-400" x-text="user.email"></p>
                    <span class="mt-2 inline-flex px-2 py-1 text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900/20 dark:text-blue-400 rounded-full" x-text="getRoleText(user.role)"></span>
                </div>
                
                <div class="mt-6 space-y-3">
                    <div class="flex items-center text-sm text-gray-600 dark:text-gray-400">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                        </svg>
                        <span x-text="user.email"></span>
                    </div>
                    <div class="flex items-center text-sm text-gray-600 dark:text-gray-400" x-show="user.telephone">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path>
                        </svg>
                        <span x-text="user.telephone"></span>
                    </div>
                    <div class="flex items-center text-sm text-gray-600 dark:text-gray-400">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                        </svg>
                        <span>Membre depuis <span x-text="formatDate(user.created_at)"></span></span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Profile Form -->
        <div class="lg:col-span-2">
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700">
                <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white">Informations personnelles</h3>
                </div>
                <form @submit.prevent="updateProfile()" class="p-6 space-y-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Nom</label>
                            <input type="text" x-model="profileForm.name" required
                                   class="mt-1 block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Email</label>
                            <input type="email" x-model="profileForm.email" required
                                   class="mt-1 block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Téléphone</label>
                            <input type="text" x-model="profileForm.telephone"
                                   class="mt-1 block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Adresse</label>
                            <input type="text" x-model="profileForm.adresse"
                                   class="mt-1 block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white">
                        </div>
                    </div>
                    
                    <div class="flex justify-end">
                        <button type="submit" :disabled="loading"
                                class="px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700 disabled:opacity-50">
                            <span x-show="!loading">Mettre à jour</span>
                            <span x-show="loading">Mise à jour...</span>
                        </button>
                    </div>
                </form>
            </div>

            <!-- Password Change -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 mt-6">
                <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white">Changer le mot de passe</h3>
                </div>
                <form @submit.prevent="changePassword()" class="p-6 space-y-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Mot de passe actuel</label>
                            <input type="password" x-model="passwordForm.current_password" required
                                   class="mt-1 block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Nouveau mot de passe</label>
                            <input type="password" x-model="passwordForm.password" required minlength="8"
                                   class="mt-1 block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white">
                        </div>
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Confirmer le nouveau mot de passe</label>
                            <input type="password" x-model="passwordForm.password_confirmation" required
                                   class="mt-1 block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white">
                        </div>
                    </div>
                    
                    <div class="flex justify-end">
                        <button type="submit" :disabled="passwordLoading"
                                class="px-4 py-2 text-sm font-medium text-white bg-red-600 rounded-lg hover:bg-red-700 disabled:opacity-50">
                            <span x-show="!passwordLoading">Changer le mot de passe</span>
                            <span x-show="passwordLoading">Changement...</span>
                        </button>
                    </div>
                </form>
            </div>

            <!-- Preferences -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 mt-6">
                <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white">Préférences</h3>
                </div>
                <div class="p-6 space-y-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <h4 class="text-sm font-medium text-gray-900 dark:text-white">Mode sombre</h4>
                            <p class="text-sm text-gray-500 dark:text-gray-400">Activer le thème sombre pour l'interface</p>
                        </div>
                        <button @click="toggleDarkMode()" 
                                class="relative inline-flex flex-shrink-0 h-6 w-11 border-2 border-transparent rounded-full cursor-pointer transition-colors ease-in-out duration-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                                :class="darkMode ? 'bg-blue-600' : 'bg-gray-200'">
                            <span class="translate-x-0 inline-block h-5 w-5 rounded-full bg-white shadow transform ring-0 transition ease-in-out duration-200"
                                  :class="darkMode ? 'translate-x-5' : 'translate-x-0'"></span>
                        </button>
                    </div>
                    
                    <div class="flex items-center justify-between">
                        <div>
                            <h4 class="text-sm font-medium text-gray-900 dark:text-white">Notifications email</h4>
                            <p class="text-sm text-gray-500 dark:text-gray-400">Recevoir des notifications par email</p>
                        </div>
                        <button @click="toggleNotifications()" 
                                class="relative inline-flex flex-shrink-0 h-6 w-11 border-2 border-transparent rounded-full cursor-pointer transition-colors ease-in-out duration-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                                :class="notifications ? 'bg-blue-600' : 'bg-gray-200'">
                            <span class="translate-x-0 inline-block h-5 w-5 rounded-full bg-white shadow transform ring-0 transition ease-in-out duration-200"
                                  :class="notifications ? 'translate-x-5' : 'translate-x-0'"></span>
                        </button>
                    </div>
                    
                    <div class="flex items-center justify-between">
                        <div>
                            <h4 class="text-sm font-medium text-gray-900 dark:text-white">Langue</h4>
                            <p class="text-sm text-gray-500 dark:text-gray-400">Choisir la langue de l'interface</p>
                        </div>
                        <select x-model="language" @change="changeLanguage()"
                                class="px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white">
                            <option value="fr">Français</option>
                            <option value="en">English</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function profileManager() {
    return {
        user: {
            name: 'John Doe',
            email: 'john.doe@school.com',
            telephone: '77 123 45 67',
            role: 'admin',
            created_at: '2023-01-15'
        },
        profileForm: {
            name: '',
            email: '',
            telephone: '',
            adresse: ''
        },
        passwordForm: {
            current_password: '',
            password: '',
            password_confirmation: ''
        },
        loading: false,
        passwordLoading: false,
        darkMode: false,
        notifications: true,
        language: 'fr',
        
        init() {
            // Initialize form with user data
            this.profileForm = {
                name: this.user.name,
                email: this.user.email,
                telephone: this.user.telephone || '',
                adresse: this.user.adresse || ''
            };
            
            // Check for saved preferences
            this.darkMode = localStorage.getItem('darkMode') === 'true';
            this.notifications = localStorage.getItem('notifications') !== 'false';
            this.language = localStorage.getItem('language') || 'fr';
            
            // Apply dark mode
            if (this.darkMode) {
                document.documentElement.classList.add('dark');
            }
        },
        
        async updateProfile() {
            this.loading = true;
            try {
                // Simulate API call
                await new Promise(resolve => setTimeout(resolve, 1000));
                
                // Update user data
                this.user.name = this.profileForm.name;
                this.user.email = this.profileForm.email;
                this.user.telephone = this.profileForm.telephone;
                this.user.adresse = this.profileForm.adresse;
                
                // Show success message
                this.showSuccess('Profile mis à jour avec succès');
            } catch (error) {
                console.error('Error updating profile:', error);
                this.showError('Erreur lors de la mise à jour du profile');
            } finally {
                this.loading = false;
            }
        },
        
        async changePassword() {
            if (this.passwordForm.password !== this.passwordForm.password_confirmation) {
                this.showError('Les mots de passe ne correspondent pas');
                return;
            }
            
            this.passwordLoading = true;
            try {
                // Simulate API call
                await new Promise(resolve => setTimeout(resolve, 1000));
                
                // Clear form
                this.passwordForm = {
                    current_password: '',
                    password: '',
                    password_confirmation: ''
                };
                
                this.showSuccess('Mot de passe changé avec succès');
            } catch (error) {
                console.error('Error changing password:', error);
                this.showError('Erreur lors du changement de mot de passe');
            } finally {
                this.passwordLoading = false;
            }
        },
        
        toggleDarkMode() {
            this.darkMode = !this.darkMode;
            localStorage.setItem('darkMode', this.darkMode);
            
            if (this.darkMode) {
                document.documentElement.classList.add('dark');
            } else {
                document.documentElement.classList.remove('dark');
            }
        },
        
        toggleNotifications() {
            this.notifications = !this.notifications;
            localStorage.setItem('notifications', this.notifications);
        },
        
        changeLanguage() {
            localStorage.setItem('language', this.language);
            // In a real app, this would reload the page or change the language dynamically
        },
        
        getRoleText(role) {
            const roles = {
                admin: 'Administrateur',
                enseignant: 'Enseignant',
                eleve: 'Élève',
                parent: 'Parent'
            };
            return roles[role] || role;
        },
        
        formatDate(dateString) {
            const date = new Date(dateString);
            return date.toLocaleDateString('fr-FR');
        },
        
        showSuccess(message) {
            // This would show a success notification
            console.log('Success:', message);
        },
        
        showError(message) {
            // This would show an error notification
            console.log('Error:', message);
        }
    }
}
</script>
@endsection
