<?php

namespace App\Http\Controllers\Api\Session;

use App\Http\Controllers\ApiBase;
use App\Services\CurrentUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class Updatemarketing extends ApiBase
{
    public function handle(Request $request): array
    {
        if (! CurrentUser::isLoggedIn()) {
            $this->setFail();
            $this->setResponseCode(401);
            return ['message' => 'You must be signed in to update preferences.'];
        }
        $userId = (int) $request->session()->get('user_id');

        $marketing = filter_var($request->input('marketing'), FILTER_VALIDATE_BOOLEAN);

        DB::table('user')->where('id', $userId)->update(['marketing' => $marketing]);

        return [
            'message' => $marketing ? 'You are now opted in to marketing emails.' : 'You are now opted out of marketing emails.',
            'marketing' => $marketing,
        ];
    }
}
