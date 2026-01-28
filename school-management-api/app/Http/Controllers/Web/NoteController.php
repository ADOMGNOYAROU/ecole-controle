<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Note;

class NoteController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $notes = Note::with(['eleve', 'matiere'])->paginate(10);
        return view('notes.index', compact('notes'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('notes.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'eleve_id' => 'required|exists:eleves,id',
            'matiere_id' => 'required|exists:matieres,id',
            'note' => 'required|numeric|min:0|max:20',
            'type_evaluation' => 'required|string|max:255',
            'date_evaluation' => 'required|date',
            'coefficient' => 'required|numeric|min:0.1',
        ]);

        Note::create($request->all());

        return redirect()->route('notes.index')
            ->with('success', 'Note ajoutée avec succès.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Note $note)
    {
        return view('notes.show', compact('note'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Note $note)
    {
        return view('notes.edit', compact('note'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Note $note)
    {
        $request->validate([
            'eleve_id' => 'required|exists:eleves,id',
            'matiere_id' => 'required|exists:matieres,id',
            'note' => 'required|numeric|min:0|max:20',
            'type_evaluation' => 'required|string|max:255',
            'date_evaluation' => 'required|date',
            'coefficient' => 'required|numeric|min:0.1',
        ]);

        $note->update($request->all());

        return redirect()->route('notes.index')
            ->with('success', 'Note mise à jour avec succès.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Note $note)
    {
        $note->delete();

        return redirect()->route('notes.index')
            ->with('success', 'Note supprimée avec succès.');
    }

    /**
     * Show bulk grade form.
     */
    public function bulk()
    {
        return view('notes.bulk');
    }

    /**
     * Store bulk grades.
     */
    public function bulkStore(Request $request)
    {
        $request->validate([
            'matiere_id' => 'required|exists:matieres,id',
            'type_evaluation' => 'required|string|max:255',
            'date_evaluation' => 'required|date',
            'coefficient' => 'required|numeric|min:0.1',
            'notes' => 'required|array',
            'notes.*.eleve_id' => 'required|exists:eleves,id',
            'notes.*.note' => 'required|numeric|min:0|max:20',
        ]);

        foreach ($request->notes as $note) {
            Note::create([
                'eleve_id' => $note['eleve_id'],
                'matiere_id' => $request->matiere_id,
                'note' => $note['note'],
                'type_evaluation' => $request->type_evaluation,
                'date_evaluation' => $request->date_evaluation,
                'coefficient' => $request->coefficient,
            ]);
        }

        return redirect()->route('notes.index')
            ->with('success', 'Notes enregistrées avec succès.');
    }

    /**
     * Display grade reports.
     */
    public function reports()
    {
        return view('notes.reports');
    }
}
