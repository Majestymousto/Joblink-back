<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Candidate;
use Illuminate\Http\Request;

class ProfileController extends Controller
{
    // Mon profil
    public function show(Request $request)
    {
        $user = $request->user();

        if ($user->isCandidate()) {
            $profile = $user->candidate;
            return response()->json([
                'id'           => $user->id,
                'name'         => $user->name,
                'email'        => $user->email,
                'avatar'       => $user->avatar,
                'role'         => $user->role,
                'titre_poste'  => $profile->titre_poste,
                'bio'          => $profile->bio,
                'telephone'    => $profile->telephone,
                'localisation' => $profile->localisation,
                'competences'  => $profile->competences,
                'cv_path'      => $profile->cv_path,
                'buildcvpro_connected' => !is_null($profile->buildcvpro_token),
            ]);
        }

        if ($user->isEmployer()) {
            $profile = $user->employer;
            return response()->json([
                'id'                   => $user->id,
                'name'                 => $user->name,
                'email'                => $user->email,
                'avatar'               => $user->avatar,
                'role'                 => $user->role,
                'nom_entreprise'       => $profile->nom_entreprise,
                'type_entreprise'      => $profile->type_entreprise,
                'secteur'              => $profile->secteur,
                'description'          => $profile->description,
                'logo'                 => $profile->logo,
                'pays'                 => $profile->pays,
                'ville'                => $profile->ville,
                'adresse'              => $profile->adresse,
                'email_contact'        => $profile->email_contact,
                'telephone'            => $profile->telephone,
                'site_web'             => $profile->site_web,
                'taille'               => $profile->taille,
                'annee_creation'       => $profile->annee_creation,
                'statut'               => $profile->statut,
            ]);
        }
    }

    // Modifier mon profil
    public function update(Request $request)
    {
        $user = $request->user();

        // Mise à jour du nom
        if ($request->name) {
            $user->update(['name' => $request->name]);
        }

        if ($user->isCandidate()) {
            $user->candidate->update([
                'titre_poste'  => $request->titre_poste  ?? $user->candidate->titre_poste,
                'bio'          => $request->bio          ?? $user->candidate->bio,
                'telephone'    => $request->telephone    ?? $user->candidate->telephone,
                'localisation' => $request->localisation ?? $user->candidate->localisation,
                'competences'  => $request->competences  ?? $user->candidate->competences,
            ]);
        }

        if ($user->isEmployer()) {
            $user->employer->update([
                'nom_entreprise'  => $request->nom_entreprise  ?? $user->employer->nom_entreprise,
                'secteur'         => $request->secteur         ?? $user->employer->secteur,
                'description'     => $request->description     ?? $user->employer->description,
                'pays'            => $request->pays            ?? $user->employer->pays,
                'ville'           => $request->ville           ?? $user->employer->ville,
                'adresse'         => $request->adresse         ?? $user->employer->adresse,
                'telephone'       => $request->telephone       ?? $user->employer->telephone,
                'site_web'        => $request->site_web        ?? $user->employer->site_web,
                'type_entreprise' => $request->type_entreprise ?? $user->employer->type_entreprise,
                'taille'          => $request->taille          ?? $user->employer->taille,
                'annee_creation'  => $request->annee_creation  ?? $user->employer->annee_creation,
            ]);
        }

        return response()->json(['message' => 'Profil mis à jour !']);
    }

    // Liste des candidats (entreprise)
    public function candidates(Request $request)
    {
        if (!$request->user()->isEmployer()) {
            return response()->json(['message' => 'Accès réservé aux entreprises.'], 403);
        }

        $candidates = Candidate::with('user')
            ->whereNotNull('titre_poste')
            ->latest()
            ->paginate(12)
            ->through(fn($c) => [
                'id'           => $c->id,
                'name'         => $c->user->name,
                'email'        => $c->user->email,
                'avatar'       => $c->user->avatar,
                'titre_poste'  => $c->titre_poste,
                'localisation' => $c->localisation,
                'competences'  => $c->competences,
            ]);

        return response()->json($candidates);
    }

    // Upload logo entreprise
public function uploadLogo(Request $request)
{
    $request->validate([
        'logo' => 'required|image|mimes:jpeg,png,jpg,webp|max:2048',
    ]);

    $user = $request->user();

    if (!$user->isEmployer()) {
        return response()->json(['message' => 'Accès non autorisé.'], 403);
    }

    $path = $request->file('logo')->store('logos', 'public');

    $user->employer->update(['logo' => $path]);

    return response()->json([
        'message' => 'Logo mis à jour.',
        'logo'    => asset('storage/' . $path),
    ]);
}

    // Profil d'un candidat (entreprise)
    public function candidateProfile(Request $request, $id)
    {
        if (!$request->user()->isEmployer()) {
            return response()->json(['message' => 'Accès réservé aux entreprises.'], 403);
        }

        $candidate = Candidate::with('user')->findOrFail($id);

        return response()->json([
            'data' => [
                'id'           => $candidate->id,
                'name'         => $candidate->user->name,
                'email'        => $candidate->user->email,
                'avatar'       => $candidate->user->avatar,
                'titre_poste'  => $candidate->titre_poste,
                'bio'          => $candidate->bio,
                'localisation' => $candidate->localisation,
                'competences'  => $candidate->competences,
                'telephone'    => $candidate->telephone,
            ],
        ]);
    }
}