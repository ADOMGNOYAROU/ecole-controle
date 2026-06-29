@extends('layouts.app')

@section('title', $enseignant->nomComplet())

@section('content')
<div class="flex items-center justify-between mb-4">
    <div>
        <h1 class="page-title">{{ $enseignant->nomComplet() }}</h1>
        <p class="text-sm text-slate-500">{{ $enseignant->specialite ?? 'Sans spécialité' }}</p>
    </div>
    <a href="{{ route('enseignants.edit', $enseignant) }}" class="btn-secondary">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
        Modifier
    </a>
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
    <div class="card p-5">
        <h2 class="font-semibold text-slate-900 mb-3">Classes principales</h2>
        <ul class="text-sm space-y-1">
            @forelse($enseignant->classesPrincipales as $classe)
                <li><a href="{{ route('classes.show', $classe) }}" class="text-brand-600 hover:underline">{{ $classe->nom }}</a></li>
            @empty
                <li class="text-slate-400">Aucune classe principale.</li>
            @endforelse
        </ul>

        <h2 class="font-semibold text-slate-900 mt-5 mb-3">Responsabilités</h2>
        <ul class="text-sm space-y-1">
            @forelse($enseignant->responsabilites as $resp)
                <li>{{ $resp->type }} — {{ $resp->description ?? '' }}</li>
            @empty
                <li class="text-slate-400">Aucune responsabilité.</li>
            @endforelse
        </ul>
    </div>

    <div class="card p-5">
        <h2 class="font-semibold text-slate-900 mb-3">Matières & classes enseignées</h2>
        <table class="data-table">
            <thead><tr><th>Matière</th><th>Classe</th></tr></thead>
            <tbody>
                @forelse($enseignant->matieres as $matiere)
                    <tr><td>{{ $matiere->nom }}</td><td>{{ \App\Models\Classe::find($matiere->pivot->classe_id)?->nom }}</td></tr>
                @empty
                    <tr><td colspan="2" class="text-center text-slate-400 py-6">Aucune affectation.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
