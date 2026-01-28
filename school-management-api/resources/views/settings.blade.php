@extends('layouts.app')

@section('title', 'Paramètres - School Manager')

@section('content')
<div x-data="settingsManager()" x-init="init()">
    <!-- Page Header -->
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Paramètres</h1>
        <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
            Configuration générale du système
        </p>
    </div>

    <!-- Settings Tabs -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700">
        <div class="border-b border-gray-200 dark:border-gray-700">
            <nav class="flex -mb-px">
                <button @click="activeTab = 'general'" 
                        :class="activeTab === 'general' ? 'border-blue-500 text-blue-600 dark:text-blue-400' : 'border-transparent text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300'"
                        class="py-4 px-6 border-b-2 font-medium text-sm transition-colors duration-200">
                    Général
                </button>
                <button @click="activeTab = 'school'" 
                        :class="activeTab === 'school' ? 'border-blue-500 text-blue-600 dark:text-blue-400' : 'border-transparent text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300'"
                        class="py-4 px-6 border-b-2 font-medium text-sm transition-colors duration-200">
                    Établissement
                </button>
                <button @click="activeTab = 'academic'" 
                        :class="activeTab === 'academic' ? 'border-blue-500 text-blue-600 dark:text-blue-400' : 'border-transparent text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300'"
                        class="py-4 px-6 border-b-2 font-medium text-sm transition-colors duration-200">
                    Académique
                </button>
                <button @click="activeTab = 'notifications'" 
                        :class="activeTab === 'notifications' ? 'border-blue-500 text-blue-600 dark:text-blue-400' : 'border-transparent text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300'"
                        class="py-4 px-6 border-b-2 font-medium text-sm transition-colors duration-200">
                    Notifications
                </button>
                <button @click="activeTab = 'security'" 
                        :class="activeTab === 'security' ? 'border-blue-500 text-blue-600 dark:text-blue-400' : 'border-transparent text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300'"
                        class="py-4 px-6 border-b-2 font-medium text-sm transition-colors duration-200">
                    Sécurité
                </button>
            </nav>
        </div>

        <!-- Tab Content -->
        <div class="p-6">
            <!-- General Settings -->
            <div x-show="activeTab === 'general'" class="space-y-6">
                <h3 class="text-lg font-medium text-gray-900 dark:text-white">Paramètres généraux</h3>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Nom de l'application</label>
                        <input type="text" x-model="settings.app_name" 
                               class="mt-1 block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Email de support</label>
                        <input type="email" x-model="settings.support_email" 
                               class="mt-1 block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Fuseau horaire</label>
                        <select x-model="settings.timezone" 
                                class="mt-1 block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white">
                            <option value="UTC">UTC</option>
                            <option value="Africa/Dakar">Africa/Dakar</option>
                            <option value="Europe/Paris">Europe/Paris</option>
                            <option value="America/New_York">America/New_York</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Langue par défaut</label>
                        <select x-model="settings.default_language" 
                                class="mt-1 block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white">
                            <option value="fr">Français</option>
                            <option value="en">English</option>
                            <option value="ar">العربية</option>
                        </select>
                    </div>
                </div>
                
                <div class="flex items-center justify-between">
                    <div>
                        <h4 class="text-sm font-medium text-gray-900 dark:text-white">Maintenance mode</h4>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Activer le mode maintenance pour les utilisateurs</p>
                    </div>
                    <button @click="settings.maintenance_mode = !settings.maintenance_mode" 
                            class="relative inline-flex flex-shrink-0 h-6 w-11 border-2 border-transparent rounded-full cursor-pointer transition-colors ease-in-out duration-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                            :class="settings.maintenance_mode ? 'bg-blue-600' : 'bg-gray-200'">
                        <span class="translate-x-0 inline-block h-5 w-5 rounded-full bg-white shadow transform ring-0 transition ease-in-out duration-200"
                              :class="settings.maintenance_mode ? 'translate-x-5' : 'translate-x-0'"></span>
                    </button>
                </div>
            </div>

            <!-- School Settings -->
            <div x-show="activeTab === 'school'" class="space-y-6">
                <h3 class="text-lg font-medium text-gray-900 dark:text-white">Informations de l'établissement</h3>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Nom de l'établissement</label>
                        <input type="text" x-model="settings.school_name" 
                               class="mt-1 block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Type d'établissement</label>
                        <select x-model="settings.school_type" 
                                class="mt-1 block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white">
                            <option value="primaire">École primaire</option>
                            <option value="college">Collège</option>
                            <option value="lycee">Lycée</option>
                            <option value="primaire_college">Primaire et Collège</option>
                            <option value="college_lycee">Collège et Lycée</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Adresse</label>
                        <input type="text" x-model="settings.school_address" 
                               class="mt-1 block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Téléphone</label>
                        <input type="text" x-model="settings.school_phone" 
                               class="mt-1 block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Email</label>
                        <input type="email" x-model="settings.school_email" 
                               class="mt-1 block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Site web</label>
                        <input type="url" x-model="settings.school_website" 
                               class="mt-1 block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white">
                    </div>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Description</label>
                    <textarea x-model="settings.school_description" rows="3"
                              class="mt-1 block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white"></textarea>
                </div>
            </div>

            <!-- Academic Settings -->
            <div x-show="activeTab === 'academic'" class="space-y-6">
                <h3 class="text-lg font-medium text-gray-900 dark:text-white">Paramètres académiques</h3>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Année scolaire actuelle</label>
                        <input type="text" x-model="settings.current_academic_year" 
                               placeholder="2024-2025"
                               class="mt-1 block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Nombre de trimestres</label>
                        <select x-model="settings.trimesters_count" 
                                class="mt-1 block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white">
                            <option value="2">2 trimestres</option>
                            <option value="3">3 trimestres</option>
                            <option value="4">4 trimestres</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Note minimale de réussite</label>
                        <input type="number" x-model="settings.passing_grade" min="0" max="20" step="0.5"
                               class="mt-1 block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Note maximale</label>
                        <input type="number" x-model="settings.max_grade" min="0" max="100" step="1"
                               class="mt-1 block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white">
                    </div>
                </div>
                
                <div class="space-y-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <h4 class="text-sm font-medium text-gray-900 dark:text-white">Calcul automatique des moyennes</h4>
                            <p class="text-sm text-gray-500 dark:text-gray-400">Calculer automatiquement les moyennes trimestrielles et annuelles</p>
                        </div>
                        <button @click="settings.auto_calculate_averages = !settings.auto_calculate_averages" 
                                class="relative inline-flex flex-shrink-0 h-6 w-11 border-2 border-transparent rounded-full cursor-pointer transition-colors ease-in-out duration-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                                :class="settings.auto_calculate_averages ? 'bg-blue-600' : 'bg-gray-200'">
                            <span class="translate-x-0 inline-block h-5 w-5 rounded-full bg-white shadow transform ring-0 transition ease-in-out duration-200"
                                  :class="settings.auto_calculate_averages ? 'translate-x-5' : 'translate-x-0'"></span>
                        </button>
                    </div>
                    
                    <div class="flex items-center justify-between">
                        <div>
                            <h4 class="text-sm font-medium text-gray-900 dark:text-white">Publication automatique des bulletins</h4>
                            <p class="text-sm text-gray-500 dark:text-gray-400">Publier automatiquement les bulletins à la fin de chaque trimestre</p>
                        </div>
                        <button @click="settings.auto_publish_reports = !settings.auto_publish_reports" 
                                class="relative inline-flex flex-shrink-0 h-6 w-11 border-2 border-transparent rounded-full cursor-pointer transition-colors ease-in-out duration-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                                :class="settings.auto_publish_reports ? 'bg-blue-600' : 'bg-gray-200'">
                            <span class="translate-x-0 inline-block h-5 w-5 rounded-full bg-white shadow transform ring-0 transition ease-in-out duration-200"
                                  :class="settings.auto_publish_reports ? 'translate-x-5' : 'translate-x-0'"></span>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Notification Settings -->
            <div x-show="activeTab === 'notifications'" class="space-y-6">
                <h3 class="text-lg font-medium text-gray-900 dark:text-white">Configuration des notifications</h3>
                
                <div class="space-y-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <h4 class="text-sm font-medium text-gray-900 dark:text-white">Notifications par email</h4>
                            <p class="text-sm text-gray-500 dark:text-gray-400">Activer les notifications par email pour tous les utilisateurs</p>
                        </div>
                        <button @click="settings.email_notifications = !settings.email_notifications" 
                                class="relative inline-flex flex-shrink-0 h-6 w-11 border-2 border-transparent rounded-full cursor-pointer transition-colors ease-in-out duration-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                                :class="settings.email_notifications ? 'bg-blue-600' : 'bg-gray-200'">
                            <span class="translate-x-0 inline-block h-5 w-5 rounded-full bg-white shadow transform ring-0 transition ease-in-out duration-200"
                                  :class="settings.email_notifications ? 'translate-x-5' : 'translate-x-0'"></span>
                        </button>
                    </div>
                    
                    <div class="flex items-center justify-between">
                        <div>
                            <h4 class="text-sm font-medium text-gray-900 dark:text-white">Notifications SMS</h4>
                            <p class="text-sm text-gray-500 dark:text-gray-400">Activer les notifications SMS pour les absences et urgences</p>
                        </div>
                        <button @click="settings.sms_notifications = !settings.sms_notifications" 
                                class="relative inline-flex flex-shrink-0 h-6 w-11 border-2 border-transparent rounded-full cursor-pointer transition-colors ease-in-out duration-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                                :class="settings.sms_notifications ? 'bg-blue-600' : 'bg-gray-200'">
                            <span class="translate-x-0 inline-block h-5 w-5 rounded-full bg-white shadow transform ring-0 transition ease-in-out duration-200"
                                  :class="settings.sms_notifications ? 'translate-x-5' : 'translate-x-0'"></span>
                        </button>
                    </div>
                    
                    <div class="flex items-center justify-between">
                        <div>
                            <h4 class="text-sm font-medium text-gray-900 dark:text-white">Notifications push</h4>
                            <p class="text-sm text-gray-500 dark:text-gray-400">Activer les notifications push dans l'application</p>
                        </div>
                        <button @click="settings.push_notifications = !settings.push_notifications" 
                                class="relative inline-flex flex-shrink-0 h-6 w-11 border-2 border-transparent rounded-full cursor-pointer transition-colors ease-in-out duration-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                                :class="settings.push_notifications ? 'bg-blue-600' : 'bg-gray-200'">
                            <span class="translate-x-0 inline-block h-5 w-5 rounded-full bg-white shadow transform ring-0 transition ease-in-out duration-200"
                                  :class="settings.push_notifications ? 'translate-x-5' : 'translate-x-0'"></span>
                        </button>
                    </div>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Email pour les notifications système</label>
                        <input type="email" x-model="settings.system_email" 
                               class="mt-1 block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Fréquence des rapports</label>
                        <select x-model="settings.report_frequency" 
                                class="mt-1 block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white">
                            <option value="daily">Quotidien</option>
                            <option value="weekly">Hebdomadaire</option>
                            <option value="monthly">Mensuel</option>
                            <option value="never">Jamais</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Security Settings -->
            <div x-show="activeTab === 'security'" class="space-y-6">
                <h3 class="text-lg font-medium text-gray-900 dark:text-white">Paramètres de sécurité</h3>
                
                <div class="space-y-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <h4 class="text-sm font-medium text-gray-900 dark:text-white">Authentification à deux facteurs</h4>
                            <p class="text-sm text-gray-500 dark:text-gray-400">Exiger l'authentification à deux facteurs pour tous les utilisateurs</p>
                        </div>
                        <button @click="settings.two_factor_auth = !settings.two_factor_auth" 
                                class="relative inline-flex flex-shrink-0 h-6 w-11 border-2 border-transparent rounded-full cursor-pointer transition-colors ease-in-out duration-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                                :class="settings.two_factor_auth ? 'bg-blue-600' : 'bg-gray-200'">
                            <span class="translate-x-0 inline-block h-5 w-5 rounded-full bg-white shadow transform ring-0 transition ease-in-out duration-200"
                                  :class="settings.two_factor_auth ? 'translate-x-5' : 'translate-x-0'"></span>
                        </button>
                    </div>
                    
                    <div class="flex items-center justify-between">
                        <div>
                            <h4 class="text-sm font-medium text-gray-900 dark:text-white">Session timeout</h4>
                            <p class="text-sm text-gray-500 dark:text-gray-400">Déconnecter automatiquement les utilisateurs après inactivité</p>
                        </div>
                        <button @click="settings.session_timeout = !settings.session_timeout" 
                                class="relative inline-flex flex-shrink-0 h-6 w-11 border-2 border-transparent rounded-full cursor-pointer transition-colors ease-in-out duration-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                                :class="settings.session_timeout ? 'bg-blue-600' : 'bg-gray-200'">
                            <span class="translate-x-0 inline-block h-5 w-5 rounded-full bg-white shadow transform ring-0 transition ease-in-out duration-200"
                                  :class="settings.session_timeout ? 'translate-x-5' : 'translate-x-0'"></span>
                        </button>
                    </div>
                    
                    <div class="flex items-center justify-between">
                        <div>
                            <h4 class="text-sm font-medium text-gray-900 dark:text-white">Log des activités</h4>
                            <p class="text-sm text-gray-500 dark:text-gray-400">Enregistrer toutes les activités des utilisateurs dans les logs</p>
                        </div>
                        <button @click="settings.activity_logging = !settings.activity_logging" 
                                class="relative inline-flex flex-shrink-0 h-6 w-11 border-2 border-transparent rounded-full cursor-pointer transition-colors ease-in-out duration-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                                :class="settings.activity_logging ? 'bg-blue-600' : 'bg-gray-200'">
                            <span class="translate-x-0 inline-block h-5 w-5 rounded-full bg-white shadow transform ring-0 transition ease-in-out duration-200"
                                  :class="settings.activity_logging ? 'translate-x-5' : 'translate-x-0'"></span>
                        </button>
                    </div>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Durée de session (minutes)</label>
                        <input type="number" x-model="settings.session_duration" min="5" max="1440"
                               class="mt-1 block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Tentatives de connexion max</label>
                        <input type="number" x-model="settings.max_login_attempts" min="3" max="10"
                               class="mt-1 block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white">
                    </div>
                </div>
            </div>

            <!-- Save Button -->
            <div class="flex justify-end pt-6 border-t border-gray-200 dark:border-gray-700">
                <button @click="saveSettings()" :disabled="loading"
                        class="px-6 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700 disabled:opacity-50">
                    <span x-show="!loading">Enregistrer les paramètres</span>
                    <span x-show="loading">Enregistrement...</span>
                </button>
            </div>
        </div>
    </div>
</div>

<script>
function settingsManager() {
    return {
        activeTab: 'general',
        loading: false,
        settings: {
            // General
            app_name: 'School Manager',
            support_email: 'support@school.com',
            timezone: 'Africa/Dakar',
            default_language: 'fr',
            maintenance_mode: false,
            
            // School
            school_name: 'École Exemple',
            school_type: 'college',
            school_address: '123 Rue de l\'École, Dakar',
            school_phone: '+221 33 123 45 67',
            school_email: 'contact@ecole-exemple.sn',
            school_website: 'https://www.ecole-exemple.sn',
            school_description: 'Une école d\'excellence dédiée à l\'éducation de qualité.',
            
            // Academic
            current_academic_year: '2024-2025',
            trimesters_count: 3,
            passing_grade: 10,
            max_grade: 20,
            auto_calculate_averages: true,
            auto_publish_reports: false,
            
            // Notifications
            email_notifications: true,
            sms_notifications: false,
            push_notifications: true,
            system_email: 'system@school.com',
            report_frequency: 'weekly',
            
            // Security
            two_factor_auth: false,
            session_timeout: true,
            activity_logging: true,
            session_duration: 120,
            max_login_attempts: 5
        },
        
        init() {
            // Load settings from localStorage or API
            this.loadSettings();
        },
        
        loadSettings() {
            // In a real app, this would load from an API
            const saved = localStorage.getItem('settings');
            if (saved) {
                this.settings = { ...this.settings, ...JSON.parse(saved) };
            }
        },
        
        async saveSettings() {
            this.loading = true;
            try {
                // Simulate API call
                await new Promise(resolve => setTimeout(resolve, 1000));
                
                // Save to localStorage
                localStorage.setItem('settings', JSON.stringify(this.settings));
                
                // Show success message
                this.showSuccess('Paramètres enregistrés avec succès');
            } catch (error) {
                console.error('Error saving settings:', error);
                this.showError('Erreur lors de l\'enregistrement des paramètres');
            } finally {
                this.loading = false;
            }
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
