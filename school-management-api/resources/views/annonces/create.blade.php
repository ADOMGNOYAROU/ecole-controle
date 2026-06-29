@extends('layouts.app')

@section('title', 'Nouvelle annonce')

@section('content')
<h1 class="page-title mb-4">Nouvelle annonce</h1>

<form method="POST" action="{{ route('annonces.store') }}" class="card p-6 max-w-2xl space-y-4">
    @csrf
    <div>
        <label class="form-label" for="titre">Titre</label>
        <input type="text" id="titre" name="titre" class="form-input" required>
        @error('titre')<p class="form-error">{{ $message }}</p>@enderror
    </div>
    <div>
        <label class="form-label" for="contenu">Contenu</label>
        <textarea id="contenu" name="contenu" rows="5" class="form-textarea" required></textarea>
        @error('contenu')<p class="form-error">{{ $message }}</p>@enderror
    </div>
    <div>
        <label class="form-label" for="cible">Destinataires</label>
        <select id="cible" name="cible" class="form-select" required onchange="document.getElementById('classe-field').classList.toggle('hidden', this.value !== 'classe')">
            <option value="tous">Tous</option>
            <option value="parents">Parents</option>
            <option value="enseignants">Enseignants</option>
            <option value="eleves">Élèves</option>
            <option value="classe">Une classe en particulier</option>
        </select>
    </div>
    <div id="classe-field" class="hidden">
        <label class="form-label" for="classe_id">Classe</label>
        <select id="classe_id" name="classe_id" class="form-select">
            @foreach($classes as $classe)
                <option value="{{ $classe->id }}">{{ $classe->nom }}</option>
            @endforeach
        </select>
    </div>
    <button type="submit" class="btn-primary">Publier</button>
</form>
@endsection
