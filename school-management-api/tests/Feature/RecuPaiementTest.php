<?php

namespace Tests\Feature;

use App\Models\AnneeScolaire;
use App\Models\Classe;
use App\Models\Eleve;
use App\Models\Paiement;
use App\Models\Tuteur;
use App\Models\User;
use App\Support\WhatsApp;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RecuPaiementTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private Eleve $eleve;

    private Tuteur $tuteur;

    private Paiement $paiement;

    private AnneeScolaire $annee;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->admin()->create();
        $this->admin->ecole->update(['nom' => 'Collège Les Palmiers', 'telephone' => '+228 22 00 00 00']);

        $this->annee = AnneeScolaire::create([
            'ecole_id' => $this->admin->ecole_id, 'libelle' => '2026-2027',
            'date_debut' => '2026-09-14', 'date_fin' => '2027-07-16', 'active' => true,
        ]);
        $classe = Classe::create(['ecole_id' => $this->admin->ecole_id, 'nom' => '3ème A', 'annee_scolaire_id' => $this->annee->id]);
        $this->eleve = $this->makeEleve($this->admin->ecole_id, $classe->id, 'R-001', 'Kodjo');

        $this->tuteur = Tuteur::create([
            'ecole_id' => $this->admin->ecole_id, 'nom' => 'Mensah', 'prenom' => 'Afi', 'telephone' => '90 11 22 33',
        ]);
        $this->eleve->tuteurs()->attach($this->tuteur->id, ['lien_parente' => 'Mère']);

        $this->paiement = Paiement::create([
            'ecole_id' => $this->admin->ecole_id, 'eleve_id' => $this->eleve->id, 'annee_scolaire_id' => $this->annee->id,
            'type' => 'scolarite', 'montant' => 75000, 'montant_paye' => 50000,
            'date_echeance' => '2026-10-31', 'date_paiement' => '2026-10-05', 'statut' => Paiement::STATUT_PARTIEL,
        ]);
    }

    public function test_l_ecole_telecharge_le_recu_en_pdf(): void
    {
        $reponse = $this->actingAs($this->admin)->get("/paiements/{$this->paiement->id}/recu");

        $reponse->assertOk();
        $this->assertSame('application/pdf', $reponse->headers->get('Content-Type'));
        $this->assertSame(sprintf('REC-%04d-%06d', $this->admin->ecole_id, $this->paiement->id), $this->paiement->numeroRecu());
    }

    public function test_la_liste_propose_le_recu_et_whatsapp_au_parent(): void
    {
        $page = $this->actingAs($this->admin)->get('/paiements')->assertOk();

        $page->assertSee("/paiements/{$this->paiement->id}/recu", false);
        $page->assertSee('https://wa.me/22890112233?text=', false);
    }

    public function test_le_message_whatsapp_contient_le_montant_le_reste_et_le_lien_signe(): void
    {
        $lien = $this->paiement->lienWhatsApp($this->tuteur);
        $message = rawurldecode(substr($lien, strpos($lien, 'text=') + 5));

        $this->assertStringStartsWith('https://wa.me/22890112233?text=', $lien);
        $this->assertStringContainsString('Collège Les Palmiers confirme le paiement de 50 000 F', $message);
        $this->assertStringContainsString('Kodjo', $message);
        $this->assertStringContainsString('Reste à payer : 25 000 F', $message);
        $this->assertStringContainsString('signature=', $message);
    }

    public function test_apres_enregistrement_l_encadre_propose_le_recu(): void
    {
        $this->actingAs($this->admin)->post('/paiements', [
            'eleve_id' => $this->eleve->id, 'annee_scolaire_id' => $this->annee->id, 'type' => 'inscription',
            'montant' => 20000, 'montant_paye' => 20000, 'date_echeance' => '2026-10-10', 'date_paiement' => '2026-10-05',
        ])->assertRedirect('/paiements')->assertSessionHas('recu_paiement_id');

        $this->followingRedirects()->actingAs($this->admin)->post('/paiements', [
            'eleve_id' => $this->eleve->id, 'annee_scolaire_id' => $this->annee->id, 'type' => 'transport',
            'montant' => 10000, 'montant_paye' => 10000, 'date_echeance' => '2026-10-10',
        ])->assertOk()->assertSee('remettez ou envoyez le reçu');
    }

    public function test_le_parent_et_l_eleve_voient_leur_recu_mais_pas_un_autre_parent(): void
    {
        $parent = User::factory()->parent()->create(['ecole_id' => $this->admin->ecole_id]);
        $this->tuteur->update(['user_id' => $parent->id]);
        $this->actingAs($parent)->get("/paiements/{$this->paiement->id}/recu")->assertOk();
        $this->get("/mes-enfants/{$this->eleve->id}")->assertOk()->assertSee('Reçu PDF');

        $eleveUser = User::factory()->eleve()->create(['ecole_id' => $this->admin->ecole_id]);
        $this->eleve->update(['user_id' => $eleveUser->id]);
        $this->actingAs($eleveUser)->get("/paiements/{$this->paiement->id}/recu")->assertOk();

        $autreParent = User::factory()->parent()->create(['ecole_id' => $this->admin->ecole_id]);
        Tuteur::create(['ecole_id' => $this->admin->ecole_id, 'user_id' => $autreParent->id, 'nom' => 'Doe', 'prenom' => 'Jo', 'telephone' => '91000000']);
        $this->actingAs($autreParent)->get("/paiements/{$this->paiement->id}/recu")->assertForbidden();
    }

    public function test_une_autre_ecole_ne_voit_pas_le_recu(): void
    {
        $this->actingAs(User::factory()->admin()->create())
            ->get("/paiements/{$this->paiement->id}/recu")
            ->assertNotFound();
    }

    public function test_le_lien_signe_s_ouvre_sans_compte_mais_ne_peut_pas_etre_modifie(): void
    {
        $lien = $this->paiement->lienRecuPublic();

        $this->get($lien)->assertOk()->assertHeader('Content-Type', 'application/pdf');

        $autre = Paiement::create([
            'ecole_id' => $this->admin->ecole_id, 'eleve_id' => $this->eleve->id, 'annee_scolaire_id' => $this->annee->id,
            'type' => 'cantine', 'montant' => 6000, 'montant_paye' => 6000, 'date_echeance' => '2026-10-31',
            'statut' => Paiement::STATUT_PAYE,
        ]);
        $this->get(str_replace("/recus/{$this->paiement->id}?", "/recus/{$autre->id}?", $lien))->assertForbidden();
        $this->get("/recus/{$this->paiement->id}")->assertForbidden();
    }

    public function test_les_numeros_de_telephone_sont_mis_au_format_international(): void
    {
        $this->assertSame('22890112233', WhatsApp::numeroInternational('90 11 22 33'));
        $this->assertSame('22890112233', WhatsApp::numeroInternational('+228 90 11 22 33'));
        $this->assertSame('22890112233', WhatsApp::numeroInternational('0022890112233'));
        $this->assertSame('33612345678', WhatsApp::numeroInternational('+33 6 12 34 56 78'));
        $this->assertNull(WhatsApp::numeroInternational(''));
        $this->assertNull(WhatsApp::numeroInternational('123'));
        $this->assertStringStartsWith('https://wa.me/?text=', WhatsApp::lien(null, 'Bonjour'));
    }

    private function makeEleve(int $ecoleId, int $classeId, string $matricule, string $prenom): Eleve
    {
        return Eleve::create([
            'ecole_id' => $ecoleId, 'classe_id' => $classeId, 'matricule' => $matricule,
            'nom' => 'Mensah', 'prenom' => $prenom, 'sexe' => 'M', 'date_naissance' => '2012-01-01',
            'statut' => Eleve::STATUT_ACTIF, 'date_inscription' => '2026-09-14',
        ]);
    }
}
