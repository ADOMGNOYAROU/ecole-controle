@extends('layouts.app')

@section('title', 'Messagerie — '.$interlocuteur->name)

@section('content')
<div class="flex items-center gap-3 mb-4">
    <a href="{{ route('messagerie.index') }}" class="flex h-9 w-9 items-center justify-center rounded-full text-slate-500 hover:bg-slate-100 transition-colors">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
    </a>
    <x-avatar :name="$interlocuteur->name" class="h-9 w-9" />
    <div>
        <h1 class="font-semibold text-slate-900">{{ $interlocuteur->name }}</h1>
        <p class="text-xs text-slate-400">{{ $interlocuteur->isEnseignant() ? 'Enseignant' : 'Parent' }}</p>
    </div>
</div>

<div class="card flex flex-col h-[65vh]">
    <div class="flex-1 overflow-y-auto p-4 space-y-3" id="fil-messages">
        @forelse($messages as $message)
            @php $envoye = $message->expediteur_id === auth()->id(); @endphp
            <div class="flex {{ $envoye ? 'justify-end' : 'justify-start' }}">
                <div class="max-w-[75%] rounded-2xl px-4 py-2 {{ $envoye ? 'bg-brand-600 text-white' : 'bg-slate-100 text-slate-800' }}">
                    <p class="text-sm whitespace-pre-wrap break-words">{{ $message->contenu }}</p>
                    <p class="text-[10px] mt-1 {{ $envoye ? 'text-brand-100' : 'text-slate-400' }}">{{ $message->created_at->format('d/m/Y H:i') }}</p>
                </div>
            </div>
        @empty
            <p class="text-slate-400 text-sm text-center mt-8">Aucun message. Écrivez le premier ci-dessous.</p>
        @endforelse
    </div>

    <form method="POST" action="{{ route('messagerie.store', $interlocuteur) }}" class="border-t border-slate-200 p-3 flex items-end gap-2">
        @csrf
        <textarea name="contenu" rows="1" required maxlength="2000" placeholder="Écrire un message…" class="flex-1 resize-none rounded-lg border border-slate-200 bg-slate-50 py-2 px-3 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-brand-500"></textarea>
        <button type="submit" class="btn-primary shrink-0">Envoyer</button>
    </form>
    @error('contenu')
        <p class="text-xs text-red-600 px-3 pb-2">{{ $message }}</p>
    @enderror
</div>

<script>
    document.getElementById('fil-messages')?.scrollTo(0, document.getElementById('fil-messages').scrollHeight);
</script>
@endsection
