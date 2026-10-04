<?php

namespace App\Http\Requests;

use App\Rules\Tenant;
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
            'classe_id' => ['required', Tenant::exists('classes')],
            'trimestre_id' => ['nullable', Tenant::exists('trimestres')],
            'date' => ['required', 'date', 'before_or_equal:today'],
            'presences' => ['required', 'array', 'min:1'],
            'presences.*.eleve_id' => ['required', Tenant::exists('eleves')],
            'presences.*.statut' => ['required', 'in:present,absent,retard'],
            'presences.*.motif' => ['nullable', 'string', 'max:255'],
        ];
    }
}
