<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Candidate;
use App\Models\Employer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    private function createTokenWithSource(User $user, Request $request): string
    {
        $source = $request->header('X-Source', 'unknown');
        $token  = $user->createToken('api-token');
        $token->accessToken->forceFill(['source' => $source])->save();
        return $token->plainTextToken;
    }

    public function register(Request $request)
    {
        $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|email|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
            'role'     => 'required|in:candidate,employer',
        ]);

        $user = User::create([
            'name'     => $request->name,
            'email'    => $request->email,
            'password' => $request->password,
            'role'     => $request->role,
        ]);

        // Créer le profil selon le rôle
        if ($user->isCandidate()) {
            Candidate::create(['user_id' => $user->id]);
        }

        if ($user->isEmployer()) {
            Employer::create([
                'user_id'       => $user->id,
                'nom_entreprise' => $request->nom_entreprise ?? '',
                'statut'        => 'pending',
            ]);
        }

        $token = $this->createTokenWithSource($user, $request);

        return response()->json([
            'token' => $token,
            'user'  => $this->formatUser($user),
        ], 201);
    }

    public function login(Request $request)
    {
        $request->validate([
            'email'    => 'required|email',
            'password' => 'required',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json(['message' => 'Identifiants invalides.'], 401);
        }

        // Vérifier si employeur est validé
        if ($user->isEmployer()) {
            $employer = $user->employer;
            if ($employer && $employer->statut === 'pending') {
                return response()->json([
                    'message' => 'Votre compte est en attente de validation.',
                    'status'  => 'pending',
                ], 403);
            }
            if ($employer && $employer->statut === 'rejected') {
                return response()->json([
                    'message' => 'Votre compte a été rejeté.',
                    'status'  => 'rejected',
                ], 403);
            }
        }

        $token = $this->createTokenWithSource($user, $request);

        return response()->json([
            'token' => $token,
            'user'  => $this->formatUser($user),
        ]);
    }

    public function googleAuth(Request $request)
    {
        $request->validate([
            'token' => 'required|string',
            'role'  => 'nullable|in:candidate,employer',
        ]);

        try {
            $googleUser = \Laravel\Socialite\Facades\Socialite::driver('google')
                ->stateless()
                ->userFromToken($request->token);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Token Google invalide.'], 401);
        }

        $user = User::where('email', $googleUser->getEmail())->first();

        if (!$user) {
            // Nouveau compte
            $role = $request->role ?? 'candidate';
            $user = User::create([
                'name'      => $googleUser->getName(),
                'email'     => $googleUser->getEmail(),
                'google_id' => $googleUser->getId(),
                'avatar'    => $googleUser->getAvatar(),
                'role'      => $role,
                'password'  => \Illuminate\Support\Str::random(24),
            ]);

            if ($user->isCandidate()) {
                Candidate::create(['user_id' => $user->id]);
            }

            if ($user->isEmployer()) {
                Employer::create([
                    'user_id'        => $user->id,
                    'nom_entreprise' => '',
                    'statut'         => 'pending',
                ]);
            }
        } else {
            // Mise à jour des infos Google
            $user->update([
                'google_id' => $googleUser->getId(),
                'avatar'    => $googleUser->getAvatar(),
            ]);
        }

        $token = $this->createTokenWithSource($user, $request);

        return response()->json([
            'token' => $token,
            'user'  => $this->formatUser($user),
        ]);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        return response()->json(['message' => 'Déconnecté avec succès.']);
    }

    public function me(Request $request)
    {
        return response()->json($this->formatUser($request->user()));
    }

    private function formatUser(User $user): array
    {
        $data = [
            'id'     => $user->id,
            'name'   => $user->name,
            'email'  => $user->email,
            'avatar' => $user->avatar,
            'role'   => $user->role,
        ];

        if ($user->isCandidate() && $user->candidate) {
            $data['profile'] = $user->candidate;
        }

        if ($user->isEmployer() && $user->employer) {
            $data['profile'] = $user->employer;
        }

        return $data;
    }
}