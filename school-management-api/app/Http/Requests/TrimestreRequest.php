<?php

namespace App\Http\Requests;

use App\Rules\Tenant;
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
            'annee_scolaire_id' => ['required', Tenant::exists('annees_scolaires')],
            'nom' => ['required', 'string', 'max:50'],
            'ordre' => ['required', 'integer', 'between:1,4'],
            'date_debut' => ['required', 'date'],
            'date_fin' => ['required', 'date', 'after:date_debut'],
        ];
    }
}
