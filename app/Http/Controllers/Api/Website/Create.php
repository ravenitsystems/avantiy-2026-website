<?php

namespace App\Http\Controllers\Api\Website;

use App\Http\Controllers\ApiBase;
use App\Models\Team;
use App\Services\CurrentUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class Create extends ApiBase
{
    public function handle(Request $request): array
    {
        if (! CurrentUser::isLoggedIn()) {
            $this->setFail();
            $this->setResponseCode(401);

            return ['message' => 'You must be logged in to create a website.'];
        }

        $userId = (int) $request->session()->get('user_id');
        $activeTeamId = $request->session()->get('active_team_id');
        $ownerUserId = $userId;

        if ($activeTeamId) {
            $team = Team::find($activeTeamId);
            if (!$team) {
                $request->session()->forget('active_team_id');
            } else {
                $member = $team->memberRecord($userId);
                if (!$member) {
                    $this->setFail();
                    $this->setResponseCode(403);

                    return ['message' => 'You are not a member of the active team.'];
                }
                if (!$member->hasPermission('websites.edit')) {
                    $this->setFail();
                    $this->setResponseCode(403);

                    return ['message' => 'You do not have permission to create websites.'];
                }
                $ownerUserId = (int) $team->user_id;
            }
        }

        $siteName = $request->input('site_name');
        if (empty(trim((string) $siteName))) {
            $this->setFail();
            $this->setResponseCode(400);

            return ['message' => 'Site name is required.'];
        }

        $now = now();
        $data = [
            'site_name' => trim($siteName),
            'domain' => trim((string) $request->input('domain', '')),
            'duda_id' => trim((string) $request->input('duda_id', '')),
            'created_at' => $now,
            'accessed_at' => $now,
            'published_at' => null,
            'user_id' => $ownerUserId,
        ];

        $id = DB::table('website')->insertGetId($data);
        $data['id'] = $id;

        return $data;
    }
}
