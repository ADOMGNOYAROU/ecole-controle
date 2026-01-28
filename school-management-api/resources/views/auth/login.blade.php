@extends('layouts.auth')

@section('title', 'Connexion - School Manager')

@section('content')
<div class="min-h-screen flex items-center justify-center bg-gray-50 py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full space-y-8 bg-white p-8 rounded-lg shadow-lg">
        <!-- Header -->
        <div class="text-center">
            <div class="w-16 h-16 mx-auto bg-gradient-to-r from-blue-600 to-indigo-600 rounded-full flex items-center justify-center">
                <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5z"></path>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z"></path>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5zm0 0l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14zm-4 6v-7.5l4-2.222"></path>
                </svg>
            </div>
            <h2 class="mt-6 text-3xl font-extrabold text-gray-900">
                School Manager
            </h2>
            <p class="text-sm text-gray-600 mt-2">
                Connectez-vous à votre espace de gestion
            </p>
        </div>

        <!-- Login Form -->
        <form class="space-y-6" method="POST" action="{{ route('login') }}">
            @csrf
            
            <!-- Email -->
            <div>
                <label for="email" class="block text-sm font-medium text-gray-700 mb-2">
                    Adresse Email
                </label>
                <div>
                    <input id="email" name="email" type="email" autocomplete="email" required
                           class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                           placeholder="exemple@email.com">
                </div>
                @error('email')
                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Password -->
            <div>
                <label for="password" class="block text-sm font-medium text-gray-700 mb-2">
                    Mot de passe
                </label>
                <div>
                    <input id="password" name="password" type="password" autocomplete="current-password" required
                           class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                           placeholder="•••••••••">
                </div>
                @error('password')
                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Remember Me -->
            <div style="display: flex; align-items: center; justify-content: space-between;">
                <div style="display: flex; align-items: center;">
                    <input id="remember" name="remember" type="checkbox"
                           style="height: 1rem; width: 1rem; color: #3b82f6; border-color: #d1d5db; border-radius: 0.25rem;">
                    <label for="remember" style="margin-left: 0.5rem; display: block; font-size: 0.875rem; color: #374151;">
                        Se souvenir de moi
                    </label>
                </div>
                @if(Route::has('password.request'))
                <div style="font-size: 0.875rem;">
                    <a href="{{ route('password.request') }}" 
                       style="font-weight: 500; color: #3b82f6; text-decoration: none;">
                        Mot de passe oublié?
                    </a>
                </div>
                @endif
            </div>

            <!-- Submit Button -->
            <div>
                <button type="submit" class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    Se connecter
                </button>
            </div>
        </form>

        <!-- Demo Accounts -->
        <div style="margin-top: 1.5rem; padding-top: 1.5rem; border-top: 1px solid #e5e7eb;">
            <p class="text-center text-sm text-gray-600" style="margin-bottom: 0.75rem;">
                Comptes de démonstration
            </p>
            <div style="margin-top: 0.5rem;">
                <button onclick="fillDemoCredentials('admin@school.com', 'password')" 
                        style="width: 100%; text-align: left; padding: 0.75rem; font-size: 0.875rem; background-color: #f9fafb; border-radius: 0.5rem; margin-bottom: 0.5rem; border: none; cursor: pointer;">
                    <div style="font-weight: 500; color: #111827;">Administrateur</div>
                    <div style="color: #6b7280;">admin@school.com / password</div>
                </button>
                <button onclick="fillDemoCredentials('enseignant@school.com', 'password')" 
                        style="width: 100%; text-align: left; padding: 0.75rem; font-size: 0.875rem; background-color: #f9fafb; border-radius: 0.5rem; border: none; cursor: pointer;">
                    <div style="font-weight: 500; color: #111827;">Enseignant</div>
                    <div style="color: #6b7280;">enseignant@school.com / password</div>
                </button>
            </div>
        </div>

        <!-- Footer -->
        <div class="text-center" style="margin-top: 1.5rem;">
            <p class="text-sm text-gray-600">
                © 2024 School Manager. Tous droits réservés.
            </p>
        </div>
    </div>
</div>

<script>
function fillDemoCredentials(email, password) {
    document.getElementById('email').value = email;
    document.getElementById('password').value = password;
}
</script>
@endsection
