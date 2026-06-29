@extends('layouts.auth')

@section('title', 'Connexion')

@section('content')
<h2 class="text-xl font-semibold text-slate-900 mb-1">Connexion</h2>
<p class="text-sm text-slate-500 mb-6">Accédez à votre espace de gestion scolaire.</p>

<form class="space-y-5" method="POST" action="{{ route('login') }}">
    @csrf

    <div>
        <label for="email" class="form-label">Adresse email</label>
        <input id="email" name="email" type="email" value="{{ old('email') }}" autocomplete="email" required autofocus
               class="form-input" placeholder="vous@ecole.test">
        @error('email')<p class="form-error">{{ $message }}</p>@enderror
    </div>

    <div>
        <label for="password" class="form-label">Mot de passe</label>
        <input id="password" name="password" type="password" autocomplete="current-password" required
               class="form-input" placeholder="••••••••">
        @error('password')<p class="form-error">{{ $message }}</p>@enderror
    </div>

    <div class="flex items-center">
        <input id="remember" name="remember" type="checkbox" class="h-4 w-4 rounded border-slate-300 text-brand-600 focus:ring-brand-500">
        <label for="remember" class="ml-2 text-sm text-slate-600">Se souvenir de moi</label>
    </div>

    <button type="submit" class="btn-primary w-full">Se connecter</button>
</form>

<p class="text-sm text-slate-500 mt-5 text-center">
    Votre école n'a pas encore de compte ? <a href="{{ route('inscription.show') }}" class="text-brand-600 hover:underline">Inscrire mon école</a>
</p>

<div class="mt-6 border-t border-slate-200 pt-5">
    <p class="text-xs font-medium text-slate-500 mb-2">Comptes de démonstration (mot de passe : <code>password</code>)</p>
    <ul class="space-y-1 text-xs text-slate-500">
        <li><span class="font-medium text-slate-700">Admin</span> — admin@ecole.test</li>
        <li><span class="font-medium text-slate-700">Enseignant</span> — claire.dubois@ecole.test</li>
    </ul>
</div>
@endsection
