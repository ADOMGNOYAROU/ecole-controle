<?php

namespace App\Http\Controllers;

use App\Models\Classe;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ClasseController extends Controller
{
    /**
     * GET /api/classes
     * Liste toutes les classes avec leurs élèves
     */
    public function index()
    {
        $classes = Classe::with('eleves')->get();
        
        return response()->json([
            'success' => true,
            'data' => $classes
        ], 200);
    }

    /**
     * POST /api/classes
     * Créer une nouvelle classe
     */
    public function store(Request $request)
    {
        // Validation
        $validator = Validator::make($request->all(), [
            'nom' => 'required|string|max:255|unique:classes,nom',
            'niveau' => 'required|string|max:255|in:CP,CE1,CE2,CM1,CM2,6ème,5ème,4ème,3ème,2nd,1ère,Tle',
            'effectif_max' => 'required|integer|min:1',
            'annee_scolaire' => 'required|string|regex:/^\d{4}-\d{4}$/',
            'description' => 'nullable|string',
            'salle' => 'nullable|string|max:50',
            'statut' => 'required|in:active,inactive',
            'responsable_id' => 'nullable|exists:enseignants,id'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        // Créer la classe
        $classe = Classe::create($request->all());
        
        // Recharger avec les relations
        $classe->load('eleves');

        return response()->json([
            'success' => true,
            'message' => 'Classe créée avec succès',
            'data' => $classe
        ], 201);
    }

    /**
     * GET /api/classes/{id}
     * Afficher une classe spécifique avec ses élèves et détails
     */
    public function show($id)
    {
        $classe = Classe::with(['eleves', 'enseignant'])->find($id);

        if (!$classe) {
            return response()->json([
                'success' => false,
                'message' => 'Classe non trouvée'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $classe
        ], 200);
    }

    /**
     * PUT /api/classes/{id}
     * Modifier une classe
     */
    public function update(Request $request, $id)
    {
        $classe = Classe::find($id);

        if (!$classe) {
            return response()->json([
                'success' => false,
                'message' => 'Classe non trouvée'
            ], 404);
        }

        // Validation (sauf pour le nom de la classe actuelle)
        $validator = Validator::make($request->all(), [
            'nom' => 'required|string|max:255|unique:classes,nom,' . $id,
            'niveau' => 'required|string|max:255|in:CP,CE1,CE2,CM1,CM2,6ème,5ème,4ème,3ème,2nd,1ère,Tle',
            'effectif_max' => 'required|integer|min:1',
            'annee_scolaire' => 'required|string|regex:/^\d{4}-\d{4}$/',
            'description' => 'nullable|string',
            'salle' => 'nullable|string|max:50',
            'statut' => 'required|in:active,inactive',
            'responsable_id' => 'nullable|exists:enseignants,id'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $classe->update($request->all());
        $classe->load('eleves', 'enseignant');

        return response()->json([
            'success' => true,
            'message' => 'Classe modifiée avec succès',
            'data' => $classe
        ], 200);
    }

    /**
     * DELETE /api/classes/{id}
     * Supprimer une classe
     */
    public function destroy($id)
    {
        $classe = Classe::find($id);

        if (!$classe) {
            return response()->json([
                'success' => false,
                'message' => 'Classe non trouvée'
            ], 404);
        }

        // Vérifier s'il y a des élèves dans la classe
        if ($classe->eleves()->count() > 0) {
            return response()->json([
                'success' => false,
                'message' => 'Impossible de supprimer une classe qui contient des élèves. Veuillez d\'abord réaffecter ou supprimer les élèves.'
            ], 400);
        }

        // Vérifier s'il y a des cours associés
        if ($classe->cours()->count() > 0) {
            return response()->json([
                'success' => false,
                'message' => 'Impossible de supprimer une classe qui a des cours associés.'
            ], 400);
        }

        $classe->delete();

        return response()->json([
            'success' => true,
            'message' => 'Classe supprimée avec succès'
        ], 200);
    }
}