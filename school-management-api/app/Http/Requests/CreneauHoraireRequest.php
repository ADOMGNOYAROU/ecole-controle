<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreneauHoraireRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'classe_id' => ['required', 'exists:classes,id'],
            'matiere_id' => ['required', 'exists:matieres,id'],
            'enseignant_id' => ['nullable', 'exists:enseignants,id'],
            'jour_semaine' => ['required', 'integer', 'between:1,6'],
            'heure_debut' => ['required', 'date_format:H:i'],
            'heure_fin' => ['required', 'date_format:H:i', 'after:heure_debut'],
            'salle' => ['nullable', 'string', 'max:50'],
        ];
    }
}
