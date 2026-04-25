<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JobOffer extends Model
{
    use HasFactory;

    protected $fillable = [
        'employer_id',
        'titre',
        'excerpt',
        'description',
        'requirements',
        'perks',
        'type_contrat',
        'secteur',
        'localisation',
        'salaire',
        'experience',
        'niveau_etude',
        'competences',
        'date_expiration',
        'statut',
        'vues',
        'candidatures_count',
    ];

    protected $casts = [
        'requirements'   => 'array',
        'perks'          => 'array',
        'competences'    => 'array',
        'date_expiration'=> 'date',
    ];

    // Relations
    public function employer()
    {
        return $this->belongsTo(Employer::class);
    }

    public function applications()
    {
        return $this->hasMany(Application::class);
    }

    public function savedByUsers()
    {
        return $this->hasMany(SavedJob::class);
    }

    // Helper
    public function isActive(): bool
    {
        return $this->statut === 'active';
    }
}