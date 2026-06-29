@extends('layouts.app')

@section('title', 'Saisie groupée des notes')

@section('content')
<h1 class="page-title mb-4">Saisie groupée des notes</h1>

<form method="POST" action="{{ route('notes.bulk.store') }}" class="card p-6 space-y-4" id="bulk-form">
    @csrf
    <div class="grid grid-cols-1 sm:grid-cols-4 gap-4">
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
            <label class="form-label" for="bareme">Barème</label>
            <input type="number" step="0.25" id="bareme" name="bareme" value="20" class="form-input" required>
        </div>
        <div>
            <label class="form-label" for="coefficient">Coefficient</label>
            <input type="number" step="0.5" id="coefficient" name="coefficient" value="1" class="form-input" required>
        </div>
        <div>
            <label class="form-label" for="date_evaluation">Date</label>
            <input type="date" id="date_evaluation" name="date_evaluation" value="{{ now()->toDateString() }}" class="form-input" required>
        </div>
    </div>

    <div id="liste-eleves" class="card p-4">
        <p class="text-sm text-slate-400">Sélectionnez une classe pour afficher la liste des élèves.</p>
    </div>

    <button type="submit" class="btn-primary">Enregistrer les notes</button>
</form>

@push('scripts')
<script>
document.getElementById('classe_id').addEventListener('change', async (e) => {
    const container = document.getElementById('liste-eleves');
    if (!e.target.value) return;
    container.innerHTML = 'Chargement...';
    const res = await fetch(`/notes/classes/${e.target.value}/eleves`);
    const eleves = await res.json();
    container.innerHTML = eleves.map((el, i) => `
        <div class="flex items-center justify-between py-2 border-b border-slate-100 last:border-0">
            <span>${el.prenom} ${el.nom} <span class="text-slate-400">(${el.matricule})</span></span>
            <input type="hidden" name="notes[${i}][eleve_id]" value="${el.id}">
            <input type="number" step="0.25" name="notes[${i}][valeur]" class="form-input w-28" placeholder="Note" required>
        </div>
    `).join('');
});
</script>
@endpush
@endsection
