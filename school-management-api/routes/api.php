<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ClasseController;
use App\Http\Controllers\Api\EleveController;
use App\Http\Controllers\Api\EnseignantController;
use App\Http\Controllers\Api\MatiereController;
use App\Http\Controllers\Api\PresenceController;
use App\Http\Controllers\Api\NoteController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Web\ProgressController;

/*
|--------------------------------------------------------------------------
| ROUTES PUBLIQUES (sans authentification)
|--------------------------------------------------------------------------
*/

// Route de test
Route::get('/test', function () {
    return response()->json([
        'success' => true,
        'message' => 'API fonctionne correctement !',
        'timestamp' => now()
    ]);
});

// Route de test simple
Route::get('/ping', function () {
    return response()->json(['pong' => true]);
});

// Route pour l'activité récente du dashboard
Route::get('/dashboard/recent-activity', function () {
    return response()->json([
        [
            'id' => 1,
            'message' => 'Nouvel élève inscrit en 6ème A',
            'time' => 'Il y a 5 minutes',
            'type' => 'student'
        ],
        [
            'id' => 2,
            'message' => 'Note ajoutée en Mathématiques',
            'time' => 'Il y a 1 heure',
            'type' => 'grade'
        ],
        [
            'id' => 3,
            'message' => 'Présences marquées pour 3ème B',
            'time' => 'Il y a 2 heures',
            'type' => 'attendance'
        ],
        [
            'id' => 4,
            'message' => 'Nouveau professeur de Physique',
            'time' => 'Il y a 3 heures',
            'type' => 'teacher'
        ]
    ]);
})->name('api.dashboard.recent-activity');

// Routes d'authentification
Route::prefix('auth')->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);
    
    // Routes protégées
    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::get('/me', [AuthController::class, 'me']);
    });
});

/*
|--------------------------------------------------------------------------
| ROUTES PROTÉGÉES (authentification requise)
|--------------------------------------------------------------------------
*/

Route::middleware('auth:sanctum')->group(function () {
    
    // ===== AUTHENTIFICATION =====
    Route::get('/user', [AuthController::class, 'user']);
    Route::post('/logout', [AuthController::class, 'logout']);

});

// ===== CLASSES =====
Route::prefix('classes')->group(function () {
    Route::get('/', [ClasseController::class, 'index']);
    Route::post('/', [ClasseController::class, 'store']);
    Route::get('/{id}', [ClasseController::class, 'show']);
    Route::put('/{id}', [ClasseController::class, 'update']);
    Route::delete('/{id}', [ClasseController::class, 'destroy']);
    
    // Routes spéciales pour les classes (publiques pour le frontend)
    Route::get('/{id}/presences', [PresenceController::class, 'getByClasse']);
    Route::get('/{id}/notes', [NoteController::class, 'getByClasse']);
    Route::get('/{id}/eleves', [ClasseController::class, 'getEleves']);
});

// ===== ÉLÈVES =====
Route::prefix('eleves')->group(function () {
    Route::get('/', [EleveController::class, 'index']);
    Route::post('/', [EleveController::class, 'store']);
    Route::get('/{id}', [EleveController::class, 'show']);
    Route::put('/{id}', [EleveController::class, 'update']);
    Route::delete('/{id}', [EleveController::class, 'destroy']);
    
    // Routes spéciales pour les élèves
    Route::get('/{id}/presences', [PresenceController::class, 'getByEleve']);
    Route::get('/{id}/notes', [NoteController::class, 'getByEleve']);
});

// ===== ENSEIGNANTS =====
Route::prefix('enseignants')->group(function () {
    Route::get('/', [EnseignantController::class, 'index']);
    Route::post('/', [EnseignantController::class, 'store']);
    Route::get('/{id}', [EnseignantController::class, 'show']);
    Route::put('/{id}', [EnseignantController::class, 'update']);
    Route::delete('/{id}', [EnseignantController::class, 'destroy']);
});

// ===== MATIÈRES =====
Route::prefix('matieres')->group(function () {
    Route::get('/', [MatiereController::class, 'index']);
    Route::post('/', [MatiereController::class, 'store']);
    Route::get('/{id}', [MatiereController::class, 'show']);
    Route::put('/{id}', [MatiereController::class, 'update']);
    Route::delete('/{id}', [MatiereController::class, 'destroy']);
});

// ===== PRÉSENCES =====
Route::prefix('presences')->group(function () {
    Route::get('/', [PresenceController::class, 'index']);
    Route::post('/', [PresenceController::class, 'store']);
    Route::post('/bulk', [PresenceController::class, 'storeBulk']); // Marquage en masse
    Route::get('/{id}', [PresenceController::class, 'show']);
    Route::put('/{id}', [PresenceController::class, 'update']);
    Route::delete('/{id}', [PresenceController::class, 'destroy']);
});

// ===== NOTES =====
Route::prefix('notes')->group(function () {
    Route::get('/', [NoteController::class, 'index']);
    Route::post('/', [NoteController::class, 'store']);
    Route::post('/bulk', [NoteController::class, 'storeBulk']); // Ajout en masse
    Route::get('/statistiques', [NoteController::class, 'statistiques']);
    Route::get('/{id}', [NoteController::class, 'show']);
    Route::put('/{id}', [NoteController::class, 'update']);
    Route::delete('/{id}', [NoteController::class, 'destroy']);
});

// ===== UTILISATEURS =====
Route::prefix('users')->group(function () {
    Route::get('/', [UserController::class, 'index']);
    Route::post('/', [UserController::class, 'store']);
    Route::put('/{id}', [UserController::class, 'update']);
    Route::delete('/{id}', [UserController::class, 'destroy']);
});

// ===== PROGRÈS =====
Route::get('/progress/{eleve}', [ProgressController::class, 'getProgressData'])->middleware('auth:sanctum');