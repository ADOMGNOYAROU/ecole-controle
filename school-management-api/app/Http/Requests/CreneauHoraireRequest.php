<?php

namespace App\Http\Requests;

use App\Rules\Tenant;
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
            'classe_id' => ['required', Tenant::exists('classes')],
            'matiere_id' => ['required', Tenant::exists('matieres')],
            'enseignant_id' => ['nullable', Tenant::exists('enseignants')],
            'jour_semaine' => ['required', 'integer', 'between:1,6'],
            'heure_debut' => ['required', 'date_format:H:i'],
            'heure_fin' => ['required', 'date_format:H:i', 'after:heure_debut'],
            'salle' => ['nullable', 'string', 'max:50'],
        ];
    }
}
