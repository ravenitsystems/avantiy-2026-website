<?php

namespace App\Http\Controllers\Api\Session;

use App\Http\Controllers\ApiBase;
use App\Services\CurrentUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class Updateprofile extends ApiBase
{
    public function handle(Request $request): array
    {
        if (! CurrentUser::isLoggedIn()) {
            $this->setFail();
            $this->setResponseCode(401);
            return ['message' => 'You must be signed in to update your profile.'];
        }
        $userId = (int) $request->session()->get('user_id');

        $firstName = trim((string) $request->input('first_name', ''));
        $lastName = trim((string) $request->input('last_name', ''));
        $email = Str::lower(trim((string) $request->input('email', '')));
        $telephone = trim((string) $request->input('telephone', ''));
        $countryId = (int) $request->input('country_id', 0);
        $addressLine1 = trim((string) $request->input('address_line_1', ''));
        $addressLine2 = trim((string) $request->input('address_line_2', ''));
        $city = trim((string) $request->input('city', ''));
        $stateRegion = trim((string) $request->input('state_region', ''));
        $postalCode = trim((string) $request->input('postal_code', ''));

        if (empty($firstName) || empty($lastName)) {
            $this->setFail();
            $this->setResponseCode(400);
            return ['message' => 'First name and last name are required.'];
        }

        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->setFail();
            $this->setResponseCode(400);
            return ['message' => 'A valid email address is required.'];
        }

        $existingQuery = DB::table('user')->where('id', $userId);
        if (Schema::hasColumn('user', 'deleted')) {
            $existingQuery->where('deleted', false);
        }
        $existing = $existingQuery->first();
        if (!$existing) {
            $this->setFail();
            $this->setResponseCode(401);
            return ['message' => 'User not found.'];
        }

        if ($email !== $existing->email) {
            $takenQuery = DB::table('user')->where('email', $email)->where('id', '!=', $userId);
            if (Schema::hasColumn('user', 'deleted')) {
                $takenQuery->where('deleted', false);
            }
            $taken = $takenQuery->exists();
            if ($taken) {
                $this->setFail();
                $this->setResponseCode(409);
                return ['message' => 'That email address is already in use.'];
            }
        }

        if ($countryId > 0 && !DB::table('country')->where('id', $countryId)->exists()) {
            $countryId = (int) $existing->country_id;
        }
        if ($countryId <= 0) {
            $countryId = (int) $existing->country_id;
        }

        DB::table('user')->where('id', $userId)->update([
            'first_name' => $firstName,
            'last_name' => $lastName,
            'email' => $email,
            'telephone' => $telephone,
            'country_id' => $countryId,
            'address_line_1' => $addressLine1 === '' ? null : $addressLine1,
            'address_line_2' => $addressLine2 === '' ? null : $addressLine2,
            'city' => $city === '' ? null : $city,
            'state_region' => $stateRegion === '' ? null : $stateRegion,
            'postal_code' => $postalCode === '' ? null : $postalCode,
        ]);

        CurrentUser::reload();

        return [
            'message' => 'Profile updated.',
            'first_name' => $firstName,
            'last_name' => $lastName,
            'email' => $email,
            'telephone' => $telephone,
            'country_id' => $countryId,
            'address_line_1' => $addressLine1,
            'address_line_2' => $addressLine2,
            'city' => $city,
            'state_region' => $stateRegion,
            'postal_code' => $postalCode,
        ];
    }
}
