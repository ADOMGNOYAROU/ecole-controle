<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Note extends Model
{
    use HasFactory;

    protected $table = 'notes';

    protected $fillable = [
        'eleve_id',
        'matiere_id',
        'classe_id',
        'type_evaluation',
        'note',
        'note_sur',
        'date_evaluation',
        'trimestre',
        'enseignant_id',
        'observation',
    ];

    protected $casts = [
        'note' => 'decimal:2',
        'note_sur' => 'decimal:2',
        'date_evaluation' => 'date',
        'trimestre' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * RELATION : Une note appartient à un élève
     */
    public function eleve()
    {
        return $this->belongsTo(Eleve::class);
    }

    /**
     * RELATION : Une note appartient à une matière
     */
    public function matiere()
    {
        return $this->belongsTo(Matiere::class);
    }

    /**
     * RELATION : Une note appartient à une classe
     */
    public function classe()
    {
        return $this->belongsTo(Classe::class);
    }

    /**
     * RELATION : Une note est saisie par un enseignant
     */
    public function enseignant()
    {
        return $this->belongsTo(Enseignant::class);
    }

    /**
     * Calculer la note sur 20
     */
    public function getNoteSur20Attribute()
    {
        return ($this->note / $this->note_sur) * 20;
    }

    /**
     * Scope : Filtrer par trimestre
     */
    public function scopeByTrimestre($query, $trimestre)
    {
        return $query->where('trimestre', $trimestre);
    }

    /**
     * Scope : Filtrer par type d'évaluation
     */
    public function scopeByType($query, $type)
    {
        return $query->where('type_evaluation', $type);
    }
}