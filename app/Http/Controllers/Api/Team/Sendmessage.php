<?php

namespace App\Http\Controllers\Api\Team;

use App\Http\Controllers\ApiBase;
use App\Models\Team;
use App\Models\TeamMessage;
use App\Services\CurrentUser;
use Illuminate\Http\Request;

class Sendmessage extends ApiBase
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

        if (!$member->hasPermission('messages.send')) {
            $this->setFail();
            $this->setResponseCode(403);

            return ['message' => 'You do not have permission to post messages.'];
        }

        $body = trim((string) $request->input('body', ''));
        if ($body === '') {
            $this->setFail();
            $this->setResponseCode(400);

            return ['message' => 'Message body is required.'];
        }

        $msg = TeamMessage::create([
            'team_id' => $team->id,
            'user_id' => $userId,
            'body' => $body,
        ]);

        return [
            'id' => $msg->id,
            'body' => $msg->body,
            'user_id' => (int) $msg->user_id,
            'first_name' => CurrentUser::getFirstName() ?? '',
            'last_name' => CurrentUser::getLastName() ?? '',
            'created_at' => (string) $msg->created_at,
        ];
    }
}
