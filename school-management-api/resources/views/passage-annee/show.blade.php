@extends('layouts.app')

@section('title', 'Passage de fin d\'année')

@section('content')
<div class="mb-4">
    <a href="{{ route('annees-scolaires.index') }}" class="text-sm text-brand-700 hover:underline">← Années scolaires</a>
    <h1 class="page-title mt-1">Passage de fin d'année</h1>
    <p class="page-subtitle">Préparez {{ $libelleSuivant ?: 'la nouvelle année' }} en une fois : chaque classe passe dans la classe suivante, vous choisissez seulement les redoublants et les départs. L'année {{ $source->libelle }}, ses notes et ses bulletins restent consultables.</p>
</div>

<form method="POST" action="{{ route('passage-annee.executer') }}" onsubmit="return confirm('Lancer le passage ? Les élèves seront placés dans leurs nouvelles classes.')">
    @csrf

    <div class="card p-5 mb-6 grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div>
            <label class="form-label" for="annee_libelle">Nouvelle année</label>
            <input type="text" id="annee_libelle" name="annee[libelle]" value="{{ old('annee.libelle', $libelleSuivant) }}" class="form-input" required placeholder="2027-2028">
            @error('annee.libelle')<p class="form-error">{{ $message }}</p>@enderror
        </div>
        <div>
            <label class="form-label" for="annee_debut">Début</label>
            <input type="date" id="annee_debut" name="annee[date_debut]" value="{{ old('annee.date_debut', $debutSuivant) }}" class="form-input" required>
        </div>
        <div>
            <label class="form-label" for="annee_fin">Fin</label>
            <input type="date" id="annee_fin" name="annee[date_fin]" value="{{ old('annee.date_fin', $finSuivante) }}" class="form-input" required>
            @error('annee.date_fin')<p class="form-error">{{ $message }}</p>@enderror
        </div>
        <label class="sm:col-span-3 flex items-center gap-2 text-sm text-slate-700">
            <input type="hidden" name="activer" value="0">
            <input type="checkbox" name="activer" value="1" @checked(old('activer', '1') === '1') class="rounded border-slate-300">
            Faire de cette année l'année active (l'année {{ $source->libelle }} sera archivée)
        </label>
    </div>

    <div class="space-y-4">
        @forelse($classes as $classe)
            @php $propose = $suggestion($classe); @endphp
            <div class="card overflow-hidden" x-data="{ fin: {{ old("classes.{$classe->id}.fin_cycle", $propose === null ? '1' : '0') === '1' ? 'true' : 'false' }} }">
                <div class="p-4 border-b border-slate-100 flex flex-wrap items-center gap-3">
                    <h2 class="font-semibold text-slate-900">{{ $classe->nom }}</h2>
                    <span class="text-slate-400">→</span>
                    <input type="text" name="classes[{{ $classe->id }}][destination]" value="{{ old("classes.{$classe->id}.destination", $propose) }}" class="form-input max-w-[12rem]" aria-label="Classe de destination de {{ $classe->nom }}" x-bind:disabled="fin" placeholder="Classe suivante">
                    <label class="flex items-center gap-2 text-sm text-slate-700">
                        <input type="hidden" name="classes[{{ $classe->id }}][fin_cycle]" value="0">
                        <input type="checkbox" name="classes[{{ $classe->id }}][fin_cycle]" value="1" x-model="fin" class="rounded border-slate-300">
                        Fin de cycle : les élèves qui passent quittent l'école
                    </label>
                    <span class="text-sm text-slate-500 ml-auto">{{ $classe->eleves->count() }} élève(s)</span>
                </div>
                <table class="data-table">
                    <thead><tr><th>Élève</th><th>Moyenne annuelle</th><th>Décision</th></tr></thead>
                    <tbody>
                        @forelse($classe->eleves as $eleve)
                            @php
                                $moyenne = $moyennes[$eleve->id] ?? null;
                                $defaut = $moyenne !== null && $moyenne < $seuil ? 'redouble' : 'passe';
                                $choix = old("decisions.{$eleve->id}", $defaut);
                            @endphp
                            <tr>
                                <td><span class="font-medium text-slate-900">{{ $eleve->nomComplet() }}</span> <span class="text-xs text-slate-500">{{ $eleve->matricule }}</span></td>
                                <td class="{{ $moyenne !== null && $moyenne < $seuil ? 'text-red-600 font-medium' : 'text-slate-600' }}">{{ $moyenne !== null ? number_format($moyenne, 2, ',', ' ').'/20' : '—' }}</td>
                                <td>
                                    <select name="decisions[{{ $eleve->id }}]" class="form-select" aria-label="Décision pour {{ $eleve->nomComplet() }}">
                                        <option value="passe" @selected($choix === 'passe')>Passe</option>
                                        <option value="redouble" @selected($choix === 'redouble')>Redouble</option>
                                        <option value="quitte" @selected($choix === 'quitte')>Quitte l'école</option>
                                    </select>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="3" class="text-center text-slate-400 py-4">Aucun élève actif.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        @empty
            <div class="card p-6 text-center text-slate-500">Aucune classe dans l'année {{ $source->libelle }}.</div>
        @endforelse
    </div>

    <p class="text-sm text-slate-500 mt-4">« Redouble » est proposé quand la moyenne annuelle des bulletins est inférieure à {{ $seuil }}/20. Vérifiez chaque décision avant de valider.</p>

    <div class="mt-6 flex justify-end">
        <button type="submit" class="btn-primary">Lancer le passage</button>
    </div>
</form>
@endsection
