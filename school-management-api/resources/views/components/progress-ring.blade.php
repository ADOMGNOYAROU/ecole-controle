@props([
    'value', // 0-100 ou null
    'color' => 'green',
    'size' => 112,
])

@php
    $colors = [
        'green' => '#16a34a',
        'brand' => '#2647d6',
        'red' => '#dc2626',
        'amber' => '#d97706',
    ];
    $stroke = $colors[$color] ?? $colors['green'];
    $pct = $value !== null ? max(0, min(100, $value)) : 0;
    $radius = ($size / 2) - 8;
    $circumference = 2 * 3.14159265 * $radius;
    $offset = $circumference * (1 - $pct / 100);
@endphp

<div class="relative inline-flex items-center justify-center" style="width: {{ $size }}px; height: {{ $size }}px;">
    <svg width="{{ $size }}" height="{{ $size }}" class="-rotate-90">
        <circle cx="{{ $size / 2 }}" cy="{{ $size / 2 }}" r="{{ $radius }}" fill="none" stroke="#e2e8f0" stroke-width="8"/>
        @if($value !== null)
            <circle cx="{{ $size / 2 }}" cy="{{ $size / 2 }}" r="{{ $radius }}" fill="none" stroke="{{ $stroke }}"
                    stroke-width="8" stroke-linecap="round"
                    stroke-dasharray="{{ $circumference }}" stroke-dashoffset="{{ $offset }}"
                    style="transition: stroke-dashoffset 0.6s ease;"/>
        @endif
    </svg>
    <div class="absolute inset-0 flex flex-col items-center justify-center">
        <span class="text-2xl font-bold text-slate-900">{{ $value !== null ? $value.'%' : '—' }}</span>
    </div>
</div>
