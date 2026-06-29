<?php

namespace App\Http\Requests;

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
            'matiere_id' => ['required', 'exists:matieres,id'],
            'classe_id' => ['required', 'exists:classes,id'],
            'trimestre_id' => ['required', 'exists:trimestres,id'],
            'type' => ['required', 'in:devoir,composition'],
            'bareme' => ['required', 'numeric', 'min:1', 'max:100'],
            'coefficient' => ['required', 'numeric', 'min:0.5', 'max:10'],
            'date_evaluation' => ['required', 'date', 'before_or_equal:today'],
            'notes' => ['required', 'array', 'min:1'],
            'notes.*.eleve_id' => ['required', 'exists:eleves,id'],
            'notes.*.valeur' => ['required', 'numeric', 'min:0', 'lte:bareme'],
        ];
    }
}
