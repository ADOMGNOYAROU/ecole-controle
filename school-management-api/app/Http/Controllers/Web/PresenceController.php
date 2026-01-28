<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Presence;

class PresenceController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $presences = Presence::with('eleve')->paginate(10);
        return view('presences.index', compact('presences'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('presences.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'eleve_id' => 'required|exists:eleves,id',
            'date' => 'required|date',
            'statut' => 'required|in:present,absent,retard',
            'motif' => 'nullable|string',
        ]);

        // Récupérer l'élève pour obtenir son classe_id
        $eleve = \App\Models\Eleve::find($request->eleve_id);
        
        if (!$eleve || !$eleve->classe_id) {
            return back()->with('error', 'L\'élève n\'est pas assigné à une classe.');
        }

        // Vérifier si une présence existe déjà pour cet élève à cette date
        $existingPresence = Presence::where('eleve_id', $request->eleve_id)
            ->whereDate('date', $request->date)
            ->first();

        if ($existingPresence) {
            // Mettre à jour la présence existante
            $existingPresence->update([
                'statut' => $request->statut,
                'motif' => $request->motif,
                'classe_id' => $eleve->classe_id, // Au cas où la classe a changé
            ]);
            $message = 'La présence a été mise à jour avec succès.';
        } else {
            // Créer une nouvelle présence
            Presence::create([
                'eleve_id' => $request->eleve_id,
                'classe_id' => $eleve->classe_id,
                'date' => $request->date,
                'statut' => $request->statut,
                'motif' => $request->motif,
            ]);
            $message = 'Présence enregistrée avec succès.';
        }

        return redirect()->route('presences.index')
            ->with('success', $message);
    }

    /**
     * Display the specified resource.
     */
    public function show(Presence $presence)
    {
        return view('presences.show', compact('presence'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Presence $presence)
    {
        return view('presences.edit', compact('presence'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Presence $presence)
    {
        $request->validate([
            'eleve_id' => 'required|exists:eleves,id',
            'date' => 'required|date',
            'statut' => 'required|in:present,absent,retard',
            'motif' => 'nullable|string',
        ]);

        // Récupérer l'élève pour obtenir son classe_id
        $eleve = \App\Models\Eleve::find($request->eleve_id);
        
        if (!$eleve || !$eleve->classe_id) {
            return back()->with('error', 'L\'élève n\'est pas assigné à une classe.');
        }

        // Mettre à jour la présence avec le classe_id
        $presence->update([
            'eleve_id' => $request->eleve_id,
            'classe_id' => $eleve->classe_id,
            'date' => $request->date,
            'statut' => $request->statut,
            'motif' => $request->motif,
        ]);

        return redirect()->route('presences.index')
            ->with('success', 'Présence mise à jour avec succès.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Presence $presence)
    {
        $presence->delete();

        return redirect()->route('presences.index')
            ->with('success', 'Présence supprimée avec succès.');
    }

    /**
     * Show bulk attendance form.
     */
    public function bulk()
    {
        $user = auth()->user();
        
        if ($user->isAdmin()) {
            // Administrateur peut voir toutes les classes
            $classes = \App\Models\Classe::all();
        } elseif ($user->isEnseignant()) {
            // Enseignant ne voit que ses classes
            if ($user->enseignant) {
                $classes = $user->enseignant->classes;
            } else {
                $classes = collect(); // Collection vide si pas de relation enseignant
            }
        } else {
            // Autres rôles ne peuvent pas accéder
            abort(403, 'Accès non autorisé');
        }
        
        return view('presences.bulk', compact('classes'));
    }

    /**
     * Store bulk attendance.
     */
    public function bulkStore(Request $request)
    {
        $user = auth()->user();
        
        $request->validate([
            'date' => 'required|date',
            'presences' => 'required|array',
            'presences.*.eleve_id' => 'required|exists:eleves,id',
            'presences.*.statut' => 'required|in:present,absent,retard',
        ]);

        // Vérifier les permissions pour les enseignants
        if ($user->isEnseignant()) {
            $enseignantClasses = $user->enseignant->classes->pluck('id');
            
            foreach ($request->presences as $presence) {
                $eleve = \App\Models\Eleve::find($presence['eleve_id']);
                
                // Vérifier que l'élève appartient à une classe enseignée par cet enseignant
                if (!$enseignantClasses->contains($eleve->classe_id)) {
                    abort(403, 'Vous ne pouvez pas marquer la présence pour cet élève');
                }
            }
        }

        foreach ($request->presences as $presence) {
            Presence::updateOrCreate(
                [
                    'eleve_id' => $presence['eleve_id'],
                    'date' => $request->date,
                ],
                [
                    'statut' => $presence['statut'],
                    'motif' => $presence['motif'] ?? null,
                ]
            );
        }

        return redirect()->route('presences.index')
            ->with('success', 'Appel enregistré avec succès.');
    }
}
