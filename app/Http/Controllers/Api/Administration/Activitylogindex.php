<?php

namespace App\Http\Controllers\Api\Administration;

use App\Http\Controllers\ApiBase;
use App\Services\CurrentUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class Activitylogindex extends ApiBase
{
    public static function canAccessActivityLog(): bool
    {
        $code = (string) (CurrentUser::getAdminCode() ?? '');
        return str_contains($code, 'A') || str_contains($code, 'L');
    }

    public function handle(Request $request): array
    {
        if (! CurrentUser::isLoggedIn()) {
            $this->setFail();
            $this->setResponseCode(401);
            return ['message' => 'You must be logged in.', 'entries' => [], 'meta' => []];
        }

        if (! self::canAccessActivityLog()) {
            $this->setFail();
            $this->setResponseCode(403);
            return ['message' => 'You do not have permission to view the activity log.', 'entries' => [], 'meta' => []];
        }

        $page = max(1, (int) $request->input('page', 1));
        $perPage = min(100, max(10, (int) $request->input('per_page', 25)));
        $userEmail = $request->input('user_email');
        $teamId = $request->input('team_id');
        $actionClass = $request->input('action_class');
        $actionName = $request->input('action_name');
        $ipAddress = $request->input('ip_address');
        $dateFrom = $request->input('date_from');
        $dateTo = $request->input('date_to');

        $select = [
            'activity_log.id',
            'activity_log.user_id',
            'activity_log.team_id',
            'activity_log.action',
            'activity_log.context',
            'activity_log.ip_address',
            'activity_log.user_agent',
            'activity_log.created_at',
            'user.email as user_email',
            'team.name as team_name',
        ];
        if (Schema::hasColumn('activity_log', 'action_class')) {
            $select[] = 'activity_log.action_class';
        }
        if (Schema::hasColumn('activity_log', 'action_name')) {
            $select[] = 'activity_log.action_name';
        }

        $query = DB::table('activity_log')
            ->leftJoin('user', 'activity_log.user_id', '=', 'user.id')
            ->leftJoin('team', 'activity_log.team_id', '=', 'team.id')
            ->select($select)
            ->orderByDesc('activity_log.created_at');

        if ($userEmail !== null && $userEmail !== '') {
            $query->where('user.email', 'like', '%' . $userEmail . '%');
        }
        if ($teamId !== null && $teamId !== '') {
            $query->where('activity_log.team_id', (int) $teamId);
        }
        if ($actionClass !== null && $actionClass !== '' && Schema::hasColumn('activity_log', 'action_class')) {
            $query->where('activity_log.action_class', $actionClass);
        }
        if ($actionName !== null && $actionName !== '' && Schema::hasColumn('activity_log', 'action_name')) {
            $query->where('activity_log.action_name', $actionName);
        }
        if ($ipAddress !== null && $ipAddress !== '') {
            $query->where('activity_log.ip_address', 'like', '%' . $ipAddress . '%');
        }
        if ($dateFrom !== null && $dateFrom !== '') {
            $query->whereDate('activity_log.created_at', '>=', $dateFrom);
        }
        if ($dateTo !== null && $dateTo !== '') {
            $query->whereDate('activity_log.created_at', '<=', $dateTo);
        }

        $total = $query->count('activity_log.id');
        $rows = (clone $query)
            ->offset(($page - 1) * $perPage)
            ->limit($perPage)
            ->get();

        $entries = $rows->map(function ($row) {
            $userDisplay = $row->user_email ?? '—';
            $context = $row->context;
            if (is_string($context)) {
                $decoded = json_decode($context, true);
                $context = is_array($decoded) ? $decoded : [];
            }
            $actionDisplay = $row->action ?? '';
            if ($actionDisplay === '' && isset($row->action_class, $row->action_name) && $row->action_class && $row->action_name) {
                $actionDisplay = $row->action_class . '.' . $row->action_name;
            }

            return [
                'id' => (int) $row->id,
                'user_id' => $row->user_id ? (int) $row->user_id : null,
                'user_display' => $userDisplay,
                'team_id' => $row->team_id ? (int) $row->team_id : null,
                'team_name' => $row->team_name ?? '—',
                'action' => $actionDisplay,
                'context' => $context,
                'ip_address' => $row->ip_address ?? '—',
                'user_agent' => $row->user_agent ?? null,
                'created_at' => $row->created_at ? (string) $row->created_at : null,
            ];
        })->values()->all();

        return [
            'entries' => $entries,
            'meta' => [
                'total' => $total,
                'per_page' => $perPage,
                'current_page' => $page,
                'last_page' => (int) max(1, ceil($total / $perPage)),
            ],
        ];
    }
}
