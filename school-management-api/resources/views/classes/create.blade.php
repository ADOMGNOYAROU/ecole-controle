@extends('layouts.app')

@section('title', 'Nouvelle classe')

@section('content')
<h1 class="page-title mb-4">Nouvelle classe</h1>

<form method="POST" action="{{ route('classes.store') }}" class="card p-6 max-w-xl space-y-4">
    @csrf
    @include('classes._form')
    <button type="submit" class="btn-primary">Créer la classe</button>
</form>
@endsection
