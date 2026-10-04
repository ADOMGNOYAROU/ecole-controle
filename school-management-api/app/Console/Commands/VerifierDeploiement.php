<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

/**
 * Contrôle, sur le serveur, que la configuration est prête pour la production.
 * Code de sortie 1 si un point bloquant échoue (utilisable dans un script de déploiement).
 */
class VerifierDeploiement extends Command
{
    protected $signature = 'app:verifier-deploiement {--sans-reseau : Ne pas appeler l\'API PayDunya}';

    protected $description = 'Vérifie la configuration de production (env, base, stockage, PayDunya, e-mail, planificateur)';

    private int $bloquants = 0;

    public function handle(): int
    {
        $this->info('Vérification du déploiement — '.config('app.name'));

        $this->section('Application', [
            fn () => $this->verifier(config('app.key') !== null && config('app.key') !== '', 'APP_KEY défini', 'APP_KEY manquant : php artisan key:generate'),
            fn () => $this->verifier(app()->environment('production'), 'APP_ENV=production', 'APP_ENV='.app()->environment().' (attendu : production)'),
            fn () => $this->verifier(! config('app.debug'), 'APP_DEBUG=false', 'APP_DEBUG=true : les erreurs exposeraient la configuration'),
            fn () => $this->verifier(str_starts_with((string) config('app.url'), 'https://'), 'APP_URL en https ('.config('app.url').')', 'APP_URL doit être en https (retours PayDunya, cookies)'),
        ]);

        $this->section('Serveur PHP', [
            fn () => $this->verifier(version_compare(PHP_VERSION, '8.2.0', '>='), 'PHP '.PHP_VERSION, 'PHP 8.2 minimum requis (actuel : '.PHP_VERSION.')'),
            fn () => $this->verifierExtensions(),
            fn () => $this->verifierMemoire(),
        ]);

        $this->section('Base de données', [
            fn () => $this->verifierBase(),
            fn () => $this->verifier(User::where('role', User::ROLE_SUPER_ADMIN)->exists(), 'Un super_admin existe', 'Aucun super_admin : les paiements PayDunya ne peuvent pas être confirmés (php artisan create:admin)'),
        ]);

        $this->section('Stockage', [
            fn () => $this->verifier(is_writable(storage_path('logs')) && is_writable(storage_path('framework')), 'storage/ accessible en écriture', 'storage/ non accessible en écriture'),
            fn () => $this->verifier(is_writable(base_path('bootstrap/cache')), 'bootstrap/cache accessible en écriture', 'bootstrap/cache non accessible en écriture'),
            fn () => $this->avertir(file_exists(public_path('storage')), 'Lien public/storage présent', 'Lien public/storage absent : php artisan storage:link'),
        ]);

        $this->section('PayDunya', [
            fn () => $this->verifier(config('services.paydunya.mode') === 'live', 'Mode live', 'PAYDUNYA_MODE='.config('services.paydunya.mode').' : aucun vrai paiement ne sera encaissé'),
            fn () => $this->verifierClesPayDunya(),
            fn () => $this->avertir(
                ! config('services.paydunya.redistribution_enabled') || filled(config('services.paydunya.recipient_phone')),
                config('services.paydunya.redistribution_enabled') ? 'Redistribution activée vers '.config('services.paydunya.recipient_phone') : 'Redistribution désactivée (PAYDUNYA_REDISTRIBUTION_ENABLED=false)',
                'Redistribution activée sans PAYDUNYA_RECIPIENT_PHONE'
            ),
        ]);

        $this->section('E-mails et tâches planifiées', [
            fn () => $this->avertir(! in_array(config('mail.default'), ['log', 'array'], true), 'MAIL_MAILER='.config('mail.default'), 'MAIL_MAILER='.config('mail.default').' : aucun e-mail (identifiants, rappels) ne sera réellement envoyé'),
            fn () => $this->avertir(true, 'Rappels de paiement planifiés chaque jour à 08:00', ''),
            fn () => $this->line('    ↳ nécessite la tâche cron : * * * * * cd '.base_path().' && php artisan schedule:run >> /dev/null 2>&1'),
        ]);

        $this->newLine();

        if ($this->bloquants > 0) {
            $this->error("{$this->bloquants} point(s) bloquant(s) à corriger avant la mise en production.");

            return self::FAILURE;
        }

        $this->info('Configuration prête pour la production.');

        return self::SUCCESS;
    }

    private function section(string $titre, array $verifications): void
    {
        $this->newLine();
        $this->line("<options=bold>{$titre}</>");

        foreach ($verifications as $verification) {
            $verification();
        }
    }

    private function verifier(bool $ok, string $succes, string $echec): void
    {
        if ($ok) {
            $this->line("  <fg=green>✔</> {$succes}");

            return;
        }

        $this->bloquants++;
        $this->line("  <fg=red>✘</> {$echec}");
    }

    private function avertir(bool $ok, string $succes, string $avertissement): void
    {
        $this->line($ok ? "  <fg=green>✔</> {$succes}" : "  <fg=yellow>!</> {$avertissement}");
    }

    private function verifierExtensions(): void
    {
        $manquantes = array_filter(
            ['pdo', 'mbstring', 'openssl', 'curl', 'dom', 'gd', 'fileinfo', 'bcmath'],
            fn ($extension) => ! extension_loaded($extension)
        );

        $this->verifier($manquantes === [], 'Extensions PHP requises présentes', 'Extensions PHP manquantes : '.implode(', ', $manquantes));
    }

    private function verifierMemoire(): void
    {
        $limite = ini_get('memory_limit');
        $octets = $limite === '-1' ? PHP_INT_MAX : $this->enOctets($limite);

        $this->avertir($octets >= 128 * 1024 * 1024, "memory_limit={$limite}", "memory_limit={$limite} : 128M minimum conseillé (rapports PDF)");
    }

    private function verifierBase(): void
    {
        try {
            DB::connection()->getPdo();
            $this->verifier(true, 'Connexion à la base ('.config('database.default').')', '');
        } catch (\Throwable $e) {
            $this->verifier(false, '', 'Connexion à la base impossible : '.$e->getMessage());

            return;
        }

        $migrations = collect(app('migrator')->getMigrationFiles(database_path('migrations')))->keys();
        $executees = DB::table('migrations')->pluck('migration');
        $enAttente = $migrations->diff($executees);

        $this->verifier($enAttente->isEmpty(), 'Migrations à jour', $enAttente->count().' migration(s) en attente : php artisan migrate --force');
    }

    private function verifierClesPayDunya(): void
    {
        $cles = ['master_key', 'public_key', 'private_key', 'token'];
        $manquantes = array_filter($cles, fn ($cle) => blank(config("services.paydunya.{$cle}")));

        if ($manquantes !== []) {
            $this->verifier(false, '', 'Clés PayDunya manquantes : '.implode(', ', $manquantes));

            return;
        }

        $live = config('services.paydunya.mode') === 'live';
        $prefixe = $live ? 'live_' : 'test_';
        $this->verifier(
            str_starts_with(config('services.paydunya.private_key'), $prefixe) && str_starts_with(config('services.paydunya.public_key'), $prefixe),
            "Clés de type {$prefixe}… cohérentes avec le mode",
            "Les clés ne correspondent pas au mode (attendu : {$prefixe}…)"
        );

        if ($this->option('sans-reseau')) {
            return;
        }

        try {
            $reponse = Http::withHeaders([
                'PAYDUNYA-MASTER-KEY' => config('services.paydunya.master_key'),
                'PAYDUNYA-PRIVATE-KEY' => config('services.paydunya.private_key'),
                'PAYDUNYA-TOKEN' => config('services.paydunya.token'),
            ])->acceptJson()->timeout(20)->get(
                'https://app.paydunya.com/'.($live ? 'api' : 'sandbox-api').'/v1/checkout-invoice/confirm/verification_deploiement'
            )->json();

            // 4004 = « transaction introuvable » : les clés sont acceptées. 1001 = clés refusées.
            $code = $reponse['response_code'] ?? null;
            $this->verifier($code !== '1001' && $code !== null, 'Clés PayDunya acceptées par l\'API', 'Clés PayDunya refusées : '.($reponse['response_text'] ?? 'réponse inattendue'));
        } catch (\Throwable $e) {
            $this->verifier(false, '', 'API PayDunya injoignable : '.$e->getMessage());
        }
    }

    private function enOctets(string $valeur): int
    {
        $nombre = (int) $valeur;

        return match (strtoupper(substr($valeur, -1))) {
            'G' => $nombre * 1024 ** 3,
            'M' => $nombre * 1024 ** 2,
            'K' => $nombre * 1024,
            default => $nombre,
        };
    }
}
