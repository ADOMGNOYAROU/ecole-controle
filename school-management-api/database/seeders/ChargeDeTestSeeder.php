<?php

namespace Database\Seeders;

use App\Models\Abonnement;
use App\Models\AnneeScolaire;
use App\Models\Classe;
use App\Models\Ecole;
use App\Models\Enseignant;
use App\Models\Matiere;
use App\Models\Trimestre;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use RuntimeException;

/**
 * Données fictives pour les tests de charge k6 (tests/charge) : jamais en production.
 *
 *   php artisan db:seed --class=ChargeDeTestSeeder
 *
 * Par défaut 20 écoles de 100 élèves. Comptes : directeur{n}@charge.test, parent{n}-{k}@charge.test
 * et enseignant{n}-{k}@charge.test, mot de passe « charge-test ».
 */
class ChargeDeTestSeeder extends Seeder
{
    public const MOT_DE_PASSE = 'charge-test';

    private const CLASSES = ['CP1 A', 'CP2 A', 'CE1 A', 'CE2 A', 'CM1 A', 'CM2 A'];

    private const MATIERES = [
        ['Mathématiques', 'MATH', 4], ['Français', 'FR', 4], ['Anglais', 'ANG', 2],
        ['Sciences', 'SCI', 2], ['Histoire-Géographie', 'HG', 2], ['EPS', 'EPS', 1],
    ];

    private const PRENOMS = ['Ama', 'Kofi', 'Sena', 'Yao', 'Esi', 'Kossi', 'Akossiwa', 'Komlan', 'Abla', 'Edem', 'Afi', 'Mawuli'];

    private const NOMS = ['Mensah', 'Agbeko', 'Kodjo', 'Dossou', 'Amouzou', 'Lawson', 'Ablodé', 'Tchalla', 'Gbeasor', 'Adjovi'];

    public function run(): void
    {
        if (app()->isProduction()) {
            throw new RuntimeException('Données de test de charge interdites en production.');
        }

        $nbEcoles = (int) env('CHARGE_ECOLES', 20);
        $elevesParClasse = (int) env('CHARGE_ELEVES_PAR_CLASSE', 17);
        $hash = Hash::make(self::MOT_DE_PASSE);

        // Sans événements de modèle : pas de journal d'audit pour des données fictives
        Model::withoutEvents(function () use ($nbEcoles, $elevesParClasse, $hash) {
            for ($n = 1; $n <= $nbEcoles; $n++) {
                DB::transaction(fn () => $this->ecole($n, $elevesParClasse, $hash));
                $this->command?->info("École {$n}/{$nbEcoles} créée");
            }
        });
    }

    private function ecole(int $n, int $elevesParClasse, string $hash): void
    {
        $maintenant = now();
        $ecole = Ecole::create([
            'nom' => "École Charge {$n}",
            'slug' => "ecole-charge-{$n}",
            'email_contact' => "directeur{$n}@charge.test",
            'telephone' => '90'.str_pad((string) $n, 6, '0', STR_PAD_LEFT),
            'ville' => 'Lomé',
            'statut' => Ecole::STATUT_ACTIF,
            'plan' => Ecole::PLAN_PREMIUM,
        ]);
        Abonnement::create([
            'ecole_id' => $ecole->id, 'plan' => Ecole::PLAN_PREMIUM, 'statut' => 'actif',
            'date_debut' => $maintenant->copy()->subMonth(), 'date_fin' => $maintenant->copy()->addMonths(2),
            'montant' => Ecole::TARIF_PREMIUM_2MOIS,
        ]);
        User::create([
            'ecole_id' => $ecole->id, 'name' => "Directeur {$n}", 'email' => "directeur{$n}@charge.test",
            'password' => $hash, 'role' => User::ROLE_ADMIN,
        ]);

        $annee = AnneeScolaire::create([
            'ecole_id' => $ecole->id, 'libelle' => '2026-2027', 'active' => true,
            'date_debut' => '2026-09-14', 'date_fin' => '2027-07-03',
        ]);
        $trimestre = Trimestre::create([
            'ecole_id' => $ecole->id, 'annee_scolaire_id' => $annee->id, 'nom' => '1er trimestre', 'ordre' => 1,
            'date_debut' => '2026-09-14', 'date_fin' => '2026-12-18',
        ]);
        Trimestre::create([
            'ecole_id' => $ecole->id, 'annee_scolaire_id' => $annee->id, 'nom' => '2e trimestre', 'ordre' => 2,
            'date_debut' => '2027-01-04', 'date_fin' => '2027-03-26',
        ]);

        $matieres = collect(self::MATIERES)->map(fn ($m) => Matiere::create([
            'ecole_id' => $ecole->id, 'nom' => $m[0], 'code' => $m[1], 'coefficient_defaut' => $m[2],
        ]));

        $enseignants = collect(range(1, 3))->map(function ($k) use ($ecole, $n, $hash) {
            $user = User::create([
                'ecole_id' => $ecole->id, 'name' => "Enseignant {$n}-{$k}", 'email' => "enseignant{$n}-{$k}@charge.test",
                'password' => $hash, 'role' => User::ROLE_ENSEIGNANT,
            ]);

            return Enseignant::create([
                'ecole_id' => $ecole->id, 'user_id' => $user->id, 'nom' => 'Enseignant', 'prenom' => "{$n}-{$k}",
                'email' => $user->email, 'date_embauche' => '2020-09-01',
            ]);
        });

        $matricule = 1;
        foreach (self::CLASSES as $i => $nomClasse) {
            $classe = Classe::create([
                'ecole_id' => $ecole->id, 'nom' => $nomClasse, 'niveau' => explode(' ', $nomClasse)[0],
                'annee_scolaire_id' => $annee->id, 'enseignant_principal_id' => $enseignants[$i % 3]->id,
            ]);

            foreach ($matieres as $j => $matiere) {
                DB::table('enseignant_matiere_classe')->insert([
                    'enseignant_id' => $enseignants[$j % 3]->id, 'matiere_id' => $matiere->id, 'classe_id' => $classe->id,
                    'created_at' => $maintenant, 'updated_at' => $maintenant,
                ]);
            }

            $eleves = [];
            for ($e = 0; $e < $elevesParClasse; $e++, $matricule++) {
                $eleves[] = [
                    'ecole_id' => $ecole->id, 'classe_id' => $classe->id, 'statut' => 'actif',
                    'matricule' => sprintf('CH%02d-%04d', $n, $matricule),
                    'prenom' => self::PRENOMS[$matricule % count(self::PRENOMS)],
                    'nom' => self::NOMS[($matricule * 7) % count(self::NOMS)],
                    'sexe' => $matricule % 2 ? 'F' : 'M',
                    'date_naissance' => Carbon::create(2014 + $i % 4, 1 + $matricule % 12, 1 + $matricule % 27)->toDateString(),
                    'date_inscription' => '2026-09-14', 'created_at' => $maintenant, 'updated_at' => $maintenant,
                ];
            }
            DB::table('eleves')->insert($eleves);
            $idsEleves = DB::table('eleves')->where('classe_id', $classe->id)->orderBy('id')->pluck('id');

            $this->notesPresencesPaiements($ecole->id, $classe->id, $annee->id, $trimestre->id, $enseignants[$i % 3]->id, $idsEleves, $matieres, $maintenant);
        }

        $this->parents($ecole->id, $n, $hash, $maintenant);
    }

    private function notesPresencesPaiements(int $ecoleId, int $classeId, int $anneeId, int $trimestreId, int $enseignantId, $idsEleves, $matieres, Carbon $maintenant): void
    {
        $notes = [];
        $presences = [];
        $paiements = [];
        $jours = collect(range(0, 13))->map(fn ($d) => Carbon::parse('2026-09-14')->addWeekdays($d)->toDateString());

        foreach ($idsEleves as $k => $eleveId) {
            foreach ($matieres as $matiere) {
                foreach (['devoir' => 1, 'composition' => 2] as $type => $coefficient) {
                    $notes[] = [
                        'ecole_id' => $ecoleId, 'eleve_id' => $eleveId, 'matiere_id' => $matiere->id, 'enseignant_id' => $enseignantId,
                        'classe_id' => $classeId, 'trimestre_id' => $trimestreId, 'type' => $type,
                        'valeur' => 6 + (($eleveId * 13 + $matiere->id * 7 + $coefficient) % 140) / 10, 'bareme' => 20,
                        'coefficient' => $coefficient, 'date_evaluation' => '2026-10-02',
                        'created_at' => $maintenant, 'updated_at' => $maintenant,
                    ];
                }
            }

            foreach ($jours as $d => $jour) {
                $presences[] = [
                    'ecole_id' => $ecoleId, 'eleve_id' => $eleveId, 'classe_id' => $classeId, 'enseignant_id' => $enseignantId,
                    'trimestre_id' => $trimestreId, 'date' => $jour, 'statut' => ($eleveId + $d) % 17 === 0 ? 'absent' : 'present',
                    'created_at' => $maintenant, 'updated_at' => $maintenant,
                ];
            }

            $paye = [0, 75000, 150000][$k % 3];
            $paiements[] = [
                'ecole_id' => $ecoleId, 'eleve_id' => $eleveId, 'annee_scolaire_id' => $anneeId, 'type' => 'scolarite',
                'montant' => 150000, 'montant_paye' => $paye, 'date_echeance' => '2026-11-15',
                'date_paiement' => $paye > 0 ? '2026-10-01' : null,
                'statut' => $paye === 150000 ? 'paye' : ($paye > 0 ? 'partiel' : 'en_attente'),
                'created_at' => $maintenant, 'updated_at' => $maintenant,
            ];
        }

        foreach (array_chunk($notes, 500) as $lot) {
            DB::table('notes')->insert($lot);
        }
        foreach (array_chunk($presences, 500) as $lot) {
            DB::table('presences')->insert($lot);
        }
        DB::table('paiements')->insert($paiements);
    }

    /** 10 comptes parents par école, chacun relié à un élève. */
    private function parents(int $ecoleId, int $n, string $hash, Carbon $maintenant): void
    {
        $eleves = DB::table('eleves')->where('ecole_id', $ecoleId)->orderBy('id')->limit(10)->get(['id', 'nom', 'prenom']);

        foreach ($eleves as $k => $eleve) {
            $email = 'parent'.$n.'-'.($k + 1).'@charge.test';
            $userId = DB::table('users')->insertGetId([
                'ecole_id' => $ecoleId, 'name' => "Parent de {$eleve->prenom}", 'email' => $email, 'password' => $hash,
                'role' => User::ROLE_PARENT, 'created_at' => $maintenant, 'updated_at' => $maintenant,
            ]);
            $tuteurId = DB::table('tuteurs')->insertGetId([
                'ecole_id' => $ecoleId, 'user_id' => $userId, 'nom' => $eleve->nom, 'prenom' => 'Parent',
                'telephone' => '91'.str_pad((string) ($n * 100 + $k), 6, '0', STR_PAD_LEFT), 'email' => $email,
                'created_at' => $maintenant, 'updated_at' => $maintenant,
            ]);
            DB::table('eleve_tuteur')->insert([
                'eleve_id' => $eleve->id, 'tuteur_id' => $tuteurId, 'lien_parente' => 'Parent', 'contact_principal' => true,
                'created_at' => $maintenant, 'updated_at' => $maintenant,
            ]);
        }
    }
}
