<?php

namespace App\Http\Controllers\Api\Team;

use App\Http\Controllers\ApiBase;
use App\Models\Team;
use App\Models\TeamInvitation;
use App\Services\CurrentUser;
use Illuminate\Http\Request;

class Cancelinvite extends ApiBase
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

            return ['message' => 'You do not have permission to manage invitations.'];
        }

        $invitationId = (int) $request->input('invitation_id', 0);
        $invitation = TeamInvitation::where('id', $invitationId)
            ->where('team_id', $team->id)
            ->where('status', 'pending')
            ->first();

        if (!$invitation) {
            $this->setFail();
            $this->setResponseCode(404);

            return ['message' => 'Invitation not found.'];
        }

        $invitation->update(['status' => 'cancelled']);

        return ['message' => 'Invitation cancelled.'];
    }
}
