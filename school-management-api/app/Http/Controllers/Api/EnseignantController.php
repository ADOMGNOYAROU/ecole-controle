<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Enseignant;
use App\Models\User;
use App\Models\Classe;
use App\Models\Matiere;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class EnseignantController extends Controller
{
    /**
     * Affiche une liste paginée des enseignants avec possibilité de recherche et de filtrage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        try {
            $query = Enseignant::with(['user', 'classes', 'matieres']);

            // Recherche par nom, prénom ou matricule
            if ($search = $request->input('search')) {
                $query->whereHas('user', function($q) use ($search) {
                    $q->where('nom', 'like', "%{$search}%")
                      ->orWhere('prenom', 'like', "%{$search}%");
                })->orWhere('matricule', 'like', "%{$search}%");
            }

            // Filtrage par statut
            if ($statut = $request->input('statut')) {
                $query->where('statut', $statut);
            }

            // Filtrage par spécialité
            if ($specialite = $request->input('specialite')) {
                $query->where('specialite', 'like', "%{$specialite}%");
            }

            // Tri
            $sortField = $request->input('sort_by', 'created_at');
            $sortDirection = $request->input('sort_direction', 'desc');
            
            if ($sortField === 'nom' || $sortField === 'prenom' || $sortField === 'email') {
                $query->join('users', 'enseignants.user_id', '=', 'users.id')
                      ->orderBy("users.{$sortField}", $sortDirection)
                      ->select('enseignants.*');
            } else {
                $query->orderBy($sortField, $sortDirection);
            }

            // Pagination
            $perPage = $request->input('per_page', 15);
            $enseignants = $query->paginate($perPage);

            return response()->json([
                'success' => true,
                'data' => $enseignants,
                'message' => 'Liste des enseignants récupérée avec succès.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération des enseignants.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Enregistre un nouvel enseignant dans la base de données.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'prenom' => 'required|string|max:50',
                'nom' => 'required|string|max:50',
                'email' => 'required|email|unique:users,email',
                'telephone' => 'required|string|max:20',
                'adresse' => 'required|string|max:255',
                'date_naissance' => 'required|date',
                'lieu_naissance' => 'required|string|max:100',
                'genre' => 'required|in:Masculin,Féminin',
                'date_embauche' => 'required|date',
                'specialite' => 'required|string|max:100',
                'statut' => 'required|in:Actif,En congé,Démissionné,Retraité',
                'photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
                'classes' => 'nullable|array',
                'classes.*.classe_id' => 'required_with:classes|exists:classes,id',
                'classes.*.matiere_id' => 'required_with:classes|exists:matieres,id',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Erreur de validation',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Démarrer une transaction pour assurer l'intégrité des données
            return \DB::transaction(function () use ($request) {
                // Créer l'utilisateur
                $user = User::create([
                    'prenom' => $request->prenom,
                    'nom' => $request->nom,
                    'email' => $request->email,
                    'telephone' => $request->telephone,
                    'adresse' => $request->adresse,
                    'date_naissance' => $request->date_naissance,
                    'lieu_naissance' => $request->lieu_naissance,
                    'genre' => $request->genre,
                    'role' => 'enseignant',
                    'password' => Hash::make(Str::random(10)), // Mot de passe aléatoire
                ]);

                // Générer un matricule unique
                $matricule = 'ENS' . date('y') . strtoupper(Str::random(3)) . str_pad(Enseignant::withTrashed()->count() + 1, 4, '0', STR_PAD_LEFT);
                
                // Gestion de la photo
                $photoPath = null;
                if ($request->hasFile('photo')) {
                    $path = $request->file('photo')->store('public/enseignants/photos');
                    $photoPath = Storage::url($path);
                }

                // Créer l'enseignant
                $enseignant = Enseignant::create([
                    'user_id' => $user->id,
                    'matricule' => $matricule,
                    'specialite' => $request->specialite,
                    'date_embauche' => $request->date_embauche,
                    'statut' => $request->statut,
                    'photo' => $photoPath,
                ]);

                // Attacher les classes et matières
                if ($request->has('classes')) {
                    $attachments = [];
                    foreach ($request->classes as $item) {
                        $attachments[$item['classe_id']] = ['matiere_id' => $item['matiere_id']];
                    }
                    $enseignant->classes()->sync($attachments);
                }

                // Envoyer un email avec les informations de connexion (à implémenter)
                // Mail::to($user->email)->send(new CompteEnseignantCree($user, $password));

                return response()->json([
                    'success' => true,
                    'data' => $enseignant->load(['user', 'classes', 'matieres']),
                    'message' => 'Enseignant créé avec succès.'
                ], 201);
            });
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la création de l\'enseignant.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Affiche les détails d'un enseignant spécifique.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        try {
            $enseignant = Enseignant::with([
                'user', 
                'classes' => function($query) {
                    $query->withPivot('matiere_id');
                },
                'matieres'
            ])->findOrFail($id);
            
            return response()->json([
                'success' => true,
                'data' => $enseignant,
                'message' => 'Détails de l\'enseignant récupérés avec succès.'
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Enseignant non trouvé.',
                'error' => $e->getMessage()
            ], 404);
        }
    }

    /**
     * Met à jour les informations d'un enseignant spécifique.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        try {
            $enseignant = Enseignant::with('user')->findOrFail($id);
            
            $validator = Validator::make($request->all(), [
                'prenom' => 'sometimes|required|string|max:50',
                'nom' => 'sometimes|required|string|max:50',
                'email' => 'sometimes|required|email|unique:users,email,' . $enseignant->user_id,
                'telephone' => 'sometimes|required|string|max:20',
                'adresse' => 'sometimes|required|string|max:255',
                'date_naissance' => 'sometimes|required|date',
                'lieu_naissance' => 'sometimes|required|string|max:100',
                'genre' => 'sometimes|required|in:Masculin,Féminin',
                'date_embauche' => 'sometimes|required|date',
                'date_depart' => 'nullable|date|after_or_equal:date_embauche',
                'motif_depart' => 'nullable|required_with:date_depart|string|max:255',
                'specialite' => 'sometimes|required|string|max:100',
                'statut' => 'sometimes|required|in:Actif,En congé,Démissionné,Retraité',
                'photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
                'classes' => 'nullable|array',
                'classes.*.classe_id' => 'required_with:classes|exists:classes,id',
                'classes.*.matiere_id' => 'required_with:classes|exists:matieres,id',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Erreur de validation',
                    'errors' => $validator->errors()
                ], 422);
            }

            return \DB::transaction(function () use ($request, $enseignant) {
                // Mise à jour des informations utilisateur
                $userData = [
                    'prenom' => $request->input('prenom', $enseignant->user->prenom),
                    'nom' => $request->input('nom', $enseignant->user->nom),
                    'email' => $request->input('email', $enseignant->user->email),
                    'telephone' => $request->input('telephone', $enseignant->user->telephone),
                    'adresse' => $request->input('adresse', $enseignant->user->adresse),
                    'date_naissance' => $request->input('date_naissance', $enseignant->user->date_naissance),
                    'lieu_naissance' => $request->input('lieu_naissance', $enseignant->user->lieu_naissance),
                    'genre' => $request->input('genre', $enseignant->user->genre),
                ];
                
                $enseignant->user->update($userData);

                // Mise à jour de la photo si fournie
                $photoData = [];
                if ($request->hasFile('photo')) {
                    // Supprimer l'ancienne photo si elle existe
                    if ($enseignant->photo) {
                        $oldPhotoPath = str_replace('/storage', 'public', $enseignant->photo);
                        if (Storage::exists($oldPhotoPath)) {
                            Storage::delete($oldPhotoPath);
                        }
                    }
                    
                    $path = $request->file('photo')->store('public/enseignants/photos');
                    $photoData['photo'] = Storage::url($path);
                }

                // Mise à jour des informations de l'enseignant
                $enseignantData = [
                    'specialite' => $request->input('specialite', $enseignant->specialite),
                    'date_embauche' => $request->input('date_embauche', $enseignant->date_embauche),
                    'statut' => $request->input('statut', $enseignant->statut),
                    'date_depart' => $request->input('date_depart', $enseignant->date_depart),
                    'motif_depart' => $request->input('motif_depart', $enseignant->motif_depart),
                ];
                
                $enseignant->update(array_merge($enseignantData, $photoData));

                // Mise à jour des classes et matières
                if ($request->has('classes')) {
                    $attachments = [];
                    foreach ($request->classes as $item) {
                        $attachments[$item['classe_id']] = ['matiere_id' => $item['matiere_id']];
                    }
                    $enseignant->classes()->sync($attachments);
                }

                return response()->json([
                    'success' => true,
                    'data' => $enseignant->fresh(['user', 'classes', 'matieres']),
                    'message' => 'Enseignant mis à jour avec succès.'
                ]);
            });
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la mise à jour de l\'enseignant.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Supprime un enseignant spécifique (soft delete).
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        try {
            $enseignant = Enseignant::findOrFail($id);
            
            // Vérifier s'il y a des dépendances avant de supprimer
            // Par exemple, vérifier s'il a des cours planifiés
            
            // Supprimer la photo si elle existe
            if ($enseignant->photo) {
                $photoPath = str_replace('/storage', 'public', $enseignant->photo);
                if (Storage::exists($photoPath)) {
                    Storage::delete($photoPath);
                }
            }
            
            // Supprimer l'utilisateur associé
            $user = $enseignant->user;
            $enseignant->delete();
            $user->delete();
            
            return response()->json([
                'success' => true,
                'message' => 'Enseignant supprimé avec succès.'
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la suppression de l\'enseignant.',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Restaure un enseignant supprimé (soft delete).
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function restore($id)
    {
        try {
            $enseignant = Enseignant::withTrashed()->findOrFail($id);
            
            if ($enseignant->trashed()) {
                $enseignant->restore();
                $enseignant->user()->restore();
                
                return response()->json([
                    'success' => true,
                    'message' => 'Enseignant restauré avec succès.',
                    'data' => $enseignant->load('user')
                ]);
            }
            
            return response()->json([
                'success' => false,
                'message' => 'Cet enseignant n\'a pas été supprimé.'
            ], 400);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la restauration de l\'enseignant.',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Récupère les statistiques des enseignants.
     *
     * @return \Illuminate\Http\Response
     */
    public function statistics()
    {
        try {
            $total = Enseignant::count();
            $actifs = Enseignant::where('statut', 'Actif')->count();
            $enConge = Enseignant::where('statut', 'En congé')->count();
            $demissionnes = Enseignant::where('statut', 'Démissionné')->count();
            $retraites = Enseignant::where('statut', 'Retraité')->count();
            
            // Statistiques par genre
            $hommes = Enseignant::whereHas('user', function($q) {
                $q->where('genre', 'Masculin');
            })->count();
            
            $femmes = Enseignant::whereHas('user', function($q) {
                $q->where('genre', 'Féminin');
            })->count();
            
            // Spécialités les plus courantes
            $specialites = Enseignant::select('specialite', \DB::raw('count(*) as total'))
                ->groupBy('specialite')
                ->orderBy('total', 'desc')
                ->take(5)
                ->get();
            
            return response()->json([
                'success' => true,
                'data' => [
                    'total' => $total,
                    'par_statut' => [
                        'actifs' => $actifs,
                        'en_conge' => $enConge,
                        'demissionnes' => $demissionnes,
                        'retraites' => $retraites,
                    ],
                    'par_genre' => [
                        'hommes' => $hommes,
                        'femmes' => $femmes,
                    ],
                    'specialites' => $specialites,
                ],
                'message' => 'Statistiques des enseignants récupérées avec succès.'
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération des statistiques.',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Récupère les classes et matières enseignées par un enseignant.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function getClassesEtMatieres($id)
    {
        try {
            $enseignant = Enseignant::with([
                'classes' => function($query) {
                    $query->withPivot('matiere_id');
                },
                'matieres'
            ])->findOrFail($id);
            
            // Formater la réponse pour une meilleure lisibilité
            $classesAvecMatieres = $enseignant->classes->map(function($classe) use ($enseignant) {
                $matiere = $enseignant->matieres->firstWhere('id', $classe->pivot->matiere_id);
                return [
                    'classe_id' => $classe->id,
                    'classe_nom' => $classe->nom,
                    'niveau' => $classe->niveau,
                    'matiere_id' => $matiere ? $matiere->id : null,
                    'matiere_nom' => $matiere ? $matiere->nom : null,
                    'coefficient' => $matiere ? $matiere->coefficient : null,
                ];
            });
            
            return response()->json([
                'success' => true,
                'data' => [
                    'enseignant_id' => $enseignant->id,
                    'enseignant_nom' => $enseignant->user->nom_complet,
                    'specialite' => $enseignant->specialite,
                    'classes_et_matieres' => $classesAvecMatieres
                ],
                'message' => 'Classes et matières de l\'enseignant récupérées avec succès.'
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération des classes et matières.',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Met à jour le mot de passe d'un enseignant.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function updatePassword(Request $request, $id)
    {
        try {
            $validator = Validator::make($request->all(), [
                'password' => 'required|string|min:8|confirmed',
                'password_confirmation' => 'required|string|same:password',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Erreur de validation',
                    'errors' => $validator->errors()
                ], 422);
            }
            
            $enseignant = Enseignant::findOrFail($id);
            $enseignant->user->update([
                'password' => Hash::make($request->password)
            ]);
            
            // Envoyer un email de notification (à implémenter)
            // Mail::to($enseignant->user->email)->send(new MotDePasseModifie($enseignant->user));
            
            return response()->json([
                'success' => true,
                'message' => 'Mot de passe mis à jour avec succès.'
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la mise à jour du mot de passe.',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
