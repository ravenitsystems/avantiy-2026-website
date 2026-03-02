<?php

namespace App\Http\Controllers\Api\Session;

use App\Http\Controllers\ApiBase;
use App\Services\CurrentUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use PragmaRX\Google2FA\Google2FA;

class Twofasetup extends ApiBase
{
    public function handle(Request $request): array
    {
        if (! CurrentUser::isLoggedIn()) {
            $this->setFail();
            $this->setResponseCode(401);
            return ['message' => 'You must be signed in to set up two-factor authentication.'];
        }
        $userId = (int) $request->session()->get('user_id');

        $twofaQuery = DB::table('user')->where('id', $userId);
        if (Schema::hasColumn('user', 'deleted')) {
            $twofaQuery->where('deleted', false);
        }
        $row = $twofaQuery->first();
        if (! $row) {
            $this->setFail();
            $this->setResponseCode(401);
            return ['message' => 'User not found.'];
        }
        if ($row->two_factor_confirmed_at !== null) {
            $this->setFail();
            $this->setResponseCode(400);
            return ['message' => 'Two-factor authentication is already enabled.'];
        }

        $google2fa = new Google2FA();
        $secret = $google2fa->generateSecretKey(32);
        $companyName = config('app.name', 'Avantiy');
        $holder = CurrentUser::getEmail() ?? $row->email;
        $qrCodeUrl = $google2fa->getQRCodeUrl($companyName, $holder, $secret);

        $request->session()->put('two_factor_secret_pending', $secret);

        return [
            'qr_code_url' => $qrCodeUrl,
            'secret' => $secret,
        ];
    }
}
