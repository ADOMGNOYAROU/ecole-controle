<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Envoie les contacts (directeurs d'école) à Smart Engage, notre outil de campagnes email.
 * Désactivé tant que SMART_ENGAGE_URL et SMART_ENGAGE_CLE ne sont pas renseignés.
 * Une panne de Smart Engage ne doit jamais bloquer l'inscription d'une école.
 */
class SmartEngage
{
    public function actif(): bool
    {
        return filled(config('services.smart_engage.url')) && filled(config('services.smart_engage.cle'));
    }

    /** @return bool vrai si Smart Engage a accepté le contact (nouveau ou déjà connu) */
    public function ajouterContact(string $email): bool
    {
        if (! $this->actif()) {
            return false;
        }

        try {
            $reponse = Http::timeout(15)
                ->acceptJson()
                ->withHeaders(['X-Site-Key' => config('services.smart_engage.cle')])
                ->post(rtrim(config('services.smart_engage.url'), '/').'/api/emails', ['email' => $email]);

            if ($reponse->successful()) {
                return true;
            }

            Log::warning('Smart Engage a refusé le contact', ['statut' => $reponse->status()]);
        } catch (\Throwable $e) {
            Log::warning('Smart Engage injoignable', ['erreur' => $e->getMessage()]);
        }

        return false;
    }
}
