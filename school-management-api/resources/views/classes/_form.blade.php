@php $classe ??= null; @endphp

<div>
    <label class="form-label" for="nom">Nom de la classe</label>
    <input type="text" id="nom" name="nom" value="{{ old('nom', $classe?->nom) }}" class="form-input" required>
    @error('nom')<p class="form-error">{{ $message }}</p>@enderror
</div>

<div>
    <label class="form-label" for="niveau">Niveau</label>
    <input type="text" id="niveau" name="niveau" value="{{ old('niveau', $classe?->niveau) }}" class="form-input" placeholder="ex: 6ème">
    @error('niveau')<p class="form-error">{{ $message }}</p>@enderror
</div>

<div>
    <label class="form-label" for="annee_scolaire_id">Année scolaire</label>
    <select id="annee_scolaire_id" name="annee_scolaire_id" class="form-select" required>
        @foreach($anneesScolaires as $annee)
            <option value="{{ $annee->id }}" @selected(old('annee_scolaire_id', $classe?->annee_scolaire_id) == $annee->id)>{{ $annee->libelle }}</option>
        @endforeach
    </select>
    @error('annee_scolaire_id')<p class="form-error">{{ $message }}</p>@enderror
</div>

<div>
    <label class="form-label" for="enseignant_principal_id">Professeur principal</label>
    <select id="enseignant_principal_id" name="enseignant_principal_id" class="form-select">
        <option value="">Aucun</option>
        @foreach($enseignants as $enseignant)
            <option value="{{ $enseignant->id }}" @selected(old('enseignant_principal_id', $classe?->enseignant_principal_id) == $enseignant->id)>{{ $enseignant->nomComplet() }}</option>
        @endforeach
    </select>
    @error('enseignant_principal_id')<p class="form-error">{{ $message }}</p>@enderror
</div>

<div>
    <label class="form-label" for="capacite">Capacité</label>
    <input type="number" id="capacite" name="capacite" value="{{ old('capacite', $classe?->capacite) }}" class="form-input" min="1" max="500">
    @error('capacite')<p class="form-error">{{ $message }}</p>@enderror
</div>
