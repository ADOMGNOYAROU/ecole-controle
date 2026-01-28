<?php

namespace App\Http\Controllers;

use App\Models\Note;
use App\Models\Eleve;
use App\Models\Classe;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class NoteController extends Controller
{
    /**
     * GET /api/notes
     * Liste toutes les notes (avec filtres optionnels)
     */
    public function index(Request $request)
    {
        $query = Note::with(['eleve', 'matiere', 'classe', 'enseignant.user']);

        // Filtre par classe
        if ($request->has('classe_id')) {
            $query->where('classe_id', $request->classe_id);
        }

        // Filtre par matière
        if ($request->has('matiere_id')) {
            $query->where('matiere_id', $request->matiere_id);
        }

        // Filtre par trimestre
        if ($request->has('trimestre')) {
            $query->byTrimestre($request->trimestre);
        }

        // Filtre par type d'évaluation
        if ($request->has('type_evaluation')) {
            $query->byType($request->type_evaluation);
        }

        $notes = $query->orderBy('date_evaluation', 'desc')->get();
        
        return response()->json([
            'success' => true,
            'data' => $notes
        ], 200);
    }

    /**
     * POST /api/notes
     * Ajouter une note
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'eleve_id' => 'required|exists:eleves,id',
            'matiere_id' => 'required|exists:matieres,id',
            'classe_id' => 'required|exists:classes,id',
            'type_evaluation' => 'required|in:devoir,interrogation,examen,composition',
            'note' => 'required|numeric|min:0',
            'note_sur' => 'required|numeric|min:1',
            'date_evaluation' => 'required|date',
            'trimestre' => 'required|integer|min:1|max:3',
            'enseignant_id' => 'nullable|exists:enseignants,id',
            'observation' => 'nullable|string'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        // Vérifier que la note ne dépasse pas note_sur
        if ($request->note > $request->note_sur) {
            return response()->json([
                'success' => false,
                'message' => 'La note ne peut pas dépasser la note maximale'
            ], 400);
        }

        $note = Note::create($request->all());
        $note->load(['eleve', 'matiere', 'classe', 'enseignant.user']);

        return response()->json([
            'success' => true,
            'message' => 'Note ajoutée avec succès',
            'data' => $note
        ], 201);
    }

    /**
     * POST /api/notes/bulk
     * Ajouter plusieurs notes en une fois
     */
    public function storeBulk(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'matiere_id' => 'required|exists:matieres,id',
            'classe_id' => 'required|exists:classes,id',
            'type_evaluation' => 'required|in:devoir,interrogation,examen,composition',
            'note_sur' => 'required|numeric|min:1',
            'date_evaluation' => 'required|date',
            'trimestre' => 'required|integer|min:1|max:3',
            'enseignant_id' => 'required|exists:enseignants,id',
            'notes' => 'required|array',
            'notes.*.eleve_id' => 'required|exists:eleves,id',
            'notes.*.note' => 'required|numeric|min:0',
            'notes.*.observation' => 'nullable|string'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $notesCreated = [];
        $errors = [];

        DB::beginTransaction();

        try {
            foreach ($request->notes as $noteData) {
                // Vérifier que la note ne dépasse pas note_sur
                if ($noteData['note'] > $request->note_sur) {
                    $errors[] = "La note de l'élève {$noteData['eleve_id']} dépasse la note maximale";
                    continue;
                }

                $note = Note::create([
                    'eleve_id' => $noteData['eleve_id'],
                    'matiere_id' => $request->matiere_id,
                    'classe_id' => $request->classe_id,
                    'type_evaluation' => $request->type_evaluation,
                    'note' => $noteData['note'],
                    'note_sur' => $request->note_sur,
                    'date_evaluation' => $request->date_evaluation,
                    'trimestre' => $request->trimestre,
                    'enseignant_id' => $request->enseignant_id,
                    'observation' => $noteData['observation'] ?? null
                ]);

                $notesCreated[] = $note;
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => count($notesCreated) . ' notes enregistrées',
                'data' => $notesCreated,
                'errors' => $errors
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de l\'enregistrement : ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * GET /api/notes/{id}
     * Afficher une note spécifique
     */
    public function show($id)
    {
        $note = Note::with(['eleve', 'matiere', 'classe', 'enseignant.user'])->find($id);

        if (!$note) {
            return response()->json([
                'success' => false,
                'message' => 'Note non trouvée'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $note
        ], 200);
    }

    /**
     * PUT /api/notes/{id}
     * Modifier une note
     */
    public function update(Request $request, $id)
    {
        $note = Note::find($id);

        if (!$note) {
            return response()->json([
                'success' => false,
                'message' => 'Note non trouvée'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'note' => 'required|numeric|min:0',
            'note_sur' => 'required|numeric|min:1',
            'observation' => 'nullable|string'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        // Vérifier que la note ne dépasse pas note_sur
        if ($request->note > $request->note_sur) {
            return response()->json([
                'success' => false,
                'message' => 'La note ne peut pas dépasser la note maximale'
            ], 400);
        }

        $note->update($request->all());
        $note->load(['eleve', 'matiere', 'classe', 'enseignant.user']);

        return response()->json([
            'success' => true,
            'message' => 'Note modifiée avec succès',
            'data' => $note
        ], 200);
    }

    /**
     * DELETE /api/notes/{id}
     * Supprimer une note
     */
    public function destroy($id)
    {
        $note = Note::find($id);

        if (!$note) {
            return response()->json([
                'success' => false,
                'message' => 'Note non trouvée'
            ], 404);
        }

        $note->delete();

        return response()->json([
            'success' => true,
            'message' => 'Note supprimée avec succès'
        ], 200);
    }

    /**
     * GET /api/eleves/{id}/notes
     * Récupérer toutes les notes d'un élève
     */
    public function getByEleve($eleveId, Request $request)
    {
        $eleve = Eleve::find($eleveId);

        if (!$eleve) {
            return response()->json([
                'success' => false,
                'message' => 'Élève non trouvé'
            ], 404);
        }

        $query = Note::with(['matiere', 'classe', 'enseignant.user'])
                     ->where('eleve_id', $eleveId);

        // Filtre optionnel par trimestre
        if ($request->has('trimestre')) {
            $query->byTrimestre($request->trimestre);
        }

        $notes = $query->orderBy('date_evaluation', 'desc')->get();

        // Calculer la moyenne générale
        $moyenneGenerale = 0;
        $totalCoefficients = 0;

        foreach ($notes as $note) {
            $noteSur20 = ($note->note / $note->note_sur) * 20;
            $coefficient = $note->matiere->coefficient ?? 1;
            $moyenneGenerale += $noteSur20 * $coefficient;
            $totalCoefficients += $coefficient;
        }

        if ($totalCoefficients > 0) {
            $moyenneGenerale = round($moyenneGenerale / $totalCoefficients, 2);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'eleve' => $eleve,
                'notes' => $notes,
                'moyenne_generale' => $moyenneGenerale,
                'total_notes' => $notes->count()
            ]
        ], 200);
    }

    /**
     * GET /api/classes/{id}/notes
     * Récupérer toutes les notes d'une classe
     */
    public function getByClasse($classeId, Request $request)
    {
        $classe = Classe::find($classeId);

        if (!$classe) {
            return response()->json([
                'success' => false,
                'message' => 'Classe non trouvée'
            ], 404);
        }

        $query = Note::with(['eleve', 'matiere', 'enseignant.user'])
                     ->where('classe_id', $classeId);

        // Filtre optionnel par matière
        if ($request->has('matiere_id')) {
            $query->where('matiere_id', $request->matiere_id);
        }

        // Filtre optionnel par trimestre
        if ($request->has('trimestre')) {
            $query->byTrimestre($request->trimestre);
        }

        $notes = $query->orderBy('date_evaluation', 'desc')->get();

        // Calculer la moyenne de la classe
        $moyenneClasse = 0;
        if ($notes->count() > 0) {
            $totalNotes = 0;
            foreach ($notes as $note) {
                $totalNotes += ($note->note / $note->note_sur) * 20;
            }
            $moyenneClasse = round($totalNotes / $notes->count(), 2);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'classe' => $classe,
                'notes' => $notes,
                'moyenne_classe' => $moyenneClasse,
                'total_notes' => $notes->count()
            ]
        ], 200);
    }

    /**
     * GET /api/notes/statistiques
     * Statistiques globales des notes
     */
    public function statistiques(Request $request)
    {
        $query = Note::query();

        // Filtres optionnels
        if ($request->has('classe_id')) {
            $query->where('classe_id', $request->classe_id);
        }

        if ($request->has('matiere_id')) {
            $query->where('matiere_id', $request->matiere_id);
        }

        if ($request->has('trimestre')) {
            $query->byTrimestre($request->trimestre);
        }

        $notes = $query->get();

        // Convertir toutes les notes sur 20
        $notesSur20 = $notes->map(function($note) {
            return ($note->note / $note->note_sur) * 20;
        });

        $stats = [
            'total_notes' => $notes->count(),
            'moyenne' => $notes->count() > 0 ? round($notesSur20->avg(), 2) : 0,
            'note_min' => $notes->count() > 0 ? round($notesSur20->min(), 2) : 0,
            'note_max' => $notes->count() > 0 ? round($notesSur20->max(), 2) : 0,
            'notes_par_type' => [
                'devoir' => $notes->where('type_evaluation', 'devoir')->count(),
                'interrogation' => $notes->where('type_evaluation', 'interrogation')->count(),
                'examen' => $notes->where('type_evaluation', 'examen')->count(),
                'composition' => $notes->where('type_evaluation', 'composition')->count(),
            ]
        ];

        return response()->json([
            'success' => true,
            'data' => $stats
        ], 200);
    }
}