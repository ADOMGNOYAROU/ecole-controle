<?php

namespace App\Http\Requests;

use App\Rules\Tenant;
use Illuminate\Foundation\Http\FormRequest;

class MatiereRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $matiere = $this->route('matiere');

        return [
            'nom' => ['required', 'string', 'max:100'],
            'code' => ['required', 'string', 'max:20', Tenant::unique('matieres', 'code')->ignore($matiere)],
            'coefficient_defaut' => ['required', 'numeric', 'min:0.5', 'max:10'],
        ];
    }
}
