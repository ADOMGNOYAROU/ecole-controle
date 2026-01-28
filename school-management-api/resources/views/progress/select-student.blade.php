@extends('layouts.app')

@section('title', 'Suivi des Élèves')

@section('content')
<div class="container mx-auto px-4 py-8">
    <h1 class="text-2xl font-bold mb-6">Suivi des Progrès des Élèves</h1>
    
    @if($eleves->isEmpty())
        <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-6 text-center">
            <svg class="w-16 h-16 text-yellow-600 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
            </svg>
            <h3 class="text-lg font-semibold text-yellow-800 mb-2">Aucun élève trouvé</h3>
            <p class="text-yellow-700">Vous n'êtes titulaire d'aucune classe pour le moment.</p>
        </div>
    @else
        <div class="bg-white rounded-lg shadow-md p-6 mb-6">
            <h2 class="text-xl font-semibold mb-4">Vos Classes</h2>
            <div class="flex flex-wrap gap-2 mb-6">
                @foreach($classes as $classe)
                <span class="bg-blue-100 text-blue-800 px-3 py-1 rounded-full text-sm font-medium">
                    {{ $classe->nom }} ({{ $classe->niveau }})
                </span>
                @endforeach
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-md p-6">
            <h2 class="text-xl font-semibold mb-4">Sélectionner un élève</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                @foreach($eleves as $eleve)
                <a href="{{ route('progress.student.detail', $eleve->id) }}" 
                   class="block p-4 border border-gray-200 rounded-lg hover:bg-gray-50 hover:border-blue-300 transition-colors">
                    <div class="flex items-center space-x-3">
                        <div class="bg-blue-500 text-white rounded-full w-10 h-10 flex items-center justify-center font-semibold">
                            {{ strtoupper(substr($eleve->prenom, 0, 1)) }}{{ strtoupper(substr($eleve->nom, 0, 1)) }}
                        </div>
                        <div>
                            <h3 class="font-medium text-gray-900">{{ $eleve->nom }} {{ $eleve->prenom }}</h3>
                            <p class="text-sm text-gray-500">{{ $eleve->classe->nom }}</p>
                            @if($eleve->email)
                            <p class="text-xs text-gray-400">{{ $eleve->email }}</p>
                            @endif
                        </div>
                    </div>
                    <div class="mt-3 flex items-center text-sm text-gray-600">
                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                        </svg>
                        Voir l'évolution
                    </div>
                </a>
                @endforeach
            </div>
        </div>
    @endif
</div>
@endsection
