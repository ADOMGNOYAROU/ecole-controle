<?php

namespace App\Models;

use App\Models\Concerns\BelongsToEcole;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Eleve extends Model
{
    use HasFactory, BelongsToEcole;

    protected $fillable = [
        'ecole_id',
        'user_id',
        'matricule',
        'nom',
        'prenom',
        'sexe',
        'date_naissance',
        'lieu_naissance',
        'adresse',
        'telephone',
        'email',
        'photo',
        'classe_id',
        'statut',
        'date_inscription',
        'contact_urgence_nom',
        'contact_urgence_telephone',
    ];

    protected $casts = [
        'date_naissance' => 'date',
        'date_inscription' => 'date',
    ];

    public const STATUT_ACTIF = 'actif';
    public const STATUT_INACTIF = 'inactif';
    public const STATUT_DIPLOME = 'diplome';
    public const STATUT_EXCLU = 'exclu';

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function classe(): BelongsTo
    {
        return $this->belongsTo(Classe::class);
    }

    public function tuteurs(): BelongsToMany
    {
        return $this->belongsToMany(Tuteur::class, 'eleve_tuteur')
            ->withPivot('lien_parente', 'contact_principal')
            ->withTimestamps();
    }

    public function notes(): HasMany
    {
        return $this->hasMany(Note::class);
    }

    public function presences(): HasMany
    {
        return $this->hasMany(Presence::class);
    }

    public function paiements(): HasMany
    {
        return $this->hasMany(Paiement::class);
    }

    public function bulletins(): HasMany
    {
        return $this->hasMany(Bulletin::class);
    }

    public function nomComplet(): string
    {
        return "{$this->prenom} {$this->nom}";
    }

    public function moyenneTrimestre(int $trimestreId): ?float
    {
        $notes = $this->notes()->where('trimestre_id', $trimestreId)->get();

        if ($notes->isEmpty()) {
            return null;
        }

        $totalPondere = $notes->sum(fn (Note $note) => ($note->valeur / $note->bareme) * 20 * $note->coefficient);
        $totalCoefficients = $notes->sum('coefficient');

        return $totalCoefficients > 0 ? round($totalPondere / $totalCoefficients, 2) : null;
    }

    /**
     * Moyenne par matière puis moyenne générale pondérée par le coefficient de chaque matière.
     *
     * @return array{matieres: \Illuminate\Support\Collection, moyenne_generale: float|null}
     */
    public function bulletinDonnees(int $trimestreId): array
    {
        $notes = $this->notes()
            ->where('trimestre_id', $trimestreId)
            ->with('matiere')
            ->get()
            ->groupBy('matiere_id');

        $matieres = $notes->map(function ($notesMatiere) {
            $matiere = $notesMatiere->first()->matiere;
            $totalPondere = $notesMatiere->sum(fn (Note $n) => $n->noteSur20() * $n->coefficient);
            $totalCoefficients = $notesMatiere->sum('coefficient');

            return [
                'matiere' => $matiere,
                'moyenne' => $totalCoefficients > 0 ? round($totalPondere / $totalCoefficients, 2) : null,
            ];
        })->filter(fn ($m) => $m['moyenne'] !== null)->values();

        $totalPondereGeneral = $matieres->sum(fn ($m) => $m['moyenne'] * (float) $m['matiere']->coefficient_defaut);
        $totalCoefficientsGeneral = $matieres->sum(fn ($m) => (float) $m['matiere']->coefficient_defaut);

        return [
            'matieres' => $matieres,
            'moyenne_generale' => $totalCoefficientsGeneral > 0 ? round($totalPondereGeneral / $totalCoefficientsGeneral, 2) : null,
        ];
    }

    public function tauxPresenceTrimestre(int $trimestreId): ?float
    {
        $presences = $this->presences()->where('trimestre_id', $trimestreId)->get();

        if ($presences->isEmpty()) {
            return null;
        }

        $presents = $presences->where('statut', 'present')->count();

        return round(($presents / $presences->count()) * 100, 1);
    }
}
