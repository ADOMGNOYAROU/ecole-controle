<?php

namespace App\Http\Requests;

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
            'annee_scolaire_id' => ['required', 'exists:annees_scolaires,id'],
            'enseignant_principal_id' => ['nullable', 'exists:enseignants,id'],
            'capacite' => ['nullable', 'integer', 'min:1', 'max:500'],
        ];
    }
}
