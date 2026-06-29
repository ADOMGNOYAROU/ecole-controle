@extends('layouts.app')

@section('title', 'Modifier l\'enseignant')

@section('content')
<h1 class="page-title mb-4">Modifier {{ $enseignant->nomComplet() }}</h1>

<form method="POST" action="{{ route('enseignants.update', $enseignant) }}" class="card p-6 max-w-3xl space-y-4">
    @csrf
    @method('PUT')
    @include('enseignants._form')
    <div class="flex gap-3">
        <button type="submit" class="btn-primary">Enregistrer</button>
        <a href="{{ route('enseignants.show', $enseignant) }}" class="btn-secondary">Annuler</a>
    </div>
</form>

<form method="POST" action="{{ route('enseignants.destroy', $enseignant) }}" class="mt-4" onsubmit="return confirm('Supprimer cet enseignant ?');">
    @csrf
    @method('DELETE')
    <button type="submit" class="btn-danger">Supprimer l'enseignant</button>
</form>
@endsection
