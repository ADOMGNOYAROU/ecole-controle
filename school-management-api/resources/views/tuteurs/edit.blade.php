@extends('layouts.app')

@section('title', 'Modifier le tuteur')

@section('content')
<h1 class="page-title mb-4">Modifier {{ $tuteur->nomComplet() }}</h1>
<form method="POST" action="{{ route('tuteurs.update', $tuteur) }}" class="card p-6 max-w-3xl space-y-4">
    @csrf
    @method('PUT')
    @include('tuteurs._form')
    <div class="flex gap-3">
        <button type="submit" class="btn-primary">Enregistrer</button>
        <a href="{{ route('tuteurs.show', $tuteur) }}" class="btn-secondary">Annuler</a>
    </div>
</form>

<form method="POST" action="{{ route('tuteurs.destroy', $tuteur) }}" class="mt-4" onsubmit="return confirm('Supprimer ce tuteur ?');">
    @csrf @method('DELETE')
    <button type="submit" class="btn-danger">Supprimer</button>
</form>
@endsection
