<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Employer extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'nom_entreprise',
        'type_entreprise',
        'secteur',
        'description',
        'logo',
        'pays',
        'ville',
        'adresse',
        'email_contact',
        'telephone',
        'site_web',
        'responsable_nom',
        'responsable_fonction',
        'responsable_telephone',
        'responsable_email',
        'numero_identification',
        'annee_creation',
        'nombre_employes',
        'statut',
        'raison_rejet',
    ];

    // Relations
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function jobOffers()
    {
        return $this->hasMany(JobOffer::class);
    }

    // Helper
    public function isActive(): bool
    {
        return $this->statut === 'active';
    }
}