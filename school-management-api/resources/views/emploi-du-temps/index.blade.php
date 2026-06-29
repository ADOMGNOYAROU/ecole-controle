@extends('layouts.app')

@section('title', 'Emploi du temps')

@section('content')
<div class="flex items-center justify-between mb-4">
    <h1 class="page-title">Emploi du temps {{ $classe ? '— '.$classe->nom : '' }}</h1>
    <form method="GET">
        <select name="classe" class="form-select" onchange="window.location='{{ url('emploi-du-temps') }}/'+this.value">
            @foreach($classes as $c)
                <option value="{{ $c->id }}" @selected($classe?->id === $c->id)>{{ $c->nom }}</option>
            @endforeach
        </select>
    </form>
</div>

@if(!$classe)
    <div class="card p-6 text-slate-400">Aucune classe à afficher.</div>
@else
    <div class="card overflow-hidden">
        <table class="data-table">
            <thead>
                <tr>
                    @foreach($jours as $numero => $nom)
                        <th>{{ $nom }}</th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                <tr class="align-top">
                    @foreach($jours as $numero => $nom)
                        <td class="space-y-2">
                            @forelse($creneaux->get($numero, collect()) as $creneau)
                                <div class="rounded-lg bg-brand-50 p-2 text-xs">
                                    <p class="font-medium text-brand-700">{{ \Carbon\Carbon::parse($creneau->heure_debut)->format('H:i') }} - {{ \Carbon\Carbon::parse($creneau->heure_fin)->format('H:i') }}</p>
                                    <p>{{ $creneau->matiere->nom }}</p>
                                    <p class="text-slate-500">{{ $creneau->enseignant?->nomComplet() ?? '' }} {{ $creneau->salle ? '· '.$creneau->salle : '' }}</p>
                                    @can('delete', $creneau)
                                        <form method="POST" action="{{ route('emploi-du-temps.destroy', $creneau) }}" onsubmit="return confirm('Supprimer ce créneau ?');">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="text-red-600 hover:underline mt-1">Retirer</button>
                                        </form>
                                    @endcan
                                </div>
                            @empty
                                <p class="text-slate-300 text-xs">—</p>
                            @endforelse
                        </td>
                    @endforeach
                </tr>
            </tbody>
        </table>
    </div>

    @can('create', \App\Models\CreneauHoraire::class)
        <div class="card p-5 mt-6 max-w-2xl">
            <h2 class="font-semibold text-slate-900 mb-4">Ajouter un créneau</h2>
            <form method="POST" action="{{ route('emploi-du-temps.store') }}" class="grid grid-cols-2 sm:grid-cols-3 gap-4">
                @csrf
                <input type="hidden" name="classe_id" value="{{ $classe->id }}">
                <div>
                    <label class="form-label">Matière</label>
                    <select name="matiere_id" class="form-select" required>
                        @foreach($matieres as $matiere)
                            <option value="{{ $matiere->id }}">{{ $matiere->nom }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="form-label">Jour</label>
                    <select name="jour_semaine" class="form-select" required>
                        @foreach($jours as $numero => $nom)
                            <option value="{{ $numero }}">{{ $nom }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="form-label">Salle</label>
                    <input type="text" name="salle" class="form-input">
                </div>
                <div>
                    <label class="form-label">Heure début</label>
                    <input type="time" name="heure_debut" class="form-input" required>
                </div>
                <div>
                    <label class="form-label">Heure fin</label>
                    <input type="time" name="heure_fin" class="form-input" required>
                </div>
                <div class="flex items-end">
                    <button type="submit" class="btn-primary">Ajouter</button>
                </div>
            </form>
        </div>
    @endcan
@endif
@endsection
