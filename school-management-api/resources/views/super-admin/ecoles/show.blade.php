@extends('layouts.app')

@section('title', $ecole->nom)

@php
    $libellesRoles = ['admin' => 'Directeur / admin', 'enseignant' => 'Enseignant', 'parent' => 'Parent', 'eleve' => 'Élève'];
@endphp

@section('content')
<div class="flex flex-wrap items-start justify-between gap-3 mb-4" x-data="{ support: {{ $errors->has('motif') ? 'true' : 'false' }} }">
    <div>
        <h1 class="page-title">{{ $ecole->nom }}</h1>
        <p class="text-sm text-slate-500">{{ $ecole->ville ?? 'Ville non renseignée' }} · {{ $ecole->email_contact ?? 'sans email' }} · {{ $ecole->telephone ?? 'sans téléphone' }}</p>
    </div>
    <div class="flex items-center gap-2">
        @if($directeur)
            <button type="button" class="btn-secondary" @click="support = ! support">Accéder en mode support</button>
        @endif
        @if($ecole->statut === 'suspendu')
            <form method="POST" action="{{ route('super-admin.ecoles.activer', $ecole) }}">
                @csrf
                @method('PATCH')
                <x-icon-button icon="check" variant="success">Activer</x-icon-button>
            </form>
        @else
            <form method="POST" action="{{ route('super-admin.ecoles.suspendre', $ecole) }}" onsubmit="return confirm('Suspendre cette école ?');">
                @csrf
                @method('PATCH')
                <x-icon-button icon="pause" variant="warning">Suspendre</x-icon-button>
            </form>
        @endif
    </div>

    @if($directeur)
        <form x-show="support" x-cloak method="POST" action="{{ route('super-admin.ecoles.support', $ecole) }}" class="w-full card p-4 border-orange-200 bg-orange-50">
            @csrf
            <p class="text-sm text-orange-900 mb-2">
                Vous verrez l'école comme <strong>{{ $directeur->name }}</strong>, <strong>en lecture seule</strong>, pendant 30 minutes.
                Les messages privés restent fermés. Votre entrée, le motif et chaque page vue sont notés dans le journal d'audit.
            </p>
            <label class="form-label" for="motif">Motif de l'accès</label>
            <div class="flex flex-wrap gap-2">
                <input id="motif" type="text" name="motif" value="{{ old('motif') }}" class="form-input flex-1 min-w-[16rem]" placeholder="Ex. : appel du directeur, bulletins introuvables" required minlength="10" maxlength="255">
                <button type="submit" class="btn-primary">Entrer en mode support</button>
            </div>
            @error('motif')<p class="text-sm text-red-600 mt-1">{{ $message }}</p>@enderror
        </form>
    @endif
</div>

@if($acces = session('acces_temporaire'))
    <div class="card p-5 mb-6 border-green-300 bg-green-50">
        <h2 class="font-semibold text-green-900 mb-2">Accès réinitialisé pour {{ $acces['nom'] }}</h2>
        <p class="text-sm text-green-900">Identifiant : <strong>{{ $acces['email'] }}</strong></p>
        <p class="text-sm text-green-900">Mot de passe temporaire : <strong class="font-mono text-base">{{ $acces['mot_de_passe'] }}</strong></p>
        <p class="text-xs text-green-800 mt-2">Ce mot de passe ne sera plus jamais affiché. Envoyez-le maintenant ; la personne devra le changer.</p>
        <a href="{{ $acces['whatsapp'] }}" target="_blank" rel="noopener" class="btn-primary mt-3 inline-flex">Envoyer sur WhatsApp</a>
    </div>
@endif

<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4 mb-6">
    <x-stat-card label="Élèves" :value="$stats['eleves']" icon="users" />
    <x-stat-card label="Enseignants" :value="$stats['enseignants']" icon="academic-cap" color="violet" />
    <x-stat-card label="Classes" :value="$stats['classes']" icon="building" color="amber" />
    <x-stat-card label="Utilisateurs" :value="$stats['utilisateurs']" icon="chart" color="slate" />
    <div class="card p-5">
        <p class="text-sm text-slate-500">Santé (30 jours)</p>
        <p class="text-2xl font-semibold {{ match($sante['niveau'] ?? 'faible') { 'bonne' => 'text-green-600', 'moyenne' => 'text-amber-600', default => 'text-red-600' } }}">{{ $sante['score'] ?? 0 }}/100</p>
        <p class="text-xs text-slate-400 mt-1">{{ $sante['notes'] ?? 0 }} notes · {{ $sante['presences'] ?? 0 }} appels · {{ $sante['paiements'] ?? 0 }} paiements</p>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <div class="card p-5">
        <h2 class="font-semibold text-slate-900 mb-3">Abonnement</h2>
        <dl class="space-y-2 text-sm">
            <div class="flex justify-between"><dt class="text-slate-500">Plan</dt><dd><span class="badge-{{ $ecole->plan === 'premium' ? 'brand' : 'slate' }}">{{ $ecole->plan }}</span></dd></div>
            <div class="flex justify-between"><dt class="text-slate-500">Statut école</dt><dd><span class="badge-{{ match($ecole->statut) { 'actif' => 'green', 'suspendu' => 'red', 'expire' => 'yellow', default => 'slate' } }}">{{ $ecole->statut }}</span></dd></div>
            <div class="flex justify-between"><dt class="text-slate-500">Accès Premium</dt><dd>{{ $ecole->aAccesPremium() ? 'Oui' : 'Non' }}</dd></div>
            @if($ecole->trial_ends_at)
                <div class="flex justify-between"><dt class="text-slate-500">Fin d'essai</dt><dd>{{ $ecole->trial_ends_at->format('d/m/Y') }} @if($ecole->estEnEssai())({{ $ecole->joursEssaiRestants() }} j)@endif</dd></div>
            @endif
            @if($abonnementActif)
                <div class="flex justify-between"><dt class="text-slate-500">Abonnement actif jusqu'au</dt><dd>{{ $abonnementActif->date_fin->format('d/m/Y') }}</dd></div>
            @endif
            <div class="flex justify-between"><dt class="text-slate-500">Inscrite le</dt><dd>{{ $ecole->created_at->format('d/m/Y') }}</dd></div>
        </dl>

        @if(in_array($ecole->statut, ['essai', 'expire'], true))
            <form method="POST" action="{{ route('super-admin.ecoles.prolonger-essai', $ecole) }}" class="mt-4 pt-4 border-t border-slate-100 flex items-center gap-2">
                @csrf
                <select name="jours" class="form-select">
                    <option value="7">+ 7 jours</option>
                    <option value="15">+ 15 jours</option>
                    <option value="30">+ 30 jours</option>
                </select>
                <button type="submit" class="btn-secondary">Prolonger l'essai</button>
            </form>
        @endif
    </div>

    <div class="card overflow-hidden lg:col-span-2">
        <div class="p-5 pb-0"><h2 class="font-semibold text-slate-900">Historique des factures</h2></div>
        <table class="data-table">
            <thead><tr><th>Montant</th><th>Échéance</th><th>Statut</th><th>Méthode</th><th>Payée le</th></tr></thead>
            <tbody>
                @forelse($ecole->factures as $facture)
                    <tr>
                        <td>{{ number_format($facture->montant, 0, ',', ' ') }} FCFA</td>
                        <td class="{{ $facture->statut === 'en_attente' && $facture->date_echeance->isPast() ? 'text-red-600 font-medium' : 'text-slate-500' }}">{{ $facture->date_echeance->format('d/m/Y') }}</td>
                        <td><span class="badge-{{ match($facture->statut) { 'payee' => 'green', 'en_retard' => 'red', 'annulee' => 'slate', default => 'yellow' } }}">{{ $facture->statut }}</span></td>
                        <td>{{ $facture->methode_paiement ?? '—' }}</td>
                        <td>{{ $facture->payee_le?->format('d/m/Y') ?? '—' }}</td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="text-center text-slate-400 py-6">Aucune facture pour cette école.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mt-6">
    <div class="card overflow-x-auto lg:col-span-2">
        <div class="p-5 pb-0"><h2 class="font-semibold text-slate-900">Utilisateurs</h2></div>
        <table class="data-table">
            <thead><tr><th>Nom</th><th>Rôle</th><th>Contact</th><th>Dernière connexion</th><th></th></tr></thead>
            <tbody>
                @forelse($utilisateurs as $utilisateur)
                    <tr>
                        <td class="font-medium text-slate-900">{{ $utilisateur->name }}</td>
                        <td>{{ $libellesRoles[$utilisateur->role] ?? $utilisateur->role }}</td>
                        <td class="text-xs text-slate-500">{{ $utilisateur->email }}@if($utilisateur->phone)<br>{{ $utilisateur->phone }}@endif</td>
                        <td class="{{ $utilisateur->derniere_connexion_le?->gte(now()->subDays(14)) ? 'text-slate-600' : 'text-red-600' }}">{{ $utilisateur->derniere_connexion_le?->format('d/m/Y H:i') ?? 'jamais' }}</td>
                        <td class="text-right">
                            <form method="POST" action="{{ route('super-admin.ecoles.reinitialiser-acces', [$ecole, $utilisateur]) }}" onsubmit="return confirm('Créer un mot de passe temporaire pour {{ addslashes($utilisateur->name) }} ? L\'ancien ne fonctionnera plus.');">
                                @csrf
                                <button type="submit" class="text-sm text-brand-600 hover:underline">Réinitialiser l'accès</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="text-center text-slate-400 py-6">Aucun utilisateur.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="card p-5">
        <h2 class="font-semibold text-slate-900">Notes internes</h2>
        <p class="text-xs text-slate-400 mb-3">Visibles uniquement par le super administrateur.</p>
        <form method="POST" action="{{ route('super-admin.ecoles.notes.store', $ecole) }}" class="mb-4">
            @csrf
            <textarea name="contenu" rows="3" class="form-input w-full" placeholder="Ex. : appelé le directeur, rappeler lundi pour le Premium" required maxlength="2000">{{ old('contenu') }}</textarea>
            @error('contenu')<p class="text-sm text-red-600 mt-1">{{ $message }}</p>@enderror
            <button type="submit" class="btn-secondary mt-2">Ajouter la note</button>
        </form>
        <ul class="space-y-3">
            @forelse($notesInternes as $note)
                <li class="rounded-lg bg-slate-50 p-3 text-sm">
                    <p class="whitespace-pre-line text-slate-800">{{ $note->contenu }}</p>
                    <div class="mt-1 flex items-center justify-between text-xs text-slate-400">
                        <span>{{ $note->auteur?->name ?? 'Compte supprimé' }} · {{ $note->created_at->format('d/m/Y H:i') }}</span>
                        <form method="POST" action="{{ route('super-admin.ecoles.notes.destroy', [$ecole, $note]) }}" onsubmit="return confirm('Supprimer cette note ?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="hover:text-red-600">Supprimer</button>
                        </form>
                    </div>
                </li>
            @empty
                <li class="text-sm text-slate-400">Aucune note pour l'instant.</li>
            @endforelse
        </ul>
    </div>
</div>

<div class="flex items-center justify-between mt-6 mb-3">
    <h2 class="font-semibold text-slate-900">Activité récente</h2>
    <a href="{{ route('super-admin.journal.index', ['ecole_id' => $ecole->id]) }}" class="text-sm text-indigo-600 hover:underline">Tout le journal de cette école</a>
</div>
@include('super-admin.journal._tableau', ['entrees' => $activite, 'avecEcole' => false])
@endsection
