@extends('layouts.app')

@section('title', 'Notifications')

@section('content')
<div class="flex items-center justify-between mb-4">
    <h1 class="page-title">Notifications</h1>
    <form method="POST" action="{{ route('notifications.toutes-lues') }}">
        @csrf @method('PATCH')
        <button type="submit" class="btn-secondary">Tout marquer comme lu</button>
    </form>
</div>

<div class="space-y-3">
    @forelse($notifications as $notification)
        <div class="card p-4 flex items-start justify-between {{ $notification->lu ? 'opacity-60' : '' }}">
            <div>
                <p class="font-medium text-slate-900">{{ $notification->titre }}</p>
                <p class="text-sm text-slate-600">{{ $notification->message }}</p>
                <p class="text-xs text-slate-400 mt-1">{{ $notification->created_at->format('d/m/Y H:i') }}</p>
            </div>
            @unless($notification->lu)
                <form method="PATCH" action="{{ route('notifications.lue', $notification) }}">
                    @csrf
                    <button type="submit" class="text-sm text-brand-600 hover:underline">Marquer comme lue</button>
                </form>
            @endunless
        </div>
    @empty
        <div class="card p-6 text-slate-400">Aucune notification.</div>
    @endforelse
</div>

<div class="mt-4">{{ $notifications->links() }}</div>
@endsection
