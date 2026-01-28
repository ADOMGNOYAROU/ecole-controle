<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'School Manager') - School Manager</title>
    
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- Custom Styles -->
    <style>
        .form-label {
            @apply block text-sm font-medium text-gray-700 mb-2;
        }
        .form-input {
            @apply block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm;
        }
        .btn-primary {
            @apply w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500;
        }
        .login-container {
            @apply min-h-screen flex items-center justify-center bg-gray-50 py-12 px-4 sm:px-6 lg:px-8;
        }
        .login-form {
            @apply max-w-md w-full space-y-8 bg-white p-8 rounded-lg shadow-lg;
        }
    </style>
</head>
<body class="bg-gray-100">
    @auth
        <!-- Navigation Bar -->
        <nav class="bg-white shadow-lg">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between h-16">
                    <div class="flex items-center">
                        <div class="flex-shrink-0 flex items-center">
                            <h1 class="text-xl font-bold text-indigo-600">School Manager</h1>
                        </div>
                        <div class="hidden sm:ml-6 sm:flex sm:space-x-8">
                            @if(Auth::user()->isAdmin())
                                <!-- Navigation pour l'admin -->
                                <a href="{{ route('dashboard') }}" class="border-indigo-500 text-gray-900 inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium">
                                    Tableau de bord
                                </a>
                                <a href="{{ route('classes.index') }}" class="border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700 inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium">
                                    Classes
                                </a>
                                <a href="{{ route('eleves.index') }}" class="border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700 inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium">
                                    Élèves
                                </a>
                                <a href="{{ route('enseignants.index') }}" class="border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700 inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium">
                                    Enseignants
                                </a>
                                <a href="{{ route('prof-titulaire.index') }}" class="border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700 inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium">
                                    Prof. Titulaires
                                </a>
                                <a href="{{ route('accounts.index') }}" class="border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700 inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium">
                                    Comptes
                                </a>
                                <a href="{{ route('notes.index') }}" class="border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700 inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium">
                                    Notes
                                </a>
                                <a href="{{ route('presences.index') }}" class="border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700 inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium">
                                    Présences
                                </a>
                            @elseif(Auth::user()->isEnseignant())
                                <!-- Navigation pour les enseignants -->
                                <a href="{{ route('dashboard.enseignant') }}" class="border-indigo-500 text-gray-900 inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium">
                                    Tableau de bord
                                </a>
                                <a href="{{ route('classes.index') }}" class="border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700 inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium">
                                    Mes Classes
                                </a>
                                <a href="{{ route('notes.index') }}" class="border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700 inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium">
                                    Gérer les Notes
                                </a>
                                <a href="{{ route('presences.index') }}" class="border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700 inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium">
                                    Appel
                                </a>
                            @elseif(Auth::user()->isProfTitulaire())
                                <!-- Navigation pour les professeurs titulaires -->
                                <a href="{{ route('dashboard.enseignant') }}" class="border-indigo-500 text-gray-900 inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium">
                                    Tableau de bord
                                </a>
                                <a href="{{ route('classes.index') }}" class="border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700 inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium">
                                    Mes Classes
                                </a>
                                <a href="{{ route('progress.select-student') }}" class="border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700 inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium">
                                    Suivi des Progrès
                                </a>
                                <a href="{{ route('notes.index') }}" class="border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700 inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium">
                                    Gérer les Notes
                                </a>
                                <a href="{{ route('presences.index') }}" class="border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700 inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium">
                                    Appel
                                </a>
                            @elseif(Auth::user()->isEleve())
                                <!-- Navigation pour les élèves -->
                                @php
                                    $eleve = \App\Models\Eleve::where('email', Auth::user()->email)->first();
                                @endphp
                                <a href="{{ route('dashboard.eleve') }}" class="border-indigo-500 text-gray-900 inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium">
                                    Mon Tableau de bord
                                </a>
                                @if($eleve)
                                    <a href="{{ route('eleves.grades', $eleve->id) }}" class="border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700 inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium">
                                        Mes Notes
                                    </a>
                                    <a href="{{ route('eleves.attendances', $eleve->id) }}" class="border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700 inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium">
                                        Présences
                                    </a>
                                @endif
                                <a href="/profile" class="border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700 inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium">
                                    Mon Profil
                                </a>
                            @elseif(Auth::user()->role === 'parent')
                                <!-- Navigation pour les parents -->
                                @php
                                    $parent = \App\Models\ParentModel::where('email', Auth::user()->email)->first();
                                @endphp
                                <a href="{{ route('dashboard.parent') }}" class="border-indigo-500 text-gray-900 inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium">
                                    Tableau de bord
                                </a>
                                @if($parent && $parent->eleves->count() > 0)
                                    <a href="{{ route('dashboard.parent') }}" class="border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700 inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium">
                                        Mes Enfants ({{ $parent->eleves->count() }})
                                    </a>
                                    @foreach($parent->eleves->take(1) as $enfant)
                                        <a href="{{ route('eleves.grades', $enfant->id) }}" class="border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700 inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium">
                                            Notes
                                        </a>
                                        <a href="{{ route('eleves.attendances', $enfant->id) }}" class="border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700 inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium">
                                            Présences
                                        </a>
                                    @endforeach
                                @endif
                                <a href="/profile" class="border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700 inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium">
                                    Mon Profil
                                </a>
                            @endif
                        </div>
                    </div>
                    <div class="flex items-center space-x-4">
                        <span class="text-sm text-gray-700">{{ Auth::user()->name }}</span>
                        <form method="POST" action="{{ route('logout') }}" class="inline">
                            @csrf
                            <button type="submit" class="bg-red-600 hover:bg-red-700 text-white px-3 py-2 rounded-md text-sm font-medium">
                                Déconnexion
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </nav>
    @endauth

    <!-- Main Content -->
    <main>
        @yield('content')
    </main>

    <!-- Scripts -->
    <script>
        // CSRF Token for AJAX requests
        window.Laravel = {
            csrfToken: '{{ csrf_token() }}'
        };
    </script>
    @stack('scripts')
</body>
</html>
