<?php

namespace App\Http\Controllers\Api\Team;

use App\Http\Controllers\ApiBase;
use App\Models\TeamInvitation;
use App\Services\CurrentUser;
use Illuminate\Http\Request;

class Declineinvite extends ApiBase
{
    public function handle(Request $request): array
    {
        if (! CurrentUser::isLoggedIn()) {
            $this->setFail();
            $this->setResponseCode(401);

            return ['message' => 'You must be logged in.'];
        }
        $userId = (int) $request->session()->get('user_id');

        $userEmail = CurrentUser::getEmail();
        if ($userEmail === null || $userEmail === '') {
            $this->setFail();
            $this->setResponseCode(401);

            return ['message' => 'User not found.'];
        }

        $token = trim((string) $request->input('token', ''));
        if ($token === '') {
            $this->setFail();
            $this->setResponseCode(400);

            return ['message' => 'Invitation token is required.'];
        }

        $invitation = TeamInvitation::where('token', $token)
            ->where('status', 'pending')
            ->first();

        if (! $invitation) {
            $this->setFail();
            $this->setResponseCode(404);

            return ['message' => 'Invitation not found.'];
        }

        if (strtolower($userEmail) !== strtolower($invitation->email)) {
            $this->setFail();
            $this->setResponseCode(403);

            return ['message' => 'This invitation is not addressed to your account.'];
        }

        $invitation->update(['status' => 'declined']);

        return ['message' => 'Invitation declined.'];
    }
}
