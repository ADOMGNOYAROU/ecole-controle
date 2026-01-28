<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Note;

class NoteController extends Controller
{
    public function index()
    {
        return response()->json(Note::with(['eleve', 'matiere'])->get());
    }

    public function store(Request $request)
    {
        $note = Note::create($request->all());
        return response()->json($note, 201);
    }

    public function storeBulk(Request $request)
    {
        $notes = $request->all();
        foreach ($notes as $noteData) {
            Note::create($noteData);
        }
        return response()->json(['message' => 'Notes enregistrées'], 201);
    }

    public function show($id)
    {
        $note = Note::with(['eleve', 'matiere'])->find($id);
        if (!$note) {
            return response()->json(['message' => 'Note non trouvée'], 404);
        }
        return response()->json($note);
    }

    public function update(Request $request, $id)
    {
        $note = Note::find($id);
        if (!$note) {
            return response()->json(['message' => 'Note non trouvée'], 404);
        }
        $note->update($request->all());
        return response()->json($note);
    }

    public function destroy($id)
    {
        $note = Note::find($id);
        if (!$note) {
            return response()->json(['message' => 'Note non trouvée'], 404);
        }
        $note->delete();
        return response()->json(['message' => 'Note supprimée']);
    }

    public function statistiques()
    {
        return response()->json([
            'moyenne_generale' => 12.5,
            'nombre_notes' => 150,
            'taux_reussite' => 85
        ]);
    }

    public function getByClasse($classeId)
    {
        $notes = Note::whereHas('eleve', function($query) use ($classeId) {
            $query->where('classe_id', $classeId);
        })->with(['eleve', 'matiere'])->get();
        return response()->json($notes);
    }

    public function getByEleve($eleveId)
    {
        $notes = Note::where('eleve_id', $eleveId)->with(['eleve', 'matiere'])->get();
        return response()->json($notes);
    }
}
