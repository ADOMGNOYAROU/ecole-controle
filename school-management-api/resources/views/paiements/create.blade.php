@extends('layouts.app')

@section('title', 'Nouveau paiement')

@section('content')
<h1 class="page-title mb-4">Nouveau paiement</h1>
<form method="POST" action="{{ route('paiements.store') }}" class="card p-6 max-w-2xl space-y-4">
    @csrf
    @include('paiements._form')
    <button type="submit" class="btn-primary">Enregistrer</button>
</form>
@endsection
