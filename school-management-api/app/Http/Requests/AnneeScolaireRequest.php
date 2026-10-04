<?php

namespace App\Http\Requests;

use App\Rules\Tenant;
use Illuminate\Foundation\Http\FormRequest;

class AnneeScolaireRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $anneeScolaire = $this->route('anneeScolaire');

        return [
            'libelle' => ['required', 'string', 'max:20', Tenant::unique('annees_scolaires', 'libelle')->ignore($anneeScolaire)],
            'date_debut' => ['required', 'date'],
            'date_fin' => ['required', 'date', 'after:date_debut'],
            'active' => ['boolean'],
        ];
    }
}
