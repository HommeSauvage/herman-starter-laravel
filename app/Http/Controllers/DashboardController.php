<?php

namespace App\Http\Controllers;

use App\Models\Note;
use App\Models\Team;
use App\Models\TeamInvitation;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function __invoke(Request $request, Team $current_team): Response
    {
        $email = strtolower($request->user()->email);

        $pendingInvitations = TeamInvitation::query()
            ->with(['inviter', 'team'])
            ->whereRaw('LOWER(email) = ?', [$email])
            ->whereNull('accepted_at')
            ->where(fn ($query) => $query
                ->whereNull('expires_at')
                ->orWhere('expires_at', '>=', now()))
            ->latest()
            ->get()
            ->map(fn (TeamInvitation $invitation) => [
                'code' => $invitation->code,
                'inviterName' => $invitation->inviter->name,
                'team' => [
                    'name' => $invitation->team->name,
                    'slug' => $invitation->team->slug,
                ],
            ]);

        return Inertia::render('dashboard', [
            'pendingInvitations' => $pendingInvitations,
            'stats' => [
                'members' => $current_team->members()->count(),
                'pendingInvitations' => $current_team->invitations()
                    ->whereNull('accepted_at')
                    ->where(fn ($query) => $query
                        ->whereNull('expires_at')
                        ->orWhere('expires_at', '>=', now()))
                    ->count(),
                // REFERENCE MODULE — Notes stat. Remove with the Notes feature.
                'notes' => $current_team->notes()->count(),
            ],
            // REFERENCE MODULE — recent notes list. Remove with the Notes feature.
            'recentNotes' => $current_team->notes()
                ->with('user:id,name')
                ->latest()
                ->limit(5)
                ->get()
                ->map(fn (Note $note) => [
                    'id' => $note->id,
                    'title' => $note->title,
                    'excerpt' => str($note->body)->stripTags()->limit(140)->toString(),
                    'author' => $note->user->name,
                    'updated_at' => $note->updated_at?->toIso8601String(),
                ]),
        ]);
    }
}
