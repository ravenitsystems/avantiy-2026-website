<?php

namespace App\Http\Controllers\Api\Team;

use App\Http\Controllers\ApiBase;
use App\Mail\TeamInvitationMail;
use App\Models\Team;
use App\Models\TeamInvitation;
use App\Models\TeamRole;
use App\Models\User;
use App\Services\CurrentUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class Invite extends ApiBase
{
    public function handle(Request $request): array
    {
        if (! CurrentUser::isLoggedIn()) {
            $this->setFail();
            $this->setResponseCode(401);

            return ['message' => 'You must be logged in.'];
        }
        $userId = (int) $request->session()->get('user_id');

        $teamId = $request->route('id1');
        if (!$teamId) {
            $this->setFail();
            $this->setResponseCode(400);

            return ['message' => 'Team ID is required.'];
        }

        $team = Team::find($teamId);
        if (!$team) {
            $this->setFail();
            $this->setResponseCode(404);

            return ['message' => 'Team not found.'];
        }

        $currentMember = $team->memberRecord((int) $userId);
        if (!$currentMember) {
            $this->setFail();
            $this->setResponseCode(403);

            return ['message' => 'You are not a member of this team.'];
        }

        if (!$currentMember->hasPermission('team.invite_user')) {
            $this->setFail();
            $this->setResponseCode(403);

            return ['message' => 'You do not have permission to invite users.'];
        }

        $email = strtolower(trim((string) $request->input('email', '')));
        if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->setFail();
            $this->setResponseCode(400);

            return ['message' => 'A valid email address is required.'];
        }

        $teamRoleId = (int) $request->input('team_role_id', 0);
        $role = TeamRole::where('id', $teamRoleId)
            ->where(fn ($q) => $q->where('team_id', $team->id)->orWhereNull('team_id'))
            ->first();

        if (!$role) {
            $this->setFail();
            $this->setResponseCode(400);

            return ['message' => 'A valid role must be selected.'];
        }

        if ($role->is_owner) {
            $this->setFail();
            $this->setResponseCode(400);

            return ['message' => 'Cannot assign the Owner role via invitation.'];
        }

        $existingMember = $team->members()->whereHas('user', fn ($q) => $q->where('email', $email))->exists();
        if ($existingMember) {
            $this->setFail();
            $this->setResponseCode(400);

            return ['message' => 'This user is already a member of the team.'];
        }

        $pendingInvite = TeamInvitation::where('team_id', $team->id)
            ->where('email', $email)
            ->where('status', 'pending')
            ->where('expires_at', '>', now())
            ->exists();

        if ($pendingInvite) {
            $this->setFail();
            $this->setResponseCode(400);

            return ['message' => 'A pending invitation already exists for this email.'];
        }

        $targetUser = User::where('email', $email)->first();
        $token = Str::random(64);

        $invitation = TeamInvitation::create([
            'team_id' => $team->id,
            'email' => $email,
            'user_id' => $targetUser?->id,
            'invited_by_user_id' => $userId,
            'token' => $token,
            'expires_at' => now()->addDays(7),
            'status' => 'pending',
            'team_role_id' => $role->id,
        ]);

        $inviterName = trim((CurrentUser::getFirstName() ?? '') . ' ' . (CurrentUser::getLastName() ?? '')) ?: CurrentUser::getEmail();
        $acceptUrl = url("/dashboard/teams?accept_token={$token}");

        Mail::to($email)->send(new TeamInvitationMail(
            invitation: $invitation,
            teamName: $team->name,
            inviterName: $inviterName,
            acceptUrl: $acceptUrl,
            roleName: $role->name,
        ));

        return [
            'id' => $invitation->id,
            'email' => $invitation->email,
            'status' => $invitation->status,
            'role_name' => $role->name,
            'has_account' => $targetUser !== null,
        ];
    }
}
