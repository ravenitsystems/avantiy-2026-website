<?php

namespace App\Http\Controllers\Api\Team;

use App\Http\Controllers\ApiBase;
use App\Models\Team;
use App\Services\CurrentUser;
use Illuminate\Http\Request;

class Messages extends ApiBase
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

        if (!$member->hasPermission('messages.view')) {
            $this->setFail();
            $this->setResponseCode(403);

            return ['message' => 'You do not have permission to view the conversation.'];
        }

        $page = max(1, (int) $request->input('page', 1));
        $perPage = 50;

        $query = $team->messages()
            ->with('author')
            ->orderByDesc('created_at');

        $total = $query->count();
        $messages = $query->skip(($page - 1) * $perPage)->take($perPage)->get();

        return [
            'messages' => $messages->map(fn ($m) => [
                'id' => $m->id,
                'body' => $m->body,
                'user_id' => (int) $m->user_id,
                'first_name' => $m->author->first_name ?? '',
                'last_name' => $m->author->last_name ?? '',
                'created_at' => (string) $m->created_at,
            ])->values()->all(),
            'page' => $page,
            'per_page' => $perPage,
            'total' => $total,
        ];
    }
}
