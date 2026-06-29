@extends('layouts.app')

@section('title', 'Nouvelle note')

@section('content')
<h1 class="page-title mb-4">Nouvelle note</h1>

<form method="POST" action="{{ route('notes.store') }}" class="card p-6 max-w-2xl space-y-4">
    @csrf
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div>
            <label class="form-label" for="eleve_id">Élève</label>
            <select id="eleve_id" name="eleve_id" class="form-select" required>
                <option value="">Sélectionner une classe d'abord</option>
            </select>
            @error('eleve_id')<p class="form-error">{{ $message }}</p>@enderror
        </div>
        <div>
            <label class="form-label" for="classe_id">Classe</label>
            <select id="classe_id" name="classe_id" class="form-select" required>
                <option value="">—</option>
                @foreach($classes as $classe)
                    <option value="{{ $classe->id }}">{{ $classe->nom }}</option>
                @endforeach
            </select>
            @error('classe_id')<p class="form-error">{{ $message }}</p>@enderror
        </div>
        <div>
            <label class="form-label" for="matiere_id">Matière</label>
            <select id="matiere_id" name="matiere_id" class="form-select" required>
                @foreach($matieres as $matiere)
                    <option value="{{ $matiere->id }}">{{ $matiere->nom }}</option>
                @endforeach
            </select>
        </div>
        @if(auth()->user()->isAdmin())
            <div>
                <label class="form-label" for="enseignant_id">Enseignant</label>
                <select id="enseignant_id" name="enseignant_id" class="form-select" required>
                    @foreach($enseignants as $enseignant)
                        <option value="{{ $enseignant->id }}">{{ $enseignant->nomComplet() }}</option>
                    @endforeach
                </select>
            </div>
        @endif
        <div>
            <label class="form-label" for="trimestre_id">Trimestre</label>
            <select id="trimestre_id" name="trimestre_id" class="form-select" required>
                @foreach($trimestres as $trimestre)
                    <option value="{{ $trimestre->id }}">{{ $trimestre->nom }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="form-label" for="type">Type</label>
            <select id="type" name="type" class="form-select" required>
                <option value="devoir">Devoir</option>
                <option value="composition">Composition</option>
            </select>
        </div>
        <div>
            <label class="form-label" for="date_evaluation">Date</label>
            <input type="date" id="date_evaluation" name="date_evaluation" value="{{ now()->toDateString() }}" class="form-input" required>
        </div>
        <div>
            <label class="form-label" for="valeur">Note obtenue</label>
            <input type="number" step="0.25" id="valeur" name="valeur" class="form-input" required>
            @error('valeur')<p class="form-error">{{ $message }}</p>@enderror
        </div>
        <div>
            <label class="form-label" for="bareme">Barème</label>
            <input type="number" step="0.25" id="bareme" name="bareme" value="20" class="form-input" required>
        </div>
        <div>
            <label class="form-label" for="coefficient">Coefficient</label>
            <input type="number" step="0.5" id="coefficient" name="coefficient" value="1" class="form-input" required>
        </div>
    </div>
    <button type="submit" class="btn-primary">Enregistrer la note</button>
</form>

@push('scripts')
<script>
document.getElementById('classe_id').addEventListener('change', async (e) => {
    const select = document.getElementById('eleve_id');
    select.innerHTML = '<option>Chargement...</option>';
    if (!e.target.value) return;
    const res = await fetch(`/notes/classes/${e.target.value}/eleves`);
    const eleves = await res.json();
    select.innerHTML = eleves.map(el => `<option value="${el.id}">${el.prenom} ${el.nom} (${el.matricule})</option>`).join('');
});
</script>
@endpush
@endsection
