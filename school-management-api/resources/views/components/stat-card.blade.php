@props([
    'label',
    'value',
    'color' => 'brand',
    'icon' => 'chart',
    'trend' => null,
    'trendDirection' => 'flat',
    'spark' => null,
])

@php
    $palettes = [
        'brand' => ['from-brand-500 to-brand-600', 'text-brand-600', 'bg-brand-500', '#2647d6'],
        'green' => ['from-green-500 to-emerald-600', 'text-green-600', 'bg-green-500', '#16a34a'],
        'red' => ['from-red-500 to-rose-600', 'text-red-600', 'bg-red-500', '#dc2626'],
        'amber' => ['from-amber-400 to-orange-500', 'text-amber-600', 'bg-amber-500', '#d97706'],
        'violet' => ['from-violet-500 to-purple-600', 'text-violet-600', 'bg-violet-500', '#7c3aed'],
        'slate' => ['from-slate-400 to-slate-600', 'text-slate-600', 'bg-slate-500', '#64748b'],
    ];
    [$gradient, $text, $accent, $hex] = $palettes[$color] ?? $palettes['brand'];

    $icons = [
        'users' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a4 4 0 00-3-3.87M9 20H4v-2a4 4 0 014-4h2a4 4 0 014 4v2zm0-10a4 4 0 100-8 4 4 0 000 8zm8-2a3 3 0 11-6 0 3 3 0 016 0z"/>',
        'academic-cap' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5zm0 0l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z"/>',
        'building' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2M5 21H3m4-2h.01M11 9h.01M11 13h.01M15 9h.01M15 13h.01M9 21v-4a1 1 0 011-1h4a1 1 0 011 1v4"/>',
        'currency' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>',
        'chart' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>',
        'check-circle' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>',
        'wallet' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a2.25 2.25 0 00-2.25-2.25H5.25A2.25 2.25 0 003 12m18 0v6a2.25 2.25 0 01-2.25 2.25H5.25A2.25 2.25 0 013 18v-6m18 0V9a2.25 2.25 0 00-2.25-2.25H5.25A2.25 2.25 0 003 9v3m15 3h.01"/>',
    ];
    $path = $icons[$icon] ?? $icons['chart'];

    $trendColors = [
        'up' => 'text-green-600',
        'down' => 'text-red-600',
        'flat' => 'text-slate-400',
    ];
@endphp

<div class="card-hover relative overflow-hidden p-5">
    <span class="absolute inset-x-0 top-0 h-1 {{ $accent }}"></span>
    <div class="flex items-center justify-between">
        <p class="text-sm font-medium text-slate-500">{{ $label }}</p>
        <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-gradient-to-br {{ $gradient }} text-white shadow-sm">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">{!! $path !!}</svg>
        </span>
    </div>
    <p class="mt-3 text-3xl font-bold {{ $text }}">{{ $value }}</p>

    @if($trend)
        <p class="mt-1 text-xs font-medium {{ $trendColors[$trendDirection] ?? 'text-slate-400' }}">{{ $trend }}</p>
    @endif

    @if($spark)
        <div class="mt-3 -mb-1 -mx-1">
            <x-sparkline :points="$spark" :color="$hex" :width="160" :height="36" />
        </div>
    @endif
</div>
