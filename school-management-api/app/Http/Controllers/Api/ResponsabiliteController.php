<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Enseignant;
use App\Models\Responsabilite;
use App\Models\Classe;
use App\Models\Matiere;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ResponsabiliteController extends Controller
{
    /**
     * Affiche les responsabilités d'un enseignant
     *
     * @param  int  $enseignantId
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function index($enseignantId, Request $request)
    {
        try {
            $enseignant = Enseignant::findOrFail($enseignantId);
            
            $query = $enseignant->responsabilites()
                ->with(['classe', 'matiere'])
                ->orderBy('date_debut', 'desc');
            
            // Filtrage par statut
            if ($request->has('statut')) {
                $query->where('statut', $request->statut);
            }
            
            // Filtrage par type
            if ($request->has('type')) {
                $query->where('type', $request->type);
            }
            
            // Filtrage par période
            if ($request->has('periode')) {
                $now = now();
                switch ($request->periode) {
                    case 'en_cours':
                        $query->where('date_debut', '<=', $now)
                            ->where(function($q) use ($now) {
                                $q->whereNull('date_fin')
                                  ->orWhere('date_fin', '>=', $now);
                            });
                        break;
                    case 'a_venir':
                        $query->where('date_debut', '>', $now);
                        break;
                    case 'termine':
                        $query->where('date_fin', '<', $now);
                        break;
                }
            }
            
            $responsabilites = $query->paginate($request->per_page ?? 15);
            
            return response()->json([
                'success' => true,
                'data' => $responsabilites,
                'message' => 'Responsabilités récupérées avec succès.'
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération des responsabilités.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Ajoute une nouvelle responsabilité à un enseignant
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $enseignantId
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request, $enseignantId)
    {
        try {
            $enseignant = Enseignant::findOrFail($enseignantId);
            
            $validator = Validator::make($request->all(), [
                'type' => 'required|in:' . implode(',', [
                    Responsabilite::TYPE_COURS,
                    Responsabilite::TYPE_SURVEILLANCE,
                    Responsabilite::TYPE_ACTIVITE,
                    Responsabilite::TYPE_COMMISSION,
                    Responsabilite::TYPE_AUTRE
                ]),
                'description' => 'required|string|max:1000',
                'date_debut' => 'required|date',
                'date_fin' => 'nullable|date|after_or_equal:date_debut',
                'statut' => 'in:' . implode(',', [
                    Responsabilite::STATUT_ACTIF,
                    Responsabilite::STATUT_TERMINE,
                    Responsabilite::STATUT_ANNULE
                ]),
                'classe_id' => 'nullable|exists:classes,id',
                'matiere_id' => 'nullable|exists:matieres,id',
                'commentaires' => 'nullable|string|max:2000',
                'horaires' => 'nullable|array',
                'horaires.*.jour' => 'required|in:lundi,mardi,mercredi,jeudi,vendredi,samedi,dimanche',
                'horaires.*.debut' => 'required|date_format:H:i',
                'horaires.*.fin' => 'required|date_format:H:i|after:horaires.*.debut',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Erreur de validation',
                    'errors' => $validator->errors()
                ], 422);
            }
            
            $data = $validator->validated();
            $data['enseignant_id'] = $enseignant->id;
            $data['statut'] = $data['statut'] ?? Responsabilite::STATUT_ACTIF;
            
            $responsabilite = Responsabilite::create($data);
            
            return response()->json([
                'success' => true,
                'data' => $responsabilite->load(['classe', 'matiere']),
                'message' => 'Responsabilité ajoutée avec succès.'
            ], 201);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de l\'ajout de la responsabilité.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Affiche les détails d'une responsabilité
     *
     * @param  int  $enseignantId
     * @param  int  $responsabiliteId
     * @return \Illuminate\Http\Response
     */
    public function show($enseignantId, $responsabiliteId)
    {
        try {
            $responsabilite = Responsabilite::with(['enseignant', 'classe', 'matiere'])
                ->where('enseignant_id', $enseignantId)
                ->findOrFail($responsabiliteId);
            
            return response()->json([
                'success' => true,
                'data' => $responsabilite,
                'message' => 'Détails de la responsabilité récupérés avec succès.'
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Responsabilité non trouvée.',
                'error' => $e->getMessage()
            ], 404);
        }
    }

    /**
     * Met à jour une responsabilité
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $enseignantId
     * @param  int  $responsabiliteId
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $enseignantId, $responsabiliteId)
    {
        try {
            $responsabilite = Responsabilite::where('enseignant_id', $enseignantId)
                ->findOrFail($responsabiliteId);
            
            $validator = Validator::make($request->all(), [
                'type' => 'sometimes|in:' . implode(',', [
                    Responsabilite::TYPE_COURS,
                    Responsabilite::TYPE_SURVEILLANCE,
                    Responsabilite::TYPE_ACTIVITE,
                    Responsabilite::TYPE_COMMISSION,
                    Responsabilite::TYPE_AUTRE
                ]),
                'description' => 'sometimes|string|max:1000',
                'date_debut' => 'sometimes|date',
                'date_fin' => 'nullable|date|after_or_equal:date_debut',
                'statut' => 'sometimes|in:' . implode(',', [
                    Responsabilite::STATUT_ACTIF,
                    Responsabilite::STATUT_TERMINE,
                    Responsabilite::STATUT_ANNULE
                ]),
                'classe_id' => 'nullable|exists:classes,id',
                'matiere_id' => 'nullable|exists:matieres,id',
                'commentaires' => 'nullable|string|max:2000',
                'horaires' => 'nullable|array',
                'horaires.*.jour' => 'required_with:horaires|in:lundi,mardi,mercredi,jeudi,vendredi,samedi,dimanche',
                'horaires.*.debut' => 'required_with:horaires|date_format:H:i',
                'horaires.*.fin' => 'required_with:horaires|date_format:H:i|after:horaires.*.debut',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Erreur de validation',
                    'errors' => $validator->errors()
                ], 422);
            }
            
            $responsabilite->update($validator->validated());
            
            return response()->json([
                'success' => true,
                'data' => $responsabilite->fresh(['classe', 'matiere']),
                'message' => 'Responsabilité mise à jour avec succès.'
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la mise à jour de la responsabilité.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Supprime une responsabilité
     *
     * @param  int  $enseignantId
     * @param  int  $responsabiliteId
     * @return \Illuminate\Http\Response
     */
    public function destroy($enseignantId, $responsabiliteId)
    {
        try {
            $responsabilite = Responsabilite::where('enseignant_id', $enseignantId)
                ->findOrFail($responsabiliteId);
            
            // Vérifier s'il y a des dépendances avant de supprimer
            // Par exemple, s'il y a des présences ou des évaluations liées
            
            $responsabilite->delete();
            
            return response()->json([
                'success' => true,
                'message' => 'Responsabilité supprimée avec succès.'
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la suppression de la responsabilité.',
                'error' => $e->getMessage()
            ], 500);
        }
     * Annule une responsabilité
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $responsabiliteId
     * @return \Illuminate\Http\Response
     */
    public function annuler(Request $request, $responsabiliteId)
    {
        try {
            $responsabilite = Responsabilite::findOrFail($responsabiliteId);
            
            $raison = $request->input('raison', 'Raison non spécifiée');
            $responsabilite->marquerAnnulee($raison);
            
            return response()->json([
                'success' => true,
                'message' => 'Responsabilité annulée avec succès.',
                'data' => $responsabilite->fresh()
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de l\'annulation de la responsabilité.',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Récupère les statistiques des responsabilités d'un enseignant
     *
     * @param  int  $enseignantId
     * @return \Illuminate\Http\Response
     */
    public function statistiques(Enseignant $enseignant)
    {
        try {
            // Nombre total de responsabilités
            $total = $enseignant->responsabilites()->count();
            
            // Répartition par type
            $parType = $enseignant->responsabilites()
                ->select('type', DB::raw('count(*) as total'))
                ->groupBy('type')
                ->pluck('total', 'type');
            
            // Répartition par statut
            $parStatut = $enseignant->responsabilites()
                ->select('statut', DB::raw('count(*) as total'))
                ->groupBy('statut')
                ->pluck('total', 'statut');
            
            // Responsabilités actuelles
            $actuelles = $enseignant->responsabilites()
                ->where('statut', Responsabilite::STATUT_ACTIF)
                ->where(function($query) {
                    $query->whereNull('date_fin')
                          ->orWhere('date_fin', '>=', now());
                })
                ->with(['classe', 'matiere'])
                ->orderBy('date_debut')
                ->get();
            
            // Prochaines responsabilités
            $prochaines = $enseignant->responsabilites()
                ->where('statut', Responsabilite::STATUT_ACTIF)
                ->where('date_debut', '>', now())
                ->with(['classe', 'matiere'])
                ->orderBy('date_debut')
                ->take(5)
                ->get();
            
            return response()->json([
                'success' => true,
                'data' => [
                    'total' => $total,
                    'par_type' => $parType,
                    'par_statut' => $parStatut,
                    'actuelles' => $actuelles,
                    'prochaines' => $prochaines,
                ],
                'message' => 'Statistiques des responsabilités récupérées avec succès.'
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération des statistiques.',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
