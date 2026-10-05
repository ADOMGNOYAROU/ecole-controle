<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Note du super administrateur sur une école. Volontairement sans BelongsToEcole :
 * seul le super administrateur y a accès.
 */
class NoteInterne extends Model
{
    protected $table = 'notes_internes';

    protected $fillable = ['ecole_id', 'auteur_id', 'contenu'];

    public function ecole(): BelongsTo
    {
        return $this->belongsTo(Ecole::class);
    }

    public function auteur(): BelongsTo
    {
        return $this->belongsTo(User::class, 'auteur_id');
    }
}
