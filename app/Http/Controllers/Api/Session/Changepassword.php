<?php

namespace App\Http\Controllers\Api\Session;

use App\Http\Controllers\ApiBase;
use App\Services\CurrentUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Hash;

class Changepassword extends ApiBase
{
    public function handle(Request $request): array
    {
        if (! CurrentUser::isLoggedIn()) {
            $this->setFail();
            $this->setResponseCode(401);
            return ['message' => 'You must be signed in to change your password.'];
        }
        $userId = (int) $request->session()->get('user_id');

        $currentPassword = $request->input('current_password');
        $newPassword = $request->input('new_password');

        if (empty($currentPassword) || empty($newPassword)) {
            $this->setFail();
            $this->setResponseCode(400);
            return ['message' => 'Current password and new password are required.'];
        }

        if (!preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[^a-zA-Z\d]).{8,}$/', $newPassword)) {
            $this->setFail();
            $this->setResponseCode(400);
            return ['message' => 'New password must be at least 8 characters and include uppercase, lowercase, number, and special character.'];
        }

        $pwdQuery = DB::table('user')->where('id', $userId);
        if (Schema::hasColumn('user', 'deleted')) {
            $pwdQuery->where('deleted', false);
        }
        $row = $pwdQuery->first();
        if (!$row || !Hash::check($currentPassword, $row->password)) {
            $this->setFail();
            $this->setResponseCode(400);
            return ['message' => 'Current password is incorrect.'];
        }

        DB::table('user')->where('id', $userId)->update([
            'password' => Hash::make($newPassword),
        ]);

        return ['message' => 'Your password has been updated.'];
    }
}
