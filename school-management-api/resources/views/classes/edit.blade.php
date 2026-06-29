@extends('layouts.app')

@section('title', 'Modifier la classe')

@section('content')
<h1 class="page-title mb-4">Modifier {{ $classe->nom }}</h1>

<form method="POST" action="{{ route('classes.update', $classe) }}" class="card p-6 max-w-xl space-y-4">
    @csrf
    @method('PUT')
    @include('classes._form')
    <div class="flex gap-3">
        <button type="submit" class="btn-primary">Enregistrer</button>
        <a href="{{ route('classes.show', $classe) }}" class="btn-secondary">Annuler</a>
    </div>
</form>

@can('delete', $classe)
<form method="POST" action="{{ route('classes.destroy', $classe) }}" class="mt-4" onsubmit="return confirm('Supprimer cette classe ?');">
    @csrf
    @method('DELETE')
    <button type="submit" class="btn-danger">Supprimer la classe</button>
</form>
@endcan
@endsection
