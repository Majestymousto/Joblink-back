<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Application;
use App\Models\Candidate;
use App\Models\Employer;
use App\Models\JobOffer;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class AdminController extends Controller
{
    private function checkAdmin(Request $request)
    {
        if (! $request->user()->isAdmin()) {
            return response()->json(['message' => 'Accès réservé aux administrateurs.'], 403);
        }

        return null;
    }

    private function checkSuperAdmin(Request $request)
    {
        if (! $request->user()->isSuperAdmin()) {
            return response()->json(['message' => 'Accès réservé au super administrateur.'], 403);
        }

        return null;
    }

    // Liste des employeurs en attente
    public function pendingEmployers(Request $request)
    {
        $check = $this->checkAdmin($request);
        if ($check) {
            return $check;
        }

        $employers = Employer::with('user')
            ->where('statut', 'pending')
            ->latest()
            ->get()
            ->map(fn ($e) => [
                'id' => $e->id,
                'nom_entreprise' => $e->nom_entreprise,
                'type_entreprise' => $e->type_entreprise,
                'secteur' => $e->secteur,
                'description' => $e->description,
                'pays' => $e->pays,
                'ville' => $e->ville,
                'email_contact' => $e->email_contact,
                'telephone' => $e->telephone,
                'site_web' => $e->site_web,
                'responsable_nom' => $e->responsable_nom,
                'responsable_fonction' => $e->responsable_fonction,
                'responsable_email' => $e->responsable_email,
                'responsable_telephone' => $e->responsable_telephone,
                'adresse' => $e->adresse,
                'raison_rejet' => $e->raison_rejet,
                'nif' => $e->nif,
                'logo' => $e->logo ? asset('storage/'.$e->logo) : null,
                'numero_identification' => $e->numero_identification,
                'annee_creation' => $e->annee_creation,
                'nombre_employes' => $e->nombre_employes,
                'statut' => $e->statut,
                'created_at' => $e->created_at->format('d M Y'),
                'user' => [
                    'id' => $e->user->id,
                    'name' => $e->user->name,
                    'email' => $e->user->email,
                ],
            ]);

        return response()->json(['data' => $employers]);
    }

    // Liste de tous les employeurs
    public function allEmployers(Request $request)
    {
        $check = $this->checkAdmin($request);
        if ($check) {
            return $check;
        }

        $employers = Employer::with('user')
            ->latest()
            ->paginate(15)
            ->through(fn ($e) => [
                'id' => $e->id,
                'nom_entreprise' => $e->nom_entreprise,
                'type_entreprise' => $e->type_entreprise,
                'secteur' => $e->secteur,
                'description' => $e->description,
                'pays' => $e->pays,
                'ville' => $e->ville,
                'adresse' => $e->adresse,
                'email_contact' => $e->email_contact,
                'telephone' => $e->telephone,
                'site_web' => $e->site_web,
                'responsable_nom' => $e->responsable_nom,
                'responsable_fonction' => $e->responsable_fonction,
                'responsable_email' => $e->responsable_email,
                'responsable_telephone' => $e->responsable_telephone,
                'numero_identification' => $e->numero_identification,
                'nif' => $e->nif,
                'logo' => $e->logo ? asset('storage/'.$e->logo) : null,
                'annee_creation' => $e->annee_creation,
                'nombre_employes' => $e->nombre_employes,
                'statut' => $e->statut,
                'raison_rejet' => $e->raison_rejet,
                'created_at' => $e->created_at->format('d M Y'),
                'user' => [
                    'id' => $e->user->id,
                    'name' => $e->user->name,
                    'email' => $e->user->email,
                ],
            ]);

        return response()->json($employers);
    }

    public function validateEmployer(Request $request, $id)
    {
        $check = $this->checkAdmin($request);
        if ($check) {
            return $check;
        }

        $employer = Employer::find($id);

        if (! $employer) {
            return response()->json(['message' => 'Entreprise introuvable.'], 404);
        }

        $employer->update([
            'statut' => 'active',
            'raison_rejet' => null,
        ]);

        return response()->json([
            'message' => 'Compte employeur validé avec succès !',
            'data' => $employer,
        ]);
    }

    public function rejectEmployer(Request $request, $id)
    {
        $check = $this->checkAdmin($request);
        if ($check) {
            return $check;
        }

        $request->validate([
            'raison' => 'required|string',
        ]);

        $employer = Employer::find($id);

        if (! $employer) {
            return response()->json(['message' => 'Entreprise introuvable.'], 404);
        }

        $employer->update([
            'statut' => 'rejected',
            'raison_rejet' => $request->raison,
        ]);

        return response()->json([
            'message' => 'Compte employeur rejeté.',
            'data' => $employer,
        ]);
    }

    // Statistiques globales
    public function stats(Request $request)
    {
        $check = $this->checkAdmin($request);
        if ($check) {
            return $check;
        }

        return response()->json([
            'data' => [
                'total_users' => User::count(),
                'total_candidats' => User::where('role', 'candidate')->count(),
                'total_employeurs' => User::where('role', 'employer')->count(),
                'employeurs_pending' => Employer::where('statut', 'pending')->count(),
                'employeurs_actifs' => Employer::where('statut', 'active')->count(),
                'total_offres' => JobOffer::count(),
                'offres_actives' => JobOffer::where('statut', 'active')->count(),
                'total_candidatures' => Application::count(),
            ],
        ]);
    }

    // GET /api/admin/users
    public function allUsers(Request $request)
    {
        $check = $this->checkAdmin($request);
        if ($check) {
            return $check;
        }

        $users = User::query()
            ->when($request->search, fn ($q) => $q
                ->where('name', 'like', "%{$request->search}%")
                ->orWhere('email', 'like', "%{$request->search}%"))
            ->when($request->role, fn ($q) => $q->where('role', $request->role))
            ->when($request->status, fn ($q) => $q->where('is_active', $request->status === 'active'))
            ->latest()
            ->paginate($request->per_page ?? 15)
            ->through(fn ($u) => [
                'id' => $u->id,
                'name' => $u->name,
                'email' => $u->email,
                'role' => $u->role,
                'is_active' => $u->is_active,
                'created_at' => $u->created_at->format('d M Y'),
            ]);

        return response()->json($users);
    }

    // GET /api/admin/users/{id}
    public function showUser(Request $request, $id)
    {
        $check = $this->checkAdmin($request);
        if ($check) {
            return $check;
        }

        $user = User::find($id);

        if (! $user) {
            return response()->json(['message' => 'Utilisateur introuvable.'], 404);
        }

        return response()->json(['data' => [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'role' => $user->role,
            'is_active' => $user->is_active,
            'created_at' => $user->created_at->format('d M Y'),
        ]]);
    }

    public function publicStats()
    {
        return response()->json([
            'total_candidats' => User::where('role', 'candidate')->count(),
            'total_entreprises' => User::where('role', 'employer')->count(),
            'total_offres' => JobOffer::where('statut', 'active')->count(),
        ]);
    }

    // POST /api/admin/users
    public function createUser(Request $request)
    {
        $check = $this->checkAdmin($request);
        if ($check) {
            return $check;
        }

        $allowedRoles = $request->user()->isSuperAdmin()
            ? ['super_admin', 'admin', 'employer', 'candidate']
            : ['admin', 'employer', 'candidate'];

        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => ['required', Password::min(8)->mixedCase()->numbers()],
            'role' => ['required', 'in:'.implode(',', $allowedRoles)],
        ]);

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'role' => $data['role'],
            'is_active' => true,
            'email_verified_at' => now(),
        ]);

        return response()->json([
            'message' => 'Utilisateur créé avec succès.',
            'data' => $user,
        ], 201);
    }

    // PUT /api/admin/users/{id}
    public function updateUser(Request $request, $id)
    {
        $check = $this->checkAdmin($request);
        if ($check) {
            return $check;
        }

        $user = User::find($id);

        if (! $user) {
            return response()->json(['message' => 'Utilisateur introuvable.'], 404);
        }

        if ($user->isSuperAdmin() && ! $request->user()->isSuperAdmin()) {
            return response()->json(['message' => 'Vous ne pouvez pas modifier un super administrateur.'], 403);
        }

        $allowedRoles = $request->user()->isSuperAdmin()
            ? ['super_admin', 'admin', 'employer', 'candidate']
            : ['admin', 'employer', 'candidate'];

        $data = $request->validate([
            'name' => 'sometimes|string|max:255',
            'email' => "sometimes|email|unique:users,email,{$id}",
            'password' => ['sometimes', Password::min(8)->mixedCase()->numbers()],
            'role' => ['sometimes', 'in:'.implode(',', $allowedRoles)],
        ]);

        if (isset($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        }

        $user->update($data);

        return response()->json([
            'message' => 'Utilisateur mis à jour avec succès.',
            'data' => $user,
        ]);
    }

    // DELETE /api/admin/users/{id}
    public function deleteUser(Request $request, $id)
    {
        $check = $this->checkAdmin($request);
        if ($check) {
            return $check;
        }

        if ((int) $id === $request->user()->id) {
            return response()->json(['message' => 'Vous ne pouvez pas supprimer votre propre compte.'], 403);
        }

        $user = User::find($id);

        if (! $user) {
            return response()->json(['message' => 'Utilisateur introuvable.'], 404);
        }

        if ($user->isSuperAdmin()) {
            return response()->json(['message' => 'Le super administrateur ne peut pas être supprimé.'], 403);
        }

        $user->delete();

        return response()->json(['message' => 'Utilisateur supprimé avec succès.']);
    }

    // PATCH /api/admin/users/{id}/toggle-status
    public function toggleUserStatus(Request $request, $id)
    {
        $check = $this->checkAdmin($request);
        if ($check) {
            return $check;
        }

        if ((int) $id === $request->user()->id) {
            return response()->json(['message' => 'Vous ne pouvez pas désactiver votre propre compte.'], 403);
        }

        $user = User::find($id);

        if (! $user) {
            return response()->json(['message' => 'Utilisateur introuvable.'], 404);
        }

        if ($user->isSuperAdmin()) {
            return response()->json(['message' => 'Le super administrateur ne peut pas être suspendu.'], 403);
        }

        $user->update(['is_active' => ! $user->is_active]);

        $statut = $user->is_active ? 'activé' : 'désactivé';

        return response()->json([
            'message' => "Compte {$statut} avec succès.",
            'data' => $user,
        ]);
    }

    public function statsWeekly(Request $request)
    {
        $periode = in_array($request->get('periode'), [4, 8, 12, 24])
            ? (int) $request->get('periode')
            : 4;

        $semaines = [];

        for ($i = $periode - 1; $i >= 0; $i--) {
            $debut = now()->startOfWeek()->subWeeks($i);
            $fin = (clone $debut)->endOfWeek();

            $semaines[] = [
                'semaine' => $debut->format('d M'),
                'inscriptions' => User::whereBetween('created_at', [$debut, $fin])->count(),
                'offres' => JobOffer::whereBetween('created_at', [$debut, $fin])->count(),
                'candidatures' => Application::whereBetween('created_at', [$debut, $fin])->count(),
            ];
        }

        return response()->json([
            'periode' => $periode,
            'data' => $semaines,
        ]);
    }

    public function statsRegion(Request $request)
    {
        $type = $request->query('type'); // null = tout retourner

        $data = [];

        if (! $type || $type === 'candidats') {
            $data['candidats'] = Candidate::select('localisation', \DB::raw('count(*) as total'))
                ->whereNotNull('localisation')
                ->groupBy('localisation')
                ->orderByDesc('total')
                ->get();
        }

        if (! $type || $type === 'employeurs') {
            $data['employeurs'] = Employer::select('ville', 'pays', \DB::raw('count(*) as total'))
                ->whereNotNull('ville')
                ->groupBy('ville', 'pays')
                ->orderByDesc('total')
                ->get();
        }

        if (! $type || $type === 'offres') {
            $data['offres'] = JobOffer::select('localisation', \DB::raw('count(*) as total'))
                ->whereNotNull('localisation')
                ->groupBy('localisation')
                ->orderByDesc('total')
                ->get();
        }

        return response()->json($data);
    }
}
