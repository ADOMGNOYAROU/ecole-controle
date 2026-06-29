<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Tableau de bord') · École Manager</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
@php
    $user = auth()->user();
    $premium = ! $user->isSuperAdmin() && $user->ecole?->aAccesPremium();
    $notificationsNonLues = $premium ? $user->notifications()->where('lu', false)->count() : 0;
@endphp
<body class="bg-slate-100 text-slate-900 antialiased">
    <div class="flex h-screen overflow-hidden">
        <aside class="hidden lg:flex w-64 shrink-0 flex-col h-screen overflow-y-auto bg-gradient-to-b from-[#0b1d4d] to-[#101a3d] px-4 py-6">
            <div class="mb-8 flex items-center gap-2 px-2 shrink-0">
                <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-gradient-to-br from-brand-500 to-violet-600 text-white font-bold shadow-sm">É</div>
                <span class="text-lg font-semibold text-white">École Manager</span>
            </div>

            <nav class="flex-1 space-y-0.5">
                @if($user->isSuperAdmin())
                    <a href="{{ route('super-admin.dashboard') }}" class="nav-link {{ request()->routeIs('super-admin.dashboard') ? 'active' : '' }}"><x-nav-icon name="home" />Tableau de bord</a>
                    <a href="{{ route('super-admin.ecoles.index') }}" class="nav-link {{ request()->routeIs('super-admin.ecoles.*') ? 'active' : '' }}"><x-nav-icon name="building" />Écoles</a>
                    <a href="{{ route('super-admin.factures.index') }}" class="nav-link {{ request()->routeIs('super-admin.factures.*') ? 'active' : '' }}"><x-nav-icon name="receipt" />Factures</a>
                @else
                    <a href="{{ route('dashboard') }}" class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}"><x-nav-icon name="home" />Tableau de bord</a>

                    @if($user->isAdmin() || $user->isEnseignant())
                        <a href="{{ route('classes.index') }}" class="nav-link {{ request()->routeIs('classes.*') ? 'active' : '' }}"><x-nav-icon name="building" />Classes</a>
                        <a href="{{ route('eleves.index') }}" class="nav-link {{ request()->routeIs('eleves.*') ? 'active' : '' }}"><x-nav-icon name="user" />Élèves</a>
                        <a href="{{ route('notes.index') }}" class="nav-link {{ request()->routeIs('notes.*') ? 'active' : '' }}"><x-nav-icon name="document-text" />Notes</a>
                        <a href="{{ route('presences.index') }}" class="nav-link {{ request()->routeIs('presences.*') ? 'active' : '' }}"><x-nav-icon name="check-circle" />Présences</a>
                        @if($user->isAdmin() || $user->enseignant?->estProfTitulaire())
                            <a href="{{ route('bulletins.index') }}" class="nav-link {{ request()->routeIs('bulletins.*') ? 'active' : '' }}"><x-nav-icon name="document-report" />Bulletins @unless($premium)<span class="badge-brand ml-1">Premium</span>@endunless</a>
                        @endif
                    @endif

                    <a href="{{ route('emploi-du-temps.index') }}" class="nav-link {{ request()->routeIs('emploi-du-temps.*') ? 'active' : '' }}"><x-nav-icon name="clock" />Emploi du temps</a>

                    @if($user->isAdmin())
                        <p class="px-3 pt-3 pb-1 text-xs font-semibold uppercase tracking-wide text-slate-500">Administration</p>
                        <a href="{{ route('enseignants.index') }}" class="nav-link {{ request()->routeIs('enseignants.*') ? 'active' : '' }}"><x-nav-icon name="academic-cap" />Enseignants</a>
                        <a href="{{ route('tuteurs.index') }}" class="nav-link {{ request()->routeIs('tuteurs.*') ? 'active' : '' }}"><x-nav-icon name="users" />Parents / Tuteurs</a>
                        <a href="{{ route('matieres.index') }}" class="nav-link {{ request()->routeIs('matieres.*') ? 'active' : '' }}"><x-nav-icon name="book-open" />Matières</a>
                        <a href="{{ route('paiements.index') }}" class="nav-link {{ request()->routeIs('paiements.*') ? 'active' : '' }}"><x-nav-icon name="credit-card" />Paiements @unless($premium)<span class="badge-brand ml-1">Premium</span>@endunless</a>
                        <a href="{{ route('annees-scolaires.index') }}" class="nav-link {{ request()->routeIs('annees-scolaires.*') || request()->routeIs('trimestres.*') ? 'active' : '' }}"><x-nav-icon name="calendar" />Années scolaires</a>
                        <a href="{{ route('comptes.index') }}" class="nav-link {{ request()->routeIs('comptes.*') ? 'active' : '' }}"><x-nav-icon name="key" />Comptes @unless($premium)<span class="badge-brand ml-1">Premium</span>@endunless</a>
                        <a href="{{ route('abonnement.index') }}" class="nav-link {{ request()->routeIs('abonnement.*') ? 'active' : '' }}"><x-nav-icon name="star" />Abonnement</a>
                    @endif

                    @if($user->isEleve())
                        <a href="{{ route('mon-espace.notes') }}" class="nav-link {{ request()->routeIs('mon-espace.notes') ? 'active' : '' }}"><x-nav-icon name="document-text" />Mes notes</a>
                        <a href="{{ route('mon-espace.presences') }}" class="nav-link {{ request()->routeIs('mon-espace.presences') ? 'active' : '' }}"><x-nav-icon name="check-circle" />Mes présences</a>
                        <a href="{{ route('mon-espace.paiements') }}" class="nav-link {{ request()->routeIs('mon-espace.paiements') ? 'active' : '' }}"><x-nav-icon name="credit-card" />Ma scolarité</a>
                    @endif

                    @if($user->isParent())
                        <a href="{{ route('mes-enfants.index') }}" class="nav-link {{ request()->routeIs('mes-enfants.*') ? 'active' : '' }}"><x-nav-icon name="users" />Mes enfants</a>
                    @endif

                    <p class="px-3 pt-3 pb-1 text-xs font-semibold uppercase tracking-wide text-slate-500">Communication</p>
                    <a href="{{ route('annonces.index') }}" class="nav-link {{ request()->routeIs('annonces.*') ? 'active' : '' }}"><x-nav-icon name="megaphone" />Annonces @unless($premium)<span class="badge-brand ml-1">Premium</span>@endunless</a>
                    <a href="{{ route('notifications.index') }}" class="nav-link {{ request()->routeIs('notifications.*') ? 'active' : '' }}"><x-nav-icon name="bell" />Notifications @unless($premium)<span class="badge-brand ml-1">Premium</span>@endunless</a>
                @endif
            </nav>

            @if($user->isAdmin())
                <a href="{{ $premium ? route('bulletins.index') : route('abonnement.index') }}" class="mb-3 mt-3 block rounded-xl bg-gradient-to-br from-brand-600 to-violet-700 p-3.5 text-white shadow-lg hover:shadow-xl transition-shadow">
                    <svg class="w-6 h-6 mb-2 opacity-90" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5zm0 0l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z"/></svg>
                    @if($premium)
                        <p class="text-sm font-semibold">Bulletins du trimestre</p>
                        <p class="text-xs text-brand-100 mt-0.5">Générez les bulletins PDF de vos classes en un clic.</p>
                    @else
                        <p class="text-sm font-semibold">Passez au Premium</p>
                        <p class="text-xs text-brand-100 mt-0.5">Bulletins, paiements et annonces dès 15 000 FCFA/trimestre.</p>
                    @endif
                </a>
            @endif

            <div class="border-t border-white/10 pt-4">
                <a href="{{ route('profile') }}" class="nav-link {{ request()->routeIs('profile') ? 'active' : '' }}"><x-nav-icon name="cog" />Mon profil</a>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="nav-link w-full text-left"><x-nav-icon name="logout" />Déconnexion</button>
                </form>
            </div>
        </aside>

        <div class="flex-1 flex flex-col min-w-0 h-screen overflow-hidden">
            <header class="shrink-0 flex items-center justify-between gap-4 border-b border-slate-200 bg-white/80 backdrop-blur-sm px-4 py-3 lg:px-8 shadow-sm">
                <div class="shrink-0">
                    <h1 class="text-lg font-semibold text-slate-900">@yield('title', 'Tableau de bord')</h1>
                    <p class="text-xs text-slate-400">{{ now()->locale('fr')->isoFormat('dddd D MMMM YYYY') }}</p>
                </div>

                @if($user->isAdmin() || $user->isEnseignant())
                    <form method="GET" action="{{ route('eleves.index') }}" class="hidden md:flex flex-1 max-w-sm">
                        <div class="relative w-full">
                            <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35M17 11a6 6 0 11-12 0 6 6 0 0112 0z"/></svg>
                            <input type="text" name="recherche" placeholder="Rechercher un élève…" class="w-full rounded-lg border border-slate-200 bg-slate-50 py-2 pl-9 pr-12 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-brand-500">
                            <kbd class="absolute right-2 top-1/2 -translate-y-1/2 rounded border border-slate-200 bg-white px-1.5 py-0.5 text-[10px] font-medium text-slate-400">⏎</kbd>
                        </div>
                    </form>
                @endif

                <div class="flex items-center gap-2 shrink-0">
                    @if($premium)
                        <a href="{{ route('notifications.index') }}" class="relative flex h-9 w-9 items-center justify-center rounded-full text-slate-500 hover:bg-slate-100 hover:text-slate-700 transition-colors">
                            <x-nav-icon name="bell" class="w-5 h-5" />
                            @if($notificationsNonLues > 0)
                                <span class="absolute -top-0.5 -right-0.5 flex h-4 min-w-[1rem] items-center justify-center rounded-full bg-red-500 px-1 text-[10px] font-bold text-white">{{ $notificationsNonLues > 9 ? '9+' : $notificationsNonLues }}</span>
                            @endif
                        </a>
                    @endif
                    <a href="{{ route('profile') }}" class="flex items-center gap-3 rounded-full pl-1 pr-3 py-1 hover:bg-slate-100 transition-colors">
                        <x-avatar :name="auth()->user()->name" class="h-8 w-8 text-[11px]" />
                        <span class="hidden sm:inline text-sm font-medium text-slate-700">{{ auth()->user()->name }}</span>
                        <span class="badge-brand">{{ ucfirst(str_replace('_', ' ', auth()->user()->role)) }}</span>
                        <svg class="hidden sm:block w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                    </a>
                </div>
            </header>

            <main class="flex-1 overflow-y-auto p-4 lg:p-8 bg-[radial-gradient(circle_at_top_right,theme(colors.brand.50),transparent_45%)]">
                @if(! $user->isSuperAdmin() && $user->ecole?->estEnEssai() && ! request()->routeIs('abonnement.*'))
                    <div class="mb-4 rounded-lg bg-brand-50 border border-brand-200 px-4 py-3 text-sm text-brand-800 flex items-center justify-between">
                        <span>Essai Premium : {{ $user->ecole->joursEssaiRestants() }} jour(s) restant(s).</span>
                        <a href="{{ route('abonnement.index') }}" class="font-medium underline">Passer au Premium</a>
                    </div>
                @endif

                <x-flash-messages />
                @yield('content')
            </main>
        </div>
    </div>
    @stack('scripts')
</body>
</html>
