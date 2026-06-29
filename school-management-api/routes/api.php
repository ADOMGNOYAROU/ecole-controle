<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ClasseController;
use App\Http\Controllers\Api\EleveController;
use App\Http\Controllers\Api\EnseignantController;
use App\Http\Controllers\Api\MatiereController;
use App\Http\Controllers\Api\NoteController;
use App\Http\Controllers\Api\PresenceController;
use Illuminate\Support\Facades\Route;

Route::post('/auth/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->name('api.')->group(function () {
    Route::post('/auth/logout', [AuthController::class, 'logout'])->name('auth.logout');
    Route::get('/auth/me', [AuthController::class, 'me'])->name('auth.me');

    Route::apiResource('classes', ClasseController::class)->parameters(['classes' => 'classe']);
    Route::get('/classes/{classe}/eleves', [ClasseController::class, 'eleves'])->name('classes.eleves');

    Route::apiResource('eleves', EleveController::class)->parameters(['eleves' => 'eleve']);
    Route::apiResource('enseignants', EnseignantController::class);
    Route::apiResource('matieres', MatiereController::class);
    Route::apiResource('notes', NoteController::class);
    Route::apiResource('presences', PresenceController::class);
});
