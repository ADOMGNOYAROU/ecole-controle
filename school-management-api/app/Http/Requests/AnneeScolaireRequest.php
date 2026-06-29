<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AnneeScolaireRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $anneeScolaire = $this->route('annee_scolaire');

        return [
            'libelle' => ['required', 'string', 'max:20', Rule::unique('annees_scolaires', 'libelle')->ignore($anneeScolaire)],
            'date_debut' => ['required', 'date'],
            'date_fin' => ['required', 'date', 'after:date_debut'],
            'active' => ['boolean'],
        ];
    }
}
