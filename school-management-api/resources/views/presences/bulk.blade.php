@extends('layouts.app')

@section('title', 'Faire l\'appel')

@section('content')
<h1 class="page-title mb-4">Faire l'appel</h1>

<form method="POST" action="{{ route('presences.bulk.store') }}" class="card p-6 space-y-4">
    @csrf
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
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
            <label class="form-label" for="date">Date</label>
            <input type="date" id="date" name="date" value="{{ now()->toDateString() }}" class="form-input" required>
        </div>
    </div>

    <div id="liste-eleves" class="card p-4">
        <p class="text-sm text-slate-400">Sélectionnez une classe pour afficher la liste des élèves.</p>
    </div>

    <button type="submit" class="btn-primary">Enregistrer l'appel</button>
</form>

@push('scripts')
<script>
document.getElementById('classe_id').addEventListener('change', async (e) => {
    const container = document.getElementById('liste-eleves');
    if (!e.target.value) return;
    container.innerHTML = 'Chargement...';
    const res = await fetch(`/presences/classes/${e.target.value}/eleves`);
    const eleves = await res.json();
    container.innerHTML = eleves.map((el, i) => `
        <div class="flex items-center justify-between py-2 border-b border-slate-100 last:border-0">
            <span>${el.prenom} ${el.nom} <span class="text-slate-400">(${el.matricule})</span></span>
            <input type="hidden" name="presences[${i}][eleve_id]" value="${el.id}">
            <select name="presences[${i}][statut]" class="form-select w-36">
                <option value="present">Présent</option>
                <option value="absent">Absent</option>
                <option value="retard">Retard</option>
            </select>
        </div>
    `).join('');
});
</script>
@endpush
@endsection
