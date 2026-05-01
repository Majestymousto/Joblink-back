<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Employer;
use Illuminate\Http\Request;

class EntrepriseController extends Controller
{
    // GET /api/entreprises
    public function index(Request $request)
    {
        $entreprises = Employer::query()
            ->where('statut', 'active') // uniquement les entreprises validées
            ->when($request->search, fn($q) => $q
                ->where('nom_entreprise', 'like', "%{$request->search}%")
                ->orWhere('secteur', 'like', "%{$request->search}%"))
            ->when($request->secteur, fn($q) => $q
                ->where('secteur', $request->secteur))
            ->when($request->ville, fn($q) => $q
                ->where('ville', $request->ville))
            ->withCount(['jobOffers' => fn($q) => $q->where('statut', 'active')]) // nb d'offres actives
            ->latest()
            ->paginate($request->per_page ?? 12)
            ->through(fn($e) => [
                'id'              => $e->id,
                'nom_entreprise'  => $e->nom_entreprise,
                'type_entreprise' => $e->type_entreprise,
                'secteur'         => $e->secteur,
                'description'     => $e->description,
                'logo'            => $e->logo,
                'ville'           => $e->ville,
                'pays'            => $e->pays,
                'site_web'        => $e->site_web,
                'annee_creation'  => $e->annee_creation,
                'nombre_employes' => $e->nombre_employes,
                'offres_actives'  => $e->job_offers_count,
            ]);

        return response()->json($entreprises);
    }

    // GET /api/entreprises/{id}
    public function show($id)
    {
        $entreprise = Employer::where('statut', 'active')->find($id);

        if (!$entreprise) {
            return response()->json(['message' => 'Entreprise introuvable.'], 404);
        }

        // Offres actives de l'entreprise
        $offres = $entreprise->jobOffers()
            ->where('statut', 'active')
            ->latest()
            ->get(['id', 'titre', 'type_contrat', 'localisation', 'created_at']);

        return response()->json([
            'data' => [
                'id'              => $entreprise->id,
                'nom_entreprise'  => $entreprise->nom_entreprise,
                'type_entreprise' => $entreprise->type_entreprise,
                'secteur'         => $entreprise->secteur,
                'description'     => $entreprise->description,
                'logo'            => $entreprise->logo,
                'ville'           => $entreprise->ville,
                'pays'            => $entreprise->pays,
                'adresse'         => $entreprise->adresse,
                'email_contact'   => $entreprise->email_contact,
                'telephone'       => $entreprise->telephone,
                'site_web'        => $entreprise->site_web,
                'annee_creation'  => $entreprise->annee_creation,
                'nombre_employes' => $entreprise->nombre_employes,
                'offres'          => $offres,
            ]
        ]);
    }
}