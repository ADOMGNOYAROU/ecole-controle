<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\EnseignantRequest;
use App\Models\Enseignant;
use Illuminate\Http\JsonResponse;

class EnseignantController extends Controller
{
    public function index(): JsonResponse
    {
        $this->authorize('viewAny', Enseignant::class);

        return response()->json(Enseignant::orderBy('nom')->get());
    }

    public function store(EnseignantRequest $request): JsonResponse
    {
        $this->authorize('create', Enseignant::class);

        return response()->json(Enseignant::create($request->safe()->except(['matieres', 'classes'])), 201);
    }

    public function show(Enseignant $enseignant): JsonResponse
    {
        $this->authorize('view', $enseignant);

        return response()->json($enseignant->load('classesPrincipales', 'matieres', 'classes'));
    }

    public function update(EnseignantRequest $request, Enseignant $enseignant): JsonResponse
    {
        $this->authorize('update', $enseignant);

        $enseignant->update($request->safe()->except(['matieres', 'classes']));

        return response()->json($enseignant);
    }

    public function destroy(Enseignant $enseignant): JsonResponse
    {
        $this->authorize('delete', $enseignant);

        $enseignant->delete();

        return response()->json(null, 204);
    }
}
