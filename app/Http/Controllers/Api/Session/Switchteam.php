<?php

namespace App\Http\Controllers\Api\Session;

use App\Http\Controllers\ApiBase;
use App\Models\Team;
use App\Services\CurrentUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class Switchteam extends ApiBase
{
    public function handle(Request $request): array
    {
        if (! CurrentUser::isLoggedIn()) {
            $this->setFail();
            $this->setResponseCode(401);

            return ['message' => 'You must be logged in.'];
        }
        $userId = (int) $request->session()->get('user_id');

        $teamId = $request->input('team_id');

        if ($teamId === null || $teamId === '' || $teamId === 0 || $teamId === '0') {
            $request->session()->forget('active_team_id');

            return [
                'active_team_id' => null,
                'active_team_name' => null,
            ];
        }

        $teamId = (int) $teamId;

        $team = Team::find($teamId);
        if (!$team) {
            $this->setFail();
            $this->setResponseCode(404);

            return ['message' => 'Team not found.'];
        }

        $isMember = DB::table('team_member')
            ->where('team_id', $teamId)
            ->where('user_id', $userId)
            ->exists();

        if (!$isMember) {
            $this->setFail();
            $this->setResponseCode(403);

            return ['message' => 'You are not a member of this team.'];
        }

        $request->session()->put('active_team_id', $teamId);

        return [
            'active_team_id' => $teamId,
            'active_team_name' => $team->name,
        ];
    }
}
