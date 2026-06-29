<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\EleveRequest;
use App\Models\Eleve;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class EleveController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Eleve::class);

        $eleves = Eleve::with('classe')
            ->when($request->filled('classe_id'), fn ($q) => $q->where('classe_id', $request->classe_id))
            ->orderBy('nom')
            ->get();

        return response()->json($eleves);
    }

    public function store(EleveRequest $request): JsonResponse
    {
        $this->authorize('create', Eleve::class);

        return response()->json(Eleve::create($request->validated()), 201);
    }

    public function show(Eleve $eleve): JsonResponse
    {
        $this->authorize('view', $eleve);

        return response()->json($eleve->load('classe', 'tuteurs'));
    }

    public function update(EleveRequest $request, Eleve $eleve): JsonResponse
    {
        $this->authorize('update', $eleve);

        $eleve->update($request->validated());

        return response()->json($eleve);
    }

    public function destroy(Eleve $eleve): JsonResponse
    {
        $this->authorize('delete', $eleve);

        $eleve->delete();

        return response()->json(null, 204);
    }
}
