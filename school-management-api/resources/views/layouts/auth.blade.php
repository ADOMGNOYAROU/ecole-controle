<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Connexion') · École Manager</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-slate-50">
    <div class="grid min-h-screen lg:grid-cols-2">
        {{-- Panneau de marque (visible à partir de lg) --}}
        <div class="relative hidden lg:flex flex-col justify-between overflow-hidden bg-gradient-to-br from-brand-700 via-brand-600 to-violet-700 p-12 text-white">
            <div class="absolute inset-0 opacity-10" style="background-image: radial-gradient(circle at 20% 20%, white 0, transparent 35%), radial-gradient(circle at 80% 70%, white 0, transparent 30%);"></div>

            <div class="relative flex items-center gap-3">
                <div class="flex h-11 w-11 items-center justify-center rounded-xl bg-white/15 text-xl font-bold backdrop-blur-sm">É</div>
                <span class="text-xl font-semibold">École Manager</span>
            </div>

            <div class="relative space-y-6">
                <h1 class="text-3xl font-bold leading-tight">
                    La gestion scolaire,<br>simplifiée pour le Togo.
                </h1>
                <p class="text-brand-100 max-w-md">
                    Classes, notes, présences, bulletins et paiements de scolarité dans une seule
                    plateforme — gratuite pour démarrer, pensée pour les écoles d'ici.
                </p>
                <ul class="space-y-3 text-sm">
                    <li class="flex items-center gap-3">
                        <span class="flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-white/15">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                        </span>
                        Gestion des classes, élèves et enseignants gratuite et illimitée
                    </li>
                    <li class="flex items-center gap-3">
                        <span class="flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-white/15">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                        </span>
                        Bulletins PDF, paiements et annonces avec le plan Premium
                    </li>
                    <li class="flex items-center gap-3">
                        <span class="flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-white/15">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                        </span>
                        Paiement Mobile Money — Flooz &amp; TMoney
                    </li>
                </ul>
            </div>

            <p class="relative text-xs text-brand-200">© {{ date('Y') }} École Manager — Togo</p>
        </div>

        {{-- Formulaire --}}
        <div class="flex items-center justify-center px-4 py-12">
            <div class="w-full max-w-md">
                <div class="mb-8 flex items-center justify-center gap-2 lg:hidden">
                    <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-gradient-to-br from-brand-500 to-violet-600 text-white font-bold shadow-sm">É</div>
                    <span class="text-xl font-semibold text-slate-900">École Manager</span>
                </div>
                <div class="card p-8">
                    <x-flash-messages />
                    @yield('content')
                </div>
            </div>
        </div>
    </div>
</body>
</html>
