<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Employer;
use App\Models\User;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    // Vérifier si l'utilisateur est admin
    private function checkAdmin(Request $request)
    {
        if ($request->user()->role !== 'admin') {
            return response()->json(['message' => 'Accès réservé aux administrateurs.'], 403);
        }
        return null;
    }

    // Liste des employeurs en attente
    public function pendingEmployers(Request $request)
    {
        $check = $this->checkAdmin($request);
        if ($check) return $check;

        $employers = Employer::with('user')
            ->where('statut', 'pending')
            ->latest()
            ->get()
            ->map(fn($e) => [
                'id'                   => $e->id,
                'nom_entreprise'       => $e->nom_entreprise,
                'type_entreprise'      => $e->type_entreprise,
                'secteur'              => $e->secteur,
                'description'          => $e->description,
                'pays'                 => $e->pays,
                'ville'                => $e->ville,
                'email_contact'        => $e->email_contact,
                'telephone'            => $e->telephone,
                'site_web'             => $e->site_web,
                'responsable_nom'      => $e->responsable_nom,
                'responsable_fonction' => $e->responsable_fonction,
                'responsable_email'    => $e->responsable_email,
                'numero_identification'=> $e->numero_identification,
                'annee_creation'       => $e->annee_creation,
                'nombre_employes'      => $e->nombre_employes,
                'statut'               => $e->statut,
                'created_at'           => $e->created_at->format('d M Y'),
                'user'                 => [
                    'id'    => $e->user->id,
                    'name'  => $e->user->name,
                    'email' => $e->user->email,
                ],
            ]);

        return response()->json(['data' => $employers]);
    }

    // Liste de tous les employeurs
    public function allEmployers(Request $request)
    {
        $check = $this->checkAdmin($request);
        if ($check) return $check;

        $employers = Employer::with('user')
            ->latest()
            ->paginate(15)
            ->through(fn($e) => [
                'id'             => $e->id,
                'nom_entreprise' => $e->nom_entreprise,
                'secteur'        => $e->secteur,
                'ville'          => $e->ville,
                'statut'         => $e->statut,
                'created_at'     => $e->created_at->format('d M Y'),
                'user'           => [
                    'id'    => $e->user->id,
                    'name'  => $e->user->name,
                    'email' => $e->user->email,
                ],
            ]);

        return response()->json($employers);
    }

    public function validateEmployer(Request $request, $id)
{
    $check = $this->checkAdmin($request);
    if ($check) return $check;

    $employer = Employer::find($id);

    if (!$employer) {
        return response()->json(['message' => 'Entreprise introuvable.'], 404);
    }

    $employer->update([
        'statut'       => 'active',
        'raison_rejet' => null,
    ]);

    return response()->json([
        'message' => 'Compte employeur validé avec succès !',
        'data'    => $employer,
    ]);
}

public function rejectEmployer(Request $request, $id)
{
    $check = $this->checkAdmin($request);
    if ($check) return $check;

    $request->validate([
        'raison' => 'required|string',
    ]);

    $employer = Employer::find($id);

    if (!$employer) {
        return response()->json(['message' => 'Entreprise introuvable.'], 404);
    }

    $employer->update([
        'statut'       => 'rejected',
        'raison_rejet' => $request->raison,
    ]);

    return response()->json([
        'message' => 'Compte employeur rejeté.',
        'data'    => $employer,
    ]);
}

    // Statistiques globales
    public function stats(Request $request)
    {
        $check = $this->checkAdmin($request);
        if ($check) return $check;

        return response()->json([
            'data' => [
                'total_users'        => User::count(),
                'total_candidats'    => User::where('role', 'candidate')->count(),
                'total_employeurs'   => User::where('role', 'employer')->count(),
                'employeurs_pending' => Employer::where('statut', 'pending')->count(),
                'employeurs_actifs'  => Employer::where('statut', 'active')->count(),
                'total_offres'       => \App\Models\JobOffer::count(),
                'offres_actives'     => \App\Models\JobOffer::where('statut', 'active')->count(),
                'total_candidatures' => \App\Models\Application::count(),
            ],
        ]);
    }
}