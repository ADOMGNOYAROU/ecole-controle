@extends('layouts.app')

@section('title', 'Nouvel élève')

@section('content')
<h1 class="page-title mb-4">Nouvel élève</h1>

<form method="POST" action="{{ route('eleves.store') }}" class="card p-6 max-w-3xl space-y-4">
    @csrf
    @include('eleves._form')
    <button type="submit" class="btn-primary">Inscrire l'élève</button>
</form>
@endsection
