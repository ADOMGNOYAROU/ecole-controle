<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
use Carbon\Carbon;

class Etudiant extends Authenticatable
{
    use HasFactory, Notifiable, SoftDeletes;

    /**
     * Les attributs qui sont mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'matricule',
        'prenom',
        'nom',
        'date_naissance',
        'genre',
        'lieu_naissance',
        'adresse',
        'telephone',
        'email',
        'photo',
        'groupe_sanguin',
        'allergies',
        'antecedents_medicaux',
        'traitement_medical',
        'classe_id',
        'username',
        'password',
        'statut',
        'date_inscription',
        'date_sortie',
        'motif_sortie',
        'nationalite',
        'religion',
        'groupe_ethnique',
        'langue_maternelle',
        'contact_urgence_nom',
        'contact_urgence_telephone',
        'contact_urgence_lien',
        'est_boursier',
        'numero_bourse',
        'notes_administratives',
    ];

    /**
     * Les attributs qui doivent être cachés pour la sérialisation.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Les attributs qui doivent être convertis.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'date_naissance' => 'date',
        'date_inscription' => 'date',
        'date_sortie' => 'date',
        'est_boursier' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    /**
     * Les attributs par défaut pour les nouveaux modèles.
     *
     * @var array
     */
    protected $attributes = [
        'statut' => 'actif',
        'nationalite' => 'Sénégalaise',
        'est_boursier' => false,
    ];

    /**
     * Les relations avec les autres modèles.
     */
    
    // Relation avec la classe
    public function classe()
    {
        return $this->belongsTo(Classe::class);
    }

    // Relation avec le parent
    /**
     * Relation plusieurs-à-plusieurs avec le modèle ParentModel.
     */
    public function parents()
    {
        return $this->belongsToMany(ParentModel::class, 'etudiant_parent')
            ->withPivot('lien_parente', 'est_representant_legal')
            ->withTimestamps();
    }

    /**
     * Obtenir le représentant légal de l'étudiant.
     */
    public function representantLegal()
    {
        return $this->parents()
            ->wherePivot('est_representant_legal', true)
            ->first();
    }

    // Relation avec les présences
    public function presences()
    {
        return $this->hasMany(Presence::class);
    }

    // Relation avec les notes
    public function notes()
    {
        return $this->hasMany(Note::class);
    }

    // Relation avec les paiements
    public function paiements()
    {
        return $this->hasMany(Paiement::class);
    }

    /**
     * Les accesseurs
     */
    
    // Nom complet de l'étudiant
    public function getNomCompletAttribute()
    {
        return "{$this->prenom} {$this->nom}";
    }

    // Âge de l'étudiant
    public function getAgeAttribute()
    {
        return Carbon::parse($this->date_naissance)->age;
    }

    // Photo de profil ou photo par défaut
    public function getPhotoUrlAttribute()
    {
        if ($this->photo) {
            return asset('storage/' . $this->photo);
        }
        return asset('images/default-avatar.png');
    }

    // Statut formaté
    public function getStatutFormateAttribute()
    {
        return [
            'actif' => 'Actif',
            'inactif' => 'Inactif',
            'suspendu' => 'Suspendu',
            'diplomé' => 'Diplômé',
            'abandon' => 'Abandon',
        ][$this->statut] ?? $this->statut;
    }

    /**
     * Les mutateurs
     */
    
    // Mettre en majuscule le nom de famille
    public function setNomAttribute($value)
    {
        $this->attributes['nom'] = mb_strtoupper($value);
    }

    // Mettre en minuscule l'email
    public function setEmailAttribute($value)
    {
        $this->attributes['email'] = strtolower($value);
    }

    // Générer un nom d'utilisateur si non fourni
    public function setUsernameAttribute($value)
    {
        if (empty($value)) {
            $base = Str::slug($this->prenom . ' ' . $this->nom);
            $username = $base;
            $count = 1;
            
            while (static::where('username', $username)->exists()) {
                $username = $base . $count++;
            }
            
            $this->attributes['username'] = $username;
        } else {
            $this->attributes['username'] = $value;
        }
    }

    // Générer un mot de passe par défaut si non fourni
    public function setPasswordAttribute($value)
    {
        if (empty($value)) {
            $value = Str::random(8); // Générer un mot de passe aléatoire
            // Ici, vous pourriez envoyer un email avec le mot de passe
        }
        $this->attributes['password'] = bcrypt($value);
    }

    /**
     * Les scopes de requête
     */
    
    // Étudiants actifs
    public function scopeActif($query)
    {
        return $query->where('statut', 'actif');
    }

    // Étudiants d'une classe spécifique
    public function scopeDeLaClasse($query, $classeId)
    {
        return $query->where('classe_id', $classeId);
    }

    // Recherche par nom ou prénom
    public function scopeRechercher($query, $terme)
    {
        return $query->where('nom', 'like', "%{$terme}%")
                    ->orWhere('prenom', 'like', "%{$terme}%")
                    ->orWhere('matricule', 'like', "%{$terme}%");
    }

    // Étudiants avec des présences à une date donnée
    public function scopeAvecPresencesALaDate($query, $date)
    {
        return $query->whereHas('presences', function($q) use ($date) {
            $q->whereDate('date', $date);
        });
    }

    /**
     * Méthodes utilitaires
     */
    
    // Vérifier si l'étudiant est actif
    public function estActif()
    {
        return $this->statut === 'actif';
    }

    // Obtenir la moyenne générale de l'étudiant
    public function moyenneGenerale()
    {
        return $this->notes()->avg('valeur') ?? 0;
    }

    // Obtenir le nombre d'absences sur une période
    public function nombreAbsences($debut, $fin)
    {
        return $this->presences()
                   ->where('est_present', false)
                   ->whereBetween('date', [$debut, $fin])
                   ->count();
    }

    // Générer un nouveau matricule
    public static function genererMatricule()
    {
        $prefixe = 'ETU' . date('y');
        $dernier = static::where('matricule', 'like', $prefixe . '%')
                        ->orderBy('matricule', 'desc')
                        ->first();
        
        $numero = 1;
        if ($dernier) {
            $dernierNumero = (int) substr($dernier->matricule, 5);
            $numero = $dernierNumero + 1;
        }
        
        return $prefixe . str_pad($numero, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Événements du modèle
     */
    
    protected static function boot()
    {
        parent::boot();

        // Avant de créer un nouvel étudiant
        static::creating(function ($etudiant) {
            if (empty($etudiant->matricule)) {
                $etudiant->matricule = static::genererMatricule();
            }
            
            if (empty($etudiant->date_inscription)) {
                $etudiant->date_inscription = now();
            }
        });

        // Après avoir créé un nouvel étudiant
        static::created(function ($etudiant) {
            // Ici, vous pourriez envoyer un email de bienvenue
            // ou créer des enregistrements associés
        });

        // Avant de supprimer un étudiant
        static::deleting(function ($etudiant) {
            if ($etudiant->isForceDeleting()) {
                // Supprimer les fichiers associés (photo de profil, etc.)
                if ($etudiant->photo) {
                    \Storage::delete($etudiant->photo);
                }
            }
        });
    }
}
