<?php

use App\Http\Controllers\Web\AbonnementController;
use App\Http\Controllers\Web\AnneeScolaireController;
use App\Http\Controllers\Web\AnnonceController;
use App\Http\Controllers\Web\Auth\LoginController;
use App\Http\Controllers\Web\BulletinController;
use App\Http\Controllers\Web\ClasseController;
use App\Http\Controllers\Web\DashboardController;
use App\Http\Controllers\Web\EleveController;
use App\Http\Controllers\Web\EmploiDuTempsController;
use App\Http\Controllers\Web\EnseignantController;
use App\Http\Controllers\Web\EspaceEleveController;
use App\Http\Controllers\Web\EspaceParentController;
use App\Http\Controllers\Web\InscriptionController;
use App\Http\Controllers\Web\MatiereController;
use App\Http\Controllers\Web\NoteController;
use App\Http\Controllers\Web\NotificationController;
use App\Http\Controllers\Web\PaiementController;
use App\Http\Controllers\Web\PresenceController;
use App\Http\Controllers\Web\ProfileController;
use App\Http\Controllers\Web\SuperAdmin\DashboardController as SuperAdminDashboardController;
use App\Http\Controllers\Web\SuperAdmin\EcoleController as SuperAdminEcoleController;
use App\Http\Controllers\Web\SuperAdmin\FactureController as SuperAdminFactureController;
use App\Http\Controllers\Web\TrimestreController;
use App\Http\Controllers\Web\TuteurController;
use App\Http\Controllers\Web\UserAccountController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'show'])->name('login');
    Route::post('/login', [LoginController::class, 'login']);

    Route::get('/inscription', [InscriptionController::class, 'show'])->name('inscription.show');
    Route::post('/inscription', [InscriptionController::class, 'store'])->name('inscription.store');
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

    Route::get('/', fn () => redirect()->route('dashboard'));
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Profil personnel (toutes les sessions authentifiées)
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::put('/profile/password', [ProfileController::class, 'updatePassword'])->name('profile.password');

    // Abonnement de l'école (accessible même hors Premium pour pouvoir s'abonner)
    Route::middleware('role:admin')->prefix('abonnement')->name('abonnement.')->group(function () {
        Route::get('/', [AbonnementController::class, 'index'])->name('index');
        Route::post('/souscrire', [AbonnementController::class, 'souscrire'])->name('souscrire');
    });

    Route::get('/emploi-du-temps/{classe?}', [EmploiDuTempsController::class, 'index'])->name('emploi-du-temps.index');

    // Fonctionnalités Premium : notifications, annonces, bulletins, paiements,
    // espaces self-service élève/parent, gestion des comptes.
    Route::middleware('premium')->group(function () {
        Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
        Route::patch('/notifications/{notification}/lue', [NotificationController::class, 'marquerLue'])->name('notifications.lue');
        Route::patch('/notifications/lues', [NotificationController::class, 'marquerToutesLues'])->name('notifications.toutes-lues');

        Route::get('/annonces', [AnnonceController::class, 'index'])->name('annonces.index');

        Route::middleware('role:eleve')->prefix('mon-espace')->name('mon-espace.')->group(function () {
            Route::get('/notes', [EspaceEleveController::class, 'notes'])->name('notes');
            Route::get('/presences', [EspaceEleveController::class, 'presences'])->name('presences');
            Route::get('/paiements', [EspaceEleveController::class, 'paiements'])->name('paiements');
        });

        Route::middleware('role:parent')->prefix('mes-enfants')->name('mes-enfants.')->group(function () {
            Route::get('/', [EspaceParentController::class, 'enfants'])->name('index');
            Route::get('/{eleve}', [EspaceParentController::class, 'enfant'])->name('show');
        });

        Route::get('/bulletins', [BulletinController::class, 'index'])->name('bulletins.index')->middleware('role:admin,enseignant');
        Route::post('/bulletins/classes/{classe}/trimestres/{trimestre}', [BulletinController::class, 'genererClasse'])->name('bulletins.generer')->middleware('role:admin,enseignant');
        Route::get('/bulletins/eleves/{eleve}/trimestres/{trimestre}', [BulletinController::class, 'show'])->name('bulletins.show');

        Route::middleware('role:admin,enseignant')->group(function () {
            Route::post('/annonces', [AnnonceController::class, 'store'])->name('annonces.store');
            Route::get('/annonces/create', [AnnonceController::class, 'create'])->name('annonces.create');
            Route::delete('/annonces/{annonce}', [AnnonceController::class, 'destroy'])->name('annonces.destroy');

            // Rapports PDF par section (fonctionnalité Premium)
            Route::get('/classes/rapport', [ClasseController::class, 'rapport'])->name('classes.rapport');
            Route::get('/eleves/rapport', [EleveController::class, 'rapport'])->name('eleves.rapport');
            Route::get('/enseignants/rapport', [EnseignantController::class, 'rapport'])->name('enseignants.rapport');
            Route::get('/matieres/rapport', [MatiereController::class, 'rapport'])->name('matieres.rapport');
            Route::get('/tuteurs/rapport', [TuteurController::class, 'rapport'])->name('tuteurs.rapport');
            Route::get('/notes/rapport', [NoteController::class, 'rapport'])->name('notes.rapport');
            Route::get('/presences/rapport', [PresenceController::class, 'rapport'])->name('presences.rapport');
        });

        Route::middleware('role:admin')->group(function () {
            Route::resource('paiements', PaiementController::class)->except('show');
            Route::get('/paiements/rapport', [PaiementController::class, 'rapport'])->name('paiements.rapport');

            Route::get('/comptes', [UserAccountController::class, 'index'])->name('comptes.index');
            Route::post('/comptes/generer', [UserAccountController::class, 'generer'])->name('comptes.generer');
            Route::post('/comptes/{user}/reinitialiser-mot-de-passe', [UserAccountController::class, 'reinitialiserMotDePasse'])->name('comptes.reinitialiser');
            Route::delete('/comptes/{user}', [UserAccountController::class, 'destroy'])->name('comptes.destroy');
        });
    });

    // Gestion des notes et présences (cœur gratuit) : admin + enseignant
    Route::middleware('role:admin,enseignant')->group(function () {
        Route::get('/notes/bulk', [NoteController::class, 'bulk'])->name('notes.bulk');
        Route::post('/notes/bulk', [NoteController::class, 'bulkStore'])->name('notes.bulk.store');
        Route::get('/notes/reports', [NoteController::class, 'reports'])->name('notes.reports');
        Route::get('/notes/classes/{classe}/eleves', [NoteController::class, 'elevesPourSaisie'])->name('notes.eleves');
        Route::resource('notes', NoteController::class)->except('show');

        Route::get('/presences/bulk', [PresenceController::class, 'bulk'])->name('presences.bulk');
        Route::post('/presences/bulk', [PresenceController::class, 'bulkStore'])->name('presences.bulk.store');
        Route::get('/presences/classes/{classe}/eleves', [PresenceController::class, 'elevesPourAppel'])->name('presences.eleves');
        Route::resource('presences', PresenceController::class)->except('show');
    });

    // Administration complète (cœur gratuit) : admin uniquement
    // (déclarée avant le bloc de consultation ci-dessous : les routes statiques
    // /classes/create, /eleves/{eleve}/edit, etc. doivent être enregistrées
    // avant la route générique /classes/{classe} pour ne pas être interceptées)
    Route::middleware('role:admin')->group(function () {
        Route::resource('eleves', EleveController::class)->except(['index', 'show'])->parameters(['eleves' => 'eleve']);
        Route::resource('enseignants', EnseignantController::class);
        Route::resource('classes', ClasseController::class)->except(['index', 'show'])->parameters(['classes' => 'classe']);
        Route::resource('matieres', MatiereController::class);
        Route::resource('tuteurs', TuteurController::class);

        Route::resource('annees-scolaires', AnneeScolaireController::class)->only(['index', 'store', 'update', 'destroy'])->parameters(['annees-scolaires' => 'anneeScolaire']);
        Route::resource('trimestres', TrimestreController::class)->only(['index', 'store', 'update', 'destroy']);

        Route::post('/emploi-du-temps', [EmploiDuTempsController::class, 'store'])->name('emploi-du-temps.store');
        Route::delete('/emploi-du-temps/{creneauHoraire}', [EmploiDuTempsController::class, 'destroy'])->name('emploi-du-temps.destroy');
    });

    // Consultation des fiches élèves/classes (cœur gratuit) : admin + enseignant
    Route::middleware('role:admin,enseignant')->group(function () {
        Route::resource('eleves', EleveController::class)->only(['index', 'show'])->parameters(['eleves' => 'eleve']);
        Route::get('/classes/{classe}', [ClasseController::class, 'show'])->name('classes.show');
        Route::get('/classes', [ClasseController::class, 'index'])->name('classes.index');
    });

    // Back-office plateforme : super-admin uniquement
    Route::middleware('role:super_admin')->prefix('super-admin')->name('super-admin.')->group(function () {
        Route::get('/', [SuperAdminDashboardController::class, 'index'])->name('dashboard');

        Route::get('/ecoles', [SuperAdminEcoleController::class, 'index'])->name('ecoles.index');
        Route::get('/ecoles/{ecole}', [SuperAdminEcoleController::class, 'show'])->name('ecoles.show');
        Route::patch('/ecoles/{ecole}/suspendre', [SuperAdminEcoleController::class, 'suspendre'])->name('ecoles.suspendre');
        Route::patch('/ecoles/{ecole}/activer', [SuperAdminEcoleController::class, 'activer'])->name('ecoles.activer');

        Route::get('/factures', [SuperAdminFactureController::class, 'index'])->name('factures.index');
        Route::post('/factures/{facture}/confirmer', [SuperAdminFactureController::class, 'confirmer'])->name('factures.confirmer');
    });
});
