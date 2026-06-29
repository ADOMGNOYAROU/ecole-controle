@extends('layouts.app')

@section('title', 'Écoles')

@section('content')
<h1 class="page-title mb-4">Écoles inscrites</h1>

<form method="GET" class="filter-bar mb-4">
    <div>
        <label class="form-label">Recherche</label>
        <input type="text" name="recherche" value="{{ request('recherche') }}" class="form-input" placeholder="Nom, ville">
    </div>
    <div>
        <label class="form-label">Statut</label>
        <select name="statut" class="form-select">
            <option value="">Tous</option>
            <option value="essai" @selected(request('statut') === 'essai')>Essai</option>
            <option value="actif" @selected(request('statut') === 'actif')>Actif</option>
            <option value="suspendu" @selected(request('statut') === 'suspendu')>Suspendu</option>
            <option value="expire" @selected(request('statut') === 'expire')>Expiré</option>
        </select>
    </div>
    <div>
        <label class="form-label">Plan</label>
        <select name="plan" class="form-select">
            <option value="">Tous</option>
            <option value="gratuit" @selected(request('plan') === 'gratuit')>Gratuit</option>
            <option value="premium" @selected(request('plan') === 'premium')>Premium</option>
        </select>
    </div>
    <button type="submit" class="btn-secondary">Filtrer</button>
</form>

<div class="card overflow-hidden">
    <table class="data-table">
        <thead><tr><th>École</th><th>Ville</th><th>Plan</th><th>Statut</th><th>Utilisateurs</th><th>Essai jusqu'au</th><th></th></tr></thead>
        <tbody>
            @forelse($ecoles as $ecole)
                <tr>
                    <td>
                        <div class="flex items-center gap-3">
                            <x-avatar :name="$ecole->nom" />
                            <span class="font-medium text-slate-900">{{ $ecole->nom }}</span>
                        </div>
                    </td>
                    <td>{{ $ecole->ville ?? '—' }}</td>
                    <td><span class="badge-{{ $ecole->plan === 'premium' ? 'brand' : 'slate' }}">{{ $ecole->plan }}</span></td>
                    <td>
                        <span class="badge-{{ match($ecole->statut) { 'actif' => 'green', 'suspendu' => 'red', 'expire' => 'yellow', default => 'slate' } }}">{{ $ecole->statut }}</span>
                    </td>
                    <td>{{ $ecole->users_count }}</td>
                    <td>{{ $ecole->trial_ends_at?->format('d/m/Y') ?? '—' }}</td>
                    <td class="text-right space-x-1.5 whitespace-nowrap">
                        <x-action-link :href="route('super-admin.ecoles.show', $ecole)" type="view">Voir</x-action-link>
                        @if($ecole->statut === 'suspendu')
                            <form method="PATCH" action="{{ route('super-admin.ecoles.activer', $ecole) }}" class="inline">
                                @csrf
                                <x-icon-button icon="check" variant="success">Activer</x-icon-button>
                            </form>
                        @else
                            <form method="PATCH" action="{{ route('super-admin.ecoles.suspendre', $ecole) }}" class="inline" onsubmit="return confirm('Suspendre cette école ?');">
                                @csrf
                                <x-icon-button icon="pause" variant="warning">Suspendre</x-icon-button>
                            </form>
                        @endif
                    </td>
                </tr>
            @empty
                <tr><td colspan="7" class="text-center text-slate-400 py-6">Aucune école trouvée.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="mt-4">{{ $ecoles->links() }}</div>
@endsection
