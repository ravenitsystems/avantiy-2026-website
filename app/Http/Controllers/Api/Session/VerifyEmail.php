<?php

namespace App\Http\Controllers\Api\Session;

use App\Http\Controllers\ApiBase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class VerifyEmail extends ApiBase
{
    public function handle(Request $request): array
    {
        $email = Str::lower(trim((string) $request->input('email')));
        $code = (string) $request->input('verification_code', '');

        if (empty($email) || empty($code)) {
            $this->setFail();
            $this->setResponseCode(400);

            return ['message' => 'Email and verification code are required.'];
        }

        $pending = DB::table('email_verification')
            ->where('email', $email)
            ->first();

        if (!$pending) {
            $this->setFail();
            $this->setResponseCode(400);

            return ['message' => 'Invalid or expired verification code.'];
        }

        if (now()->greaterThan($pending->expires_at)) {
            DB::table('email_verification')->where('id', $pending->id)->delete();
            $this->setFail();
            $this->setResponseCode(400);

            return ['message' => 'Verification code has expired. Please request a new one.'];
        }

        if (!\Illuminate\Support\Facades\Hash::check($code, $pending->code_hash)) {
            $this->setFail();
            $this->setResponseCode(400);

            return ['message' => 'Invalid verification code.'];
        }

        $registrationData = json_decode($pending->registration_data, true);

        $userId = DB::table('user')->insertGetId([
            'date_created' => now(),
            'date_last_login' => null,
            'admin_code' => '',
            'pricing_group' => 'standard',
            'duda_username' => null,
            'stripe_username' => null,
            'first_name' => $registrationData['first_name'],
            'last_name' => $registrationData['last_name'],
            'email' => $email,
            'password' => $registrationData['password'],
            'country_id' => $registrationData['country_id'],
            'telephone' => $registrationData['telephone'],
            'marketing' => $registrationData['marketing'],
            'two_factor_secret' => null,
            'two_factor_confirmed_at' => null,
            'two_factor_recovery_codes' => null,
        ]);

        DB::table('email_verification')->where('id', $pending->id)->delete();

        $request->session()->put('user_id', $userId);

        $user = DB::table('user')->where('id', $userId)->first();

        return [
            'user_id' => (int) $user->id,
            'first_name' => $user->first_name,
            'last_name' => $user->last_name,
            'email' => $user->email,
        ];
    }
}
