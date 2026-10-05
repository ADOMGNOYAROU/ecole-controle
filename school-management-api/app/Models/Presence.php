<?php

namespace App\Models;

use App\Models\Concerns\Auditable;
use App\Models\Concerns\BelongsToEcole;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Presence extends Model
{
    use Auditable, BelongsToEcole, HasFactory;

    /** Les créations sont très nombreuses : seules modifications et suppressions sont journalisées. */
    public const AUDIT_IGNORE = ['creation'];

    protected $fillable = [
        'ecole_id',
        'eleve_id',
        'classe_id',
        'enseignant_id',
        'trimestre_id',
        'date',
        'statut',
        'motif',
    ];

    protected $casts = [
        'date' => 'date',
    ];

    public const STATUT_PRESENT = 'present';

    public const STATUT_ABSENT = 'absent';

    public const STATUT_RETARD = 'retard';

    public function eleve(): BelongsTo
    {
        return $this->belongsTo(Eleve::class);
    }

    public function classe(): BelongsTo
    {
        return $this->belongsTo(Classe::class);
    }

    public function enseignant(): BelongsTo
    {
        return $this->belongsTo(Enseignant::class);
    }

    public function trimestre(): BelongsTo
    {
        return $this->belongsTo(Trimestre::class);
    }
}
