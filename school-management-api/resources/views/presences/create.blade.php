@extends('layouts.app')

@section('title', 'Nouvelle présence')

@section('content')
<h1 class="page-title mb-4">Nouvelle saisie de présence</h1>

<form method="POST" action="{{ route('presences.store') }}" class="card p-6 max-w-xl space-y-4">
    @csrf
    <div>
        <label class="form-label" for="classe_id">Classe</label>
        <select id="classe_id" name="classe_id" class="form-select" required>
            <option value="">—</option>
            @foreach($classes as $classe)
                <option value="{{ $classe->id }}">{{ $classe->nom }}</option>
            @endforeach
        </select>
    </div>
    <div>
        <label class="form-label" for="eleve_id">Élève</label>
        <select id="eleve_id" name="eleve_id" class="form-select" required>
            <option value="">Sélectionner une classe d'abord</option>
        </select>
        @error('eleve_id')<p class="form-error">{{ $message }}</p>@enderror
    </div>
    <div>
        <label class="form-label" for="date">Date</label>
        <input type="date" id="date" name="date" value="{{ now()->toDateString() }}" class="form-input" required>
    </div>
    <div>
        <label class="form-label" for="statut">Statut</label>
        <select id="statut" name="statut" class="form-select" required>
            <option value="present">Présent</option>
            <option value="absent">Absent</option>
            <option value="retard">Retard</option>
        </select>
    </div>
    <div>
        <label class="form-label" for="motif">Motif (si absent)</label>
        <input type="text" id="motif" name="motif" class="form-input">
    </div>
    <button type="submit" class="btn-primary">Enregistrer</button>
</form>

@push('scripts')
<script>
document.getElementById('classe_id').addEventListener('change', async (e) => {
    const select = document.getElementById('eleve_id');
    if (!e.target.value) return;
    select.innerHTML = '<option>Chargement...</option>';
    const res = await fetch(`/presences/classes/${e.target.value}/eleves`);
    const eleves = await res.json();
    select.innerHTML = eleves.map(el => `<option value="${el.id}">${el.prenom} ${el.nom} (${el.matricule})</option>`).join('');
});
</script>
@endpush
@endsection
