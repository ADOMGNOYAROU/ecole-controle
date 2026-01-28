<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Web\DashboardController;
use App\Http\Controllers\Web\ClasseController;
use App\Http\Controllers\Web\EleveController;
use App\Http\Controllers\Web\EnseignantController;
use App\Http\Controllers\Web\PresenceController;
use App\Http\Controllers\Web\NoteController;
use App\Http\Controllers\Web\SettingsController;
use App\Http\Controllers\Web\MatiereController;
use App\Http\Controllers\Web\ParentController;
use App\Http\Controllers\Web\ProgressController;
use App\Http\Controllers\Web\ProfTitulaireController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

// Authentication routes
use App\Http\Controllers\Web\SimpleLoginController;
Route::middleware('guest')->group(function () {
    Route::get('/login', [SimpleLoginController::class, 'show'])->name('login');
    Route::post('/login', [SimpleLoginController::class, 'login']);
});

Route::post('/logout', [SimpleLoginController::class, 'logout'])->name('logout');

Route::middleware(['auth', 'verified'])->group(function () {
    // Dashboard principal (accessible à tous les rôles)
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    
    // Dashboard spécifique pour les élèves
    Route::get('/dashboard-eleve', [DashboardController::class, 'eleveDashboard'])->name('dashboard.eleve');
    
    // Dashboard spécifique pour les parents
    Route::get('/dashboard-parent', [DashboardController::class, 'parentDashboard'])->name('dashboard.parent');
    
    // Routes pour les enseignants, profs titulaires et admins
    Route::middleware(['role:enseignant,prof_titulaire,admin'])->group(function () {
        Route::get('/dashboard-enseignant', function () {
            return view('dashboard_enseignant');
        })->name('dashboard.enseignant');
        Route::get('/dashboard-modern', [DashboardController::class, 'modern'])->name('dashboard.modern');
        
        // Routes de gestion des comptes (admin uniquement)
        Route::middleware(['role:admin'])->group(function () {
            Route::get('/accounts', [App\Http\Controllers\Web\UserAccountController::class, 'index'])->name('accounts.index');
            Route::post('/accounts/create', [App\Http\Controllers\Web\UserAccountController::class, 'createManual'])->name('accounts.create.manual');
            Route::post('/accounts/generate', [App\Http\Controllers\Web\UserAccountController::class, 'generateAccounts'])->name('accounts.generate');
            Route::post('/accounts/reset-password/{userId}', [App\Http\Controllers\Web\UserAccountController::class, 'resetPassword'])->name('accounts.reset-password');
            Route::get('/accounts/export', [App\Http\Controllers\Web\UserAccountController::class, 'exportAccounts'])->name('accounts.export');
        });
        
        // Classes
        Route::resource('classes', ClasseController::class);
        Route::get('/classes/{classe}/students', [ClasseController::class, 'students'])->name('classes.students');
        
        // Students
        Route::resource('eleves', EleveController::class);
        Route::get('/eleves/{eleve}/grades', [EleveController::class, 'grades'])->name('eleves.grades');
        Route::get('/eleves/{eleve}/attendances', [EleveController::class, 'attendances'])->name('eleves.attendances');
        
        // Teachers
        Route::resource('enseignants', EnseignantController::class);
        Route::get('/enseignants/{enseignant}/classes', [EnseignantController::class, 'classes'])->name('enseignants.classes');
        Route::get('/enseignants/{enseignant}/schedule', [EnseignantController::class, 'schedule'])->name('enseignants.schedule');
        
        // Attendance
        Route::resource('presences', PresenceController::class);
        Route::get('/presences/bulk', [PresenceController::class, 'bulk'])->name('presences.bulk');
        Route::post('/presences/bulk', [PresenceController::class, 'bulkStore'])->name('presences.bulk.store');
        
        // Grades
        Route::resource('notes', NoteController::class);
        Route::get('/notes/bulk', [NoteController::class, 'bulk'])->name('notes.bulk');
        Route::post('/notes/bulk', [NoteController::class, 'bulkStore'])->name('notes.bulk.store');
        Route::get('/notes/reports', [NoteController::class, 'reports'])->name('notes.reports');
        
        // Matières (Subjects)
        Route::resource('matieres', MatiereController::class);
        
        // Parents
        Route::resource('parents', ParentController::class);
        
        // Profile and Settings
        Route::get('/profile', function () {
            return view('profile');
        })->name('profile');
        
        Route::get('/settings', [SettingsController::class, 'index'])->name('settings');
        Route::post('/settings', [SettingsController::class, 'store'])->name('settings.store');
    });
    
    // Classes
    Route::resource('classes', ClasseController::class);
    Route::get('/classes/{classe}/students', [ClasseController::class, 'students'])->name('classes.students');
    
    // Students
    Route::resource('eleves', EleveController::class);
    Route::get('/eleves/{eleve}/grades', [EleveController::class, 'grades'])->name('eleves.grades');
    Route::get('/eleves/{eleve}/attendances', [EleveController::class, 'attendances'])->name('eleves.attendances');
    
    // Teachers
    Route::resource('enseignants', EnseignantController::class);
    Route::get('/enseignants/{enseignant}/classes', [EnseignantController::class, 'classes'])->name('enseignants.classes');
    Route::get('/enseignants/{enseignant}/schedule', [EnseignantController::class, 'schedule'])->name('enseignants.schedule');
    
    // Attendance
    Route::resource('presences', PresenceController::class);
    Route::get('/presences/bulk', [PresenceController::class, 'bulk'])->name('presences.bulk');
    Route::post('/presences/bulk', [PresenceController::class, 'bulkStore'])->name('presences.bulk.store');
    
    // Grades
    Route::resource('notes', NoteController::class);
    Route::get('/notes/bulk', [NoteController::class, 'bulk'])->name('notes.bulk');
    Route::post('/notes/bulk', [NoteController::class, 'bulkStore'])->name('notes.bulk.store');
    Route::get('/notes/reports', [NoteController::class, 'reports'])->name('notes.reports');
    
    // Matières (Subjects)
    Route::resource('matieres', MatiereController::class);
    
    // Parents
    Route::resource('parents', ParentController::class);
    
    // Routes pour la gestion des professeurs titulaires
    Route::get('/prof-titulaires', [ProfTitulaireController::class, 'index'])->name('prof-titulaire.index')->middleware('role:admin');
    Route::post('/prof-titulaires/assign', [ProfTitulaireController::class, 'assign'])->name('prof-titulaire.assign')->middleware('role:admin');
    Route::delete('/prof-titulaires/remove/{classe}', [ProfTitulaireController::class, 'remove'])->name('prof-titulaire.remove')->middleware('role:admin');
    
    // Routes pour le suivi des progrès
    Route::get('/mes-progres', [ProgressController::class, 'showStudentProgress'])->name('progress.student');
    Route::get('/mes-progres/{eleve}', [ProgressController::class, 'showStudentProgress'])->name('progress.student.detail');
    Route::get('/suivi-classe/{classe}', [ProgressController::class, 'showClassProgress'])->name('progress.class')->middleware('role:prof_titulaire');
    Route::get('/selection-eleve', [ProgressController::class, 'selectStudent'])->name('progress.select-student')->middleware('role:prof_titulaire');
    
    // API pour les données de progression (auth web)
    Route::get('/api/progress/{eleve}', [ProgressController::class, 'getProgressData']);
    
    // Profile and Settings
    Route::get('/profile', function () {
        return view('profile');
    })->name('profile');
    
    Route::get('/settings', [SettingsController::class, 'index'])->name('settings');
    Route::post('/settings', [SettingsController::class, 'store'])->name('settings.store');
    
    // Default redirect to dashboard
    Route::get('/', function () {
        return redirect()->route('dashboard');
    });
});
