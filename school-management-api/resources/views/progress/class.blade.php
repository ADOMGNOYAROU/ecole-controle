@extends('layouts.app')

@section('title', 'Suivi de la Classe')

@section('content')
<div class="container mx-auto px-4 py-8">
    <h1 class="text-2xl font-bold mb-6">Suivi des Progrès de la Classe</h1>
    
    <div class="bg-white rounded-lg shadow-md p-6 mb-6">
        <h2 class="text-xl font-semibold mb-4">Aperçu des moyennes</h2>
        <div class="overflow-x-auto">
            <table class="min-w-full bg-white">
                <thead>
                    <tr>
                        <th class="py-2 px-4 border-b border-gray-200 bg-gray-50 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                            Élève
                        </th>
                        @foreach($matieres as $matiere)
                        <th class="py-2 px-4 border-b border-gray-200 bg-gray-50 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider">
                            {{ $matiere->nom }}
                        </th>
                        @endforeach
                    </tr>
                </thead>
                <tbody class="bg-white">
                    @foreach($progressData as $eleve)
                    <tr class="hover:bg-gray-50">
                        <td class="py-3 px-4 border-b border-gray-200 whitespace-nowrap">
                            {{ $eleve['eleve'] }}
                        </td>
                        @foreach($matieres as $matiere)
                        <td class="py-3 px-4 border-b border-gray-200 text-center">
                            @if(isset($eleve['matieres'][$matiere->nom]))
                                @php
                                    $notes = collect($eleve['matieres'][$matiere->nom]);
                                    $moyenne = $notes->avg('valeur');
                                    $couleur = $moyenne >= 10 ? 'text-green-600' : 'text-red-600';
                                @endphp
                                <span class="font-semibold {{ $couleur }}">
                                    {{ number_format($moyenne, 2) }}/20
                                </span>
                            @else
                                <span class="text-gray-400">-</span>
                            @endif
                        </td>
                        @endforeach
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    @foreach($progressData as $eleve)
    <div class="bg-white rounded-lg shadow-md p-6 mb-6">
        <h3 class="text-xl font-semibold mb-4">{{ $eleve['eleve'] }}</h3>
        <div class="chart-container" style="position: relative; height:300px; width:100%">
            <canvas id="chart-{{ $loop->index }}"></canvas>
        </div>
    </div>
    @endforeach
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        @foreach($progressData as $index => $eleve)
        const ctx{{ $index }} = document.getElementById('chart-{{ $index }}').getContext('2d');
        
        const data{{ $index }} = {
            @foreach($eleve['matieres'] as $matiere => $notes)
            '{{ $matiere }}': {!! json_encode($notes) !!},
            @endforeach
        };

        const datasets{{ $index }} = Object.entries(data{{ $index }}).map(([matiere, notesData]) => {
            return {
                label: matiere,
                data: notesData.map(note => ({
                    x: note.date,
                    y: parseFloat(note.valeur)
                })),
                borderColor: getRandomColor(),
                backgroundColor: 'rgba(0, 0, 0, 0)',
                tension: 0.3,
                fill: false
            };
        });

        new Chart(ctx{{ $index }}, {
            type: 'line',
            data: {
                datasets: datasets{{ $index }}
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    x: {
                        type: 'time',
                        time: {
                            unit: 'day',
                            tooltipFormat: 'DD/MM/YYYY',
                            displayFormats: {
                                day: 'DD/MM'
                            }
                        },
                        title: {
                            display: true,
                            text: 'Date'
                        }
                    },
                    y: {
                        min: 0,
                        max: 20,
                        title: {
                            display: true,
                            text: 'Note /20'
                        }
                    }
                },
                plugins: {
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return `${context.dataset.label}: ${context.parsed.y}/20`;
                            }
                        }
                    }
                }
            }
        });
        @endforeach

        function getRandomColor() {
            const letters = '0123456789ABCDEF';
            let color = '#';
            for (let i = 0; i < 6; i++) {
                color += letters[Math.floor(Math.random() * 16)];
            }
            return color;
        }
    });
</script>
@endpush
@endsection
