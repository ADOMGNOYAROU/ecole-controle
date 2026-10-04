<?php

namespace App\Http\Requests;

use App\Rules\Tenant;
use Illuminate\Foundation\Http\FormRequest;

class ClasseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nom' => ['required', 'string', 'max:100'],
            'niveau' => ['nullable', 'string', 'max:100'],
            'annee_scolaire_id' => ['required', Tenant::exists('annees_scolaires')],
            'enseignant_principal_id' => ['nullable', Tenant::exists('enseignants')],
            'capacite' => ['nullable', 'integer', 'min:1', 'max:500'],
        ];
    }
}
