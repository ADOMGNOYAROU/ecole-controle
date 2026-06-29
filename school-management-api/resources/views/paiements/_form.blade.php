@php $paiement ??= null; @endphp

<div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
    <div>
        <label class="form-label" for="eleve_id">Élève</label>
        <select id="eleve_id" name="eleve_id" class="form-select" required>
            @foreach($eleves as $eleve)
                <option value="{{ $eleve->id }}" @selected(old('eleve_id', $paiement?->eleve_id) == $eleve->id)>{{ $eleve->nomComplet() }} ({{ $eleve->matricule }})</option>
            @endforeach
        </select>
    </div>
    <div>
        <label class="form-label" for="annee_scolaire_id">Année scolaire</label>
        <select id="annee_scolaire_id" name="annee_scolaire_id" class="form-select" required>
            @foreach($anneesScolaires as $annee)
                <option value="{{ $annee->id }}" @selected(old('annee_scolaire_id', $paiement?->annee_scolaire_id) == $annee->id)>{{ $annee->libelle }}</option>
            @endforeach
        </select>
    </div>
    <div>
        <label class="form-label" for="type">Type</label>
        <select id="type" name="type" class="form-select" required>
            @foreach(['scolarite', 'inscription', 'transport', 'cantine', 'autre'] as $type)
                <option value="{{ $type }}" @selected(old('type', $paiement?->type) === $type)>{{ ucfirst($type) }}</option>
            @endforeach
        </select>
    </div>
    <div>
        <label class="form-label" for="montant">Montant dû</label>
        <input type="number" step="0.01" id="montant" name="montant" value="{{ old('montant', $paiement?->montant) }}" class="form-input" required>
        @error('montant')<p class="form-error">{{ $message }}</p>@enderror
    </div>
    <div>
        <label class="form-label" for="montant_paye">Montant payé</label>
        <input type="number" step="0.01" id="montant_paye" name="montant_paye" value="{{ old('montant_paye', $paiement?->montant_paye ?? 0) }}" class="form-input">
        @error('montant_paye')<p class="form-error">{{ $message }}</p>@enderror
    </div>
    <div>
        <label class="form-label" for="date_echeance">Date d'échéance</label>
        <input type="date" id="date_echeance" name="date_echeance" value="{{ old('date_echeance', $paiement?->date_echeance?->toDateString()) }}" class="form-input" required>
    </div>
    <div>
        <label class="form-label" for="date_paiement">Date de paiement</label>
        <input type="date" id="date_paiement" name="date_paiement" value="{{ old('date_paiement', $paiement?->date_paiement?->toDateString()) }}" class="form-input">
    </div>
    <div>
        <label class="form-label" for="commentaire">Commentaire</label>
        <input type="text" id="commentaire" name="commentaire" value="{{ old('commentaire', $paiement?->commentaire) }}" class="form-input">
    </div>
</div>
