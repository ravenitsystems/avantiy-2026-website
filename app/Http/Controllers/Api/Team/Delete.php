<?php

namespace App\Http\Controllers\Api\Team;

use App\Http\Controllers\ApiBase;
use App\Models\Team;
use App\Services\CurrentUser;
use Illuminate\Http\Request;

class Delete extends ApiBase
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

        if ((int) $team->user_id !== (int) $userId) {
            $this->setFail();
            $this->setResponseCode(403);

            return ['message' => 'Only the team owner can delete the team.'];
        }

        $activeTeamId = $request->session()->get('active_team_id');
        if ((int) $activeTeamId === (int) $team->id) {
            $request->session()->forget('active_team_id');
        }

        $team->update(['deleted' => true, 'deleted_at' => now()]);

        return ['message' => 'Team deleted.'];
    }
}
