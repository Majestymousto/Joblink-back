<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Avis;
use Illuminate\Http\Request;

class AvisController extends Controller
{
    /**
     * POST /api/avis
     * 👤 Candidat connecté — Soumettre un avis (note + commentaire).
     */
    public function store(Request $request)
    {
        $request->validate([
            'note'        => 'required|integer|min:1|max:5',
            'commentaire' => 'required|string|max:1000',
            'context'     => 'nullable|string|max:100',
        ]);

        // Un utilisateur ne peut poster qu'un seul avis
        $dejaPoste = Avis::where('user_id', $request->user()->id)->exists();
        if ($dejaPoste) {
            return response()->json([
                'message' => 'Vous avez déjà soumis un avis.',
            ], 409);
        }

        $avis = Avis::create([
            'user_id'     => $request->user()->id,
            'note'        => $request->note,
            'commentaire' => $request->commentaire,
            'context'     => $request->context,
            'status'      => 'pending',
        ]);

        return response()->json([
            'message' => 'Avis soumis avec succès ! Il sera visible après modération.',
            'data'    => [
                'id'          => $avis->id,
                'note'        => $avis->note,
                'commentaire' => $avis->commentaire,
                'context'     => $avis->context,
                'status'      => $avis->status,
            ],
        ], 201);
    }
    public function approved()
{
    $avis = Avis::where('status', 'approved')
        ->with('user:id,name,avatar')
        ->latest('created_at')
        ->get()
        ->map(fn($a) => [
            'id'          => $a->id,
            'note'        => $a->note,
            'commentaire' => $a->commentaire,
            'context'     => $a->context,
            'created_at'  => $a->created_at->format('d M Y'),
            'auteur'      => [
                'name'   => $a->user->name ?? 'Anonyme',
                'avatar' => $a->user->avatar ?? null,
            ],
        ]);

    return response()->json(['data' => $avis]);
}

public function adminIndex(Request $request)
{
    $query = Avis::with('user:id,name,avatar')->latest('created_at');

    if ($request->filled('status')) {
        $query->where('status', $request->status);
    }

    $avis = $query->get()->map(fn($a) => [
        'id'          => $a->id,
        'note'        => $a->note,
        'commentaire' => $a->commentaire,
        'context'     => $a->context,
        'status'      => $a->status,
        'created_at'  => $a->created_at->format('d M Y H:i'),
        'auteur'      => [
            'id'     => $a->user->id ?? null,
            'name'   => $a->user->name ?? 'Utilisateur supprimé',
            'email'  => $a->user->email ?? null,
            'avatar' => $a->user->avatar ?? null,
        ],
    ]);

    return response()->json(['data' => $avis]);
}
public function approve($id)
{
    $avis = Avis::findOrFail($id);

    if ($avis->status === 'approved') {
        return response()->json(['message' => 'Cet avis est déjà approuvé.'], 409);
    }

    $avis->update(['status' => 'approved']);

    return response()->json(['message' => 'Avis approuvé avec succès.']);
}

public function reject($id)
{
    $avis = Avis::findOrFail($id);

    if ($avis->status === 'rejected') {
        return response()->json(['message' => 'Cet avis est déjà rejeté.'], 409);
    }

    $avis->update(['status' => 'rejected']);

    return response()->json(['message' => 'Avis rejeté avec succès.']);
}
}