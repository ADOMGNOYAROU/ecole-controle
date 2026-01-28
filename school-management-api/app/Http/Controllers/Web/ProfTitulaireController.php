<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Classe;
use App\Models\Enseignant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProfTitulaireController extends Controller
{
    /**
     * Afficher la liste des classes et leurs professeurs titulaires
     */
    public function index()
    {
        $user = auth()->user();
        
        if (!$user->isAdmin()) {
            return redirect()->route('dashboard')->with('error', 'Accès non autorisé.');
        }

        $classes = Classe::with('enseignant')->get();
        $enseignants = Enseignant::with('user')->get();

        return view('prof-titulaire.index', compact('classes', 'enseignants'));
    }

    /**
     * Assigner un professeur titulaire à une classe
     */
    public function assign(Request $request)
    {
        $user = auth()->user();
        
        if (!$user->isAdmin()) {
            return redirect()->route('dashboard')->with('error', 'Accès non autorisé.');
        }

        $request->validate([
            'classe_id' => 'required|exists:classes,id',
            'enseignant_id' => 'required|exists:enseignants,id',
        ]);

        $classe = Classe::findOrFail($request->classe_id);
        $enseignant = Enseignant::findOrFail($request->enseignant_id);

        // Mettre à jour la classe
        $classe->enseignant_id = $enseignant->id;
        $classe->save();

        // Mettre à jour le rôle de l'utilisateur du professeur
        $userProf = $enseignant->user;
        if ($userProf) {
            $userProf->role = 'prof_titulaire';
            $userProf->save();
        }

        return redirect()->route('prof-titulaire.index')
            ->with('success', "Le professeur {$enseignant->nom} {$enseignant->prenom} a été assigné comme titulaire de la classe {$classe->nom}.");
    }

    /**
     * Retirer un professeur titulaire d'une classe
     */
    public function remove($classeId)
    {
        $user = auth()->user();
        
        if (!$user->isAdmin()) {
            return redirect()->route('dashboard')->with('error', 'Accès non autorisé.');
        }

        $classe = Classe::findOrFail($classeId);
        
        if ($classe->enseignant) {
            $enseignant = $classe->enseignant;
            
            // Remettre le rôle à 'enseignant' si le prof n'a plus d'autres classes
            $otherClasses = Classe::where('enseignant_id', $enseignant->id)->where('id', '!=', $classeId)->count();
            if ($otherClasses == 0) {
                $userProf = $enseignant->user;
                if ($userProf) {
                    $userProf->role = 'enseignant';
                    $userProf->save();
                }
            }
        }

        // Retirer le titulaire de la classe
        $classe->enseignant_id = null;
        $classe->save();

        return redirect()->route('prof-titulaire.index')
            ->with('success', "Le professeur titulaire a été retiré de la classe {$classe->nom}.");
    }
}
