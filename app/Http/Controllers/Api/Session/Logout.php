<?php

namespace App\Http\Controllers\Api\Session;

use App\Http\Controllers\ApiBase;
use Illuminate\Http\Request;

class Logout extends ApiBase
{
    public function handle(Request $request): array
    {
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return ['message' => 'Signed out.'];
    }
}
