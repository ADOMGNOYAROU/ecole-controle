@extends('layouts.app')

@section('title', 'Rapports de notes')

@section('content')
<div class="flex items-center justify-between mb-4">
    <h1 class="page-title">Rapports de notes</h1>
    <form method="GET" class="flex gap-2">
        <select name="trimestre_id" class="form-select" onchange="this.form.submit()">
            @foreach($trimestres as $t)
                <option value="{{ $t->id }}" @selected($trimestre?->id === $t->id)>{{ $t->nom }}</option>
            @endforeach
        </select>
    </form>
</div>

<div class="card overflow-hidden">
    <table class="data-table">
        <thead><tr><th>Classe</th><th>Effectif</th><th>Moyenne de classe</th></tr></thead>
        <tbody>
            @forelse($rapportParClasse as $ligne)
                <tr>
                    <td class="font-medium text-slate-900">{{ $ligne['classe']->nom }}</td>
                    <td>{{ $ligne['effectif'] }}</td>
                    <td>{{ $ligne['moyenne_classe'] !== null ? $ligne['moyenne_classe'].'/20' : '—' }}</td>
                </tr>
            @empty
                <tr><td colspan="3" class="text-center text-slate-400 py-6">Aucune donnée.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
