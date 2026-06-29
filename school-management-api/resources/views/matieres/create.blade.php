@extends('layouts.app')

@section('title', 'Nouvelle matière')

@section('content')
<h1 class="page-title mb-4">Nouvelle matière</h1>
<form method="POST" action="{{ route('matieres.store') }}" class="card p-6 max-w-md space-y-4">
    @csrf
    @include('matieres._form')
    <button type="submit" class="btn-primary">Créer</button>
</form>
@endsection
