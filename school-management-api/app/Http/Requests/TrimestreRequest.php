<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class TrimestreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'annee_scolaire_id' => ['required', 'exists:annees_scolaires,id'],
            'nom' => ['required', 'string', 'max:50'],
            'ordre' => ['required', 'integer', 'between:1,4'],
            'date_debut' => ['required', 'date'],
            'date_fin' => ['required', 'date', 'after:date_debut'],
        ];
    }
}
