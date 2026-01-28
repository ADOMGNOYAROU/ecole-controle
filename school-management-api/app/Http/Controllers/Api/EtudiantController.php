<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Etudiant;
use App\Models\Classe;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class EtudiantController extends Controller
{
    /**
     * Affiche une liste paginée des étudiants avec possibilité de recherche et de filtrage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        try {
            $query = Etudiant::with(['classe', 'parent']);

            // Recherche par nom, prénom ou matricule
            if ($search = $request->input('search')) {
                $query->where(function($q) use ($search) {
                    $q->where('nom', 'like', "%{$search}%")
                      ->orWhere('prenom', 'like', "%{$search}%")
                      ->orWhere('matricule', 'like', "%{$search}%");
                });
            }

            // Filtrage par classe
            if ($classeId = $request->input('classe_id')) {
                $query->where('classe_id', $classeId);
            }

            // Filtrage par statut
            if ($statut = $request->input('statut')) {
                $query->where('statut', $statut);
            }

            // Tri
            $sortField = $request->input('sort_by', 'nom');
            $sortDirection = $request->input('sort_direction', 'asc');
            $query->orderBy($sortField, $sortDirection);

            // Pagination
            $perPage = $request->input('per_page', 15);
            $etudiants = $query->paginate($perPage);

            return response()->json([
                'success' => true,
                'data' => $etudiants,
                'message' => 'Liste des étudiants récupérée avec succès.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération des étudiants.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Enregistre un nouvel étudiant dans la base de données.
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
                'date_naissance' => 'required|date',
                'genre' => 'required|in:Masculin,Féminin',
                'lieu_naissance' => 'required|string|max:100',
                'adresse' => 'required|string|max:255',
                'telephone' => 'required|string|max:20',
                'email' => 'required|email|unique:etudiants,email',
                'classe_id' => 'required|exists:classes,id',
                'parent_id' => 'nullable|exists:parents,id',
                'statut' => 'required|in:Actif,Inactif,Diplômé,Abandonné,Exclu',
                'date_inscription' => 'required|date',
                'photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
                'groupe_sanguin' => 'nullable|string|max:10',
                'allergies' => 'nullable|string',
                'antecedents_medicaux' => 'nullable|string',
                'traitement_medical' => 'nullable|string',
                'nationalite' => 'required|string|max:50',
                'contact_urgence_nom' => 'required|string|max:100',
                'contact_urgence_telephone' => 'required|string|max:20',
                'contact_urgence_lien' => 'required|string|max:100',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Erreur de validation',
                    'errors' => $validator->errors()
                ], 422);
            }

            $data = $request->all();
            
            // Génération du matricule
            $annee = date('y');
            $classe = Classe::find($data['classe_id']);
            $classeCode = $classe ? strtoupper(substr($classe->code, 0, 3)) : 'GEN';
            $matricule = $classeCode . $annee . strtoupper(Str::random(3)) . str_pad(Etudiant::withTrashed()->count() + 1, 4, '0', STR_PAD_LEFT);
            
            $data['matricule'] = $matricule;
            
            // Génération du nom d'utilisateur et mot de passe
            $username = strtolower(substr($data['prenom'], 0, 1) . $data['nom']);
            $username = preg_replace('/[^a-z0-9]/', '', $username);
            $username = $this->generateUniqueUsername($username);
            
            $password = Str::random(8);
            
            $data['username'] = $username;
            $data['password'] = Hash::make($password);
            $data['date_naissance'] = Carbon::parse($data['date_naissance'])->format('Y-m-d');
            $data['date_inscription'] = Carbon::parse($data['date_inscription'])->format('Y-m-d');
            
            // Gestion de la photo
            if ($request->hasFile('photo')) {
                $path = $request->file('photo')->store('public/etudiants/photos');
                $data['photo'] = Storage::url($path);
            }
            
            $etudiant = Etudiant::create($data);
            
            // Envoyer un email avec les informations de connexion (à implémenter)
            // Mail::to($etudiant->email)->send(new CompteEtudiantCree($etudiant, $password));
            
            return response()->json([
                'success' => true,
                'data' => $etudiant,
                'message' => 'Étudiant créé avec succès.',
                'credentials' => [
                    'username' => $username,
                    'password' => $password
                ]
            ], 201);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la création de l\'étudiant.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Affiche les détails d'un étudiant spécifique.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        try {
            $etudiant = Etudiant::with(['classe', 'parent', 'notes', 'presences'])->findOrFail($id);
            
            return response()->json([
                'success' => true,
                'data' => $etudiant,
                'message' => 'Détails de l\'étudiant récupérés avec succès.'
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Étudiant non trouvé.',
                'error' => $e->getMessage()
            ], 404);
        }
    }

    /**
     * Met à jour les informations d'un étudiant spécifique.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        try {
            $etudiant = Etudiant::findOrFail($id);
            
            $validator = Validator::make($request->all(), [
                'prenom' => 'sometimes|required|string|max:50',
                'nom' => 'sometimes|required|string|max:50',
                'date_naissance' => 'sometimes|required|date',
                'genre' => 'sometimes|required|in:Masculin,Féminin',
                'lieu_naissance' => 'sometimes|required|string|max:100',
                'adresse' => 'sometimes|required|string|max:255',
                'telephone' => 'sometimes|required|string|max:20',
                'email' => 'sometimes|required|email|unique:etudiants,email,' . $id,
                'classe_id' => 'sometimes|required|exists:classes,id',
                'parent_id' => 'nullable|exists:parents,id',
                'statut' => 'sometimes|required|in:Actif,Inactif,Diplômé,Abandonné,Exclu',
                'date_inscription' => 'sometimes|required|date',
                'date_sortie' => 'nullable|date|after_or_equal:date_inscription',
                'motif_sortie' => 'nullable|required_with:date_sortie|string|max:255',
                'photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
                'groupe_sanguin' => 'nullable|string|max:10',
                'allergies' => 'nullable|string',
                'antecedents_medicaux' => 'nullable|string',
                'traitement_medical' => 'nullable|string',
                'nationalite' => 'sometimes|required|string|max:50',
                'contact_urgence_nom' => 'sometimes|required|string|max:100',
                'contact_urgence_telephone' => 'sometimes|required|string|max:20',
                'contact_urgence_lien' => 'sometimes|required|string|max:100',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Erreur de validation',
                    'errors' => $validator->errors()
                ], 422);
            }

            $data = $request->all();
            
            // Mise à jour de la photo si fournie
            if ($request->hasFile('photo')) {
                // Supprimer l'ancienne photo si elle existe
                if ($etudiant->photo) {
                    $oldPhotoPath = str_replace('/storage', 'public', $etudiant->photo);
                    if (Storage::exists($oldPhotoPath)) {
                        Storage::delete($oldPhotoPath);
                    }
                }
                
                $path = $request->file('photo')->store('public/etudiants/photos');
                $data['photo'] = Storage::url($path);
            }
            
            // Gestion de la date de sortie
            if (isset($data['date_sortie']) && $data['date_sortie']) {
                $data['date_sortie'] = Carbon::parse($data['date_sortie'])->format('Y-m-d');
            }
            
            $etudiant->update($data);
            
            return response()->json([
                'success' => true,
                'data' => $etudiant,
                'message' => 'Étudiant mis à jour avec succès.'
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la mise à jour de l\'étudiant.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Supprime un étudiant spécifique (soft delete).
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        try {
            $etudiant = Etudiant::findOrFail($id);
            
            // Vérifier s'il y a des dépendances avant de supprimer
            // Par exemple, vérifier s'il y a des notes ou des présences liées
            if ($etudiant->notes()->count() > 0 || $etudiant->presences()->count() > 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Impossible de supprimer cet étudiant car il a des enregistrements associés.',
                ], 422);
            }
            
            // Supprimer la photo si elle existe
            if ($etudiant->photo) {
                $photoPath = str_replace('/storage', 'public', $etudiant->photo);
                if (Storage::exists($photoPath)) {
                    Storage::delete($photoPath);
                }
            }
            
            $etudiant->delete();
            
            return response()->json([
                'success' => true,
                'message' => 'Étudiant supprimé avec succès.'
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la suppression de l\'étudiant.',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Restaure un étudiant supprimé (soft delete).
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function restore($id)
    {
        try {
            $etudiant = Etudiant::withTrashed()->findOrFail($id);
            
            if ($etudiant->trashed()) {
                $etudiant->restore();
                
                return response()->json([
                    'success' => true,
                    'message' => 'Étudiant restauré avec succès.',
                    'data' => $etudiant
                ]);
            }
            
            return response()->json([
                'success' => false,
                'message' => 'Cet étudiant n\'a pas été supprimé.'
            ], 400);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la restauration de l\'étudiant.',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Force la suppression d'un étudiant (suppression définitive).
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function forceDelete($id)
    {
        try {
            $etudiant = Etudiant::withTrashed()->findOrFail($id);
            
            // Supprimer définitivement l'étudiant et ses relations
            // Note: Assurez-vous d'avoir configuré les suppressions en cascade dans les modèles
            $etudiant->forceDelete();
            
            return response()->json([
                'success' => true,
                'message' => 'Étudiant supprimé définitivement avec succès.'
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la suppression définitive de l\'étudiant.',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Génère un nom d'utilisateur unique en ajoutant un numéro si nécessaire.
     *
     * @param  string  $username
     * @return string
     */
    private function generateUniqueUsername($username)
    {
        $originalUsername = $username;
        $count = 1;
        
        while (Etudiant::where('username', $username)->exists()) {
            $username = $originalUsername . $count;
            $count++;
        }
        
        return $username;
    }
    
    /**
     * Met à jour le mot de passe d'un étudiant.
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
            
            $etudiant = Etudiant::findOrFail($id);
            $etudiant->update([
                'password' => Hash::make($request->password)
            ]);
            
            // Envoyer un email de notification (à implémenter)
            // Mail::to($etudiant->email)->send(new MotDePasseModifie($etudiant));
            
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
    
    /**
     * Récupère les statistiques des étudiants.
     *
     * @return \Illuminate\Http\Response
     */
    public function statistics()
    {
        try {
            $total = Etudiant::count();
            $actifs = Etudiant::where('statut', 'Actif')->count();
            $inactifs = Etudiant::where('statut', 'Inactif')->count();
            $diplomes = Etudiant::where('statut', 'Diplômé')->count();
            $abandons = Etudiant::where('statut', 'Abandonné')->count();
            $exclus = Etudiant::where('statut', 'Exclu')->count();
            
            // Statistiques par genre
            $hommes = Etudiant::where('genre', 'Masculin')->count();
            $femmes = Etudiant::where('genre', 'Féminin')->count();
            
            // Statistiques par classe (top 5)
            $classes = Classe::withCount('etudiants')
                ->orderBy('etudiants_count', 'desc')
                ->take(5)
                ->get()
                ->map(function($classe) {
                    return [
                        'classe' => $classe->nom,
                        'nombre_etudiants' => $classe->etudiants_count
                    ];
                });
            
            return response()->json([
                'success' => true,
                'data' => [
                    'total' => $total,
                    'par_statut' => [
                        'actifs' => $actifs,
                        'inactifs' => $inactifs,
                        'diplomes' => $diplomes,
                        'abandons' => $abandons,
                        'exclus' => $exclus,
                    ],
                    'par_genre' => [
                        'hommes' => $hommes,
                        'femmes' => $femmes,
                    ],
                    'classes_plus_remplies' => $classes,
                ],
                'message' => 'Statistiques des étudiants récupérées avec succès.'
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
     * Exporte la liste des étudiants au format Excel.
     *
     * @return \Illuminate\Http\Response
     */
    public function export()
    {
        try {
            // À implémenter avec Maatwebsite/Excel ou similaire
            // $export = new EtudiantsExport();
            // return Excel::download($export, 'etudiants-' . now()->format('Y-m-d') . '.xlsx');
            
            return response()->json([
                'success' => false,
                'message' => 'Fonctionnalité d\'exportation non implémentée.'
            ], 501);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de l\'exportation des données.',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
