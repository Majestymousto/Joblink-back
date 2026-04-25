<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Application;
use App\Models\JobOffer;
use Illuminate\Http\Request;

class ApplicationController extends Controller
{
    // Postuler à une offre (candidat)
    public function apply(Request $request, $id)
    {
        if (!$request->user()->isCandidate()) {
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
            'message'          => 'nullable|string',
            'cv_path'          => 'nullable|string',
            'buildcvpro_cv_id' => 'nullable|string',
        ]);

        $application = Application::create([
            'candidate_id'     => $candidate->id,
            'job_offer_id'     => $id,
            'status'           => 'pending',
            'message'          => $request->message,
            'cv_path'          => $request->cv_path,
            'buildcvpro_cv_id' => $request->buildcvpro_cv_id,
        ]);

        // Incrémenter le compteur de candidatures
        $offer->increment('candidatures_count');

        return response()->json([
            'data'    => $application,
            'message' => 'Candidature envoyée avec succès !',
        ], 201);
    }

    // Mes candidatures (candidat)
    public function myApplications(Request $request)
    {
        if (!$request->user()->isCandidate()) {
            return response()->json(['message' => 'Accès réservé aux candidats.'], 403);
        }

        $candidate = $request->user()->candidate;

        $applications = Application::with('jobOffer.employer')
            ->where('candidate_id', $candidate->id)
            ->latest()
            ->get()
            ->map(fn($app) => [
                'id'       => $app->id,
                'status'   => $app->status,
                'date'     => $app->created_at->format('d M Y'),
                'message'  => $app->message,
                'job'      => [
                    'id'           => $app->jobOffer->id,
                    'titre'        => $app->jobOffer->titre,
                    'type_contrat' => $app->jobOffer->type_contrat,
                    'localisation' => $app->jobOffer->localisation,
                    'entreprise'   => $app->jobOffer->employer->nom_entreprise,
                ],
            ]);

        return response()->json(['data' => $applications]);
    }

    // Retirer une candidature (candidat)
    public function withdraw(Request $request, $id)
    {
        $application = Application::findOrFail($id);
        $candidate   = $request->user()->candidate;

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
        if (!$request->user()->isEmployer()) {
            return response()->json(['message' => 'Accès réservé aux entreprises.'], 403);
        }

        $offer = JobOffer::findOrFail($id);

        if ($offer->employer->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Non autorisé.'], 403);
        }

        $applications = Application::with('candidate.user')
            ->where('job_offer_id', $id)
            ->latest()
            ->get()
            ->map(fn($app) => [
                'id'        => $app->id,
                'status'    => $app->status,
                'date'      => $app->created_at->format('d M Y'),
                'message'   => $app->message,
                'candidat'  => [
                    'id'          => $app->candidate->id,
                    'name'        => $app->candidate->user->name,
                    'email'       => $app->candidate->user->email,
                    'avatar'      => $app->candidate->user->avatar,
                    'titre_poste' => $app->candidate->titre_poste,
                    'localisation'=> $app->candidate->localisation,
                    'competences' => $app->candidate->competences,
                ],
            ]);

        return response()->json(['data' => $applications]);
    }

    // Changer le statut d'une candidature (entreprise)
    public function updateStatus(Request $request, $id)
    {
        if (!$request->user()->isEmployer()) {
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