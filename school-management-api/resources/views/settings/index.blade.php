@extends('layouts.app')

@section('title', 'Paramètres - School Manager')

@section('content')
<div class="container mx-auto px-4 py-6">
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-900">Paramètres de l'établissement</h1>
        <p class="text-gray-600 mt-2">Configurez les informations générales de l'établissement</p>
    </div>

    @if(session('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-6">
            {{ session('success') }}
        </div>
    @endif

    <div class="bg-white shadow rounded-lg">
        <form method="POST" action="{{ route('settings.store') }}" class="p-6">
            @csrf
            
            <!-- Nom de l'établissement -->
            <div class="mb-4">
                <label for="school_name" class="block text-sm font-medium text-gray-700 mb-2">Nom de l'établissement *</label>
                <input type="text" id="school_name" name="school_name" 
                       value="{{ session('settings.school_name', old('school_name', 'École Secondaire')) }}" required
                       class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm @error('school_name') border-red-500 @enderror">
                @error('school_name')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Adresse -->
            <div class="mb-4">
                <label for="school_address" class="block text-sm font-medium text-gray-700 mb-2">Adresse</label>
                <textarea id="school_address" name="school_address" rows="3"
                          class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">{{ session('settings.school_address', old('school_address')) }}</textarea>
                @error('school_address')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Téléphone -->
            <div class="mb-4">
                <label for="school_phone" class="block text-sm font-medium text-gray-700 mb-2">Téléphone</label>
                <input type="tel" id="school_phone" name="school_phone" 
                       value="{{ session('settings.school_phone', old('school_phone')) }}"
                       class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm @error('school_phone') border-red-500 @enderror">
                @error('school_phone')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Email -->
            <div class="mb-6">
                <label for="school_email" class="block text-sm font-medium text-gray-700 mb-2">Email</label>
                <input type="email" id="school_email" name="school_email" 
                       value="{{ session('settings.school_email', old('school_email')) }}"
                       class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm @error('school_email') border-red-500 @enderror">
                @error('school_email')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Boutons -->
            <div class="flex justify-end space-x-3">
                <a href="{{ route('dashboard') }}" 
                   class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50">
                    Retour
                </a>
                <button type="submit" 
                        class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700">
                    Enregistrer les paramètres
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
