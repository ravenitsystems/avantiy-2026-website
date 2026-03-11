<?php

namespace App\Http\Controllers\Api\Administration;

use App\Http\Controllers\ApiBase;
use App\Services\CurrentUser;
use App\Services\ExchangeRate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class Currencyrefreshrate extends ApiBase
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

        $code = $row->code ?? '';
        if ($code === '') {
            $this->setFail();
            $this->setResponseCode(400);
            return ['message' => 'Currency has no code.'];
        }

        try {
            $rate = ExchangeRate::getRate($code);
        } catch (\Throwable $e) {
            $this->setFail();
            $this->setResponseCode(422);
            return ['message' => 'Could not fetch rate: ' . $e->getMessage()];
        }

        DB::table('currency')->where('id', $id)->update(['exchange_rate' => $rate]);

        return [
            'message' => 'Exchange rate updated.',
            'currency' => ['id' => $id, 'exchange_rate' => $rate],
        ];
    }
}
