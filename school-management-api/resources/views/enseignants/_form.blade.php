@php
    $enseignant ??= null;
    $matiereIds ??= [];
    $classeIds ??= [];
@endphp

<div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
    <div>
        <label class="form-label" for="nom">Nom</label>
        <input type="text" id="nom" name="nom" value="{{ old('nom', $enseignant?->nom) }}" class="form-input" required>
        @error('nom')<p class="form-error">{{ $message }}</p>@enderror
    </div>
    <div>
        <label class="form-label" for="prenom">Prénom</label>
        <input type="text" id="prenom" name="prenom" value="{{ old('prenom', $enseignant?->prenom) }}" class="form-input" required>
        @error('prenom')<p class="form-error">{{ $message }}</p>@enderror
    </div>
    <div>
        <label class="form-label" for="email">Email</label>
        <input type="email" id="email" name="email" value="{{ old('email', $enseignant?->email) }}" class="form-input">
        @error('email')<p class="form-error">{{ $message }}</p>@enderror
    </div>
    <div>
        <label class="form-label" for="telephone">Téléphone</label>
        <input type="text" id="telephone" name="telephone" value="{{ old('telephone', $enseignant?->telephone) }}" class="form-input">
    </div>
    <div>
        <label class="form-label" for="specialite">Spécialité</label>
        <input type="text" id="specialite" name="specialite" value="{{ old('specialite', $enseignant?->specialite) }}" class="form-input">
    </div>
    <div>
        <label class="form-label" for="date_embauche">Date d'embauche</label>
        <input type="date" id="date_embauche" name="date_embauche" value="{{ old('date_embauche', $enseignant?->date_embauche?->toDateString()) }}" class="form-input">
    </div>
</div>

<div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mt-4">
    <div>
        <label class="form-label">Matières enseignées</label>
        <div class="card p-3 max-h-48 overflow-y-auto space-y-1">
            @foreach($matieres as $matiere)
                <label class="flex items-center gap-2 text-sm">
                    <input type="checkbox" name="matieres[]" value="{{ $matiere->id }}" @checked(in_array($matiere->id, old('matieres', $matiereIds)))>
                    {{ $matiere->nom }}
                </label>
            @endforeach
        </div>
    </div>
    <div>
        <label class="form-label">Classes affectées</label>
        <div class="card p-3 max-h-48 overflow-y-auto space-y-1">
            @foreach($classes as $classe)
                <label class="flex items-center gap-2 text-sm">
                    <input type="checkbox" name="classes[]" value="{{ $classe->id }}" @checked(in_array($classe->id, old('classes', $classeIds)))>
                    {{ $classe->nom }}
                </label>
            @endforeach
        </div>
    </div>
</div>
<p class="text-xs text-slate-500">L'enseignant sera affecté à chaque combinaison matière × classe sélectionnée.</p>
