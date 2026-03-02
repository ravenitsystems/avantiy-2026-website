<?php

namespace App\Http\Controllers\Api\Team;

use App\Http\Controllers\ApiBase;
use App\Models\Team;
use App\Models\TeamMember;
use App\Models\User;
use App\Services\CurrentUser;
use App\Services\TeamPermissions;
use Illuminate\Http\Request;

class Create extends ApiBase
{
    public function handle(Request $request): array
    {
        if (! CurrentUser::isLoggedIn()) {
            $this->setFail();
            $this->setResponseCode(401);

            return ['message' => 'You must be logged in to create a team.'];
        }
        $userId = (int) $request->session()->get('user_id');

        $accountType = CurrentUser::getAccountType();
        if ($accountType === null || $accountType === '') {
            $this->setFail();
            $this->setResponseCode(401);

            return ['message' => 'User not found.'];
        }

        if (! in_array($accountType, ['agency', 'enterprise'], true)) {
            $this->setFail();
            $this->setResponseCode(403);

            return ['message' => 'Only agency and enterprise accounts can create teams.'];
        }

        if ($accountType === 'agency') {
            $count = User::find($userId)?->teams()->count() ?? 0;
            if ($count >= 3) {
                $this->setFail();
                $this->setResponseCode(400);

                return ['message' => 'Maximum number of teams (3) reached for agency accounts.'];
            }
        }

        $name = trim((string) $request->input('name', ''));
        if ($name === '') {
            $this->setFail();
            $this->setResponseCode(400);

            return ['message' => 'Team name is required.'];
        }

        $description = trim((string) $request->input('description', ''));

        $team = Team::create([
            'user_id' => $userId,
            'name' => $name,
            'description' => $description !== '' ? $description : null,
        ]);

        $ownerRole = TeamPermissions::getOwnerRole();

        TeamMember::create([
            'team_id' => $team->id,
            'user_id' => $userId,
            'team_role_id' => $ownerRole->id,
        ]);

        return [
            'id' => $team->id,
            'name' => $team->name,
            'description' => $team->description,
        ];
    }
}
