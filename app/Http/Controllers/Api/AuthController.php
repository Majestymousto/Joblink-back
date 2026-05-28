<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Candidate;
use App\Models\Employer;
use App\Models\User;
use App\Notifications\OtpVerification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;

class AuthController extends Controller
{
    private function createTokenWithSource(User $user, Request $request): string
    {
        $source = $request->header('X-Source', 'unknown');
        $token = $user->createToken('api-token');
        $token->accessToken->forceFill(['source' => $source])->save();

        return $token->plainTextToken;
    }

    private function generateAndSendOtp(User $user): void
    {
        $otp = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        $user->update([
            'otp_code' => $otp,
            'otp_expires_at' => now()->addMinutes(10),
        ]);

        $user->notify(new OtpVerification($otp));
    }

    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
            'role' => 'required|in:candidate,employer',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => $request->password,
            'role' => $request->role,
        ]);

        if ($user->isCandidate()) {
            Candidate::create(['user_id' => $user->id]);
        }

        if ($user->isEmployer()) {
            Employer::create([
                'user_id' => $user->id,
                'nom_entreprise' => $request->nom_entreprise ?? '',
                'type_entreprise' => $request->type_entreprise,
                'secteur' => $request->secteur,
                'description' => $request->description,
                'pays' => $request->pays,
                'ville' => $request->ville,
                'adresse' => $request->adresse,
                'email_contact' => $request->email_contact,
                'telephone' => $request->telephone,
                'site_web' => $request->site_web,
                'responsable_nom' => $request->responsable_nom,
                'responsable_fonction' => $request->responsable_fonction,
                'responsable_telephone' => $request->responsable_telephone,
                'responsable_email' => $request->responsable_email,
                'numero_identification' => $request->numero_identification,
                'nif' => $request->nif,
                'annee_creation' => $request->annee_creation,
                'nombre_employes' => $request->nombre_employes,
                'statut' => 'pending',
            ]);
        }

        $this->generateAndSendOtp($user);

        return response()->json([
            'message' => 'Inscription réussie. Un code de vérification a été envoyé à votre adresse email.',
            'email' => $user->email,
        ], 201);
    }

    public function verifyOtp(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'otp' => 'required|string|size:6',
        ]);

        $user = User::where('email', $request->email)->first();

        if (! $user) {
            return response()->json(['message' => 'Utilisateur introuvable.'], 404);
        }

        if ($user->email_verified_at) {
            return response()->json(['message' => 'Ce compte est déjà vérifié.'], 422);
        }

        if (! $user->otp_code || $user->otp_code !== $request->otp) {
            return response()->json(['message' => 'Code OTP invalide.'], 422);
        }

        if (! $user->otp_expires_at || $user->otp_expires_at->isPast()) {
            return response()->json(['message' => 'Le code OTP a expiré. Veuillez en demander un nouveau.'], 422);
        }

        $user->update([
            'email_verified_at' => now(),
            'otp_code' => null,
            'otp_expires_at' => null,
        ]);

        $token = $this->createTokenWithSource($user, $request);

        return response()->json([
            'message' => 'Email vérifié avec succès.',
            'token' => $token,
            'user' => $this->formatUser($user),
        ]);
    }

    public function resendOtp(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
        ]);

        $user = User::where('email', $request->email)->first();

        if (! $user) {
            return response()->json(['message' => 'Utilisateur introuvable.'], 404);
        }

        if ($user->email_verified_at) {
            return response()->json(['message' => 'Ce compte est déjà vérifié.'], 422);
        }

        // Anti-spam : attendre au moins 1 minute entre deux envois
        if ($user->otp_expires_at && $user->otp_expires_at->diffInSeconds(now(), false) < -540) {
            return response()->json([
                'message' => 'Veuillez attendre avant de demander un nouveau code.',
            ], 429);
        }

        $this->generateAndSendOtp($user);

        return response()->json([
            'message' => 'Un nouveau code de vérification a été envoyé à votre adresse email.',
        ]);
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $user = User::where('email', $request->email)->first();

        if (! $user || ! Hash::check($request->password, $user->password)) {
            return response()->json(['message' => 'Identifiants invalides.'], 401);
        }

        if (! $user->email_verified_at) {
            return response()->json([
                'message' => 'Veuillez vérifier votre adresse email avant de vous connecter.',
                'status' => 'unverified',
                'email' => $user->email,
            ], 403);
        }

        if (! $user->is_active) {
            return response()->json([
                'message' => 'Votre compte a été suspendu. Contactez l\'administrateur.',
                'status' => 'suspended',
            ], 403);
        }

        if ($user->isEmployer()) {
            $employer = $user->employer;
            if ($employer && $employer->statut === 'pending') {
                return response()->json([
                    'message' => 'Votre compte est en attente de validation.',
                    'status' => 'pending',
                ], 403);
            }
            if ($employer && $employer->statut === 'rejected') {
                return response()->json([
                    'message' => 'Votre compte a été rejeté.',
                    'status' => 'rejected',
                ], 403);
            }
        }

        $token = $this->createTokenWithSource($user, $request);

        return response()->json([
            'token' => $token,
            'user' => $this->formatUser($user),
        ]);
    }

    public function googleAuth(Request $request)
    {
        $request->validate([
            'token' => 'required|string',
            'role' => 'nullable|in:candidate,employer',
        ]);

        try {
            $googleUser = Socialite::driver('google')
                ->stateless()
                ->userFromToken($request->token);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Token Google invalide.'], 401);
        }

        $user = User::where('email', $googleUser->getEmail())->first();

        if (! $user) {
            $role = $request->role ?? 'candidate';
            $user = User::create([
                'name' => $googleUser->getName(),
                'email' => $googleUser->getEmail(),
                'google_id' => $googleUser->getId(),
                'avatar' => $googleUser->getAvatar(),
                'role' => $role,
                'password' => Str::random(24),
                'email_verified_at' => now(), // Google garantit l'email
            ]);

            if ($user->isCandidate()) {
                Candidate::create(['user_id' => $user->id]);
            }

            if ($user->isEmployer()) {
                Employer::create([
                    'user_id' => $user->id,
                    'nom_entreprise' => '',
                    'statut' => 'pending',
                ]);
            }
        } else {
            $user->update([
                'google_id' => $googleUser->getId(),
                'avatar' => $googleUser->getAvatar(),
            ]);
        }

        $token = $this->createTokenWithSource($user, $request);

        return response()->json([
            'token' => $token,
            'user' => $this->formatUser($user),
        ]);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Déconnecté avec succès.']);
    }

    public function me(Request $request)
    {
        $user = $request->user();

        if (! $user->is_active) {
            $user->currentAccessToken()->delete();

            return response()->json([
                'message' => 'Votre compte a été suspendu.',
                'status' => 'suspended',
            ], 403);
        }

        return response()->json($this->formatUser($user));
    }

    private function formatUser(User $user): array
    {
        $data = [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'avatar' => $user->avatar,
            'role' => $user->role,
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
