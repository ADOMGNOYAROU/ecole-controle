@extends('layouts.app')

@section('title', 'Mes présences')

@section('content')
<h1 class="page-title mb-4">Mes présences</h1>

<div class="card overflow-hidden">
    <table class="data-table">
        <thead><tr><th>Date</th><th>Statut</th><th>Motif</th></tr></thead>
        <tbody>
            @forelse($presences as $presence)
                <tr>
                    <td>{{ $presence->date->format('d/m/Y') }}</td>
                    <td><span class="badge-{{ match($presence->statut) { 'present' => 'green', 'retard' => 'yellow', default => 'red' } }}">{{ $presence->statut }}</span></td>
                    <td>{{ $presence->motif ?? '—' }}</td>
                </tr>
            @empty
                <tr><td colspan="3" class="text-center text-slate-400 py-6">Aucune présence enregistrée.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="mt-4">{{ $presences->links() }}</div>
@endsection
