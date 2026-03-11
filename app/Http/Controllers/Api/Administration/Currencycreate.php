<?php

namespace App\Http\Controllers\Api\Administration;

use App\Http\Controllers\ApiBase;
use App\Services\CurrentUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class Currencycreate extends ApiBase
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

        $id = (int) $request->input('id', 0);
        if ($id <= 0 || $id > 999999) {
            $this->setFail();
            $this->setResponseCode(400);
            return ['message' => 'A valid numeric ID is required (e.g. ISO 4217).'];
        }

        $name = trim((string) $request->input('name', ''));
        if ($name === '') {
            $this->setFail();
            $this->setResponseCode(400);
            return ['message' => 'Name is required.'];
        }
        $name = substr($name, 0, 64);

        $code = strtoupper(substr(trim((string) $request->input('code', '')), 0, 3));
        if ($code === '') {
            $this->setFail();
            $this->setResponseCode(400);
            return ['message' => 'Code is required (3 letters).'];
        }

        $symbol = trim((string) $request->input('symbol', '$'));
        $symbol = substr($symbol, 0, 16);
        if ($symbol === '') {
            $symbol = $code;
        }

        $decimals = min(4, max(0, (int) $request->input('decimals', 2)));
        $exchangeRate = (float) $request->input('exchange_rate', 0);
        $enabled = $request->boolean('enabled', true);

        if (DB::table('currency')->where('id', $id)->exists()) {
            $this->setFail();
            $this->setResponseCode(409);
            return ['message' => 'A currency with this ID already exists.'];
        }
        if (DB::table('currency')->where('code', $code)->exists()) {
            $this->setFail();
            $this->setResponseCode(409);
            return ['message' => 'A currency with this code already exists.'];
        }

        $row = [
            'id' => $id,
            'name' => $name,
            'code' => $code,
            'symbol' => $symbol,
            'decimals' => $decimals,
            'exchange_rate' => $exchangeRate,
            'enabled' => $enabled,
        ];
        if (Schema::hasColumn('currency', 'last_processed_at')) {
            $row['last_processed_at'] = null;
        }
        if (Schema::hasColumn('currency', 'flag_svg')) {
            $row['flag_svg'] = null;
        }

        DB::table('currency')->insert($row);

        return ['message' => 'Currency created.', 'currency' => ['id' => $id, 'name' => $name, 'code' => $code]];
    }
}
