@props([
    'points' => [],
    'color' => '#2647d6',
    'width' => 100,
    'height' => 36,
])

@php
    $values = array_values($points);
    $max = max($values ?: [0]);
    $min = min($values ?: [0]);
    $range = max($max - $min, 1);
    $count = max(count($values) - 1, 1);

    $coords = [];
    foreach ($values as $i => $v) {
        $x = round(($i / $count) * $width, 2);
        $y = round($height - (($v - $min) / $range) * ($height - 4) - 2, 2);
        $coords[] = "$x,$y";
    }
    $linePath = implode(' ', $coords);
    $areaPath = "0,{$height} " . $linePath . " {$width},{$height}";
    $gradientId = 'spark-' . substr(md5($linePath . $color), 0, 8);
@endphp

<svg width="{{ $width }}" height="{{ $height }}" viewBox="0 0 {{ $width }} {{ $height }}" class="overflow-visible">
    <defs>
        <linearGradient id="{{ $gradientId }}" x1="0" y1="0" x2="0" y2="1">
            <stop offset="0%" stop-color="{{ $color }}" stop-opacity="0.35"/>
            <stop offset="100%" stop-color="{{ $color }}" stop-opacity="0"/>
        </linearGradient>
    </defs>
    <polygon points="{{ $areaPath }}" fill="url(#{{ $gradientId }})"/>
    <polyline points="{{ $linePath }}" fill="none" stroke="{{ $color }}" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
</svg>
