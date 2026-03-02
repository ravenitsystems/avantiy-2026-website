<?php

namespace App\Http\Controllers\Api\Administration;

use App\Http\Controllers\ApiBase;
use App\Services\CurrentUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class Activitylogfilters extends ApiBase
{
    public function handle(Request $request): array
    {
        if (! CurrentUser::isLoggedIn()) {
            $this->setFail();
            $this->setResponseCode(401);
            return ['message' => 'You must be logged in.', 'teams' => [], 'action_classes' => [], 'actions' => []];
        }

        if (! Activitylogindex::canAccessActivityLog()) {
            $this->setFail();
            $this->setResponseCode(403);
            return ['message' => 'You do not have permission.', 'teams' => [], 'action_classes' => [], 'actions' => []];
        }

        $teamsQuery = DB::table('activity_log')
            ->join('team', 'activity_log.team_id', '=', 'team.id')
            ->select('team.id', 'team.name')
            ->distinct()
            ->orderBy('team.name');
        if (Schema::hasColumn('team', 'deleted')) {
            $teamsQuery->where('team.deleted', false);
        }
        $teams = $teamsQuery->get()
            ->map(fn ($t) => ['id' => (int) $t->id, 'label' => $t->name ?? ''])
            ->values()->all();

        $actionClasses = [];
        if (Schema::hasColumn('activity_log', 'action_class')) {
            $actionClasses = DB::table('activity_log')
                ->select('action_class')
                ->whereNotNull('action_class')
                ->distinct()
                ->orderBy('action_class')
                ->pluck('action_class')
                ->values()->all();
        }

        $actions = [];
        $actionClass = $request->input('action_class');
        if ($actionClass && Schema::hasColumn('activity_log', 'action_name')) {
            $actions = DB::table('activity_log')
                ->select('action_name')
                ->where('action_class', $actionClass)
                ->whereNotNull('action_name')
                ->distinct()
                ->orderBy('action_name')
                ->pluck('action_name')
                ->values()->all();
        }

        return ['teams' => $teams, 'action_classes' => $actionClasses, 'actions' => $actions];
    }
}
