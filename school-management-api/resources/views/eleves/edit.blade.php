@extends('layouts.app')

@section('title', 'Modifier l\'élève')

@section('content')
<h1 class="page-title mb-4">Modifier {{ $eleve->nomComplet() }}</h1>

<form method="POST" action="{{ route('eleves.update', $eleve) }}" class="card p-6 max-w-3xl space-y-4">
    @csrf
    @method('PUT')
    @include('eleves._form')
    <div class="flex gap-3">
        <button type="submit" class="btn-primary">Enregistrer</button>
        <a href="{{ route('eleves.show', $eleve) }}" class="btn-secondary">Annuler</a>
    </div>
</form>

@can('delete', $eleve)
<form method="POST" action="{{ route('eleves.destroy', $eleve) }}" class="mt-4" onsubmit="return confirm('Supprimer cet élève ?');">
    @csrf
    @method('DELETE')
    <button type="submit" class="btn-danger">Supprimer l'élève</button>
</form>
@endcan
@endsection
