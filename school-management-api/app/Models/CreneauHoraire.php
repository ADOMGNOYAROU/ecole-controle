<?php

namespace App\Models;

use App\Models\Concerns\BelongsToEcole;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CreneauHoraire extends Model
{
    use HasFactory, BelongsToEcole;

    protected $table = 'creneaux_horaires';

    protected $fillable = [
        'ecole_id',
        'classe_id',
        'matiere_id',
        'enseignant_id',
        'jour_semaine',
        'heure_debut',
        'heure_fin',
        'salle',
    ];

    public const JOURS = [
        1 => 'Lundi',
        2 => 'Mardi',
        3 => 'Mercredi',
        4 => 'Jeudi',
        5 => 'Vendredi',
        6 => 'Samedi',
    ];

    public function classe(): BelongsTo
    {
        return $this->belongsTo(Classe::class);
    }

    public function matiere(): BelongsTo
    {
        return $this->belongsTo(Matiere::class);
    }

    public function enseignant(): BelongsTo
    {
        return $this->belongsTo(Enseignant::class);
    }

    public function nomJour(): string
    {
        return self::JOURS[$this->jour_semaine] ?? '';
    }
}
