@props([
    'title',
    'icon' => 'list',
    'color' => 'brand',
])

@php
    $palettes = [
        'brand' => ['bg-brand-50', 'text-brand-600'],
        'green' => ['bg-green-50', 'text-green-600'],
        'red' => ['bg-red-50', 'text-red-600'],
        'amber' => ['bg-amber-50', 'text-amber-600'],
        'violet' => ['bg-violet-50', 'text-violet-600'],
        'slate' => ['bg-slate-100', 'text-slate-600'],
    ];
    [$bg, $text] = $palettes[$color] ?? $palettes['brand'];

    $icons = [
        'list' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17h6m-6-4h6m-6-4h6M5 21h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v14a2 2 0 002 2z"/>',
        'megaphone' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z"/>',
        'presence' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>',
        'classes' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5zm0 0l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z"/>',
        'building' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2M5 21H3m4-2h.01M11 9h.01M11 13h.01M15 9h.01M15 13h.01M9 21v-4a1 1 0 011-1h4a1 1 0 011 1v4"/>',
        'receipt' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 14l2 2 4-4m3 9V5a2 2 0 00-2-2H8a2 2 0 00-2 2v16l3-2 3 2 3-2 3 2z"/>',
    ];
    $path = $icons[$icon] ?? $icons['list'];
@endphp

<div class="flex items-center justify-between mb-4">
    <div class="flex items-center gap-3">
        <span class="flex h-9 w-9 items-center justify-center rounded-lg {{ $bg }} {{ $text }}">
            <svg class="w-[18px] h-[18px]" fill="none" stroke="currentColor" viewBox="0 0 24 24">{!! $path !!}</svg>
        </span>
        <h2 class="font-semibold text-slate-900">{{ $title }}</h2>
    </div>
    @isset($slot)
        @if(trim($slot))
            <div>{{ $slot }}</div>
        @endif
    @endisset
</div>
