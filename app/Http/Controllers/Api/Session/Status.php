<?php

namespace App\Http\Controllers\Api\Session;

use App\Http\Controllers\ApiBase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class Status extends ApiBase
{
    public function handle(Request $request): array
    {
        $userId = $request->session()->get('user_id');

        if ($userId === null) {
            return [
                'user_id' => 0,
                'first_name' => '',
                'email' => '',
            ];
        }

        $row = DB::table('user')->where('id', $userId)->first();

        if (!$row) {
            $request->session()->forget('user_id');

            return [
                'user_id' => 0,
                'first_name' => '',
                'email' => '',
            ];
        }

        return [
            'user_id' => (int) $row->id,
            'first_name' => $row->first_name ?? '',
            'last_name' => $row->last_name ?? '',
            'email' => $row->email ?? '',
        ];
    }
}
