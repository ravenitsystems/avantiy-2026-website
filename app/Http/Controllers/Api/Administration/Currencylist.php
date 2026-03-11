<?php

namespace App\Http\Controllers\Api\Administration;

use App\Http\Controllers\ApiBase;
use App\Services\CurrentUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class Currencylist extends ApiBase
{
    public function handle(Request $request): array
    {
        if (! CurrentUser::isLoggedIn()) {
            $this->setFail();
            $this->setResponseCode(401);
            return ['message' => 'You must be logged in.', 'currencies' => []];
        }

        if (! Currencyindex::canAccessSiteAdmin()) {
            $this->setFail();
            $this->setResponseCode(403);
            return ['message' => 'You do not have permission.', 'currencies' => []];
        }

        if (! Schema::hasTable('currency')) {
            return ['currencies' => []];
        }

        $query = DB::table('currency')->where('enabled', true)->orderBy('code');
        $rows = $query->get(['id', 'name', 'code', 'symbol']);

        $currencies = $rows->map(fn ($r) => [
            'id' => (int) $r->id,
            'name' => $r->name ?? '',
            'code' => $r->code ?? '',
            'symbol' => $r->symbol ?? '',
        ])->values()->all();

        return ['currencies' => $currencies];
    }
}
