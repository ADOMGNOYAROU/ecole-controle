<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Eleve;
use App\Services\ProgressionService;
use Illuminate\View\View;

class ProgressionController extends Controller
{
    public function show(Eleve $eleve, ProgressionService $service): View
    {
        $this->authorize('view', $eleve);

        $data = $service->analyser($eleve);

        return view('eleves.progression', array_merge(['eleve' => $eleve], $data));
    }
}
