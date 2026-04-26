<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\JobOffer;
use App\Models\SavedJob;
use Illuminate\Http\Request;

class JobOfferController extends Controller
{
    // Liste publique des offres avec filtres
    public function index(Request $request)
    {
        $query = JobOffer::with('employer')->where('statut', 'active');

        // Recherche par mot clé
        // Après
if ($request->get('query')) {
    $search = $request->get('query');
    $query->where(function($q) use ($search) {
        $q->where('titre', 'like', '%' . $search . '%')
          ->orWhere('description', 'like', '%' . $search . '%');
    });
}

        // Filtre par localisation
        if ($request->localisation) {
            $query->where('localisation', 'like', "%{$request->localisation}%");
        }

        // Filtre par type de contrat
        if ($request->type_contrat) {
            $query->whereIn('type_contrat', explode(',', $request->type_contrat));
        }

        // Filtre par secteur
        if ($request->secteur) {
            $query->whereIn('secteur', explode(',', $request->secteur));
        }

        // Tri
        $sortBy = $request->sort ?? 'recent';
        if ($sortBy === 'recent') {
            $query->latest();
        } elseif ($sortBy === 'salary') {
            $query->orderBy('salaire', 'desc');
        }

        $offers = $query->paginate(8);

        return response()->json($offers);
    }

    // Détail d'une offre
    public function show($id)
    {
        $offer = JobOffer::with('employer')->findOrFail($id);
        $offer->increment('vues');

        // Offres similaires
        $similar = JobOffer::where('secteur', $offer->secteur)
            ->where('id', '!=', $offer->id)
            ->where('statut', 'active')
            ->limit(3)
            ->get();

        return response()->json([
            'data'    => $offer,
            'similar' => $similar,
        ]);
    }

    // Créer une offre (employeur)
    public function store(Request $request)
    {
        if (!$request->user()->isEmployer()) {
            return response()->json(['message' => 'Non autorisé.'], 403);
        }

        $employer = $request->user()->employer;

        if (!$employer || !$employer->isActive()) {
            return response()->json(['message' => 'Votre compte employeur n\'est pas encore validé.'], 403);
        }

        $request->validate([
            'titre'        => 'required|string|max:255',
            'description'  => 'required|string',
            'type_contrat' => 'required|in:cdi,cdd,stage,freelance,alternance',
        ]);

        $offer = JobOffer::create([
            'employer_id'     => $employer->id,
            'titre'           => $request->titre,
            'excerpt'         => $request->excerpt,
            'description'     => $request->description,
            'requirements'    => $request->requirements ?? [],
            'perks'           => $request->perks ?? [],
            'type_contrat'    => $request->type_contrat,
            'secteur'         => $request->secteur,
            'localisation'    => $request->localisation,
            'salaire'         => $request->salaire,
            'experience'      => $request->experience,
            'niveau_etude'    => $request->niveau_etude,
            'competences'     => $request->competences ?? [],
            'date_expiration' => $request->date_expiration,
            'statut'          => 'active',
        ]);

        return response()->json([
            'data'    => $offer,
            'message' => 'Offre créée avec succès !',
        ], 201);
    }

    // Modifier une offre
    public function update(Request $request, $id)
    {
        $offer = JobOffer::findOrFail($id);
        $employer = $request->user()->employer;

        if (!$employer || $offer->employer_id !== $employer->id) {
            return response()->json(['message' => 'Non autorisé.'], 403);
        }

        $offer->update($request->only([
            'titre', 'excerpt', 'description', 'requirements', 'perks',
            'type_contrat', 'secteur', 'localisation', 'salaire',
            'experience', 'niveau_etude', 'competences', 'date_expiration', 'statut',
        ]));

        return response()->json(['message' => 'Offre mise à jour !']);
    }

    // Supprimer une offre
    public function destroy(Request $request, $id)
    {
        $offer = JobOffer::findOrFail($id);
        $employer = $request->user()->employer;

        if (!$employer || $offer->employer_id !== $employer->id) {
            return response()->json(['message' => 'Non autorisé.'], 403);
        }

        $offer->delete();
        return response()->json(['message' => 'Offre supprimée !']);
    }

    // Sauvegarder une offre
    public function save(Request $request, $id)
    {
        if (!$request->user()->isCandidate()) {
            return response()->json(['message' => 'Non autorisé.'], 403);
        }

        $candidate = $request->user()->candidate;

        SavedJob::firstOrCreate([
            'candidate_id' => $candidate->id,
            'job_offer_id' => $id,
        ]);

        return response()->json(['message' => 'Offre sauvegardée !']);
    }

    // Retirer une offre sauvegardée
    public function unsave(Request $request, $id)
    {
        if (!$request->user()->isCandidate()) {
            return response()->json(['message' => 'Non autorisé.'], 403);
        }

        $candidate = $request->user()->candidate;

        SavedJob::where('candidate_id', $candidate->id)
            ->where('job_offer_id', $id)
            ->delete();

        return response()->json(['message' => 'Offre retirée des sauvegardes !']);
    }

    // Liste des offres sauvegardées
    public function savedJobs(Request $request)
    {
        if (!$request->user()->isCandidate()) {
            return response()->json(['message' => 'Non autorisé.'], 403);
        }

        $candidate = $request->user()->candidate;

        $saved = SavedJob::with('jobOffer.employer')
            ->where('candidate_id', $candidate->id)
            ->latest()
            ->get()
            ->map(fn($s) => $s->jobOffer);

        return response()->json(['data' => $saved]);
    }
}