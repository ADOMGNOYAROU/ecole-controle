<?php

namespace App\Http\Controllers;

use App\Models\Matiere;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class MatiereController extends Controller
{
    /**
     * GET /api/matieres
     * Liste toutes les matières
     */
    public function index()
    {
        $matieres = Matiere::all();
        
        return response()->json([
            'success' => true,
            'data' => $matieres
        ], 200);
    }

    /**
     * POST /api/matieres
     * Créer une nouvelle matière
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nom' => 'required|string|max:255',
            'code' => 'required|string|unique:matieres,code|max:10',
            'coefficient' => 'required|numeric|min:0.5|max:10'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $matiere = Matiere::create($request->all());

        return response()->json([
            'success' => true,
            'message' => 'Matière créée avec succès',
            'data' => $matiere
        ], 201);
    }

    /**
     * GET /api/matieres/{id}
     * Afficher une matière spécifique
     */
    public function show($id)
    {
        $matiere = Matiere::with('enseignants')->find($id);

        if (!$matiere) {
            return response()->json([
                'success' => false,
                'message' => 'Matière non trouvée'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $matiere
        ], 200);
    }

    /**
     * PUT /api/matieres/{id}
     * Modifier une matière
     */
    public function update(Request $request, $id)
    {
        $matiere = Matiere::find($id);

        if (!$matiere) {
            return response()->json([
                'success' => false,
                'message' => 'Matière non trouvée'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'nom' => 'required|string|max:255',
            'code' => 'required|string|max:10|unique:matieres,code,' . $id,
            'coefficient' => 'required|numeric|min:0.5|max:10'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $matiere->update($request->all());

        return response()->json([
            'success' => true,
            'message' => 'Matière modifiée avec succès',
            'data' => $matiere
        ], 200);
    }

    /**
     * DELETE /api/matieres/{id}
     * Supprimer une matière
     */
    public function destroy($id)
    {
        $matiere = Matiere::find($id);

        if (!$matiere) {
            return response()->json([
                'success' => false,
                'message' => 'Matière non trouvée'
            ], 404);
        }

        // Vérifier s'il y a des notes pour cette matière
        if ($matiere->notes()->count() > 0) {
            return response()->json([
                'success' => false,
                'message' => 'Impossible de supprimer une matière qui a des notes'
            ], 400);
        }

        $matiere->delete();

        return response()->json([
            'success' => true,
            'message' => 'Matière supprimée avec succès'
        ], 200);
    }
}