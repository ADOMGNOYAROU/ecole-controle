@extends('layouts.auth')

@section('title', 'Inscrire mon école')

@section('content')
<h2 class="text-xl font-semibold text-slate-900 mb-1">Inscrire mon école</h2>
<p class="text-sm text-slate-500 mb-6">30 jours d'essai Premium gratuit, sans engagement. Gestion de base toujours gratuite ensuite.</p>

<form class="space-y-5" method="POST" action="{{ route('inscription.store') }}">
    @csrf

    <div>
        <label for="nom_ecole" class="form-label">Nom de l'école</label>
        <input id="nom_ecole" name="nom_ecole" type="text" value="{{ old('nom_ecole') }}" required class="form-input">
        @error('nom_ecole')<p class="form-error">{{ $message }}</p>@enderror
    </div>

    <div class="grid grid-cols-2 gap-4">
        <div>
            <label for="ville" class="form-label">Ville</label>
            <input id="ville" name="ville" type="text" value="{{ old('ville') }}" class="form-input">
        </div>
        <div>
            <label for="telephone" class="form-label">Téléphone</label>
            <input id="telephone" name="telephone" type="text" value="{{ old('telephone') }}" class="form-input">
        </div>
    </div>

    <hr class="border-slate-200">

    <div>
        <label for="admin_nom" class="form-label">Votre nom (administrateur)</label>
        <input id="admin_nom" name="admin_nom" type="text" value="{{ old('admin_nom') }}" required class="form-input">
        @error('admin_nom')<p class="form-error">{{ $message }}</p>@enderror
    </div>

    <div>
        <label for="admin_email" class="form-label">Votre email</label>
        <input id="admin_email" name="admin_email" type="email" value="{{ old('admin_email') }}" required class="form-input">
        @error('admin_email')<p class="form-error">{{ $message }}</p>@enderror
    </div>

    <div>
        <label for="admin_password" class="form-label">Mot de passe</label>
        <input id="admin_password" name="admin_password" type="password" required class="form-input">
        @error('admin_password')<p class="form-error">{{ $message }}</p>@enderror
    </div>

    <div>
        <label for="admin_password_confirmation" class="form-label">Confirmer le mot de passe</label>
        <input id="admin_password_confirmation" name="admin_password_confirmation" type="password" required class="form-input">
    </div>

    <button type="submit" class="btn-primary w-full">Créer mon école</button>
</form>

<p class="text-sm text-slate-500 mt-6 text-center">
    Déjà inscrit ? <a href="{{ route('login') }}" class="text-brand-600 hover:underline">Se connecter</a>
</p>
@endsection
