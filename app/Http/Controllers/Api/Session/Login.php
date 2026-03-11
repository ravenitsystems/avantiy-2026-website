<?php

namespace App\Http\Controllers\Api\Session;

use App\Http\Controllers\ApiBase;
use App\Services\ActivityLog;
use App\Services\EmailValidation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Hash;
use PragmaRX\Google2FA\Google2FA;

class Login extends ApiBase
{
    public function handle(Request $request): array
    {
        $email = $request->input('email');
        $password = $request->input('password');

        if (empty($email) || empty($password)) {
            $this->setFail();
            $this->setResponseCode(400);

            return ['message' => 'Email and password are required.'];
        }

        if (EmailValidation::rejectEmailWithPlus($email)) {
            $this->setFail();
            $this->setResponseCode(400);

            return ['message' => EmailValidation::rejectEmailWithPlusMessage()];
        }

        $loginQuery = DB::table('user')->where('email', $email);
        if (Schema::hasColumn('user', 'deleted')) {
            $loginQuery->where('deleted', false);
        }
        $row = $loginQuery->first();

        if (!$row || !Hash::check($password, $row->password)) {
            $this->setFail();
            $this->setResponseCode(401);

            return ['message' => 'Invalid email or password.'];
        }

        $twoFactorCode = $request->input('2fa');
        $hasTwoFactor = $row->two_factor_confirmed_at !== null;

        if ($hasTwoFactor && empty($twoFactorCode)) {
            $this->setPass();

            return ['two_factor_required' => true];
        }

        if ($hasTwoFactor && !empty($twoFactorCode)) {
            if (empty($row->two_factor_secret)) {
                $this->setFail();
                $this->setResponseCode(401);

                return ['message' => 'Two-factor authentication is not set up.'];
            }

            try {
                $secret = Crypt::decrypt($row->two_factor_secret);
            } catch (\Throwable) {
                $this->setFail();
                $this->setResponseCode(401);

                return ['message' => 'Invalid two-factor configuration.'];
            }

            $google2fa = new Google2FA();
            if ($google2fa->verifyKey($secret, $twoFactorCode) === false) {
                $this->setFail();
                $this->setResponseCode(401);

                return ['message' => 'Invalid two-factor code.'];
            }
        }

        $request->session()->put('user_id', $row->id);

        ActivityLog::log('session.login', ['email' => $row->email, 'user_id' => $row->id]);

        return [
            'user_id' => $row->id,
            'first_name' => $row->first_name,
            'last_name' => $row->last_name,
            'email' => $row->email,
        ];
    }
}
