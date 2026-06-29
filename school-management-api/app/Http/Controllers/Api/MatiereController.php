<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\MatiereRequest;
use App\Models\Matiere;
use Illuminate\Http\JsonResponse;

class MatiereController extends Controller
{
    public function index(): JsonResponse
    {
        $this->authorize('viewAny', Matiere::class);

        return response()->json(Matiere::orderBy('nom')->get());
    }

    public function store(MatiereRequest $request): JsonResponse
    {
        $this->authorize('create', Matiere::class);

        return response()->json(Matiere::create($request->validated()), 201);
    }

    public function show(Matiere $matiere): JsonResponse
    {
        $this->authorize('view', $matiere);

        return response()->json($matiere);
    }

    public function update(MatiereRequest $request, Matiere $matiere): JsonResponse
    {
        $this->authorize('update', $matiere);

        $matiere->update($request->validated());

        return response()->json($matiere);
    }

    public function destroy(Matiere $matiere): JsonResponse
    {
        $this->authorize('delete', $matiere);

        $matiere->delete();

        return response()->json(null, 204);
    }
}
