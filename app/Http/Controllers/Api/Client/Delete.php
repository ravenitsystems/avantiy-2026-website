<?php

namespace App\Http\Controllers\Api\Client;

use App\Http\Controllers\ApiBase;
use App\Models\ClientAssociation;
use App\Models\Team;
use App\Services\CurrentUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class Delete extends ApiBase
{
    public function handle(Request $request): array
    {
        if (! CurrentUser::isLoggedIn()) {
            $this->setFail();
            $this->setResponseCode(401);

            return ['message' => 'You must be logged in to remove a client.'];
        }
        $userId = (int) $request->session()->get('user_id');

        $id = $request->route('id1');
        $associationId = $id !== null ? (int) $id : 0;
        if ($associationId <= 0) {
            $this->setFail();
            $this->setResponseCode(400);

            return ['message' => 'Invalid association id.'];
        }

        $association = ClientAssociation::with('client')->find($associationId);
        if (!$association) {
            $this->setFail();
            $this->setResponseCode(404);

            return ['message' => 'Client association not found.'];
        }

        if ($association->scope_type === 'user') {
            if ((int) $association->scope_user_id !== (int) $userId) {
                $this->setFail();
                $this->setResponseCode(403);

                return ['message' => 'You do not have access to this client association.'];
            }
        } else {
            $team = Team::find($association->scope_team_id);
            if (!$team) {
                $this->setFail();
                $this->setResponseCode(404);

                return ['message' => 'Team not found.'];
            }
            $isOwner = (int) $team->user_id === (int) $userId;
            $isMember = DB::table('team_member')
                ->where('team_id', $association->scope_team_id)
                ->where('user_id', $userId)
                ->exists();
            if (!$isOwner && !$isMember) {
                $this->setFail();
                $this->setResponseCode(403);

                return ['message' => 'You do not have access to this client association.'];
            }
        }

        $association->delete();

        return ['message' => 'Client removed.'];
    }
}
