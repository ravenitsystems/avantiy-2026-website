<?php

namespace App\Http\Controllers\Api\Team;

use App\Http\Controllers\ApiBase;
use App\Models\Team;
use App\Services\CurrentUser;
use Illuminate\Http\Request;

class Members extends ApiBase
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

        $members = $team->members()->with(['user', 'teamRole'])->get();

        return [
            'members' => $members->map(fn ($m) => [
                'id' => $m->id,
                'user_id' => (int) $m->user_id,
                'first_name' => $m->user->first_name ?? '',
                'last_name' => $m->user->last_name ?? '',
                'email' => $m->user->email ?? '',
                'is_owner' => $m->isOwner(),
                'role_id' => $m->team_role_id,
                'role_name' => $m->roleName(),
                'permissions' => $m->permissionKeys(),
            ])->values()->all(),
        ];
    }
}
