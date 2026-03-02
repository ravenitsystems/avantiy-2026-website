<?php

namespace App\Http\Controllers\Api\Team;

use App\Http\Controllers\ApiBase;
use App\Models\Permission;
use App\Models\Team;
use App\Services\CurrentUser;
use App\Models\TeamRole;
use App\Services\TeamPermissions;
use Illuminate\Http\Request;

class Createrole extends ApiBase
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

        if (!$member->hasPermission('team.edit_roles')) {
            $this->setFail();
            $this->setResponseCode(403);

            return ['message' => 'You do not have permission to edit roles.'];
        }

        $name = trim((string) $request->input('name', ''));
        if ($name === '') {
            $this->setFail();
            $this->setResponseCode(400);

            return ['message' => 'Role name is required.'];
        }

        $description = trim((string) $request->input('description', ''));
        $permissions = $request->input('permissions', []);
        if (!is_array($permissions)) {
            $permissions = [];
        }

        $validKeys = Permission::pluck('key')->all();
        $permissions = array_values(array_unique(array_filter($permissions, fn ($p) => in_array($p, $validKeys, true))));

        $maxSort = $team->roles()->max('sort_order') ?? 0;

        $role = TeamRole::create([
            'team_id' => $team->id,
            'name' => $name,
            'description' => $description !== '' ? $description : null,
            'is_owner' => false,
            'is_preset' => false,
            'sort_order' => $maxSort + 1,
        ]);

        TeamPermissions::syncRolePermissions($role, $permissions);

        return [
            'id' => $role->id,
            'name' => $role->name,
            'description' => $role->description ?? '',
            'permissions' => $permissions,
        ];
    }
}
