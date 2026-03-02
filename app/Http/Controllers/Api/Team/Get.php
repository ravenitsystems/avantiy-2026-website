<?php

namespace App\Http\Controllers\Api\Team;

use App\Http\Controllers\ApiBase;
use App\Models\Team;
use App\Services\CurrentUser;
use Illuminate\Http\Request;

class Get extends ApiBase
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

        $member = $team->memberRecord((int) $userId);
        if (!$member) {
            $this->setFail();
            $this->setResponseCode(403);

            return ['message' => 'You are not a member of this team.'];
        }

        if (!$member->team_role_id && (int) $team->user_id === (int) $userId) {
            $ownerRole = \App\Models\TeamRole::whereNull('team_id')->where('is_owner', true)->first();
            if ($ownerRole) {
                $member->update(['team_role_id' => $ownerRole->id]);
                $member->refresh();
            }
        }

        return [
            'id' => $team->id,
            'name' => $team->name,
            'description' => $team->description ?? '',
            'owner_id' => (int) $team->user_id,
            'is_owner' => $member->isOwner(),
            'member_count' => $team->members()->count(),
            'role_name' => $member->roleName(),
            'permissions' => $member->permissionKeys(),
        ];
    }
}
