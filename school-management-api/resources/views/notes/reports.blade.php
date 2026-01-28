@extends('layouts.app')

@section('title', 'Rapports de notes - School Manager')

@section('content')
<div class="container mx-auto px-4 py-6">
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-900">Rapports de notes</h1>
        <p class="text-gray-600 mt-2">Consultez les statistiques et rapports des notes</p>
    </div>

    <!-- Statistiques générales -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-blue-100 mr-4">
                    <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                    </svg>
                </div>
                <div>
                    <p class="text-sm font-medium text-gray-500">Moyenne générale</p>
                    <p class="text-2xl font-semibold text-gray-900">12.5/20</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-green-100 mr-4">
                    <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                    </svg>
                </div>
                <div>
                    <p class="text-sm font-medium text-gray-500">Total des notes</p>
                    <p class="text-2xl font-semibold text-gray-900">150</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-yellow-100 mr-4">
                    <svg class="w-6 h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                    </svg>
                </div>
                <div>
                    <p class="text-sm font-medium text-gray-500">Taux de réussite</p>
                    <p class="text-2xl font-semibold text-gray-900">85%</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-purple-100 mr-4">
                    <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                    </svg>
                </div>
                <div>
                    <p class="text-sm font-medium text-gray-500">Élèves évalués</p>
                    <p class="text-2xl font-semibold text-gray-900">45</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Filtres -->
    <div class="bg-white rounded-lg shadow p-6 mb-6">
        <h3 class="text-lg font-medium text-gray-900 mb-4">Filtres</h3>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
                <label for="classe_filter" class="block text-sm font-medium text-gray-700 mb-2">Classe</label>
                <select id="classe_filter" class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm">
                    <option value="">Toutes les classes</option>
                    @foreach(\App\Models\Classe::all() as $classe)
                        <option value="{{ $classe->id }}">{{ $classe->nom }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="matiere_filter" class="block text-sm font-medium text-gray-700 mb-2">Matière</label>
                <select id="matiere_filter" class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm">
                    <option value="">Toutes les matières</option>
                    @foreach(\App\Models\Matiere::all() as $matiere)
                        <option value="{{ $matiere->id }}">{{ $matiere->nom }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="periode_filter" class="block text-sm font-medium text-gray-700 mb-2">Période</label>
                <select id="periode_filter" class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm">
                    <option value="">Toutes les périodes</option>
                    <option value="trimestre1">1er Trimestre</option>
                    <option value="trimestre2">2ème Trimestre</option>
                    <option value="trimestre3">3ème Trimestre</option>
                </select>
            </div>
        </div>
    </div>

    <!-- Tableau des notes -->
    <div class="bg-white rounded-lg shadow">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-medium text-gray-900">Détail des notes</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Élève</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Classe</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Matière</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Note</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">Dupont Jean</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">3ème A</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">Mathématiques</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">15.5/20</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">Devoir</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">15/01/2024</td>
                    </tr>
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">Martin Marie</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">3ème A</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">Français</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">18/20</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">Examen</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">12/01/2024</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
