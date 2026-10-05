<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Services\AccesSupport;
use Illuminate\Http\RedirectResponse;

class SupportController extends Controller
{
    public function quitter(AccesSupport $support): RedirectResponse
    {
        $ecoleId = $support->terminer('sortie_manuelle');

        return $ecoleId
            ? redirect()->route('super-admin.ecoles.show', $ecoleId)->with('success', 'Vous avez quitté le mode support.')
            : redirect()->route('dashboard');
    }
}
