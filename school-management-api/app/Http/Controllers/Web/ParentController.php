<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\ParentModel;
use App\Models\Eleve;
use Illuminate\Http\Request;

class ParentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $parents = ParentModel::with('eleves')->latest()->paginate(10);
        return view('parents.index', compact('parents'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $eleves = Eleve::all();
        return view('parents.create', compact('eleves'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nom' => 'required|string|max:255',
            'prenom' => 'required|string|max:255',
            'email' => 'required|email|unique:parents,email',
            'telephone' => 'required|string|max:20',
            'adresse' => 'required|string|max:255',
            'eleves' => 'nullable|array',
            'eleves.*' => 'exists:eleves,id',
        ]);

        $parent = ParentModel::create($validated);
        
        if (isset($validated['eleves'])) {
            $parent->eleves()->sync($validated['eleves']);
        }

        return redirect()->route('parents.index')
                        ->with('success', 'Parent ajouté avec succès.');
    }

    /**
     * Display the specified resource.
     */
    public function show(ParentModel $parent)
    {
        $parent->load('eleves');
        return view('parents.show', compact('parent'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(ParentModel $parent)
    {
        $eleves = Eleve::all();
        $parent->load('eleves');
        return view('parents.edit', compact('parent', 'eleves'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, ParentModel $parent)
    {
        $validated = $request->validate([
            'nom' => 'required|string|max:255',
            'prenom' => 'required|string|max:255',
            'email' => 'required|email|unique:parents,email,' . $parent->id,
            'telephone' => 'required|string|max:20',
            'adresse' => 'required|string|max:255',
            'eleves' => 'nullable|array',
            'eleves.*' => 'exists:eleves,id',
        ]);

        $parent->update($validated);
        
        if (isset($validated['eleves'])) {
            $parent->eleves()->sync($validated['eleves']);
        } else {
            $parent->eleves()->detach();
        }

        return redirect()->route('parents.index')
                        ->with('success', 'Parent mis à jour avec succès.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ParentModel $parent)
    {
        $parent->eleves()->detach();
        $parent->delete();

        return redirect()->route('parents.index')
                        ->with('success', 'Parent supprimé avec succès.');
    }
}
