<?php

namespace App\Http\Requests;

use App\Rules\Tenant;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PaiementRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'eleve_id' => ['required', Tenant::exists('eleves')],
            'annee_scolaire_id' => ['required', Tenant::exists('annees_scolaires')],
            'type' => ['required', Rule::in(['scolarite', 'inscription', 'transport', 'cantine', 'autre'])],
            'montant' => ['required', 'numeric', 'min:0'],
            'montant_paye' => ['nullable', 'numeric', 'min:0', 'lte:montant'],
            'date_echeance' => ['required', 'date'],
            'date_paiement' => ['nullable', 'date'],
            'commentaire' => ['nullable', 'string', 'max:255'],
        ];
    }
}
