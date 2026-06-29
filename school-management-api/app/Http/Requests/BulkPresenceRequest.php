<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class BulkPresenceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'classe_id' => ['required', 'exists:classes,id'],
            'trimestre_id' => ['nullable', 'exists:trimestres,id'],
            'date' => ['required', 'date', 'before_or_equal:today'],
            'presences' => ['required', 'array', 'min:1'],
            'presences.*.eleve_id' => ['required', 'exists:eleves,id'],
            'presences.*.statut' => ['required', 'in:present,absent,retard'],
            'presences.*.motif' => ['nullable', 'string', 'max:255'],
        ];
    }
}
