<?php

namespace App\Http\Controllers;

use App\Models\Enseignant;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class EnseignantController extends Controller
{
    /**
     * GET /api/enseignants
     * Liste tous les enseignants
     */
    public function index()
    {
        $enseignants = Enseignant::with('user')->get();
        
        return response()->json([
            'success' => true,
            'data' => $enseignants
        ], 200);
    }

    /**
     * POST /api/enseignants
     * Créer un nouvel enseignant
     */
    public function store(Request $request)
    {
        // Validation
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8',
            'telephone' => 'nullable|string',
            'matricule' => 'required|string|unique:enseignants,matricule',
            'specialite' => 'nullable|string',
            'date_embauche' => 'nullable|date',
            'statut' => 'required|in:actif,inactif'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            // Transaction pour créer user ET enseignant
            DB::beginTransaction();

            // Créer l'utilisateur
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'role' => 'enseignant',
                'telephone' => $request->telephone
            ]);

            // Créer l'enseignant
            $enseignant = Enseignant::create([
                'user_id' => $user->id,
                'matricule' => $request->matricule,
                'specialite' => $request->specialite,
                'date_embauche' => $request->date_embauche,
                'statut' => $request->statut
            ]);

            DB::commit();

            // Charger la relation user
            $enseignant->load('user');

            return response()->json([
                'success' => true,
                'message' => 'Enseignant créé avec succès',
                'data' => $enseignant
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la création : ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * GET /api/enseignants/{id}
     * Afficher un enseignant spécifique
     */
    public function show($id)
    {
        $enseignant = Enseignant::with(['user', 'classes', 'matieres'])->find($id);

        if (!$enseignant) {
            return response()->json([
                'success' => false,
                'message' => 'Enseignant non trouvé'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $enseignant
        ], 200);
    }

    /**
     * PUT /api/enseignants/{id}
     * Modifier un enseignant
     */
    public function update(Request $request, $id)
    {
        $enseignant = Enseignant::find($id);

        if (!$enseignant) {
            return response()->json([
                'success' => false,
                'message' => 'Enseignant non trouvé'
            ], 404);
        }

        // Validation
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $enseignant->user_id,
            'telephone' => 'nullable|string',
            'matricule' => 'required|string|unique:enseignants,matricule,' . $id,
            'specialite' => 'nullable|string',
            'date_embauche' => 'nullable|date',
            'statut' => 'required|in:actif,inactif'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            DB::beginTransaction();

            // Mettre à jour l'utilisateur
            $enseignant->user->update([
                'name' => $request->name,
                'email' => $request->email,
                'telephone' => $request->telephone
            ]);

            // Mettre à jour l'enseignant
            $enseignant->update([
                'matricule' => $request->matricule,
                'specialite' => $request->specialite,
                'date_embauche' => $request->date_embauche,
                'statut' => $request->statut
            ]);

            DB::commit();

            $enseignant->load('user');

            return response()->json([
                'success' => true,
                'message' => 'Enseignant modifié avec succès',
                'data' => $enseignant
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();
            
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la modification : ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * DELETE /api/enseignants/{id}
     * Supprimer un enseignant
     */
    public function destroy($id)
    {
        $enseignant = Enseignant::find($id);

        if (!$enseignant) {
            return response()->json([
                'success' => false,
                'message' => 'Enseignant non trouvé'
            ], 404);
        }

        try {
            DB::beginTransaction();

            $user = $enseignant->user;
            
            // Supprimer l'enseignant (cascade supprimera les relations)
            $enseignant->delete();
            
            // Supprimer l'utilisateur associé
            $user->delete();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Enseignant supprimé avec succès'
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();
            
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la suppression : ' . $e->getMessage()
            ], 500);
        }
    }
}