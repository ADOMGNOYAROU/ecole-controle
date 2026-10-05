<?php

namespace App\Support;

/**
 * Liens « Envoyer sur WhatsApp » (wa.me) : ouvrent la conversation avec un message
 * déjà rédigé. Aucun coût : c'est le WhatsApp de l'école qui envoie.
 */
class WhatsApp
{
    /** Indicatif ajouté aux numéros locaux à 8 chiffres (Togo). */
    public const INDICATIF_PAR_DEFAUT = '228';

    public static function lien(?string $telephone, string $message): string
    {
        $numero = self::numeroInternational($telephone);

        return 'https://wa.me/'.($numero ?? '').'?text='.rawurlencode($message);
    }

    /**
     * « 90 11 22 33 », « +228 90112233 » ou « 0022890112233 » donnent « 22890112233 ».
     * Retourne null si le numéro est vide ou inexploitable.
     */
    public static function numeroInternational(?string $telephone): ?string
    {
        $chiffres = preg_replace('/\D+/', '', (string) $telephone);

        if (str_starts_with($chiffres, '00')) {
            $chiffres = substr($chiffres, 2);
        }

        if (strlen($chiffres) === 8) {
            return self::INDICATIF_PAR_DEFAUT.$chiffres;
        }

        return strlen($chiffres) >= 10 && strlen($chiffres) <= 15 ? $chiffres : null;
    }
}
