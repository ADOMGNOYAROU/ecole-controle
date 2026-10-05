<?php

namespace App\Support;

/**
 * Niveaux du système scolaire togolais, de la maternelle à la terminale, pour proposer
 * la classe suivante lors du passage de fin d'année (« 6ème A » → « 5ème A »).
 */
class NiveauxScolaires
{
    /** Ordre des niveaux : libellé affiché => motif reconnu dans le nom d'une classe. */
    private const NIVEAUX = [
        'PS' => '(petite\s*section|ps)',
        'MS' => '(moyenne\s*section|ms)',
        'GS' => '(grande\s*section|gs)',
        'CP1' => '(cp\s*1|cpi)',
        'CP2' => '(cp\s*2)',
        'CE1' => '(ce\s*1)',
        'CE2' => '(ce\s*2)',
        'CM1' => '(cm\s*1)',
        'CM2' => '(cm\s*2)',
        '6ème' => '(6\s*(?:e|eme|ème|è)?)',
        '5ème' => '(5\s*(?:e|eme|ème|è)?)',
        '4ème' => '(4\s*(?:e|eme|ème|è)?)',
        '3ème' => '(3\s*(?:e|eme|ème|è)?)',
        '2nde' => '(2\s*nde?|seconde)',
        '1ère' => '(1\s*(?:ere|ère|re)|premi[eè]re)',
        'Tle' => '(tle|terminale)',
    ];

    /**
     * Nom proposé pour la classe de l'année suivante, ou null en fin de cycle (terminale).
     * Un nom non reconnu est renvoyé tel quel, pour que l'administrateur le corrige.
     */
    public static function classeSuivante(string $nom): ?string
    {
        $niveaux = array_keys(self::NIVEAUX);

        foreach (self::NIVEAUX as $libelle => $motif) {
            $regex = '/^\s*'.$motif.'(?=\s|$|[^a-z0-9])/iu';

            if (preg_match($regex, $nom, $m)) {
                $position = array_search($libelle, $niveaux, true);
                $suivant = $niveaux[$position + 1] ?? null;

                if ($suivant === null) {
                    return null;
                }

                return trim($suivant.substr($nom, strlen($m[0])));
            }
        }

        return $nom;
    }

    /** Vrai si la classe est en terminale : ses élèves terminent leur scolarité dans l'école. */
    public static function estFinDeCycle(string $nom): bool
    {
        return self::classeSuivante($nom) === null;
    }
}
