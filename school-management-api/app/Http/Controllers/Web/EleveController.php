<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Eleve;

class EleveController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $eleves = Eleve::with('classe')->paginate(10);
        return view('eleves.index', compact('eleves'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('eleves.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'nom' => 'required|string|max:255',
            'prenom' => 'required|string|max:255',
            'date_naissance' => 'required|date',
            'sexe' => 'required|in:M,F',
            'adresse' => 'nullable|string',
            'telephone' => 'nullable|string',
            'email' => 'nullable|email|unique:eleves,email',
            'classe_id' => 'required|exists:classes,id',
        ]);

        Eleve::create($request->all());

        return redirect()->route('eleves.index')
            ->with('success', 'Élève ajouté avec succès.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Eleve $eleve)
    {
        return view('eleves.show', compact('eleve'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Eleve $eleve)
    {
        return view('eleves.edit', compact('eleve'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Eleve $eleve)
    {
        $request->validate([
            'nom' => 'required|string|max:255',
            'prenom' => 'required|string|max:255',
            'date_naissance' => 'required|date',
            'sexe' => 'required|in:M,F',
            'adresse' => 'nullable|string',
            'telephone' => 'nullable|string',
            'email' => 'nullable|email|unique:eleves,email,' . $eleve->id,
            'classe_id' => 'required|exists:classes,id',
        ]);

        $eleve->update($request->all());

        return redirect()->route('eleves.index')
            ->with('success', 'Élève mis à jour avec succès.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Eleve $eleve)
    {
        $eleve->delete();

        return redirect()->route('eleves.index')
            ->with('success', 'Élève supprimé avec succès.');
    }

    /**
     * Display grades of the student.
     */
    public function grades(Eleve $eleve)
    {
        $grades = $eleve->notes()->with('matiere')->paginate(10);
        return view('eleves.grades', compact('eleve', 'grades'));
    }

    /**
     * Display attendance of the student.
     */
    public function attendances(Eleve $eleve)
    {
        $attendances = $eleve->presences()->paginate(10);
        return view('eleves.attendances', compact('eleve', 'attendances'));
    }
}
