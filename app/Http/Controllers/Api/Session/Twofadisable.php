<?php

namespace App\Http\Controllers\Api\Session;

use App\Http\Controllers\ApiBase;
use App\Services\CurrentUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Hash;

class Twofadisable extends ApiBase
{
    public function handle(Request $request): array
    {
        if (! CurrentUser::isLoggedIn()) {
            $this->setFail();
            $this->setResponseCode(401);
            return ['message' => 'You must be signed in to disable two-factor authentication.'];
        }
        $userId = (int) $request->session()->get('user_id');

        $password = $request->input('password');
        if (empty($password)) {
            $this->setFail();
            $this->setResponseCode(400);
            return ['message' => 'Your password is required to disable two-factor authentication.'];
        }

        $twofaQuery = DB::table('user')->where('id', $userId);
        if (Schema::hasColumn('user', 'deleted')) {
            $twofaQuery->where('deleted', false);
        }
        $row = $twofaQuery->first();
        if (!$row || !Hash::check($password, $row->password)) {
            $this->setFail();
            $this->setResponseCode(400);
            return ['message' => 'Password is incorrect.'];
        }
        if ($row->two_factor_confirmed_at === null) {
            $this->setFail();
            $this->setResponseCode(400);
            return ['message' => 'Two-factor authentication is not enabled.'];
        }

        DB::table('user')->where('id', $userId)->update([
            'two_factor_secret' => null,
            'two_factor_confirmed_at' => null,
            'two_factor_recovery_codes' => null,
        ]);

        return [
            'message' => 'Two-factor authentication has been disabled.',
            'two_factor_enabled' => false,
        ];
    }
}
