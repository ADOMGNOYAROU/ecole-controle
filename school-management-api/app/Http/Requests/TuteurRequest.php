<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class TuteurRequest extends FormRequest
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
            'telephone' => ['required', 'string', 'max:30'],
            'email' => ['nullable', 'email', 'max:150'],
            'profession' => ['nullable', 'string', 'max:150'],
            'adresse' => ['nullable', 'string', 'max:255'],
            'eleves' => ['nullable', 'array'],
            'eleves.*.id' => ['exists:eleves,id'],
            'eleves.*.lien_parente' => ['required_with:eleves.*.id', 'string', 'max:50'],
        ];
    }
}
