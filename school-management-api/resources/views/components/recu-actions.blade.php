@props(['paiement', 'grand' => false])

@php
    $classes = $grand ? 'btn-secondary' : 'text-sm font-medium hover:underline';
    $tuteurs = $paiement->eleve->tuteurs->filter(fn ($t) => \App\Support\WhatsApp::numeroInternational($t->telephone));
@endphp

<span class="inline-flex flex-wrap items-center gap-3">
    <a href="{{ route('paiements.recu', $paiement) }}" target="_blank" rel="noopener" class="{{ $classes }} text-brand-700">Reçu PDF</a>
    @forelse($tuteurs as $tuteur)
        <a href="{{ $paiement->lienWhatsApp($tuteur) }}" target="_blank" rel="noopener" class="{{ $classes }} text-green-700" title="Envoyer le reçu à {{ $tuteur->prenom }} {{ $tuteur->nom }} sur WhatsApp">
            WhatsApp {{ $tuteurs->count() > 1 ? '· '.$tuteur->prenom : '' }}
        </a>
    @empty
        <span class="text-xs text-slate-400" title="Ajoutez le téléphone d'un parent pour envoyer le reçu sur WhatsApp">Pas de téléphone parent</span>
    @endforelse
</span>
