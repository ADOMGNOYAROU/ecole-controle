@extends('layouts.app')

@section('title', 'Nouvel enseignant')

@section('content')
<h1 class="page-title mb-4">Nouvel enseignant</h1>

<form method="POST" action="{{ route('enseignants.store') }}" class="card p-6 max-w-3xl space-y-4">
    @csrf
    @include('enseignants._form')
    <button type="submit" class="btn-primary">Créer l'enseignant</button>
</form>
@endsection
