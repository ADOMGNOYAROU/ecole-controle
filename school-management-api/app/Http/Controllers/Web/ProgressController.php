<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Note;
use App\Models\Eleve;
use App\Models\Matiere;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ProgressController extends Controller
{
    /**
     * Afficher le suivi des progrès pour un élève
     */
    public function showStudentProgress($eleveId = null)
    {
        $user = auth()->user();
        
        // Débogage
        \Log::info('showStudentProgress - User role: ' . $user->role);
        \Log::info('showStudentProgress - Eleve ID: ' . $eleveId);
        
        // Si aucun ID n'est fourni, rediriger vers la sélection
        if ($eleveId === null) {
            return redirect()->route('progress.select-student');
        }

        // Récupérer l'élève
        $eleve = Eleve::with('classe')->findOrFail($eleveId);
        
        // Vérifier les permissions pour les professeurs titulaires
        if ($user->isProfTitulaire()) {
            // Récupérer l'enseignant connecté
            $enseignant = \App\Models\Enseignant::where('user_id', $user->id)->first();
            // Vérifier si le professeur est titulaire de la classe de l'élève
            if ($eleve->classe->enseignant_id != $enseignant->id) {
                return redirect()->route('dashboard')->with('error', 'Vous n\'êtes pas autorisé à voir les progrès de cet élève.');
            }
        } else {
            // Pour les autres rôles, refuser l'accès
            return redirect()->route('dashboard')->with('error', 'Accès non autorisé.');
        }
        $matieres = Matiere::all();
        
        $data = [];
        foreach ($matieres as $matiere) {
            $notes = Note::where('eleve_id', $eleveId)
                        ->where('matiere_id', $matiere->id)
                        ->orderBy('date_evaluation')
                        ->get();
            
            if ($notes->isNotEmpty()) {
                $data[] = [
                    'matiere' => $matiere->nom,
                    'notes' => $notes->map(function($note) {
                        return [
                            'date' => $note->date_evaluation->format('d/m/Y'),
                            'valeur' => $note->note,
                            'type' => $note->type_evaluation
                        ];
                    })
                ];
            }
        }

        // Debug: Afficher les données
        \Log::info('Progress data for student ' . $eleveId . ': ' . json_encode($data));
        
        return view('progress.student', compact('eleve', 'data'));
    }

    /**
     * Afficher le suivi des progrès pour une classe (vue enseignant)
     */
    public function showClassProgress($classeId)
    {
        $eleves = Eleve::where('classe_id', $classeId)->get();
        $matieres = Matiere::all();
        $progressData = [];

        foreach ($eleves as $eleve) {
            $eleveData = [
                'eleve' => $eleve->nom . ' ' . $eleve->prenom,
                'matieres' => []
            ];

            foreach ($matieres as $matiere) {
                $notes = Note::where('eleve_id', $eleve->id)
                            ->where('matiere_id', $matiere->id)
                            ->orderBy('date_evaluation')
                            ->get();

                if ($notes->isNotEmpty()) {
                    $eleveData['matieres'][$matiere->nom] = $notes->map(function($note) {
                        return [
                            'date' => $note->date_evaluation->format('d/m/Y'),
                            'valeur' => $note->note,
                            'type' => $note->type_evaluation
                        ];
                    });
                }
            }

            $progressData[] = $eleveData;
        }

        return view('progress.class', [
            'progressData' => $progressData,
            'matieres' => $matieres
        ]);
    }

    /**
     * Obtenir les données pour le graphique des progrès (API)
     */
    public function getProgressData($eleveId, $matiereId = null)
    {
        $query = Note::where('eleve_id', $eleveId)
                    ->with('matiere')
                    ->orderBy('date_evaluation');

        if ($matiereId) {
            $query->where('matiere_id', $matiereId);
        }

        $notes = $query->get()
            ->groupBy(function($note) {
                return $note->matiere->nom;
            })
            ->map(function($notes) {
                return $notes->map(function($note) {
                    return [
                        'x' => $note->date_evaluation->format('Y-m-d'),
                        'y' => (float)$note->note,
                        'type' => $note->type_evaluation
                    ];
                });
            });

        return response()->json($notes);
    }

    /**
     * Afficher la page de sélection d'élève pour les enseignants
     */
    public function selectStudent()
    {
        $user = auth()->user();
        
        // Débogage
        \Log::info('selectStudent - User role: ' . $user->role);
        \Log::info('selectStudent - isProfTitulaire: ' . ($user->isProfTitulaire() ? 'true' : 'false'));
        \Log::info('selectStudent - User email: ' . $user->email);
        
        if (!$user->isProfTitulaire()) {
            \Log::error('selectStudent - Accès refusé pour le rôle: ' . $user->role);
            return redirect()->route('dashboard')->with('error', 'Accès non autorisé. Cette fonctionnalité est réservée aux professeurs titulaires de classe. Votre rôle: ' . $user->role);
        }

        // Récupérer l'enseignant connecté
        $enseignant = \App\Models\Enseignant::where('user_id', $user->id)->first();
        
        if (!$enseignant) {
            return redirect()->route('dashboard.enseignant')->with('error', 'Profil enseignant non trouvé.');
        }

        // Récupérer les classes où l'enseignant est titulaire
        $classes = \App\Models\Classe::where('enseignant_id', $enseignant->id)->get();
        
        // Récupérer tous les élèves de ces classes
        $eleves = Eleve::whereIn('classe_id', $classes->pluck('id'))
                        ->with('classe')
                        ->orderBy('nom')
                        ->orderBy('prenom')
                        ->get();

        return view('progress.select-student', compact('eleves', 'classes'));
    }
}
