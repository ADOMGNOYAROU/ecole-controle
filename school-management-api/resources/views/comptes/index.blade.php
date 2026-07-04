@extends('layouts.app')

@section('title', 'Comptes utilisateurs')

@section('content')
<h1 class="page-title mb-4">Comptes utilisateurs</h1>

{{-- Affichage des identifiants après création / réinitialisation --}}
@if(session('compte_cree'))
@php $cree = session('compte_cree'); @endphp
<div class="mb-6 rounded-xl border-2 border-brand-300 bg-brand-50 p-5" x-data="{ visible: true }" x-show="visible">
    <div class="flex items-start justify-between gap-4">
        <div>
            <p class="font-semibold text-brand-900 text-base mb-3">
                <svg class="w-5 h-5 inline-block mr-1 text-brand-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                Compte créé — Identifiants à communiquer à {{ $cree['name'] }}
            </p>
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                <div class="rounded-lg bg-white border border-brand-200 p-3">
                    <p class="text-xs text-slate-500 mb-1">Nom</p>
                    <p class="font-semibold text-slate-900">{{ $cree['name'] }}</p>
                </div>
                <div class="rounded-lg bg-white border border-brand-200 p-3">
                    <p class="text-xs text-slate-500 mb-1">Email (identifiant)</p>
                    <p class="font-mono font-semibold text-slate-900 text-sm">{{ $cree['email'] }}</p>
                </div>
                <div class="rounded-lg bg-white border border-red-200 bg-red-50 p-3">
                    <p class="text-xs text-red-600 mb-1 font-medium">Mot de passe — noter maintenant</p>
                    <p class="font-mono font-bold text-red-800 text-lg tracking-widest select-all">{{ $cree['password'] }}</p>
                </div>
            </div>
            <p class="text-xs text-slate-500 mt-2">⚠ Notez ce mot de passe maintenant. Il ne sera plus affiché après avoir quitté cette page.</p>
        </div>
        <button @click="visible = false" class="shrink-0 text-slate-400 hover:text-slate-600">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
        </button>
    </div>
</div>
@endif

{{-- Création de comptes --}}
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">

    {{-- Élèves --}}
    <div class="card p-5">
        <h2 class="font-semibold text-slate-900 mb-4">Élèves sans compte</h2>
        <ul class="space-y-4 text-sm">
            @forelse($elevesSansCompte as $eleve)
                <li class="border-b border-slate-100 pb-4 last:border-0 last:pb-0">
                    <p class="font-medium text-slate-800 mb-2">{{ $eleve->nomComplet() }}</p>
                    <form method="POST" action="{{ route('comptes.generer') }}" class="space-y-2">
                        @csrf
                        <input type="hidden" name="type" value="eleve">
                        <input type="hidden" name="id" value="{{ $eleve->id }}">
                        @unless($eleve->email)
                            <input type="email" name="email_manuel" placeholder="Email *" class="form-input text-xs w-full" required>
                        @endunless
                        <div class="flex gap-2">
                            <input type="text" name="mot_de_passe" placeholder="Mot de passe (vide = aléatoire)" class="form-input text-xs flex-1">
                            <button type="submit" class="btn-primary text-xs whitespace-nowrap">Créer</button>
                        </div>
                    </form>
                </li>
            @empty
                <li class="text-slate-400">Aucun élève sans compte.</li>
            @endforelse
        </ul>
    </div>

    {{-- Enseignants --}}
    <div class="card p-5">
        <h2 class="font-semibold text-slate-900 mb-4">Enseignants sans compte</h2>
        <ul class="space-y-4 text-sm">
            @forelse($enseignantsSansCompte as $enseignant)
                <li class="border-b border-slate-100 pb-4 last:border-0 last:pb-0">
                    <p class="font-medium text-slate-800 mb-2">{{ $enseignant->nomComplet() }}</p>
                    <form method="POST" action="{{ route('comptes.generer') }}" class="space-y-2">
                        @csrf
                        <input type="hidden" name="type" value="enseignant">
                        <input type="hidden" name="id" value="{{ $enseignant->id }}">
                        @unless($enseignant->email)
                            <input type="email" name="email_manuel" placeholder="Email *" class="form-input text-xs w-full" required>
                        @endunless
                        <div class="flex gap-2">
                            <input type="text" name="mot_de_passe" placeholder="Mot de passe (vide = aléatoire)" class="form-input text-xs flex-1">
                            <button type="submit" class="btn-primary text-xs whitespace-nowrap">Créer</button>
                        </div>
                    </form>
                </li>
            @empty
                <li class="text-slate-400">Aucun enseignant sans compte.</li>
            @endforelse
        </ul>
    </div>

    {{-- Tuteurs --}}
    <div class="card p-5">
        <h2 class="font-semibold text-slate-900 mb-4">Tuteurs / Parents sans compte</h2>
        <ul class="space-y-4 text-sm">
            @forelse($tuteursSansCompte as $tuteur)
                <li class="border-b border-slate-100 pb-4 last:border-0 last:pb-0">
                    <p class="font-medium text-slate-800 mb-2">{{ $tuteur->nomComplet() }}</p>
                    <form method="POST" action="{{ route('comptes.generer') }}" class="space-y-2">
                        @csrf
                        <input type="hidden" name="type" value="tuteur">
                        <input type="hidden" name="id" value="{{ $tuteur->id }}">
                        @unless($tuteur->email)
                            <input type="email" name="email_manuel" placeholder="Email *" class="form-input text-xs w-full" required>
                        @endunless
                        <div class="flex gap-2">
                            <input type="text" name="mot_de_passe" placeholder="Mot de passe (vide = aléatoire)" class="form-input text-xs flex-1">
                            <button type="submit" class="btn-primary text-xs whitespace-nowrap">Créer</button>
                        </div>
                    </form>
                </li>
            @empty
                <li class="text-slate-400">Aucun tuteur sans compte.</li>
            @endforelse
        </ul>
    </div>
</div>

{{-- Liste des comptes existants --}}
<div class="card overflow-hidden">
    <div class="p-5 pb-0">
        <h2 class="font-semibold text-slate-900">Comptes existants</h2>
    </div>
    <table class="data-table">
        <thead><tr><th>Nom</th><th>Email</th><th>Rôle</th><th></th></tr></thead>
        <tbody>
            @foreach($utilisateurs as $user)
                <tr>
                    <td>
                        <div class="flex items-center gap-3">
                            <x-avatar :name="$user->name" />
                            <span class="font-medium text-slate-900">{{ $user->name }}</span>
                        </div>
                    </td>
                    <td class="text-slate-500">{{ $user->email }}</td>
                    <td><span class="badge-slate">{{ $user->role }}</span></td>
                    <td class="text-right">
                        <div class="flex items-center justify-end gap-2" x-data="{ open: false }" @click.outside="open = false">
                            {{-- Formulaire réinitialisation avec champ mot de passe --}}
                            <form method="POST" action="{{ route('comptes.reinitialiser', $user) }}" class="flex items-center gap-1.5">
                                @csrf
                                <input type="text" name="nouveau_mot_de_passe" placeholder="Nouveau mdp (vide = aléatoire)" class="form-input text-xs w-44">
                                <x-icon-button icon="key">Réinitialiser</x-icon-button>
                            </form>
                            @if($user->id !== auth()->id())
                                <x-delete-button :action="route('comptes.destroy', $user)" confirm="Supprimer ce compte ?">Supprimer</x-delete-button>
                            @endif
                        </div>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>

<div class="mt-4">{{ $utilisateurs->links() }}</div>
@endsection
