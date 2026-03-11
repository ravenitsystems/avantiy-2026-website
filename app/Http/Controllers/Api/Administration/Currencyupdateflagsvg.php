<?php

namespace App\Http\Controllers\Api\Administration;

use App\Http\Controllers\ApiBase;
use App\Services\CurrentUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class Currencyupdateflagsvg extends ApiBase
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

        if (! Schema::hasColumn('currency', 'flag_svg')) {
            $this->setFail();
            $this->setResponseCode(400);
            return ['message' => 'Currency flag_svg column does not exist.'];
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

        $svgRaw = $request->input('flag_svg');
        if ($svgRaw === null) {
            $svgRaw = $request->input('svg_content', '');
        }
        $svgRaw = trim((string) $svgRaw);

        if ($svgRaw === '') {
            DB::table('currency')->where('id', $id)->update(['flag_svg' => null]);
            return ['message' => 'Flag SVG cleared.', 'currency' => ['id' => $id], 'flag_svg' => null];
        }

        DB::table('currency')->where('id', $id)->update(['flag_svg' => $svgRaw]);

        return ['message' => 'Flag SVG updated.', 'currency' => ['id' => $id], 'flag_svg' => $svgRaw];
    }
}
