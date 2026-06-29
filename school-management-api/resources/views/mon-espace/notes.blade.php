@extends('layouts.app')

@section('title', 'Mes notes')

@section('content')
<div class="flex items-center justify-between mb-4">
    <h1 class="page-title">Mes notes</h1>
    <form method="GET">
        <select name="trimestre_id" class="form-select" onchange="this.form.submit()">
            @foreach($trimestres as $t)
                <option value="{{ $t->id }}" @selected($trimestre?->id === $t->id)>{{ $t->nom }}</option>
            @endforeach
        </select>
    </form>
</div>

@if($donnees)
    <div class="card p-5 mb-6 flex items-center justify-between">
        <span class="text-slate-600">Moyenne générale du trimestre</span>
        <span class="text-3xl font-semibold text-brand-600">{{ $donnees['moyenne_generale'] !== null ? $donnees['moyenne_generale'].'/20' : '—' }}</span>
    </div>

    <div class="card overflow-hidden">
        <table class="data-table">
            <thead><tr><th>Matière</th><th>Moyenne /20</th><th>Coefficient</th></tr></thead>
            <tbody>
                @forelse($donnees['matieres'] as $entry)
                    <tr>
                        <td>{{ $entry['matiere']->nom }}</td>
                        <td>{{ $entry['moyenne'] }}</td>
                        <td>{{ $entry['matiere']->coefficient_defaut }}</td>
                    </tr>
                @empty
                    <tr><td colspan="3" class="text-center text-slate-400 py-6">Aucune note ce trimestre.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($trimestre)
        <a href="{{ route('bulletins.show', ['eleve' => $eleve, 'trimestre' => $trimestre]) }}" class="btn-secondary inline-block mt-4">Télécharger mon bulletin PDF</a>
    @endif
@else
    <div class="card p-6 text-slate-400">Aucun trimestre actif.</div>
@endif
@endsection
