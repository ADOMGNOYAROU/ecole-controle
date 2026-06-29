@php
    $tuteur ??= null;
    $liens ??= collect();
@endphp

<div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
    <div>
        <label class="form-label" for="nom">Nom</label>
        <input type="text" id="nom" name="nom" value="{{ old('nom', $tuteur?->nom) }}" class="form-input" required>
        @error('nom')<p class="form-error">{{ $message }}</p>@enderror
    </div>
    <div>
        <label class="form-label" for="prenom">Prénom</label>
        <input type="text" id="prenom" name="prenom" value="{{ old('prenom', $tuteur?->prenom) }}" class="form-input" required>
        @error('prenom')<p class="form-error">{{ $message }}</p>@enderror
    </div>
    <div>
        <label class="form-label" for="telephone">Téléphone</label>
        <input type="text" id="telephone" name="telephone" value="{{ old('telephone', $tuteur?->telephone) }}" class="form-input" required>
        @error('telephone')<p class="form-error">{{ $message }}</p>@enderror
    </div>
    <div>
        <label class="form-label" for="email">Email</label>
        <input type="email" id="email" name="email" value="{{ old('email', $tuteur?->email) }}" class="form-input">
    </div>
    <div>
        <label class="form-label" for="profession">Profession</label>
        <input type="text" id="profession" name="profession" value="{{ old('profession', $tuteur?->profession) }}" class="form-input">
    </div>
    <div>
        <label class="form-label" for="adresse">Adresse</label>
        <input type="text" id="adresse" name="adresse" value="{{ old('adresse', $tuteur?->adresse) }}" class="form-input">
    </div>
</div>

<div>
    <label class="form-label">Enfants à associer</label>
    <div class="card p-3 max-h-64 overflow-y-auto space-y-2">
        @foreach($eleves as $eleve)
            <div class="flex items-center gap-3 text-sm">
                <input type="checkbox" name="eleves[{{ $eleve->id }}][id]" value="{{ $eleve->id }}"
                       @checked($tuteur && $tuteur->eleves->contains($eleve->id))>
                <span class="w-48">{{ $eleve->nomComplet() }} ({{ $eleve->matricule }})</span>
                <input type="text" name="eleves[{{ $eleve->id }}][lien_parente]" value="{{ $liens[$eleve->id] ?? '' }}" placeholder="Lien (Père, Mère...)" class="form-input max-w-xs">
            </div>
        @endforeach
    </div>
</div>
