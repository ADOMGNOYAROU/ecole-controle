<?php

namespace App\Http\Requests;

use App\Rules\Tenant;
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
            'eleve_id' => ['required', Tenant::exists('eleves')],
            'classe_id' => ['required', Tenant::exists('classes')],
            'trimestre_id' => ['nullable', Tenant::exists('trimestres')],
            'date' => ['required', 'date', 'before_or_equal:today'],
            'statut' => ['required', Rule::in(['present', 'absent', 'retard'])],
            'motif' => ['nullable', 'string', 'max:255', 'required_if:statut,absent'],
        ];
    }
}
