<?php

namespace App\Http\Controllers\Api\Session;

use App\Http\Controllers\ApiBase;
use App\Services\CurrentUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class Profile extends ApiBase
{
    public function handle(Request $request): array
    {
        if (! CurrentUser::isLoggedIn()) {
            $this->setFail();
            $this->setResponseCode(401);
            return ['message' => 'You must be signed in to view your profile.'];
        }
        $userId = (int) $request->session()->get('user_id');

        $profileQuery = DB::table('user')->where('id', $userId);
        if (Schema::hasColumn('user', 'deleted')) {
            $profileQuery->where('deleted', false);
        }
        $row = $profileQuery->first();
        if (!$row) {
            $this->setFail();
            $this->setResponseCode(401);
            return ['message' => 'User not found.'];
        }

        return [
            'first_name' => CurrentUser::getFirstName() ?? '',
            'last_name' => CurrentUser::getLastName() ?? '',
            'email' => CurrentUser::getEmail() ?? '',
            'telephone' => $row->telephone ?? '',
            'country_id' => (int) $row->country_id,
            'address_line_1' => $row->address_line_1 ?? '',
            'address_line_2' => $row->address_line_2 ?? '',
            'city' => $row->city ?? '',
            'state_region' => $row->state_region ?? '',
            'postal_code' => $row->postal_code ?? '',
        ];
    }
}
