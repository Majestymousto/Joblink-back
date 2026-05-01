<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class BuildCVProController extends Controller
{
    private string $buildCVBaseUrl;

public function __construct()
{
    $this->buildCVBaseUrl = env('BUILDCVPRO_URL', 'https://buildcvpro.com/api');
}
    // Connecter son compte BuildCVPro
    public function connect(Request $request)
    {
        if (!$request->user()->isCandidate()) {
            return response()->json(['message' => 'Accès réservé aux candidats.'], 403);
        }

        $request->validate([
            'email'    => 'required|email',
            'password' => 'required|string',
        ]);

        // Appel à l'API BuildCVPro pour obtenir un token
        try {
            $response = Http::withHeaders([
                'Accept'       => 'application/json',
                'Content-Type' => 'application/json',
                'X-Source'     => 'web-memoire',
            ])->post($this->buildCVBaseUrl . '/login', [
                'email'    => $request->email,
                'password' => $request->password,
            ]);

            if (!$response->successful()) {
                return response()->json([
                    'message' => 'Identifiants BuildCVPro invalides.',
                ], 401);
            }

            $data  = $response->json();
            $token = $data['token'];

            // Stocker le token dans le profil candidat
            $candidate = $request->user()->candidate;
            $candidate->update([
                'buildcvpro_token' => $token,
                'buildcvpro_email' => $request->email,
            ]);

            return response()->json([
                'message' => 'Compte BuildCVPro connecté avec succès !',
                'email'   => $request->email,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Impossible de contacter BuildCVPro.',
            ], 500);
        }
    }

    // Récupérer les CVs depuis BuildCVPro
    public function getCvs(Request $request)
    {
        if (!$request->user()->isCandidate()) {
            return response()->json(['message' => 'Accès réservé aux candidats.'], 403);
        }

        $candidate = $request->user()->candidate;

        if (!$candidate->buildcvpro_token) {
            return response()->json([
                'message' => 'Compte BuildCVPro non connecté.',
            ], 400);
        }

        try {
            $response = Http::withHeaders([
                'Accept'        => 'application/json',
                'Authorization' => 'Bearer ' . $candidate->buildcvpro_token,
                'X-Source'      => 'web-memoire',
            ])->get($this->buildCVBaseUrl . '/resumes');

            if (!$response->successful()) {
                return response()->json([
                    'message' => 'Token BuildCVPro expiré. Veuillez vous reconnecter.',
                ], 401);
            }

            return response()->json($response->json());

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Impossible de contacter BuildCVPro.',
            ], 500);
        }
    }

    // Déconnecter BuildCVPro
    public function disconnect(Request $request)
    {
        if (!$request->user()->isCandidate()) {
            return response()->json(['message' => 'Accès réservé aux candidats.'], 403);
        }

        $request->user()->candidate->update([
            'buildcvpro_token' => null,
            'buildcvpro_email' => null,
        ]);

        return response()->json(['message' => 'Compte BuildCVPro déconnecté.']);
    }

    // Vérifier si email existe sur BuildCVPro
    public function check(Request $request)
    {
        if (!$request->user()->isCandidate()) {
            return response()->json(['message' => 'Accès réservé aux candidats.'], 403);
        }

        $email = $request->user()->email;

        try {
            $response = Http::withHeaders([
                'Accept'       => 'application/json',
                'Content-Type' => 'application/json',
            ])->post($this->buildCVBaseUrl . '/check-email', [
                'email' => $email,
            ]);

            return response()->json($response->json());

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Impossible de contacter BuildCVPro.',
            ], 500);
        }
    }

    // GET /api/buildcvpro/cvs/{id}
public function showCv(Request $request, $id)
{
    if (!$request->user()->isCandidate()) {
        return response()->json(['message' => 'Accès réservé aux candidats.'], 403);
    }

    $candidate = $request->user()->candidate;

    if (!$candidate->buildcvpro_token) {
        return response()->json(['message' => 'Compte BuildCVPro non connecté.'], 400);
    }

    try {
        // Récupérer les détails du CV
        $response = Http::withHeaders([
            'Accept'        => 'application/json',
            'Authorization' => 'Bearer ' . $candidate->buildcvpro_token,
            'X-Source'      => 'web-memoire',
        ])->get($this->buildCVBaseUrl . '/resumes/' . $id);

        if ($response->status() === 404) {
            return response()->json(['message' => 'CV introuvable.'], 404);
        }

        if (!$response->successful()) {
            return response()->json(['message' => 'Token BuildCVPro expiré. Veuillez vous reconnecter.'], 401);
        }

        // Récupérer aussi le share_url depuis la liste
        $listResponse = Http::withHeaders([
            'Accept'        => 'application/json',
            'Authorization' => 'Bearer ' . $candidate->buildcvpro_token,
            'X-Source'      => 'web-memoire',
        ])->get($this->buildCVBaseUrl . '/resumes');

        $shareUrl = null;
        if ($listResponse->successful()) {
            $cvInList = collect($listResponse->json()['data'] ?? [])
                ->firstWhere('id', (int) $id);
            $shareUrl = $cvInList['share_url'] ?? null;
        }

        $data = $response->json()['data'];
        $data['share_url'] = $shareUrl;

        return response()->json(['data' => $data]);

    } catch (\Exception $e) {
        return response()->json(['message' => 'Impossible de contacter BuildCVPro.'], 500);
    }
}
}