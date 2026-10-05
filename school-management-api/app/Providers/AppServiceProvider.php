<?php

namespace App\Providers;

use App\Models\User;
use App\Services\JournalAuditeur;
use Illuminate\Auth\Events\Failed;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(JournalAuditeur::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Journal d'audit : connexions réussies, échouées et déconnexions
        Event::listen(Login::class, function (Login $event) {
            app(JournalAuditeur::class)->enregistrer('connexion', $event->user, user: $event->user);

            if ($event->user instanceof User) {
                $event->user->marquerConnexion();
            }
        });

        Event::listen(Failed::class, function (Failed $event) {
            $email = (string) ($event->credentials['email'] ?? '');
            $user = $event->user instanceof User ? $event->user : null;

            app(JournalAuditeur::class)->enregistrer(
                'connexion_echouee',
                $user,
                ['email_saisi' => $email],
                ecoleId: $user?->ecole_id,
                libelle: $email ?: 'Identifiant inconnu',
            );
        });

        Event::listen(Logout::class, function (Logout $event) {
            if ($event->user) {
                app(JournalAuditeur::class)->enregistrer('deconnexion', $event->user, user: $event->user);
            }
        });
    }
}
