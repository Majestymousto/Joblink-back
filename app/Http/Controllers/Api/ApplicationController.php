<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Application;
use App\Models\Experience;
use App\Models\JobOffer;
use Illuminate\Http\Request;

class ApplicationController extends Controller
{
    // Postuler à une offre (candidat)
    public function apply(Request $request, $id)
    {
        if (! $request->user()->isCandidate()) {
            return response()->json(['message' => 'Accès réservé aux candidats.'], 403);
        }

        $candidate = $request->user()->candidate;
        $offer = JobOffer::findOrFail($id);

        // Vérifier si déjà postulé
        $existing = Application::where('candidate_id', $candidate->id)
            ->where('job_offer_id', $id)
            ->first();

        if ($existing) {
            return response()->json(['message' => 'Vous avez déjà postulé à cette offre.'], 409);
        }

        $request->validate([
            'message' => 'nullable|string',
            'cv_path' => 'nullable|string',
            'buildcvpro_cv_id' => 'nullable|string',
        ]);

        $application = Application::create([
            'candidate_id' => $candidate->id,
            'job_offer_id' => $id,
            'status' => 'pending',
            'message' => $request->message,
            'cv_path' => $request->cv_path,
            'buildcvpro_cv_id' => $request->buildcvpro_cv_id,
        ]);

        // Incrémenter le compteur de candidatures
        $offer->increment('candidatures_count');

        return response()->json([
            'data' => $application,
            'message' => 'Candidature envoyée avec succès !',
        ], 201);
    }

    // Mes candidatures (candidat)
    public function myApplications(Request $request)
    {
        if (! $request->user()->isCandidate()) {
            return response()->json(['message' => 'Accès réservé aux candidats.'], 403);
        }

        $candidate = $request->user()->candidate;

        $applications = Application::with('jobOffer.employer')
            ->where('candidate_id', $candidate->id)
            ->latest()
            ->get()
            ->map(fn ($app) => [
                'id' => $app->id,
                'status' => $app->status,
                'date' => $app->created_at->format('d M Y'),
                'message' => $app->message,
                'job' => [
                    'id' => $app->jobOffer->id,
                    'titre' => $app->jobOffer->titre,
                    'type_contrat' => $app->jobOffer->type_contrat,
                    'localisation' => $app->jobOffer->localisation,
                    'entreprise' => $app->jobOffer->employer->nom_entreprise,
                ],
            ]);

        return response()->json(['data' => $applications]);
    }

    // Retirer une candidature (candidat)
    public function withdraw(Request $request, $id)
    {
        $application = Application::findOrFail($id);
        $candidate = $request->user()->candidate;

        if ($application->candidate_id !== $candidate->id) {
            return response()->json(['message' => 'Non autorisé.'], 403);
        }

        if ($application->status !== 'pending') {
            return response()->json(['message' => 'Impossible de retirer cette candidature.'], 400);
        }

        $application->jobOffer->decrement('candidatures_count');
        $application->delete();

        return response()->json(['message' => 'Candidature retirée.']);
    }

    // Voir les candidats d'une offre (entreprise)
    public function jobCandidates(Request $request, $id)
    {
        if (! $request->user()->isEmployer()) {
            return response()->json(['message' => 'Accès réservé aux entreprises.'], 403);
        }

        $offer = JobOffer::findOrFail($id);

        if ($offer->employer->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Non autorisé.'], 403);
        }

        $sortBy = $request->query('sort_by', 'date');
        $sortDir = strtolower($request->query('sort_dir', 'desc')) === 'asc' ? 'asc' : 'desc';

        $query = Application::with(['candidate.user', 'candidate.experiences'])
            ->withCount('candidate as experiences_count')
            ->where('job_offer_id', $id);

        // Filtre par compétence
        if ($request->filled('competence')) {
            $competence = mb_strtolower($request->query('competence'));
            $query->whereHas('candidate', function ($q) use ($competence) {
                $q->whereRaw('LOWER(competences) LIKE ?', ["%{$competence}%"]);
            });
        }

        // Filtre par localisation
        if ($request->filled('localisation')) {
            $localisation = $request->query('localisation');
            $query->whereHas('candidate', function ($q) use ($localisation) {
                $q->where('localisation', 'like', "%{$localisation}%");
            });
        }

        // Tri
        if ($sortBy === 'experiences') {
            $query->orderBy(
                Experience::selectRaw('COUNT(*)')
                    ->whereColumn('candidate_id', 'applications.candidate_id'),
                $sortDir
            );
        } else {
            $query->orderBy('applications.created_at', $sortDir);
        }

        $applications = $query->get();

        // Marquer toutes les candidatures non ouvertes comme ouvertes
        $applications->where('is_opened', false)->each(fn ($app) => $app->update(['is_opened' => true]));

        return response()->json([
            'data' => $applications->map(fn ($app) => [
                'id' => $app->id,
                'status' => $app->status,
                'is_opened' => $app->is_opened,
                'date' => $app->created_at->format('d M Y'),
                'message' => $app->message,
                'candidat' => [
                    'id' => $app->candidate->id,
                    'name' => $app->candidate->user->name,
                    'email' => $app->candidate->user->email,
                    'avatar' => $app->candidate->user->avatar,
                    'titre_poste' => $app->candidate->titre_poste,
                    'localisation' => $app->candidate->localisation,
                    'competences' => $app->candidate->competences,
                    'nb_experiences' => $app->candidate->experiences->count(),
                ],
            ]),
        ]);
    }

    // Modifier une candidature (candidat) — seulement si non ouverte
    public function update(Request $request, $id)
    {
        if (! $request->user()->isCandidate()) {
            return response()->json(['message' => 'Accès réservé aux candidats.'], 403);
        }

        $application = Application::findOrFail($id);
        $candidate = $request->user()->candidate;

        if ($application->candidate_id !== $candidate->id) {
            return response()->json(['message' => 'Non autorisé.'], 403);
        }

        if ($application->is_opened) {
            return response()->json([
                'message' => 'Vous ne pouvez plus modifier cette candidature, elle a déjà été consultée par l\'employeur.',
            ], 403);
        }

        $request->validate([
            'message' => 'nullable|string',
            'cv_path' => 'nullable|string',
            'buildcvpro_cv_id' => 'nullable|string',
        ]);

        $application->update($request->only(['message', 'cv_path', 'buildcvpro_cv_id']));

        return response()->json([
            'message' => 'Candidature modifiée avec succès.',
            'data' => $application->fresh(),
        ]);
    }

    // Changer le statut d'une candidature (entreprise)
    public function updateStatus(Request $request, $id)
    {
        if (! $request->user()->isEmployer()) {
            return response()->json(['message' => 'Accès réservé aux entreprises.'], 403);
        }

        $request->validate([
            'status' => 'required|in:pending,interview,accepted,rejected',
        ]);

        $application = Application::findOrFail($id);

        if ($application->jobOffer->employer->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Non autorisé.'], 403);
        }

        $application->update(['status' => $request->status]);

        return response()->json(['message' => 'Statut mis à jour !']);
    }
}
