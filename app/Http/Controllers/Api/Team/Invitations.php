<?php

namespace App\Http\Controllers\Api\Team;

use App\Http\Controllers\ApiBase;
use App\Models\Team;
use App\Services\CurrentUser;
use Illuminate\Http\Request;

class Invitations extends ApiBase
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

        if (!$currentMember->hasPermission('team.invite_user')) {
            $this->setFail();
            $this->setResponseCode(403);

            return ['message' => 'You do not have permission to view invitations.'];
        }

        $invitations = $team->invitations()
            ->with('teamRole')
            ->where('status', 'pending')
            ->where('expires_at', '>', now())
            ->orderByDesc('created_at')
            ->get();

        return [
            'invitations' => $invitations->map(fn ($inv) => [
                'id' => $inv->id,
                'email' => $inv->email,
                'status' => $inv->status,
                'role_id' => $inv->team_role_id,
                'role_name' => $inv->teamRole?->name ?? '',
                'expires_at' => (string) $inv->expires_at,
                'created_at' => (string) $inv->created_at,
            ])->values()->all(),
        ];
    }
}
