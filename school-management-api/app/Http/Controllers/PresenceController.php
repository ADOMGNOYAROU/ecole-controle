<?php

namespace App\Http\Controllers;

use App\Models\Presence;
use App\Models\Classe;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class PresenceController extends Controller
{
    /**
     * GET /api/presences
     * Liste toutes les présences (avec filtres optionnels)
     */
    public function index(Request $request)
    {
        $query = Presence::with(['eleve', 'classe', 'enseignant.user']);

        // Filtre par date
        if ($request->has('date')) {
            $query->byDate($request->date);
        }

        // Filtre par classe
        if ($request->has('classe_id')) {
            $query->where('classe_id', $request->classe_id);
        }

        // Filtre par statut
        if ($request->has('statut')) {
            $query->byStatut($request->statut);
        }

        $presences = $query->orderBy('date', 'desc')->get();
        
        return response()->json([
            'success' => true,
            'data' => $presences
        ], 200);
    }

    /**
     * POST /api/presences
     * Marquer la présence d'un ou plusieurs élèves
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'eleve_id' => 'required|exists:eleves,id',
            'classe_id' => 'required|exists:classes,id',
            'date' => 'required|date',
            'statut' => 'required|in:present,absent,retard,absent_justifie',
            'motif' => 'nullable|string',
            'enseignant_id' => 'nullable|exists:enseignants,id'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        // Vérifier si la présence existe déjà
        $existingPresence = Presence::where('eleve_id', $request->eleve_id)
                                    ->where('date', $request->date)
                                    ->first();

        if ($existingPresence) {
            return response()->json([
                'success' => false,
                'message' => 'La présence pour cet élève à cette date existe déjà'
            ], 400);
        }

        $presence = Presence::create($request->all());
        $presence->load(['eleve', 'classe', 'enseignant.user']);

        return response()->json([
            'success' => true,
            'message' => 'Présence enregistrée avec succès',
            'data' => $presence
        ], 201);
    }

    /**
     * POST /api/presences/bulk
     * Marquer la présence de plusieurs élèves en une fois
     */
    public function storeBulk(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'classe_id' => 'required|exists:classes,id',
            'date' => 'required|date',
            'enseignant_id' => 'required|exists:enseignants,id',
            'presences' => 'required|array',
            'presences.*.eleve_id' => 'required|exists:eleves,id',
            'presences.*.statut' => 'required|in:present,absent,retard,absent_justifie',
            'presences.*.motif' => 'nullable|string'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $presencesCreated = [];

        foreach ($request->presences as $presenceData) {
            $presence = Presence::updateOrCreate(
                [
                    'eleve_id' => $presenceData['eleve_id'],
                    'date' => $request->date
                ],
                [
                    'classe_id' => $request->classe_id,
                    'statut' => $presenceData['statut'],
                    'motif' => $presenceData['motif'] ?? null,
                    'enseignant_id' => $request->enseignant_id
                ]
            );

            $presencesCreated[] = $presence;
        }

        return response()->json([
            'success' => true,
            'message' => count($presencesCreated) . ' présences enregistrées',
            'data' => $presencesCreated
        ], 201);
    }

    /**
     * GET /api/presences/{id}
     * Afficher une présence spécifique
     */
    public function show($id)
    {
        $presence = Presence::with(['eleve', 'classe', 'enseignant.user'])->find($id);

        if (!$presence) {
            return response()->json([
                'success' => false,
                'message' => 'Présence non trouvée'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $presence
        ], 200);
    }

    /**
     * PUT /api/presences/{id}
     * Modifier une présence
     */
    public function update(Request $request, $id)
    {
        $presence = Presence::find($id);

        if (!$presence) {
            return response()->json([
                'success' => false,
                'message' => 'Présence non trouvée'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'statut' => 'required|in:present,absent,retard,absent_justifie',
            'motif' => 'nullable|string'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $presence->update($request->all());
        $presence->load(['eleve', 'classe', 'enseignant.user']);

        return response()->json([
            'success' => true,
            'message' => 'Présence modifiée avec succès',
            'data' => $presence
        ], 200);
    }

    /**
     * DELETE /api/presences/{id}
     * Supprimer une présence
     */
    public function destroy($id)
    {
        $presence = Presence::find($id);

        if (!$presence) {
            return response()->json([
                'success' => false,
                'message' => 'Présence non trouvée'
            ], 404);
        }

        $presence->delete();

        return response()->json([
            'success' => true,
            'message' => 'Présence supprimée avec succès'
        ], 200);
    }

    /**
     * GET /api/classes/{id}/presences
     * Récupérer les présences d'une classe pour une date donnée
     */
    public function getByClasse($classeId, Request $request)
    {
        $validator = Validator::make($request->all(), [
            'date' => 'required|date'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $classe = Classe::find($classeId);

        if (!$classe) {
            return response()->json([
                'success' => false,
                'message' => 'Classe non trouvée'
            ], 404);
        }

        $presences = Presence::with('eleve')
                            ->where('classe_id', $classeId)
                            ->byDate($request->date)
                            ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'classe' => $classe,
                'date' => $request->date,
                'presences' => $presences
            ]
        ], 200);
    }

    /**
     * GET /api/eleves/{id}/presences
     * Historique des présences d'un élève
     */
    public function getByEleve($eleveId)
    {
        $presences = Presence::with(['classe', 'enseignant.user'])
                            ->where('eleve_id', $eleveId)
                            ->orderBy('date', 'desc')
                            ->get();

        // Statistiques
        $stats = [
            'total' => $presences->count(),
            'presents' => $presences->where('statut', 'present')->count(),
            'absents' => $presences->where('statut', 'absent')->count(),
            'retards' => $presences->where('statut', 'retard')->count(),
            'absents_justifies' => $presences->where('statut', 'absent_justifie')->count(),
        ];

        return response()->json([
            'success' => true,
            'data' => [
                'presences' => $presences,
                'statistiques' => $stats
            ]
        ], 200);
    }
}