<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class EleveRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $eleve = $this->route('eleve');

        return [
            'matricule' => ['required', 'string', 'max:50', Rule::unique('eleves', 'matricule')->ignore($eleve)],
            'nom' => ['required', 'string', 'max:100'],
            'prenom' => ['required', 'string', 'max:100'],
            'sexe' => ['required', Rule::in(['M', 'F'])],
            'date_naissance' => ['required', 'date', 'before:today'],
            'lieu_naissance' => ['nullable', 'string', 'max:150'],
            'adresse' => ['nullable', 'string', 'max:255'],
            'telephone' => ['nullable', 'string', 'max:30'],
            'email' => ['nullable', 'email', 'max:150'],
            'classe_id' => ['nullable', 'exists:classes,id'],
            'statut' => ['required', Rule::in(['actif', 'inactif', 'diplome', 'exclu'])],
            'date_inscription' => ['required', 'date'],
            'contact_urgence_nom' => ['nullable', 'string', 'max:150'],
            'contact_urgence_telephone' => ['nullable', 'string', 'max:30'],
        ];
    }
}
