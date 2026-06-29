@props([
    'icon' => 'plus',
    'variant' => 'view',
])

@php
    $icons = [
        'plus' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>',
        'key' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 11-12 0 6 6 0 0112 0zM3 21l6.5-6.5"/>',
        'check' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>',
        'pause' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 9v6m4-6v6m7-3a9 9 0 11-18 0 9 9 0 0118 0z"/>',
    ];
    $path = $icons[$icon] ?? $icons['plus'];

    $classes = match ($variant) {
        'success' => 'btn-icon-success',
        'warning' => 'btn-icon-warning',
        'edit' => 'btn-icon-edit',
        default => 'btn-icon-view',
    };
@endphp

<button type="submit" {{ $attributes->merge(['class' => $classes]) }}>
    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">{!! $path !!}</svg>
    {{ $slot }}
</button>
