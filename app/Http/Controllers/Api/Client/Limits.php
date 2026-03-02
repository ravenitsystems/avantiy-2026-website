<?php

namespace App\Http\Controllers\Api\Client;

use App\Http\Controllers\ApiBase;
use App\Models\ClientAssociation;
use App\Models\Team;
use App\Services\CurrentUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class Limits extends ApiBase
{
    private const LIMIT_CONTRACTOR = 20;

    private const LIMIT_AGENCY = 200;

    public function handle(Request $request): array
    {
        if (! CurrentUser::isLoggedIn()) {
            $this->setFail();
            $this->setResponseCode(401);

            return ['message' => 'You must be logged in.'];
        }
        $userId = (int) $request->session()->get('user_id');

        $accountType = CurrentUser::getAccountType();
        if ($accountType === null || $accountType === '') {
            $this->setFail();
            $this->setResponseCode(401);

            return ['message' => 'User not found.'];
        }
        $canAdd = in_array($accountType, ['contractor', 'agency', 'enterprise'], true);
        $limit = match ($accountType) {
            'contractor' => self::LIMIT_CONTRACTOR,
            'agency' => self::LIMIT_AGENCY,
            'enterprise' => null,
            default => null,
        };

        $countPersonal = $canAdd
            ? ClientAssociation::where('scope_type', 'user')->where('scope_user_id', $userId)->count()
            : 0;

        $countByTeam = [];
        if ($canAdd) {
            $teamIds = Team::where('user_id', $userId)->pluck('id');
            $validTeamIds = Team::pluck('id');
            $memberTeamIds = DB::table('team_member')
                ->where('user_id', $userId)
                ->whereIn('team_id', $validTeamIds)
                ->pluck('team_id');
            $allTeamIds = $teamIds->merge($memberTeamIds)->unique()->values();
            foreach ($allTeamIds as $tid) {
                $countByTeam[(string) $tid] = ClientAssociation::where('scope_type', 'team')
                    ->where('scope_team_id', $tid)
                    ->count();
            }
        }

        return [
            'can_add_client_association' => $canAdd,
            'client_limit' => $limit,
            'client_count_personal' => $countPersonal,
            'client_count_by_team' => $countByTeam,
        ];
    }
}
