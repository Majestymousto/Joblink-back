<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Experience;
use App\Models\Formation;
use Illuminate\Http\Request;

class CandidateProfileController extends Controller
{
    private function candidate(Request $request)
    {
        return $request->user()->candidate;
    }

    // ─── EXPERIENCES ─────────────────────────────────────

    // GET /api/candidat/experiences
    public function indexExperiences(Request $request)
    {
        return response()->json([
            'data' => $this->candidate($request)->experiences,
        ]);
    }

    // POST /api/candidat/experiences
    public function storeExperience(Request $request)
    {
        $data = $request->validate([
            'intitule_poste' => 'required|string|max:255',
            'entreprise'     => 'required|string|max:255',
            'date_debut'     => 'required|date',
            'date_fin'       => 'nullable|date|after_or_equal:date_debut',
            'poste_actuel'   => 'boolean',
            'description'    => 'nullable|string',
        ]);

        // Si poste actuel, on efface la date de fin
        if (!empty($data['poste_actuel'])) {
            $data['date_fin'] = null;
        }

        $experience = $this->candidate($request)->experiences()->create($data);

        return response()->json([
            'message' => 'Expérience ajoutée avec succès.',
            'data'    => $experience,
        ], 201);
    }

    // PUT /api/candidat/experiences/{id}
    public function updateExperience(Request $request, $id)
    {
        $experience = $this->candidate($request)->experiences()->find($id);

        if (!$experience) {
            return response()->json(['message' => 'Expérience introuvable.'], 404);
        }

        $data = $request->validate([
            'intitule_poste' => 'sometimes|string|max:255',
            'entreprise'     => 'sometimes|string|max:255',
            'date_debut'     => 'sometimes|date',
            'date_fin'       => 'nullable|date|after_or_equal:date_debut',
            'poste_actuel'   => 'boolean',
            'description'    => 'nullable|string',
        ]);

        if (!empty($data['poste_actuel'])) {
            $data['date_fin'] = null;
        }

        $experience->update($data);

        return response()->json([
            'message' => 'Expérience mise à jour.',
            'data'    => $experience,
        ]);
    }

    // DELETE /api/candidat/experiences/{id}
    public function destroyExperience(Request $request, $id)
    {
        $experience = $this->candidate($request)->experiences()->find($id);

        if (!$experience) {
            return response()->json(['message' => 'Expérience introuvable.'], 404);
        }

        $experience->delete();

        return response()->json(['message' => 'Expérience supprimée.']);
    }

    // ─── FORMATIONS ──────────────────────────────────────

    // GET /api/candidat/formations
    public function indexFormations(Request $request)
    {
        return response()->json([
            'data' => $this->candidate($request)->formations,
        ]);
    }

    // POST /api/candidat/formations
    public function storeFormation(Request $request)
    {
        $data = $request->validate([
            'diplome'       => 'required|string|max:255',
            'etablissement' => 'required|string|max:255',
            'annee_debut'   => 'nullable|integer|min:1950|max:' . date('Y'),
            'annee_fin'     => 'nullable|integer|min:1950|max:' . (date('Y') + 6) . '|gte:annee_debut',
        ]);

        $formation = $this->candidate($request)->formations()->create($data);

        return response()->json([
            'message' => 'Formation ajoutée avec succès.',
            'data'    => $formation,
        ], 201);
    }

    // PUT /api/candidat/formations/{id}
    public function updateFormation(Request $request, $id)
    {
        $formation = $this->candidate($request)->formations()->find($id);

        if (!$formation) {
            return response()->json(['message' => 'Formation introuvable.'], 404);
        }

        $data = $request->validate([
            'diplome'       => 'sometimes|string|max:255',
            'etablissement' => 'sometimes|string|max:255',
            'annee_debut'   => 'nullable|integer|min:1950|max:' . date('Y'),
            'annee_fin'     => 'nullable|integer|min:1950|max:' . (date('Y') + 6) . '|gte:annee_debut',
        ]);

        $formation->update($data);

        return response()->json([
            'message' => 'Formation mise à jour.',
            'data'    => $formation,
        ]);
    }

    // DELETE /api/candidat/formations/{id}
    public function destroyFormation(Request $request, $id)
    {
        $formation = $this->candidate($request)->formations()->find($id);

        if (!$formation) {
            return response()->json(['message' => 'Formation introuvable.'], 404);
        }

        $formation->delete();

        return response()->json(['message' => 'Formation supprimée.']);
    }
}