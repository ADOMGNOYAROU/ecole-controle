<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ClasseRequest;
use App\Models\Classe;
use Illuminate\Http\JsonResponse;

class ClasseController extends Controller
{
    public function index(): JsonResponse
    {
        $this->authorize('viewAny', Classe::class);

        return response()->json(Classe::with('anneeScolaire')->withCount('eleves')->orderBy('nom')->get());
    }

    public function store(ClasseRequest $request): JsonResponse
    {
        $this->authorize('create', Classe::class);

        return response()->json(Classe::create($request->validated()), 201);
    }

    public function show(Classe $classe): JsonResponse
    {
        $this->authorize('view', $classe);

        return response()->json($classe->load('eleves', 'enseignants', 'matieres'));
    }

    public function update(ClasseRequest $request, Classe $classe): JsonResponse
    {
        $this->authorize('update', $classe);

        $classe->update($request->validated());

        return response()->json($classe);
    }

    public function destroy(Classe $classe): JsonResponse
    {
        $this->authorize('delete', $classe);

        $classe->delete();

        return response()->json(null, 204);
    }

    public function eleves(Classe $classe): JsonResponse
    {
        $this->authorize('view', $classe);

        return response()->json($classe->elevesActifs()->orderBy('nom')->get());
    }
}
