<?php

namespace App\Http\Controllers\Api\Team;

use App\Http\Controllers\ApiBase;
use App\Models\TeamInvitation;
use App\Services\CurrentUser;
use Illuminate\Http\Request;

class Myinvitations extends ApiBase
{
    public function handle(Request $request): array
    {
        if (! CurrentUser::isLoggedIn()) {
            $this->setFail();
            $this->setResponseCode(401);

            return ['message' => 'You must be logged in.'];
        }

        $userEmail = CurrentUser::getEmail();
        if ($userEmail === null || $userEmail === '') {
            $this->setFail();
            $this->setResponseCode(401);

            return ['message' => 'User not found.'];
        }

        $invitations = TeamInvitation::with('team')
            ->where('email', $userEmail)
            ->where('status', 'pending')
            ->where('expires_at', '>', now())
            ->whereHas('team')
            ->orderByDesc('created_at')
            ->get();

        return [
            'invitations' => $invitations->map(fn ($inv) => [
                'id' => $inv->id,
                'team_id' => (int) $inv->team_id,
                'team_name' => $inv->team?->name ?? '',
                'token' => $inv->token,
                'permissions' => $inv->permissions ?? [],
                'expires_at' => (string) $inv->expires_at,
                'created_at' => (string) $inv->created_at,
            ])->values()->all(),
        ];
    }
}
