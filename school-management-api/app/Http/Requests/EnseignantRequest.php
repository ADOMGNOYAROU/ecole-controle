<?php

namespace App\Http\Requests;

use App\Rules\Tenant;
use Illuminate\Foundation\Http\FormRequest;

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
            'matieres.*' => [Tenant::exists('matieres')],
            'classes' => ['nullable', 'array'],
            'classes.*' => [Tenant::exists('classes')],
        ];
    }
}
