<?php

namespace App\Http\Controllers\Api\Session;

use App\Http\Controllers\ApiBase;
use App\Jobs\SendMail;
use App\Services\EmailValidation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class Forgotpassword extends ApiBase
{
    private const CODE_LENGTH = 6;
    private const CODE_EXPIRY_MINUTES = 15;


    public function handle(Request $request): array
    {
        $email = Str::lower(trim((string) $request->input('email')));

        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->setFail();
            $this->setResponseCode(400);

            return ['message' => 'A valid email address is required.'];
        }

        if (EmailValidation::rejectEmailWithPlus($email)) {
            $this->setFail();
            $this->setResponseCode(400);

            return ['message' => EmailValidation::rejectEmailWithPlusMessage()];
        }

        $forgotQuery = DB::table('user')->where('email', $email);
        if (Schema::hasColumn('user', 'deleted')) {
            $forgotQuery->where('deleted', false);
        }
        $user = $forgotQuery->first();
        if (!$user) {
            return [
                'message' => 'If an account exists for that email, we\'ve sent a verification code. Check your inbox.',
                'email' => $email,
            ];
        }

        $code = (string) random_int(10 ** (self::CODE_LENGTH - 1), 10 ** self::CODE_LENGTH - 1);
        $codeHash = Hash::make($code);
        $expiresAt = now()->addMinutes(self::CODE_EXPIRY_MINUTES);

        DB::table('password_reset')->where('email', $email)->delete();
        DB::table('password_reset')->insert([
            'email' => $email,
            'code_hash' => $codeHash,
            'expires_at' => $expiresAt,
            'created_at' => now(),
        ]);

        try {
            $firstName = $user->first_name ?? 'User';
            SendMail::dispatch($email, $user->first_name . ' ' . $user->last_name, 5, ['first_name' => $firstName, 'token' => $code]);
        } catch (\Throwable $e) {
            $this->setFail();
            $this->setResponseCode(500);

            return ['message' => 'Failed to send verification email. Please try again later.'];
        }

        return [
            'message' => 'If an account exists for that email, we\'ve sent a verification code. Check your inbox.',
            'email' => $email,
        ];
    }
}
