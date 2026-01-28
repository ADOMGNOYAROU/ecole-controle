@extends('layouts.app')

@section('title', 'Dashboard Élève')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <!-- Header -->
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900 dark:text-white">
            👋 Bonjour {{ auth()->user()->name }} !
        </h1>
        <p class="text-gray-600 dark:text-gray-400 mt-2">
            Bienvenue sur votre espace personnel
        </p>
    </div>

    <!-- Message d'erreur si nécessaire -->
    @if(isset($error))
        <div class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg p-4 mb-6">
            <div class="flex items-center">
                <svg class="w-5 h-5 text-red-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <span class="text-red-800 dark:text-red-200">{{ $error }}</span>
            </div>
        </div>
    @endif

    <!-- Informations personnelles -->
    @if(isset($eleve))
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg p-6 mb-8">
        <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-4">📚 Mes informations</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
            <div class="bg-blue-50 dark:bg-blue-900/20 rounded-xl p-4">
                <div class="flex items-center">
                    <svg class="w-8 h-8 text-blue-600 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                    </svg>
                    <div>
                        <p class="text-sm text-gray-600 dark:text-gray-400">Classe</p>
                        <p class="font-semibold text-gray-900 dark:text-white">{{ $eleve->classe->nom ?? 'Non assigné' }}</p>
                    </div>
                </div>
            </div>
            
            <div class="bg-green-50 dark:bg-green-900/20 rounded-xl p-4">
                <div class="flex items-center">
                    <svg class="w-8 h-8 text-green-600 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <div>
                        <p class="text-sm text-gray-600 dark:text-gray-400">Présences aujourd'hui</p>
                        <p class="font-semibold text-gray-900 dark:text-white">{{ $stats['presences'] }}</p>
                    </div>
                </div>
            </div>
            
            <div class="bg-purple-50 dark:bg-purple-900/20 rounded-xl p-4">
                <div class="flex items-center">
                    <svg class="w-8 h-8 text-purple-600 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                    </svg>
                    <div>
                        <p class="text-sm text-gray-600 dark:text-gray-400">Matricule</p>
                        <p class="font-semibold text-gray-900 dark:text-white">{{ $eleve->matricule }}</p>
                    </div>
                </div>
            </div>
            
            <div class="bg-orange-50 dark:bg-orange-900/20 rounded-xl p-4">
                <div class="flex items-center">
                    <svg class="w-8 h-8 text-orange-600 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                    </svg>
                    <div>
                        <p class="text-sm text-gray-600 dark:text-gray-400">Statut</p>
                        <p class="font-semibold text-gray-900 dark:text-white">{{ ucfirst($eleve->statut) }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Graphique de présence -->
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg p-6 mb-8">
        <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-4">📊 Mes présences récentes</h2>
        <div class="h-64 flex items-center justify-center">
            <div class="text-center w-full">
                <!-- Barres de présence -->
                <div class="flex justify-center space-x-3 mb-4 items-end">
                    @php
                        $recentPresences = isset($eleve) ? $eleve->presences()->orderBy('date', 'desc')->take(5)->get() : [];
                        $days = ['Aujourd\'hui', 'Hier', 'Il y a 2 jours', 'Il y a 3 jours', 'Il y a 4 jours'];
                    @endphp
                    @foreach($recentPresences as $index => $presence)
                        @php
                            $height = $presence->statut === 'present' ? 80 : ($presence->statut === 'retard' ? 60 : 40);
                            $color = $presence->statut === 'present' ? 'from-green-500 to-green-400' : 
                                    ($presence->statut === 'retard' ? 'from-yellow-500 to-yellow-400' : 'from-red-500 to-red-400');
                            $icon = $presence->statut === 'present' ? '✓' : 
                                   ($presence->statut === 'retard' ? '⏰' : '✗');
                        @endphp
                        <div class="flex flex-col items-center">
                            <div class="w-14 bg-gradient-to-t {{ $color }} rounded-t-lg transition-all duration-300 hover:opacity-80" 
                                 style="height: {{ $height }}px;"
                                 title="{{ ucfirst($presence->statut) }} - {{ \Carbon\Carbon::parse($presence->date)->format('d/m') }}">
                            </div>
                            <span class="text-xs mt-1 font-bold {{ $presence->statut === 'present' ? 'text-green-600' : ($presence->statut === 'retard' ? 'text-yellow-600' : 'text-red-600') }}">
                                {{ $icon }}
                            </span>
                            <span class="text-xs text-gray-500 mt-1">{{ \Carbon\Carbon::parse($presence->date)->format('d/m') }}</span>
                        </div>
                    @endforeach
                    
                    @if($recentPresences->isEmpty())
                        <div class="text-center text-gray-500">
                            <p class="text-sm">Aucune donnée de présence disponible</p>
                        </div>
                    @endif
                </div>
                
                <!-- Légende -->
                @if(!$recentPresences->isEmpty())
                <div class="flex justify-center space-x-6 mt-6 text-xs">
                    <div class="flex items-center">
                        <div class="w-3 h-3 bg-green-500 rounded-full mr-2"></div>
                        <span class="text-gray-600">Présent</span>
                    </div>
                    <div class="flex items-center">
                        <div class="w-3 h-3 bg-yellow-500 rounded-full mr-2"></div>
                        <span class="text-gray-600">Retard</span>
                    </div>
                    <div class="flex items-center">
                        <div class="w-3 h-3 bg-red-500 rounded-full mr-2"></div>
                        <span class="text-gray-600">Absent</span>
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Actions rapides -->
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg p-6">
        <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-4">⚡ Actions rapides</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            <a href="{{ route('progress.student') }}" class="flex items-center p-4 bg-gradient-to-r from-blue-50 to-blue-100 dark:from-blue-900/20 dark:to-blue-900/30 rounded-xl hover:from-blue-100 hover:to-blue-200 dark:hover:from-blue-900/30 dark:hover:to-blue-900/40 transition-all duration-200 group">
                <div class="bg-blue-600 text-white rounded-lg p-2 mr-4 group-hover:scale-110 transition-transform">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                    </svg>
                </div>
                <div class="flex-1">
                    <p class="font-medium text-gray-900 dark:text-white">Mes progrès</p>
                    <p class="text-sm text-gray-600 dark:text-gray-400">Voir mon évolution</p>
                </div>
            </a>
            
            <a href="{{ route('eleves.attendances', isset($eleve) ? $eleve->id : 1) }}" class="flex items-center p-4 bg-gradient-to-r from-green-50 to-green-100 dark:from-green-900/20 dark:to-green-900/30 rounded-xl hover:from-green-100 hover:to-green-200 dark:hover:from-green-900/30 dark:hover:to-green-900/40 transition-all duration-200 group">
                <div class="bg-green-600 text-white rounded-lg p-2 mr-4 group-hover:scale-110 transition-transform">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <div class="flex-1">
                    <p class="font-medium text-gray-900 dark:text-white">Présences</p>
                    <p class="text-sm text-gray-600 dark:text-gray-400">Historique</p>
                </div>
            </a>
            
            <a href="/profile" class="flex items-center p-4 bg-gradient-to-r from-purple-50 to-purple-100 dark:from-purple-900/20 dark:to-purple-900/30 rounded-xl hover:from-purple-100 hover:to-purple-200 dark:hover:from-purple-900/30 dark:hover:to-purple-900/40 transition-all duration-200 group">
                <div class="bg-purple-600 text-white rounded-lg p-2 mr-4 group-hover:scale-110 transition-transform">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                    </svg>
                </div>
                <div class="flex-1">
                    <p class="font-medium text-gray-900 dark:text-white">Profil</p>
                    <p class="text-sm text-gray-600 dark:text-gray-400">Mes informations</p>
                </div>
            </a>
        </div>
    </div>
    @endif
</div>
@endsection
