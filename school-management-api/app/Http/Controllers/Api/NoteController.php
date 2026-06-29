<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\NoteRequest;
use App\Models\Note;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NoteController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Note::class);

        $user = Auth::user();

        $notes = Note::with(['matiere', 'classe'])
            ->when($user->isEleve(), fn ($q) => $q->where('eleve_id', $user->eleve?->id))
            ->when($user->isEnseignant(), fn ($q) => $q->where('enseignant_id', $user->enseignant?->id))
            ->when($request->filled('eleve_id') && ! $user->isEleve(), fn ($q) => $q->where('eleve_id', $request->eleve_id))
            ->when($request->filled('classe_id'), fn ($q) => $q->where('classe_id', $request->classe_id))
            ->latest('date_evaluation')
            ->get();

        return response()->json($notes);
    }

    public function store(NoteRequest $request): JsonResponse
    {
        $this->authorize('create', Note::class);

        $note = Note::create([
            ...$request->validated(),
            'enseignant_id' => Auth::user()->enseignant?->id ?? $request->input('enseignant_id'),
        ]);

        return response()->json($note, 201);
    }

    public function show(Note $note): JsonResponse
    {
        $this->authorize('view', $note);

        return response()->json($note->load('matiere', 'eleve', 'classe'));
    }

    public function update(NoteRequest $request, Note $note): JsonResponse
    {
        $this->authorize('update', $note);

        $note->update($request->validated());

        return response()->json($note);
    }

    public function destroy(Note $note): JsonResponse
    {
        $this->authorize('delete', $note);

        $note->delete();

        return response()->json(null, 204);
    }
}
