<?php

namespace App\Http\Controllers\Api\Session;

use App\Http\Controllers\ApiBase;
use App\Services\CurrentUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use PragmaRX\Google2FA\Google2FA;

class Twofaconfirm extends ApiBase
{
    public function handle(Request $request): array
    {
        if (! CurrentUser::isLoggedIn()) {
            $this->setFail();
            $this->setResponseCode(401);
            return ['message' => 'You must be signed in to confirm two-factor authentication.'];
        }
        $userId = (int) $request->session()->get('user_id');

        $code = (string) $request->input('verification_code', '');
        if (strlen($code) !== 6 || !ctype_digit($code)) {
            $this->setFail();
            $this->setResponseCode(400);
            return ['message' => 'Please enter the 6-digit code from your authenticator app.'];
        }

        $secret = $request->session()->get('two_factor_secret_pending');
        if (empty($secret)) {
            $this->setFail();
            $this->setResponseCode(400);
            return ['message' => 'Setup session expired. Please start two-factor setup again.'];
        }

        $google2fa = new Google2FA();
        if ($google2fa->verifyKey($secret, $code) === false) {
            $this->setFail();
            $this->setResponseCode(400);
            return ['message' => 'Invalid code. Please try again.'];
        }

        DB::table('user')->where('id', $userId)->update([
            'two_factor_secret' => Crypt::encrypt($secret),
            'two_factor_confirmed_at' => now(),
        ]);
        $request->session()->forget('two_factor_secret_pending');

        return [
            'message' => 'Two-factor authentication is now enabled.',
            'two_factor_enabled' => true,
        ];
    }
}
