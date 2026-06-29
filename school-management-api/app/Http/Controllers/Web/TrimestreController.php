<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\TrimestreRequest;
use App\Models\Trimestre;
use Illuminate\Http\RedirectResponse;

class TrimestreController extends Controller
{
    public function index(): RedirectResponse
    {
        $this->authorize('viewAny', Trimestre::class);

        return redirect()->route('annees-scolaires.index');
    }

    public function store(TrimestreRequest $request): RedirectResponse
    {
        $this->authorize('create', Trimestre::class);

        Trimestre::create($request->validated());

        return redirect()->route('trimestres.index')->with('success', 'Trimestre créé.');
    }

    public function update(TrimestreRequest $request, Trimestre $trimestre): RedirectResponse
    {
        $this->authorize('update', $trimestre);

        $trimestre->update($request->validated());

        return redirect()->route('trimestres.index')->with('success', 'Trimestre mis à jour.');
    }

    public function destroy(Trimestre $trimestre): RedirectResponse
    {
        $this->authorize('delete', $trimestre);

        $trimestre->delete();

        return redirect()->route('trimestres.index')->with('success', 'Trimestre supprimé.');
    }
}
