<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PresenceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'eleve_id' => ['required', 'exists:eleves,id'],
            'classe_id' => ['required', 'exists:classes,id'],
            'trimestre_id' => ['nullable', 'exists:trimestres,id'],
            'date' => ['required', 'date', 'before_or_equal:today'],
            'statut' => ['required', Rule::in(['present', 'absent', 'retard'])],
            'motif' => ['nullable', 'string', 'max:255', 'required_if:statut,absent'],
        ];
    }
}
