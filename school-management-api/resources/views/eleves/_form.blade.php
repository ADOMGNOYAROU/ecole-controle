@php $eleve ??= null; @endphp

<div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
    <div>
        <label class="form-label" for="matricule">Matricule</label>
        <input type="text" id="matricule" name="matricule" value="{{ old('matricule', $eleve?->matricule) }}" class="form-input" required>
        @error('matricule')<p class="form-error">{{ $message }}</p>@enderror
    </div>
    <div>
        <label class="form-label" for="classe_id">Classe</label>
        <select id="classe_id" name="classe_id" class="form-select">
            <option value="">Aucune</option>
            @foreach($classes as $classe)
                <option value="{{ $classe->id }}" @selected(old('classe_id', $eleve?->classe_id) == $classe->id)>{{ $classe->nom }}</option>
            @endforeach
        </select>
        @error('classe_id')<p class="form-error">{{ $message }}</p>@enderror
    </div>
    <div>
        <label class="form-label" for="nom">Nom</label>
        <input type="text" id="nom" name="nom" value="{{ old('nom', $eleve?->nom) }}" class="form-input" required>
        @error('nom')<p class="form-error">{{ $message }}</p>@enderror
    </div>
    <div>
        <label class="form-label" for="prenom">Prénom</label>
        <input type="text" id="prenom" name="prenom" value="{{ old('prenom', $eleve?->prenom) }}" class="form-input" required>
        @error('prenom')<p class="form-error">{{ $message }}</p>@enderror
    </div>
    <div>
        <label class="form-label" for="sexe">Sexe</label>
        <select id="sexe" name="sexe" class="form-select" required>
            <option value="M" @selected(old('sexe', $eleve?->sexe) === 'M')>Masculin</option>
            <option value="F" @selected(old('sexe', $eleve?->sexe) === 'F')>Féminin</option>
        </select>
        @error('sexe')<p class="form-error">{{ $message }}</p>@enderror
    </div>
    <div>
        <label class="form-label" for="date_naissance">Date de naissance</label>
        <input type="date" id="date_naissance" name="date_naissance" value="{{ old('date_naissance', $eleve?->date_naissance?->toDateString()) }}" class="form-input" required>
        @error('date_naissance')<p class="form-error">{{ $message }}</p>@enderror
    </div>
    <div>
        <label class="form-label" for="lieu_naissance">Lieu de naissance</label>
        <input type="text" id="lieu_naissance" name="lieu_naissance" value="{{ old('lieu_naissance', $eleve?->lieu_naissance) }}" class="form-input">
    </div>
    <div>
        <label class="form-label" for="email">Email</label>
        <input type="email" id="email" name="email" value="{{ old('email', $eleve?->email) }}" class="form-input">
        @error('email')<p class="form-error">{{ $message }}</p>@enderror
    </div>
    <div>
        <label class="form-label" for="telephone">Téléphone</label>
        <input type="text" id="telephone" name="telephone" value="{{ old('telephone', $eleve?->telephone) }}" class="form-input">
    </div>
    <div>
        <label class="form-label" for="statut">Statut</label>
        <select id="statut" name="statut" class="form-select" required>
            @foreach(['actif', 'inactif', 'diplome', 'exclu'] as $statut)
                <option value="{{ $statut }}" @selected(old('statut', $eleve?->statut ?? 'actif') === $statut)>{{ ucfirst($statut) }}</option>
            @endforeach
        </select>
    </div>
    <div>
        <label class="form-label" for="date_inscription">Date d'inscription</label>
        <input type="date" id="date_inscription" name="date_inscription" value="{{ old('date_inscription', $eleve?->date_inscription?->toDateString() ?? now()->toDateString()) }}" class="form-input" required>
        @error('date_inscription')<p class="form-error">{{ $message }}</p>@enderror
    </div>
    <div>
        <label class="form-label" for="adresse">Adresse</label>
        <input type="text" id="adresse" name="adresse" value="{{ old('adresse', $eleve?->adresse) }}" class="form-input">
    </div>
    <div>
        <label class="form-label" for="contact_urgence_nom">Contact d'urgence (nom)</label>
        <input type="text" id="contact_urgence_nom" name="contact_urgence_nom" value="{{ old('contact_urgence_nom', $eleve?->contact_urgence_nom) }}" class="form-input">
    </div>
    <div>
        <label class="form-label" for="contact_urgence_telephone">Contact d'urgence (téléphone)</label>
        <input type="text" id="contact_urgence_telephone" name="contact_urgence_telephone" value="{{ old('contact_urgence_telephone', $eleve?->contact_urgence_telephone) }}" class="form-input">
    </div>
</div>
