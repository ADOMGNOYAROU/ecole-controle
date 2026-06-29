@extends('layouts.app')

@section('title', 'Mon profil')

@section('content')
<h1 class="page-title mb-4">Mon profil</h1>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
    <div class="card p-6">
        <h2 class="font-semibold text-slate-900 mb-4">Informations personnelles</h2>
        <form method="POST" action="{{ route('profile.update') }}" class="space-y-4">
            @csrf
            @method('PATCH')
            <div>
                <label class="form-label" for="name">Nom</label>
                <input type="text" id="name" name="name" value="{{ old('name', $user->name) }}" class="form-input" required>
                @error('name')<p class="form-error">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="form-label" for="email">Email</label>
                <input type="email" id="email" name="email" value="{{ old('email', $user->email) }}" class="form-input" required>
                @error('email')<p class="form-error">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="form-label" for="phone">Téléphone</label>
                <input type="text" id="phone" name="phone" value="{{ old('phone', $user->phone) }}" class="form-input">
            </div>
            <button type="submit" class="btn-primary">Enregistrer</button>
        </form>
    </div>

    <div class="card p-6">
        <h2 class="font-semibold text-slate-900 mb-4">Changer le mot de passe</h2>
        @if($user->must_change_password)
            <p class="text-sm text-yellow-700 bg-yellow-50 rounded-lg p-3 mb-4">Vous devez changer votre mot de passe temporaire.</p>
        @endif
        <form method="POST" action="{{ route('profile.password') }}" class="space-y-4">
            @csrf
            @method('PUT')
            <div>
                <label class="form-label" for="current_password">Mot de passe actuel</label>
                <input type="password" id="current_password" name="current_password" class="form-input" required>
                @error('current_password')<p class="form-error">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="form-label" for="password">Nouveau mot de passe</label>
                <input type="password" id="password" name="password" class="form-input" required>
                @error('password')<p class="form-error">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="form-label" for="password_confirmation">Confirmer le mot de passe</label>
                <input type="password" id="password_confirmation" name="password_confirmation" class="form-input" required>
            </div>
            <button type="submit" class="btn-primary">Mettre à jour</button>
        </form>
    </div>
</div>
@endsection
