@extends('layouts.app')

@section('title', 'Tableau de bord')

@section('content')
<div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
    <x-stat-card label="Moyenne {{ $trimestre?->nom ?? '' }}" :value="$moyenne !== null ? $moyenne.'/20' : '—'" icon="chart" color="brand" />
    <x-stat-card label="Taux de présence" :value="$tauxPresence !== null ? $tauxPresence.'%' : '—'" icon="check-circle" color="green" />
    <x-stat-card label="Solde dû" :value="number_format($solde, 0, ',', ' ').' F'" icon="wallet" :color="$solde > 0 ? 'red' : 'green'" />
</div>

<div class="grid grid-cols-1 gap-6 lg:grid-cols-3 mt-6">
    <div class="card p-5 lg:col-span-2">
        <x-card-header title="Dernières notes" icon="list" />
        <table class="data-table">
            <thead><tr><th>Matière</th><th>Note</th><th>Date</th></tr></thead>
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
                        <td class="font-medium text-slate-900">{{ $note->matiere->nom }}</td>
                        <td><span class="{{ $pillClass }}">{{ $sur20 }}/20</span></td>
                        <td class="text-slate-500">{{ $note->date_evaluation->format('d/m/Y') }}</td>
                    </tr>
                @empty
                    <tr><td colspan="3" class="text-center text-slate-400 py-6">Aucune note.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="card p-5">
        <x-card-header title="Annonces" icon="megaphone" color="amber" />
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
@endsection
