<?php

namespace App\Http\Controllers\Api\Team;

use App\Http\Controllers\ApiBase;
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

            return ['message' => 'You must be logged in to list teams.', 'teams' => []];
        }
        $userId = (int) $request->session()->get('user_id');

        $ownedIds = Team::where('user_id', $userId)->pluck('id');
        $validTeamIds = Team::pluck('id');
        $memberIds = DB::table('team_member')
            ->where('user_id', $userId)
            ->whereIn('team_id', $validTeamIds)
            ->pluck('team_id')
            ->unique()
            ->values();

        $allIds = $ownedIds->merge($memberIds)->unique()->values();

        $teams = Team::whereIn('id', $allIds)
            ->orderBy('name')
            ->get()
            ->map(function (Team $team) use ($userId) {
                $isOwner = (int) $team->user_id === (int) $userId;

                return [
                    'id' => $team->id,
                    'name' => $team->name,
                    'description' => $team->description ?? '',
                    'is_owner' => $isOwner,
                ];
            })
            ->values()
            ->all();

        return ['teams' => $teams];
    }
}
