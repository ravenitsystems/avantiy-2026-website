<?php

namespace App\Http\Controllers\Api\Administration;

use App\Http\Controllers\ApiBase;
use App\Services\CurrentUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class Countryupdate extends ApiBase
{
    public function handle(Request $request): array
    {
        if (! CurrentUser::isLoggedIn()) {
            $this->setFail();
            $this->setResponseCode(401);
            return ['message' => 'You must be logged in.'];
        }

        if (! Countryindex::canAccessSiteAdmin()) {
            $this->setFail();
            $this->setResponseCode(403);
            return ['message' => 'You do not have permission.'];
        }

        $id1 = $request->route('id1');
        $id = $id1 !== null ? (int) $id1 : 0;
        if ($id <= 0) {
            $this->setFail();
            $this->setResponseCode(400);
            return ['message' => 'Invalid country ID.'];
        }

        $row = DB::table('country')->where('id', $id)->first();
        if ($row === null) {
            $this->setFail();
            $this->setResponseCode(404);
            return ['message' => 'Country not found.'];
        }

        $updates = [];
        if (Schema::hasColumn('country', 'enabled') && $request->has('enabled')) {
            $updates['enabled'] = (bool) $request->input('enabled');
        }
        if (Schema::hasColumn('country', 'order_index') && $request->has('order_index')) {
            $updates['order_index'] = min(255, max(0, (int) $request->input('order_index')));
        }
        $name = $request->input('name');
        if ($name !== null && $name !== '') {
            $updates['name'] = substr(trim((string) $name), 0, 64);
        }
        $dialCode = $request->input('dial_code');
        if ($dialCode !== null) {
            $updates['dial_code'] = substr((string) $dialCode, 0, 16);
        }

        if (empty($updates)) {
            return ['message' => 'Nothing to update.', 'country' => ['id' => $id]];
        }

        DB::table('country')->where('id', $id)->update($updates);

        return ['message' => 'Country updated.', 'country' => ['id' => $id]];
    }
}
