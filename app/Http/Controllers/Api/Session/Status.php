<?php

namespace App\Http\Controllers\Api\Session;

use App\Http\Controllers\ApiBase;
use App\Models\ClientAssociation;
use App\Models\Team;
use App\Models\TeamInvitation;
use App\Models\User;
use App\Services\CurrentUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class Status extends ApiBase
{
    public function handle(Request $request): array
    {
        if (! CurrentUser::isLoggedIn()) {
            return [
                'user_id' => 0,
                'first_name' => '',
                'last_name' => '',
                'email' => '',
                'marketing' => false,
                'two_factor_enabled' => false,
                'admin_code' => '',
                'account_type' => null,
                'can_add_client_association' => false,
                'client_limit' => null,
                'client_count_personal' => 0,
                'can_create_team' => false,
                'team_count' => 0,
                'team_limit' => null,
                'has_any_team' => false,
                'active_team_id' => null,
                'active_team_name' => null,
                'pending_invitation_count' => 0,
                'teams_list' => [],
                'locale' => config('app.locale'),
            ];
        }

        $userId = (int) $request->session()->get('user_id');
        $user = User::find($userId);
        if (! $user) {
            return [
                'user_id' => 0,
                'first_name' => '',
                'last_name' => '',
                'email' => '',
                'marketing' => false,
                'two_factor_enabled' => false,
                'admin_code' => '',
                'account_type' => null,
                'can_add_client_association' => false,
                'client_limit' => null,
                'client_count_personal' => 0,
                'can_create_team' => false,
                'team_count' => 0,
                'team_limit' => null,
                'has_any_team' => false,
                'active_team_id' => null,
                'active_team_name' => null,
                'pending_invitation_count' => 0,
                'teams_list' => [],
                'locale' => config('app.locale'),
            ];
        }

        $accountType = CurrentUser::getAccountType();

        $canAddClient = in_array($accountType, ['contractor', 'agency', 'enterprise'], true);
        $clientLimit = match ($accountType) {
            'contractor' => 20,
            'agency' => 200,
            'enterprise' => null,
            default => null,
        };
        $clientCountPersonal = $canAddClient
            ? ClientAssociation::where('scope_type', 'user')->where('scope_user_id', $user->getKey())->count()
            : 0;

        $canCreateTeam = in_array($accountType, ['agency', 'enterprise'], true);
        $teamCount = $canCreateTeam ? $user->teams()->count() : 0;
        $teamLimit = match ($accountType) {
            'agency' => 3,
            'enterprise' => null,
            default => null,
        };
        $validTeamIds = Team::pluck('id');
        $validTeamIds = Team::pluck('id');
        $hasAnyTeam = $user->teams()->exists()
            || DB::table('team_member')->where('user_id', $user->getKey())->whereIn('team_id', $validTeamIds)->exists();

        // Active team context
        $activeTeamId = $request->session()->get('active_team_id');
        $activeTeamName = null;
        if ($activeTeamId) {
            $isMember = DB::table('team_member')
                ->where('team_id', $activeTeamId)
                ->where('user_id', $user->getKey())
                ->exists();

            if ($isMember) {
                $activeTeam = Team::find($activeTeamId);
                $activeTeamName = $activeTeam?->name;
            }

            if (!$isMember || !$activeTeamName) {
                $request->session()->forget('active_team_id');
                $activeTeamId = null;
            }
        }

        // Pending invitations count (exclude invitations to deleted teams)
        $pendingInvitationCount = TeamInvitation::where('email', CurrentUser::getEmail())
            ->where('status', 'pending')
            ->where('expires_at', '>', now())
            ->whereHas('team')
            ->count();

        // All teams the user belongs to (for the context switcher)
        $ownedIds = $user->teams()->pluck('id');
        $memberIds = DB::table('team_member')
            ->where('user_id', $user->getKey())
            ->whereIn('team_id', $validTeamIds)
            ->pluck('team_id');
        $allTeamIds = $ownedIds->merge($memberIds)->unique()->values();

        $teamsList = $allTeamIds->isEmpty() ? [] : Team::whereIn('id', $allTeamIds)
            ->orderBy('name')
            ->get()
            ->map(fn (Team $t) => [
                'id' => $t->id,
                'name' => $t->name,
                'is_owner' => (int) $t->user_id === (int) $user->getKey(),
            ])
            ->values()
            ->all();

        return [
            'user_id' => (int) $user->getKey(),
            'first_name' => CurrentUser::getFirstName() ?? '',
            'last_name' => CurrentUser::getLastName() ?? '',
            'email' => CurrentUser::getEmail() ?? '',
            'marketing' => (bool) $user->marketing,
            'two_factor_enabled' => $user->two_factor_confirmed_at !== null,
            'admin_code' => (string) (CurrentUser::getAdminCode() ?? ''),
            'account_type' => $accountType,
            'can_add_client_association' => $canAddClient,
            'client_limit' => $clientLimit,
            'client_count_personal' => $clientCountPersonal,
            'can_create_team' => $canCreateTeam,
            'team_count' => $teamCount,
            'team_limit' => $teamLimit,
            'has_any_team' => $hasAnyTeam,
            'active_team_id' => $activeTeamId ? (int) $activeTeamId : null,
            'active_team_name' => $activeTeamName,
            'pending_invitation_count' => $pendingInvitationCount,
            'teams_list' => $teamsList,
            'locale' => config('app.locale'),
        ];
    }
}
