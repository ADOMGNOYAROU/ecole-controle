<?php

namespace Tests\Feature\EndToEnd;

use App\Models\AnneeScolaire;
use App\Models\Bulletin;
use App\Models\Classe;
use App\Models\Ecole;
use App\Models\Eleve;
use App\Models\Enseignant;
use App\Models\Matiere;
use App\Models\Note;
use App\Models\Notification;
use App\Models\Presence;
use App\Models\Trimestre;
use App\Models\Tuteur;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Parcours complet d'une école, uniquement par les pages web :
 * inscription → paramétrage → comptes → notes/présences → bulletins
 * → espaces parent/élève → annonces, messagerie, paiements et rappels.
 */
class ParcoursEcoleCompletTest extends TestCase
{
    use RefreshDatabase;

    public function test_parcours_complet_d_une_ecole(): void
    {
        // 1. Inscription de l'école : l'admin est connecté et l'essai Premium démarre
        $this->post('/inscription', [
            'nom_ecole' => 'Collège Espoir',
            'ville' => 'Lomé',
            'admin_nom' => 'Directeur Espoir',
            'admin_email' => 'directeur@espoir.tg',
            'admin_password' => 'motdepasse123',
            'admin_password_confirmation' => 'motdepasse123',
        ])->assertRedirect();

        $admin = User::where('email', 'directeur@espoir.tg')->firstOrFail();
        $ecole = $admin->ecole;
        $this->assertAuthenticatedAs($admin);
        $this->assertTrue($ecole->aAccesPremium());

        // 2. Paramétrage : année, trimestre, matière, classe, enseignant, élève, tuteur
        $this->actingAs($admin)->post('/annees-scolaires', [
            'libelle' => '2030-2031',
            'date_debut' => '2030-09-15',
            'date_fin' => '2031-07-05',
            'active' => 1,
        ])->assertSessionHasNoErrors();
        $annee = AnneeScolaire::where('libelle', '2030-2031')->firstOrFail();

        $this->post('/trimestres', [
            'annee_scolaire_id' => $annee->id,
            'nom' => 'Trimestre test',
            'ordre' => 4,
            'date_debut' => now()->subMonth()->toDateString(),
            'date_fin' => now()->addMonth()->toDateString(),
        ])->assertSessionHasNoErrors();
        $trimestre = Trimestre::where('nom', 'Trimestre test')->firstOrFail();

        $this->post('/matieres', ['nom' => 'Mathématiques', 'code' => 'MATH', 'coefficient_defaut' => 4])
            ->assertSessionHasNoErrors();
        $matiere = Matiere::where('code', 'MATH')->firstOrFail();

        $this->post('/classes', ['nom' => '3ème A', 'niveau' => '3ème', 'annee_scolaire_id' => $annee->id])
            ->assertSessionHasNoErrors();
        $classe = Classe::where('nom', '3ème A')->firstOrFail();

        $this->post('/enseignants', [
            'nom' => 'Koffi',
            'prenom' => 'Ama',
            'email' => 'ama.koffi@espoir.tg',
            'matieres' => [$matiere->id],
            'classes' => [$classe->id],
        ])->assertSessionHasNoErrors();
        $enseignant = Enseignant::where('email', 'ama.koffi@espoir.tg')->firstOrFail();

        $this->put("/classes/{$classe->id}", [
            'nom' => '3ème A',
            'niveau' => '3ème',
            'annee_scolaire_id' => $annee->id,
            'enseignant_principal_id' => $enseignant->id,
        ])->assertSessionHasNoErrors();

        $this->post('/eleves', [
            'matricule' => 'ESP-001',
            'nom' => 'Mensah',
            'prenom' => 'Kodjo',
            'sexe' => 'M',
            'date_naissance' => '2016-03-12',
            'classe_id' => $classe->id,
            'statut' => 'actif',
            'date_inscription' => '2030-09-15',
            'email' => 'kodjo@espoir.tg',
        ])->assertSessionHasNoErrors();
        $eleve = Eleve::where('matricule', 'ESP-001')->firstOrFail();

        $this->post('/tuteurs', [
            'nom' => 'Mensah',
            'prenom' => 'Afi',
            'telephone' => '+228 90 11 22 33',
            'email' => 'afi.mensah@espoir.tg',
            'eleves' => [['id' => $eleve->id, 'lien_parente' => 'Mère']],
        ])->assertSessionHasNoErrors();
        $tuteur = Tuteur::where('email', 'afi.mensah@espoir.tg')->firstOrFail();
        $this->assertTrue($tuteur->eleves->contains($eleve));

        // 3. Création des comptes de connexion
        foreach ([['enseignant', $enseignant], ['eleve', $eleve], ['tuteur', $tuteur]] as [$type, $profil]) {
            $this->post('/comptes/generer', ['type' => $type, 'id' => $profil->id, 'mot_de_passe' => 'secret123'])
                ->assertSessionHas('compte_cree');
        }

        $userEnseignant = $enseignant->fresh()->user;
        $userEleve = $eleve->fresh()->user;
        $userParent = $tuteur->fresh()->user;
        $this->assertSame(User::ROLE_PARENT, $userParent->role);

        // Un second compte pour la même personne est refusé proprement (pas d'erreur 500)
        $this->post('/comptes/generer', ['type' => 'tuteur', 'id' => $tuteur->id])
            ->assertSessionHas('error');

        // 4. L'enseignant se connecte et saisit notes et présences
        $this->post('/logout');
        $this->post('/login', ['email' => $userEnseignant->email, 'password' => 'secret123'])->assertRedirect();
        $this->assertAuthenticatedAs($userEnseignant);

        $this->post('/notes/bulk', [
            'matiere_id' => $matiere->id,
            'classe_id' => $classe->id,
            'trimestre_id' => $trimestre->id,
            'type' => 'composition',
            'bareme' => 20,
            'coefficient' => 2,
            'date_evaluation' => now()->toDateString(),
            'notes' => [['eleve_id' => $eleve->id, 'valeur' => 15.5]],
        ])->assertSessionHasNoErrors();
        $this->assertSame(1, Note::withoutGlobalScopes()->where('eleve_id', $eleve->id)->count());

        $this->post('/presences/bulk', [
            'classe_id' => $classe->id,
            'trimestre_id' => $trimestre->id,
            'date' => now()->toDateString(),
            'presences' => [['eleve_id' => $eleve->id, 'statut' => 'absent', 'motif' => 'Malade']],
        ])->assertSessionHasNoErrors();
        $this->assertSame(1, Presence::withoutGlobalScopes()->where('eleve_id', $eleve->id)->count());

        // 5. Le professeur principal génère les bulletins de sa classe (PDF)
        $this->post("/bulletins/classes/{$classe->id}/trimestres/{$trimestre->id}")
            ->assertSessionHas('success');
        $this->assertSame(1, Bulletin::withoutGlobalScopes()->where('eleve_id', $eleve->id)->count());
        $this->get("/bulletins/eleves/{$eleve->id}/trimestres/{$trimestre->id}")->assertOk();

        // 6. Le parent suit son enfant et écrit à l'enseignant
        $this->actingAs($userParent);
        $this->get('/mes-enfants')->assertOk()->assertSee('Kodjo');
        $this->get("/mes-enfants/{$eleve->id}")->assertOk();
        $this->get('/messagerie')->assertOk();
        $this->post("/messagerie/{$userEnseignant->id}", ['contenu' => 'Bonjour, comment va Kodjo ?'])
            ->assertSessionHasNoErrors();
        $this->assertTrue(Notification::where('user_id', $userEnseignant->id)->exists());

        // 7. L'élève consulte ses notes
        $this->actingAs($userEleve);
        $this->get('/mon-espace/notes')->assertOk()->assertSee('Mathématiques');
        $this->get('/mon-espace/presences')->assertOk();

        // 8. L'admin publie une annonce : les parents sont notifiés
        $this->actingAs($admin);
        $this->post('/annonces', ['titre' => 'Réunion des parents', 'contenu' => 'Samedi à 9h', 'cible' => 'parents'])
            ->assertSessionHasNoErrors();
        $this->assertTrue(Notification::where('user_id', $userParent->id)->where('type', 'annonce')->where('message', 'Réunion des parents')->exists());

        // 9. Paiement de scolarité en retard → rappel automatique au parent
        $this->post('/paiements', [
            'eleve_id' => $eleve->id,
            'annee_scolaire_id' => $annee->id,
            'type' => 'scolarite',
            'montant' => 50000,
            'montant_paye' => 10000,
            'date_echeance' => now()->subDays(3)->toDateString(),
        ])->assertSessionHasNoErrors();

        $this->artisan('paiements:rappels')->assertSuccessful();
        $this->assertTrue(Notification::where('user_id', $userParent->id)->where('type', 'paiement_rappel')->exists());

        // 10. Tableaux de bord de chaque rôle
        foreach ([$admin, $userEnseignant, $userParent, $userEleve] as $user) {
            $this->actingAs($user)->get('/dashboard')->assertOk();
        }

        $this->assertSame(Ecole::STATUT_ESSAI, $ecole->fresh()->statut);
    }
}
