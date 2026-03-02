<?php

namespace App\Http\Controllers\Api\Team;

use App\Http\Controllers\ApiBase;
use App\Models\Team;
use App\Models\TeamRole;
use App\Services\CurrentUser;
use Illuminate\Http\Request;

class Roles extends ApiBase
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

        $roles = TeamRole::where('team_id', $team->id)
            ->orWhereNull('team_id')
            ->with('permissions')
            ->orderBy('sort_order')
            ->get();

        return [
            'roles' => $roles->map(fn ($r) => [
                'id' => $r->id,
                'name' => $r->name,
                'description' => $r->description ?? '',
                'is_owner' => $r->is_owner,
                'is_preset' => $r->is_preset,
                'is_global' => $r->team_id === null,
                'permissions' => $r->permissionKeys(),
                'member_count' => $team->members()->where('team_role_id', $r->id)->count(),
            ])->values()->all(),
        ];
    }
}
