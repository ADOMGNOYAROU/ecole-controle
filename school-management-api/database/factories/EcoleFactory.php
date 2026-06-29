<?php

namespace Database\Factories;

use App\Models\Abonnement;
use App\Models\Ecole;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Ecole>
 */
class EcoleFactory extends Factory
{
    protected $model = Ecole::class;

    public function configure(): static
    {
        return $this->afterCreating(function (Ecole $ecole) {
            if ($ecole->plan === Ecole::PLAN_PREMIUM && $ecole->statut === Ecole::STATUT_ACTIF) {
                Abonnement::create([
                    'ecole_id' => $ecole->id,
                    'plan' => Ecole::PLAN_PREMIUM,
                    'date_debut' => now()->subMonth(),
                    'date_fin' => now()->addMonths(2),
                    'statut' => 'actif',
                    'montant' => Ecole::TARIF_PREMIUM_TRIMESTRIEL,
                ]);
            }
        });
    }

    public function definition(): array
    {
        $nom = fake()->unique()->company();

        return [
            'nom' => $nom,
            'slug' => Ecole::genererSlug($nom),
            'email_contact' => fake()->unique()->safeEmail(),
            'telephone' => fake()->phoneNumber(),
            'ville' => fake()->city(),
            'statut' => Ecole::STATUT_ACTIF,
            'plan' => Ecole::PLAN_PREMIUM,
            'trial_ends_at' => null,
        ];
    }

    public function essai(): static
    {
        return $this->state([
            'statut' => Ecole::STATUT_ESSAI,
            'trial_ends_at' => now()->addDays(30),
        ]);
    }

    public function gratuit(): static
    {
        return $this->state([
            'plan' => Ecole::PLAN_GRATUIT,
            'trial_ends_at' => null,
        ]);
    }

    public function suspendue(): static
    {
        return $this->state(['statut' => Ecole::STATUT_SUSPENDU]);
    }
}
