@extends('layouts.app')

@section('title', 'Annonces')

@section('content')
<div class="flex items-center justify-between mb-4">
    <h1 class="page-title">Annonces</h1>
    @can('create', \App\Models\Annonce::class)
        <a href="{{ route('annonces.create') }}" class="btn-primary">+ Nouvelle annonce</a>
    @endcan
</div>

<div class="space-y-4">
    @forelse($annonces as $annonce)
        <div class="card p-5">
            <div class="flex items-center justify-between">
                <h2 class="font-semibold text-slate-900">{{ $annonce->titre }}</h2>
                <span class="badge-slate">{{ ucfirst($annonce->cible) }}</span>
            </div>
            <p class="text-sm text-slate-500 mb-2">{{ $annonce->date_publication->format('d/m/Y H:i') }} — {{ $annonce->auteur->name }}</p>
            <p class="text-slate-700">{{ $annonce->contenu }}</p>
            @can('delete', $annonce)
                <div class="mt-3">
                    <x-delete-button :action="route('annonces.destroy', $annonce)" confirm="Supprimer cette annonce ?">Supprimer</x-delete-button>
                </div>
            @endcan
        </div>
    @empty
        <div class="card p-6 text-slate-400">Aucune annonce.</div>
    @endforelse
</div>

<div class="mt-4">{{ $annonces->links() }}</div>
@endsection
