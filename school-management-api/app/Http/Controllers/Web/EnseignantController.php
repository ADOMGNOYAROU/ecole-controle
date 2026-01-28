<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Enseignant;

class EnseignantController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $enseignants = Enseignant::paginate(10);
        return view('enseignants.index', compact('enseignants'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('enseignants.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'nom' => 'required|string|max:255',
            'prenom' => 'required|string|max:255',
            'email' => 'required|email|unique:enseignants,email',
            'telephone' => 'nullable|string',
            'adresse' => 'nullable|string',
            'specialite' => 'required|string|max:255',
            'date_embauche' => 'required|date',
        ]);

        Enseignant::create($request->all());

        return redirect()->route('enseignants.index')
            ->with('success', 'Enseignant ajouté avec succès.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Enseignant $enseignant)
    {
        return view('enseignants.show', compact('enseignant'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Enseignant $enseignant)
    {
        return view('enseignants.edit', compact('enseignant'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Enseignant $enseignant)
    {
        $request->validate([
            'nom' => 'required|string|max:255',
            'prenom' => 'required|string|max:255',
            'email' => 'required|email|unique:enseignants,email,' . $enseignant->id,
            'telephone' => 'nullable|string',
            'adresse' => 'nullable|string',
            'specialite' => 'required|string|max:255',
            'date_embauche' => 'required|date',
        ]);

        $enseignant->update($request->all());

        return redirect()->route('enseignants.index')
            ->with('success', 'Enseignant mis à jour avec succès.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Enseignant $enseignant)
    {
        $enseignant->delete();

        return redirect()->route('enseignants.index')
            ->with('success', 'Enseignant supprimé avec succès.');
    }

    /**
     * Display classes of the teacher.
     */
    public function classes(Enseignant $enseignant)
    {
        $classes = $enseignant->classes()->paginate(10);
        return view('enseignants.classes', compact('enseignant', 'classes'));
    }

    /**
     * Display schedule of the teacher.
     */
    public function schedule(Enseignant $enseignant)
    {
        return view('enseignants.schedule', compact('enseignant'));
    }
}
