<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class NoteRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'eleve_id' => ['required', 'exists:eleves,id'],
            'matiere_id' => ['required', 'exists:matieres,id'],
            'classe_id' => ['required', 'exists:classes,id'],
            'trimestre_id' => ['required', 'exists:trimestres,id'],
            'type' => ['required', Rule::in(['devoir', 'composition'])],
            'valeur' => ['required', 'numeric', 'min:0', 'lte:bareme'],
            'bareme' => ['required', 'numeric', 'min:1', 'max:100'],
            'coefficient' => ['required', 'numeric', 'min:0.5', 'max:10'],
            'date_evaluation' => ['required', 'date', 'before_or_equal:today'],
            'commentaire' => ['nullable', 'string', 'max:255'],
        ];
    }
}
