<?php

namespace App\Http\Controllers\Api\Administration;

use App\Http\Controllers\ApiBase;
use App\Services\CurrentUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class Currencyupdate extends ApiBase
{
    public function handle(Request $request): array
    {
        if (! CurrentUser::isLoggedIn()) {
            $this->setFail();
            $this->setResponseCode(401);
            return ['message' => 'You must be logged in.'];
        }

        if (! Currencyindex::canAccessSiteAdmin()) {
            $this->setFail();
            $this->setResponseCode(403);
            return ['message' => 'You do not have permission.'];
        }

        $id1 = $request->route('id1');
        $id = $id1 !== null ? (int) $id1 : 0;
        if ($id <= 0) {
            $this->setFail();
            $this->setResponseCode(400);
            return ['message' => 'Invalid currency ID.'];
        }

        $row = DB::table('currency')->where('id', $id)->first();
        if ($row === null) {
            $this->setFail();
            $this->setResponseCode(404);
            return ['message' => 'Currency not found.'];
        }

        $updates = [];
        if ($request->has('enabled')) {
            $updates['enabled'] = (bool) $request->input('enabled');
        }
        $name = $request->input('name');
        if ($name !== null && $name !== '') {
            $updates['name'] = substr(trim((string) $name), 0, 64);
        }
        $code = $request->input('code');
        if ($code !== null && $code !== '') {
            $updates['code'] = strtoupper(substr(trim((string) $code), 0, 3));
        }
        $symbol = $request->input('symbol');
        if ($symbol !== null) {
            $updates['symbol'] = substr((string) $symbol, 0, 16);
        }
        if ($request->has('decimals')) {
            $updates['decimals'] = min(4, max(0, (int) $request->input('decimals')));
        }
        if ($request->has('exchange_rate')) {
            $updates['exchange_rate'] = (float) $request->input('exchange_rate');
        }

        if (empty($updates)) {
            return ['message' => 'Nothing to update.', 'currency' => ['id' => $id]];
        }

        DB::table('currency')->where('id', $id)->update($updates);

        return ['message' => 'Currency updated.', 'currency' => ['id' => $id]];
    }
}
