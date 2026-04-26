<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class CvController extends Controller
{
    // Uploader un CV
    public function upload(Request $request)
    {
        if (!$request->user()->isCandidate()) {
            return response()->json(['message' => 'Accès réservé aux candidats.'], 403);
        }

        $request->validate([
            'cv' => 'required|file|mimes:pdf|max:5120', // max 5MB
        ]);

        $candidate = $request->user()->candidate;

        // Supprimer l'ancien CV s'il existe
        if ($candidate->cv_path) {
            \Illuminate\Support\Facades\Storage::disk('public')->delete($candidate->cv_path);
        }

        // Stocker le nouveau CV
        $path = $request->file('cv')->store('cvs', 'public');

        // Mettre à jour le profil candidat
        $candidate->update(['cv_path' => $path]);

        return response()->json([
            'message' => 'CV uploadé avec succès !',
            'cv_path' => $path,
            'cv_url'  => asset('storage/' . $path),
        ], 201);
    }

    // Supprimer le CV
    public function delete(Request $request)
    {
        if (!$request->user()->isCandidate()) {
            return response()->json(['message' => 'Accès réservé aux candidats.'], 403);
        }

        $candidate = $request->user()->candidate;

        if (!$candidate->cv_path) {
            return response()->json(['message' => 'Aucun CV à supprimer.'], 404);
        }

        \Illuminate\Support\Facades\Storage::disk('public')->delete($candidate->cv_path);
        $candidate->update(['cv_path' => null]);

        return response()->json(['message' => 'CV supprimé avec succès !']);
    }

    // Télécharger le CV
    public function download(Request $request)
    {
        if (!$request->user()->isCandidate()) {
            return response()->json(['message' => 'Accès réservé aux candidats.'], 403);
        }

        $candidate = $request->user()->candidate;

        if (!$candidate->cv_path) {
            return response()->json(['message' => 'Aucun CV disponible.'], 404);
        }

        return response()->json([
            'cv_url' => asset('storage/' . $candidate->cv_path),
        ]);
    }
}