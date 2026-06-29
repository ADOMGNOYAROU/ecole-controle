@extends('layouts.app')

@section('title', 'Modifier la matière')

@section('content')
<h1 class="page-title mb-4">Modifier {{ $matiere->nom }}</h1>
<form method="POST" action="{{ route('matieres.update', $matiere) }}" class="card p-6 max-w-md space-y-4">
    @csrf
    @method('PUT')
    @include('matieres._form')
    <div class="flex gap-3">
        <button type="submit" class="btn-primary">Enregistrer</button>
        <a href="{{ route('matieres.index') }}" class="btn-secondary">Annuler</a>
    </div>
</form>
@endsection
