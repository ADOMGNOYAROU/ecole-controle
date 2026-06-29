@php $matiere ??= null; @endphp

<div>
    <label class="form-label" for="nom">Nom</label>
    <input type="text" id="nom" name="nom" value="{{ old('nom', $matiere?->nom) }}" class="form-input" required>
    @error('nom')<p class="form-error">{{ $message }}</p>@enderror
</div>
<div>
    <label class="form-label" for="code">Code</label>
    <input type="text" id="code" name="code" value="{{ old('code', $matiere?->code) }}" class="form-input" required>
    @error('code')<p class="form-error">{{ $message }}</p>@enderror
</div>
<div>
    <label class="form-label" for="coefficient_defaut">Coefficient</label>
    <input type="number" step="0.5" id="coefficient_defaut" name="coefficient_defaut" value="{{ old('coefficient_defaut', $matiere?->coefficient_defaut ?? 1) }}" class="form-input" required>
    @error('coefficient_defaut')<p class="form-error">{{ $message }}</p>@enderror
</div>
