@props(['href'])

@php
    $user = auth()->user();
    $premium = $user->isSuperAdmin() || $user->ecole?->aAccesPremium();
@endphp

<a href="{{ $href }}" class="btn-secondary" title="{{ $premium ? 'Télécharger le rapport PDF' : 'Fonctionnalité Premium' }}">
    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m-9 8h12a2 2 0 002-2V8a2 2 0 00-2-2h-3.586a1 1 0 01-.707-.293l-1.414-1.414A1 1 0 0011.586 4H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
    Exporter en PDF
    @unless($premium)
        <span class="badge-brand ml-1">Premium</span>
    @endunless
</a>
