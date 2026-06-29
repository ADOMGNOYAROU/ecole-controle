@extends('layouts.app')

@section('title', 'Modifier le paiement')

@section('content')
<h1 class="page-title mb-4">Modifier le paiement</h1>
<form method="POST" action="{{ route('paiements.update', $paiement) }}" class="card p-6 max-w-2xl space-y-4">
    @csrf
    @method('PUT')
    @include('paiements._form')
    <div class="flex gap-3">
        <button type="submit" class="btn-primary">Enregistrer</button>
        <a href="{{ route('paiements.index') }}" class="btn-secondary">Annuler</a>
    </div>
</form>

<form method="POST" action="{{ route('paiements.destroy', $paiement) }}" class="mt-4" onsubmit="return confirm('Supprimer ce paiement ?');">
    @csrf @method('DELETE')
    <button type="submit" class="btn-danger">Supprimer</button>
</form>
@endsection
