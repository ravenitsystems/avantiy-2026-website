<?php

namespace App\Http\Controllers\Api\Client;

use App\Http\Controllers\ApiBase;
use App\Models\Client;
use App\Models\ClientAssociation;
use App\Models\Team;
use App\Services\CurrentUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class Create extends ApiBase
{
    private const ALLOWED_ACCOUNT_TYPES = ['contractor', 'agency', 'enterprise'];

    private const LIMIT_CONTRACTOR = 20;

    private const LIMIT_AGENCY = 200;

    public function handle(Request $request): array
    {
        if (! CurrentUser::isLoggedIn()) {
            $this->setFail();
            $this->setResponseCode(401);

            return ['message' => 'You must be logged in to add a client.'];
        }
        $userId = (int) $request->session()->get('user_id');

        $accountType = CurrentUser::getAccountType();
        if ($accountType === null || $accountType === '') {
            $this->setFail();
            $this->setResponseCode(401);

            return ['message' => 'User not found.'];
        }
        if (! in_array($accountType, self::ALLOWED_ACCOUNT_TYPES, true)) {
            $this->setFail();
            $this->setResponseCode(403);

            return ['message' => 'Your account type does not allow adding clients.'];
        }

        $scopeType = $request->input('scope_type');
        if (!in_array($scopeType, ['user', 'team'], true)) {
            $this->setFail();
            $this->setResponseCode(400);

            return ['message' => 'Invalid scope_type. Use "user" or "team".'];
        }

        $scopeUserId = null;
        $scopeTeamId = null;

        if ($scopeType === 'user') {
            $scopeUserId = (int) $userId;
            $scopeTeamId = null;
        } else {
            $scopeTeamId = $request->input('scope_team_id');
            if (empty($scopeTeamId)) {
                $this->setFail();
                $this->setResponseCode(400);

                return ['message' => 'scope_team_id is required when scope_type is "team".'];
            }
            $scopeTeamId = (int) $scopeTeamId;

            $team = Team::find($scopeTeamId);
            if (!$team) {
                $this->setFail();
                $this->setResponseCode(404);

                return ['message' => 'Team not found.'];
            }

            $isOwner = (int) $team->user_id === (int) $userId;
            $isMember = DB::table('team_member')
                ->where('team_id', $scopeTeamId)
                ->where('user_id', $userId)
                ->exists();
            if (!$isOwner && !$isMember) {
                $this->setFail();
                $this->setResponseCode(403);

                return ['message' => 'You do not have access to this team.'];
            }

            if (!empty($team->suspended ?? false)) {
                $this->setFail();
                $this->setResponseCode(400);

                return ['message' => 'Cannot add clients to a suspended team.'];
            }
        }

        $limit = match ($accountType) {
            'contractor' => self::LIMIT_CONTRACTOR,
            'agency' => self::LIMIT_AGENCY,
            'enterprise' => null,
            default => null,
        };

        if ($limit !== null) {
            $count = ClientAssociation::query()
                ->when($scopeType === 'user', fn ($q) => $q->where('scope_type', 'user')->where('scope_user_id', $userId))
                ->when($scopeType === 'team', fn ($q) => $q->where('scope_type', 'team')->where('scope_team_id', $scopeTeamId))
                ->count();
            if ($count >= $limit) {
                $this->setFail();
                $this->setResponseCode(400);

                return ['message' => "Client limit reached ({$limit} for your account type)."];
            }
        }

        $name = trim((string) $request->input('name', ''));
        $email = trim((string) $request->input('email', ''));
        if ($name === '' || $email === '') {
            $this->setFail();
            $this->setResponseCode(400);

            return ['message' => 'Name and email are required.'];
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->setFail();
            $this->setResponseCode(400);

            return ['message' => 'Invalid email address.'];
        }

        $permissions = $request->input('permissions');
        if (!is_array($permissions)) {
            $permissions = [];
        }
        $allowed = config('client.allowed_permissions', ['asset.client_dummy.view', 'asset.client_dummy.edit']);
        $permissions = array_values(array_unique(array_intersect($permissions, $allowed)));

        $client = Client::firstOrCreate(
            ['email' => $email],
            ['name' => $name, 'user_id' => null]
        );

        if ($client->name !== $name) {
            $client->update(['name' => $name]);
        }

        $existing = ClientAssociation::where('client_id', $client->id)
            ->where('scope_type', $scopeType)
            ->where('scope_user_id', $scopeUserId)
            ->where('scope_team_id', $scopeTeamId)
            ->exists();

        if ($existing) {
            $this->setFail();
            $this->setResponseCode(400);

            return ['message' => 'This client is already associated with this scope.'];
        }

        $association = ClientAssociation::create([
            'client_id' => $client->id,
            'scope_type' => $scopeType,
            'scope_user_id' => $scopeUserId,
            'scope_team_id' => $scopeTeamId,
            'permissions' => $permissions,
        ]);

        return [
            'client' => [
                'id' => $client->id,
                'name' => $client->name,
                'email' => $client->email,
            ],
            'association' => [
                'id' => $association->id,
                'scope_type' => $association->scope_type,
                'scope_user_id' => $association->scope_user_id,
                'scope_team_id' => $association->scope_team_id,
                'permissions' => $association->permissions ?? [],
            ],
        ];
    }
}
