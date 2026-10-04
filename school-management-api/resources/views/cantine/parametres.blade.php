@extends('layouts.app')

@section('title', 'Réglages de la cantine')

@section('content')
<div class="mb-4">
    <a href="{{ route('cantine.index') }}" class="text-sm text-brand-700 hover:underline">← Retour à la liste du jour</a>
    <h1 class="page-title mt-1">Réglages de la cantine</h1>
    <p class="page-subtitle">Seuls les élèves cochés ici apparaissent chaque jour dans la liste de la cantine.</p>
</div>

<form method="POST" action="{{ route('cantine.parametres.update') }}">
    @csrf
    @method('PUT')

    <div class="card p-5 mb-6 max-w-md">
        <label class="form-label" for="prix_cantine">Prix d'un jour de cantine (FCFA)</label>
        <input type="number" id="prix_cantine" name="prix_cantine" min="0" step="1" value="{{ old('prix_cantine', $prix !== null ? (int) $prix : null) }}" class="form-input" required>
        @error('prix_cantine')<p class="form-error">{{ $message }}</p>@enderror
        <p class="text-xs text-slate-500 mt-2">Un changement de prix ne modifie pas les paiements déjà enregistrés.</p>
    </div>

    <div class="space-y-4">
        @forelse($elevesParClasse as $nomClasse => $eleves)
            <div class="card overflow-hidden" x-data>
                <div class="p-4 border-b border-slate-100 flex items-center justify-between gap-3">
                    <h2 class="font-semibold text-slate-900">{{ $nomClasse }} <span class="text-sm font-normal text-slate-500">({{ $eleves->count() }} élèves)</span></h2>
                    <div class="flex gap-2 text-sm">
                        <button type="button" class="text-brand-700 hover:underline" @click="$root.querySelectorAll('input[type=checkbox]').forEach(c => c.checked = true)">Tout cocher</button>
                        <span class="text-slate-300">|</span>
                        <button type="button" class="text-slate-600 hover:underline" @click="$root.querySelectorAll('input[type=checkbox]').forEach(c => c.checked = false)">Tout décocher</button>
                    </div>
                </div>
                <div class="p-4 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-2">
                    @foreach($eleves as $eleve)
                        <label class="flex items-center gap-2 text-sm text-slate-700">
                            <input type="checkbox" name="eleves[]" value="{{ $eleve->id }}" @checked(in_array($eleve->id, old('eleves', [])) || (! old('eleves') && $eleve->inscrit_cantine)) class="rounded border-slate-300">
                            {{ $eleve->nomComplet() }}
                        </label>
                    @endforeach
                </div>
            </div>
        @empty
            <div class="card p-6 text-center text-slate-500">Aucun élève actif. Ajoutez d'abord des élèves.</div>
        @endforelse
    </div>

    <div class="mt-6 flex justify-end">
        <button type="submit" class="btn-primary">Enregistrer</button>
    </div>
</form>
@endsection
