<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Message;
use App\Models\Application;
use Illuminate\Http\Request;

class MessageController extends Controller
{
    // Liste des conversations du candidat ou de l'employeur
    public function index(Request $request)
    {
        $user = $request->user();

        if ($user->isCandidate()) {
            $applications = Application::with(['jobOffer.employer.user', 'messages' => function($q) {
                $q->latest()->limit(1);
            }])
            ->where('candidate_id', $user->candidate->id)
            ->get()
            ->map(fn($app) => [
                'application_id' => $app->id,
                'job'            => [
                    'id'    => $app->jobOffer->id,
                    'titre' => $app->jobOffer->titre,
                ],
                'entreprise' => [
                    'id'   => $app->jobOffer->employer->id,
                    'nom'  => $app->jobOffer->employer->nom_entreprise,
                ],
                'dernier_message' => $app->messages->first()?->content ?? null,
                'unread'          => Message::where('application_id', $app->id)
                    ->where('sender_id', '!=', $user->id)
                    ->where('read', false)
                    ->count(),
            ]);

            return response()->json(['data' => $applications]);
        }

        if ($user->isEmployer()) {
            $applications = Application::with(['candidate.user', 'jobOffer', 'messages' => function($q) {
                $q->latest()->limit(1);
            }])
            ->whereHas('jobOffer', function($q) use ($user) {
                $q->where('employer_id', $user->employer->id);
            })
            ->get()
            ->map(fn($app) => [
                'application_id' => $app->id,
                'job'            => [
                    'id'    => $app->jobOffer->id,
                    'titre' => $app->jobOffer->titre,
                ],
                'candidat' => [
                    'id'     => $app->candidate->id,
                    'name'   => $app->candidate->user->name,
                    'avatar' => $app->candidate->user->avatar,
                ],
                'dernier_message' => $app->messages->first()?->content ?? null,
                'unread'          => Message::where('application_id', $app->id)
                    ->where('sender_id', '!=', $user->id)
                    ->where('read', false)
                    ->count(),
            ]);

            return response()->json(['data' => $applications]);
        }
    }

    // Messages d'une conversation
    public function show(Request $request, $applicationId)
    {
        $user        = $request->user();
        $application = Application::find($applicationId);
if (!$application) {
    return response()->json(['message' => 'Conversation introuvable.'], 404);
}

        // Vérifier que l'utilisateur a accès à cette conversation
        if ($user->isCandidate() && $application->candidate_id !== $user->candidate->id) {
            return response()->json(['message' => 'Non autorisé.'], 403);
        }

        if ($user->isEmployer() && $application->jobOffer->employer->user_id !== $user->id) {
            return response()->json(['message' => 'Non autorisé.'], 403);
        }

        // Marquer les messages comme lus
        Message::where('application_id', $applicationId)
            ->where('sender_id', '!=', $user->id)
            ->where('read', false)
            ->update(['read' => true]);

        $messages = Message::where('application_id', $applicationId)
            ->oldest()
            ->get()
            ->map(fn($msg) => [
                'id'        => $msg->id,
                'content'   => $msg->content,
                'from_me'   => $msg->sender_id === $user->id,
                'read'      => $msg->read,
                'created_at'=> $msg->created_at->format('H:i'),
            ]);

        return response()->json(['data' => $messages]);
    }

    // Envoyer un message
    public function send(Request $request, $applicationId)
    {
        $user        = $request->user();
        $application = Application::find($applicationId);
if (!$application) {
    return response()->json(['message' => 'Conversation introuvable.'], 404);
}

        // Vérifier accès
        if ($user->isCandidate() && $application->candidate_id !== $user->candidate->id) {
            return response()->json(['message' => 'Non autorisé.'], 403);
        }

        if ($user->isEmployer() && $application->jobOffer->employer->user_id !== $user->id) {
            return response()->json(['message' => 'Non autorisé.'], 403);
        }

        $request->validate([
            'content' => 'required|string|max:1000',
        ]);

        $message = Message::create([
            'application_id' => $applicationId,
            'sender_id'      => $user->id,
            'content'        => $request->content,
            'read'           => false,
        ]);

        return response()->json([
            'data'    => [
                'id'        => $message->id,
                'content'   => $message->content,
                'from_me'   => true,
                'read'      => false,
                'created_at'=> $message->created_at->format('H:i'),
            ],
            'message' => 'Message envoyé !',
        ], 201);
    }
}