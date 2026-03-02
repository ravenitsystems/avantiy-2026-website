<?php

namespace App\Http\Controllers\Api\Team;

use App\Http\Controllers\ApiBase;
use App\Models\TeamInvitation;
use App\Models\TeamMember;
use App\Services\CurrentUser;
use Illuminate\Http\Request;

class Acceptinvite extends ApiBase
{
    public function handle(Request $request): array
    {
        if (! CurrentUser::isLoggedIn()) {
            $this->setFail();
            $this->setResponseCode(401);

            return ['message' => 'You must be logged in.'];
        }
        $userId = (int) $request->session()->get('user_id');

        $userEmail = CurrentUser::getEmail();
        if ($userEmail === null || $userEmail === '') {
            $this->setFail();
            $this->setResponseCode(401);

            return ['message' => 'User not found.'];
        }

        $token = trim((string) $request->input('token', ''));
        if ($token === '') {
            $this->setFail();
            $this->setResponseCode(400);

            return ['message' => 'Invitation token is required.'];
        }

        $invitation = TeamInvitation::where('token', $token)
            ->where('status', 'pending')
            ->where('expires_at', '>', now())
            ->first();

        if (! $invitation) {
            $this->setFail();
            $this->setResponseCode(404);

            return ['message' => 'Invitation not found or has expired.'];
        }

        if (strtolower($userEmail) !== strtolower($invitation->email)) {
            $this->setFail();
            $this->setResponseCode(403);

            return ['message' => 'This invitation is not addressed to your account.'];
        }

        $team = $invitation->team;
        if (! $team) {
            $this->setFail();
            $this->setResponseCode(404);

            return ['message' => 'This team no longer exists.'];
        }

        $alreadyMember = TeamMember::where('team_id', $invitation->team_id)
            ->where('user_id', $userId)
            ->exists();

        if ($alreadyMember) {
            $invitation->update(['status' => 'accepted']);

            return ['message' => 'You are already a member of this team.', 'team_id' => (int) $invitation->team_id];
        }

        TeamMember::create([
            'team_id' => $invitation->team_id,
            'user_id' => $userId,
            'team_role_id' => $invitation->team_role_id,
        ]);

        $invitation->update([
            'status' => 'accepted',
            'user_id' => $userId,
        ]);

        return [
            'message' => 'Invitation accepted.',
            'team_id' => (int) $invitation->team_id,
        ];
    }
}
