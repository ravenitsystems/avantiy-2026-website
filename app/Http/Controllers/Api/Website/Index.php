<?php

namespace App\Http\Controllers\Api\Website;

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

            return ['message' => 'You must be logged in to list websites.', 'websites' => []];
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

                    return ['message' => 'You are not a member of the active team.', 'websites' => []];
                }
                if (!$member->hasPermission('websites.view')) {
                    $this->setFail();
                    $this->setResponseCode(403);

                    return ['message' => 'You do not have permission to view websites.', 'websites' => []];
                }
                $ownerUserId = (int) $team->user_id;
            }
        }

        $page = max(1, (int) $request->input('page', 1));
        $perPage = min(50, max(6, (int) $request->input('per_page', 12)));
        $search = trim((string) $request->input('search', ''));

        $query = DB::table('website')
            ->where('user_id', $ownerUserId)
            ->where('deleted', false)
            ->orderByDesc('accessed_at');

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('site_name', 'like', '%' . $search . '%')
                    ->orWhere('domain', 'like', '%' . $search . '%');
            });
        }

        $total = $query->count();
        $rows = $query
            ->offset(($page - 1) * $perPage)
            ->limit($perPage)
            ->get();

        $websites = $rows->map(function ($row) {
            return [
                'id' => (int) $row->id,
                'site_name' => $row->site_name,
                'domain' => $row->domain ?? '',
                'duda_id' => $row->duda_id ?? '',
                'created_at' => $row->created_at,
                'accessed_at' => $row->accessed_at,
                'published_at' => $row->published_at,
                'is_published' => $row->published_at !== null,
            ];
        })->values()->all();

        return [
            'websites' => $websites,
            'meta' => [
                'total' => $total,
                'per_page' => $perPage,
                'current_page' => $page,
                'last_page' => (int) max(1, ceil($total / $perPage)),
            ],
        ];
    }
}
