<?php

namespace App\Services;

use App\Services\CurrentUser;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Static service to record client actions for auditing and support.
 * Call from anywhere: ActivityLog::log('action.name', ['key' => 'value']);
 *
 * When called during an HTTP request, user_id and team_id are taken from the session and
 * ip_address/user_agent from the request. Pass user_id/team_id when outside a request (e.g. jobs).
 */
class ActivityLog
{
    /**
     * Record an action with optional context.
     *
     * @param  string  $action  Action identifier (e.g. "session.login", "payment_method.added")
     * @param  array<string, mixed>  $context  Arbitrary context data (will be JSON-encoded; avoid objects)
     * @param  int|null  $userId  User ID; when null, resolved from current request session
     * @param  int|null  $teamId  Team ID (active team context); when null, resolved from session
     */
    public static function log(string $action, array $context = [], ?int $userId = null, ?int $teamId = null): void
    {
        $userId = $userId ?? self::resolveUserId();
        $teamId = $teamId ?? self::resolveTeamId();
        $ip = null;
        $userAgent = null;
        if (app()->has('request')) {
            $req = app('request');
            $ip = $req->ip();
            $userAgent = $req->userAgent();
        }
        if ($userAgent !== null && strlen($userAgent) > 512) {
            $userAgent = substr($userAgent, 0, 512);
        }

        $fullAction = substr($action, 0, 128);
        $dot = strpos($action, '.');
        $actionClass = $dot !== false ? substr(substr($action, 0, $dot), 0, 64) : null;
        $actionName = $dot !== false ? substr(substr($action, $dot + 1), 0, 64) : ($action ? substr($action, 0, 64) : null);

        $data = [
            'user_id' => $userId ?: null,
            'team_id' => $teamId ?: null,
            'action' => $fullAction,
            'context' => $context === [] ? null : json_encode($context),
            'ip_address' => $ip,
            'user_agent' => $userAgent,
        ];
        if (Schema::hasColumn('activity_log', 'action_class')) {
            $data['action_class'] = $actionClass;
        }
        if (Schema::hasColumn('activity_log', 'action_name')) {
            $data['action_name'] = $actionName;
        }

        try {
            DB::table('activity_log')->insert($data);
        } catch (\Throwable $e) {
            // Fail silently so logging never breaks the request; optionally report to your error service
            report($e);
        }
    }

    private static function resolveUserId(): ?int
    {
        if (! CurrentUser::isLoggedIn()) {
            return null;
        }
        $id = session()->get('user_id');
        return $id !== null ? (int) $id : null;
    }

    private static function resolveTeamId(): ?int
    {
        $id = session()->get('active_team_id');
        return $id !== null ? (int) $id : null;
    }
}
