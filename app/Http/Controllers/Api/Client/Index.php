<?php

namespace App\Http\Controllers\Api\Client;

use App\Http\Controllers\ApiBase;
use App\Models\Client;
use App\Models\ClientAssociation;
use App\Models\Team;
use App\Services\CurrentUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class Index extends ApiBase
{
    public function handle(Request $request): array
    {
        if (! CurrentUser::isLoggedIn()) {
            $this->setFail();
            $this->setResponseCode(401);

            return ['message' => 'You must be logged in to list clients.', 'clients' => []];
        }
        $userId = (int) $request->session()->get('user_id');

        $scopeType = $request->input('scope_type', 'user');
        if (!in_array($scopeType, ['user', 'team'], true)) {
            $this->setFail();
            $this->setResponseCode(400);

            return ['message' => 'Invalid scope_type.', 'clients' => []];
        }

        $scopeTeamId = $scopeType === 'team' ? $request->input('scope_team_id') : null;
        if ($scopeType === 'team') {
            if (empty($scopeTeamId)) {
                $this->setFail();
                $this->setResponseCode(400);

                return ['message' => 'scope_team_id is required when scope_type is "team".', 'clients' => []];
            }
            $scopeTeamId = (int) $scopeTeamId;

            $team = Team::find($scopeTeamId);
            if (!$team) {
                $this->setFail();
                $this->setResponseCode(404);

                return ['message' => 'Team not found.', 'clients' => []];
            }

            $isOwner = (int) $team->user_id === (int) $userId;
            $isMember = DB::table('team_member')
                ->where('team_id', $scopeTeamId)
                ->where('user_id', $userId)
                ->exists();
            if (!$isOwner && !$isMember) {
                $this->setFail();
                $this->setResponseCode(403);

                return ['message' => 'You do not have access to this team.', 'clients' => []];
            }
        }

        $query = ClientAssociation::query()
            ->with('client')
            ->when($scopeType === 'user', fn ($q) => $q->where('scope_type', 'user')->where('scope_user_id', $userId))
            ->when($scopeType === 'team', fn ($q) => $q->where('scope_type', 'team')->where('scope_team_id', $scopeTeamId));

        $associations = $query->orderByDesc('created_at')->get();

        $clients = $associations->map(function (ClientAssociation $a) {
            $client = $a->client;

            return [
                'id' => $client->id,
                'name' => $client->name,
                'email' => $client->email,
                'association_id' => $a->id,
                'permissions' => $a->permissions ?? [],
                'created_at' => $a->created_at?->toDateTimeString(),
            ];
        })->values()->all();

        return ['clients' => $clients];
    }
}
