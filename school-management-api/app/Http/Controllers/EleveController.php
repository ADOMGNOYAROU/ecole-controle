<?php

namespace App\Http\Controllers;

use App\Models\Eleve;
use App\Models\Classe;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class EleveController extends Controller
{
    /**
     * GET /api/eleves
     * Liste tous les élèves avec leur classe
     */
    public function index()
    {
        $eleves = Eleve::with('classe')->get();
        
        return response()->json([
            'success' => true,
            'data' => $eleves
        ], 200);
    }

    /**
     * POST /api/eleves
     * Créer un nouvel élève
     */
    public function store(Request $request)
    {
        // Règles de validation
        $rules = [
            'nom' => 'required|string|max:255',
            'prenom' => 'required|string|max:255',
            'matricule' => 'required|string|unique:eleves,matricule',
            'date_naissance' => 'required|date',
            'lieu_naissance' => 'nullable|string',
            'sexe' => 'required|in:M,F',
            'classe_id' => 'nullable|exists:classes,id',
            'parent_contact' => 'nullable|string',
            'adresse' => 'nullable|string',
            'statut' => 'required|in:actif,inactif'
        ];

        // Validation des données
        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        // Création de l'élève
        $eleve = Eleve::create($request->all());

        // Charger la relation classe pour la réponse
        $eleve->load('classe');

        return response()->json([
            'success' => true,
            'message' => 'Élève créé avec succès',
            'data' => $eleve
        ], 201);
    }

    /**
     * GET /api/eleves/{id}
     * Afficher un élève spécifique avec sa classe
     */
    public function show($id)
    {
        $eleve = Eleve::with('classe')->find($id);

        if (!$eleve) {
            return response()->json([
                'success' => false,
                'message' => 'Élève non trouvé'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $eleve
        ], 200);
    }

    /**
     * PUT /api/eleves/{id}
     * Modifier un élève
     */
    public function update(Request $request, $id)
    {
        $eleve = Eleve::find($id);

        if (!$eleve) {
            return response()->json([
                'success' => false,
                'message' => 'Élève non trouvé'
            ], 404);
        }

        // Règles de validation
        $rules = [
            'nom' => 'required|string|max:255',
            'prenom' => 'required|string|max:255',
            'matricule' => 'required|string|unique:eleves,matricule,' . $eleve->id,
            'date_naissance' => 'required|date',
            'lieu_naissance' => 'nullable|string',
            'sexe' => 'required|in:M,F',
            'classe_id' => 'nullable|exists:classes,id',
            'parent_contact' => 'nullable|string',
            'adresse' => 'nullable|string',
            'statut' => 'required|in:actif,inactif'
        ];

        // Validation des données
        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        // Mise à jour de l'élève
        $eleve->update($request->all());
        $eleve->load('classe');

        return response()->json([
            'success' => true,
            'message' => 'Élève mis à jour avec succès',
            'data' => $eleve
        ], 200);
    }

    /**
     * DELETE /api/eleves/{id}
     * Supprimer un élève
     */
    public function destroy($id)
    {
        $eleve = Eleve::find($id);

        if (!$eleve) {
            return response()->json([
                'success' => false,
                'message' => 'Élève non trouvé'
            ], 404);
        }

        $eleve->delete();

        return response()->json([
            'success' => true,
            'message' => 'Élève supprimé avec succès'
        ], 200);
    }
}
