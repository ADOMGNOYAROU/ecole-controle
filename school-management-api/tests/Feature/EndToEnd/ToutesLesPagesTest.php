<?php

namespace Tests\Feature\EndToEnd;

use App\Models\Classe;
use App\Models\Eleve;
use App\Models\Enseignant;
use App\Models\Matiere;
use App\Models\Trimestre;
use App\Models\Tuteur;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Routing\Route;
use Illuminate\Support\Facades\Route as RouteFacade;
use Tests\TestCase;

/**
 * Test de fumée avant déploiement : ouvre chaque page GET de l'application avec chaque rôle,
 * sur les données de démonstration, et vérifie qu'aucune ne provoque d'erreur serveur (500).
 * Les refus d'accès (403/404/redirection) sont normaux selon le rôle.
 */
class ToutesLesPagesTest extends TestCase
{
    use RefreshDatabase;

    /** Pages à ne pas ouvrir : actions (déconnexion), appels externes (PayDunya), outils internes. */
    private const EXCLUES = ['logout', 'paydunya/', 'sanctum/', 'storage/', 'up', '_ignition', 'api/'];

    public function test_aucune_page_ne_provoque_d_erreur_serveur(): void
    {
        $this->seed();

        $utilisateurs = $this->utilisateursParRole();
        $parametres = $this->parametresDeRoute();
        $erreurs = [];
        $pagesTestees = 0;

        foreach ($this->pagesGet() as $route) {
            $uri = $this->remplirParametres($route, $parametres);

            if ($uri === null) {
                continue;
            }

            foreach ($utilisateurs as $role => $user) {
                $statut = $this->actingAs($user)->get($uri)->getStatusCode();
                $pagesTestees++;

                if ($statut >= 500) {
                    $erreurs[] = "[{$role}] GET {$uri} → {$statut}";
                }
            }
        }

        $this->assertGreaterThan(50, $pagesTestees, 'Trop peu de pages testées : le crawl ne fonctionne pas.');
        $this->assertSame([], $erreurs, "Pages en erreur serveur :\n".implode("\n", $erreurs));
    }

    public function test_les_pages_publiques_s_affichent(): void
    {
        $this->get('/login')->assertOk();
        $this->get('/inscription')->assertOk();
        $this->get('/up')->assertOk();
        $this->get('/dashboard')->assertRedirect('/login');
    }

    /** @return array<string, User> */
    private function utilisateursParRole(): array
    {
        $admin = User::where('role', User::ROLE_ADMIN)->firstOrFail();
        $ecoleId = $admin->ecole_id;

        $lier = function ($profil, string $role) use ($ecoleId): User {
            if ($profil->user_id) {
                return User::findOrFail($profil->user_id);
            }

            $user = User::factory()->create(['ecole_id' => $ecoleId, 'role' => $role]);
            $profil->update(['user_id' => $user->id]);

            return $user;
        };

        $enseignant = Enseignant::withoutGlobalScopes()->where('ecole_id', $ecoleId)->firstOrFail();
        $classeTitulaire = Classe::withoutGlobalScopes()->where('ecole_id', $ecoleId)->first();
        $classeTitulaire?->update(['enseignant_principal_id' => $enseignant->id]);

        $tuteur = Tuteur::withoutGlobalScopes()->where('ecole_id', $ecoleId)->whereHas('eleves')->firstOrFail();

        return [
            'super_admin' => User::factory()->superAdmin()->create(),
            'admin' => $admin,
            'enseignant' => $lier($enseignant, User::ROLE_ENSEIGNANT),
            'parent' => $lier($tuteur, User::ROLE_PARENT),
            'eleve' => $lier($tuteur->eleves()->withoutGlobalScopes()->firstOrFail(), User::ROLE_ELEVE),
        ];
    }

    /** @return array<string, int> */
    private function parametresDeRoute(): array
    {
        $ecoleId = User::where('role', User::ROLE_ADMIN)->value('ecole_id');
        $premier = fn (string $modele) => $modele::withoutGlobalScopes()->where('ecole_id', $ecoleId)->value('id');

        return array_filter([
            'classe' => $premier(Classe::class),
            'eleve' => $premier(Eleve::class),
            'enseignant' => $premier(Enseignant::class),
            'matiere' => $premier(Matiere::class),
            'tuteur' => $premier(Tuteur::class),
            'trimestre' => $premier(Trimestre::class),
            'ecole' => $ecoleId,
        ]);
    }

    /** @return array<int, Route> */
    private function pagesGet(): array
    {
        return array_values(array_filter(
            RouteFacade::getRoutes()->getRoutes(),
            fn (Route $route) => in_array('GET', $route->methods(), true)
                && ! collect(self::EXCLUES)->contains(fn ($prefixe) => str_starts_with($route->uri(), $prefixe))
        ));
    }

    /** Remplace {param} par un identifiant réel ; ignore la page si un paramètre obligatoire est inconnu. */
    private function remplirParametres(Route $route, array $parametres): ?string
    {
        $uri = $route->uri();

        foreach ($route->parameterNames() as $nom) {
            if (isset($parametres[$nom])) {
                $uri = preg_replace('/\{'.$nom.'\??\}/', (string) $parametres[$nom], $uri);
            } elseif (str_contains($uri, '{'.$nom.'?}')) {
                $uri = str_replace('/{'.$nom.'?}', '', $uri);
            } else {
                return null;
            }
        }

        return '/'.ltrim($uri, '/');
    }
}
