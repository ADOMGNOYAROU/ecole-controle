@props(['name' => ''])

@php
    $palette = [
        ['bg-brand-100', 'text-brand-700'],
        ['bg-amber-100', 'text-amber-700'],
        ['bg-emerald-100', 'text-emerald-700'],
        ['bg-rose-100', 'text-rose-700'],
        ['bg-violet-100', 'text-violet-700'],
        ['bg-cyan-100', 'text-cyan-700'],
    ];
    $index = $name !== '' ? crc32($name) % count($palette) : 0;
    [$bg, $text] = $palette[$index];

    $parts = array_filter(explode(' ', trim($name)));
    $initiales = strtoupper(collect($parts)->map(fn ($p) => mb_substr($p, 0, 1))->take(2)->implode(''));
@endphp

<span {{ $attributes->merge(['class' => "avatar $bg $text"]) }}>{{ $initiales ?: '?' }}</span>
