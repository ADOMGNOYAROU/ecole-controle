<?php

namespace App\Http\Requests;

use App\Rules\Tenant;
use Illuminate\Foundation\Http\FormRequest;

class BulkNoteRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'matiere_id' => ['required', Tenant::exists('matieres')],
            'classe_id' => ['required', Tenant::exists('classes')],
            'trimestre_id' => ['required', Tenant::exists('trimestres')],
            'type' => ['required', 'in:devoir,composition'],
            'bareme' => ['required', 'numeric', 'min:1', 'max:100'],
            'coefficient' => ['required', 'numeric', 'min:0.5', 'max:10'],
            'date_evaluation' => ['required', 'date', 'before_or_equal:today'],
            'notes' => ['required', 'array', 'min:1'],
            'notes.*.eleve_id' => ['required', Tenant::exists('eleves')],
            'notes.*.valeur' => ['required', 'numeric', 'min:0', 'lte:bareme'],
        ];
    }
}
