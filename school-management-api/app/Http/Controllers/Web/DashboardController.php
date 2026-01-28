<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Classe;
use App\Models\Eleve;
use App\Models\Enseignant;
use App\Models\Presence;
use Illuminate\Support\Facades\Log;

class DashboardController extends Controller
{
    /**
     * Afficher le tableau de bord moderne.
     *
     * @return \Illuminate\View\View
     */
    public function modern()
    {
        try {
            $stats = [
                'classes' => Classe::count(),
                'eleves' => Eleve::count(),
                'enseignants' => Enseignant::count(),
                'presences' => Presence::whereDate('date', today())->count(),
            ];

            // Données pour le graphique de tendance des présences
            $attendanceData = [95, 92, 88, 94, 92];

            return view('dashboard_modern', compact('stats', 'attendanceData'));
            
        } catch (\Exception $e) {
            Log::error('Dashboard Modern Error: ' . $e->getMessage());
            
            $stats = [
                'classes' => 0,
                'eleves' => 0,
                'enseignants' => 0,
                'presences' => 0,
            ];
            
            $attendanceData = [0, 0, 0, 0, 0];
            
            return view('dashboard_modern', compact('stats', 'attendanceData'));
        }
    }

    /**
     * Afficher le tableau de bord.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        try {
            $user = auth()->user();
            
            // Debug : afficher le rôle détecté
            Log::info('Dashboard - User role: ' . $user->role);
            Log::info('Dashboard - User email: ' . $user->email);
            
            // Rediriger selon le rôle
            if ($user->role === 'eleve') {
                Log::info('Dashboard - Redirecting to eleve dashboard');
                return $this->eleveDashboard();
            } elseif ($user->role === 'parent') {
                Log::info('Dashboard - Redirecting to parent dashboard');
                return $this->parentDashboard();
            } elseif ($user->role === 'enseignant' || $user->role === 'prof_titulaire') {
                Log::info('Dashboard - Redirecting to enseignant dashboard');
                return $this->enseignantDashboard();
            } elseif ($user->role === 'admin') {
                Log::info('Dashboard - Redirecting to admin dashboard');
                return $this->adminDashboard();
            } else {
                Log::info('Dashboard - Unknown role, using admin dashboard');
                return $this->adminDashboard();
            }
            
        } catch (\Exception $e) {
            Log::error('Dashboard Error: ' . $e->getMessage());
            
            $stats = [
                'classes' => 0,
                'eleves' => 0,
                'enseignants' => 0,
                'presences' => 0,
            ];
            
            $attendanceData = [0, 0, 0, 0, 0];
            
            return view('dashboard', compact('stats', 'attendanceData'));
        }
    }
    
    /**
     * Dashboard pour les élèves
     */
    public function eleveDashboard()
    {
        $user = auth()->user();
        
        // Chercher l'élève correspondant à cet utilisateur par email
        $eleve = \App\Models\Eleve::where('email', $user->email)->first();
        
        if (!$eleve) {
            Log::warning('Aucun élève trouvé pour l\'utilisateur', ['user_id' => $user->id, 'email' => $user->email]);
            return view('dashboard_eleve', [
                'error' => 'Aucune information élève trouvée. Veuillez contacter l\'administration.',
                'stats' => ['classes' => 0, 'eleves' => 0, 'enseignants' => 0, 'presences' => 0],
                'attendanceData' => [0, 0, 0, 0, 0]
            ]);
        }
        
        // Statistiques de l'élève
        $stats = [
            'classes' => 1,
            'eleves' => 1,
            'enseignants' => $eleve->classe ? $eleve->classe->enseignants->count() : 0,
            'presences' => $eleve->presences()->whereDate('date', today())->count(),
        ];
        
        // Données de présence de l'élève
        $attendanceData = $eleve->presences()
            ->orderBy('date', 'desc')
            ->take(5)
            ->pluck('statut')
            ->map(function($statut) {
                return $statut === 'present' ? 1 : 0;
            })
            ->toArray();
            
        if (empty($attendanceData)) {
            $attendanceData = [0, 0, 0, 0, 0];
        }
        
        Log::info('Dashboard élève affiché', ['eleve_id' => $eleve->id, 'user_id' => $user->id]);
        
        return view('dashboard_eleve', compact('stats', 'attendanceData', 'eleve'));
    }
    
    /**
     * Dashboard pour les parents
     */
    public function parentDashboard()
    {
        $user = auth()->user();
        
        // Chercher le parent correspondant par email (pas de relation user_id)
        $parent = \App\Models\ParentModel::where('email', $user->email)->first();
        
        // Statistiques du parent
        $stats = [
            'classes' => $parent && $parent->eleves ? $parent->eleves->pluck('classe_id')->unique()->count() : 0,
            'eleves' => $parent && $parent->eleves ? $parent->eleves->count() : 0,
            'enseignants' => 0,
            'presences' => 0,
        ];
        
        $attendanceData = [0, 0, 0, 0, 0];
        
        Log::info('Dashboard parent affiché', ['parent_id' => $parent?->id, 'user_id' => $user->id]);
        
        return view('dashboard_parent', compact('stats', 'attendanceData', 'parent'));
    }
    
    /**
     * Dashboard pour les enseignants
     */
    private function enseignantDashboard()
    {
        $enseignant = auth()->user()->enseignant;
        
        $stats = [
            'classes' => $enseignant ? $enseignant->classes->count() : 0,
            'eleves' => $enseignant ? $enseignant->classes->sum(function($classe) {
                return $classe->eleves->count();
            }) : 0,
            'enseignants' => 1,
            'presences' => 0,
        ];
        
        $attendanceData = [95, 92, 88, 94, 92];
        
        return view('dashboard_enseignant', compact('stats', 'attendanceData', 'enseignant'));
    }
    
    /**
     * Dashboard pour les administrateurs
     */
    private function adminDashboard()
    {
        $stats = [
            'classes' => Classe::count(),
            'eleves' => Eleve::count(),
            'enseignants' => Enseignant::count(),
            'presences' => Presence::whereDate('date', today())->count(),
        ];
        
        $attendanceData = [95, 92, 88, 94, 92];
        
        return view('dashboard', compact('stats', 'attendanceData'));
    }
}
