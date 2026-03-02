<?php

namespace App\Http\Controllers\Api\Website;

use App\Http\Controllers\ApiBase;
use App\Models\Team;
use App\Services\CurrentUser;
use App\Services\Duda;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class Editurl extends ApiBase
{
    public function handle(Request $request): array
    {
        if (!CurrentUser::isLoggedIn()) {
            $this->setFail();
            $this->setResponseCode(401);
            return ['message' => 'You must be logged in to edit websites.'];
        }

        $websiteId = $request->route('id1');
        if ($websiteId === null || $websiteId === '') {
            $this->setFail();
            $this->setResponseCode(400);
            return ['message' => 'Website ID is required.'];
        }

        $userId = (int) $request->session()->get('user_id');
        $activeTeamId = $request->session()->get('active_team_id');
        $ownerId = $userId;
        if ($activeTeamId !== null) {
            $team = DB::table("team")->where("id", $activeTeamId)->where('deleted', false)->first();
            if ($team === null) {
                throw new Exception("Invalid team id.");
            }
            $ownerId = $team->user_id;
        }

        $website = DB::table('website')
            ->where('id', (int) $websiteId)
            ->where('user_id', $ownerId)
            ->where('deleted', false)
            ->first();

        if ($website === null) {
            $this->setFail();
            $this->setResponseCode(404);
            return ['message' => 'Website not found.'];
        }



        $ownerQuery = DB::table('user')->where('id', $website->user_id);
        if (Schema::hasColumn('user', 'deleted')) {
            $ownerQuery->where('deleted', false);
        }
        $owner = $ownerQuery->first(['duda_username']);
        if ($owner === null || empty($owner->duda_username)) {
            $this->setFail();
            $this->setResponseCode(400);

            return ['message' => 'Website owner does not have a Duda account configured.'];
        }

        try {
            $redirectUrl = Duda::getEditorSsoLink($owner->duda_username, $website->duda_id ?? '');

            return ['redirect_url' => $redirectUrl];
        } catch (Exception $e) {
            $this->setFail();
            $this->setResponseCode(502);

            return ['message' => $e->getMessage()];
        }
    }
}
