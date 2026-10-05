@extends('layouts.app')

@section('title', "Journal d'audit")

@section('content')
<div class="flex flex-wrap items-center justify-between gap-3 mb-4">
    <div>
        <h1 class="page-title">Journal d'audit</h1>
        <p class="text-sm text-slate-500">Toutes les actions de la plateforme. Les entrées ne peuvent être ni modifiées ni supprimées ; elles sont conservées 12 mois.</p>
    </div>
    <a href="{{ route('super-admin.journal.export', request()->query()) }}" class="btn-secondary">Exporter en CSV</a>
</div>

<form method="GET" class="filter-bar mb-4">
    <div>
        <label class="form-label">École</label>
        <select name="ecole_id" class="form-select">
            <option value="">Toutes</option>
            @foreach($ecoles as $ecoleOption)
                <option value="{{ $ecoleOption->id }}" @selected(request('ecole_id') == $ecoleOption->id)>{{ $ecoleOption->nom }}</option>
            @endforeach
        </select>
    </div>
    <div>
        <label class="form-label">Action</label>
        <select name="action" class="form-select">
            <option value="">Toutes</option>
            @foreach($actions as $code => $libelle)
                <option value="{{ $code }}" @selected(request('action') === $code)>{{ $libelle }}</option>
            @endforeach
        </select>
    </div>
    <div>
        <label class="form-label">Élément</label>
        <select name="objet_type" class="form-select">
            <option value="">Tous</option>
            @foreach($types as $type)
                <option value="{{ $type }}" @selected(request('objet_type') === $type)>{{ $type }}</option>
            @endforeach
        </select>
    </div>
    <div>
        <label class="form-label">Utilisateur</label>
        <input type="text" name="utilisateur" value="{{ request('utilisateur') }}" class="form-input" placeholder="Nom">
    </div>
    <div>
        <label class="form-label">Du</label>
        <input type="date" name="du" value="{{ request('du') }}" class="form-input">
    </div>
    <div>
        <label class="form-label">Au</label>
        <input type="date" name="au" value="{{ request('au') }}" class="form-input">
    </div>
    <button type="submit" class="btn-secondary">Filtrer</button>
</form>

@include('super-admin.journal._tableau', ['entrees' => $entrees, 'avecEcole' => true])

<div class="mt-4">{{ $entrees->links() }}</div>
@endsection
