<?php

namespace App\Http\Controllers\Api\Website;

use App\Http\Controllers\ApiBase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class Create extends ApiBase
{
    public function handle(Request $request): array
    {
        $userId = $request->session()->get('user_id');
        if ($userId === null) {
            $this->setFail();
            $this->setResponseCode(401);

            return ['message' => 'You must be logged in to create a website.'];
        }

        $siteName = $request->input('site_name');
        if (empty(trim((string) $siteName))) {
            $this->setFail();
            $this->setResponseCode(400);

            return ['message' => 'Site name is required.'];
        }

        $now = now();
        $data = [
            'site_name' => trim($siteName),
            'domain' => trim((string) $request->input('domain', '')),
            'duda_id' => trim((string) $request->input('duda_id', '')),
            'created_at' => $now,
            'accessed_at' => $now,
            'published_at' => null,
            'user_id' => $userId,
        ];

        $id = DB::table('website')->insertGetId($data);
        $data['id'] = $id;

        return $data;
    }
}
