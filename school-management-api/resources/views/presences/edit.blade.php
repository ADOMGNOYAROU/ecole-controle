@extends('layouts.app')

@section('title', 'Modifier la présence')

@section('content')
<h1 class="page-title mb-4">Modifier la présence de {{ $presence->eleve->nomComplet() }}</h1>

<form method="POST" action="{{ route('presences.update', $presence) }}" class="card p-6 max-w-xl space-y-4">
    @csrf
    @method('PUT')
    <input type="hidden" name="eleve_id" value="{{ $presence->eleve_id }}">
    <div>
        <label class="form-label" for="classe_id">Classe</label>
        <select id="classe_id" name="classe_id" class="form-select" required>
            @foreach($classes as $classe)
                <option value="{{ $classe->id }}" @selected($presence->classe_id == $classe->id)>{{ $classe->nom }}</option>
            @endforeach
        </select>
    </div>
    <div>
        <label class="form-label" for="date">Date</label>
        <input type="date" id="date" name="date" value="{{ $presence->date->toDateString() }}" class="form-input" required>
    </div>
    <div>
        <label class="form-label" for="statut">Statut</label>
        <select id="statut" name="statut" class="form-select" required>
            <option value="present" @selected($presence->statut === 'present')>Présent</option>
            <option value="absent" @selected($presence->statut === 'absent')>Absent</option>
            <option value="retard" @selected($presence->statut === 'retard')>Retard</option>
        </select>
    </div>
    <div>
        <label class="form-label" for="motif">Motif</label>
        <input type="text" id="motif" name="motif" value="{{ $presence->motif }}" class="form-input">
    </div>
    <div class="flex gap-3">
        <button type="submit" class="btn-primary">Enregistrer</button>
        <a href="{{ route('presences.index') }}" class="btn-secondary">Annuler</a>
    </div>
</form>
@endsection
