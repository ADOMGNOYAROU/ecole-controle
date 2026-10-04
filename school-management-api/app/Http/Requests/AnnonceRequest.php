<?php

namespace App\Http\Requests;

use App\Rules\Tenant;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AnnonceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'titre' => ['required', 'string', 'max:150'],
            'contenu' => ['required', 'string', 'max:5000'],
            'cible' => ['required', Rule::in(['tous', 'parents', 'enseignants', 'eleves', 'classe'])],
            'classe_id' => ['required_if:cible,classe', 'nullable', Tenant::exists('classes')],
        ];
    }
}
