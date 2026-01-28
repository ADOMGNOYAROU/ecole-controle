@extends('layouts.app')

@section('title', 'Tableau de Bord')

@section('content')
<div class="container mx-auto px-4 py-6">
    <div class="mb-8">
        <h1 class="text-2xl font-bold text-gray-800 dark:text-white mb-2">
            Bonjour, {{ Auth::user()->name }} 👋
        </h1>
        <p class="text-gray-600 dark:text-gray-300">
            {{ now()->translatedFormat('l d F Y') }} • {{ now()->format('H:i') }}
        </p>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <!-- Élèves Card -->
        <div class="group relative bg-white dark:bg-gray-800 rounded-2xl shadow-lg hover:shadow-xl transition-all duration-300 overflow-hidden">
            <div class="absolute inset-0 bg-gradient-to-br from-blue-500 to-blue-600 opacity-0 group-hover:opacity-5 transition-opacity"></div>
            <div class="relative p-6">
                <div class="flex items-center justify-between mb-4">
                    <div class="w-12 h-12 bg-blue-100 dark:bg-blue-900/30 rounded-xl flex items-center justify-center group-hover:scale-110 transition-transform">
                        <svg class="w-6 h-6 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                        </svg>
                    </div>
                    <span class="text-xs font-medium text-blue-600 dark:text-blue-400 bg-blue-50 dark:bg-blue-900/20 px-2 py-1 rounded-full">
                        +12%
                    </span>
                </div>
                <h3 class="text-2xl font-bold text-gray-900 dark:text-white mb-1">
                    {{ number_format($stats['eleves'] ?? 0) }}
                </h3>
                <p class="text-sm text-gray-600 dark:text-gray-400">Élèves Inscrits</p>
            </div>
        </div>

        <!-- Enseignants Card -->
        <div class="group relative bg-white dark:bg-gray-800 rounded-2xl shadow-lg hover:shadow-xl transition-all duration-300 overflow-hidden">
            <div class="absolute inset-0 bg-gradient-to-br from-green-500 to-green-600 opacity-0 group-hover:opacity-5 transition-opacity"></div>
            <div class="relative p-6">
                <div class="flex items-center justify-between mb-4">
                    <div class="w-12 h-12 bg-green-100 dark:bg-green-900/30 rounded-xl flex items-center justify-center group-hover:scale-110 transition-transform">
                        <svg class="w-6 h-6 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                        </svg>
                    </div>
                    <span class="text-xs font-medium text-green-600 dark:text-green-400 bg-green-50 dark:bg-green-900/20 px-2 py-1 rounded-full">
                        +5%
                    </span>
                </div>
                <h3 class="text-2xl font-bold text-gray-900 dark:text-white mb-1">
                    {{ number_format($stats['enseignants'] ?? 0) }}
                </h3>
                <p class="text-sm text-gray-600 dark:text-gray-400">Enseignants</p>
            </div>
        </div>

        <!-- Classes Card -->
        <div class="group relative bg-white dark:bg-gray-800 rounded-2xl shadow-lg hover:shadow-xl transition-all duration-300 overflow-hidden">
            <div class="absolute inset-0 bg-gradient-to-br from-purple-500 to-purple-600 opacity-0 group-hover:opacity-5 transition-opacity"></div>
            <div class="relative p-6">
                <div class="flex items-center justify-between mb-4">
                    <div class="w-12 h-12 bg-purple-100 dark:bg-purple-900/30 rounded-xl flex items-center justify-center group-hover:scale-110 transition-transform">
                        <svg class="w-6 h-6 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                        </svg>
                    </div>
                    <span class="text-xs font-medium text-purple-600 dark:text-purple-400 bg-purple-50 dark:bg-purple-900/20 px-2 py-1 rounded-full">
                        +3
                    </span>
                </div>
                <h3 class="text-2xl font-bold text-gray-900 dark:text-white mb-1">
                    {{ number_format($stats['classes'] ?? 0) }}
                </h3>
                <p class="text-sm text-gray-600 dark:text-gray-400">Classes</p>
            </div>
        </div>

        <!-- Présences Aujourd'hui Card -->
        <div class="group relative bg-white dark:bg-gray-800 rounded-2xl shadow-lg hover:shadow-xl transition-all duration-300 overflow-hidden">
            <div class="absolute inset-0 bg-gradient-to-br from-amber-500 to-orange-600 opacity-0 group-hover:opacity-5 transition-opacity"></div>
            <div class="relative p-6">
                <div class="flex items-center justify-between mb-4">
                    <div class="w-12 h-12 bg-amber-100 dark:bg-amber-900/30 rounded-xl flex items-center justify-center group-hover:scale-110 transition-transform">
                        <svg class="w-6 h-6 text-amber-600 dark:text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                        </svg>
                    </div>
                    <span class="text-xs font-medium text-amber-600 dark:text-amber-400 bg-amber-50 dark:bg-amber-900/20 px-2 py-1 rounded-full">
                        {{ $stats['presences'] ?? 0 }}%
                    </span>
                </div>
                <h3 class="text-2xl font-bold text-gray-900 dark:text-white mb-1">
                    {{ number_format($stats['presences'] ?? 0) }}%
                </h3>
                <p class="text-sm text-gray-600 dark:text-gray-400">Présences Aujourd'hui</p>
            </div>
        </div>
    </div>

    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Quick Actions & Charts -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
            <!-- Quick Actions -->
            <section class="lg:col-span-1">
                <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg p-6">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-6 flex items-center">
                        <svg class="w-5 h-5 mr-2 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                        </svg>
                        Actions Rapides
                    </h2>
                    <div class="space-y-3">
                        @if(Auth::user()->isAdmin())
                        <a href="{{ route('eleves.create') }}" class="flex items-center p-4 bg-gradient-to-r from-blue-50 to-blue-100 dark:from-blue-900/20 dark:to-blue-900/30 rounded-xl hover:from-blue-100 hover:to-blue-200 dark:hover:from-blue-900/30 dark:hover:to-blue-900/40 transition-all duration-200 group">
                            <div class="bg-blue-600 text-white rounded-lg p-2 mr-4 group-hover:scale-110 transition-transform">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"></path>
                                </svg>
                            </div>
                            <div class="flex-1">
                                <p class="font-medium text-gray-900 dark:text-white">Nouvel Élève</p>
                                <p class="text-sm text-gray-600 dark:text-gray-400">Inscrire un élève</p>
                            </div>
                        </a>

                        <a href="{{ route('enseignants.create') }}" class="flex items-center p-4 bg-gradient-to-r from-green-50 to-green-100 dark:from-green-900/20 dark:to-green-900/30 rounded-xl hover:from-green-100 hover:to-green-200 dark:hover:from-green-900/30 dark:hover:to-green-900/40 transition-all duration-200 group">
                            <div class="bg-green-600 text-white rounded-lg p-2 mr-4 group-hover:scale-110 transition-transform">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                </svg>
                            </div>
                            <div class="flex-1">
                                <p class="font-medium text-gray-900 dark:text-white">Nouvel Enseignant</p>
                                <p class="text-sm text-gray-600 dark:text-gray-400">Ajouter au personnel</p>
                            </div>
                        </a>

                        <a href="{{ route('classes.create') }}" class="flex items-center p-4 bg-gradient-to-r from-purple-50 to-purple-100 dark:from-purple-900/20 dark:to-purple-900/30 rounded-xl hover:from-purple-100 hover:to-purple-200 dark:hover:from-purple-900/30 dark:hover:to-purple-900/40 transition-all duration-200 group">
                            <div class="bg-purple-600 text-white rounded-lg p-2 mr-4 group-hover:scale-110 transition-transform">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                                </svg>
                            </div>
                            <div class="flex-1">
                                <p class="font-medium text-gray-900 dark:text-white">Nouvelle Classe</p>
                                <p class="text-sm text-gray-600 dark:text-gray-400">Créer une classe</p>
                            </div>
                        </a>
                        @endif

                        <a href="{{ route('presences.create') }}" class="flex items-center p-4 bg-gradient-to-r from-amber-50 to-amber-100 dark:from-amber-900/20 dark:to-amber-900/30 rounded-xl hover:from-amber-100 hover:to-amber-200 dark:hover:from-amber-900/30 dark:hover:to-amber-900/40 transition-all duration-200 group">
                            <div class="bg-amber-600 text-white rounded-lg p-2 mr-4 group-hover:scale-110 transition-transform">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                                </svg>
                            </div>
                            <div class="flex-1">
                                <p class="font-medium text-gray-900 dark:text-white">Marquer Présences</p>
                                <p class="text-sm text-gray-600 dark:text-gray-400">Appel du jour</p>
                            </div>
                        </a>

                        <a href="{{ route('notes.create') }}" class="flex items-center p-4 bg-gradient-to-r from-purple-50 to-purple-100 dark:from-purple-900/20 dark:to-purple-900/30 rounded-xl hover:from-purple-100 hover:to-purple-200 dark:hover:from-purple-900/30 dark:hover:to-purple-900/40 transition-all duration-200 group">
                            <div class="bg-purple-600 text-white rounded-lg p-2 mr-4 group-hover:scale-110 transition-transform">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                                </svg>
                            </div>
                            <div class="flex-1">
                                <p class="font-medium text-gray-900 dark:text-white">Ajouter Notes</p>
                                <p class="text-sm text-gray-600 dark:text-gray-400">Évaluations</p>
                            </div>
                        </a>

                        @if(Auth::user()->isAdmin())
                        <a href="{{ route('accounts.index') }}" class="flex items-center p-4 bg-gradient-to-r from-indigo-50 to-indigo-100 dark:from-indigo-900/20 dark:to-indigo-900/30 rounded-xl hover:from-indigo-100 hover:to-indigo-200 dark:hover:from-indigo-900/30 dark:hover:to-indigo-900/40 transition-all duration-200 group">
                            <div class="bg-indigo-600 text-white rounded-lg p-2 mr-4 group-hover:scale-110 transition-transform">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"></path>
                                </svg>
                            </div>
                            <div class="flex-1">
                                <p class="font-medium text-gray-900 dark:text-white">Gestion Comptes</p>
                                <p class="text-sm text-gray-600 dark:text-gray-400">Créer comptes élèves/parents</p>
                            </div>
                        </a>
                        @endif
                    </div>
                </div>
            </section>

            <!-- Rapports Automatiques (pour enseignants) -->
            @if(Auth::user()->isEnseignant())
            <section class="lg:col-span-2">
                <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg p-6">
                    <div class="flex items-center justify-between mb-6">
                        <h2 class="text-lg font-semibold text-gray-900 dark:text-white flex items-center">
                            <svg class="w-5 h-5 mr-2 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v1a1 1 0 001 1h4a1 1 0 001-1v-1m3-2V8a2 2 0 00-2-2H8a2 2 0 00-2 2v7m3-2h6"></path>
                            </svg>
                            Rapports Automatiques
                        </h2>
                        <button onclick="generateAllReports()" class="text-sm bg-indigo-600 text-white px-3 py-1 rounded-lg hover:bg-indigo-700 transition-colors">
                            Générer tous les rapports
                        </button>
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <!-- Rapport Présences -->
                        <div class="border border-gray-200 dark:border-gray-700 rounded-lg p-4 hover:shadow-md transition-shadow">
                            <div class="flex items-center mb-3">
                                <div class="w-10 h-10 bg-red-100 dark:bg-red-900/30 rounded-lg flex items-center justify-center mr-3">
                                    <svg class="w-5 h-5 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                    </svg>
                                </div>
                                <div>
                                    <h3 class="font-medium text-gray-900 dark:text-white">Présences</h3>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">Absences et retards</p>
                                </div>
                            </div>
                            <div class="space-y-2">
                                <button onclick="generatePresenceReport('week')" class="w-full text-left text-sm bg-gray-50 dark:bg-gray-700 px-3 py-2 rounded hover:bg-gray-100 dark:hover:bg-gray-600 transition-colors">
                                    📊 Rapport hebdomadaire
                                </button>
                                <button onclick="generatePresenceReport('month')" class="w-full text-left text-sm bg-gray-50 dark:bg-gray-700 px-3 py-2 rounded hover:bg-gray-100 dark:hover:bg-gray-600 transition-colors">
                                    📈 Rapport mensuel
                                </button>
                                <button onclick="generatePresenceReport('class')" class="w-full text-left text-sm bg-gray-50 dark:bg-gray-700 px-3 py-2 rounded hover:bg-gray-100 dark:hover:bg-gray-600 transition-colors">
                                    👥 Par classe
                                </button>
                            </div>
                        </div>

                        <!-- Rapport Notes -->
                        <div class="border border-gray-200 dark:border-gray-700 rounded-lg p-4 hover:shadow-md transition-shadow">
                            <div class="flex items-center mb-3">
                                <div class="w-10 h-10 bg-green-100 dark:bg-green-900/30 rounded-lg flex items-center justify-center mr-3">
                                    <svg class="w-5 h-5 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                                    </svg>
                                </div>
                                <div>
                                    <h3 class="font-medium text-gray-900 dark:text-white">Notes</h3>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">Statistiques et moyennes</p>
                                </div>
                            </div>
                            <div class="space-y-2">
                                <button onclick="generateNotesReport('statistics')" class="w-full text-left text-sm bg-gray-50 dark:bg-gray-700 px-3 py-2 rounded hover:bg-gray-100 dark:hover:bg-gray-600 transition-colors">
                                    📊 Statistiques générales
                                </button>
                                <button onclick="generateNotesReport('progression')" class="w-full text-left text-sm bg-gray-50 dark:bg-gray-700 px-3 py-2 rounded hover:bg-gray-100 dark:hover:bg-gray-600 transition-colors">
                                    📈 Progression des élèves
                                </button>
                                <button onclick="generateNotesReport('comparison')" class="w-full text-left text-sm bg-gray-50 dark:bg-gray-700 px-3 py-2 rounded hover:bg-gray-100 dark:hover:bg-gray-600 transition-colors">
                                    🔍 Comparaison classes
                                </button>
                            </div>
                        </div>

                        <!-- Rapport Évolution -->
                        <div class="border border-gray-200 dark:border-gray-700 rounded-lg p-4 hover:shadow-md transition-shadow">
                            <div class="flex items-center mb-3">
                                <div class="w-10 h-10 bg-purple-100 dark:bg-purple-900/30 rounded-lg flex items-center justify-center mr-3">
                                    <svg class="w-5 h-5 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
                                    </svg>
                                </div>
                                <div>
                                    <h3 class="font-medium text-gray-900 dark:text-white">Évolution</h3>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">Suivi des progrès</p>
                                </div>
                            </div>
                            <div class="space-y-2">
                                <button onclick="generateEvolutionReport('individual')" class="w-full text-left text-sm bg-gray-50 dark:bg-gray-700 px-3 py-2 rounded hover:bg-gray-100 dark:hover:bg-gray-600 transition-colors">
                                    👤 Élèves individuels
                                </button>
                                <button onclick="generateEvolutionReport('class')" class="w-full text-left text-sm bg-gray-50 dark:bg-gray-700 px-3 py-2 rounded hover:bg-gray-100 dark:hover:bg-gray-600 transition-colors">
                                    👥 Évolution par classe
                                </button>
                                <button onclick="generateEvolutionReport('subject')" class="w-full text-left text-sm bg-gray-50 dark:bg-gray-700 px-3 py-2 rounded hover:bg-gray-100 dark:hover:bg-gray-600 transition-colors">
                                    📚 Par matière
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Zone de résultats -->
                    <div id="reportResults" class="mt-6 hidden">
                        <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg p-4">
                            <div class="flex items-center">
                                <svg class="animate-spin h-5 w-5 text-blue-600 mr-3" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                <span class="text-blue-800 dark:text-blue-200">Génération du rapport en cours...</span>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
            @endif

            <!-- Chart Section -->
            <section class="lg:col-span-2">
                <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg p-6">
                    <div class="flex items-center justify-between mb-6">
                        <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Tendance des Présences</h2>
                        <div class="flex items-center space-x-2">
                            <span class="w-3 h-3 bg-blue-500 rounded-full"></span>
                            <span class="text-sm text-gray-600 dark:text-gray-400">Cette semaine</span>
                        </div>
                    </div>
                    <div class="h-80">
                        <canvas id="attendanceChart"></canvas>
                    </div>
                </div>
            </section>
        </div>

        <!-- Gestion des Classes - Section Admin -->
        @if(Auth::user()->isAdmin())
        <section class="mt-8">
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg p-6">
                <div class="flex items-center justify-between mb-6">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white flex items-center">
                        <svg class="w-5 h-5 mr-2 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                        </svg>
                        Gestion des Classes
                    </h2>
                    <div class="flex items-center space-x-3">
                        <button onclick="toggleClassForm()" class="text-sm bg-purple-600 text-white px-4 py-2 rounded-lg hover:bg-purple-700 transition-colors">
                            ➕ Ajouter une classe
                        </button>
                        <a href="{{ route('classes.index') }}" class="text-sm text-purple-600 dark:text-purple-400 hover:text-purple-800 dark:hover:text-purple-300 transition-colors">
                            Voir toutes les classes →
                        </a>
                    </div>
                </div>

                <!-- Formulaire d'ajout rapide de classe -->
                <div id="classForm" class="hidden mb-6 p-4 bg-purple-50 dark:bg-purple-900/20 rounded-xl border border-purple-200 dark:border-purple-700">
                    <h3 class="text-md font-semibold text-gray-900 dark:text-white mb-4">Créer une nouvelle classe</h3>
                    <form action="{{ route('classes.store') }}" method="POST" class="grid grid-cols-1 md:grid-cols-4 gap-4">
                        @csrf
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Nom de la classe</label>
                            <input type="text" name="nom" required 
                                   class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent dark:bg-gray-700 dark:text-white"
                                   placeholder="ex: 6ème A">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Niveau</label>
                            <select name="niveau" required 
                                    class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent dark:bg-gray-700 dark:text-white">
                                <option value="">Sélectionner...</option>
                                <option value="6ème">6ème</option>
                                <option value="5ème">5ème</option>
                                <option value="4ème">4ème</option>
                                <option value="3ème">3ème</option>
                                <option value="2nde">2nde</option>
                                <option value="1ère">1ère</option>
                                <option value="Terminale">Terminale</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Capacité</label>
                            <input type="number" name="capacite" required min="1" max="50"
                                   class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent dark:bg-gray-700 dark:text-white"
                                   placeholder="30">
                        </div>
                        <div class="flex items-end space-x-2">
                            <button type="submit" class="flex-1 bg-purple-600 text-white px-4 py-2 rounded-lg hover:bg-purple-700 transition-colors">
                                ✅ Créer
                            </button>
                            <button type="button" onclick="toggleClassForm()" class="bg-gray-500 text-white px-4 py-2 rounded-lg hover:bg-gray-600 transition-colors">
                                ❌
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Liste rapide des classes récentes -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    @php
                        $recentClasses = \App\Models\Classe::latest()->take(6)->get();
                    @endphp
                    @if($recentClasses->count() > 0)
                        @foreach($recentClasses as $class)
                        <div class="border border-gray-200 dark:border-gray-700 rounded-lg p-4 hover:shadow-md transition-all duration-200 group">
                            <div class="flex items-center justify-between mb-3">
                                <div class="flex items-center">
                                    <div class="w-10 h-10 bg-purple-100 dark:bg-purple-900/30 rounded-lg flex items-center justify-center mr-3 group-hover:scale-110 transition-transform">
                                        <svg class="w-5 h-5 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                                        </svg>
                                    </div>
                                    <div>
                                        <h3 class="font-semibold text-gray-900 dark:text-white">{{ $class->nom }}</h3>
                                        <p class="text-xs text-gray-500 dark:text-gray-400">{{ $class->niveau ?? 'Niveau non défini' }}</p>
                                    </div>
                                </div>
                                <span class="text-xs bg-purple-100 dark:bg-purple-900/30 text-purple-800 dark:text-purple-300 px-2 py-1 rounded-full">
                                    {{ $class->eleves->count() }}/{{ $class->capacite ?? 30 }}
                                </span>
                            </div>
                            <div class="space-y-2">
                                <div class="flex justify-between text-sm">
                                    <span class="text-gray-600 dark:text-gray-400">Élèves inscrits:</span>
                                    <span class="font-medium text-gray-900 dark:text-white">{{ $class->eleves->count() }}</span>
                                </div>
                                @if($class->enseignants->count() > 0)
                                <div class="flex justify-between text-sm">
                                    <span class="text-gray-600 dark:text-gray-400">Enseignants:</span>
                                    <span class="font-medium text-gray-900 dark:text-white">{{ $class->enseignants->count() }}</span>
                                </div>
                                @endif
                            </div>
                            <div class="mt-3 flex space-x-2">
                                <a href="{{ route('classes.show', $class->id) }}" class="text-xs bg-blue-100 dark:bg-blue-900/30 text-blue-800 dark:text-blue-300 px-2 py-1 rounded hover:bg-blue-200 dark:hover:bg-blue-900/40 transition-colors">
                                    👁️ Voir
                                </a>
                                <a href="{{ route('classes.edit', $class->id) }}" class="text-xs bg-green-100 dark:bg-green-900/30 text-green-800 dark:text-green-300 px-2 py-1 rounded hover:bg-green-200 dark:hover:bg-green-900/40 transition-colors">
                                    ✏️ Modifier
                                </a>
                            </div>
                        </div>
                        @endforeach
                    @else
                        <div class="col-span-3 text-center py-8">
                            <div class="text-gray-400 mb-4">
                                <svg class="mx-auto h-12 w-12" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                                </svg>
                            </div>
                            <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-2">Aucune classe créée</h3>
                            <p class="text-gray-500 dark:text-gray-400 mb-4">Commencez par créer votre première classe.</p>
                            <button onclick="toggleClassForm()" class="bg-purple-600 text-white px-4 py-2 rounded-lg hover:bg-purple-700 transition-colors">
                                ➕ Créer la première classe
                            </button>
                        </div>
                    @endif
                </div>
            </div>
        </section>
        @endif

        <!-- Recent Activity -->
        <section>
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg p-6">
                <div class="flex items-center justify-between mb-6">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white flex items-center">
                        <svg class="w-5 h-5 mr-2 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        Activité Récente
                    </h2>
                    <button onclick="location.reload()" class="text-sm text-blue-600 dark:text-blue-400 hover:text-blue-800 dark:hover:text-blue-300 transition-colors">
                        Actualiser
                    </button>
                </div>
                <div class="space-y-4">
                    <div class="flex items-center p-4 bg-blue-50 dark:bg-blue-900/20 rounded-xl border-l-4 border-blue-500">
                        <div class="bg-blue-100 dark:bg-blue-900/30 rounded-full p-2 mr-4">
                            <svg class="w-5 h-5 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"></path>
                            </svg>
                        </div>
                        <div class="flex-1">
                            <p class="text-sm font-medium text-gray-900 dark:text-white">Nouvel élève inscrit</p>
                            <p class="text-xs text-gray-600 dark:text-gray-400">6ème A • Il y a 5 minutes</p>
                        </div>
                    </div>

                    <div class="flex items-center p-4 bg-green-50 dark:bg-green-900/20 rounded-xl border-l-4 border-green-500">
                        <div class="bg-green-100 dark:bg-green-900/30 rounded-full p-2 mr-4">
                            <svg class="w-5 h-5 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                        <div class="flex-1">
                            <p class="text-sm font-medium text-gray-900 dark:text-white">Présences marquées</p>
                            <p class="text-xs text-gray-600 dark:text-gray-400">3ème B • Il y a 1 heure</p>
                        </div>
                    </div>

                    <div class="flex items-center p-4 bg-purple-50 dark:bg-purple-900/20 rounded-xl border-l-4 border-purple-500">
                        <div class="bg-purple-100 dark:bg-purple-900/30 rounded-full p-2 mr-4">
                            <svg class="w-5 h-5 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                            </svg>
                        </div>
                        <div class="flex-1">
                            <p class="text-sm font-medium text-gray-900 dark:text-white">Notes ajoutées</p>
                            <p class="text-xs text-gray-600 dark:text-gray-400">Mathématiques • Il y a 2 heures</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </main>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const ctx = document.getElementById('attendanceChart');
    if (!ctx) return;

    const attendanceData = {!! json_encode($attendanceData ?? [95, 92, 88, 94, 92]) !!};
    
    new Chart(ctx, {
        type: 'line',
        data: {
            labels: ['Lundi', 'Mardi', 'Mercredi', 'Jeudi', 'Vendredi'],
            datasets: [{
                label: 'Taux de présence',
                data: attendanceData,
                borderColor: 'rgb(59, 130, 246)',
                backgroundColor: 'rgba(59, 130, 246, 0.1)',
                borderWidth: 3,
                pointBackgroundColor: 'rgb(59, 130, 246)',
                pointBorderColor: '#fff',
                pointBorderWidth: 2,
                pointRadius: 6,
                pointHoverRadius: 8,
                tension: 0.4,
                fill: true
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                },
                tooltip: {
                    backgroundColor: 'rgba(0, 0, 0, 0.8)',
                    titleColor: '#fff',
                    bodyColor: '#fff',
                    borderColor: 'rgb(59, 130, 246)',
                    borderWidth: 1,
                    padding: 12,
                    displayColors: false,
                    callbacks: {
                        label: function(context) {
                            return `Présence: ${context.parsed.y}%`;
                        }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: false,
                    min: 80,
                    max: 100,
                    grid: {
                        color: 'rgba(0, 0, 0, 0.05)',
                        drawBorder: false
                    },
                    ticks: {
                        callback: function(value) {
                            return value + '%';
                        },
                        color: '#6b7280',
                        font: {
                            size: 12
                        }
                    }
                },
                x: {
                    grid: {
                        display: false,
                        drawBorder: false
                    },
                    ticks: {
                        color: '#6b7280',
                        font: {
                            size: 12
                        }
                    }
                }
            },
            interaction: {
                intersect: false,
                mode: 'index'
            }
        }
    });
});

// Fonctions pour les rapports automatiques
function showReportLoading() {
    const resultsDiv = document.getElementById('reportResults');
    if (resultsDiv) {
        resultsDiv.classList.remove('hidden');
    }
}

function hideReportLoading() {
    const resultsDiv = document.getElementById('reportResults');
    if (resultsDiv) {
        resultsDiv.classList.add('hidden');
    }
}

function showReportResult(title, content, type = 'success') {
    const resultsDiv = document.getElementById('reportResults');
    if (!resultsDiv) return;

    const bgColor = type === 'success' ? 'bg-green-50 dark:bg-green-900/20 border-green-200 dark:border-green-800' : 
                   type === 'error' ? 'bg-red-50 dark:bg-red-900/20 border-red-200 dark:border-red-800' : 
                   'bg-blue-50 dark:bg-blue-900/20 border-blue-200 dark:border-blue-800';
    
    const textColor = type === 'success' ? 'text-green-800 dark:text-green-200' : 
                     type === 'error' ? 'text-red-800 dark:text-red-200' : 
                     'text-blue-800 dark:text-blue-200';

    resultsDiv.innerHTML = `
        <div class="${bgColor} border rounded-lg p-4">
            <h3 class="font-semibold text-gray-900 dark:text-white mb-2">${title}</h3>
            <div class="${textColor}">${content}</div>
            <div class="mt-3 flex space-x-2">
                <button onclick="downloadReport('${title}')" class="text-sm bg-indigo-600 text-white px-3 py-1 rounded hover:bg-indigo-700 transition-colors">
                    📥 Télécharger PDF
                </button>
                <button onclick="printReport('${title}')" class="text-sm bg-gray-600 text-white px-3 py-1 rounded hover:bg-gray-700 transition-colors">
                    🖨️ Imprimer
                </button>
            </div>
        </div>
    `;
}

// Rapports de présence
function generatePresenceReport(type) {
    showReportLoading();
    
    // Simuler un appel API
    setTimeout(() => {
        let title, content;
        
        switch(type) {
            case 'week':
                title = 'Rapport de présence hebdomadaire';
                content = `
                    <div class="space-y-2">
                        <p><strong>Période:</strong> ${getWeekRange()}</p>
                        <p><strong>Total élèves:</strong> 156</p>
                        <p><strong>Présences:</strong> 1,248 (92.3%)</p>
                        <p><strong>Absences:</strong> 87 (6.4%)</p>
                        <p><strong>Retards:</strong> 17 (1.3%)</p>
                        <div class="mt-3">
                            <canvas id="presenceWeekChart" width="400" height="200"></canvas>
                        </div>
                    </div>
                `;
                break;
            case 'month':
                title = 'Rapport de présence mensuel';
                content = `
                    <div class="space-y-2">
                        <p><strong>Mois:</strong> ${getCurrentMonth()}</p>
                        <p><strong>Total élèves:</strong> 156</p>
                        <p><strong>Présences:</strong> 5,432 (91.8%)</p>
                        <p><strong>Absences:</strong> 382 (6.5%)</p>
                        <p><strong>Retards:</strong> 98 (1.7%)</p>
                    </div>
                `;
                break;
            case 'class':
                title = 'Rapport de présence par classe';
                content = `
                    <div class="space-y-2">
                        <p><strong>Vos classes:</strong></p>
                        <ul class="list-disc list-inside space-y-1">
                            <li>6ème A: 94.2% de présence</li>
                            <li>5ème B: 89.7% de présence</li>
                            <li>4ème A: 92.1% de présence</li>
                        </ul>
                    </div>
                `;
                break;
        }
        
        hideReportLoading();
        showReportResult(title, content);
        
        // Créer le graphique si nécessaire
        if (type === 'week') {
            createPresenceWeekChart();
        }
    }, 1500);
}

// Rapports de notes
function generateNotesReport(type) {
    showReportLoading();
    
    setTimeout(() => {
        let title, content;
        
        switch(type) {
            case 'statistics':
                title = 'Statistiques générales des notes';
                content = `
                    <div class="space-y-2">
                        <p><strong>Moyenne générale:</strong> 13.2/20</p>
                        <p><strong>Note la plus haute:</strong> 19.5/20</p>
                        <p><strong>Note la plus basse:</strong> 7.0/20</p>
                        <p><strong>Élèves au-dessus de la moyenne:</strong> 68%</p>
                        <p><strong>Écart-type:</strong> 2.8</p>
                    </div>
                `;
                break;
            case 'progression':
                title = 'Progression des élèves';
                content = `
                    <div class="space-y-2">
                        <p><strong>Élèves en progression:</strong> 89 (57%)</p>
                        <p><strong>Élèves stables:</strong> 45 (29%)</p>
                        <p><strong>Élèves en régression:</strong> 22 (14%)</p>
                        <p><strong>Progression moyenne:</strong> +1.3 points</p>
                    </div>
                `;
                break;
            case 'comparison':
                title = 'Comparaison entre classes';
                content = `
                    <div class="space-y-2">
                        <p><strong>Classe 6ème A:</strong> 13.8/20 (1er)</p>
                        <p><strong>Classe 5ème B:</strong> 12.9/20 (2ème)</p>
                        <p><strong>Classe 4ème A:</strong> 12.7/20 (3ème)</p>
                        <p><strong>Écart entre 1er et 3ème:</strong> 1.1 points</p>
                    </div>
                `;
                break;
        }
        
        hideReportLoading();
        showReportResult(title, content);
    }, 1500);
}

// Rapports d'évolution
function generateEvolutionReport(type) {
    showReportLoading();
    
    setTimeout(() => {
        let title, content;
        
        switch(type) {
            case 'individual':
                title = 'Évolution individuelle des élèves';
                content = `
                    <div class="space-y-2">
                        <p><strong>Meilleure progression:</strong> Martin Dubois (+4.2 points)</p>
                        <p><strong>Plus grande régression:</strong> Sophie Martin (-2.1 points)</p>
                        <p><strong>Élèves constants:</strong> 45</p>
                        <p><strong>Objectif atteint:</strong> 78% des élèves</p>
                    </div>
                `;
                break;
            case 'class':
                title = 'Évolution par classe';
                content = `
                    <div class="space-y-2">
                        <p><strong>6ème A:</strong> +0.8 points vs mois dernier</p>
                        <p><strong>5ème B:</strong> +1.2 points vs mois dernier</p>
                        <p><strong>4ème A:</strong> -0.3 points vs mois dernier</p>
                        <p><strong>Tendance générale:</strong> Positive (+0.6 points)</p>
                    </div>
                `;
                break;
            case 'subject':
                title = 'Évolution par matière';
                content = `
                    <div class="space-y-2">
                        <p><strong>Mathématiques:</strong> 13.5/20 (+0.5)</p>
                        <p><strong>Français:</strong> 14.2/20 (+0.8)</p>
                        <p><strong>Histoire:</strong> 12.8/20 (+0.2)</p>
                        <p><strong>Sciences:</strong> 13.1/20 (+0.7)</p>
                    </div>
                `;
                break;
        }
        
        hideReportLoading();
        showReportResult(title, content);
    }, 1500);
}

// Fonction pour afficher/masquer le formulaire de classe
function toggleClassForm() {
    const form = document.getElementById('classForm');
    form.classList.toggle('hidden');
    
    // Scroll vers le formulaire si affiché
    if (!form.classList.contains('hidden')) {
        form.scrollIntoView({ behavior: 'smooth', block: 'center' });
        // Focus sur le premier champ
        form.querySelector('input[name="nom"]').focus();
    }
}

// Générer tous les rapports
function generateAllReports() {
    showReportLoading();
    
    setTimeout(() => {
        hideReportLoading();
        showReportResult(
            'Rapport complet généré', 
            `
            <div class="space-y-3">
                <p>✅ Rapport de présence hebdomadaire généré</p>
                <p>✅ Statistiques générales des notes générées</p>
                <p>✅ Évolution individuelle des élèves générée</p>
                <p><strong>Tous les rapports sont prêts pour consultation!</strong></p>
            </div>
            `,
            'success'
        );
    }, 3000);
}

// Fonctions utilitaires
function getWeekRange() {
    const now = new Date();
    const startOfWeek = new Date(now.setDate(now.getDate() - now.getDay()));
    const endOfWeek = new Date(now.setDate(now.getDate() - now.getDay() + 6));
    return `${startOfWeek.toLocaleDateString('fr-FR')} - ${endOfWeek.toLocaleDateString('fr-FR')}`;
}

function getCurrentMonth() {
    const now = new Date();
    return now.toLocaleDateString('fr-FR', { month: 'long', year: 'numeric' });
}

function createPresenceWeekChart() {
    const ctx = document.getElementById('presenceWeekChart');
    if (!ctx) return;
    
    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: ['Lun', 'Mar', 'Mer', 'Jeu', 'Ven'],
            datasets: [{
                label: 'Présences',
                data: [94, 92, 88, 94, 92],
                backgroundColor: 'rgba(34, 197, 94, 0.8)',
                borderColor: 'rgb(34, 197, 94)',
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true,
                    max: 100,
                    ticks: {
                        callback: function(value) {
                            return value + '%';
                        }
                    }
                }
            }
        }
    });
}

function downloadReport(title) {
    // Simuler le téléchargement
    alert(`Téléchargement du rapport: ${title}\n\nDans une vraie application, ceci générerait un fichier PDF.`);
}

function printReport(title) {
    // Simuler l'impression
    alert(`Impression du rapport: ${title}\n\nDans une vraie application, ceci ouvrirait la boîte de dialogue d'impression.`);
}
</script>
@endpush
@endsection
