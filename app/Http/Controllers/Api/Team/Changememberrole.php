<?php

namespace App\Http\Controllers\Api\Team;

use App\Http\Controllers\ApiBase;
use App\Models\Team;
use App\Models\TeamMember;
use App\Models\TeamRole;
use App\Services\CurrentUser;
use Illuminate\Http\Request;

class Changememberrole extends ApiBase
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

        if (!$currentMember->hasPermission('team.change_user_roles')) {
            $this->setFail();
            $this->setResponseCode(403);

            return ['message' => 'You do not have permission to change user roles.'];
        }

        $targetMemberId = (int) $request->input('team_member_id', 0);
        $targetMember = TeamMember::where('id', $targetMemberId)->where('team_id', $team->id)->first();

        if (!$targetMember) {
            $this->setFail();
            $this->setResponseCode(404);

            return ['message' => 'Member not found.'];
        }

        if ($targetMember->isOwner()) {
            $this->setFail();
            $this->setResponseCode(400);

            return ['message' => 'The owner\'s role cannot be changed.'];
        }

        $newRoleId = (int) $request->input('team_role_id', 0);
        $newRole = TeamRole::where('id', $newRoleId)
            ->where(fn ($q) => $q->where('team_id', $team->id)->orWhereNull('team_id'))
            ->first();

        if (!$newRole) {
            $this->setFail();
            $this->setResponseCode(400);

            return ['message' => 'Invalid role.'];
        }

        if ($newRole->is_owner) {
            $this->setFail();
            $this->setResponseCode(400);

            return ['message' => 'Cannot assign the Owner role.'];
        }

        $targetMember->update(['team_role_id' => $newRole->id]);

        return [
            'team_member_id' => $targetMember->id,
            'role_id' => $newRole->id,
            'role_name' => $newRole->name,
        ];
    }
}
