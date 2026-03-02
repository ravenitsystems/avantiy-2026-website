<?php

namespace App\Http\Controllers\Api\Session;

use App\Http\Controllers\ApiBase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class Resetpassword extends ApiBase
{
    public function handle(Request $request): array
    {
        $email = Str::lower(trim((string) $request->input('email')));
        $code = (string) $request->input('verification_code', '');
        $password = $request->input('password');

        if (empty($email) || empty($code)) {
            $this->setFail();
            $this->setResponseCode(400);

            return ['message' => 'Email and verification code are required.'];
        }

        if (empty($password)) {
            $this->setFail();
            $this->setResponseCode(400);

            return ['message' => 'A new password is required.'];
        }

        if (!preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[^a-zA-Z\d]).{8,}$/', $password)) {
            $this->setFail();
            $this->setResponseCode(400);

            return ['message' => 'Password must be at least 8 characters and include uppercase, lowercase, number, and special character.'];
        }

        $pending = DB::table('password_reset')->where('email', $email)->first();

        if (!$pending) {
            $this->setFail();
            $this->setResponseCode(400);

            return ['message' => 'Invalid or expired verification code.'];
        }

        if (now()->greaterThan($pending->expires_at)) {
            DB::table('password_reset')->where('id', $pending->id)->delete();
            $this->setFail();
            $this->setResponseCode(400);

            return ['message' => 'Verification code has expired. Please request a new one.'];
        }

        if (!Hash::check($code, $pending->code_hash)) {
            $this->setFail();
            $this->setResponseCode(400);

            return ['message' => 'Invalid verification code.'];
        }

        $resetQuery = DB::table('user')->where('email', $email);
        if (Schema::hasColumn('user', 'deleted')) {
            $resetQuery->where('deleted', false);
        }
        $resetQuery->update([
            'password' => Hash::make($password),
        ]);
        DB::table('password_reset')->where('id', $pending->id)->delete();

        return [
            'message' => 'Your password has been reset. You can now sign in.',
        ];
    }
}
