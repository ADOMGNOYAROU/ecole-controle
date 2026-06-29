<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\PresenceRequest;
use App\Models\Presence;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PresenceController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Presence::class);

        $user = Auth::user();

        $presences = Presence::with('classe')
            ->when($user->isEleve(), fn ($q) => $q->where('eleve_id', $user->eleve?->id))
            ->when($request->filled('eleve_id') && ! $user->isEleve(), fn ($q) => $q->where('eleve_id', $request->eleve_id))
            ->when($request->filled('classe_id'), fn ($q) => $q->where('classe_id', $request->classe_id))
            ->latest('date')
            ->get();

        return response()->json($presences);
    }

    public function store(PresenceRequest $request): JsonResponse
    {
        $this->authorize('create', Presence::class);

        $presence = Presence::updateOrCreate(
            ['eleve_id' => $request->eleve_id, 'date' => $request->date],
            [...$request->validated(), 'enseignant_id' => Auth::user()->enseignant?->id]
        );

        return response()->json($presence, 201);
    }

    public function show(Presence $presence): JsonResponse
    {
        $this->authorize('view', $presence);

        return response()->json($presence->load('classe', 'eleve'));
    }

    public function update(PresenceRequest $request, Presence $presence): JsonResponse
    {
        $this->authorize('update', $presence);

        $presence->update($request->validated());

        return response()->json($presence);
    }

    public function destroy(Presence $presence): JsonResponse
    {
        $this->authorize('delete', $presence);

        $presence->delete();

        return response()->json(null, 204);
    }
}
