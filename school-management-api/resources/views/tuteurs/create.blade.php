@extends('layouts.app')

@section('title', 'Nouveau tuteur')

@section('content')
<h1 class="page-title mb-4">Nouveau parent / tuteur</h1>
<form method="POST" action="{{ route('tuteurs.store') }}" class="card p-6 max-w-3xl space-y-4">
    @csrf
    @include('tuteurs._form')
    <button type="submit" class="btn-primary">Créer</button>
</form>
@endsection
