<?php

namespace App\Http\Controllers\Api\Team;

use App\Http\Controllers\ApiBase;
use App\Models\Team;
use App\Services\CurrentUser;
use Illuminate\Http\Request;

class Update extends ApiBase
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

            return ['message' => 'Only the team owner can update the team.'];
        }

        $name = trim((string) $request->input('name', ''));
        if ($name === '') {
            $this->setFail();
            $this->setResponseCode(400);

            return ['message' => 'Team name is required.'];
        }

        $description = trim((string) $request->input('description', ''));

        $team->update([
            'name' => $name,
            'description' => $description !== '' ? $description : null,
        ]);

        return [
            'id' => $team->id,
            'name' => $team->name,
            'description' => $team->description ?? '',
        ];
    }
}
