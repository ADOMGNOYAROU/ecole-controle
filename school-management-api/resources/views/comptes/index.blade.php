@extends('layouts.app')

@section('title', 'Comptes utilisateurs')

@section('content')
<h1 class="page-title mb-4">Comptes utilisateurs</h1>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
    <div class="card p-5">
        <h2 class="font-semibold text-slate-900 mb-3">Élèves sans compte</h2>
        <ul class="space-y-2 text-sm">
            @forelse($elevesSansCompte as $eleve)
                <li class="flex items-center justify-between">
                    <span>{{ $eleve->nomComplet() }}</span>
                    <form method="POST" action="{{ route('comptes.generer') }}">
                        @csrf
                        <input type="hidden" name="type" value="eleve">
                        <input type="hidden" name="id" value="{{ $eleve->id }}">
                        <x-icon-button icon="plus">Créer le compte</x-icon-button>
                    </form>
                </li>
            @empty
                <li class="text-slate-400">Aucun.</li>
            @endforelse
        </ul>
    </div>

    <div class="card p-5">
        <h2 class="font-semibold text-slate-900 mb-3">Enseignants sans compte</h2>
        <ul class="space-y-2 text-sm">
            @forelse($enseignantsSansCompte as $enseignant)
                <li class="flex items-center justify-between">
                    <span>{{ $enseignant->nomComplet() }}</span>
                    <form method="POST" action="{{ route('comptes.generer') }}">
                        @csrf
                        <input type="hidden" name="type" value="enseignant">
                        <input type="hidden" name="id" value="{{ $enseignant->id }}">
                        <x-icon-button icon="plus">Créer le compte</x-icon-button>
                    </form>
                </li>
            @empty
                <li class="text-slate-400">Aucun.</li>
            @endforelse
        </ul>
    </div>

    <div class="card p-5">
        <h2 class="font-semibold text-slate-900 mb-3">Tuteurs sans compte</h2>
        <ul class="space-y-2 text-sm">
            @forelse($tuteursSansCompte as $tuteur)
                <li class="flex items-center justify-between">
                    <span>{{ $tuteur->nomComplet() }}</span>
                    <form method="POST" action="{{ route('comptes.generer') }}">
                        @csrf
                        <input type="hidden" name="type" value="tuteur">
                        <input type="hidden" name="id" value="{{ $tuteur->id }}">
                        <x-icon-button icon="plus">Créer le compte</x-icon-button>
                    </form>
                </li>
            @empty
                <li class="text-slate-400">Aucun.</li>
            @endforelse
        </ul>
    </div>
</div>

<div class="card overflow-hidden">
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
                    <td class="text-right space-x-1.5 whitespace-nowrap">
                        <form method="POST" action="{{ route('comptes.reinitialiser', $user) }}" class="inline">
                            @csrf
                            <x-icon-button icon="key">Réinitialiser</x-icon-button>
                        </form>
                        @if($user->id !== auth()->id())
                            <x-delete-button :action="route('comptes.destroy', $user)" confirm="Supprimer ce compte ?">Supprimer</x-delete-button>
                        @endif
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>

<div class="mt-4">{{ $utilisateurs->links() }}</div>
@endsection
