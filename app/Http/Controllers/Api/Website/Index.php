<?php

namespace App\Http\Controllers\Api\Website;

use App\Http\Controllers\ApiBase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class Index extends ApiBase
{
    public function handle(Request $request): array
    {
        $userId = $request->session()->get('user_id');
        if ($userId === null) {
            $this->setFail();
            $this->setResponseCode(401);

            return ['message' => 'You must be logged in to list websites.', 'websites' => []];
        }

        $rows = DB::table('website')
            ->where('user_id', $userId)
            ->orderByDesc('accessed_at')
            ->get();

        $websites = $rows->map(function ($row) {
            return [
                'id' => (int) $row->id,
                'site_name' => $row->site_name,
                'domain' => $row->domain ?? '',
                'duda_id' => $row->duda_id ?? '',
                'created_at' => $row->created_at,
                'accessed_at' => $row->accessed_at,
                'published_at' => $row->published_at,
                'is_published' => $row->published_at !== null,
            ];
        })->values()->all();

        return ['websites' => $websites];
    }
}
