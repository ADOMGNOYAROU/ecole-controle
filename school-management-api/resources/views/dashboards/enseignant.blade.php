@extends('layouts.app')

@section('title', 'Tableau de bord')

@section('content')
<div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
    <div class="card p-5 lg:col-span-2">
        <x-card-header title="Mes classes" icon="classes" color="violet" />
        <table class="data-table">
            <thead><tr><th>Classe</th><th>Effectif</th><th></th></tr></thead>
            <tbody>
                @forelse($classes as $classe)
                    <tr>
                        <td class="font-medium text-slate-900">{{ $classe->nom }}</td>
                        <td>{{ $classe->eleves_count }}</td>
                        <td class="text-right"><a href="{{ route('classes.show', $classe) }}" class="btn-icon-view">Voir</a></td>
                    </tr>
                @empty
                    <tr><td colspan="3" class="text-center text-slate-400 py-6">Aucune classe assignée.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="card p-5">
        <x-card-header title="Annonces récentes" icon="megaphone" color="amber" />
        <ul class="space-y-3">
            @forelse($annonces as $annonce)
                <li class="text-sm">
                    <p class="font-medium text-slate-800">{{ $annonce->titre }}</p>
                    <p class="text-slate-500">{{ $annonce->date_publication->format('d/m/Y') }}</p>
                </li>
            @empty
                <li class="text-sm text-slate-400">Aucune annonce.</li>
            @endforelse
        </ul>
    </div>
</div>

<div class="card p-5 mt-6">
    <x-card-header title="Dernières notes que j'ai saisies" icon="list" />
    <table class="data-table">
        <thead><tr><th>Élève</th><th>Matière</th><th>Note</th><th>Date</th></tr></thead>
        <tbody>
            @forelse($dernieresNotes as $note)
                @php
                    $sur20 = $note->noteSur20();
                    $pillClass = match (true) {
                        $sur20 >= 14 => 'stat-pill-good',
                        $sur20 >= 10 => 'stat-pill-mid',
                        default => 'stat-pill-bad',
                    };
                @endphp
                <tr>
                    <td>
                        <div class="flex items-center gap-3">
                            <x-avatar :name="$note->eleve->nomComplet()" />
                            <span class="font-medium text-slate-900">{{ $note->eleve->nomComplet() }}</span>
                        </div>
                    </td>
                    <td>{{ $note->matiere->nom }}</td>
                    <td><span class="{{ $pillClass }}">{{ $sur20 }}/20</span></td>
                    <td class="text-slate-500">{{ $note->date_evaluation->format('d/m/Y') }}</td>
                </tr>
            @empty
                <tr><td colspan="4" class="text-center text-slate-400 py-6">Aucune note saisie.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
