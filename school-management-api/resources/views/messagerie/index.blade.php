@extends('layouts.app')

@section('title', 'Messagerie')

@section('content')
<div class="flex items-center justify-between mb-4">
    <h1 class="page-title">Messagerie</h1>
</div>

<div class="space-y-2">
    @forelse($conversations as $conversation)
        @php $interlocuteur = $conversation['interlocuteur']; @endphp
        <a href="{{ route('messagerie.show', $interlocuteur) }}" class="card card-hover p-4 flex items-center gap-3">
            <x-avatar :name="$interlocuteur->name" class="h-10 w-10 shrink-0" />
            <div class="min-w-0 flex-1">
                <div class="flex items-center justify-between gap-2">
                    <p class="font-semibold text-slate-900 truncate">{{ $interlocuteur->name }}</p>
                    @if($conversation['dernier_message'])
                        <span class="text-xs text-slate-400 shrink-0">{{ $conversation['dernier_message']->created_at->diffForHumans() }}</span>
                    @endif
                </div>
                <p class="text-sm text-slate-500 truncate">
                    {{ $conversation['dernier_message']?->contenu ?? 'Aucun message pour le moment — démarrez la conversation.' }}
                </p>
            </div>
            @if($conversation['non_lus'] > 0)
                <span class="flex h-5 min-w-[1.25rem] items-center justify-center rounded-full bg-red-500 px-1.5 text-[11px] font-bold text-white shrink-0">{{ $conversation['non_lus'] }}</span>
            @endif
        </a>
    @empty
        <div class="card p-6 text-slate-400">
            @if(auth()->user()->isEnseignant())
                Aucun parent à contacter pour le moment — vous devez être affecté à une classe.
            @else
                Aucun enseignant à contacter pour le moment.
            @endif
        </div>
    @endforelse
</div>
@endsection
