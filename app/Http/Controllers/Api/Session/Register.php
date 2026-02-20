<?php

namespace App\Http\Controllers\Api\Session;

use App\Http\Controllers\ApiBase;
use App\Mail\VerificationCodeMail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class Register extends ApiBase
{
    private const CODE_LENGTH = 6;
    private const CODE_EXPIRY_MINUTES = 15;

    public function handle(Request $request): array
    {
        $email = Str::lower(trim((string) $request->input('email')));
        $firstName = trim((string) $request->input('first_name', ''));
        $lastName = trim((string) $request->input('last_name', ''));
        $password = $request->input('password');
        $countryId = (int) $request->input('country_id', 840);
        $telephone = trim((string) $request->input('telephone', ''));
        $marketing = (bool) $request->input('marketing', false);

        if (empty($email) || empty($firstName) || empty($lastName) || empty($password)) {
            $this->setFail();
            $this->setResponseCode(400);

            return ['message' => 'Email, first name, last name, and password are required.'];
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->setFail();
            $this->setResponseCode(400);

            return ['message' => 'Invalid email address.'];
        }

        if (!preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[^a-zA-Z\d]).{8,}$/', $password)) {
            $this->setFail();
            $this->setResponseCode(400);

            return ['message' => 'Password must be at least 8 characters and include uppercase, lowercase, number, and special character.'];
        }

        $existingUser = DB::table('user')->where('email', $email)->first();
        if ($existingUser) {
            $this->setFail();
            $this->setResponseCode(409);

            return ['message' => 'An account with this email already exists.'];
        }

        $countryExists = DB::table('country')->where('id', $countryId)->exists();
        if (!$countryExists) {
            $countryId = 840;
        }

        $code = (string) random_int(10 ** (self::CODE_LENGTH - 1), 10 ** self::CODE_LENGTH - 1);
        $codeHash = Hash::make($code);
        $expiresAt = now()->addMinutes(self::CODE_EXPIRY_MINUTES);

        $registrationData = [
            'first_name' => $firstName,
            'last_name' => $lastName,
            'password' => Hash::make($password),
            'country_id' => $countryId,
            'telephone' => $telephone,
            'marketing' => $marketing,
        ];

        DB::table('email_verification')
            ->where('email', $email)
            ->delete();

        DB::table('email_verification')->insert([
            'email' => $email,
            'code_hash' => $codeHash,
            'registration_data' => json_encode($registrationData),
            'expires_at' => $expiresAt,
            'created_at' => now(),
        ]);

        try {
            Mail::to($email)->send(new VerificationCodeMail($code, $email));
        } catch (\Throwable $e) {
            $this->setFail();
            $this->setResponseCode(500);

            return ['message' => 'Failed to send verification email. Please try again later.'];
        }

        return [
            'message' => 'Verification code sent to your email.',
            'email' => $email,
        ];
    }
}
