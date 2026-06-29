<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class EnseignantRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nom' => ['required', 'string', 'max:100'],
            'prenom' => ['required', 'string', 'max:100'],
            'telephone' => ['nullable', 'string', 'max:30'],
            'email' => ['nullable', 'email', 'max:150'],
            'specialite' => ['nullable', 'string', 'max:150'],
            'date_embauche' => ['nullable', 'date'],
            'matieres' => ['nullable', 'array'],
            'matieres.*' => ['exists:matieres,id'],
            'classes' => ['nullable', 'array'],
            'classes.*' => ['exists:classes,id'],
        ];
    }
}
