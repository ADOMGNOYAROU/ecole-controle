<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Matiere extends Model
{
    use HasFactory;

    protected $table = 'matieres';

    protected $fillable = [
        'nom',
        'code',
        'coefficient',
    ];

    protected $casts = [
        'coefficient' => 'decimal:1',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * RELATION : Une matière est enseignée par plusieurs enseignants
     */
    public function enseignants()
    {
        return $this->belongsToMany(
            Enseignant::class,
            'enseignant_matiere_classe',
            'matiere_id',
            'enseignant_id'
        )->withPivot('classe_id')->withTimestamps();
    }

    /**
     * RELATION : Une matière a plusieurs notes
     */
    public function notes()
    {
        return $this->hasMany(Note::class);
    }
}