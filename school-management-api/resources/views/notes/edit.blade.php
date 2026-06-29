@extends('layouts.app')

@section('title', 'Modifier la note')

@section('content')
<h1 class="page-title mb-4">Modifier la note de {{ $note->eleve->nomComplet() }}</h1>

<form method="POST" action="{{ route('notes.update', $note) }}" class="card p-6 max-w-2xl space-y-4">
    @csrf
    @method('PUT')
    <input type="hidden" name="eleve_id" value="{{ $note->eleve_id }}">
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div>
            <label class="form-label" for="classe_id">Classe</label>
            <select id="classe_id" name="classe_id" class="form-select" required>
                @foreach($classes as $classe)
                    <option value="{{ $classe->id }}" @selected($note->classe_id == $classe->id)>{{ $classe->nom }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="form-label" for="matiere_id">Matière</label>
            <select id="matiere_id" name="matiere_id" class="form-select" required>
                @foreach($matieres as $matiere)
                    <option value="{{ $matiere->id }}" @selected($note->matiere_id == $matiere->id)>{{ $matiere->nom }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="form-label" for="trimestre_id">Trimestre</label>
            <select id="trimestre_id" name="trimestre_id" class="form-select" required>
                @foreach($trimestres as $trimestre)
                    <option value="{{ $trimestre->id }}" @selected($note->trimestre_id == $trimestre->id)>{{ $trimestre->nom }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="form-label" for="type">Type</label>
            <select id="type" name="type" class="form-select" required>
                <option value="devoir" @selected($note->type === 'devoir')>Devoir</option>
                <option value="composition" @selected($note->type === 'composition')>Composition</option>
            </select>
        </div>
        <div>
            <label class="form-label" for="date_evaluation">Date</label>
            <input type="date" id="date_evaluation" name="date_evaluation" value="{{ $note->date_evaluation->toDateString() }}" class="form-input" required>
        </div>
        <div>
            <label class="form-label" for="valeur">Note obtenue</label>
            <input type="number" step="0.25" id="valeur" name="valeur" value="{{ $note->valeur }}" class="form-input" required>
            @error('valeur')<p class="form-error">{{ $message }}</p>@enderror
        </div>
        <div>
            <label class="form-label" for="bareme">Barème</label>
            <input type="number" step="0.25" id="bareme" name="bareme" value="{{ $note->bareme }}" class="form-input" required>
        </div>
        <div>
            <label class="form-label" for="coefficient">Coefficient</label>
            <input type="number" step="0.5" id="coefficient" name="coefficient" value="{{ $note->coefficient }}" class="form-input" required>
        </div>
    </div>
    <div class="flex gap-3">
        <button type="submit" class="btn-primary">Enregistrer</button>
        <a href="{{ route('notes.index') }}" class="btn-secondary">Annuler</a>
    </div>
</form>
@endsection
